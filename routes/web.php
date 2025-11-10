<?php

use App\Http\Controllers\ProfileController;
use Illuminate\Support\Facades\Route;

Route::get('/', [\App\Http\Controllers\DashboardController::class, 'index'])->middleware(['auth'])->name('home');

Route::get('/dashboard', [\App\Http\Controllers\DashboardController::class, 'index'])->middleware(['auth', 'verified'])->name('dashboard');

Route::get('/dashboard/station/{id}', [\App\Http\Controllers\DashboardController::class, 'getStationDetails'])->middleware(['auth'])->name('dashboard.station.details');

Route::get('/operations-monitor', [\App\Http\Controllers\OperationsMonitorController::class, 'index'])->name('operations-monitor');

Route::get('/operations-monitor/stations/{station}', [\App\Http\Controllers\OperationsMonitorController::class, 'show'])->name('operations-monitor.station');

Route::get('/alerts', [\App\Http\Controllers\AlertController::class, 'index'])->name('alerts.index');

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
    Route::put('fuel-grades/{fuelGrade}/price', [\App\Http\Controllers\FuelGradeController::class, 'updatePrice'])->name('fuel-grades.update-price');
    Route::put('fuel-grades/{fuelGrade}/schedule-price', [\App\Http\Controllers\FuelGradeController::class, 'schedulePrice'])->name('fuel-grades.schedule-price');

    // Price Updates (new design wrapper around fuel grade scheduling)
    Route::get('price-updates', [\App\Http\Controllers\PriceUpdateController::class, 'index'])
        ->middleware('permission:view-fuel-grades')
        ->name('price-updates');
    Route::get('price-updates/products', [\App\Http\Controllers\PriceUpdateController::class, 'products'])
        ->middleware('permission:view-fuel-grades')
        ->name('price-updates.products');

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

    // User Management
    Route::resource('users', \App\Http\Controllers\UserController::class)->middleware('permission:view-users');

    // Role Management
    Route::resource('roles', \App\Http\Controllers\RoleController::class)->middleware('permission:view-roles');

    // HOS Commands
    Route::prefix('hos-commands')->group(function () {
        Route::get('/', [\App\Http\Controllers\HosCommandController::class, 'index'])->name('hos-commands.index');
        Route::get('/{hosCommand}', [\App\Http\Controllers\HosCommandController::class, 'show'])->name('hos-commands.show');
    });

    // HOS Reports
    Route::get('hos-reports', \App\Http\Controllers\HosReportsController::class)->name('hos-reports');
    Route::get('hos-reports/partial/{tab}', [\App\Http\Controllers\HosReportsController::class, 'loadPartial'])->name('hos-reports.partial');
    Route::get('hos-reports/stations', [\App\Http\Controllers\HosReportsController::class, 'getStations'])->name('hos-reports.stations');
    Route::get('hos-reports/fuel-grades', [\App\Http\Controllers\HosReportsController::class, 'getFuelGrades'])->name('hos-reports.fuel-grades');
    Route::get('hos-reports/transactions/data', [\App\Http\Controllers\HosReportsController::class, 'getTransactionsData'])->name('hos-reports.transactions.data');
    Route::get('hos-reports/sales', [\App\Http\Controllers\HosReportsController::class, 'sales'])->name('hos-reports.sales');
    Route::get('hos-reports/sales/export-excel', [\App\Http\Controllers\HosReportsController::class, 'exportSalesExcel'])->name('hos-reports.sales.export.excel');
    Route::get('hos-reports/sales/export-pdf', [\App\Http\Controllers\HosReportsController::class, 'exportSalesPdf'])->name('hos-reports.sales.export.pdf');
    Route::get('hos-reports/tank-inventory', [\App\Http\Controllers\HosReportsController::class, 'tankInventory'])->name('hos-reports.tank-inventory');
    Route::get('hos-reports/tanks', [\App\Http\Controllers\HosReportsController::class, 'getTanks'])->name('hos-reports.tanks');
    Route::get('hos-reports/tank-inventory/export-excel', [\App\Http\Controllers\HosReportsController::class, 'exportTankInventoryExcel'])->name('hos-reports.tank-inventory.export.excel');
    Route::get('hos-reports/tank-inventory/export-pdf', [\App\Http\Controllers\HosReportsController::class, 'exportTankInventoryPdf'])->name('hos-reports.tank-inventory.export.pdf');
    Route::get('hos-reports/tank-deliveries', [\App\Http\Controllers\HosReportsController::class, 'tankDeliveries'])->name('hos-reports.tank-deliveries');
    Route::get('hos-reports/tank-deliveries/export-excel', [\App\Http\Controllers\HosReportsController::class, 'exportTankDeliveriesExcel'])->name('hos-reports.tank-deliveries.export.excel');
    Route::get('hos-reports/tank-deliveries/export-pdf', [\App\Http\Controllers\HosReportsController::class, 'exportTankDeliveriesPdf'])->name('hos-reports.tank-deliveries.export.pdf');
    Route::get('hos-reports/shift-summary', [\App\Http\Controllers\HosReportsController::class, 'getShiftSummary'])->name('hos-reports.shift-summary');
    Route::get('hos-reports/shift-times', [\App\Http\Controllers\HosReportsController::class, 'getShiftTimes'])->name('hos-reports.shift-times');
    Route::get('hos-reports/sales-summary', [\App\Http\Controllers\HosReportsController::class, 'getSalesSummary'])->name('hos-reports.sales-summary');
    Route::get('hos-reports/analytical-sales', [\App\Http\Controllers\HosReportsController::class, 'getAnalyticalSales'])->name('hos-reports.analytical-sales');
    Route::get('hos-reports/tank-monitoring', [\App\Http\Controllers\HosReportsController::class, 'getTankMonitoring'])->name('hos-reports.tank-monitoring');
    Route::get('hos-reports/tanks-from-measurements', [\App\Http\Controllers\HosReportsController::class, 'getTanksFromMeasurements'])->name('hos-reports.tanks-from-measurements');
    Route::get('hos-reports/export-excel', [\App\Http\Controllers\HosReportsController::class, 'exportExcel'])->name('hos-reports.export.excel');
    Route::get('hos-reports/export-pdf', [\App\Http\Controllers\HosReportsController::class, 'exportPdf'])->name('hos-reports.export.pdf');
});

require __DIR__.'/auth.php';
