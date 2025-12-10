<?php

namespace App\Jobs;

use App\Models\PumpTransaction;
use App\Models\Station;
use App\Models\User;
use Barryvdh\Snappy\Facades\SnappyPdf;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Throwable;

class GenerateAnalyticalSalesPdf implements ShouldQueue
{
    use Queueable, InteractsWithQueue, SerializesModels;

    protected array $filters;

    protected string $filename;

    protected ?int $userId;

    /**
     * Create a new job instance.
     */
    public function __construct(array $filters, string $filename, ?int $userId = null)
    {
        $this->filters = $filters;
        $this->filename = $filename;
        $this->userId = $userId;
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        set_time_limit(1800);
        ini_set('memory_limit', '4096M');

        try {
            $query = PumpTransaction::query()
                ->leftJoin('stations', 'pump_transactions.station_id', '=', 'stations.id')
                ->leftJoin('fuel_grades', function ($join) {
                    $join->on('pump_transactions.pts_fuel_grade_id', '=', 'fuel_grades.id')
                        ->on('pump_transactions.station_id', '=', 'fuel_grades.station_id');
                });

            if (!empty($this->filters['station_id'])) {
                $query->where('pump_transactions.station_id', $this->filters['station_id']);
            }

            $fromDate = $this->filters['from_date'] ?? null;
            $toDate = $this->filters['to_date'] ?? null;
            $fromTime = $this->filters['from_time'] ?? '00:00:00';
            $toTime = $this->filters['to_time'] ?? '23:59:59';

            if ($fromDate && $toDate) {
                $query->whereBetween('pump_transactions.date_time_start', [$fromDate . ' ' . $fromTime, $toDate . ' ' . $toTime]);
            } elseif ($fromDate) {
                $query->where('pump_transactions.date_time_start', '>=', $fromDate . ' ' . $fromTime);
            } elseif ($toDate) {
                $query->where('pump_transactions.date_time_start', '<=', $toDate . ' ' . $toTime);
            }

            if (!empty($this->filters['product_id'])) {
                $query->where('pump_transactions.pts_fuel_grade_id', $this->filters['product_id']);
            }

            $analyticalData = $query->select([
                    DB::raw('DATE(pump_transactions.date_time_start) as date'),
                    'stations.site_name',
                    'stations.pts_id as site_ref',
                    'fuel_grades.name as product_name',
                    DB::raw('SUM(pump_transactions.volume) as liters_sold'),
                    DB::raw('SUM(pump_transactions.amount) as total_amount'),
                    DB::raw('COUNT(pump_transactions.id) as transactions_count'),
                ])
                ->groupBy('date', 'stations.id', 'stations.site_name', 'stations.pts_id', 'fuel_grades.id', 'fuel_grades.name')
                ->orderBy('date', 'desc')
                ->orderBy('stations.site_name')
                ->orderBy('fuel_grades.name')
                ->get()
                ->map(function ($item) {
                    $avgTransactionAmount = $item->transactions_count > 0 ? $item->total_amount / $item->transactions_count : 0;

                    return [
                        'date' => $item->date,
                        'site' => $item->site_name ?? 'Unknown Site',
                        'site_ref' => $item->site_ref ?? '',
                        'product' => $item->product_name ?? 'Unknown Product',
                        'liters_sold' => (float) $item->liters_sold,
                        'amount' => (float) $item->total_amount,
                        'transactions' => (int) $item->transactions_count,
                        'avg_transaction_amount' => (float) $avgTransactionAmount,
                    ];
                });

            $totalLiters = $analyticalData->sum('liters_sold');
            $totalAmount = $analyticalData->sum('amount');
            $totalTransactions = $analyticalData->sum('transactions');
            $overallAvgTransaction = $totalTransactions > 0 ? $totalAmount / $totalTransactions : 0;
            $totalCount = $analyticalData->count();

            Log::info('Analytical Sales PDF Export Job Started (Snappy)', [
                'filename' => $this->filename,
                'total_records' => $totalCount,
                'user_id' => $this->userId,
                'filters' => $this->filters,
            ]);

            $filePath = 'exports/' . $this->filename;

            $filters = [
                'Station' => !empty($this->filters['station_id']) ? Station::query()->whereKey($this->filters['station_id'])->value('site_name') : null,
                'From Date' => $this->filters['from_date'] ?? null,
                'To Date' => $this->filters['to_date'] ?? null,
                'From Time' => $this->filters['from_time'] ?? null,
                'To Time' => $this->filters['to_time'] ?? null,
                'Product' => $this->filters['product_id'] ?? null,
            ];

            $html = view('hos-reports.pdf.analytical-sales', [
                'filters' => array_filter($filters),
                'records' => $analyticalData->toArray(),
                'totalLiters' => $totalLiters,
                'totalAmount' => $totalAmount,
                'totalTransactions' => $totalTransactions,
                'overallAvgTransaction' => $overallAvgTransaction,
                'generatedAt' => now(),
            ])->render();

            $snappy = SnappyPdf::loadHtml($html);
            $snappy->setTimeout(600);
            $snappy->setPaper('a4', 'landscape');
            $snappy->setOption('enable-local-file-access', true);
            $snappy->setOption('enable-javascript', true);
            $snappy->setOption('no-stop-slow-scripts', true);
            $snappy->setOption('lowquality', false);
            $snappy->setOption('encoding', 'UTF-8');
            $snappy->setOption('margin-top', 10);
            $snappy->setOption('margin-right', 10);
            $snappy->setOption('margin-bottom', 10);
            $snappy->setOption('margin-left', 10);
            $snappy->setOption('page-size', 'A4');
            $snappy->setOption('orientation', 'Landscape');

            Storage::disk('public')->put($filePath, $snappy->output());

            Log::info('Analytical Sales PDF Export Completed (Snappy)', [
                'filename' => $this->filename,
                'total_records' => $totalCount,
                'user_id' => $this->userId,
            ]);

            if ($this->userId) {
                $user = User::find($this->userId);

                if ($user) {
                    $downloadUrl = route('hos-reports.download', ['filename' => $this->filename]);
                    $user->notify(new \App\Notifications\PdfExportCompleted($this->filename, $downloadUrl, $totalCount));
                }
            }
        } catch (\Exception $e) {
            Log::error('Analytical Sales PDF Export Job Failed (Snappy)', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'filters' => $this->filters,
                'filename' => $this->filename,
            ]);

            throw $e;
        }
    }

    /**
     * Handle a job failure.
     */
    public function failed(Throwable $exception): void
    {
        Log::error('Analytical Sales PDF Export Job Failed (Snappy)', [
            'error' => $exception->getMessage(),
            'trace' => $exception->getTraceAsString(),
            'filters' => $this->filters,
            'filename' => $this->filename,
            'user_id' => $this->userId,
        ]);
    }
}
