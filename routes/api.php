<?php

use App\Http\Controllers\Api\SyncController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');

// Role Management API Routes
Route::prefix('api')->middleware(['auth:sanctum'])->group(function () {
    Route::get('/roles', [\App\Http\Controllers\RoleController::class, 'index']);
    Route::post('/roles', [\App\Http\Controllers\RoleController::class, 'store']);
    Route::get('/roles/{role}', [\App\Http\Controllers\RoleController::class, 'show']);
    Route::put('/roles/{role}', [\App\Http\Controllers\RoleController::class, 'update']);
    Route::delete('/roles/{role}', [\App\Http\Controllers\RoleController::class, 'destroy']);
    Route::post('/roles/{role}/permissions', [\App\Http\Controllers\RoleController::class, 'updatePermissions']);
});

// BOS Sync API Routes
Route::prefix('sync')->middleware(['bos.api.key', 'throttle:120,1'])->group(function () {
    Route::post('/pump-transactions', [SyncController::class, 'syncPumpTransactions']);
    Route::post('/pumps', [SyncController::class, 'syncPumps']);
    Route::get('/status', [SyncController::class, 'getSyncStatus']);
    Route::post('/tank-measurements', [SyncController::class, 'syncTankMeasurements']);
    Route::post('/tank-deliveries', [SyncController::class, 'syncTankDeliveries']);
    Route::post('/fuel-grades', [SyncController::class, 'syncFuelGrades']);
    Route::post('/fuel-grade-price-history', [SyncController::class, 'syncFuelGradePriceHistory']);
    Route::post('/shifts', [SyncController::class, 'syncShifts']);
    Route::post('/product-wise-summaries', [SyncController::class, 'syncProductWiseSummaries']);
    Route::post('/payment-mode-wise-summaries', [SyncController::class, 'syncPaymentModeWiseSummaries']);
    Route::post('/shift-pump-totals', [SyncController::class, 'syncShiftPumpTotals']);
    Route::post('/tank-inventories', [SyncController::class, 'syncTankInventories']);
    Route::post('/pts-users', [SyncController::class, 'syncPtsUsers']);
    Route::post('/alerts', [SyncController::class, 'syncAlerts']);
    Route::post('/pts2-devices', [SyncController::class, 'syncPts2Devices']);
    Route::get('/pending-commands', [SyncController::class, 'getPendingCommands']);
    Route::post('/acknowledge-command', [SyncController::class, 'acknowledgeCommand']);
    // Future endpoints: alert-records
});
