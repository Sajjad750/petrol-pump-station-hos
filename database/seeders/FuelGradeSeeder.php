<?php

namespace Database\Seeders;

use App\Models\FuelGrade;
use App\Models\Station;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class FuelGradeSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Get all stations to create fuel grades for each
        $stations = Station::all();

        if ($stations->isEmpty()) {
            $this->command->warn('No stations found. Please run StationSeeder first.');

            return;
        }

        $fuelGrades = [
            [
                'name' => 'Gasoline 95',
                'price' => 1.85,
                'pts_fuel_grade_id' => '1',
            ],
            [
                'name' => 'Gasoline 91',
                'price' => 1.75,
                'pts_fuel_grade_id' => '2',
            ],
            [
                'name' => 'Diesel',
                'price' => 1.65,
                'pts_fuel_grade_id' => '3',
            ],
            [
                'name' => 'Petrol',
                'price' => 1.70,
                'pts_fuel_grade_id' => '4',
            ],
        ];

        foreach ($stations as $station) {
            foreach ($fuelGrades as $fuelGradeData) {
                FuelGrade::create([
                    'uuid' => Str::uuid(),
                    'pts_fuel_grade_id' => $fuelGradeData['pts_fuel_grade_id'],
                    'name' => $fuelGradeData['name'],
                    'price' => $fuelGradeData['price'],
                    'station_id' => $station->id,
                    'bos_fuel_grade_id' => rand(1000, 9999),
                    'bos_uuid' => Str::uuid(),
                    'synced_at' => now(),
                    'created_at_bos' => now()->subDays(rand(1, 30)),
                    'updated_at_bos' => now()->subDays(rand(1, 5)),
                ]);
            }
        }

        $this->command->info('Fuel grades seeded successfully for ' . $stations->count() . ' stations.');
    }
}
