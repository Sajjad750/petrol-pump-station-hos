<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Exports\ShiftExport;
use Maatwebsite\Excel\Facades\Excel;
use Barryvdh\DomPDF\Facade\Pdf;
use App\Models\Shift;

class ShiftListController extends Controller
{
    /**
     * Handle the incoming request.
     */
    public function __invoke(Request $request)
    {
        if (request()->ajax()) {
            // Return filter options if requested
            if ($request->has('get_filter_options')) {
                return response()->json($this->getFilterOptions());
            }

            return response()->json($this->showData(request()));
        }

        return view('shifts.index');
    }

    /**
     * Export shifts to Excel.
     */
    public function exportExcel(Request $request): \Symfony\Component\HttpFoundation\BinaryFileResponse
    {
        $filters = $request->only(['start_date', 'end_date', 'start_time', 'end_time', 'status', 'close_type', 'user_id']);

        return Excel::download(new ShiftExport($filters), 'shifts_' . now()->format('Y-m-d_His') . '.xlsx');
    }

    /**
     * Export shifts to PDF.
     */
    public function exportPdf(Request $request): \Illuminate\Http\Response
    {
        $filters = $request->only(['start_date', 'end_date', 'start_time', 'end_time', 'status', 'close_type', 'user_id']);

        $query = Shift::query()->with('user');

        if (!empty($filters['start_date'])) {
            $query->whereDate('start_time', '>=', $filters['start_date']);
        }

        if (!empty($filters['end_date'])) {
            $query->whereDate('start_time', '<=', $filters['end_date']);
        }

        if (!empty($filters['status'])) {
            $query->where('status', $filters['status']);
        }

        if (!empty($filters['close_type'])) {
            $query->where('close_type', $filters['close_type']);
        }

        if (!empty($filters['user_id'])) {
            $query->where('user_id', 'like', '%' . $filters['user_id'] . '%');
        }

        $shifts = $query->orderBy('start_time', 'desc')->get();

        $pdf = Pdf::loadView('reports.generic_pdf', [
            'title' => 'Shifts Report',
            'filters' => $filters,
            'summary' => ['Total Records' => count($shifts), 'Total Sales' => '₹' . number_format($shifts->sum('total_sales'), 2)],
            'headers' => ['ID', 'Device ID', 'User ID', 'Start Time', 'End Time', 'Status', 'Close Type', 'Total Sales'],
            'data' => $shifts->map(fn ($s) => [$s->id, $s->device_id, $s->user_id, \Carbon\Carbon::parse($s->start_time)->format('Y-m-d H:i:s'), $s->end_time ? \Carbon\Carbon::parse($s->end_time)->format('Y-m-d H:i:s') : 'N/A', ucfirst($s->status ?? 'N/A'), ucfirst($s->close_type ?? 'N/A'), '₹' . number_format($s->total_sales ?? 0, 2)]),
        ]);

        return $pdf->download('shifts_' . now()->format('Y-m-d_His') . '.pdf');
    }

    protected function getFilterOptions(): array
    {
        $statuses = ['started', 'completed'];
        $closeTypes = ['manual', 'auto'];

        return [
            'statuses' => $statuses,
            'close_types' => $closeTypes,
        ];
    }

