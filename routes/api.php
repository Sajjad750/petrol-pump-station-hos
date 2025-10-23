<?php

use App\Http\Controllers\Api\SyncController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');

// BOS Sync API Routes
Route::prefix('sync')->middleware(['bos.api.key', 'throttle:120,1'])->group(function () {
    Route::post('/pump-transactions', [SyncController::class, 'syncPumpTransactions']);
    Route::post('/pumps', [SyncController::class, 'syncPumps']);
    Route::get('/status', [SyncController::class, 'getSyncStatus']);
    Route::post('/tank-measurements', [SyncController::class, 'syncTankMeasurements']);
    Route::post('/tank-deliveries', [SyncController::class, 'syncTankDeliveries']);
    Route::post('/fuel-grades', [SyncController::class, 'syncFuelGrades']);
    Route::post('/shifts', [SyncController::class, 'syncShifts']);
    Route::post('/product-wise-summaries', [SyncController::class, 'syncProductWiseSummaries']);
    Route::post('/payment-mode-wise-summaries', [SyncController::class, 'syncPaymentModeWiseSummaries']);
    Route::post('/shift-pump-totals', [SyncController::class, 'syncShiftPumpTotals']);
    Route::post('/tank-inventories', [SyncController::class, 'syncTankInventories']);
    // Future endpoints: alert-records
});
