<?php

namespace App\Exports;

use App\Models\ProductWiseSummary;
use Maatwebsite\Excel\Concerns\FromQuery;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use Illuminate\Support\Carbon;

class ProductWiseSummaryExport implements FromQuery, WithHeadings, WithMapping, WithStyles
{
    protected array $filters;

    public function __construct(array $filters = [])
    {
        $this->filters = $filters;
    }

    public function query()
    {
        $query = ProductWiseSummary::query()->with('shift');

        // Apply filters
        if (!empty($this->filters['start_date'])) {
            $query->whereHas('shift', function ($q) {
                $q->whereDate('start_time', '>=', $this->filters['start_date']);
            });
        }

        if (!empty($this->filters['end_date'])) {
            $query->whereHas('shift', function ($q) {
                $q->whereDate('start_time', '<=', $this->filters['end_date']);
            });
        }

        if (!empty($this->filters['shift_id'])) {
            $query->where('shift_id', 'like', '%' . $this->filters['shift_id'] . '%');
        }

        if (!empty($this->filters['fuel_grade_id'])) {
            $query->where('fuel_grade_id', 'like', '%' . $this->filters['fuel_grade_id'] . '%');
        }

        return $query->orderBy('created_at', 'desc');
    }

    public function headings(): array
    {
        return [
            'ID',
            'Device ID',
            'Shift ID',
            'Fuel Grade ID',
            'Total Volume',
            'Total Amount',
            'Average Price/Liter',
            'Transaction Count',
            'Created At',
        ];
    }

    public function map($summary): array
    {
        $avgPrice = $summary->total_volume > 0
            ? number_format($summary->total_amount / $summary->total_volume, 2)
            : '0.00';

        return [
            $summary->id,
            $summary->device_id,
            $summary->shift_id,
            $summary->fuel_grade_id,
            number_format($summary->total_volume, 2) . ' L',
            '₹' . number_format($summary->total_amount, 2),
            '₹' . $avgPrice,
            $summary->transaction_count ?? 0,
            Carbon::parse($summary->created_at)->format('Y-m-d H:i:s'),
        ];
    }

    public function styles(Worksheet $sheet): array
    {
        return [
            1 => ['font' => ['bold' => true]],
        ];
    }
}
