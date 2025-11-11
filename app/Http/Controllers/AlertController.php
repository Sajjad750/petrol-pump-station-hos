<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Alert;
use Carbon\Carbon;

class AlertController extends Controller
{
    public function index(Request $request)
    {
        // Include Pump, Probe, and BOS alerts
        $alertsQuery = Alert::with('station');
        $tab = $request->get('tab', 'unread');
        
        // Get base query for counts before applying tab-specific filters
        $baseQuery = clone $alertsQuery;
        
        // Handle different tabs
        $alerts = match($tab) {
            'all' => $alertsQuery->latest('datetime')->get(),
            'bos' => $alertsQuery->where('device_type', 'BOS')->latest('datetime')->get(),
            'hos' => $alertsQuery->whereIn('device_type', ['Pump', 'Probe', 'BOS'])->latest('datetime')->get(),
            default => $alertsQuery->where('is_read', false)->latest('datetime')->get(),
        };
        
        // Reset alerts query for counts
        $alertsQuery = $baseQuery;
        // Counts - use base query to get accurate counts
        $totalToday = $baseQuery->clone()->whereDate('datetime', Carbon::today())->count();
        $unread = $baseQuery->clone()->where('is_read', false)->count();
        
        // Critical and warning counts
        $criticalCodes = [3, 6, 8]; // Code 3,6,8 for Probe are 'critical' in API spec
        $warningCodes = [1, 2, 5, 7];
        
        // Count critical and warning alerts
        $critical = $baseQuery->clone()->whereIn('code', $criticalCodes)->count();
        $warning = $baseQuery->clone()->whereIn('code', $warningCodes)->count();
        
        // Include BOS alerts in the total count
        $totalToday += $baseQuery->clone()->where('device_type', 'BOS')
            ->whereDate('datetime', Carbon::today())->count();
        $unread += $baseQuery->clone()->where('device_type', 'BOS')
            ->where('is_read', false)->count();

        return view('alerts.index', [
            'alerts' => $alerts,
            'totalToday' => $totalToday,
            'unread' => $unread,
            'critical' => $critical,
            'warning' => $warning,
            'tab' => $tab
        ]);
    }
}
