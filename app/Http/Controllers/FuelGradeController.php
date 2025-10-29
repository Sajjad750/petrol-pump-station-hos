<?php

namespace App\Http\Controllers;

use App\Http\Requests\ScheduleFuelGradePriceRequest;
use App\Http\Requests\UpdateFuelGradePriceRequest;
use App\Models\FuelGrade;
use App\Models\HosCommand;
use Illuminate\Http\JsonResponse;

class FuelGradeController extends Controller
{
    /**
     * Update fuel grade price
     */
    public function updatePrice(UpdateFuelGradePriceRequest $request, FuelGrade $fuelGrade): JsonResponse
    {
        $validated = $request->validated();

        // Update fuel grade in HOS database
        $data = [];

        if (!empty($validated['scheduled_at'])) {
            $data['scheduled_price'] = $validated['price'];
            $data['scheduled_at'] = $validated['scheduled_at'];
        } else {
            $data['price'] = $validated['price'];
        }

        $fuelGrade->update($data);

        // Determine command type
        $command_type = $validated['scheduled_at'] ? 'schedule_fuel_grade_price' : 'update_fuel_grade_price';

        // Create HosCommand
        HosCommand::create([
            'station_id' => $fuelGrade->station_id,
            'command_type' => $command_type,
            'command_data' => [
                'bos_fuel_grade_id' => $fuelGrade->bos_fuel_grade_id,
                'bos_uuid' => $fuelGrade->bos_uuid,
                'price' => $validated['price'],
                'scheduled_price' => $validated['scheduled_price'] ?? null,
                'scheduled_at' => $validated['scheduled_at'] ?? null,
            ],
            'status' => 'pending',
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Price update command queued successfully',
            'data' => [
                'fuel_grade_id' => $fuelGrade->id,
                'price' => $fuelGrade->price,
                'scheduled_price' => $fuelGrade->scheduled_price,
                'scheduled_at' => $fuelGrade->scheduled_at,
            ],
        ]);
    }

    /**
     * Schedule fuel grade price
     */
    public function schedulePrice(ScheduleFuelGradePriceRequest $request, FuelGrade $fuelGrade): JsonResponse
    {
        $validated = $request->validated();

        // Update fuel grade in HOS database
        $fuelGrade->update([
            'scheduled_price' => $validated['scheduled_price'],
            'scheduled_at' => $validated['scheduled_at'],
        ]);

        // Create HosCommand
        HosCommand::create([
            'station_id' => $fuelGrade->station_id,
            'command_type' => 'schedule_fuel_grade_price',
            'command_data' => [
                'bos_fuel_grade_id' => $fuelGrade->bos_fuel_grade_id,
                'bos_uuid' => $fuelGrade->bos_uuid,
                'scheduled_price' => $validated['scheduled_price'],
                'scheduled_at' => $validated['scheduled_at'],
            ],
            'status' => 'pending',
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Price schedule command queued successfully',
            'data' => [
                'fuel_grade_id' => $fuelGrade->id,
                'scheduled_price' => $fuelGrade->scheduled_price,
                'scheduled_at' => $fuelGrade->scheduled_at,
            ],
        ]);
    }
}
