<?php

namespace App\Http\Controllers;

use App\Models\FuelGrade;
use App\Models\HosCommand;
use App\Models\Station;
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
        $stations = Station::query()
            ->orderBy('site_name')
            ->get(['id', 'site_name']);

        // Recent price change history from queued commands, enriched with fuel grade and prices
        $rawHistory = HosCommand::query()
            ->whereIn('command_type', ['schedule_fuel_grade_price', 'update_fuel_grade_price'])
            ->latest()
            ->limit(20)
            ->get(['id', 'station_id', 'command_type', 'command_data', 'created_at']);

        $history = $rawHistory->map(function (HosCommand $cmd) {
            $data = (array) ($cmd->command_data ?? []);
            $bosFuelGradeId = $data['bos_fuel_grade_id'] ?? null;
            $scheduledAt = $data['scheduled_at'] ?? null;
            $newPrice = $data['scheduled_price'] ?? ($data['price'] ?? null);
            $oldPrice = $data['price'] ?? null;

            $fuelGrade = null;

            if ($bosFuelGradeId) {
                $fuelGrade = FuelGrade::query()
                    ->where('station_id', $cmd->station_id)
                    ->where('bos_fuel_grade_id', $bosFuelGradeId)
                    ->first(['name']);
            }

            return [
                'id' => $cmd->id,
                'type' => $cmd->command_type,
                'product_name' => $fuelGrade->name ?? 'Unknown Product',
                'scheduled_at' => $scheduledAt,
                'price_from' => $oldPrice,
                'price_to' => $newPrice,
                'created_at' => $cmd->created_at,
            ];
        });

        return view('price_updates.index', [
            'stations' => $stations,
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
}
