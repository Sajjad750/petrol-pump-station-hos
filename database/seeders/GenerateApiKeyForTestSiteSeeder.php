<?php

namespace Database\Seeders;

use App\Models\Station;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Artisan;

class GenerateApiKeyForTestSiteSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        Artisan::call('hos:generate-api-key', [
            'pts_id' => '0030003E3033511437393538',
            '--site-name' => 'Test Site',
            '--show-key' => true,
        ]);

        $station = Station::wherePtsId('0030003E3033511437393538')->first();
        $this->command->line("API Key: {$station->api_key}");
    }
}
