<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Exports\PumpTransactionExport;
use Maatwebsite\Excel\Facades\Excel;
use Barryvdh\DomPDF\Facade\Pdf;
use App\Models\PumpTransaction;

class PumpTransactionListController extends Controller
{
    /**
     * Handle the incoming request.
     */
    public function __invoke(Request $request)
    {
        if (request()->ajax()) {
            // Return stations for dropdown
            if ($request->has('get_stations')) {
                return response()->json([
                    'stations' => \App\Models\Station::select('id', 'site_name')->orderBy('site_name')->get()
                ]);
            }

            return response()->json($this->showData(request()));
        }

        return view('pump_transactions.index');
    }

    /**
     * Export pump transactions to Excel.
     */
    public function exportExcel(Request $request): \Symfony\Component\HttpFoundation\BinaryFileResponse
    {
        $filters = [
            'from_date' => $request->input('from_date'),
            'to_date' => $request->input('to_date'),
            'from_time' => $request->input('from_time'),
            'to_time' => $request->input('to_time'),
            'station_id' => $request->input('station_id'),
        ];

        $filename = 'pump_transactions_' . now()->format('Y-m-d_His') . '.xlsx';

        return Excel::download(new PumpTransactionExport($filters), $filename);
    }

    /**
     * Export pump transactions to PDF.
     */
    public function exportPdf(Request $request): \Illuminate\Http\Response
    {
        $filters = [
            'from_date' => $request->input('from_date'),
            'to_date' => $request->input('to_date'),
            'from_time' => $request->input('from_time'),
            'to_time' => $request->input('to_time'),
            'station_id' => $request->input('station_id'),
        ];

        $query = PumpTransaction::query()->with(['pump', 'shift', 'station']);

        // Apply same filters as export
        if (!empty($filters['from_date'])) {
            $query->whereDate('created_at', '>=', $filters['from_date']);
        }

        if (!empty($filters['to_date'])) {
            $query->whereDate('created_at', '<=', $filters['to_date']);
        }

        if (!empty($filters['from_time']) && !empty($filters['to_time'])) {
            $query->whereTime('created_at', '>=', $filters['from_time'])
                  ->whereTime('created_at', '<=', $filters['to_time']);
        }

        if (!empty($filters['station_id'])) {
            $query->where('station_id', $filters['station_id']);
        }

        $transactions = $query->orderBy('created_at', 'desc')->get();

        $pdf = Pdf::loadView('reports.pump_transactions_pdf', [
            'transactions' => $transactions,
            'filters' => $filters,
        ]);

        $filename = 'pump_transactions_' . now()->format('Y-m-d_His') . '.pdf';

        return $pdf->download($filename);
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

        // Date and Time Filters
        if ($request->filled('from_date') || $request->filled('to_date') || $request->filled('from_time') || $request->filled('to_time')) {
            $from_date = $request->input('from_date');
            $to_date = $request->input('to_date');
            $from_time = $request->input('from_time', '00:00:00');
            $to_time = $request->input('to_time', '23:59:59');

            if ($from_date && $to_date) {
                // Both dates provided
                $from_datetime = $from_date.' '.$from_time;
                $to_datetime = $to_date.' '.$to_time;
                $query->whereBetween('date_time_start', [$from_datetime, $to_datetime]);
            } elseif ($from_date) {
                // Only from_date provided
                $from_datetime = $from_date.' '.$from_time;
                $query->where('date_time_start', '>=', $from_datetime);
            } elseif ($to_date) {
                // Only to_date provided
                $to_datetime = $to_date.' '.$to_time;
                $query->where('date_time_start', '<=', $to_datetime);
            }
        }

        // Station Filter
        if ($request->filled('station_id')) {
            $query->where('station_id', $request->input('station_id'));
        }

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
