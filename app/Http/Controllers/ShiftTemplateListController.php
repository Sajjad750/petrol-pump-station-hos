<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Exports\ShiftTemplateExport;
use Maatwebsite\Excel\Facades\Excel;
use Barryvdh\DomPDF\Facade\Pdf;
use App\Models\ShiftTemplate;

class ShiftTemplateListController extends Controller
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

        return view('shift_templates.index');
    }

    public function exportExcel(Request $request): \Symfony\Component\HttpFoundation\BinaryFileResponse
    {
        $filters = $request->only(['timezone', 'device_id']);

        return Excel::download(new ShiftTemplateExport($filters), 'shift_templates_' . now()->format('Y-m-d_His') . '.xlsx');
    }

    public function exportPdf(Request $request): \Illuminate\Http\Response
    {
        $filters = $request->only(['timezone', 'device_id']);
        $query = ShiftTemplate::query();

        if (!empty($filters['timezone'])) {
            $query->where('timezone', $filters['timezone']);
        }

        if (!empty($filters['device_id'])) {
            $query->where('device_id', 'like', '%' . $filters['device_id'] . '%');
        }
        $templates = $query->orderBy('created_at', 'desc')->get();
        $pdf = Pdf::loadView('reports.generic_pdf', [
            'title' => 'Shift Templates Report',
            'filters' => $filters,
            'summary' => ['Total Records' => count($templates)],
            'headers' => ['ID', 'Device ID', 'Name', 'Start Time', 'End Time', 'Timezone'],
            'data' => $templates->map(fn ($t) => [$t->id, $t->device_id, $t->name ?? 'N/A', $t->start_time ?? 'N/A', $t->end_time ?? 'N/A', $t->timezone ?? 'N/A']),
        ]);

        return $pdf->download('shift_templates_' . now()->format('Y-m-d_His') . '.pdf');
    }

    protected function getFilterOptions(): array
    {
        $timezones = \App\Models\ShiftTemplate::query()
            ->distinct()
            ->whereNotNull('timezone')
            ->pluck('timezone')
            ->sort()
            ->values()
            ->toArray();

        return [
            'timezones' => $timezones,
        ];
    }

    protected function showData($request)
    {
        $columns = [
            'id',
            'uuid',
            'pts2_device_id',
            'end_time',
            'timezone',
            'station_id',
            'bos_shift_template_id',
            'bos_uuid',
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

        $query = \App\Models\ShiftTemplate::with('station');

        // Timezone Filter
        if ($request->filled('timezone')) {
            $query->where('timezone', $request->input('timezone'));
        }

        // Device ID Filter
        if ($request->filled('device_id')) {
            $query->where('pts2_device_id', $request->input('device_id'));
        }

        // Global search for all columns
        if ($request->has('search') && $request->search['value'] != '') {
            $search = $request->search['value'];
            $query->where(function ($q) use ($search) {
                $q->where('id', 'like', "%{$search}%")
                    ->orWhere('uuid', 'like', "%{$search}%")
                    ->orWhere('pts2_device_id', 'like', "%{$search}%")
                    ->orWhere('timezone', 'like', "%{$search}%");
            });
        }

        $totalData = \App\Models\ShiftTemplate::count();
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

            // Format end_time to display
            if ($row['end_time']) {
                // end_time might be in H:i:s format already, let's format it
                try {
                    $time = \Carbon\Carbon::parse($row['end_time']);
                    $row['end_time_24h'] = $time->format('H:i:s');
                    $row['end_time_12h'] = $time->format('g:i A');
                } catch (\Exception $e) {
                    $row['end_time_24h'] = $row['end_time'];
                    $row['end_time_12h'] = '-';
                }
            } else {
                $row['end_time_24h'] = '-';
                $row['end_time_12h'] = '-';
            }

            // Add timezone display badge
            $timezone_display = match ($row['timezone']) {
                'UTC' => 'UTC',
                'America/New_York' => 'Eastern',
                'America/Chicago' => 'Central',
                'America/Denver' => 'Mountain',
                'America/Los_Angeles' => 'Pacific',
                'Europe/London' => 'GMT',
                'Europe/Paris' => 'CET',
                'Asia/Tokyo' => 'JST',
                'Asia/Shanghai' => 'CST',
                default => $row['timezone'],
            };

            $row['timezone_badge'] = '<span class="badge badge-info">' . $timezone_display . '</span>';

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
