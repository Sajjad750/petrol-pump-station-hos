<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Alert;
use Carbon\Carbon;

class AlertController extends Controller
{
    public function index(Request $request)
    {
        // Only Pump and Probe alerts
        $baseQuery = Alert::with('station')->whereIn('device_type', ['Pump', 'Probe']);
        $tab = $request->get('tab', 'unread');
        // Tabs support: unread, all, bos, hos, controller (future: use device_type, etc)
        $alerts = match($tab) {
            'all' => (clone $baseQuery)->latest('datetime')->get(),
            default => (clone $baseQuery)->where('is_read', false)->latest('datetime')->get(),
        };
        // Counts
        $totalToday = (clone $baseQuery)->whereDate('datetime', Carbon::today())->count();
        $unread = (clone $baseQuery)->where('is_read', false)->count();
        // Very basic crit/warn logic, adjust as needed
        $criticalCodes = [3, 6, 8]; // Code 3,6,8 for Probe are 'critical' in API spec
        $critical = (clone $baseQuery)->whereIn('code', $criticalCodes)->count();
        $warningCodes = [1, 2, 5, 7];
        $warning = (clone $baseQuery)->whereIn('code', $warningCodes)->count();

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
