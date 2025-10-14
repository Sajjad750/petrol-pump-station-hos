<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\PumpTransaction;
use App\Models\Station;
use App\Models\SyncLog;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class SyncController extends Controller
{
    /**
     * Sync pump transactions from BOS
     */
    public function syncPumpTransactions(Request $request): JsonResponse
    {
        Log::debug("syncPumpTransactions: ", (array)$request->all());

        $station = $request->get('station');
        $ptsId = $request->input('pts_id');
        $transactions = $request->input('data', []);
        Log::debug("transactions: ", (array)$transactions);

        $created = 0;
        $updated = 0;
        $failed = 0;
        $errors = [];

        DB::beginTransaction();

        try {
            foreach ($transactions as $transactionData) {
                try {
                    // Create sync log entry
                    $syncLog = SyncLog::createLog(
                        $station->id,
                        'pump_transactions',
                        'create',
                        $transactionData,
                        'pending'
                    );

                    // Prepare transaction data for HOS
                    $hosTransactionData = $this->prepareTransactionData($transactionData, $station->id);

                    // Use updateOrCreate to handle duplicates
                    $transaction = PumpTransaction::updateOrCreate(
                        [
                            'station_id' => $station->id,
                            'bos_transaction_id' => $transactionData['id'],
                        ],
                        array_merge($hosTransactionData, [
                            'synced_at' => now(),
                        ])
                    );

                    // Mark sync log as successful
                    $syncLog->markAsSuccessful([
                        'transaction_id' => $transaction->id,
                        'action' => $transaction->wasRecentlyCreated ? 'created' : 'updated',
                    ]);

                    if ($transaction->wasRecentlyCreated) {
                        $created++;
                    } else {
                        $updated++;
                    }
                } catch (\Exception $e) {
                    $failed++;
                    $errors[] = [
                        'transaction_id' => $transactionData['id'] ?? 'unknown',
                        'error' => $e->getMessage(),
                    ];

                    // Mark sync log as failed
                    if (isset($syncLog)) {
                        $syncLog->markAsFailed($e->getMessage());
                    }

                    Log::error('Failed to sync pump transaction', [
                        'station_id' => $station->id,
                        'pts_id' => $ptsId,
                        'transaction_data' => $transactionData,
                        'error' => $e->getMessage(),
                    ]);
                }
            }

            // Update station's last sync time
            $station->updateLastSync();

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => "Synced {$created} created, {$updated} updated, {$failed} failed transactions",
                'data' => [
                    'created' => $created,
                    'updated' => $updated,
                    'failed' => $failed,
                    'errors' => $errors,
                ],
            ]);
        } catch (\Exception $e) {
            DB::rollBack();

            Log::error('Sync pump transactions failed', [
                'station_id' => $station->id,
                'pts_id' => $ptsId,
                'error' => $e->getMessage(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Sync operation failed: ' . $e->getMessage(),
                'data' => [
                    'created' => $created,
                    'updated' => $updated,
                    'failed' => $failed,
                    'errors' => $errors,
                ],
            ], 500);
        }
    }

    /**
     * Prepare transaction data for HOS storage
     */
    private function prepareTransactionData(array $bosData, int $stationId): array
    {
        return [
            'uuid' => Str::uuid7(),
            'pts2_device_id' => $bosData['pts2_device_id'],
            'pts_id' => $bosData['pts_id'],
            'request_id' => $bosData['request_id'] ?? null,
            'date_time_start' => $bosData['date_time_start'],
            'date_time_end' => $bosData['date_time_end'] ?? null,
            'pts_pump_id' => $bosData['pts_pump_id'],
            'pts_nozzle_id' => $bosData['pts_nozzle_id'],
            'pts_fuel_grade_id' => $bosData['pts_fuel_grade_id'],
            'pts_tank_id' => $bosData['pts_tank_id'],
            'transaction_number' => $bosData['transaction_number'],
            'volume' => $bosData['volume'],
            'tc_volume' => $bosData['tc_volume'],
            'price' => $bosData['price'],
            'amount' => $bosData['amount'],
            'total_volume' => $bosData['total_volume'],
            'total_amount' => $bosData['total_amount'],
            'tag' => $bosData['tag'],
            'pts_user_id' => $bosData['pts_user_id'],
            'pts_configuration_id' => $bosData['pts_configuration_id'],
            'shift_id' => $bosData['shift_id'],
//            'fuel_grade_id' => $bosData['fuel_grade_id'],
//            'pump_id' =>$bosData['pump_id'],
//            'tank_id' => $bosData['tank_id'],
            'station_id' => $stationId,
            'bos_transaction_id' => $bosData['id'],
            'bos_uuid' => $bosData['uuid'],
            'created_at_bos' => $bosData['created_at'] ?? null,
            'updated_at_bos' => $bosData['updated_at'] ?? null,
        ];
    }

    /**
     * Get sync status for a station
     */
    public function getSyncStatus(Request $request): JsonResponse
    {
        $station = $request->get('station');

        $recentLogs = SyncLog::forStation($station->id)
            ->recent(24)
            ->orderBy('created_at', 'desc')
            ->limit(10)
            ->get();

        $successCount = SyncLog::forStation($station->id)
            ->successful()
            ->recent(24)
            ->count();

        $failedCount = SyncLog::forStation($station->id)
            ->failed()
            ->recent(24)
            ->count();

        return response()->json([
            'success' => true,
            'data' => [
                'station' => [
                    'id' => $station->id,
                    'pts_id' => $station->pts_id,
                    'site_name' => $station->site_name,
                    'last_sync_at' => $station->last_sync_at,
                    'connectivity_status' => $station->connectivity_status,
                ],
                'sync_stats' => [
                    'successful_syncs_24h' => $successCount,
                    'failed_syncs_24h' => $failedCount,
                    'success_rate' => $successCount + $failedCount > 0
                        ? round(($successCount / ($successCount + $failedCount)) * 100, 2)
                        : 0,
                ],
                'recent_logs' => $recentLogs,
            ],
        ]);
    }
}
