<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Exports\TankInventoryExport;
use Maatwebsite\Excel\Facades\Excel;
use Barryvdh\DomPDF\Facade\Pdf;
use App\Models\TankInventory;

class TankInventoryListController extends Controller
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

            return response()->json($this->showData(request()));
        }

        return view('tank_inventories.index');
    }

    /**
     * Export tank inventories to Excel.
     */
    public function exportExcel(Request $request): \Symfony\Component\HttpFoundation\BinaryFileResponse
    {
        $filters = [
            'start_date' => $request->input('start_date'),
            'end_date' => $request->input('end_date'),
            'start_time' => $request->input('start_time'),
            'end_time' => $request->input('end_time'),
            'tank_id' => $request->input('tank_id'),
            'station_id' => $request->input('station_id'),
        ];

        return Excel::download(new TankInventoryExport($filters), 'tank_inventories_' . now()->format('Y-m-d_His') . '.xlsx');
    }

    /**
     * Export tank inventories to PDF.
     */
    public function exportPdf(Request $request): \Illuminate\Http\Response
    {
        $filters = [
            'start_date' => $request->input('start_date'),
            'end_date' => $request->input('end_date'),
            'start_time' => $request->input('start_time'),
            'end_time' => $request->input('end_time'),
            'tank_id' => $request->input('tank_id'),
            'station_id' => $request->input('station_id'),
        ];

        $query = TankInventory::query();

        if (!empty($filters['start_date'])) {
            $query->whereDate('timestamp', '>=', $filters['start_date']);
        }

        if (!empty($filters['end_date'])) {
            $query->whereDate('timestamp', '<=', $filters['end_date']);
        }

        if (!empty($filters['start_time']) && !empty($filters['end_time'])) {
            $query->whereTime('timestamp', '>=', $filters['start_time'])->whereTime('timestamp', '<=', $filters['end_time']);
        }

        if (!empty($filters['tank_id'])) {
            $query->where('tank_id', 'like', '%' . $filters['tank_id'] . '%');
        }

        if (!empty($filters['station_id'])) {
            $query->where('station_id', $filters['station_id']);
        }

        $inventories = $query->orderBy('timestamp', 'desc')->get();

        $pdf = Pdf::loadView('reports.generic_pdf', [
            'title' => 'Tank Inventories Report',
            'filters' => $filters,
            'summary' => [
                'Total Records' => count($inventories),
                'Total Opening Stock' => number_format($inventories->sum('opening_stock'), 2) . ' L',
                'Total Closing Stock' => number_format($inventories->sum('closing_stock'), 2) . ' L',
            ],
            'headers' => ['ID', 'Device ID', 'Tank ID', 'Opening Stock', 'Closing Stock', 'Deliveries', 'Total Sales', 'Timestamp'],
            'data' => $inventories->map(function ($inv) {
                return [
                    $inv->id,
                    $inv->device_id,
                    $inv->tank_id,
                    number_format($inv->opening_stock, 2) . ' L',
                    number_format($inv->closing_stock, 2) . ' L',
                    number_format($inv->deliveries, 2) . ' L',
                    number_format($inv->total_sales, 2) . ' L',
                    \Carbon\Carbon::parse($inv->timestamp)->format('Y-m-d H:i:s'),
                ];
            }),
        ]);

        return $pdf->download('tank_inventories_' . now()->format('Y-m-d_His') . '.pdf');
    }

    protected function showData($request)
    {
        $columns = [
            'id',
            'uuid',
            'request_id',
            'pts_id',
            'tank',
            'fuel_grade_id',
            'fuel_grade_name',
            'configuration_id',
            'snapshot_datetime',
            'absolute_product_height',
            'absolute_water_height',
            'absolute_temperature',
            'absolute_product_volume',
            'absolute_product_tc_volume',
            'absolute_product_density',
            'absolute_product_mass',
            'pumps_dispensed_volume',
            'station_id',
            'bos_tank_inventory_id',
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

        $query = \App\Models\TankInventory::with('station');

        // Date and Time Filters (using snapshot_datetime)
        if ($request->filled('from_date') || $request->filled('to_date') || $request->filled('from_time') || $request->filled('to_time')) {
            $from_date = $request->input('from_date');
            $to_date = $request->input('to_date');
            $from_time = $request->input('from_time', '00:00:00');
            $to_time = $request->input('to_time', '23:59:59');

            if ($from_date && $to_date) {
                // Both dates provided
                $from_datetime = $from_date.' '.$from_time;
                $to_datetime = $to_date.' '.$to_time;
                $query->whereBetween('snapshot_datetime', [$from_datetime, $to_datetime]);
            } elseif ($from_date) {
                // Only from_date provided
                $from_datetime = $from_date.' '.$from_time;
                $query->where('snapshot_datetime', '>=', $from_datetime);
            } elseif ($to_date) {
                // Only to_date provided
                $to_datetime = $to_date.' '.$to_time;
                $query->where('snapshot_datetime', '<=', $to_datetime);
            }
        }

        // Tank ID Search Filter
        if ($request->filled('tank_search')) {
            $query->where('tank', 'like', '%'.$request->input('tank_search').'%');
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
                    ->orWhere('fuel_grade_name', 'like', "%{$search}%");
            });
        }

        $totalData = \App\Models\TankInventory::count();
        $totalFiltered = $query->count();

        // Ordering and Pagination
        $data = $query->orderBy($order, $orderDir)
            ->offset($start)
            ->limit($length)
            ->get();

        $data = $data->map(function ($row) {
            $row = $row->toArray();

            // Format timestamps
            $row['snapshot_datetime'] = $row['snapshot_datetime'] ? \Carbon\Carbon::parse($row['snapshot_datetime'])->format('Y-m-d H:i:s') : '-';
            $row['synced_at'] = $row['synced_at'] ? \Carbon\Carbon::parse($row['synced_at'])->format('Y-m-d H:i:s') : '-';
            $row['created_at_bos'] = $row['created_at_bos'] ? \Carbon\Carbon::parse($row['created_at_bos'])->format('Y-m-d H:i:s') : '-';
            $row['updated_at_bos'] = $row['updated_at_bos'] ? \Carbon\Carbon::parse($row['updated_at_bos'])->format('Y-m-d H:i:s') : '-';
            $row['created_at'] = \Carbon\Carbon::parse($row['created_at'])->format('Y-m-d H:i:s');
            $row['updated_at'] = \Carbon\Carbon::parse($row['updated_at'])->format('Y-m-d H:i:s');

            // Format decimal values
            $row['absolute_product_height'] = $row['absolute_product_height'] ? number_format($row['absolute_product_height'], 3) : '-';
            $row['absolute_water_height'] = $row['absolute_water_height'] ? number_format($row['absolute_water_height'], 3) : '-';
            $row['absolute_temperature'] = $row['absolute_temperature'] ? number_format($row['absolute_temperature'], 2) : '-';
            $row['absolute_product_volume'] = $row['absolute_product_volume'] ? number_format($row['absolute_product_volume'], 3) : '-';
            $row['absolute_product_tc_volume'] = $row['absolute_product_tc_volume'] ? number_format($row['absolute_product_tc_volume'], 3) : '-';
            $row['absolute_product_density'] = $row['absolute_product_density'] ? number_format($row['absolute_product_density'], 3) : '-';
            $row['absolute_product_mass'] = $row['absolute_product_mass'] ? number_format($row['absolute_product_mass'], 3) : '-';
            $row['pumps_dispensed_volume'] = $row['pumps_dispensed_volume'] ? number_format($row['pumps_dispensed_volume'], 3) : '-';

            // Format probe_data JSON
            if (is_string($row['probe_data'])) {
                $row['probe_data'] = json_decode($row['probe_data'], true);
            }
            $row['probe_data'] = is_array($row['probe_data']) ? json_encode($row['probe_data']) : '-';

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
