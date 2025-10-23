<?php

namespace App\Exports;

use App\Models\PaymentModeWiseSummary;
use Maatwebsite\Excel\Concerns\FromQuery;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use Illuminate\Support\Carbon;

class PaymentModeWiseSummaryExport implements FromQuery, WithHeadings, WithMapping, WithStyles
{
    protected array $filters;

    public function __construct(array $filters = [])
    {
        $this->filters = $filters;
    }

    public function query()
    {
        $query = PaymentModeWiseSummary::query()->with('shift');

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

        if (!empty($this->filters['payment_mode'])) {
            $query->where('payment_mode', 'like', '%' . $this->filters['payment_mode'] . '%');
        }

        return $query->orderBy('created_at', 'desc');
    }

    public function headings(): array
    {
        return [
            'ID',
            'Device ID',
            'Shift ID',
            'Shift Start Time',
            'Payment Mode',
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
            $summary->shift?->start_time ? Carbon::parse($summary->shift->start_time)->format('Y-m-d H:i:s') : 'N/A',
            ucfirst($summary->payment_mode ?? 'N/A'),
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
