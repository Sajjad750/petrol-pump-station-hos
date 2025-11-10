<?php

namespace App\Console\Commands;

use App\Models\Station;
use Illuminate\Console\Command;

class UpdateStationConnectivityStatus extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'stations:update-connectivity-status';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Update connectivity status for all stations based on last sync time';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $this->info('Updating connectivity status for all stations...');

        $stations = Station::all();
        $updated = 0;

        foreach ($stations as $station) {
            $oldStatus = $station->connectivity_status;
            $station->updateConnectivityStatus();

            if ($oldStatus !== $station->connectivity_status) {
                $updated++;
                $this->line("Station {$station->pts_id} ({$station->site_name}): {$oldStatus} → {$station->connectivity_status}");
            }
        }

        $this->info("Updated {$updated} station(s) out of {$stations->count()} total.");

        return Command::SUCCESS;
    }
}
