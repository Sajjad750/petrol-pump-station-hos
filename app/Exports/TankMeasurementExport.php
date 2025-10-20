<?php

namespace App\Exports;

use App\Models\TankMeasurement;
use Maatwebsite\Excel\Concerns\FromQuery;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use Illuminate\Support\Carbon;

class TankMeasurementExport implements FromQuery, WithHeadings, WithMapping, WithStyles
{
    protected array $filters;

    public function __construct(array $filters = [])
    {
        $this->filters = $filters;
    }

    public function query()
    {
        $query = TankMeasurement::query()->with('tank');

        // Apply filters
        if (!empty($this->filters['from_date'])) {
            $query->whereDate('created_at', '>=', $this->filters['from_date']);
        }

        if (!empty($this->filters['to_date'])) {
            $query->whereDate('created_at', '<=', $this->filters['to_date']);
        }

        if (!empty($this->filters['status'])) {
            $query->where('status', $this->filters['status']);
        }

        if (!empty($this->filters['tank_id'])) {
            $query->where('tank_id', $this->filters['tank_id']);
        }

        if (!empty($this->filters['station_id'])) {
            $query->where('station_id', $this->filters['station_id']);
        }

        return $query->orderBy('created_at', 'desc');
    }

    public function headings(): array
    {
        return [
            'ID',
            'Device ID',
            'Tank ID',
            'Tank Name',
            'Volume',
            'Temperature',
            'Water Level',
            'Status',
            'Measurement Date',
            'Measurement Time',
        ];
    }

    public function map($measurement): array
    {
        return [
            $measurement->id,
            $measurement->device_id,
            $measurement->tank_id,
            $measurement->tank?->name ?? 'N/A',
            number_format($measurement->volume, 2) . ' L',
            number_format($measurement->temperature, 2) . ' °C',
            number_format($measurement->water_level, 2) . ' mm',
            ucfirst($measurement->status ?? 'N/A'),
            Carbon::parse($measurement->created_at)->format('Y-m-d'),
            Carbon::parse($measurement->created_at)->format('H:i:s'),
        ];
    }

    public function styles(Worksheet $sheet): array
    {
        return [
            1 => ['font' => ['bold' => true]],
        ];
    }
}
