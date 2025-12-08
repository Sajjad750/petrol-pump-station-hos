<?php

namespace App\Http\Controllers;

use App\Models\Station;
use App\Models\Pump;
use App\Models\TankInventory;
use App\Models\Alert;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;

class OperationsMonitorController extends Controller
{
    public function index(Request $request)
    {
        $stations = Station::with(['pumps', 'pumpTransactions'])
            ->withCount('alerts')
            ->withCount(['alerts as unread_alerts_count' => function ($query) {
                $query->where('is_read', false);
            }])
            ->get();

        $totalSites = $stations->count();
        $onlineSites = $stations->filter(fn ($station) => $station->isOnline())->count();
        $offlineSites = $stations->filter(fn ($station) => $station->isOffline())->count();

        $totalPumps = Pump::count();

        // Count pumps by status
        $idlePumps = Pump::where('status', 'idle')->count();
        $fillingPumps = Pump::where('status', 'filling')->count();
        $endOfTransactionPumps = Pump::where('status', 'end_of_transaction')->count();
        $offlinePumps = Pump::where('status', 'offline')->count();
        $pumplockPumps = Pump::where('status', 'pumplock')->count();

        // Operational pumps are: idle, filling, end_of_transaction
        $onlinePumps = $idlePumps + $fillingPumps + $endOfTransactionPumps;
        // Non-operational pumps are: offline, pumplock
        $offlinePumpsTotal = $offlinePumps + $pumplockPumps;

        $latestTankEntriesByStation = TankInventory::query()
            ->whereIn('id', function ($query) {
                $query->selectRaw('MAX(id)')
                    ->from('tank_inventories')
                    ->groupBy('station_id', 'tank');
            })
            ->get()
            ->groupBy('station_id');

        $flatTankEntries = $latestTankEntriesByStation->flatten(1);
        $totalTanks = $flatTankEntries->count();
        $onlineTanks = $flatTankEntries->filter(function ($entry) {
            return is_null($entry->product_volume) || (float) $entry->product_volume > 0;
        })->count();
        $offlineTanks = max(0, $totalTanks - $onlineTanks);

        $totalAlerts = Alert::count();
        $sitesWithAlerts = Alert::whereNotNull('station_id')->distinct('station_id')->count('station_id');
        $normalSites = max(0, $totalSites - $sitesWithAlerts);

        $allSites = $stations->map(function ($station) use ($latestTankEntriesByStation) {
            $total = $station->pumps->count();

            // Count pumps by status for this station
            $idle = $station->pumps->filter(fn ($pump) => $pump->status === 'idle')->count();
            $filling = $station->pumps->filter(fn ($pump) => $pump->status === 'filling')->count();
            $endOfTransaction = $station->pumps->filter(fn ($pump) => $pump->status === 'end_of_transaction')->count();
            $offline = $station->pumps->filter(fn ($pump) => $pump->status === 'offline')->count();
            $pumplock = $station->pumps->filter(fn ($pump) => $pump->status === 'pumplock')->count();

            // Operational pumps are: idle, filling, end_of_transaction
            $online = $idle + $filling + $endOfTransaction;
            $pumpPercent = $total ? round($online / $total * 100) : 0;

            /** @var Collection $tankEntries */
            $tankEntries = $latestTankEntriesByStation->get($station->id, collect());
            $tank_total = $tankEntries->count();
            $tank_online = $tankEntries->filter(function ($entry) {
                return is_null($entry->product_volume) || (float) $entry->product_volume > 0;
            })->count();
            $tank_percent = $tank_total ? round($tank_online / $tank_total * 100) : 0;

            $lastTransaction = $station->pumpTransactions->sortByDesc('created_at')->first();

            return [
                'id' => $station->id,
                'code' => $station->pts_id,
                'name' => $station->site_name,
                'status' => $station->isOnline() ? 'online' : ($station->hasWarning() ? 'warning' : 'offline'),
                'last_connected' => $station->last_sync_at?->toIso8601String(),
                'last_transaction' => $lastTransaction?->created_at?->toIso8601String(),
                'pump_percent' => $pumpPercent,
                'pump_online' => $online,
                'pump_total' => $total,
                'pump_idle' => $idle,
                'pump_filling' => $filling,
                'pump_end_of_transaction' => $endOfTransaction,
                'pump_offline' => $offline,
                'pump_pumplock' => $pumplock,
                'tank_total' => $tank_total,
                'tank_online' => $tank_online,
                'tank_offline' => max(0, $tank_total - $tank_online),
                'tank_percent' => $tank_percent,
                'alerts_total' => $station->alerts_count ?? 0,
                'alerts_unread' => $station->unread_alerts_count ?? 0,
            ];
        });

        return view('operations_monitor.index', compact(
            'totalSites',
            'onlineSites',
            'offlineSites',
            'totalPumps',
            'onlinePumps',
            'offlinePumpsTotal',
            'idlePumps',
            'fillingPumps',
            'endOfTransactionPumps',
            'offlinePumps',
            'pumplockPumps',
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
        $station = \App\Models\Station::with(['pumps', 'tankInventories', 'tankMeasurements'])->findOrFail($stationId);
        $address = $station->address ?? ($station->city ?? '-') . ', ' . ($station->region ?? '-');

        // Station status
        $station_status = $station->isOnline() ? 'online' : ($station->isOffline() ? 'offline' : 'warning');

        // Pump stats
        $pump_total = $station->pumps->count();

        // Count operational pumps (idle, filling, end_of_transaction)
        $pump_online = $station->pumps->filter(function ($pump) {
            return in_array($pump->status, ['idle', 'filling', 'end_of_transaction']);
        })->count();

        // Get pump transactions to extract product and nozzle information dynamically
        $pumpTransactions = \App\Models\PumpTransaction::query()
            ->where('station_id', $station->id)
            ->select('pts_pump_id', 'pts_nozzle_id', 'pts_fuel_grade_id')
            ->with('fuelGrade:id,name')
            ->get()
            ->groupBy('pts_pump_id');

        $pumps = $station->pumps->map(function ($pump) use ($pumpTransactions) {
            // Get pump number
            $pumpNumber = $pump->name ?? $pump->pump_id ?? $pump->pts_pump_id ?? '-';

            // Get product(s) from pump transactions for this pump
            $transactionsForPump = $pumpTransactions->get($pump->pts_pump_id, collect());
            $products = $transactionsForPump
                ->pluck('fuelGrade.name')
                ->filter()
                ->unique()
                ->values()
                ->all();
            $productDisplay = !empty($products) ? implode(', ', $products) : '-';

            // Get nozzles from pump transactions for this pump
            $nozzles = $transactionsForPump
                ->pluck('pts_nozzle_id')
                ->filter()
                ->unique()
                ->sort()
                ->values()
                ->all();
            $nozzleDisplay = !empty($nozzles) ? implode(', ', $nozzles) : ($pump->nozzles_count ? "Count: {$pump->nozzles_count}" : '-');

            return [
                'number' => $pumpNumber,
                'product' => $productDisplay,
                'nozzles' => $nozzleDisplay,
                'status' => $pump->status ?? 'offline',
            ];
        });

        // Tank inventory: Use TankInventory model if available, otherwise fallback to TankMeasurement
        $latestInventories = TankInventory::query()
            ->where('station_id', $station->id)
            ->latestForTanks()
            ->get()
            ->sortBy('tank')
            ->groupBy('tank')
            ->map(function ($tankGroup) {
                return $tankGroup->sortByDesc('snapshot_datetime')->first();
            });

        // If no tank inventories, fallback to tank measurements
        if ($latestInventories->isEmpty()) {
            $latestMeasurements = $station->tankMeasurements->sortBy('tank')->groupBy('tank')->map(function ($tankGroup) {
                return $tankGroup->sortByDesc('date_time')->first();
            });

            $tank_total = $latestMeasurements->count();
            $tank_online = $latestMeasurements->filter(function ($m) {
                return ($m->product_volume ?? 0) > 0;
            })->count();

            $tanks = $latestMeasurements->map(function ($m, $tankNum) {
                $capacity = 33000;
                $current = $m->product_volume ?? 0;

                // Use tank_filling_percentage if available, otherwise calculate from volume and capacity
                $percentage = $m->tank_filling_percentage ?? null;

                if ($percentage === null) {
                    // Calculate percentage based on current volume and fixed capacity
                    $percentage = $capacity > 0 ? round(($current / $capacity) * 100, 2) : 0;
                } else {
                    $percentage = (float) $percentage;
                }

                // Status logic using model method if available
                $status = 'normal';

                if ($m->isCriticallyLow()) {
                    $status = 'critical';
                } elseif ($percentage < 25) {
                    $status = 'low';
                }

                return [
                    'number' => $tankNum,
                    'product' => $m->fuel_grade_name ?? '-',
                    'capacity' => 33000,
                    'current' => $m->product_volume ?? 0,
                    'percentage' => $percentage,
                    'status' => $status,
                ];
            });
        } else {
            // Use TankInventory data (preferred)
            $tank_total = $latestInventories->count();
            $tank_online = $latestInventories->filter(function ($inv) {
                return ($inv->absolute_product_volume ?? $inv->product_volume ?? 0) > 0;
            })->count();

            $tanks = $latestInventories->map(function ($inv, $tankNum) {
                $capacity = 33000;
                $current = $inv->absolute_product_volume ?? $inv->product_volume ?? 0;

                // Use tank_filling_percentage if available, otherwise calculate from volume and capacity
                $percentage = $inv->tank_filling_percentage ?? null;

                if ($percentage === null) {
                    // Calculate percentage based on current volume and fixed capacity
                    $percentage = $capacity > 0 ? round(($current / $capacity) * 100, 2) : 0;
                } else {
                    $percentage = (float) $percentage;
                }

                // Use model method for status
                $status = $inv->getInventoryStatus();

                if ($status === 'low' || $status === 'incomplete') {
                    $status = 'low';
                } elseif ($status === 'high_water') {
                    $status = 'critical';
                } else {
                    $status = 'normal';
                }

                // Override with percentage-based status if needed
                if ($percentage < 15) {
                    $status = 'critical';
                } elseif ($percentage < 30) {
                    $status = 'low';
                }

                return [
                    'number' => $tankNum,
                    'product' => $inv->fuel_grade_name ?? '-',
                    'capacity' => 33000,
                    'current' => $inv->absolute_product_volume ?? $inv->product_volume ?? 0,
                    'percentage' => $percentage,
                    'status' => $status,
                ];
            });
        }

        $alerts = Alert::query()
            ->where('station_id', $station->id)
            ->latest('datetime')
            ->limit(15)
            ->get()
            ->map(function (Alert $alert) {
                $context = $this->formatAlert($alert);

                return [
                    'title' => $context['title'],
                    'level' => $context['level'],
                    'message' => $context['message'],
                    'date' => $context['date'],
                ];
            })
            ->all();

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

    private function formatAlert(Alert $alert): array
    {
        $level = 'low';
        $message = $alert->description ?? 'Alert notification received.';

        if ($alert->device_type === 'Pump') {
            if ($alert->code == 1) {
                $message = 'Pump '.$alert->device_number.' offline state detected';
                $level = 'high';
            } elseif ($alert->code == 2) {
                $message = 'Pump '.$alert->device_number.' overfilling detected';
                $level = 'medium';
            } else {
                $message = 'Pump '.$alert->device_number.' notification';
                $level = 'medium';
            }
        } elseif ($alert->device_type === 'Probe') {
            switch ($alert->code) {
                case 1:
                    $message = 'Probe '.$alert->device_number.' offline state detected';
                    $level = 'high';

                    break;

                case 2:
                    $message = 'Probe '.$alert->device_number.' error detected';
                    $level = 'medium';

                    break;

                case 3:
                    $message = 'Probe '.$alert->device_number.' critical high product level';
                    $level = 'high';

                    break;

                case 4:
                    $message = 'Probe '.$alert->device_number.' high product level';
                    $level = 'medium';

                    break;

                case 5:
                    $message = 'Probe '.$alert->device_number.' low product level';
                    $level = 'medium';

                    break;

                case 6:
                    $message = 'Probe '.$alert->device_number.' critical low product level';
                    $level = 'high';

                    break;

                case 7:
                    $message = 'Probe '.$alert->device_number.' high water level';
                    $level = 'medium';

                    break;

                case 8:
                    $message = 'Probe '.$alert->device_number.' tank leakage detected';
                    $level = 'high';

                    break;

                default:
                    $message = 'Probe '.$alert->device_number.' notification';
                    $level = 'medium';

                    break;
            }
        }

        return [
            'title' => ucfirst($alert->device_type ?? 'Alert'),
            'level' => $level,
            'message' => $message,
            'date' => optional($alert->datetime)->format('Y-m-d H:i:s') ?? '-',
        ];
    }
}
