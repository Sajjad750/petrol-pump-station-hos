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
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Throwable;

class GenerateSalesSummaryPdf implements ShouldQueue
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
                })
                ->leftJoin('pts_users', function ($join) {
                    $join->on('pump_transactions.pts_user_id', '=', 'pts_users.pts_user_id')
                        ->on('pump_transactions.station_id', '=', 'pts_users.station_id');
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

            $transactions = $query->select([
                'pump_transactions.*',
                'stations.site_name',
                'fuel_grades.name as fuel_grade_name',
                'pts_users.login as attendant_login',
            ])->get();

            // Sales Type Wise Summary
            $salesTypeSummary = $transactions->groupBy('mode_of_payment')
                ->map(function ($group) {
                    $mode = $group->first()->mode_of_payment ?? 'Unknown';

                    return [
                        'sales_type' => ucfirst($mode),
                        'volume' => $group->sum('volume'),
                        'total_amount' => $group->sum('amount'),
                        'sales_count' => $group->count(),
                    ];
                })->values();

            // Product Wise Summary
            $productSummary = $transactions->groupBy('fuel_grade_name')
                ->map(function ($group) {
                    $name = $group->first()->fuel_grade_name ?? 'Unknown';
                    $salesCount = $group->count();

                    return [
                        'product_name' => $name,
                        'volume' => $group->sum('volume'),
                        'total_amount' => $group->sum('amount'),
                        'sales_count' => $salesCount,
                        'avg_sales_amount' => $salesCount > 0 ? $group->sum('amount') / $salesCount : 0,
                    ];
                })->values();

            // Attendant Wise Summary
            $attendantSummary = $transactions->groupBy('attendant_login')
                ->map(function ($group) {
                    $name = $group->first()->attendant_login ?? 'Unknown';

                    return [
                        'attendant_name' => $name,
                        'volume' => $group->sum('volume'),
                        'total_amount' => $group->sum('amount'),
                        'transactions_count' => $group->count(),
                    ];
                })->values();

            $totalVolume = $transactions->sum('volume');
            $totalAmount = $transactions->sum('amount');
            $totalSalesCount = $transactions->count();

            $filtersDisplay = [
                'Station' => !empty($this->filters['station_id']) ? Station::query()->whereKey($this->filters['station_id'])->value('site_name') : null,
                'From Date' => $this->filters['from_date'] ?? null,
                'From Time' => $this->filters['from_time'] ?? null,
                'To Date' => $this->filters['to_date'] ?? null,
                'To Time' => $this->filters['to_time'] ?? null,
                'Product' => $this->filters['product_id'] ?? null,
            ];

            Log::info('Sales Summary PDF Export Job Started (Snappy)', [
                'filename' => $this->filename,
                'total_records' => $totalSalesCount,
                'user_id' => $this->userId,
                'filters' => $this->filters,
            ]);

            $filePath = 'exports/' . $this->filename;

            $html = view('hos-reports.pdf.sales-summary', [
                'filters' => array_filter($filtersDisplay),
                'paymentSummary' => $salesTypeSummary,
                'productSummary' => $productSummary,
                'attendantSummary' => $attendantSummary,
                'totalVolume' => $totalVolume,
                'totalAmount' => $totalAmount,
                'totalSalesCount' => $totalSalesCount,
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

            Log::info('Sales Summary PDF Export Completed (Snappy)', [
                'filename' => $this->filename,
                'total_records' => $totalSalesCount,
                'user_id' => $this->userId,
            ]);

            if ($this->userId) {
                $user = User::find($this->userId);

                if ($user) {
                    $downloadUrl = route('hos-reports.download', ['filename' => $this->filename]);
                    $user->notify(new \App\Notifications\PdfExportCompleted($this->filename, $downloadUrl, $totalSalesCount));
                }
            }
        } catch (\Exception $e) {
            Log::error('Sales Summary PDF Export Job Failed (Snappy)', [
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
        Log::error('Sales Summary PDF Export Job Failed (Snappy)', [
            'error' => $exception->getMessage(),
            'trace' => $exception->getTraceAsString(),
            'filters' => $this->filters,
            'filename' => $this->filename,
            'user_id' => $this->userId,
        ]);
    }
}
