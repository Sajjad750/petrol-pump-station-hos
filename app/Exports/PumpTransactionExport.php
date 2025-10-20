<?php

namespace App\Exports;

use App\Models\PumpTransaction;
use Maatwebsite\Excel\Concerns\FromQuery;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use Illuminate\Support\Carbon;

class PumpTransactionExport implements FromQuery, WithHeadings, WithMapping, WithStyles
{
    protected array $filters;

    public function __construct(array $filters = [])
    {
        $this->filters = $filters;
    }

    public function query()
    {
        $query = PumpTransaction::query()->with(['pump', 'shift']);

        // Apply filters
        if (!empty($this->filters['from_date'])) {
            $query->whereDate('created_at', '>=', $this->filters['from_date']);
        }

        if (!empty($this->filters['to_date'])) {
            $query->whereDate('created_at', '<=', $this->filters['to_date']);
        }

        if (!empty($this->filters['from_time']) && !empty($this->filters['to_time'])) {
            $query->whereTime('created_at', '>=', $this->filters['from_time'])
                  ->whereTime('created_at', '<=', $this->filters['to_time']);
        }

        return $query->orderBy('created_at', 'desc');
    }

    public function headings(): array
    {
        return [
            'ID',
            'Device ID',
            'Pump Name',
            'Shift ID',
            'Total Volume',
            'Total Amount',
            'Unit Price',
            'Transaction Date',
            'Transaction Time',
            'Status',
        ];
    }

    public function map($transaction): array
    {
        return [
            $transaction->id,
            $transaction->device_id,
            $transaction->pump?->name ?? 'N/A',
            $transaction->shift_id,
            number_format($transaction->total_volume, 2) . ' L',
            '₹' . number_format($transaction->total_amount, 2),
            '₹' . number_format($transaction->unit_price, 2),
            Carbon::parse($transaction->created_at)->format('Y-m-d'),
            Carbon::parse($transaction->created_at)->format('H:i:s'),
            ucfirst($transaction->status ?? 'N/A'),
        ];
    }

    public function styles(Worksheet $sheet): array
    {
        return [
            1 => ['font' => ['bold' => true]],
        ];
    }
}
