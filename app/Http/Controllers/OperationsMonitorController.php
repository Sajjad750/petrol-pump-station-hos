<?php

namespace App\Http\Controllers;

use App\Models\Station;
use App\Models\Pump;
use App\Models\TankInventory;
use Illuminate\Http\Request;

class OperationsMonitorController extends Controller
{
    public function index(Request $request)
    {
        // Summary counts
        $totalSites = Station::count();
        $onlineSites = Station::all()->filter(function ($station) { return $station->isOnline(); })->count();
        $offlineSites = Station::all()->filter(function ($station) { return $station->isOffline(); })->count();

        $totalPumps = Pump::count();
        $onlinePumps = Pump::where('status', 'active')->count();
        $offlinePumps = $totalPumps - $onlinePumps;

        // Tank overview counts derived from tank inventory snapshots
        $tankInventoryBase = TankInventory::query();
        $totalTanks = (clone $tankInventoryBase)->distinct('tank')->count('tank');
        $onlineTanks = (clone $tankInventoryBase)->where(function ($query) {
            $query->whereNull('product_volume')
                ->orWhere('product_volume', '>', 0);
        })->distinct('tank')->count('tank');
        $offlineTanks = max(0, $totalTanks - $onlineTanks);

        // Alerts placeholder
        $totalAlerts = 0;
        $normalSites = $onlineSites; // Fallback
        $sitesWithAlerts = 0;

        // All Sites Table: Each station row summary
        $stations = Station::with(['pumps', 'pumpTransactions'])->get();
        $allSites = $stations->map(function ($station) {
            $total = $station->pumps->count();
            $online = $station->pumps->filter(fn ($pump) => $pump->status === 'active')->count();
            $pumpPercent = $total ? round($online / $total * 100) : 0;

            // Tank counts based on inventory records
            $tank_total = TankInventory::where('station_id', $station->id)->distinct('tank')->count('tank');
            $tank_online = TankInventory::where('station_id', $station->id)
                ->where(function ($query) {
                    $query->whereNull('product_volume')
                        ->orWhere('product_volume', '>', 0);
                })
                ->distinct('tank')
                ->count('tank');
            $tank_offline = max(0, $tank_total - $tank_online);
            $tank_percent = $tank_total ? round($tank_online / $tank_total * 100) : 0;

            return [
                'id' => $station->id,
                'code' => $station->pts_id,
                'name' => $station->site_name,
                'status' => $station->isOnline() ? 'online' : 'offline',
                'last_connected' => $station->last_sync_at?->format('m/d/Y H:i') ?? '-',
                'last_transaction' => $station->pumpTransactions->sortByDesc('created_at')->first()?->created_at?->format('m/d/Y H:i') ?? '-',
                'pump_percent' => $pumpPercent,
                'pump_online' => $online,
                'pump_total' => $total,
                // Tank stats for new column/UI
                'tank_total' => $tank_total,
                'tank_online' => $tank_online,
                'tank_offline' => $tank_offline,
                'tank_percent' => $tank_percent,
            ];
        });

        return view('operations_monitor.index', compact(
            'totalSites',
            'onlineSites',
            'offlineSites',
            'totalPumps',
            'onlinePumps',
            'offlinePumps',
            'totalTanks',
            'onlineTanks',
            'offlineTanks',
            'totalAlerts',
            'normalSites',
            'sitesWithAlerts',
            'allSites'
        ));
    }

    public function show($stationId)
    {
        $station = \App\Models\Station::with(['pumps', 'tankMeasurements'])->findOrFail($stationId);
        $address = $station->address ?? ($station->city ?? '-') . ', ' . ($station->region ?? '-');

        // Station status
        $station_status = $station->isOnline() ? 'online' : ($station->isOffline() ? 'offline' : 'warning');

        // Pump stats
        $pump_total = $station->pumps->count();
        $pump_online = $station->pumps->where('status', 'active')->count();
        $pumps = $station->pumps->map(function ($pump) {
            return [
                'number' => $pump->name ?? $pump->pump_id ?? '-',
                'product' => $pump->product ?? '-',
                'nozzles' => $pump->nozzles ?? '-',
                'status' => $pump->status === 'active' ? 'online' : 'offline',
            ];
        });

        // Tank measurements: group latest per tank
        $latestMeasurements = $station->tankMeasurements->sortBy('tank')->groupBy('tank')->map(function ($tankGroup) {
            return $tankGroup->sortByDesc('date_time')->first();
        });
        $tank_total = $latestMeasurements->count();
        $tank_online = $tank_total; // Or use alarms/status in measurement if any
        $tanks = $latestMeasurements->map(function ($m, $tankNum) {
            $capacity = 10000; // You should use real if present
            $current = $m->product_volume ?? 0;
            $percentage = $capacity > 0 ? round($current / $capacity * 100) : 0;
            // Status logic: use Eloquent methods if exist
            $status = ($m->isCriticallyLow() ?? false) ? 'critical' : ($percentage < 25 ? 'low' : 'normal');

            return [
                'number' => $tankNum,
                'product' => $m->product ?? '-',
                'capacity' => $capacity,
                'current' => $current,
                'percentage' => $percentage,
                'status' => $status,
            ];
        });

        $alerts = [
            [
                'type' => 'stock',
                'level' => 'high',
                'message' => 'Tank T-04 critical level (<10%)',
                'tank' => 'T-04',
                'date' => now()->subDays(1)->format('Y-m-d H:i:s'),
            ],
            [
                'type' => 'stock',
                'level' => 'medium',
                'message' => 'Tank T-03 low level (<25%)',
                'tank' => 'T-03',
                'date' => now()->subDays(2)->format('Y-m-d H:i:s'),
            ]
        ];

        return view('operations_monitor.station_detail', compact(
            'station',
            'address',
            'station_status',
            'pump_total',
            'pump_online',
            'tank_total',
            'tank_online',
            'pumps',
            'tanks',
            'alerts'
        ));
    }
}
