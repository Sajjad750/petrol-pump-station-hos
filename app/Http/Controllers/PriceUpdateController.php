<?php

namespace App\Http\Controllers;

use App\Models\FuelGrade;
use App\Models\FuelGradePriceHistory;
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