    protected function showData($request)
    {
        $columns = [
            'id',
            'start_time',
            'start_time_utc',
            'end_time',
            'end_time_utc',
            'user_id',
            'notes',
            'close_type',
            'status',
            'auto_close_time',
            'auto_close_time_utc',
            'station_id',
            'bos_shift_id',
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

        $query = \App\Models\Shift::with('station');

        // Date and Time Filters (using start_time)
        if ($request->filled('from_date') || $request->filled('to_date') || $request->filled('from_time') || $request->filled('to_time')) {
            $from_date = $request->input('from_date');
            $to_date = $request->input('to_date');
            $from_time = $request->input('from_time', '00:00:00');
            $to_time = $request->input('to_time', '23:59:59');

            if ($from_date && $to_date) {
                // Both dates provided
                $from_datetime = $from_date.' '.$from_time;
                $to_datetime = $to_date.' '.$to_time;
                $query->whereBetween('start_time', [$from_datetime, $to_datetime]);
            } elseif ($from_date) {
                // Only from_date provided
                $from_datetime = $from_date.' '.$from_time;
                $query->where('start_time', '>=', $from_datetime);
            } elseif ($to_date) {
                // Only to_date provided
                $to_datetime = $to_date.' '.$to_time;
                $query->where('start_time', '<=', $to_datetime);
            }
        }

        // Status Filter
        if ($request->filled('status')) {
            $query->where('status', $request->input('status'));
        }

        // Close Type Filter
        if ($request->filled('close_type')) {
            $query->where('close_type', $request->input('close_type'));
        }

        // User ID Search Filter
        if ($request->filled('user_id')) {
            $query->where('user_id', $request->input('user_id'));
        }

        // Global search for all columns
        if ($request->has('search') && $request->search['value'] != '') {
            $search = $request->search['value'];
            $query->where(function ($q) use ($search) {
                $q->where('id', 'like', "%{$search}%")
                    ->orWhere('user_id', 'like', "%{$search}%")
                    ->orWhere('status', 'like', "%{$search}%")
                    ->orWhere('close_type', 'like', "%{$search}%")
                    ->orWhere('notes', 'like', "%{$search}%");
            });
        }

        $totalData = \App\Models\Shift::count();
        $totalFiltered = $query->count();

        // Ordering and Pagination
        $data = $query->orderBy($order, $orderDir)
            ->offset($start)
            ->limit($length)
            ->get();

        $data = $data->map(function ($row) {
            $row = $row->toArray();

            // Format timestamps
            $row['start_time'] = $row['start_time'] ? \Carbon\Carbon::parse($row['start_time'])->format('Y-m-d H:i:s') : '-';
            $row['start_time_utc'] = $row['start_time_utc'] ? \Carbon\Carbon::parse($row['start_time_utc'])->format('Y-m-d H:i:s') : '-';
            $row['end_time'] = $row['end_time'] ? \Carbon\Carbon::parse($row['end_time'])->format('Y-m-d H:i:s') : '-';
            $row['end_time_utc'] = $row['end_time_utc'] ? \Carbon\Carbon::parse($row['end_time_utc'])->format('Y-m-d H:i:s') : '-';
            $row['auto_close_time'] = $row['auto_close_time'] ? \Carbon\Carbon::parse($row['auto_close_time'])->format('Y-m-d H:i:s') : '-';
            $row['auto_close_time_utc'] = $row['auto_close_time_utc'] ? \Carbon\Carbon::parse($row['auto_close_time_utc'])->format('Y-m-d H:i:s') : '-';
            $row['synced_at'] = $row['synced_at'] ? \Carbon\Carbon::parse($row['synced_at'])->format('Y-m-d H:i:s') : '-';
            $row['created_at_bos'] = $row['created_at_bos'] ? \Carbon\Carbon::parse($row['created_at_bos'])->format('Y-m-d H:i:s') : '-';
            $row['updated_at_bos'] = $row['updated_at_bos'] ? \Carbon\Carbon::parse($row['updated_at_bos'])->format('Y-m-d H:i:s') : '-';
            $row['created_at'] = \Carbon\Carbon::parse($row['created_at'])->format('Y-m-d H:i:s');
            $row['updated_at'] = \Carbon\Carbon::parse($row['updated_at'])->format('Y-m-d H:i:s');

            // Add status badge
            $row['status_badge'] = $row['status'] === 'started'
                ? '<span class="badge badge-success">Started</span>'
                : '<span class="badge badge-secondary">Completed</span>';

            // Add close type badge
            $row['close_type_badge'] = $row['close_type'] === 'manual'
                ? '<span class="badge badge-primary">Manual</span>'
                : '<span class="badge badge-info">Auto</span>';

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
