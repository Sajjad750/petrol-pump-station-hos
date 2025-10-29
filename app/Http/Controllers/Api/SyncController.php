<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\FuelGrade;
use App\Models\PaymentModeWiseSummary;
use App\Models\ProductWiseSummary;
use App\Models\Pump;
use App\Models\PumpTransaction;
use App\Models\PtsUser;
use App\Models\Shift;
use App\Models\ShiftPumpTotal;
use App\Models\Station;
use App\Models\SyncLog;
use App\Models\TankDelivery;
use App\Models\TankInventory;
use App\Models\TankMeasurement;
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

            $totalItems = $created + $updated + $failed;
            $allFailed = $totalItems > 0 && $failed === $totalItems;

            return response()->json([
                'success' => !$allFailed,
                'message' => $allFailed
                    ? "All {$failed} transactions failed to sync"
                    : "Synced {$created} created, {$updated} updated, {$failed} failed transactions",
                'data' => [
                    'created' => $created,
                    'updated' => $updated,
                    'failed' => $failed,
                    'errors' => $errors,
                ],
            ], $allFailed ? 422 : 200);
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
            'starting_totalizer' => $bosData['starting_totalizer'],
            'total_volume' => $bosData['total_volume'],
            'total_amount' => $bosData['total_amount'],
            'tag' => $bosData['tag'],
            'pts_user_id' => $bosData['pts_user_id'],
            'pts_configuration_id' => $bosData['pts_configuration_id'],
            'bos_shift_id' => $bosData['shift_id'],
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

    /**
     * Sync pumps from BOS
     */
    public function syncPumps(Request $request): JsonResponse
    {
        $station = $request->get('station');
        $ptsId = $request->input('pts_id');
        $pumps = $request->input('data', []);

        $created = 0;
        $updated = 0;
        $failed = 0;
        $errors = [];

        DB::beginTransaction();

        try {
            foreach ($pumps as $pumpData) {
                try {
                    // Create sync log entry
                    $syncLog = SyncLog::createLog(
                        $station->id,
                        'pumps',
                        'create',
                        $pumpData,
                        'pending'
                    );

                    // Prepare pump data for HOS
                    $hosPumpData = $this->preparePumpData($pumpData, $station->id);

                    // Use updateOrCreate to handle duplicates
                    $pump = Pump::updateOrCreate(
                        [
                            'station_id' => $station->id,
                            'bos_pump_id' => $pumpData['id'],
                        ],
                        array_merge($hosPumpData, [
                            'synced_at' => now(),
                        ])
                    );

                    // Mark sync log as successful
                    $syncLog->markAsSuccessful([
                        'pump_id' => $pump->id,
                        'action' => $pump->wasRecentlyCreated ? 'created' : 'updated',
                    ]);

                    if ($pump->wasRecentlyCreated) {
                        $created++;
                    } else {
                        $updated++;
                    }
                } catch (\Exception $e) {
                    $failed++;
                    $errors[] = [
                        'pump_id' => $pumpData['id'] ?? 'unknown',
                        'error' => $e->getMessage(),
                    ];

                    // Mark sync log as failed
                    if (isset($syncLog)) {
                        $syncLog->markAsFailed($e->getMessage());
                    }

                    Log::error('Failed to sync pump', [
                        'station_id' => $station->id,
                        'pts_id' => $ptsId,
                        'pump_data' => $pumpData,
                        'error' => $e->getMessage(),
                    ]);
                }
            }

            // Update station's last sync time
            $station->updateLastSync();

            DB::commit();

            $totalItems = $created + $updated + $failed;
            $allFailed = $totalItems > 0 && $failed === $totalItems;

            return response()->json([
                'success' => !$allFailed,
                'message' => $allFailed
                    ? "All {$failed} pumps failed to sync"
                    : "Synced {$created} created, {$updated} updated, {$failed} failed pumps",
                'data' => [
                    'created' => $created,
                    'updated' => $updated,
                    'failed' => $failed,
                    'errors' => $errors,
                ],
            ], $allFailed ? 422 : 200);
        } catch (\Exception $e) {
            DB::rollBack();

            Log::error('Sync pumps failed', [
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
     * Prepare pump data for HOS storage
     */
    private function preparePumpData(array $bosData, int $stationId): array
    {
        return [
            'station_id' => $stationId,
            'bos_pump_id' => $bosData['id'],
            'bos_uuid' => $bosData['uuid'],
            'name' => $bosData['name'],
            'pump_id' => $bosData['pump_id'],
            'is_self_service' => $bosData['is_self_service'],
            'nozzles_count' => $bosData['nozzles_count'],
            'status' => $bosData['status'],
            'pts_pump_id' => $bosData['pts_pump_id'],
            'pts_port_id' => $bosData['pts_port_id'] ?? null,
            'pts_address_id' => $bosData['pts_address_id'] ?? null,
            'created_at_bos' => $bosData['created_at'] ?? null,
            'updated_at_bos' => $bosData['updated_at'] ?? null,
        ];
    }

    /**
     * Sync tank measurements from BOS
     */
    public function syncTankMeasurements(Request $request): JsonResponse
    {
        Log::debug("syncTankMeasurements: ", (array)$request->all());

        $station = $request->get('station');
        $ptsId = $request->input('pts_id');
        $tankMeasurements = $request->input('data', []);
        Log::debug("tank_measurements: ", (array)$tankMeasurements);

        $created = 0;
        $updated = 0;
        $failed = 0;
        $errors = [];

        DB::beginTransaction();

        try {
            foreach ($tankMeasurements as $tankMeasurementData) {
                try {
                    // Create sync log entry
                    $syncLog = SyncLog::createLog(
                        $station->id,
                        'tank_measurements',
                        'create',
                        $tankMeasurementData,
                        'pending'
                    );

                    // Prepare tank measurement data for HOS
                    $hosTankMeasurementData = $this->prepareTankMeasurementData($tankMeasurementData, $station->id);

                    // Use updateOrCreate to handle duplicates
                    $tankMeasurement = TankMeasurement::updateOrCreate(
                        [
                            'station_id' => $station->id,
                            'bos_tank_measurement_id' => $tankMeasurementData['id'],
                        ],
                        array_merge($hosTankMeasurementData, [
                            'synced_at' => now(),
                        ])
                    );

                    // Mark sync log as successful
                    $syncLog->markAsSuccessful([
                        'tank_measurement_id' => $tankMeasurement->id,
                        'action' => $tankMeasurement->wasRecentlyCreated ? 'created' : 'updated',
                    ]);

                    if ($tankMeasurement->wasRecentlyCreated) {
                        $created++;
                    } else {
                        $updated++;
                    }
                } catch (\Exception $e) {
                    $failed++;
                    $errors[] = [
                        'tank_measurement_id' => $tankMeasurementData['id'] ?? 'unknown',
                        'error' => $e->getMessage(),
                    ];

                    // Mark sync log as failed
                    if (isset($syncLog)) {
                        $syncLog->markAsFailed($e->getMessage());
                    }

                    Log::error('Failed to sync tank measurement', [
                        'station_id' => $station->id,
                        'pts_id' => $ptsId,
                        'tank_measurement_data' => $tankMeasurementData,
                        'error' => $e->getMessage(),
                    ]);
                }
            }

            // Update station's last sync time
            $station->updateLastSync();

            DB::commit();

            $totalItems = $created + $updated + $failed;
            $allFailed = $totalItems > 0 && $failed === $totalItems;

            return response()->json([
                'success' => !$allFailed,
                'message' => $allFailed
                    ? "All {$failed} tank measurements failed to sync"
                    : "Synced {$created} created, {$updated} updated, {$failed} failed tank measurements",
                'data' => [
                    'created' => $created,
                    'updated' => $updated,
                    'failed' => $failed,
                    'errors' => $errors,
                ],
            ], $allFailed ? 422 : 200);
        } catch (\Exception $e) {
            DB::rollBack();

            Log::error('Sync tank measurements failed', [
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
     * Prepare tank measurement data for HOS storage
     */
    private function prepareTankMeasurementData(array $bosData, int $stationId): array
    {
        return [
            'uuid' => Str::uuid7(),
            'request_id' => $bosData['request_id'] ?? null,
            'pts_id' => $bosData['pts_id'],
            'date_time' => $bosData['date_time'],
            'tank' => $bosData['tank'],
            'fuel_grade_id' => $bosData['fuel_grade_id'] ?? null,
            'fuel_grade_name' => $bosData['fuel_grade_name'] ?? null,
            'status' => $bosData['status'] ?? null,
            'alarms' => $bosData['alarms'] ?? [],
            'product_height' => $bosData['product_height'] ?? null,
            'water_height' => $bosData['water_height'] ?? null,
            'temperature' => $bosData['temperature'] ?? null,
            'product_volume' => $bosData['product_volume'] ?? null,
            'water_volume' => $bosData['water_volume'] ?? null,
            'product_ullage' => $bosData['product_ullage'] ?? null,
            'product_tc_volume' => $bosData['product_tc_volume'] ?? null,
            'product_density' => $bosData['product_density'] ?? null,
            'product_mass' => $bosData['product_mass'] ?? null,
            'tank_filling_percentage' => $bosData['tank_filling_percentage'] ?? null,
            'configuration_id' => $bosData['configuration_id'] ?? null,
            'station_id' => $stationId,
            'bos_tank_measurement_id' => $bosData['id'],
            'bos_uuid' => $bosData['uuid'],
            'created_at_bos' => $bosData['created_at'] ?? null,
            'updated_at_bos' => $bosData['updated_at'] ?? null,
        ];
    }

    /**
     * Sync tank deliveries from BOS
     */
    public function syncTankDeliveries(Request $request): JsonResponse
    {
        Log::debug("syncTankDeliveries: ", (array)$request->all());

        $station = $request->get('station');
        $ptsId = $request->input('pts_id');
        $tankDeliveries = $request->input('data', []);
        Log::debug("tank_deliveries: ", (array)$tankDeliveries);

        $created = 0;
        $updated = 0;
        $failed = 0;
        $errors = [];

        DB::beginTransaction();

        try {
            foreach ($tankDeliveries as $tankDeliveryData) {
                try {
                    // Create sync log entry
                    $syncLog = SyncLog::createLog(
                        $station->id,
                        'tank_deliveries',
                        'create',
                        $tankDeliveryData,
                        'pending'
                    );

                    // Prepare tank delivery data for HOS
                    $hosTankDeliveryData = $this->prepareTankDeliveryData($tankDeliveryData, $station->id);

                    // Use updateOrCreate to handle duplicates
                    $tankDelivery = TankDelivery::updateOrCreate(
                        [
                            'station_id' => $station->id,
                            'bos_tank_delivery_id' => $tankDeliveryData['id'],
                        ],
                        array_merge($hosTankDeliveryData, [
                            'synced_at' => now(),
                        ])
                    );

                    // Mark sync log as successful
                    $syncLog->markAsSuccessful([
                        'tank_delivery_id' => $tankDelivery->id,
                        'action' => $tankDelivery->wasRecentlyCreated ? 'created' : 'updated',
                    ]);

                    if ($tankDelivery->wasRecentlyCreated) {
                        $created++;
                    } else {
                        $updated++;
                    }
                } catch (\Exception $e) {
                    $failed++;
                    $errors[] = [
                        'tank_delivery_id' => $tankDeliveryData['id'] ?? 'unknown',
                        'error' => $e->getMessage(),
                    ];

                    // Mark sync log as failed
                    if (isset($syncLog)) {
                        $syncLog->markAsFailed($e->getMessage());
                    }

                    Log::error('Failed to sync tank delivery', [
                        'station_id' => $station->id,
                        'pts_id' => $ptsId,
                        'tank_delivery_data' => $tankDeliveryData,
                        'error' => $e->getMessage(),
                    ]);
                }
            }

            // Update station's last sync time
            $station->updateLastSync();

            DB::commit();

            $totalItems = $created + $updated + $failed;
            $allFailed = $totalItems > 0 && $failed === $totalItems;

            return response()->json([
                'success' => !$allFailed,
                'message' => $allFailed
                    ? "All {$failed} tank deliveries failed to sync"
                    : "Synced {$created} created, {$updated} updated, {$failed} failed tank deliveries",
                'data' => [
                    'created' => $created,
                    'updated' => $updated,
                    'failed' => $failed,
                    'errors' => $errors,
                ],
            ], $allFailed ? 422 : 200);
        } catch (\Exception $e) {
            DB::rollBack();

            Log::error('Sync tank deliveries failed', [
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
     * Prepare tank delivery data for HOS storage
     */
    private function prepareTankDeliveryData(array $bosData, int $stationId): array
    {
        return [
            'uuid' => Str::uuid7(),
            'request_id' => $bosData['request_id'] ?? null,
            'pts_id' => $bosData['pts_id'],
            'pts_delivery_id' => $bosData['pts_delivery_id'] ?? null,
            'tank' => $bosData['tank'],
            'fuel_grade_id' => $bosData['fuel_grade_id'] ?? null,
            'fuel_grade_name' => $bosData['fuel_grade_name'] ?? null,
            'configuration_id' => $bosData['configuration_id'] ?? null,
            'start_datetime' => $bosData['start_datetime'] ?? null,
            'start_product_height' => $bosData['start_product_height'] ?? null,
            'start_water_height' => $bosData['start_water_height'] ?? null,
            'start_temperature' => $bosData['start_temperature'] ?? null,
            'start_product_volume' => $bosData['start_product_volume'] ?? null,
            'start_product_tc_volume' => $bosData['start_product_tc_volume'] ?? null,
            'start_product_density' => $bosData['start_product_density'] ?? null,
            'start_product_mass' => $bosData['start_product_mass'] ?? null,
            'end_datetime' => $bosData['end_datetime'] ?? null,
            'end_product_height' => $bosData['end_product_height'] ?? null,
            'end_water_height' => $bosData['end_water_height'] ?? null,
            'end_temperature' => $bosData['end_temperature'] ?? null,
            'end_product_volume' => $bosData['end_product_volume'] ?? null,
            'end_product_tc_volume' => $bosData['end_product_tc_volume'] ?? null,
            'end_product_density' => $bosData['end_product_density'] ?? null,
            'end_product_mass' => $bosData['end_product_mass'] ?? null,
            'received_product_volume' => $bosData['received_product_volume'] ?? null,
            'absolute_product_height' => $bosData['absolute_product_height'] ?? null,
            'absolute_water_height' => $bosData['absolute_water_height'] ?? null,
            'absolute_temperature' => $bosData['absolute_temperature'] ?? null,
            'absolute_product_volume' => $bosData['absolute_product_volume'] ?? null,
            'absolute_product_tc_volume' => $bosData['absolute_product_tc_volume'] ?? null,
            'absolute_product_density' => $bosData['absolute_product_density'] ?? null,
            'absolute_product_mass' => $bosData['absolute_product_mass'] ?? null,
            'pumps_dispensed_volume' => $bosData['pumps_dispensed_volume'] ?? null,
            'probe_data' => $bosData['probe_data'] ?? null,
            'station_id' => $stationId,
            'bos_tank_delivery_id' => $bosData['id'],
            'bos_uuid' => $bosData['uuid'],
            'created_at_bos' => $bosData['created_at'] ?? null,
            'updated_at_bos' => $bosData['updated_at'] ?? null,
        ];
    }

    /**
     * Sync fuel grades from BOS
     */
    public function syncFuelGrades(Request $request): JsonResponse
    {
        Log::debug("syncFuelGrades: ", (array)$request->all());

        $station = $request->get('station');
        $ptsId = $request->input('pts_id');
        $fuelGrades = $request->input('data', []);
        Log::debug("fuel_grades: ", (array)$fuelGrades);

        $created = 0;
        $updated = 0;
        $failed = 0;
        $errors = [];

        DB::beginTransaction();

        try {
            foreach ($fuelGrades as $fuelGradeData) {
                try {
                    // Create sync log entry
                    $syncLog = SyncLog::createLog(
                        $station->id,
                        'fuel_grades',
                        'create',
                        $fuelGradeData,
                        'pending'
                    );

                    // Prepare fuel grade data for HOS
                    $hosFuelGradeData = $this->prepareFuelGradeData($fuelGradeData, $station->id);

                    // Use updateOrCreate to handle duplicates
                    $fuelGrade = FuelGrade::updateOrCreate(
                        [
                            'station_id' => $station->id,
                            'bos_fuel_grade_id' => $fuelGradeData['id'],
                        ],
                        array_merge($hosFuelGradeData, [
                            'synced_at' => now(),
                        ])
                    );

                    // Mark sync log as successful
                    $syncLog->markAsSuccessful([
                        'fuel_grade_id' => $fuelGrade->id,
                        'action' => $fuelGrade->wasRecentlyCreated ? 'created' : 'updated',
                    ]);

                    if ($fuelGrade->wasRecentlyCreated) {
                        $created++;
                    } else {
                        $updated++;
                    }
                } catch (\Exception $e) {
                    $failed++;
                    $errors[] = [
                        'fuel_grade_id' => $fuelGradeData['id'] ?? 'unknown',
                        'error' => $e->getMessage(),
                    ];

                    // Mark sync log as failed
                    if (isset($syncLog)) {
                        $syncLog->markAsFailed($e->getMessage());
                    }

                    Log::error('Failed to sync fuel grade', [
                        'station_id' => $station->id,
                        'pts_id' => $ptsId,
                        'fuel_grade_data' => $fuelGradeData,
                        'error' => $e->getMessage(),
                    ]);
                }
            }

            // Update station's last sync time
            $station->updateLastSync();

            DB::commit();

            $totalItems = $created + $updated + $failed;
            $allFailed = $totalItems > 0 && $failed === $totalItems;

            return response()->json([
                'success' => !$allFailed,
                'message' => $allFailed
                    ? "All {$failed} fuel grades failed to sync"
                    : "Synced {$created} created, {$updated} updated, {$failed} failed fuel grades",
                'data' => [
                    'created' => $created,
                    'updated' => $updated,
                    'failed' => $failed,
                    'errors' => $errors,
                ],
            ], $allFailed ? 422 : 200);
        } catch (\Exception $e) {
            DB::rollBack();

            Log::error('Sync fuel grades failed', [
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
     * Prepare fuel grade data for HOS storage
     */
    private function prepareFuelGradeData(array $bosData, int $stationId): array
    {
        return [
            'uuid' => Str::uuid7(),
            'pts_fuel_grade_id' => $bosData['pts_fuel_grade_id'] ?? null,
            'name' => $bosData['name'],
            'price' => $bosData['price'],
            'scheduled_price' => $bosData['scheduled_price'] ?? null,
            'scheduled_at' => $bosData['scheduled_at'] ?? null,
            'expansion_coefficient' => $bosData['expansion_coefficient'] ?? null,
            'blend_tank1_id' => $bosData['blend_tank1_id'] ?? null,
            'blend_tank1_percentage' => $bosData['blend_tank1_percentage'] ?? null,
            'blend_tank2_id' => $bosData['blend_tank2_id'] ?? null,
            'station_id' => $stationId,
            'bos_fuel_grade_id' => $bosData['id'],
            'bos_uuid' => $bosData['uuid'],
            'created_at_bos' => $bosData['created_at'] ?? null,
            'updated_at_bos' => $bosData['updated_at'] ?? null,
        ];
    }

    /**
     * Sync shifts from BOS
     */
    public function syncShifts(Request $request): JsonResponse
    {
        Log::debug("syncShifts: ", (array)$request->all());

        $station = $request->get('station');
        $ptsId = $request->input('pts_id');
        $shifts = $request->input('data', []);
        Log::debug("shifts: ", (array)$shifts);

        $created = 0;
        $updated = 0;
        $failed = 0;
        $errors = [];

        DB::beginTransaction();

        try {
            foreach ($shifts as $shiftData) {
                try {
                    // Create sync log entry
                    $syncLog = SyncLog::createLog(
                        $station->id,
                        'shifts',
                        'create',
                        $shiftData,
                        'pending'
                    );

                    // Prepare shift data for HOS
                    $hosShiftData = $this->prepareShiftData($shiftData, $station->id);

                    // Use updateOrCreate to handle duplicates
                    $shift = Shift::updateOrCreate(
                        [
                            'station_id' => $station->id,
                            'bos_shift_id' => $shiftData['id'],
                        ],
                        array_merge($hosShiftData, [
                            'synced_at' => now(),
                        ])
                    );

                    // Mark sync log as successful
                    $syncLog->markAsSuccessful([
                        'shift_id' => $shift->id,
                        'action' => $shift->wasRecentlyCreated ? 'created' : 'updated',
                    ]);

                    if ($shift->wasRecentlyCreated) {
                        $created++;
                    } else {
                        $updated++;
                    }
                } catch (\Exception $e) {
                    $failed++;
                    $errors[] = [
                        'shift_id' => $shiftData['id'] ?? 'unknown',
                        'error' => $e->getMessage(),
                    ];

                    // Mark sync log as failed
                    if (isset($syncLog)) {
                        $syncLog->markAsFailed($e->getMessage());
                    }

                    Log::error('Failed to sync shift', [
                        'station_id' => $station->id,
                        'pts_id' => $ptsId,
                        'shift_data' => $shiftData,
                        'error' => $e->getMessage(),
                    ]);
                }
            }

            // Update station's last sync time
            $station->updateLastSync();

            DB::commit();

            $totalItems = $created + $updated + $failed;
            $allFailed = $totalItems > 0 && $failed === $totalItems;

            return response()->json([
                'success' => !$allFailed,
                'message' => $allFailed
                    ? "All {$failed} shifts failed to sync"
                    : "Synced {$created} created, {$updated} updated, {$failed} failed shifts",
                'data' => [
                    'created' => $created,
                    'updated' => $updated,
                    'failed' => $failed,
                    'errors' => $errors,
                ],
            ], $allFailed ? 422 : 200);
        } catch (\Exception $e) {
            DB::rollBack();

            Log::error('Sync shifts failed', [
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
     * Prepare shift data for HOS storage
     */
    private function prepareShiftData(array $bosData, int $stationId): array
    {
        return [
            'uuid' => Str::uuid7(),
            'start_time' => $bosData['start_time'],
            'end_time' => $bosData['end_time'] ?? null,
            'user_id' => $bosData['user_id'],
            'notes' => $bosData['notes'] ?? null,
            'close_type' => $bosData['close_type'],
            'status' => $bosData['status'],
            'auto_close_time' => $bosData['auto_close_time'] ?? null,
            'start_time_utc' => $bosData['start_time_utc'] ?? null,
            'end_time_utc' => $bosData['end_time_utc'] ?? null,
            'auto_close_time_utc' => $bosData['auto_close_time_utc'] ?? null,
            'station_id' => $stationId,
            'bos_shift_id' => $bosData['id'],
            'bos_uuid' => $bosData['uuid'],
            'created_at_bos' => $bosData['created_at'] ?? null,
            'updated_at_bos' => $bosData['updated_at'] ?? null,
        ];
    }

    /**
     * Sync product wise summaries from BOS
     */
    public function syncProductWiseSummaries(Request $request): JsonResponse
    {
        Log::debug("syncProductWiseSummaries: ", (array)$request->all());

        $station = $request->get('station');
        $ptsId = $request->input('pts_id');
        $productWiseSummaries = $request->input('data', []);
        Log::debug("product_wise_summaries: ", (array)$productWiseSummaries);

        $created = 0;
        $updated = 0;
        $failed = 0;
        $errors = [];

        DB::beginTransaction();

        try {
            foreach ($productWiseSummaries as $summaryData) {
                try {
                    // Create sync log entry
                    $syncLog = SyncLog::createLog(
                        $station->id,
                        'product_wise_summaries',
                        'create',
                        $summaryData,
                        'pending'
                    );

                    // Prepare product wise summary data for HOS
                    $hosSummaryData = $this->prepareProductWiseSummaryData($summaryData, $station->id);

                    // Use updateOrCreate to handle duplicates
                    $summary = ProductWiseSummary::updateOrCreate(
                        [
                            'station_id' => $station->id,
                            'bos_product_wise_summary_id' => $summaryData['id'],
                        ],
                        array_merge($hosSummaryData, [
                            'synced_at' => now(),
                        ])
                    );

                    // Mark sync log as successful
                    $syncLog->markAsSuccessful([
                        'product_wise_summary_id' => $summary->id,
                        'action' => $summary->wasRecentlyCreated ? 'created' : 'updated',
                    ]);

                    if ($summary->wasRecentlyCreated) {
                        $created++;
                    } else {
                        $updated++;
                    }
                } catch (\Exception $e) {
                    $failed++;
                    $errors[] = [
                        'product_wise_summary_id' => $summaryData['id'] ?? 'unknown',
                        'error' => $e->getMessage(),
                    ];

                    // Mark sync log as failed
                    if (isset($syncLog)) {
                        $syncLog->markAsFailed($e->getMessage());
                    }

                    Log::error('Failed to sync product wise summary', [
                        'station_id' => $station->id,
                        'pts_id' => $ptsId,
                        'summary_data' => $summaryData,
                        'error' => $e->getMessage(),
                    ]);
                }
            }

            // Update station's last sync time
            $station->updateLastSync();

            DB::commit();

            $totalItems = $created + $updated + $failed;
            $allFailed = $totalItems > 0 && $failed === $totalItems;

            return response()->json([
                'success' => !$allFailed,
                'message' => $allFailed
                    ? "All {$failed} product wise summaries failed to sync"
                    : "Synced {$created} created, {$updated} updated, {$failed} failed product wise summaries",
                'data' => [
                    'created' => $created,
                    'updated' => $updated,
                    'failed' => $failed,
                    'errors' => $errors,
                ],
            ], $allFailed ? 422 : 200);
        } catch (\Exception $e) {
            DB::rollBack();

            Log::error('Sync product wise summaries failed', [
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
     * Prepare product wise summary data for HOS storage
     */
    private function prepareProductWiseSummaryData(array $bosData, int $stationId): array
    {
        return [
            'uuid' => Str::uuid7(),
            'bos_shift_id' => $bosData['shift_id'],
            'bos_fuel_grade_id' => $bosData['fuel_grade_id'],
            'volume' => $bosData['volume'],
            'amount' => $bosData['amount'],
            'station_id' => $stationId,
            'bos_product_wise_summary_id' => $bosData['id'],
            'bos_uuid' => $bosData['uuid'],
            'created_at_bos' => $bosData['created_at'] ?? null,
            'updated_at_bos' => $bosData['updated_at'] ?? null,
        ];
    }

    /**
     * Sync payment mode wise summaries from BOS
     */
    public function syncPaymentModeWiseSummaries(Request $request): JsonResponse
    {
        Log::debug("syncPaymentModeWiseSummaries: ", (array)$request->all());

        $station = $request->get('station');
        $ptsId = $request->input('pts_id');
        $paymentModeWiseSummaries = $request->input('data', []);
        Log::debug("payment_mode_wise_summaries: ", (array)$paymentModeWiseSummaries);

        $created = 0;
        $updated = 0;
        $failed = 0;
        $errors = [];

        DB::beginTransaction();

        try {
            foreach ($paymentModeWiseSummaries as $summaryData) {
                try {
                    // Create sync log entry
                    $syncLog = SyncLog::createLog(
                        $station->id,
                        'payment_mode_wise_summaries',
                        'create',
                        $summaryData,
                        'pending'
                    );

                    // Prepare payment mode wise summary data for HOS
                    $hosSummaryData = $this->preparePaymentModeWiseSummaryData($summaryData, $station->id);

                    // Use updateOrCreate to handle duplicates
                    $summary = PaymentModeWiseSummary::updateOrCreate(
                        [
                            'station_id' => $station->id,
                            'bos_payment_mode_wise_summary_id' => $summaryData['id'],
                        ],
                        array_merge($hosSummaryData, [
                            'synced_at' => now(),
                        ])
                    );

                    // Mark sync log as successful
                    $syncLog->markAsSuccessful([
                        'payment_mode_wise_summary_id' => $summary->id,
                        'action' => $summary->wasRecentlyCreated ? 'created' : 'updated',
                    ]);

                    if ($summary->wasRecentlyCreated) {
                        $created++;
                    } else {
                        $updated++;
                    }
                } catch (\Exception $e) {
                    $failed++;
                    $errors[] = [
                        'payment_mode_wise_summary_id' => $summaryData['id'] ?? 'unknown',
                        'error' => $e->getMessage(),
                    ];

                    // Mark sync log as failed
                    if (isset($syncLog)) {
                        $syncLog->markAsFailed($e->getMessage());
                    }

                    Log::error('Failed to sync payment mode wise summary', [
                        'station_id' => $station->id,
                        'pts_id' => $ptsId,
                        'summary_data' => $summaryData,
                        'error' => $e->getMessage(),
                    ]);
                }
            }

            // Update station's last sync time
            $station->updateLastSync();

            DB::commit();

            $totalItems = $created + $updated + $failed;
            $allFailed = $totalItems > 0 && $failed === $totalItems;

            return response()->json([
                'success' => !$allFailed,
                'message' => $allFailed
                    ? "All {$failed} payment mode wise summaries failed to sync"
                    : "Synced {$created} created, {$updated} updated, {$failed} failed payment mode wise summaries",
                'data' => [
                    'created' => $created,
                    'updated' => $updated,
                    'failed' => $failed,
                    'errors' => $errors,
                ],
            ], $allFailed ? 422 : 200);
        } catch (\Exception $e) {
            DB::rollBack();

            Log::error('Sync payment mode wise summaries failed', [
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
     * Prepare payment mode wise summary data for HOS storage
     */
    private function preparePaymentModeWiseSummaryData(array $bosData, int $stationId): array
    {
        return [
            'uuid' => Str::uuid7(),
            'bos_shift_id' => $bosData['shift_id'],
            'mop' => $bosData['mop'],
            'volume' => $bosData['volume'],
            'amount' => $bosData['amount'],
            'station_id' => $stationId,
            'bos_payment_mode_wise_summary_id' => $bosData['id'],
            'bos_uuid' => $bosData['uuid'],
            'created_at_bos' => $bosData['created_at'] ?? null,
            'updated_at_bos' => $bosData['updated_at'] ?? null,
        ];
    }

    /**
     * Sync shift pump totals from BOS
     */
    public function syncShiftPumpTotals(Request $request): JsonResponse
    {
        Log::debug("syncShiftPumpTotals: ", (array)$request->all());

        $station = $request->get('station');
        $ptsId = $request->input('pts_id');
        $shiftPumpTotals = $request->input('data', []);
        Log::debug("shift_pump_totals: ", (array)$shiftPumpTotals);

        $created = 0;
        $updated = 0;
        $failed = 0;
        $errors = [];

        DB::beginTransaction();

        try {
            foreach ($shiftPumpTotals as $totalData) {
                try {
                    // Create sync log entry
                    $syncLog = SyncLog::createLog(
                        $station->id,
                        'shift_pump_totals',
                        'create',
                        $totalData,
                        'pending'
                    );

                    // Prepare shift pump total data for HOS
                    $hosTotalData = $this->prepareShiftPumpTotalData($totalData, $station->id);

                    // Use updateOrCreate to handle duplicates
                    $total = ShiftPumpTotal::updateOrCreate(
                        [
                            'station_id' => $station->id,
                            'bos_shift_pump_total_id' => $totalData['id'],
                        ],
                        array_merge($hosTotalData, [
                            'synced_at' => now(),
                        ])
                    );

                    // Mark sync log as successful
                    $syncLog->markAsSuccessful([
                        'shift_pump_total_id' => $total->id,
                        'action' => $total->wasRecentlyCreated ? 'created' : 'updated',
                    ]);

                    if ($total->wasRecentlyCreated) {
                        $created++;
                    } else {
                        $updated++;
                    }
                } catch (\Exception $e) {
                    $failed++;
                    $errors[] = [
                        'shift_pump_total_id' => $totalData['id'] ?? 'unknown',
                        'error' => $e->getMessage(),
                    ];

                    // Mark sync log as failed
                    if (isset($syncLog)) {
                        $syncLog->markAsFailed($e->getMessage());
                    }

                    Log::error('Failed to sync shift pump total', [
                        'station_id' => $station->id,
                        'pts_id' => $ptsId,
                        'total_data' => $totalData,
                        'error' => $e->getMessage(),
                    ]);
                }
            }

            // Update station's last sync time
            $station->updateLastSync();

            DB::commit();

            $totalItems = $created + $updated + $failed;
            $allFailed = $totalItems > 0 && $failed === $totalItems;

            return response()->json([
                'success' => !$allFailed,
                'message' => $allFailed
                    ? "All {$failed} shift pump totals failed to sync"
                    : "Synced {$created} created, {$updated} updated, {$failed} failed shift pump totals",
                'data' => [
                    'created' => $created,
                    'updated' => $updated,
                    'failed' => $failed,
                    'errors' => $errors,
                ],
            ], $allFailed ? 422 : 200);
        } catch (\Exception $e) {
            DB::rollBack();

            Log::error('Sync shift pump totals failed', [
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
     * Prepare shift pump total data for HOS storage
     */
    private function prepareShiftPumpTotalData(array $bosData, int $stationId): array
    {
        return [
            'uuid' => Str::uuid7(),
            'bos_shift_id' => $bosData['shift_id'],
            'pump_id' => $bosData['pump_id'],
            'nozzle_id' => $bosData['nozzle_id'],
            'fuel_grade_id' => $bosData['fuel_grade_id'],
            'volume' => $bosData['volume'],
            'amount' => $bosData['amount'],
            'transaction_count' => $bosData['transaction_count'],
            'user' => $bosData['user'] ?? null,
            'type' => $bosData['type'] ?? null,
            'recorded_at' => $bosData['recorded_at'] ?? null,
            'station_id' => $stationId,
            'bos_shift_pump_total_id' => $bosData['id'],
            'bos_uuid' => $bosData['uuid'],
            'created_at_bos' => $bosData['created_at'] ?? null,
            'updated_at_bos' => $bosData['updated_at'] ?? null,
        ];
    }

    /**
     * Sync tank inventories from BOS
     */
    public function syncTankInventories(Request $request): JsonResponse
    {
        Log::debug("syncTankInventories: ", (array)$request->all());

        $station = $request->get('station');
        $ptsId = $request->input('pts_id');
        $tankInventories = $request->input('data', []);
        Log::debug("tank_inventories: ", (array)$tankInventories);

        $created = 0;
        $updated = 0;
        $failed = 0;
        $errors = [];

        DB::beginTransaction();

        try {
            foreach ($tankInventories as $inventoryData) {
                try {
                    // Create sync log entry
                    $syncLog = SyncLog::createLog(
                        $station->id,
                        'tank_inventories',
                        'create',
                        $inventoryData,
                        'pending'
                    );

                    // Prepare tank inventory data for HOS
                    $hosInventoryData = $this->prepareTankInventoryData($inventoryData, $station->id);

                    // Use updateOrCreate to handle duplicates
                    $inventory = TankInventory::updateOrCreate(
                        [
                            'station_id' => $station->id,
                            'bos_tank_inventory_id' => $inventoryData['id'],
                        ],
                        array_merge($hosInventoryData, [
                            'synced_at' => now(),
                        ])
                    );

                    // Mark sync log as successful
                    $syncLog->markAsSuccessful([
                        'tank_inventory_id' => $inventory->id,
                        'action' => $inventory->wasRecentlyCreated ? 'created' : 'updated',
                    ]);

                    if ($inventory->wasRecentlyCreated) {
                        $created++;
                    } else {
                        $updated++;
                    }
                } catch (\Exception $e) {
                    $failed++;
                    $errors[] = [
                        'tank_inventory_id' => $inventoryData['id'] ?? 'unknown',
                        'error' => $e->getMessage(),
                    ];

                    // Mark sync log as failed
                    if (isset($syncLog)) {
                        $syncLog->markAsFailed($e->getMessage());
                    }

                    Log::error('Failed to sync tank inventory', [
                        'station_id' => $station->id,
                        'pts_id' => $ptsId,
                        'inventory_data' => $inventoryData,
                        'error' => $e->getMessage(),
                    ]);
                }
            }

            // Update station's last sync time
            $station->updateLastSync();

            DB::commit();

            $totalItems = $created + $updated + $failed;
            $allFailed = $totalItems > 0 && $failed === $totalItems;

            return response()->json([
                'success' => !$allFailed,
                'message' => $allFailed
                    ? "All {$failed} tank inventories failed to sync"
                    : "Synced {$created} created, {$updated} updated, {$failed} failed tank inventories",
                'data' => [
                    'created' => $created,
                    'updated' => $updated,
                    'failed' => $failed,
                    'errors' => $errors,
                ],
            ], $allFailed ? 422 : 200);
        } catch (\Exception $e) {
            DB::rollBack();

            Log::error('Sync tank inventories failed', [
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
     * Prepare tank inventory data for HOS storage
     */
    private function prepareTankInventoryData(array $bosData, int $stationId): array
    {
        return [
            'uuid' => Str::uuid7(),
            'request_id' => $bosData['request_id'] ?? null,
            'pts_id' => $bosData['pts_id'],
            'date_time' => $bosData['date_time'],
            'tank' => $bosData['tank'],
            'fuel_grade_id' => $bosData['fuel_grade_id'] ?? null,
            'fuel_grade_name' => $bosData['fuel_grade_name'] ?? null,
            'status' => $bosData['status'] ?? null,
            'alarms' => $bosData['alarms'] ?? [],
            'product_height' => $bosData['product_height'] ?? null,
            'water_height' => $bosData['water_height'] ?? null,
            'temperature' => $bosData['temperature'] ?? null,
            'product_volume' => $bosData['product_volume'] ?? null,
            'water_volume' => $bosData['water_volume'] ?? null,
            'product_ullage' => $bosData['product_ullage'] ?? null,
            'product_tc_volume' => $bosData['product_tc_volume'] ?? null,
            'product_density' => $bosData['product_density'] ?? null,
            'product_mass' => $bosData['product_mass'] ?? null,
            'tank_filling_percentage' => $bosData['tank_filling_percentage'] ?? null,
            'configuration_id' => $bosData['configuration_id'] ?? null,
            'station_id' => $stationId,
            'bos_tank_inventory_id' => $bosData['id'],
            'bos_uuid' => $bosData['uuid'],
            'created_at_bos' => $bosData['created_at'] ?? null,
            'updated_at_bos' => $bosData['updated_at'] ?? null,
        ];
    }

    /**
     * Sync PTS users from BOS
     */
    public function syncPtsUsers(Request $request): JsonResponse
    {
        Log::debug("syncPtsUsers: ", (array)$request->all());

        $station = $request->get('station');
        $ptsId = $request->input('pts_id');
        $ptsUsers = $request->input('data', []);
        Log::debug("pts_users: ", (array)$ptsUsers);

        $created = 0;
        $updated = 0;
        $failed = 0;
        $errors = [];

        DB::beginTransaction();

        try {
            foreach ($ptsUsers as $ptsUserData) {
                try {
                    // Create sync log entry
                    $syncLog = SyncLog::createLog(
                        $station->id,
                        'pts_users',
                        'create',
                        $ptsUserData,
                        'pending'
                    );

                    // Prepare PTS user data for HOS
                    $hosPtsUserData = $this->preparePtsUserData($ptsUserData, $station->id);

                    // Use updateOrCreate to handle duplicates
                    $ptsUser = PtsUser::updateOrCreate(
                        [
                            'station_id' => $station->id,
                            'bos_pts_user_id' => $ptsUserData['id'],
                        ],
                        array_merge($hosPtsUserData, [
                            'synced_at' => now(),
                        ])
                    );

                    // Mark sync log as successful
                    $syncLog->markAsSuccessful([
                        'pts_user_id' => $ptsUser->id,
                        'action' => $ptsUser->wasRecentlyCreated ? 'created' : 'updated',
                    ]);

                    if ($ptsUser->wasRecentlyCreated) {
                        $created++;
                    } else {
                        $updated++;
                    }
                } catch (\Exception $e) {
                    $failed++;
                    $errors[] = [
                        'pts_user_id' => $ptsUserData['id'] ?? 'unknown',
                        'error' => $e->getMessage(),
                    ];

                    // Mark sync log as failed
                    if (isset($syncLog)) {
                        $syncLog->markAsFailed($e->getMessage());
                    }

                    Log::error('Failed to sync PTS user', [
                        'station_id' => $station->id,
                        'pts_id' => $ptsId,
                        'pts_user_data' => $ptsUserData,
                        'error' => $e->getMessage(),
                    ]);
                }
            }

            // Update station's last sync time
            $station->updateLastSync();

            DB::commit();

            $totalItems = $created + $updated + $failed;
            $allFailed = $totalItems > 0 && $failed === $totalItems;

            return response()->json([
                'success' => !$allFailed,
                'message' => $allFailed
                    ? "All {$failed} PTS users failed to sync"
                    : "Synced {$created} created, {$updated} updated, {$failed} failed PTS users",
                'data' => [
                    'created' => $created,
                    'updated' => $updated,
                    'failed' => $failed,
                    'errors' => $errors,
                ],
            ], $allFailed ? 422 : 200);
        } catch (\Exception $e) {
            DB::rollBack();

            Log::error('Sync PTS users failed', [
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
     * Prepare PTS user data for HOS storage
     */
    private function preparePtsUserData(array $bosData, int $stationId): array
    {
        return [
            'uuid' => Str::uuid7(),
            'pts_user_id' => $bosData['pts_user_id'],
            'login' => $bosData['login'],
            'configuration_permission' => $bosData['configuration_permission'] ?? false,
            'control_permission' => $bosData['control_permission'] ?? false,
            'monitoring_permission' => $bosData['monitoring_permission'] ?? false,
            'reports_permission' => $bosData['reports_permission'] ?? false,
            'is_active' => $bosData['is_active'] ?? true,
            'station_id' => $stationId,
            'bos_pts_user_id' => $bosData['id'],
            'created_at_bos' => $bosData['created_at'] ?? null,
            'updated_at_bos' => $bosData['updated_at'] ?? null,
        ];
    }
}
