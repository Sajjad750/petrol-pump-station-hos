<?php

use App\Http\Controllers\ProfileController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('dashboard');
})->middleware(['auth'])->name('home');

Route::get('/dashboard', function () {
    return view('dashboard');
})->middleware(['auth', 'verified'])->name('dashboard');

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');

    Route::get('pump_transactions', \App\Http\Controllers\PumpTransactionListController::class)->name('pump_transactions');
    Route::get('pumps', \App\Http\Controllers\PumpListController::class)->name('pumps');
    Route::get('tank_measurements', \App\Http\Controllers\TankMeasurementListController::class)->name('tank_measurements');
});

require __DIR__.'/auth.php';
