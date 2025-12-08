<?php

namespace App\Http\Controllers;

use App\Http\Requests\ScheduleBulkFuelGradePriceRequest;
use App\Models\FuelGrade;
use App\Models\FuelGradePriceHistory;
use App\Models\HosCommand;
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
            ->leftJoin('fuel_grades', 'fuel_grade_price_history.fuel_grade_id', '=', 'fuel_grades.bos_fuel_grade_id')
            ->select('fuel_grade_price_history.*', 'fuel_grades.name as fuel_grade_name')
            ->latest('fuel_grade_price_history.created_at')
            ->limit(20)
            ->get()
            ->map(function ($historyItem) {
                return [
                    'id' => $historyItem->id,
                    'product_name' => $historyItem->fuel_grade_name ?? '',
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
            $row['checkbox'] = '<input type="checkbox" class="fuel-grade-checkbox" value="' . $fuelGrade->id . '" data-name="' . htmlspecialchars($fuelGrade->name) . '" data-station-id="' . $fuelGrade->station_id . '">';

            return $row;
        });

        return response()->json([
            'draw' => intval($request->input('draw')),
            'recordsTotal' => $totalData,
            'recordsFiltered' => $totalFiltered,
            'data' => $data,
        ]);
    }

    /**
     * Schedule fuel grade prices for multiple stations in bulk.
     */
    public function scheduleBulk(ScheduleBulkFuelGradePriceRequest $request): JsonResponse
    {
        $validated = $request->validated();

        $fuel_grade_name = $validated['fuel_grade_name'];
        $station_ids = $validated['station_ids'];
        $scheduled_price = $validated['scheduled_price'];
        $scheduled_at = $validated['scheduled_at'];

        // Get fuel_grade_ids which have same fuel_grade name and match the station_ids
        $fuel_grades = FuelGrade::query()
            ->where('name', $fuel_grade_name)
            ->whereIn('station_id', $station_ids)
            ->get();

        if ($fuel_grades->isEmpty()) {
            return response()->json([
                'success' => false,
                'message' => 'No fuel grades found matching the selected criteria.',
            ], 404);
        }

        $created_commands = [];
        $errors = [];

        // Create HOSCommand for each station
        foreach ($fuel_grades as $fuel_grade) {
            try {
                $hos_command = HosCommand::create([
                    'station_id' => $fuel_grade->station_id,
                    'command_type' => 'schedule_fuel_grade_price',
                    'command_data' => [
                        'bos_fuel_grade_id' => $fuel_grade->bos_fuel_grade_id,
                        'bos_uuid' => $fuel_grade->bos_uuid,
                        'scheduled_price' => $scheduled_price,
                        'scheduled_at' => $scheduled_at,
                        'source_system' => 'HOS',
                        'changed_by' => auth()->id(),
                        'changed_by_user_name' => auth()->user()->name ?? null,
                        'status' => 'pending',
                        'user_timezone' => $validated['user_timezone'] ?? null,
                    ],
                    'status' => 'pending',
                ]);

                $created_commands[] = $hos_command->id;
            } catch (\Exception $e) {
                $errors[] = "Failed to create command for station ID {$fuel_grade->station_id}: {$e->getMessage()}";
            }
        }

        if (count($created_commands) === 0) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to create any commands. ' . implode(' ', $errors),
            ], 500);
        }

        $message = count($created_commands) . ' price schedule command(s) queued successfully';

        if (count($errors) > 0) {
            $message .= '. Some errors occurred: ' . implode(' ', $errors);
        }

        return response()->json([
            'success' => true,
            'message' => $message,
            'data' => [
                'commands_created' => count($created_commands),
                'command_ids' => $created_commands,
                'errors' => $errors,
            ],
        ]);
    }
}
