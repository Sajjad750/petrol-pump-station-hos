<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Exports\PaymentModeWiseSummaryExport;
use Maatwebsite\Excel\Facades\Excel;
use Barryvdh\DomPDF\Facade\Pdf;
use App\Models\PaymentModeWiseSummary;

class PaymentModeWiseSummaryListController extends Controller
{
    /**
     * Handle the incoming request.
     */
    public function __invoke(Request $request)
    {
        if (request()->ajax()) {
            return response()->json($this->showData(request()));
        }

        return view('payment_mode_wise_summaries.index');
    }

    public function exportExcel(Request $request): \Symfony\Component\HttpFoundation\BinaryFileResponse
    {
        $filters = $request->only(['start_date', 'end_date', 'start_time', 'end_time', 'shift_id', 'payment_mode']);

        return Excel::download(new PaymentModeWiseSummaryExport($filters), 'payment_mode_wise_summaries_' . now()->format('Y-m-d_His') . '.xlsx');
    }

    public function exportPdf(Request $request): \Illuminate\Http\Response
    {
        $filters = $request->only(['start_date', 'end_date', 'start_time', 'end_time', 'shift_id', 'payment_mode']);
        $query = PaymentModeWiseSummary::query()->with('shift');

        if (!empty($filters['shift_id'])) {
            $query->where('shift_id', 'like', '%' . $filters['shift_id'] . '%');
        }

        if (!empty($filters['payment_mode'])) {
            $query->where('payment_mode', 'like', '%' . $filters['payment_mode'] . '%');
        }
        $summaries = $query->orderBy('created_at', 'desc')->get();
        $pdf = Pdf::loadView('reports.generic_pdf', [
            'title' => 'Payment Mode Wise Summaries Report',
            'filters' => $filters,
            'summary' => ['Total Records' => count($summaries), 'Total Amount' => '₹' . number_format($summaries->sum('total_amount'), 2)],
            'headers' => ['ID', 'Device ID', 'Shift ID', 'Payment Mode', 'Total Volume', 'Total Amount'],
            'data' => $summaries->map(fn ($s) => [$s->id, $s->device_id, $s->shift_id, ucfirst($s->payment_mode ?? 'N/A'), number_format($s->total_volume, 2) . ' L', '₹' . number_format($s->total_amount, 2)]),
        ]);

        return $pdf->download('payment_mode_wise_summaries_' . now()->format('Y-m-d_His') . '.pdf');
    }

    protected function showData($request)
    {
        $columns = [
            'id',
            'shift_id',
            'mop',
            'volume',
            'amount',
            'station_id',
            'bos_payment_mode_wise_summary_id',
            'synced_at',
            'created_at_bos',
            'updated_at_bos',
            'created_at',
            'updated_at',
            'options'
        ];

        $length = $request->input('length');
        $start = $request->input('start');
        $order = $columns[$request->input('order.0.column')];
        $orderDir = $request->input('order.0.dir');

        $query = \App\Models\PaymentModeWiseSummary::with('station', 'shift');

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
                $query->whereBetween('created_at', [$from_datetime, $to_datetime]);
            } elseif ($from_date) {
                // Only from_date provided
                $from_datetime = $from_date.' '.$from_time;
                $query->where('created_at', '>=', $from_datetime);
            } elseif ($to_date) {
                // Only to_date provided
                $to_datetime = $to_date.' '.$to_time;
                $query->where('created_at', '<=', $to_datetime);
            }
        }

        // Shift ID Filter
        if ($request->filled('shift_id')) {
            $query->where('shift_id', $request->input('shift_id'));
        }

        // Mode of Payment (MOP) Filter
        if ($request->filled('mop')) {
            $query->where('mop', 'like', '%'.$request->input('mop').'%');
        }

        // Global search for all columns
        if ($request->has('search') && $request->search['value'] != '') {
            $search = $request->search['value'];
            $query->where(function ($q) use ($search) {
                $q->where('id', 'like', "%{$search}%")
                    ->orWhere('shift_id', 'like', "%{$search}%")
                    ->orWhere('mop', 'like', "%{$search}%")
                    ->orWhere('volume', 'like', "%{$search}%")
                    ->orWhere('amount', 'like', "%{$search}%");
            });
        }

        $totalData = \App\Models\PaymentModeWiseSummary::count();
        $totalFiltered = $query->count();

        // Ordering and Pagination
        $data = $query->orderBy($order, $orderDir)
            ->offset($start)
            ->limit($length)
            ->get();

        $data = $data->map(function ($row) {
            $row = $row->toArray();

            // Format timestamps
            $row['synced_at'] = $row['synced_at'] ? \Carbon\Carbon::parse($row['synced_at'])->format('Y-m-d H:i:s') : '-';
            $row['created_at_bos'] = $row['created_at_bos'] ? \Carbon\Carbon::parse($row['created_at_bos'])->format('Y-m-d H:i:s') : '-';
            $row['updated_at_bos'] = $row['updated_at_bos'] ? \Carbon\Carbon::parse($row['updated_at_bos'])->format('Y-m-d H:i:s') : '-';
            $row['created_at'] = \Carbon\Carbon::parse($row['created_at'])->format('Y-m-d H:i:s');
            $row['updated_at'] = \Carbon\Carbon::parse($row['updated_at'])->format('Y-m-d H:i:s');

            // Format decimal values
            $row['volume'] = $row['volume'] ? number_format($row['volume'], 2) : '0.00';
            $row['amount'] = $row['amount'] ? number_format($row['amount'], 2) : '0.00';

            // Calculate average price per liter
            $volume_numeric = (float) str_replace(',', '', $row['volume']);
            $amount_numeric = (float) str_replace(',', '', $row['amount']);

            if ($volume_numeric > 0) {
                $row['avg_price'] = number_format($amount_numeric / $volume_numeric, 3);
            } else {
                $row['avg_price'] = '-';
            }

            // Add shift start time if available
            $row['shift_start_time'] = isset($row['shift']['start_time'])
                ? \Carbon\Carbon::parse($row['shift']['start_time'])->format('Y-m-d H:i:s')
                : '-';

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
