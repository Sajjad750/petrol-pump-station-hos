<?php

namespace App\Exports;

use App\Models\Shift;
use Maatwebsite\Excel\Concerns\FromQuery;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use Illuminate\Support\Carbon;

class ShiftExport implements FromQuery, WithHeadings, WithMapping, WithStyles
{
    protected array $filters;

    public function __construct(array $filters = [])
    {
        $this->filters = $filters;
    }

    public function query()
    {
        $query = Shift::query()->with('user');

        // Apply filters
        if (!empty($this->filters['start_date'])) {
            $query->whereDate('start_time', '>=', $this->filters['start_date']);
        }

        if (!empty($this->filters['end_date'])) {
            $query->whereDate('start_time', '<=', $this->filters['end_date']);
        }

        if (!empty($this->filters['start_time']) && !empty($this->filters['end_time'])) {
            $query->whereTime('start_time', '>=', $this->filters['start_time'])
                  ->whereTime('start_time', '<=', $this->filters['end_time']);
        }

        if (!empty($this->filters['status'])) {
            $query->where('status', $this->filters['status']);
        }

        if (!empty($this->filters['close_type'])) {
            $query->where('close_type', $this->filters['close_type']);
        }

        if (!empty($this->filters['user_id'])) {
            $query->where('user_id', 'like', '%' . $this->filters['user_id'] . '%');
        }

        return $query->orderBy('start_time', 'desc');
    }

    public function headings(): array
    {
        return [
            'ID',
            'Device ID',
            'User ID',
            'User Name',
            'Start Time',
            'End Time',
            'Status',
            'Close Type',
            'Total Sales',
            'Total Volume',
        ];
    }

    public function map($shift): array
    {
        return [
            $shift->id,
            $shift->device_id,
            $shift->user_id,
            $shift->user?->name ?? 'N/A',
            Carbon::parse($shift->start_time)->format('Y-m-d H:i:s'),
            $shift->end_time ? Carbon::parse($shift->end_time)->format('Y-m-d H:i:s') : 'N/A',
            ucfirst($shift->status ?? 'N/A'),
            ucfirst($shift->close_type ?? 'N/A'),
            '₹' . number_format($shift->total_sales ?? 0, 2),
            number_format($shift->total_volume ?? 0, 2) . ' L',
        ];
    }

    public function styles(Worksheet $sheet): array
    {
        return [
            1 => ['font' => ['bold' => true]],
        ];
    }
}
