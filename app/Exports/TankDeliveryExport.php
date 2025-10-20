<?php

namespace App\Exports;

use App\Models\TankDelivery;
use Maatwebsite\Excel\Concerns\FromQuery;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use Illuminate\Support\Carbon;

class TankDeliveryExport implements FromQuery, WithHeadings, WithMapping, WithStyles
{
    protected array $filters;

    public function __construct(array $filters = [])
    {
        $this->filters = $filters;
    }

    public function query()
    {
        $query = TankDelivery::query()->with('station');

        // Apply filters
        if (!empty($this->filters['start_date'])) {
            $query->whereDate('start_datetime', '>=', $this->filters['start_date']);
        }

        if (!empty($this->filters['end_date'])) {
            $query->whereDate('start_datetime', '<=', $this->filters['end_date']);
        }

        if (!empty($this->filters['start_time']) && !empty($this->filters['end_time'])) {
            $query->whereTime('start_datetime', '>=', $this->filters['start_time'])
                  ->whereTime('start_datetime', '<=', $this->filters['end_time']);
        }

        if (!empty($this->filters['tank'])) {
            $query->where('tank', $this->filters['tank']);
        }

        if (!empty($this->filters['tank_id'])) {
            $query->where('tank', 'like', '%' . $this->filters['tank_id'] . '%');
        }

        if (!empty($this->filters['station_id'])) {
            $query->where('station_id', $this->filters['station_id']);
        }

        return $query->orderBy('start_datetime', 'desc');
    }

    public function headings(): array
    {
        return [
            'ID',
            'Device ID',
            'Tank ID',
            'Station Name',
            'Delivered Volume',
            'Start Time',
            'End Time',
            'Duration',
            'Delivery Invoice',
        ];
    }

    public function map($delivery): array
    {
        $startTime = $delivery->start_datetime ? Carbon::parse($delivery->start_datetime) : null;
        $endTime = $delivery->end_datetime ? Carbon::parse($delivery->end_datetime) : null;
        $duration = ($startTime && $endTime) ? $startTime->diffInMinutes($endTime) . ' mins' : 'N/A';

        return [
            $delivery->id,
            $delivery->pts_id,
            $delivery->tank,
            $delivery->station?->name ?? 'N/A',
            number_format($delivery->received_product_volume ?? 0, 2) . ' L',
            $startTime ? $startTime->format('Y-m-d H:i:s') : 'N/A',
            $endTime ? $endTime->format('Y-m-d H:i:s') : 'N/A',
            $duration,
            $delivery->pts_delivery_id ?? 'N/A',
        ];
    }

    public function styles(Worksheet $sheet): array
    {
        return [
            1 => ['font' => ['bold' => true]],
        ];
    }
}
