<?php

namespace Database\Seeders;

use App\Models\FuelGrade;
use App\Models\Pump;
use App\Models\PumpTransaction;
use App\Models\PtsUser;
use App\Models\Station;
use App\Models\TankDelivery;
use App\Models\TankInventory;
use App\Models\TankMeasurement;
use Illuminate\Database\Seeder;

class ReportsDataSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $this->command->info('🚀 Starting Reports Data Seeding...');

        // Clear existing data
        $this->command->info('🗑️  Clearing existing data...');
        \DB::table('pump_transactions')->delete();
        \DB::table('tank_deliveries')->delete();
        \DB::table('tank_inventories')->delete();
        \DB::table('tank_measurements')->delete();
        \DB::table('pumps')->delete();
        \DB::table('pts_users')->delete();
        \DB::table('fuel_grades')->delete();
        \DB::table('stations')->delete();
        $this->command->info('✅ Cleared existing data');

        // Create Stations (5 stations with different statuses)
        $this->command->info('📍 Creating Stations...');
        $stations = collect([
            Station::factory()->online()->create([
                'site_name' => 'Riyadh Central Station',
                'city' => 'Riyadh',
                'region' => 'Central',
            ]),
            Station::factory()->online()->create([
                'site_name' => 'Jeddah Corniche Station',
                'city' => 'Jeddah',
                'region' => 'Western',
            ]),
            Station::factory()->warning()->create([
                'site_name' => 'Dammam Highway Station',
                'city' => 'Dammam',
                'region' => 'Eastern',
            ]),
            Station::factory()->online()->create([
                'site_name' => 'Mecca Gate Station',
                'city' => 'Mecca',
                'region' => 'Western',
            ]),
            Station::factory()->offline()->create([
                'site_name' => 'Medina North Station',
                'city' => 'Medina',
                'region' => 'Western',
            ]),
        ]);

        $this->command->info('✅ Created '.$stations->count().' stations');

        // Create Fuel Grades for each station (4 types per station)
        $this->command->info('⛽ Creating Fuel Grades...');
        $fuelGrades = collect();

        foreach ($stations as $station) {
            $fuelGrades->push(
                FuelGrade::factory()->premium95()->create([
                    'station_id' => $station->id,
                    'pts_fuel_grade_id' => 1,
                    'bos_fuel_grade_id' => 1,
                ]),
                FuelGrade::factory()->premium91()->create([
                    'station_id' => $station->id,
                    'pts_fuel_grade_id' => 2,
                    'bos_fuel_grade_id' => 2,
                ]),
                FuelGrade::factory()->diesel()->create([
                    'station_id' => $station->id,
                    'pts_fuel_grade_id' => 3,
                    'bos_fuel_grade_id' => 3,
                ]),
                FuelGrade::factory()->create([
                    'station_id' => $station->id,
                    'name' => 'Super 98',
                    'price' => 2.45,
                    'pts_fuel_grade_id' => 4,
                    'bos_fuel_grade_id' => 4,
                ])
            );
        }

        $this->command->info('✅ Created '.$fuelGrades->count().' fuel grades');

        // Create Pumps for each station (4-8 pumps per station)
        $this->command->info('⛽ Creating Pumps...');
        $totalPumps = 0;

        foreach ($stations as $station) {
            $pumpCount = fake()->numberBetween(4, 8);

            for ($i = 1; $i <= $pumpCount; $i++) {
                Pump::factory()->active()->create([
                    'station_id' => $station->id,
                    'pts_pump_id' => $i,
                    'name' => 'Pump '.$i,
                ]);
                $totalPumps++;
            }
        }

        $this->command->info('✅ Created '.$totalPumps.' pumps');

        // Create PTS Users (attendants) for each station (3-5 users per station)
        $this->command->info('👤 Creating PTS Users (Attendants)...');
        $totalPtsUsers = 0;

        foreach ($stations as $station) {
            $userCount = fake()->numberBetween(3, 5);

            for ($i = 1; $i <= $userCount; $i++) {
                PtsUser::factory()->active()->create([
                    'station_id' => $station->id,
                ]);
                $totalPtsUsers++;
            }
        }

        $this->command->info('✅ Created '.$totalPtsUsers.' PTS users');

        // Create Pump Transactions (100-200 transactions per station for the last 30 days)
        $this->command->info('💳 Creating Pump Transactions...');
        $totalTransactions = 0;

        foreach ($stations as $station) {
            $stationFuelGrades = $fuelGrades->where('station_id', $station->id);
            $stationPumps = Pump::where('station_id', $station->id)->get();
            $stationUsers = PtsUser::where('station_id', $station->id)->get();

            $transactionCount = fake()->numberBetween(100, 200);

            for ($i = 0; $i < $transactionCount; $i++) {
                $fuelGrade = $stationFuelGrades->random();
                $pump = $stationPumps->random();
                $user = $stationUsers->random();

                PumpTransaction::factory()->create([
                    'station_id' => $station->id,
                    'pts_id' => $station->pts_id,
                    'pts_fuel_grade_id' => $fuelGrade->pts_fuel_grade_id,
                    'pts_pump_id' => $pump->pts_pump_id,
                    'pts_user_id' => $user->pts_user_id,
                    'pts_nozzle_id' => fake()->numberBetween(1, 4),
                    'price' => $fuelGrade->price,
                ]);
                $totalTransactions++;
            }
        }

        $this->command->info('✅ Created '.$totalTransactions.' pump transactions');

        // Create Tank Inventories (multiple readings per tank for the last 7 days)
        $this->command->info('🛢️  Creating Tank Inventories...');
        $totalInventories = 0;

        foreach ($stations as $station) {
            $stationFuelGrades = $fuelGrades->where('station_id', $station->id);

            foreach ($stationFuelGrades as $fuelGrade) {
                // Create 10-20 inventory readings for each tank over the last 7 days
                $readingCount = fake()->numberBetween(10, 20);

                for ($i = 0; $i < $readingCount; $i++) {
                    TankInventory::factory()->create([
                        'station_id' => $station->id,
                        'fuel_grade_id' => $fuelGrade->id,
                        'fuel_grade_name' => $fuelGrade->name,
                        'tank' => $fuelGrade->pts_fuel_grade_id,
                    ]);
                    $totalInventories++;
                }
            }
        }

        $this->command->info('✅ Created '.$totalInventories.' tank inventory readings');

        // Create Tank Deliveries (2-5 deliveries per station)
        $this->command->info('🚚 Creating Tank Deliveries...');
        $totalDeliveries = 0;

        foreach ($stations as $station) {
            $stationFuelGrades = $fuelGrades->where('station_id', $station->id);
            $deliveryCount = fake()->numberBetween(2, 5);

            for ($i = 0; $i < $deliveryCount; $i++) {
                $fuelGrade = $stationFuelGrades->random();

                TankDelivery::factory()->create([
                    'station_id' => $station->id,
                    'fuel_grade_id' => $fuelGrade->id,
                    'fuel_grade_name' => $fuelGrade->name,
                    'tank' => $fuelGrade->pts_fuel_grade_id,
                ]);
                $totalDeliveries++;
            }
        }

        $this->command->info('✅ Created '.$totalDeliveries.' tank deliveries');

        // Create Tank Measurements (current readings for each tank)
        $this->command->info('📊 Creating Tank Measurements...');
        $totalMeasurements = 0;

        foreach ($stations as $station) {
            $stationFuelGrades = $fuelGrades->where('station_id', $station->id);

            foreach ($stationFuelGrades as $fuelGrade) {
                TankMeasurement::factory()->create([
                    'station_id' => $station->id,
                    'fuel_grade_id' => $fuelGrade->id,
                    'fuel_grade_name' => $fuelGrade->name,
                    'tank' => $fuelGrade->pts_fuel_grade_id,
                ]);
                $totalMeasurements++;
            }
        }

        $this->command->info('✅ Created '.$totalMeasurements.' tank measurements');

        $this->command->info('');
        $this->command->info('🎉 Reports Data Seeding Completed Successfully!');
        $this->command->info('');
        $this->command->info('Summary:');
        $this->command->info('  - Stations: '.$stations->count());
        $this->command->info('  - Fuel Grades: '.$fuelGrades->count());
        $this->command->info('  - Pumps: '.$totalPumps);
        $this->command->info('  - PTS Users: '.$totalPtsUsers);
        $this->command->info('  - Transactions: '.$totalTransactions);
        $this->command->info('  - Tank Inventories: '.$totalInventories);
        $this->command->info('  - Tank Deliveries: '.$totalDeliveries);
        $this->command->info('  - Tank Measurements: '.$totalMeasurements);
        $this->command->info('');
        $this->command->info('💡 You can now view reports at: /hos-reports');
    }
}
