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

    // Pump Transactions
    Route::get('pump_transactions', \App\Http\Controllers\PumpTransactionListController::class)->name('pump_transactions');
    Route::get('pump_transactions/export-excel', [\App\Http\Controllers\PumpTransactionListController::class, 'exportExcel'])->name('pump_transactions.export.excel');
    Route::get('pump_transactions/export-pdf', [\App\Http\Controllers\PumpTransactionListController::class, 'exportPdf'])->name('pump_transactions.export.pdf');

    // Pumps
    Route::get('pumps', \App\Http\Controllers\PumpListController::class)->name('pumps');

    // Tank Measurements
    Route::get('tank_measurements', \App\Http\Controllers\TankMeasurementListController::class)->name('tank_measurements');
    Route::get('tank_measurements/export-excel', [\App\Http\Controllers\TankMeasurementListController::class, 'exportExcel'])->name('tank_measurements.export.excel');
    Route::get('tank_measurements/export-pdf', [\App\Http\Controllers\TankMeasurementListController::class, 'exportPdf'])->name('tank_measurements.export.pdf');

    // Tank Deliveries
    Route::get('tank_deliveries', \App\Http\Controllers\TankDeliveryListController::class)->name('tank_deliveries');
    Route::get('tank_deliveries/export-excel', [\App\Http\Controllers\TankDeliveryListController::class, 'exportExcel'])->name('tank_deliveries.export.excel');
    Route::get('tank_deliveries/export-pdf', [\App\Http\Controllers\TankDeliveryListController::class, 'exportPdf'])->name('tank_deliveries.export.pdf');

    // Tank Inventories
    Route::get('tank_inventories', \App\Http\Controllers\TankInventoryListController::class)->name('tank_inventories');
    Route::get('tank_inventories/export-excel', [\App\Http\Controllers\TankInventoryListController::class, 'exportExcel'])->name('tank_inventories.export.excel');
    Route::get('tank_inventories/export-pdf', [\App\Http\Controllers\TankInventoryListController::class, 'exportPdf'])->name('tank_inventories.export.pdf');

    // Product Wise Summaries
    Route::get('product_wise_summaries', \App\Http\Controllers\ProductWiseSummaryListController::class)->name('product_wise_summaries');
    Route::get('product_wise_summaries/export-excel', [\App\Http\Controllers\ProductWiseSummaryListController::class, 'exportExcel'])->name('product_wise_summaries.export.excel');
    Route::get('product_wise_summaries/export-pdf', [\App\Http\Controllers\ProductWiseSummaryListController::class, 'exportPdf'])->name('product_wise_summaries.export.pdf');

    // Payment Mode Wise Summaries
    Route::get('payment_mode_wise_summaries', \App\Http\Controllers\PaymentModeWiseSummaryListController::class)->name('payment_mode_wise_summaries');
    Route::get('payment_mode_wise_summaries/export-excel', [\App\Http\Controllers\PaymentModeWiseSummaryListController::class, 'exportExcel'])->name('payment_mode_wise_summaries.export.excel');
    Route::get('payment_mode_wise_summaries/export-pdf', [\App\Http\Controllers\PaymentModeWiseSummaryListController::class, 'exportPdf'])->name('payment_mode_wise_summaries.export.pdf');

    // Fuel Grades
    Route::get('fuel_grades', \App\Http\Controllers\FuelGradeListController::class)->name('fuel_grades');
    Route::get('fuel_grades/export-excel', [\App\Http\Controllers\FuelGradeListController::class, 'exportExcel'])->name('fuel_grades.export.excel');
    Route::get('fuel_grades/export-pdf', [\App\Http\Controllers\FuelGradeListController::class, 'exportPdf'])->name('fuel_grades.export.pdf');

    // Shifts
    Route::get('shifts', \App\Http\Controllers\ShiftListController::class)->name('shifts.index');
    Route::get('shifts/export-excel', [\App\Http\Controllers\ShiftListController::class, 'exportExcel'])->name('shifts.export.excel');
    Route::get('shifts/export-pdf', [\App\Http\Controllers\ShiftListController::class, 'exportPdf'])->name('shifts.export.pdf');
    Route::get('shifts/{id}/summary', [\App\Http\Controllers\ShiftSummaryController::class, 'show'])->name('shifts.summary');

    // Shift Templates
    Route::get('shift_templates', \App\Http\Controllers\ShiftTemplateListController::class)->name('shift_templates');
    Route::get('shift_templates/export-excel', [\App\Http\Controllers\ShiftTemplateListController::class, 'exportExcel'])->name('shift_templates.export.excel');
    Route::get('shift_templates/export-pdf', [\App\Http\Controllers\ShiftTemplateListController::class, 'exportPdf'])->name('shift_templates.export.pdf');

    // PTS Users
    Route::get('pts_users', \App\Http\Controllers\PtsUserListController::class)->name('pts_users');
    Route::get('pts_users/export-excel', [\App\Http\Controllers\PtsUserListController::class, 'exportExcel'])->name('pts_users.export.excel');
    Route::get('pts_users/export-pdf', [\App\Http\Controllers\PtsUserListController::class, 'exportPdf'])->name('pts_users.export.pdf');
});

require __DIR__.'/auth.php';
