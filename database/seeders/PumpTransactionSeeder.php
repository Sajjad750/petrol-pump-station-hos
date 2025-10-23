<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\PumpTransaction;
use App\Models\Station;
use App\Models\FuelGrade;

class PumpTransactionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $stations = Station::all();

        if ($stations->isEmpty()) {
            $this->command->info('No stations found. Please create stations first.');

            return;
        }

        // Clear existing pump transactions
        PumpTransaction::truncate();

        // Create transactions for each station
        foreach ($stations as $station) {
            $transactionCount = rand(5, 10); // Random number of transactions per station

            // Get fuel grades for this station
            $fuelGrades = FuelGrade::where('station_id', $station->id)->get();

            if ($fuelGrades->isEmpty()) {
                $this->command->warn('No fuel grades found for station: ' . $station->site_name);

                continue;
            }

            for ($i = 0; $i < $transactionCount; $i++) {
                // Randomly select a fuel grade
                $fuelGrade = $fuelGrades->random();

                PumpTransaction::create([
                    'uuid' => \Illuminate\Support\Str::uuid(),
                    'pts2_device_id' => 'PTS2-' . rand(1000, 9999),
                    'bos_transaction_id' => rand(1000, 9999),
                    'station_id' => $station->id,
                    'pts_id' => $station->pts_id,
                    'date_time_start' => now()->subDays(rand(0, 6))->subHours(rand(0, 23))->subMinutes(rand(0, 59)),
                    'date_time_end' => now()->subDays(rand(0, 6))->subHours(rand(0, 23))->subMinutes(rand(0, 59)),
                    'pts_pump_id' => rand(1, 4),
                    'pts_nozzle_id' => rand(1, 2),
                    'pts_fuel_grade_id' => $fuelGrade->id,
                    'volume' => rand(20, 60),
                    'amount' => rand(500, 2000), // Varying sales amounts
                    'price' => $fuelGrade->price,
                    'transaction_number' => rand(1000, 9999),
                ]);
            }

            $totalSales = PumpTransaction::where('station_id', $station->id)->sum('amount');
            $this->command->info('Station "' . $station->site_name . '": ' . $transactionCount . ' transactions, Total: $' . number_format($totalSales, 2));
        }
    }
}
