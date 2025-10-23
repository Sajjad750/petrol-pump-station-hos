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
        $tankFillingPercentage = fake()->randomFloat(2, 10, 100);
        $productHeight = fake()->randomFloat(3, 0.5, 15.0);
        $waterHeight = fake()->randomFloat(3, 0, 2.0);
        $temperature = fake()->randomFloat(2, 15, 35);

        return [
            'uuid' => Str::uuid(),
            'request_id' => fake()->numberBetween(1000, 9999),
            'pts_id' => fake()->regexify('[A-Z0-9]{8}'),
            'date_time' => fake()->dateTimeBetween('-1 month', 'now'),
            'tank' => fake()->numberBetween(1, 8),
            'fuel_grade_id' => fake()->numberBetween(1, 4),
            'fuel_grade_name' => fake()->randomElement(['Regular', 'Premium', 'Diesel', 'E10']),
            'status' => fake()->randomElement(['active', 'inactive', 'maintenance']),
            'alarms' => fake()->optional(0.3)->randomElement(['High Water', 'Low Level', 'Temperature Alert']),
            'product_height' => $productHeight,
            'water_height' => $waterHeight,
            'temperature' => $temperature,
            'product_volume' => fake()->randomFloat(3, 100, 50000),
            'water_volume' => fake()->randomFloat(3, 0, 1000),
            'product_ullage' => fake()->randomFloat(3, 0, 5000),
            'product_tc_volume' => fake()->randomFloat(3, 100, 50000),
            'product_density' => fake()->randomFloat(3, 0.7, 0.9),
            'product_mass' => fake()->randomFloat(3, 100, 50000),
            'tank_filling_percentage' => $tankFillingPercentage,
            'configuration_id' => fake()->numberBetween(1, 10),
            'station_id' => Station::factory(),
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
}
