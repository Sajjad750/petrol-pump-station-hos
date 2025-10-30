<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Exports\FuelGradeExport;
use Maatwebsite\Excel\Facades\Excel;
use Barryvdh\DomPDF\Facade\Pdf;
use App\Models\FuelGrade;

class FuelGradeListController extends Controller
{
    /**
     * Handle the incoming request.
     */
    public function __invoke(Request $request)
    {
        if (request()->ajax()) {
            return response()->json($this->showData(request()));
        }

        return view('fuel_grades.index');
    }

    public function exportExcel(Request $request): \Symfony\Component\HttpFoundation\BinaryFileResponse
    {
        $filters = $request->only(['fuel_grade_name', 'min_price', 'max_price']);

        return Excel::download(new FuelGradeExport($filters), 'fuel_grades_' . now()->format('Y-m-d_His') . '.xlsx');
    }

    public function exportPdf(Request $request): \Illuminate\Http\Response
    {
        $filters = $request->only(['fuel_grade_name', 'min_price', 'max_price']);
        $query = FuelGrade::query();

        if (!empty($filters['fuel_grade_name'])) {
            $query->where('name', 'like', '%' . $filters['fuel_grade_name'] . '%');
        }

        if (!empty($filters['min_price'])) {
            $query->where('price', '>=', $filters['min_price']);
        }

        if (!empty($filters['max_price'])) {
            $query->where('price', '<=', $filters['max_price']);
        }
        $grades = $query->orderBy('created_at', 'desc')->get();
        $pdf = Pdf::loadView('reports.generic_pdf', [
            'title' => 'Fuel Grades Report',
            'filters' => $filters,
            'summary' => ['Total Records' => count($grades), 'Avg Price' => '₹' . number_format($grades->avg('price'), 2)],
            'headers' => ['ID', 'Code', 'Name', 'Price', 'Price Status', 'Is Blend'],
            'data' => $grades->map(fn ($g) => [$g->id, $g->code ?? 'N/A', $g->name, '₹' . number_format($g->price, 2), ucfirst($g->price_status ?? 'active'), $g->is_blend ? 'Yes' : 'No']),
        ]);

        return $pdf->download('fuel_grades_' . now()->format('Y-m-d_His') . '.pdf');
    }

    protected function showData($request)
    {
        $columns = [
            'id',
            'uuid',
            'pts_fuel_grade_id',
            'name',
            'price',
            'scheduled_price',
            'scheduled_at',
            'expansion_coefficient',
            'blend_tank1_id',
            'blend_tank1_percentage',
            'blend_tank2_id',
            'station_id',
            'bos_fuel_grade_id',
            'bos_uuid',
            'synced_at',
            'created_at_bos',
            'updated_at_bos',
            'created_at',
            'updated_at',
            'options',
        ];

        $length = $request->input('length');
        $start = $request->input('start');
        $order = $columns[$request->input('order.0.column')];
        $orderDir = $request->input('order.0.dir');

        $query = \App\Models\FuelGrade::with('station');

        // Name Filter
        if ($request->filled('name')) {
            $query->where('name', 'like', '%'.$request->input('name').'%');
        }

        // Price Range Filters
        if ($request->filled('min_price')) {
            $query->where('price', '>=', $request->input('min_price'));
        }

        if ($request->filled('max_price')) {
            $query->where('price', '<=', $request->input('max_price'));
        }

        // Global search for all columns
        if ($request->has('search') && $request->search['value'] != '') {
            $search = $request->search['value'];
            $query->where(function ($q) use ($search) {
                $q->where('id', 'like', "%{$search}%")
                    ->orWhere('name', 'like', "%{$search}%")
                    ->orWhere('pts_fuel_grade_id', 'like', "%{$search}%")
                    ->orWhere('price', 'like', "%{$search}%");
            });
        }

        $totalData = \App\Models\FuelGrade::count();
        $totalFiltered = $query->count();

        // Ordering and Pagination
        $data = $query->orderBy($order, $orderDir)
            ->offset($start)
            ->limit($length)
            ->get();

        $data = $data->map(function ($row) {
            $row = $row->toArray();

            // Store raw price value before formatting
            $raw_price = $row['price'] ?? 0;

            // Format timestamps
            $row['scheduled_at'] = $row['scheduled_at'] ? \Carbon\Carbon::parse($row['scheduled_at'])->format('Y-m-d H:i:s') : '-';
            $row['synced_at'] = $row['synced_at'] ? \Carbon\Carbon::parse($row['synced_at'])->format('Y-m-d H:i:s') : '-';
            $row['created_at_bos'] = $row['created_at_bos'] ? \Carbon\Carbon::parse($row['created_at_bos'])->format('Y-m-d H:i:s') : '-';
            $row['updated_at_bos'] = $row['updated_at_bos'] ? \Carbon\Carbon::parse($row['updated_at_bos'])->format('Y-m-d H:i:s') : '-';
            $row['created_at'] = \Carbon\Carbon::parse($row['created_at'])->format('Y-m-d H:i:s');
            $row['updated_at'] = \Carbon\Carbon::parse($row['updated_at'])->format('Y-m-d H:i:s');

            // Format decimal values
            $row['price'] = $row['price'] ? number_format($row['price'], 2) : '0.00';
            $row['scheduled_price'] = $row['scheduled_price'] ? number_format($row['scheduled_price'], 3) : '-';
            $row['expansion_coefficient'] = $row['expansion_coefficient'] ? number_format($row['expansion_coefficient'], 5) : '-';

            // Add blend status badge
            $is_blended = $row['blend_tank1_id'] && $row['blend_tank1_id'] > 0;
            $row['blend_status'] = $is_blended
                ? '<span class="badge badge-info">Blended</span>'
                : '<span class="badge badge-secondary">Non-Blended</span>';

            // Format blend info
            if ($is_blended) {
                $blend_info = 'Tank ' . $row['blend_tank1_id'] . ' (' . $row['blend_tank1_percentage'] . '%)';

                if ($row['blend_tank2_id']) {
                    $tank2_percentage = 100 - $row['blend_tank1_percentage'];
                    $blend_info .= ' + Tank ' . $row['blend_tank2_id'] . ' (' . $tank2_percentage . '%)';
                }
                $row['blend_info'] = $blend_info;
            } else {
                $row['blend_info'] = '-';
            }

            // Add price change status badge
            if ($row['scheduled_price'] !== '-' && $row['scheduled_at'] !== '-') {
                $scheduled_at_date = \Carbon\Carbon::parse($row['scheduled_at']);

                if ($scheduled_at_date > now()) {
                    $row['price_status'] = '<span class="badge badge-warning">Price Change Pending</span>';
                } else {
                    $row['price_status'] = '<span class="badge badge-success">Price Change Active</span>';
                }
            } else {
                $row['price_status'] = '<span class="badge badge-secondary">No Change Scheduled</span>';
            }

            // Add action buttons (use raw price for data attributes)
            $row['options'] = '
                <button class="btn btn-sm btn-primary update-price-btn" data-id="' . $row['id'] . '" data-price="' . $raw_price . '" data-name="' . htmlspecialchars($row['name']) . '">
                    <i class="fas fa-edit"></i> Update Price
                </button>
                <button class="btn btn-sm btn-warning schedule-price-btn" data-id="' . $row['id'] . '" data-price="' . $raw_price . '" data-name="' . htmlspecialchars($row['name']) . '">
                    <i class="fas fa-calendar"></i> Schedule Price
                </button>
            ';

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
