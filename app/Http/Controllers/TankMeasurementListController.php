<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Exports\TankMeasurementExport;
use Maatwebsite\Excel\Facades\Excel;
use Barryvdh\DomPDF\Facade\Pdf;
use App\Models\TankMeasurement;

class TankMeasurementListController extends Controller
{
    /**
     * Handle the incoming request.
     */
    public function __invoke(Request $request)
    {
        if (request()->ajax()) {
            // Return stations if requested
            if ($request->has('get_stations')) {
                $stations = \App\Models\Station::select('id', 'site_name')
                    ->orderBy('site_name')
                    ->get();

                return response()->json(['stations' => $stations]);
            }

            // Return filter options if requested
            if ($request->has('get_filter_options')) {
                return response()->json($this->getFilterOptions());
            }

            return response()->json($this->showData(request()));
        }

        return view('tank_measurements.index');
    }

    /**
     * Export tank measurements to Excel.
     */
    public function exportExcel(Request $request): \Symfony\Component\HttpFoundation\BinaryFileResponse
    {
        $filters = [
            'from_date' => $request->input('from_date'),
            'to_date' => $request->input('to_date'),
            'status' => $request->input('status'),
            'tank_id' => $request->input('tank_id'),
            'station_id' => $request->input('station_id'),
        ];

        $filename = 'tank_measurements_' . now()->format('Y-m-d_His') . '.xlsx';

        return Excel::download(new TankMeasurementExport($filters), $filename);
    }

    /**
     * Export tank measurements to PDF.
     */
    public function exportPdf(Request $request): \Illuminate\Http\Response
    {
        $filters = [
            'from_date' => $request->input('from_date'),
            'to_date' => $request->input('to_date'),
            'status' => $request->input('status'),
            'tank_id' => $request->input('tank_id'),
        ];

        $query = TankMeasurement::query()->with('tank');

        // Apply same filters as export
        if (!empty($filters['from_date'])) {
            $query->whereDate('created_at', '>=', $filters['from_date']);
        }

        if (!empty($filters['to_date'])) {
            $query->whereDate('created_at', '<=', $filters['to_date']);
        }

        if (!empty($filters['status'])) {
            $query->where('status', $filters['status']);
        }

        if (!empty($filters['tank_id'])) {
            $query->where('tank_id', $filters['tank_id']);
        }

        if (!empty($filters['station_id'])) {
            $query->where('station_id', $filters['station_id']);
        }

        $measurements = $query->orderBy('created_at', 'desc')->get();

        $pdf = Pdf::loadView('reports.tank_measurements_pdf', [
            'measurements' => $measurements,
            'filters' => $filters,
        ]);

        $filename = 'tank_measurements_' . now()->format('Y-m-d_His') . '.pdf';

        return $pdf->download($filename);
    }

    protected function getFilterOptions(): array
    {
        $statuses = \App\Models\TankMeasurement::query()
            ->distinct()
            ->whereNotNull('status')
            ->pluck('status')
            ->sort()
            ->values()
            ->toArray();

        return [
            'statuses' => $statuses,
        ];
    }

    protected function showData($request)
    {
        $columns = [
            'id',
            'uuid',
            'request_id',
            'pts_id',
            'date_time',
            'tank',
            'fuel_grade_id',
            'fuel_grade_name',
            'status',
            'alarms',
            'product_height',
            'water_height',
            'temperature',
            'product_volume',
            'water_volume',
            'product_ullage',
            'product_tc_volume',
            'product_density',
            'product_mass',
            'tank_filling_percentage',
            'configuration_id',
            'station_id',
            'bos_tank_measurement_id',
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

        $query = \App\Models\TankMeasurement::with('station');

        // Date Filters
        if ($request->filled('from_date') || $request->filled('to_date')) {
            $from_date = $request->input('from_date');
            $to_date = $request->input('to_date');

            if ($from_date && $to_date) {
                // Both dates provided
                $from_datetime = $from_date.' 00:00:00';
                $to_datetime = $to_date.' 23:59:59';
                $query->whereBetween('date_time', [$from_datetime, $to_datetime]);
            } elseif ($from_date) {
                // Only from_date provided
                $from_datetime = $from_date.' 00:00:00';
                $query->where('date_time', '>=', $from_datetime);
            } elseif ($to_date) {
                // Only to_date provided
                $to_datetime = $to_date.' 23:59:59';
                $query->where('date_time', '<=', $to_datetime);
            }
        }

        // Status Filter
        if ($request->filled('status')) {
            $query->where('status', $request->input('status'));
        }

        // Tank ID Filter
        if ($request->filled('tank_id')) {
            $query->where('tank', $request->input('tank_id'));
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
                    ->orWhere('uuid', 'like', "%{$search}%")
                    ->orWhere('pts_id', 'like', "%{$search}%")
                    ->orWhere('tank', 'like', "%{$search}%")
                    ->orWhere('fuel_grade_name', 'like', "%{$search}%")
                    ->orWhere('status', 'like', "%{$search}%");
            });
        }

        $totalData = \App\Models\TankMeasurement::count();
        $totalFiltered = $query->count();

        // Ordering and Pagination
        $data = $query->orderBy($order, $orderDir)
            ->offset($start)
            ->limit($length)
            ->get();

        $data = $data->map(function ($row) {
            $row = $row->toArray();

            // Format timestamps
            $row['date_time'] = $row['date_time'] ? \Carbon\Carbon::parse($row['date_time'])->format('Y-m-d H:i:s') : '-';
            $row['synced_at'] = $row['synced_at'] ? \Carbon\Carbon::parse($row['synced_at'])->format('Y-m-d H:i:s') : '-';
            $row['created_at_bos'] = $row['created_at_bos'] ? \Carbon\Carbon::parse($row['created_at_bos'])->format('Y-m-d H:i:s') : '-';
            $row['updated_at_bos'] = $row['updated_at_bos'] ? \Carbon\Carbon::parse($row['updated_at_bos'])->format('Y-m-d H:i:s') : '-';
            $row['created_at'] = \Carbon\Carbon::parse($row['created_at'])->format('Y-m-d H:i:s');
            $row['updated_at'] = \Carbon\Carbon::parse($row['updated_at'])->format('Y-m-d H:i:s');

            // Format decimal values
            $row['product_height'] = $row['product_height'] ? number_format($row['product_height'], 3) : '-';
            $row['water_height'] = $row['water_height'] ? number_format($row['water_height'], 3) : '-';
            $row['temperature'] = $row['temperature'] ? number_format($row['temperature'], 2) : '-';
            $row['product_volume'] = $row['product_volume'] ? number_format($row['product_volume'], 3) : '-';
            $row['water_volume'] = $row['water_volume'] ? number_format($row['water_volume'], 3) : '-';
            $row['product_ullage'] = $row['product_ullage'] ? number_format($row['product_ullage'], 3) : '-';
            $row['product_tc_volume'] = $row['product_tc_volume'] ? number_format($row['product_tc_volume'], 3) : '-';
            $row['product_density'] = $row['product_density'] ? number_format($row['product_density'], 3) : '-';
            $row['product_mass'] = $row['product_mass'] ? number_format($row['product_mass'], 3) : '-';
            $row['tank_filling_percentage'] = $row['tank_filling_percentage'] ? number_format($row['tank_filling_percentage'], 2) . '%' : '-';

            // Format alarms JSON
            if (is_string($row['alarms'])) {
                $row['alarms'] = json_decode($row['alarms'], true);
            }
            $row['alarms'] = is_array($row['alarms']) ? implode(', ', $row['alarms']) : $row['alarms'];

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
