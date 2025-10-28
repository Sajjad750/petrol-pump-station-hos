<?php

namespace Database\Factories;

use App\Models\Station;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\TankMeasurement>
 */
class TankMeasurementFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $tankFillingPercentage = fake()->randomFloat(2, 5, 100);
        $productHeight = fake()->randomFloat(3, 0.5, 15.0);
        $waterHeight = fake()->randomFloat(3, 0, 2.0);
        $temperature = fake()->randomFloat(2, 15, 35);
        
        // Calculate realistic volumes based on tank capacity (assuming 5000L max capacity)
        $maxCapacity = 5000;
        $productVolume = ($tankFillingPercentage / 100) * $maxCapacity;
        $waterVolume = $waterHeight * 100; // Rough calculation
        $productUllage = $maxCapacity - $productVolume;
        
        // Determine alarms based on tank level
        $alarms = null;
        if ($tankFillingPercentage < 15) {
            $alarms = ['CriticalLowProduct'];
        } elseif ($tankFillingPercentage < 30) {
            $alarms = ['LowProduct'];
        } elseif ($waterHeight > 1.5) {
            $alarms = ['HighWater'];
        } elseif ($temperature > 30) {
            $alarms = ['TemperatureAlert'];
        }

        return [
            'uuid' => Str::uuid(),
            'request_id' => fake()->numberBetween(1000, 9999),
            'pts_id' => fake()->regexify('[A-Z0-9]{8}'),
            'date_time' => fake()->dateTimeBetween('-1 month', 'now'),
            'tank' => fake()->numberBetween(1, 8),
            'fuel_grade_id' => fake()->numberBetween(1, 4),
            'fuel_grade_name' => fake()->randomElement(['Petrol 95', 'Petrol 92', 'Diesel', 'E10']),
            'status' => fake()->randomElement(['OK', 'Maintenance', 'Offline']),
            'alarms' => $alarms,
            'product_height' => $productHeight,
            'water_height' => $waterHeight,
            'temperature' => $temperature,
            'product_volume' => round($productVolume, 2),
            'water_volume' => round($waterVolume, 2),
            'product_ullage' => round($productUllage, 2),
            'product_tc_volume' => round($productVolume * 1.001, 2), // Temperature corrected
            'product_density' => fake()->randomFloat(3, 720, 850),
            'product_mass' => round($productVolume * fake()->randomFloat(3, 0.72, 0.85), 2),
            'tank_filling_percentage' => $tankFillingPercentage,
            'configuration_id' => fake()->numberBetween(1, 10),
            'station_id' => Station::inRandomOrder()->first()?->id ?? 1,
            'bos_tank_measurement_id' => fake()->numberBetween(10000, 99999),
            'bos_uuid' => Str::uuid(),
            'synced_at' => fake()->dateTimeBetween('-1 week', 'now'),
            'created_at_bos' => fake()->dateTimeBetween('-1 month', 'now'),
            'updated_at_bos' => fake()->dateTimeBetween('-1 week', 'now'),
        ];
    }

    /**
     * Indicate that the tank measurement has alarms.
     */
    public function withAlarms(): static
    {
        return $this->state(fn (array $attributes) => [
            'alarms' => fake()->randomElement(['High Water', 'Low Level', 'Temperature Alert', 'Overfill']),
        ]);
    }

    /**
     * Indicate that the tank is critically low.
     */
    public function criticallyLow(): static
    {
        return $this->state(fn (array $attributes) => [
            'tank_filling_percentage' => fake()->randomFloat(2, 5, 20),
        ]);
    }

    /**
     * Indicate that the tank is near capacity.
     */
    public function nearCapacity(): static
    {
        return $this->state(fn (array $attributes) => [
            'tank_filling_percentage' => fake()->randomFloat(2, 90, 100),
        ]);
    }

    /**
     * Indicate that the tank measurement is recent.
     */
    public function recent(): static
    {
        return $this->state(fn (array $attributes) => [
            'date_time' => fake()->dateTimeBetween('-1 hour', 'now'),
        ]);
    }

    /**
     * Indicate that the tank has high water content.
     */
    public function highWater(): static
    {
        return $this->state(fn (array $attributes) => [
            'water_height' => fake()->randomFloat(3, 1.5, 3.0),
            'water_volume' => fake()->randomFloat(3, 150, 300),
            'alarms' => ['HighWater'],
        ]);
    }

    /**
     * Indicate that the tank has temperature issues.
     */
    public function temperatureAlert(): static
    {
        return $this->state(fn (array $attributes) => [
            'temperature' => fake()->randomFloat(2, 30, 40),
            'alarms' => ['TemperatureAlert'],
        ]);
    }

    /**
     * Indicate that the tank is in maintenance mode.
     */
    public function maintenance(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'Maintenance',
            'tank_filling_percentage' => 0,
            'product_volume' => 0,
            'product_height' => 0,
        ]);
    }

    /**
     * Indicate that the tank is offline.
     */
    public function offline(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'Offline',
            'alarms' => ['TankOffline'],
        ]);
    }

    /**
     * Generate realistic tank measurement for a specific fuel grade.
     */
    public function forFuelGrade(string $fuelGrade): static
    {
        $densityRanges = [
            'Petrol 95' => [740, 760],
            'Petrol 92' => [730, 750],
            'Diesel' => [820, 850],
            'E10' => [720, 740],
        ];

        $densityRange = $densityRanges[$fuelGrade] ?? [720, 850];
        
        return $this->state(fn (array $attributes) => [
            'fuel_grade_name' => $fuelGrade,
            'product_density' => fake()->randomFloat(3, $densityRange[0], $densityRange[1]),
        ]);
    }
}
