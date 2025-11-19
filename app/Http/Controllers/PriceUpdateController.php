<?php

namespace App\Http\Controllers;

use App\Models\FuelGrade;
use App\Models\FuelGradePriceHistory;
use Illuminate\Contracts\View\View;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class PriceUpdateController extends Controller
{
    /**
     * Show the Price Update page.
     */
    public function index(Request $request): View
    {
        // Get distinct fuel grade names grouped by name
        $fuel_grades = FuelGrade::query()
            ->select('name')
            ->distinct()
            ->orderBy('name')
            ->pluck('name');

        // Recent price change history from fuel_grade_price_history table
        $history = FuelGradePriceHistory::query()
            ->with('fuelGrade')
            ->latest()
            ->limit(20)
            ->get()
            ->map(function (FuelGradePriceHistory $historyItem) {
                return [
                    'id' => $historyItem->id,
                    'product_name' => $historyItem->fuelGrade->name ?? '',
                    'effective_at' => $historyItem->effective_at,
                    'created_at' => $historyItem->created_at,
                    'price_from' => $historyItem->old_price,
                    'price_to' => $historyItem->new_price,
                    'change_type' => $historyItem->change_type,
                    'changed_by_user_name' => $historyItem->changed_by_user_name,
                    'changed_by' => $historyItem->changed_by,
                    'status' => $historyItem->status,
                    'source_system' => $historyItem->source_system,
                ];
            });

        return view('price_updates.index', [
            'fuel_grades' => $fuel_grades,
            'history' => $history,
        ]);
    }

    /**
     * Return products (fuel grades) for a given station.
     */
    public function products(Request $request): JsonResponse
    {
        $station_id = (int) $request->get('station_id');

        if ($station_id <= 0) {
            return response()->json(['products' => []]);
        }

        $products = FuelGrade::query()
            ->where('station_id', $station_id)
            ->orderBy('name')
            ->get(['id', 'name']);

        return response()->json(['products' => $products]);
    }

    /**
     * Handle DataTable server-side request for price updates.
     */
    public function dataTable(Request $request): JsonResponse
    {
        // Note: Index 0 is checkbox (not orderable), so actual data columns start at index 1
        $columns = [
            0 => 'checkbox', // Not orderable
            1 => 'id',
            2 => 'station.site_name',
            3 => 'bos_fuel_grade_id',
            4 => 'name',
            5 => 'price',
            6 => 'scheduled_price',
            7 => 'scheduled_at',
            8 => 'status', // Not orderable
        ];

        $length = $request->input('length', 10);
        $start = $request->input('start', 0);
        $orderColumnIndex = (int) $request->input('order.0.column', 1);
        $orderColumn = $columns[$orderColumnIndex] ?? 'id';
        $orderDir = $request->input('order.0.dir', 'desc');

        $query = FuelGrade::query()->with('station');

        // Filter by fuel grade name if provided
        if ($request->filled('name')) {
            $query->where('name', $request->input('name'));
        }

        // Global search
        if ($request->has('search') && $request->search['value'] != '') {
            $search = $request->search['value'];
            $query->where(function ($q) use ($search) {
                $q->where('id', 'like', "%{$search}%")
                    ->orWhere('name', 'like', "%{$search}%")
                    ->orWhere('bos_fuel_grade_id', 'like', "%{$search}%")
                    ->orWhere('price', 'like', "%{$search}%")
                    ->orWhereHas('station', function ($stationQuery) use ($search) {
                        $stationQuery->where('site_name', 'like', "%{$search}%");
                    });
            });
        }

        $totalData = FuelGrade::count();
        $totalFiltered = $query->count();

        // Handle ordering (skip checkbox and status columns as they're not orderable)
        if ($orderColumn === 'checkbox' || $orderColumn === 'status') {
            $query->orderBy('id', $orderDir);
        } elseif ($orderColumn === 'station.site_name') {
            $query->join('stations', 'fuel_grades.station_id', '=', 'stations.id')
                ->orderBy('stations.site_name', $orderDir)
                ->select('fuel_grades.*');
        } else {
            $query->orderBy($orderColumn, $orderDir);
        }

        // Pagination
        $data = $query->offset($start)
            ->limit($length)
            ->get();

        $data = $data->map(function ($fuelGrade) {
            $row = $fuelGrade->toArray();

            // Format price
            $row['price'] = $fuelGrade->price ? number_format((float) $fuelGrade->price, 2) : '0.00';

            // Format scheduled_price
            $row['scheduled_price'] = $fuelGrade->scheduled_price ? number_format((float) $fuelGrade->scheduled_price, 3) : '-';

            // Format scheduled_at
            $row['scheduled_at'] = $fuelGrade->scheduled_at ? $fuelGrade->scheduled_at->format('Y-m-d H:i:s') : '-';

            // Calculate status: if scheduled_at and scheduled_price are not null, show "scheduled", otherwise "active"
            if ($fuelGrade->scheduled_at !== null && $fuelGrade->scheduled_price !== null) {
                $row['status'] = '<span class="badge badge-warning">Scheduled</span>';
            } else {
                $row['status'] = '<span class="badge badge-success">Active</span>';
            }

            // Add station name
            $row['station_name'] = $fuelGrade->station->site_name ?? '-';

            // Add checkbox HTML (will be shown conditionally in frontend)
            $row['checkbox'] = '<input type="checkbox" class="fuel-grade-checkbox" value="' . $fuelGrade->id . '" data-name="' . htmlspecialchars($fuelGrade->name) . '">';

            return $row;
        });

        return response()->json([
            'draw' => intval($request->input('draw')),
            'recordsTotal' => $totalData,
            'recordsFiltered' => $totalFiltered,
            'data' => $data,
        ]);
    }
}
