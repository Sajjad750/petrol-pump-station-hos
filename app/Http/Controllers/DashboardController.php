<?php

namespace App\Http\Controllers;

use App\Models\Station;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    /**
     * Display the dashboard with stations data
     */
    public function index()
    {
        $stations = Station::with(['pumpTransactions', 'pumps', 'tankMeasurements'])
            ->orderBy('site_name')
            ->get();

        // Calculate dashboard statistics
        $totalStations = $stations->count();
        $onlineStations = $stations->filter(function ($station) {
            return $station->isOnline();
        })->count();
        $warningStations = $stations->filter(function ($station) {
            return $station->hasWarning();
        })->count();
        $offlineStations = $stations->filter(function ($station) {
            return $station->isOffline();
        })->count();

        return view('dashboard', compact(
            'stations',
            'totalStations',
            'onlineStations',
            'warningStations',
            'offlineStations'
        ));
    }

    /**
     * Get station details for popup
     */
    public function getStationDetails(Request $request, $id)
    {
        $station = Station::with(['pumpTransactions', 'pumps', 'tankMeasurements'])
            ->findOrFail($id);

        return response()->json([
            'station' => $station,
            'status' => $this->getStationStatus($station),
            'lastSync' => $station->last_sync_at ? $station->last_sync_at->diffForHumans() : 'Never',
            'pumpCount' => $station->pumps->count(),
            'activePumps' => $station->pumps->where('is_active', true)->count(),
        ]);
    }

    /**
     * Get station status information
     */
    private function getStationStatus($station)
    {
        if ($station->isOnline()) {
            return ['status' => 'online', 'class' => 'status-online', 'text' => 'Online'];
        } elseif ($station->hasWarning()) {
            return ['status' => 'warning', 'class' => 'status-warning', 'text' => 'Warning'];
        } else {
            return ['status' => 'offline', 'class' => 'status-offline', 'text' => 'Offline'];
        }
    }
}
