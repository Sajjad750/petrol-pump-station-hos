<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class PumpTransactionListController extends Controller
{
    /**
     * Handle the incoming request.
     */
    public function __invoke(Request $request)
    {
        if (request()->ajax()) {
            return response()->json($this->showData(request()));
        }

        return view('pump_transactions.index');
    }

    protected function showData($request)
    {
        $columns = [
            'id',
            'request_id',
            'date_time_start',
            'date_time_end',
            'pts_pump_id',
            'pts_nozzle_id',
            'pts_fuel_grade_id',
            'fuel_grade_name',
            'pts_tank_id',
            'transaction_number',
            'volume',
            'tc_volume',
            'price',
            'amount',
            'starting_totalizer',
            'total_volume',
            'total_amount',
            'tag',
            'pts_user_id',
            'pts_configuration_id',
            'mode_of_payment',
            'options'
        ];

        $length = $request->input('length');
        $start = $request->input('start');
        $order = $columns[$request->input('order.0.column')];
        $orderDir = $request->input('order.0.dir');

        $query = \App\Models\PumpTransaction::query();

        // Global search for all columns
        if ($request->has('search') && $request->search['value'] != '') {
            $search = $request->search['value'];
            $query->where(function ($q) use ($search) {
                $q->where('id', 'like', "%{$search}%")
                    ->orWhere('transaction_number', 'like', "%{$search}%");
            });
        }

        $totalData = \App\Models\PumpTransaction::count();
        $totalFiltered = $query->count();

        // Ordering and Pagination
        $data = $query->orderBy($order, $orderDir)
            ->offset($start)
            ->limit($length)
            ->get();

        $data = $data->map(function ($row) {
            $row = $row->toArray();

            return $row;
        });

        $json_data = [
            "draw" => intval($request->input('draw')),
            "recordsTotal" => $totalData,
            "recordsFiltered" => $totalFiltered,
            "data" => $data,
        ];

        return $json_data;
    }
}
