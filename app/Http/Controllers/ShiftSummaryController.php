<?php

namespace App\Http\Controllers;

use App\Models\Shift;
use App\Models\ProductWiseSummary;
use App\Models\PaymentModeWiseSummary;
use App\Models\PumpTransaction;
use Illuminate\Support\Facades\DB;

class ShiftSummaryController extends Controller
{
    /**
     * Display shift summary page
     */
    public function show($id)
    {
        // Get the shift with station
        $shift = Shift::with('station')->findOrFail($id);

        // Resolve BOS identifiers to fetch related summaries correctly
        $bos_shift_id = $shift->bos_shift_id;
        $station_id = $shift->station_id;

        // Get Product Wise Summary for this shift using BOS shift id and station
        $productSummaries = ProductWiseSummary::where('bos_shift_id', $bos_shift_id)
            ->where('station_id', $station_id)
            ->get();

        // Get Payment Mode Wise Summary for this shift using BOS shift id and station
        $paymentSummaries = PaymentModeWiseSummary::where('bos_shift_id', $bos_shift_id)
            ->where('station_id', $station_id)
            ->get();

        // Get Pump Wise Summary from pump transactions
        $pumpSummaries = PumpTransaction::where('shift_id', $id)
            ->leftJoin('fuel_grades', 'pump_transactions.pts_fuel_grade_id', '=', 'fuel_grades.id')
            ->select(
                DB::raw('COALESCE(fuel_grades.name, "N/A") as product'),
                'pump_transactions.pts_pump_id as pump_no',
                'pump_transactions.pts_nozzle_id as nozzle_no',
                DB::raw('MIN(pump_transactions.starting_totalizer) as start_totalizer'),
                DB::raw('MAX(pump_transactions.starting_totalizer + pump_transactions.volume) as end_totalizer'),
                DB::raw('(MAX(pump_transactions.starting_totalizer + pump_transactions.volume) - MIN(pump_transactions.starting_totalizer)) as totalizer_volume'),
                DB::raw('SUM(pump_transactions.volume) as txn_volume'),
                DB::raw('SUM(pump_transactions.amount) as amount')
            )
            ->groupBy('fuel_grades.name', 'pump_transactions.pts_pump_id', 'pump_transactions.pts_nozzle_id')
            ->orderBy('fuel_grades.order_number')
            ->orderBy('pump_transactions.pts_pump_id')
            ->orderBy('pump_transactions.pts_nozzle_id')
            ->get();

        // Get other shifts from the same station for quick access
        $otherShifts = Shift::where('station_id', $shift->station_id)
            ->where('id', '!=', $shift->id)
            ->orderBy('start_time', 'desc')
            ->limit(10)
            ->get();

        return view('shifts.summary', compact(
            'shift',
            'productSummaries',
            'paymentSummaries',
            'pumpSummaries',
            'otherShifts'
        ));
    }
}
