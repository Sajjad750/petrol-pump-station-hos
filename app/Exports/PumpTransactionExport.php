<?php

namespace App\Exports;

use App\Models\PumpTransaction;
use Maatwebsite\Excel\Concerns\FromQuery;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;

class PumpTransactionExport implements FromQuery, WithHeadings, WithMapping, WithStyles
{
    protected array $filters;

    public function __construct(array $filters = [])
    {
        $this->filters = $filters;
    }

    public function query()
    {
        $query = PumpTransaction::query()
            ->leftJoin('stations', 'pump_transactions.station_id', '=', 'stations.id')
            ->select(
                'pump_transactions.*',
                'stations.site_name',
                DB::raw('(SELECT name FROM fuel_grades WHERE CAST(fuel_grades.pts_fuel_grade_id AS CHAR) = CAST(pump_transactions.pts_fuel_grade_id AS CHAR) AND fuel_grades.station_id = pump_transactions.station_id LIMIT 1) as fuel_grade_name')
            );

        $fromDate = $this->filters['from_date'] ?? null;
        $toDate = $this->filters['to_date'] ?? null;
        $fromTime = $this->filters['from_time'] ?? '00:00:00';
        $toTime = $this->filters['to_time'] ?? '23:59:59';

        if ($fromDate && $toDate) {
            $query->whereBetween('pump_transactions.date_time_start', [
                $fromDate . ' ' . $fromTime,
                $toDate . ' ' . $toTime,
            ]);
        } elseif ($fromDate) {
            $query->where('pump_transactions.date_time_start', '>=', $fromDate . ' ' . $fromTime);
        } elseif ($toDate) {
            $query->where('pump_transactions.date_time_start', '<=', $toDate . ' ' . $toTime);
        }

        if (!empty($this->filters['station_id'])) {
            $query->where('pump_transactions.station_id', $this->filters['station_id']);
        }

        if (!empty($this->filters['pump_id'])) {
            $query->where('pump_transactions.pts_pump_id', 'like', '%' . $this->filters['pump_id'] . '%');
        }

        if (!empty($this->filters['mode_of_payment'])) {
            $query->where('pump_transactions.mode_of_payment', $this->filters['mode_of_payment']);
        }

        if (!empty($this->filters['product_id'])) {
            $query->where('pump_transactions.pts_fuel_grade_id', $this->filters['product_id']);
        }

        return $query->orderBy('pump_transactions.date_time_start', 'desc');
    }

    public function headings(): array
    {
        return [
            'Site',
            'Transaction ID',
            'Date & Time',
            'Pump',
            'Nozzle',
            'Product',
            'Unit Price (SAR)',
            'Volume (L)',
            'Amount (SAR)',
            'Payment Mode',
        ];
    }

    public function map($transaction): array
    {
        $dateTime = $transaction->date_time_start
            ? Carbon::parse($transaction->date_time_start)->format('Y-m-d H:i:s')
            : '';

        return [
            $transaction->site_name ?? '',
            $transaction->transaction_number ?? '',
            $dateTime,
            $transaction->pts_pump_id ?? '',
            $transaction->pts_nozzle_id ?? '',
            $transaction->fuel_grade_name ?? '',
            $transaction->price !== null ? round((float) $transaction->price, 2) : 0,
            $transaction->volume !== null ? round((float) $transaction->volume, 2) : 0,
            $transaction->amount !== null ? round((float) $transaction->amount, 2) : 0,
            ucfirst($transaction->mode_of_payment ?? ''),
        ];
    }

    public function styles(Worksheet $sheet): array
    {
        return [
            1 => ['font' => ['bold' => true]],
        ];
    }
}
