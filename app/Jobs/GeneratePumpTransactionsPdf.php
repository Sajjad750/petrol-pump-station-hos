<?php

namespace App\Jobs;

use App\Models\PumpTransaction;
use App\Models\User;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Throwable;
use Barryvdh\Snappy\Facades\SnappyPdf;

class GeneratePumpTransactionsPdf implements ShouldQueue
{
    use Queueable, InteractsWithQueue, SerializesModels;

    protected array $filters;

    protected string $filename;

    protected ?int $userId;

    protected int $recordsPerChunk = 5000; // Process 5000 records per chunk

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
        // Increase execution time and memory for PDF generation
        set_time_limit(1800); // 30 minutes
        ini_set('memory_limit', '4096M'); // 4GB

        try {
            // Build query with only necessary columns
            $query = PumpTransaction::query()
                ->select([
                    'pump_transactions.id',
                    'pump_transactions.station_id',
                    'pump_transactions.date_time_start',
                    'pump_transactions.pts_pump_id',
                    'pump_transactions.pts_nozzle_id',
                    'pump_transactions.pts_fuel_grade_id',
                    'pump_transactions.price',
                    'pump_transactions.volume',
                    'pump_transactions.amount',
                    'pump_transactions.mode_of_payment',
                ])
                ->with(['station:id,site_name', 'fuelGrade:id,name']);

            // Apply filters
            $this->applyFilters($query);

            $totalCount = (clone $query)->count();
            Log::info('PDF Export Job Started (Snappy)', [
                'filename' => $this->filename,
                'total_records' => $totalCount,
                'user_id' => $this->userId,
                'filters' => $this->filters,
            ]);

            $filePath = 'exports/' . $this->filename;

            // Generate HTML file in chunks
            $this->generatePdfWithSnappy($query, $totalCount, $filePath);

            Log::info('PDF Export Completed (Snappy)', [
                'filename' => $this->filename,
                'total_records' => $totalCount,
                'user_id' => $this->userId,
            ]);

            // Send notification to user if user_id is provided
            if ($this->userId) {
                $user = User::find($this->userId);

                if ($user) {
                    $downloadUrl = route('hos-reports.download', ['filename' => $this->filename]);
                    $user->notify(new \App\Notifications\PdfExportCompleted($this->filename, $downloadUrl, $totalCount));
                }
            }
        } catch (\Exception $e) {
            Log::error('PDF Export Job Failed (Snappy)', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'filters' => $this->filters,
                'filename' => $this->filename,
            ]);

            throw $e;
        }
    }

    /**
     * Generate PDF using Snappy with chunked HTML generation.
     */
    protected function generatePdfWithSnappy($query, int $totalCount, string $filePath): void
    {
        $tempHtmlPath = 'temp/' . Str::uuid() . '.html';
        $fullTempHtmlPath = Storage::disk('local')->path($tempHtmlPath);

        // Generate HTML header
        $htmlContent = $this->renderHtmlHeader();
        Storage::disk('local')->put($tempHtmlPath, $htmlContent);

        $processed = 0;
        $totalVolume = 0;
        $totalAmount = 0;

        // Process transactions in chunks and append to HTML file
        $query->orderBy('pump_transactions.date_time_start', 'desc')
            ->chunk($this->recordsPerChunk, function ($transactions) use ($fullTempHtmlPath, &$processed, &$totalVolume, &$totalAmount, $totalCount) {
                $chunkHtml = $this->renderHtmlChunk($transactions);
                file_put_contents($fullTempHtmlPath, $chunkHtml, FILE_APPEND);

                foreach ($transactions as $transaction) {
                    $totalVolume += $transaction->volume ?? 0;
                    $totalAmount += $transaction->amount ?? 0;
                }
                $processed += $transactions->count();

                Log::info('PDF Export Progress (Snappy)', [
                    'processed' => $processed,
                    'total' => $totalCount,
                    'percentage' => round(($processed / $totalCount) * 100, 2),
                ]);

                gc_collect_cycles(); // Free up memory
            });

        // Generate HTML footer with summary
        $summary = [
            'total_records' => $totalCount,
            'total_volume' => $totalVolume,
            'total_amount' => $totalAmount,
        ];

        $htmlContent = $this->renderHtmlFooter($summary);
        file_put_contents($fullTempHtmlPath, $htmlContent, FILE_APPEND);

        // Convert HTML to PDF using Snappy
        try {
            $html = file_get_contents($fullTempHtmlPath);
            $snappy = SnappyPdf::loadHtml($html);
            $snappy->setTimeout(600); // allow up to 10 minutes for rendering
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
        } catch (\Exception $e) {
            Log::error('Snappy PDF conversion failed: ' . $e->getMessage(), [
                'html_path' => $fullTempHtmlPath,
                'error' => $e->getTraceAsString(),
            ]);

            throw $e;
        } finally {
            // Clean up temp HTML file
            if (Storage::disk('local')->exists($tempHtmlPath)) {
                Storage::disk('local')->delete($tempHtmlPath);
            }
        }
    }

    /**
     * Render HTML header for PDF.
     */
    protected function renderHtmlHeader(): string
    {
        return view('reports.pump_transactions_pdf_header', [
            'filters' => $this->filters,
        ])->render();
    }

    /**
     * Render HTML chunk for a batch of transactions.
     */
    protected function renderHtmlChunk($transactions): string
    {
        return view('reports.pump_transactions_pdf_chunk', [
            'transactions' => $transactions->map(function ($transaction) {
                return [
                    'site_name' => $transaction->station->site_name ?? 'N/A',
                    'date_time_start' => $transaction->date_time_start,
                    'pts_pump_id' => $transaction->pts_pump_id ?? 'N/A',
                    'pts_nozzle_id' => $transaction->pts_nozzle_id ?? 'N/A',
                    'product_name' => $transaction->fuelGrade->name ?? 'N/A',
                    'price' => $transaction->price,
                    'volume' => $transaction->volume,
                    'amount' => $transaction->amount,
                    'mode_of_payment' => $transaction->mode_of_payment ?? 'N/A',
                ];
            })->toArray(),
        ])->render();
    }

    /**
     * Render HTML footer with summary.
     */
    protected function renderHtmlFooter(array $summary): string
    {
        return view('reports.pump_transactions_pdf_footer', [
            'summary' => $summary,
        ])->render();
    }

    /**
     * Apply filters to the query.
     */
    protected function applyFilters($query): void
    {
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
    }

    /**
     * Handle a job failure.
     */
    public function failed(Throwable $exception): void
    {
        Log::error('PDF Export Job Failed (Snappy)', [
            'error' => $exception->getMessage(),
            'trace' => $exception->getTraceAsString(),
            'filters' => $this->filters,
            'filename' => $this->filename,
            'user_id' => $this->userId,
        ]);
    }
}
