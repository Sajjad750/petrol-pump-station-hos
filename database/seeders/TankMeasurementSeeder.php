<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\TankMeasurement;
use App\Models\Station;

class TankMeasurementSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $station = Station::first();

        if (!$station) {
            $this->command->info('No station found. Please create a station first.');

            return;
        }

        // Clear existing tank measurements for this station
        TankMeasurement::where('station_id', $station->id)->delete();

        // Add sample tank measurements with different stock levels
        $tankMeasurements = [
            // Critical Low Stock (15% - CriticalLowProduct alarm)
            [
                'uuid' => \Illuminate\Support\Str::uuid(),
                'bos_tank_measurement_id' => rand(1000, 9999),
                'station_id' => $station->id,
                'pts_id' => $station->pts_id,
                'date_time' => now(),
                'tank' => 1,
                'fuel_grade_id' => 1,
                'fuel_grade_name' => 'Petrol 95',
                'status' => 'OK',
                'product_volume' => 500,
                'tank_filling_percentage' => 15,
                'alarms' => ['CriticalLowProduct'],
                'product_height' => 500,
                'temperature' => 22.5,
                'product_density' => 750.5,
            ],
            // Low Stock (35% - LowProduct alarm)
            [
                'uuid' => \Illuminate\Support\Str::uuid(),
                'bos_tank_measurement_id' => rand(1000, 9999),
                'station_id' => $station->id,
                'pts_id' => $station->pts_id,
                'date_time' => now(),
                'tank' => 2,
                'fuel_grade_id' => 2,
                'fuel_grade_name' => 'Diesel',
                'status' => 'OK',
                'product_volume' => 1200,
                'tank_filling_percentage' => 35,
                'alarms' => ['LowProduct'],
                'product_height' => 1200,
                'temperature' => 21.8,
                'product_density' => 820.3,
            ],
            // Normal Stock (75% - No alarms)
            [
                'uuid' => \Illuminate\Support\Str::uuid(),
                'bos_tank_measurement_id' => rand(1000, 9999),
                'station_id' => $station->id,
                'pts_id' => $station->pts_id,
                'date_time' => now(),
                'tank' => 3,
                'fuel_grade_id' => 1,
                'fuel_grade_name' => 'Petrol 95',
                'status' => 'OK',
                'product_volume' => 3000,
                'tank_filling_percentage' => 75,
                'alarms' => null,
                'product_height' => 3000,
                'temperature' => 23.1,
                'product_density' => 748.9,
            ],
            // High Stock (85% - No alarms)
            [
                'uuid' => \Illuminate\Support\Str::uuid(),
                'bos_tank_measurement_id' => rand(1000, 9999),
                'station_id' => $station->id,
                'pts_id' => $station->pts_id,
                'date_time' => now(),
                'tank' => 4,
                'fuel_grade_id' => 3,
                'fuel_grade_name' => 'Petrol 92',
                'status' => 'OK',
                'product_volume' => 4000,
                'tank_filling_percentage' => 85,
                'alarms' => null,
                'product_height' => 4000,
                'temperature' => 22.7,
                'product_density' => 752.1,
            ],
            // Another Critical Low Stock (10% - CriticalLowProduct alarm)
            [
                'uuid' => \Illuminate\Support\Str::uuid(),
                'bos_tank_measurement_id' => rand(1000, 9999),
                'station_id' => $station->id,
                'pts_id' => $station->pts_id,
                'date_time' => now(),
                'tank' => 5,
                'fuel_grade_id' => 2,
                'fuel_grade_name' => 'Diesel',
                'status' => 'OK',
                'product_volume' => 300,
                'tank_filling_percentage' => 10,
                'alarms' => ['CriticalLowProduct'],
                'product_height' => 300,
                'temperature' => 20.9,
                'product_density' => 825.7,
            ],
            // Another Low Stock (40% - LowProduct alarm)
            [
                'uuid' => \Illuminate\Support\Str::uuid(),
                'bos_tank_measurement_id' => rand(1000, 9999),
                'station_id' => $station->id,
                'pts_id' => $station->pts_id,
                'date_time' => now(),
                'tank' => 6,
                'fuel_grade_id' => 1,
                'fuel_grade_name' => 'Petrol 95',
                'status' => 'OK',
                'product_volume' => 1400,
                'tank_filling_percentage' => 40,
                'alarms' => ['LowProduct'],
                'product_height' => 1400,
                'temperature' => 24.2,
                'product_density' => 746.8,
            ],
        ];

        foreach ($tankMeasurements as $measurement) {
            TankMeasurement::create($measurement);
        }

        $this->command->info('Added ' . count($tankMeasurements) . ' tank measurements with various stock levels.');
    }
}
