<?php

namespace App\Jobs;

use App\Models\TankDelivery;
use App\Models\User;
use Barryvdh\Snappy\Facades\SnappyPdf;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Throwable;

class GenerateTankDeliveriesPdf implements ShouldQueue
{
    use Queueable, InteractsWithQueue, SerializesModels;

    public function __construct(
        protected array $filters,
        protected string $filename,
        protected ?int $userId = null,
    ) {
    }

    public function handle(): void
    {
        set_time_limit(1800);
        ini_set('memory_limit', '4096M');

        try {
            $query = TankDelivery::query()
                ->leftJoin('stations', 'tank_deliveries.station_id', '=', 'stations.id')
                ->leftJoin('fuel_grades', function ($join) {
                    $join->on('tank_deliveries.fuel_grade_id', '=', 'fuel_grades.id')
                        ->on('tank_deliveries.station_id', '=', 'fuel_grades.station_id');
                });

            $fromDate = $this->filters['from_date'] ?? null;
            $toDate = $this->filters['to_date'] ?? null;
            $fromTime = $this->filters['from_time'] ?? '00:00:00';
            $toTime = $this->filters['to_time'] ?? '23:59:59';

            if ($fromDate && $toDate) {
                $query->whereBetween('tank_deliveries.synced_at', [$fromDate . ' ' . $fromTime, $toDate . ' ' . $toTime]);
            } elseif ($fromDate) {
                $query->where('tank_deliveries.synced_at', '>=', $fromDate . ' ' . $fromTime);
            } elseif ($toDate) {
                $query->where('tank_deliveries.synced_at', '<=', $toDate . ' ' . $toTime);
            }

            if (!empty($this->filters['fuel_grade_id'])) {
                $query->where('tank_deliveries.fuel_grade_id', $this->filters['fuel_grade_id']);
            }

            if (!empty($this->filters['tank'])) {
                $query->where('tank_deliveries.tank', $this->filters['tank']);
            }

            if (!empty($this->filters['volume_min'])) {
                $query->where('tank_deliveries.absolute_product_volume', '>=', $this->filters['volume_min']);
            }

            if (!empty($this->filters['volume_max'])) {
                $query->where('tank_deliveries.absolute_product_volume', '<=', $this->filters['volume_max']);
            }

            $rows = $query
                ->orderBy('tank_deliveries.synced_at', 'desc')
                ->select([
                    'tank_deliveries.*',
                    'stations.site_name',
                    'stations.pts_id as site_ref',
                    'fuel_grades.name as fuel_grade_name_from_table',
                    'tank_deliveries.fuel_grade_name as fuel_grade_name_from_field',
                ])
                ->get()
                ->map(function ($delivery) {
                    $tankFormatted = 'T-' . str_pad((string) $delivery->tank, 2, '0', STR_PAD_LEFT);
                    $productName = $delivery->fuel_grade_name_from_table ?? $delivery->fuel_grade_name_from_field ?? '';
                    $dateTime = '';

                    if ($delivery->synced_at) {
                        $dateTime = is_string($delivery->synced_at)
                            ? $delivery->synced_at
                            : $delivery->synced_at->format('Y-m-d H:i:s');
                    }

                    return [
                        'site' => $delivery->site_name ?? '',
                        'site_ref' => $delivery->site_ref ?? '',
                        'date_time' => $dateTime,
                        'tank' => $tankFormatted,
                        'product' => $productName,
                        'volume' => $delivery->absolute_product_volume ?? 0,
                    ];
                });

            $totalCount = $rows->count();

            Log::info('Tank Deliveries PDF Export Job Started (Snappy)', [
                'filename' => $this->filename,
                'total_records' => $totalCount,
                'user_id' => $this->userId,
                'filters' => $this->filters,
            ]);

            $filePath = 'exports/' . $this->filename;

            $filtersDisplay = [
                'From Date' => $this->filters['from_date'] ?? null,
                'To Date' => $this->filters['to_date'] ?? null,
                'From Time' => $this->filters['from_time'] ?? null,
                'To Time' => $this->filters['to_time'] ?? null,
                'Product' => $this->filters['fuel_grade_id'] ?? null,
                'Tank' => $this->filters['tank'] ?? null,
                'Volume Min' => $this->filters['volume_min'] ?? null,
                'Volume Max' => $this->filters['volume_max'] ?? null,
            ];

            $html = view('hos-reports.pdf.tank-deliveries', [
                'filters' => array_filter($filtersDisplay),
                'records' => $rows->toArray(),
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

            Log::info('Tank Deliveries PDF Export Completed (Snappy)', [
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
            Log::error('Tank Deliveries PDF Export Job Failed (Snappy)', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'filters' => $this->filters,
                'filename' => $this->filename,
            ]);

            throw $e;
        }
    }

    public function failed(Throwable $exception): void
    {
        Log::error('Tank Deliveries PDF Export Job Failed (Snappy)', [
            'error' => $exception->getMessage(),
            'trace' => $exception->getTraceAsString(),
            'filters' => $this->filters,
            'filename' => $this->filename,
            'user_id' => $this->userId,
        ]);
    }
}
