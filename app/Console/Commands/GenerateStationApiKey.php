<?php

namespace App\Console\Commands;

use App\Models\Station;
use Illuminate\Console\Command;
use Illuminate\Support\Str;

class GenerateStationApiKey extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'hos:generate-api-key {pts_id} {--site-name=} {--show-key}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Generate an API key for a station';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $ptsId = $this->argument('pts_id');
        $siteName = $this->option('site-name') ?: "Station {$ptsId}";
        $showKey = $this->option('show-key');

        // Check if station already exists
        $station = Station::findByPtsId($ptsId);

        if ($station) {
            $this->info("Station with PTS ID '{$ptsId}' already exists.");

            if ($showKey) {
                $this->line("Current API Key: {$station->api_key}");
            } else {
                $this->line("Use --show-key to display the current API key.");
            }

            return 0;
        }

        // Generate a secure API key
        $apiKey = 'hos_' . Str::random(32);

        // Create the station
        $station = Station::create([
            'pts_id' => $ptsId,
            'site_name' => $siteName,
            'is_active' => true,
            'api_key' => $apiKey,
            'connectivity_status' => 'unknown',
        ]);

        $this->info("Station created successfully!");
        $this->line("PTS ID: {$station->pts_id}");
        $this->line("Site Name: {$station->site_name}");
        $this->line("Station ID: {$station->id}");
        $this->line("API Key: {$apiKey}");

        $this->newLine();
        $this->warn("IMPORTANT: Save this API key securely. It will be used to authenticate BOS sync requests.");
        $this->line("Configure this API key in your BOS .env file:");
        $this->line("HOS_API_KEY={$apiKey}");

        return 0;
    }
}
