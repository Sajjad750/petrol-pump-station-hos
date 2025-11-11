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
            'hos' => $alertsQuery->whereIn('device_type', ['Pump', 'Probe'])->latest('datetime')->get(),
            default => $alertsQuery->where('is_read', false)->latest('datetime')->get(),
        };
        
        // Format alerts for display
        $formattedAlerts = $alerts->map(function($alert) {
            $message = $this->getAlertMessage($alert->device_type, $alert->code);
            $priority = $this->getPriorityLevel($alert->code);
            
            return [
                'id' => $alert->id,
                'device_type' => $alert->device_type,
                'code' => $alert->code,
                'message' => $message,
                'priority' => $priority,
                'priority_class' => strtolower($priority),
                'date_time' => $alert->datetime,
                'read' => (bool)$alert->is_read,
                'station_name' => $alert->station->site_name ?? 'N/A',
                'device_number' => $alert->device_number ?? 'N/A'
            ];
        });

        // Return JSON for API requests
        if ($request->wantsJson()) {
            return response()->json($formattedAlerts);
        }

        // Get counts for the dashboard
        $totalToday = $baseQuery->clone()->whereDate('datetime', Carbon::today())->count();
        $unread = $baseQuery->clone()->where('is_read', false)->count();
        
        $criticalCodes = [3, 6, 8];
        $warningCodes = [1, 2, 5, 7];
        
        $critical = $baseQuery->clone()->whereIn('code', $criticalCodes)->count();
        $warning = $baseQuery->clone()->whereIn('code', $warningCodes)->count();

        return view('alerts.index', [
            'alerts' => $alerts,
            'formattedAlerts' => $formattedAlerts,
            'totalToday' => $totalToday,
            'unread' => $unread,
            'critical' => $critical,
            'warning' => $warning,
            'tab' => $tab
        ]);
    }
    
    /**
     * Get alert message based on device type and code
     */
    private function getAlertMessage($deviceType, $code)
    {
        $messages = [
            'PTS' => [
                1 => 'Low battery voltage detected',
                2 => 'High CPU temperature detected',
                3 => 'Power down detected',
                4 => 'Restart detected',
                5 => 'SD flash disk free space is too low'
            ],
            'Pump' => [
                1 => 'Offline state detected',
                2 => 'Overfilling detected'
            ],
            'Probe' => [
                1 => 'Offline state detected',
                2 => 'Error detected',
                3 => 'Critical high product level detected',
                4 => 'High product level detected',
                5 => 'Low product level detected',
                6 => 'Critical low product level detected',
                7 => 'High water level detected',
                8 => 'Tank leakage detected'
            ],
            'PriceBoard' => [
                1 => 'Offline state detected',
                2 => 'Error detected'
            ],
            'Reader' => [
                1 => 'Offline state detected',
                2 => 'Error detected'
            ],
            'BOS' => [
                // Add BOS specific messages here
                1 => 'Back Office System Alert',
                // Add more BOS alert messages as needed
            ]
        ];

        return $messages[$deviceType][$code] ?? 'Unknown alert';
    }
    
    /**
     * Get priority level based on alert code
     */
    private function getPriorityLevel($code)
    {
        $priorities = [
            'high' => [1, 3, 6, 8],
            'medium' => [2, 4, 5],
            'low' => [7]
        ];
        
        foreach ($priorities as $priority => $codes) {
            if (in_array($code, $codes)) {
                return ucfirst($priority);
            }
        }
        
        return 'Medium';
    }
    
    /**
     * Mark a specific alert as read
     */
    public function markAsRead(Alert $alert)
    {
        try {
            $alert->update(['is_read' => true]);
            return response()->json(['success' => true]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to mark alert as read: ' . $e->getMessage()
            ], 500);
        }
    }
    
    /**
     * Mark all alerts as read
     */
    public function markAllAsRead()
    {
        try {
            Alert::where('is_read', false)->update(['is_read' => true]);
            return response()->json(['success' => true]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to mark all alerts as read: ' . $e->getMessage()
            ], 500);
        }
    }
    
    /**
     * Delete an alert
     */
    public function destroy(Alert $alert)
    {
        try {
            $alert->delete();
            return response()->json(['success' => true]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to delete alert: ' . $e->getMessage()
            ], 500);
        }
    }
}
