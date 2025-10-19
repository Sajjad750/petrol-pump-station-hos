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
    // Future endpoints: alert-records
});
