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
        $tab = $request->get('tab', 'unread');
        
        // Base query for all alerts (Pump, Probe, and BOS)
        $alertsQuery = Alert::with('station');
        
        // Apply tab filters
        switch ($tab) {
            case 'all':
                $alertsQuery->latest('datetime');
                break;
                
            case 'bos':
                $alertsQuery->where('device_type', 'BOS')
                    ->latest('datetime');
                break;
                    
            case 'hos':
                $alertsQuery->whereIn('device_type', ['Pump', 'Probe'])
                    ->latest('datetime');
                break;
                    
            case 'unread':
            default:
                $alertsQuery->where('is_read', false)
                    ->latest('datetime');
                break;
        }
        
        $alerts = $alertsQuery->paginate(25);
        
        // Get alert counts for each category
        $totalToday = Alert::whereDate('created_at', Carbon::today())->count();
        $unread = Alert::where('is_read', false)->count();
        
        // Critical and warning counts for all alert types
        $criticalCodes = [3, 6, 8];
        $warningCodes = [1, 2, 5, 7];
        
        $critical = Alert::whereIn('code', $criticalCodes)->count();
        $warning = Alert::whereIn('code', $warningCodes)->count();
        
        // Count by device type for the tabs
        $bosAlertsCount = Alert::where('device_type', 'BOS')->count();
        $hosAlertsCount = Alert::whereIn('device_type', ['Pump', 'Probe'])->count();

        return view('alerts.index', [
            'alerts' => $alerts,
            'totalToday' => $totalToday,
            'unread' => $unread,
            'critical' => $critical,
            'warning' => $warning,
            'bosAlertsCount' => $bosAlertsCount,
            'hosAlertsCount' => $hosAlertsCount,
            'tab' => $tab,
            'tabs' => [
                'unread' => 'Unread',
                'all' => 'All Alerts',
                'bos' => 'BOS Alerts',
                'hos' => 'HOS Alerts'
            ]
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
