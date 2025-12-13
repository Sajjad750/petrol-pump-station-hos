<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Alert;
use Carbon\Carbon;

class AlertController extends Controller
{
    public function index(Request $request)
    {
        $baseQuery = Alert::with('station');
        $tab = $request->get('tab', 'unread');

        // Filter by tab type
        $filteredQuery = match($tab) {
            'hos' => (clone $baseQuery)->whereNull('bos_alert_id')->whereNull('bos_uuid'), // HOS native alerts (no BOS identifiers)
            'bos' => (clone $baseQuery)->where(function ($query) {
                $query->whereNotNull('bos_alert_id')->orWhereNotNull('bos_uuid');
            }), // BOS synced alerts (has either bos_alert_id or bos_uuid)
            default => (clone $baseQuery), // All alerts for 'unread' and 'all' tabs
        };

        // Apply read status filter for unread tab
        if ($tab === 'unread') {
            $filteredQuery->where('is_read', false);
        }

        $alerts = $filteredQuery->latest('datetime')->get();

        // Counts - use baseQuery for all alerts, filteredQuery for tab-specific counts
        $totalToday = (clone $baseQuery)->whereDate('datetime', Carbon::today())->count();
        $unread = (clone $baseQuery)->where('is_read', false)->count();

        // Critical and warning counts based on tab
        $countQuery = match($tab) {
            'hos' => (clone $baseQuery)->whereNull('bos_alert_id')->whereNull('bos_uuid'),
            'bos' => (clone $baseQuery)->where(function ($query) {
                $query->whereNotNull('bos_alert_id')->orWhereNotNull('bos_uuid');
            }),
            default => (clone $baseQuery),
        };

        // Very basic crit/warn logic, adjust as needed
        $criticalCodes = [3, 6, 8]; // Code 3,6,8 for Probe are 'critical' in API spec
        $critical = (clone $countQuery)->whereIn('code', $criticalCodes)->count();
        $warningCodes = [1, 2, 5, 7];
        $warning = (clone $countQuery)->whereIn('code', $warningCodes)->count();

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
