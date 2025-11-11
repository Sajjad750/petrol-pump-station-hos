<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Alert;
use Carbon\Carbon;
use Illuminate\Support\Facades\Response;

class AlertController extends Controller
{
    public function index(Request $request)
    {
        // Only Pump and Probe alerts
        $alertsQuery = Alert::with('station')->whereIn('device_type', ['Pump', 'Probe']);
        $tab = $request->get('tab', 'unread');
        // Tabs support: unread, all, bos, hos, controller (future: use device_type, etc)
        $alerts = match($tab) {
            'all' => $alertsQuery->latest('datetime')->get(),
            default => $alertsQuery->where('is_read', false)->latest('datetime')->get(),
        };
        // Counts
        $totalToday = $alertsQuery->whereDate('datetime', Carbon::today())->count();
        $unread = $alertsQuery->where('is_read', false)->count();
        // Very basic crit/warn logic, adjust as needed
        $criticalCodes = [3, 6, 8]; // Code 3,6,8 for Probe are 'critical' in API spec
        $critical = $alertsQuery->whereIn('code', $criticalCodes)->count();
        $warningCodes = [1, 2, 5, 7];
        $warning = $alertsQuery->whereIn('code', $warningCodes)->count();

        return view('alerts.index', [
            'alerts' => $alerts,
            'totalToday' => $totalToday,
            'unread' => $unread,
            'critical' => $critical,
            'warning' => $warning,
            'tab' => $tab
        ]);
    }

    /**
     * Get BOS alerts via API
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function getBosAlerts(Request $request)
    {
        // Get query parameters
        $limit = $request->input('limit', 50);
        $unreadOnly = $request->boolean('unread_only', true);
        $criticalOnly = $request->boolean('critical_only', false);
        $stationId = $request->input('station_id');
        
        // Start building the query
        $query = Alert::with('station')
            ->where('device_type', 'BOS')
            ->latest('datetime');
            
        // Apply filters
        if ($unreadOnly) {
            $query->where('is_read', false);
        }
        
        if ($criticalOnly) {
            $criticalCodes = [3, 6, 8];
            $query->whereIn('code', $criticalCodes);
        }
        
        if ($stationId) {
            $query->where('station_id', $stationId);
        }
        
        // Get paginated results
        $alerts = $query->paginate(min($limit, 100)); // Max 100 per page for performance
        
        // Transform the response
        $response = [
            'success' => true,
            'data' => $alerts->items(),
            'pagination' => [
                'total' => $alerts->total(),
                'per_page' => $alerts->perPage(),
                'current_page' => $alerts->currentPage(),
                'last_page' => $alerts->lastPage(),
                'from' => $alerts->firstItem(),
                'to' => $alerts->lastItem()
            ]
        ];
        
        return Response::json($response);
    }
    
    /**
     * Mark BOS alert as read
     * 
     * @param int $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function markBosAlertAsRead($id)
    {
        $alert = Alert::where('id', $id)
            ->where('device_type', 'BOS')
            ->firstOrFail();
            
        $alert->update(['is_read' => true]);
        
        return Response::json([
            'success' => true,
            'message' => 'Alert marked as read',
            'data' => $alert->fresh()
        ]);
    }
}
