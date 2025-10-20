<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Exports\TankDeliveryExport;
use Maatwebsite\Excel\Facades\Excel;
use Barryvdh\DomPDF\Facade\Pdf;
use App\Models\TankDelivery;

class TankDeliveryListController extends Controller
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

        return view('tank_deliveries.index');
    }

    /**
     * Export tank deliveries to Excel.
     */
    public function exportExcel(Request $request): \Symfony\Component\HttpFoundation\BinaryFileResponse
    {
        $filters = [
            'start_date' => $request->input('start_date'),
            'end_date' => $request->input('end_date'),
            'start_time' => $request->input('start_time'),
            'end_time' => $request->input('end_time'),
            'tank' => $request->input('tank'),
            'tank_id' => $request->input('tank_id'),
        ];

        $filename = 'tank_deliveries_' . now()->format('Y-m-d_His') . '.xlsx';

        return Excel::download(new TankDeliveryExport($filters), $filename);
    }

    /**
     * Export tank deliveries to PDF.
     */
    public function exportPdf(Request $request): \Illuminate\Http\Response
    {
        $filters = [
            'start_date' => $request->input('start_date'),
            'end_date' => $request->input('end_date'),
            'start_time' => $request->input('start_time'),
            'end_time' => $request->input('end_time'),
            'tank' => $request->input('tank'),
            'tank_id' => $request->input('tank_id'),
        ];

        $query = TankDelivery::query()->with('station');

        if (!empty($filters['start_date'])) {
            $query->whereDate('start_datetime', '>=', $filters['start_date']);
        }

        if (!empty($filters['end_date'])) {
            $query->whereDate('start_datetime', '<=', $filters['end_date']);
        }

        if (!empty($filters['start_time']) && !empty($filters['end_time'])) {
            $query->whereTime('start_datetime', '>=', $filters['start_time'])
                  ->whereTime('start_datetime', '<=', $filters['end_time']);
        }

        if (!empty($filters['tank'])) {
            $query->where('tank', $filters['tank']);
        }

        if (!empty($filters['tank_id'])) {
            $query->where('tank', 'like', '%' . $filters['tank_id'] . '%');
        }

        $deliveries = $query->orderBy('start_datetime', 'desc')->get();

        $pdf = Pdf::loadView('reports.tank_deliveries_pdf', [
            'deliveries' => $deliveries,
            'filters' => $filters,
        ]);

        $filename = 'tank_deliveries_' . now()->format('Y-m-d_His') . '.pdf';

        return $pdf->download($filename);
    }

    protected function getFilterOptions(): array
    {
        $tanks = \App\Models\TankDelivery::query()
            ->distinct()
            ->whereNotNull('tank')
            ->pluck('tank')
            ->sort()
            ->values()
            ->toArray();

        return [
            'tanks' => $tanks,
        ];
    }

    protected function showData($request)
    {
        $columns = [
            'id',
            'uuid',
            'request_id',
            'pts_id',
            'pts_delivery_id',
            'tank',
            'fuel_grade_id',
            'fuel_grade_name',
            'configuration_id',
            'start_datetime',
            'end_datetime',
            'start_product_height',
            'start_water_height',
            'start_temperature',
            'start_product_volume',
            'start_product_tc_volume',
            'start_product_density',
            'start_product_mass',
            'end_product_height',
            'end_water_height',
            'end_temperature',
            'end_product_volume',
            'end_product_tc_volume',
            'end_product_density',
            'end_product_mass',
            'received_product_volume',
            'absolute_product_height',
            'absolute_water_height',
            'absolute_temperature',
            'absolute_product_volume',
            'absolute_product_tc_volume',
            'absolute_product_density',
            'absolute_product_mass',
            'pumps_dispensed_volume',
            'station_id',
            'bos_tank_delivery_id',
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

        $query = \App\Models\TankDelivery::with('station');

        // Date and Time Filters (using start_datetime)
        if ($request->filled('from_date') || $request->filled('to_date') || $request->filled('from_time') || $request->filled('to_time')) {
            $from_date = $request->input('from_date');
            $to_date = $request->input('to_date');
            $from_time = $request->input('from_time', '00:00:00');
            $to_time = $request->input('to_time', '23:59:59');

            if ($from_date && $to_date) {
                // Both dates provided
                $from_datetime = $from_date.' '.$from_time;
                $to_datetime = $to_date.' '.$to_time;
                $query->whereBetween('start_datetime', [$from_datetime, $to_datetime]);
            } elseif ($from_date) {
                // Only from_date provided
                $from_datetime = $from_date.' '.$from_time;
                $query->where('start_datetime', '>=', $from_datetime);
            } elseif ($to_date) {
                // Only to_date provided
                $to_datetime = $to_date.' '.$to_time;
                $query->where('start_datetime', '<=', $to_datetime);
            }
        }

        // Tank Filter (dropdown)
        if ($request->filled('tank')) {
            $query->where('tank', $request->input('tank'));
        }

        // Tank ID Search Filter (input)
        if ($request->filled('tank_search')) {
            $query->where('tank', 'like', '%'.$request->input('tank_search').'%');
        }

        // Global search for all columns
        if ($request->has('search') && $request->search['value'] != '') {
            $search = $request->search['value'];
            $query->where(function ($q) use ($search) {
                $q->where('id', 'like', "%{$search}%")
                    ->orWhere('uuid', 'like', "%{$search}%")
                    ->orWhere('pts_id', 'like', "%{$search}%")
                    ->orWhere('pts_delivery_id', 'like', "%{$search}%")
                    ->orWhere('tank', 'like', "%{$search}%")
                    ->orWhere('fuel_grade_name', 'like', "%{$search}%");
            });
        }

        $totalData = \App\Models\TankDelivery::count();
        $totalFiltered = $query->count();

        // Ordering and Pagination
        $data = $query->orderBy($order, $orderDir)
            ->offset($start)
            ->limit($length)
            ->get();

        $data = $data->map(function ($row) {
            $row = $row->toArray();

            // Format timestamps
            $row['start_datetime'] = $row['start_datetime'] ? \Carbon\Carbon::parse($row['start_datetime'])->format('Y-m-d H:i:s') : '-';
            $row['end_datetime'] = $row['end_datetime'] ? \Carbon\Carbon::parse($row['end_datetime'])->format('Y-m-d H:i:s') : '-';
            $row['synced_at'] = $row['synced_at'] ? \Carbon\Carbon::parse($row['synced_at'])->format('Y-m-d H:i:s') : '-';
            $row['created_at_bos'] = $row['created_at_bos'] ? \Carbon\Carbon::parse($row['created_at_bos'])->format('Y-m-d H:i:s') : '-';
            $row['updated_at_bos'] = $row['updated_at_bos'] ? \Carbon\Carbon::parse($row['updated_at_bos'])->format('Y-m-d H:i:s') : '-';
            $row['created_at'] = \Carbon\Carbon::parse($row['created_at'])->format('Y-m-d H:i:s');
            $row['updated_at'] = \Carbon\Carbon::parse($row['updated_at'])->format('Y-m-d H:i:s');

            // Format decimal values
            $row['start_product_height'] = $row['start_product_height'] ? number_format($row['start_product_height'], 3) : '-';
            $row['start_water_height'] = $row['start_water_height'] ? number_format($row['start_water_height'], 3) : '-';
            $row['start_temperature'] = $row['start_temperature'] ? number_format($row['start_temperature'], 2) : '-';
            $row['start_product_volume'] = $row['start_product_volume'] ? number_format($row['start_product_volume'], 3) : '-';
            $row['start_product_tc_volume'] = $row['start_product_tc_volume'] ? number_format($row['start_product_tc_volume'], 3) : '-';
            $row['start_product_density'] = $row['start_product_density'] ? number_format($row['start_product_density'], 3) : '-';
            $row['start_product_mass'] = $row['start_product_mass'] ? number_format($row['start_product_mass'], 3) : '-';

            $row['end_product_height'] = $row['end_product_height'] ? number_format($row['end_product_height'], 3) : '-';
            $row['end_water_height'] = $row['end_water_height'] ? number_format($row['end_water_height'], 3) : '-';
            $row['end_temperature'] = $row['end_temperature'] ? number_format($row['end_temperature'], 2) : '-';
            $row['end_product_volume'] = $row['end_product_volume'] ? number_format($row['end_product_volume'], 3) : '-';
            $row['end_product_tc_volume'] = $row['end_product_tc_volume'] ? number_format($row['end_product_tc_volume'], 3) : '-';
            $row['end_product_density'] = $row['end_product_density'] ? number_format($row['end_product_density'], 3) : '-';
            $row['end_product_mass'] = $row['end_product_mass'] ? number_format($row['end_product_mass'], 3) : '-';

            $row['received_product_volume'] = $row['received_product_volume'] ? number_format($row['received_product_volume'], 3) : '-';
            $row['absolute_product_height'] = $row['absolute_product_height'] ? number_format($row['absolute_product_height'], 3) : '-';
            $row['absolute_water_height'] = $row['absolute_water_height'] ? number_format($row['absolute_water_height'], 3) : '-';
            $row['absolute_temperature'] = $row['absolute_temperature'] ? number_format($row['absolute_temperature'], 2) : '-';
            $row['absolute_product_volume'] = $row['absolute_product_volume'] ? number_format($row['absolute_product_volume'], 3) : '-';
            $row['absolute_product_tc_volume'] = $row['absolute_product_tc_volume'] ? number_format($row['absolute_product_tc_volume'], 3) : '-';
            $row['absolute_product_density'] = $row['absolute_product_density'] ? number_format($row['absolute_product_density'], 3) : '-';
            $row['absolute_product_mass'] = $row['absolute_product_mass'] ? number_format($row['absolute_product_mass'], 3) : '-';
            $row['pumps_dispensed_volume'] = $row['pumps_dispensed_volume'] ? number_format($row['pumps_dispensed_volume'], 3) : '-';

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
