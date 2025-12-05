<?php

namespace App\Http\Controllers;

use App\Models\Station;
use App\Models\PumpTransaction;
use App\Models\TankInventory;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Models\Alert;

class DashboardController extends Controller
{
    /**
     * Display the dashboard with stations data
     */
    public function index()
    {
        $stations = Station::with(['pumpTransactions', 'pumps', 'tankMeasurements'])
            ->withCount('alerts')
            ->withCount(['alerts as unread_alerts_count' => function ($query) {
                $query->where('is_read', false);
            }])
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

        // Get sales summary data (default weekly)
        $salesData = $this->getSalesSummaryData('weekly');

        // Get product distribution data (default weekly)
        $productDistributionData = $this->getProductDistributionData('weekly');

        // Top sites sales by volume and amount (default to monthly)
        $topSitesSales = $this->getTopSitesSalesData('monthly');

        // Recent alerts (latest 5) for dashboard
        $recentAlerts = Alert::with('station')
            ->whereIn('device_type', ['Pump', 'Probe'])
            ->latest('datetime')
            ->limit(5)
            ->get();

        // Live activity data
        $liveActivityData = $this->getLiveActivityData('weekly');

        // Inventory forecast data
        $inventoryForecastData = $this->getInventoryForecastData();

        // Total transactions and liters
        $totalTransactions = PumpTransaction::count();
        $totalLitersSold = PumpTransaction::sum('volume');

        // Recent hour stats
        $oneHourAgo = now()->subHour();
        $recentTransactions = PumpTransaction::where('date_time_start', '>=', $oneHourAgo)->count();
        $recentLiters = PumpTransaction::where('date_time_start', '>=', $oneHourAgo)->sum('volume');

        return view('dashboard', compact(
            'stations',
            'totalStations',
            'onlineStations',
            'warningStations',
            'offlineStations',
            'salesData',
            'productDistributionData',
            'topSitesSales',
            'recentAlerts',
            'liveActivityData',
            'inventoryForecastData',
            'totalTransactions',
            'totalLitersSold',
            'recentTransactions',
            'recentLiters'
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
     * Get sales summary data for the last 7 days
     */
    private function getSalesSummaryData(string $period = 'weekly'): array
    {
        $labels = [];
        $volumeData = [];

        if ($period === 'daily') {
            // Last 7 days
            $startDate = now()->subDays(6)->startOfDay();
            $endDate = now()->endOfDay();

            $dailySales = PumpTransaction::whereBetween('date_time_start', [$startDate, $endDate])
                ->whereNotNull('volume')
                ->select(
                    DB::raw('DATE(date_time_start) as date'),
                    DB::raw('SUM(volume) as total_volume')
                )
                ->groupBy(DB::raw('DATE(date_time_start)'))
                ->orderBy('date')
                ->get();

            for ($i = 6; $i >= 0; $i--) {
                $date = now()->subDays($i);
                $labels[] = $date->format('D'); // Mon, Tue, Wed, etc.
                $daySales = $dailySales->firstWhere('date', $date->format('Y-m-d'));
                $volumeData[] = $daySales ? (float) $daySales->total_volume : 0;
            }
        } elseif ($period === 'weekly') {
            // Last 6 weeks
            for ($i = 5; $i >= 0; $i--) {
                $weekStart = now()->subWeeks($i)->startOfWeek();
                $weekEnd = now()->subWeeks($i)->endOfWeek();
                $labels[] = $weekStart->format('M d') . ' - ' . $weekEnd->format('M d');

                // Calculate volume for this week using database query
                $weekVolume = PumpTransaction::whereBetween('date_time_start', [$weekStart, $weekEnd])
                    ->whereNotNull('volume')
                    ->sum('volume');

                $volumeData[] = (float) $weekVolume;
            }
        } else { // monthly
            // Last 6 months
            $startDate = now()->subMonths(5)->startOfMonth();
            $endDate = now()->endOfMonth();

            $monthlySales = PumpTransaction::whereBetween('date_time_start', [$startDate, $endDate])
                ->whereNotNull('volume')
                ->select(
                    DB::raw('YEAR(date_time_start) as year'),
                    DB::raw('MONTH(date_time_start) as month'),
                    DB::raw('SUM(volume) as total_volume')
                )
                ->groupBy(DB::raw('YEAR(date_time_start)'), DB::raw('MONTH(date_time_start)'))
                ->orderBy('year')
                ->orderBy('month')
                ->get();

            for ($i = 5; $i >= 0; $i--) {
                $month = now()->subMonths($i);
                $labels[] = strtoupper($month->format('M')); // JAN, FEB, MAR, etc.

                $monthSales = $monthlySales->first(function ($item) use ($month) {
                    return $item->year == $month->year && $item->month == $month->month;
                });
                $volumeData[] = $monthSales ? (float) $monthSales->total_volume : 0;
            }
        }

        return [
            'labels' => $labels,
            'volume' => $volumeData,
            'total_volume' => array_sum($volumeData),
            'total_amount' => 0 // Not calculated for sales summary chart
        ];
    }

    /**
     * Get sales summary data via AJAX
     */
    public function getSalesSummary(Request $request)
    {
        $period = $request->get('period', 'weekly');
        $data = $this->getSalesSummaryData($period);

        return response()->json($data);
    }

    /**
     * Get product distribution data by period (daily, weekly, monthly)
     */
    private function getProductDistributionData(string $period = 'weekly'): array
    {
        // Fix period calculations
        if ($period === 'daily') {
            // Last 24 hours
            $startDate = now()->subHours(24);
            $endDate = now();
        } elseif ($period === 'weekly') {
            // Last 7 days
            $startDate = now()->subDays(7)->startOfDay();
            $endDate = now()->endOfDay();
        } else { // monthly
            // Last 30 days
            $startDate = now()->subDays(30)->startOfDay();
            $endDate = now()->endOfDay();
        }

        // Get product distribution data grouped by fuel grade
        // Use DB query builder - match on pts_fuel_grade_id field (BOS field) not id
        $productData = DB::table('pump_transactions')
            ->join('fuel_grades', function ($join) {
                $join->on(DB::raw('CAST(pump_transactions.pts_fuel_grade_id AS CHAR)'), '=', DB::raw('CAST(fuel_grades.pts_fuel_grade_id AS CHAR)'))
                     ->on('pump_transactions.station_id', '=', 'fuel_grades.station_id');
            })
            ->whereBetween('pump_transactions.date_time_start', [$startDate, $endDate])
            ->whereNotNull('pump_transactions.volume')
            ->whereNotNull('pump_transactions.amount')
            ->whereNotNull('pump_transactions.pts_fuel_grade_id')
            ->whereNotNull('fuel_grades.name')
            ->select(
                'fuel_grades.name as fuel_grade_name',
                DB::raw('SUM(pump_transactions.volume) as total_volume'),
                DB::raw('SUM(pump_transactions.amount) as total_amount'),
                DB::raw('COUNT(*) as transaction_count')
            )
            ->groupBy('fuel_grades.id', 'fuel_grades.name')
            ->havingRaw('SUM(pump_transactions.volume) > 0')
            ->orderBy('total_volume', 'desc')
            ->get();

        // Prepare data for chart - ensure order: Gasoline91, Gasoline95, Gasoline98, Diesel
        // Handle both "Gasoline91" and "Gasoline 91" formats
        $productOrder = ['Gasoline91', 'Gasoline95', 'Gasoline98', 'Diesel'];
        $productOrderWithSpaces = ['Gasoline 91', 'Gasoline 95', 'Gasoline 98', 'Diesel'];
        $labels = [];
        $data = [];
        // Blue color variations matching the image
        $colorMap = [
            'Gasoline91' => 'green',  // Medium blue
            'Gasoline 91' => 'green',
            'Gasoline95' => '#FF2323',  // Lighter blue
            'Gasoline 95' => '#FF2323',
            'Gasoline98' => '#2388FF',  // Light blue
            'Gasoline 98' => '#2388FF',
            'Diesel' => '#FFB800',      // Very light blue
        ];
        $colors = [];
        $totalVolume = 0;
        $totalAmount = 0;
        $totalTransactions = 0;

        // Create a map of product data
        $productMap = [];

        foreach ($productData as $product) {
            if (isset($product->fuel_grade_name) && $product->fuel_grade_name) {
                $productMap[$product->fuel_grade_name] = $product;
            }
        }

        // Order products according to specified order (check both formats)
        $allProductNames = array_merge($productOrder, $productOrderWithSpaces);

        foreach ($allProductNames as $productName) {
            if (isset($productMap[$productName])) {
                $product = $productMap[$productName];
                $labels[] = $product->fuel_grade_name;
                $data[] = (float) $product->total_volume;
                $colors[] = $colorMap[$productName] ?? '#3b82f6';
                $totalVolume += (float) $product->total_volume;
                $totalAmount += (float) $product->total_amount;
                $totalTransactions += (int) $product->transaction_count;
                // Remove from map to avoid duplicates
                unset($productMap[$productName]);
            }
        }

        // Add any remaining products not in the order list
        foreach ($productMap as $productName => $product) {
            $labels[] = $product->fuel_grade_name;
            $data[] = (float) $product->total_volume;
            $colors[] = '#3b82f6';
            $totalVolume += (float) $product->total_volume;
            $totalAmount += (float) $product->total_amount;
            $totalTransactions += (int) $product->transaction_count;
        }

        // Calculate TOTAL volume from ALL transactions in the period (not just those with matching fuel grades)
        // This ensures the center text shows the correct total from the transaction table
        $totalVolumeAllTransactions = DB::table('pump_transactions')
            ->whereBetween('date_time_start', [$startDate, $endDate])
            ->whereNotNull('volume')
            ->sum('volume');

        // Use the total from all transactions for display
        $totalVolume = (float) $totalVolumeAllTransactions;

        // If no data found after processing, return empty arrays
        if (empty($labels)) {
            return [
                'labels' => [],
                'data' => [],
                'colors' => [],
                'total_volume' => 0,
                'total_amount' => 0,
                'total_transactions' => 0,
            ];
        }

        return [
            'labels' => $labels,
            'data' => $data,
            'colors' => $colors,
            'total_volume' => $totalVolume,
            'total_amount' => $totalAmount,
            'total_transactions' => $totalTransactions,
            'raw_data' => $productData
        ];
    }

    /**
     * Get top sites by sales (volume and amount) over the last 30 days.
     */
    private function getTopSitesSalesData(string $period = 'monthly'): array
    {
        // Calculate date range based on period
        if ($period === 'daily') {
            $startDate = now()->subHours(24);
            $endDate = now();
        } elseif ($period === 'weekly') {
            $startDate = now()->subDays(7)->startOfDay();
            $endDate = now()->endOfDay();
        } else { // monthly
            $startDate = now()->subDays(30)->startOfDay();
            $endDate = now()->endOfDay();
        }

        $rows = PumpTransaction::query()
            ->whereBetween('date_time_start', [$startDate, $endDate])
            ->whereNotNull('station_id')
            ->whereNotNull('volume')
            ->whereNotNull('amount')
            ->join('stations', 'pump_transactions.station_id', '=', 'stations.id')
            ->select(
                'stations.id as station_id',
                'stations.pts_id as station_code',
                'stations.site_name as station_name',
                DB::raw('SUM(pump_transactions.volume) as total_volume'),
                DB::raw('SUM(pump_transactions.amount) as total_amount')
            )
            ->groupBy('stations.id', 'stations.pts_id', 'stations.site_name')
            ->orderByDesc('total_amount')
            ->limit(5)
            ->get();

        if ($rows->isEmpty()) {
            return [
                'labels' => [],
                'volume' => [],
                'amount' => [],
            ];
        }

        // Use pts_id (site code) for labels, fallback to site_name if pts_id is null
        $labels = $rows->map(function ($row) {
            return $row->station_code ?? $row->station_name;
        })->all();
        $volume = $rows->pluck('total_volume')->map(fn ($v) => (float)$v)->all();
        $amount = $rows->pluck('total_amount')->map(fn ($a) => (float)$a)->all();

        return compact('labels', 'volume', 'amount');
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

    /**
     * Get live activity data for chart
     */
    private function getLiveActivityData(string $period = 'weekly'): array
    {
        $labels = [];
        $volumes = [];
        $amounts = [];

        if ($period === 'daily') {
            // Last 24 hours, grouped by hour
            for ($i = 23; $i >= 0; $i--) {
                $hourStart = now()->subHours($i)->startOfHour();
                $hourEnd = now()->subHours($i)->endOfHour();

                $data = PumpTransaction::whereBetween('date_time_start', [$hourStart, $hourEnd])
                    ->select(
                        DB::raw('SUM(volume) as total_volume'),
                        DB::raw('SUM(amount) as total_amount')
                    )
                    ->first();

                $labels[] = $hourStart->format('H:00');
                $volumes[] = (float) ($data->total_volume ?? 0);
                $amounts[] = (float) ($data->total_amount ?? 0);
            }
        } elseif ($period === 'weekly') {
            // Last 7 days
            for ($i = 6; $i >= 0; $i--) {
                $dayStart = now()->subDays($i)->startOfDay();
                $dayEnd = now()->subDays($i)->endOfDay();

                $data = PumpTransaction::whereBetween('date_time_start', [$dayStart, $dayEnd])
                    ->select(
                        DB::raw('SUM(volume) as total_volume'),
                        DB::raw('SUM(amount) as total_amount')
                    )
                    ->first();

                $labels[] = $dayStart->format('D'); // MON, TUE, WED...
                $volumes[] = (float) ($data->total_volume ?? 0);
                $amounts[] = (float) ($data->total_amount ?? 0);
            }
        } else { // monthly
            // Last 30 days
            for ($i = 29; $i >= 0; $i--) {
                $dayStart = now()->subDays($i)->startOfDay();
                $dayEnd = now()->subDays($i)->endOfDay();

                $data = PumpTransaction::whereBetween('date_time_start', [$dayStart, $dayEnd])
                    ->select(
                        DB::raw('SUM(volume) as total_volume'),
                        DB::raw('SUM(amount) as total_amount')
                    )
                    ->first();

                $labels[] = $dayStart->format('M d');
                $volumes[] = (float) ($data->total_volume ?? 0);
                $amounts[] = (float) ($data->total_amount ?? 0);
            }
        }

        return [
            'labels' => $labels,
            'volumes' => $volumes,
            'amounts' => $amounts,
        ];
    }

    /**
     * Get live activity data via AJAX
     */
    public function getLiveActivity(Request $request)
    {
        $period = $request->input('period', 'weekly');
        $data = $this->getLiveActivityData($period);

        // Also get updated totals
        $totalTransactions = PumpTransaction::count();
        $totalLitersSold = PumpTransaction::sum('volume');

        $oneHourAgo = now()->subHour();
        $recentTransactions = PumpTransaction::where('date_time_start', '>=', $oneHourAgo)->count();
        $recentLiters = PumpTransaction::where('date_time_start', '>=', $oneHourAgo)->sum('volume');

        return response()->json([
            'chartData' => $data,
            'totalTransactions' => $totalTransactions,
            'totalLitersSold' => $totalLitersSold,
            'recentTransactions' => $recentTransactions,
            'recentLiters' => $recentLiters,
        ]);
    }

    /**
     * Get product sales data by period via AJAX
     */
    public function getProductSales(Request $request)
    {
        $period = $request->input('period', 'weekly');
        $data = $this->getProductDistributionData($period);

        return response()->json($data);
    }

    /**
     * Get top sites sales data by period via AJAX
     */
    public function getTopSites(Request $request)
    {
        $period = $request->input('period', 'monthly');
        $data = $this->getTopSitesSalesData($period);

        return response()->json($data);
    }

    /**
     * Get inventory forecast data - calculate tank dry-out predictions
     */
    private function getInventoryForecastData(): array
    {
        // Get latest tank inventory for each tank using a subquery
        $latestIds = DB::table('tank_inventories')
            ->select(DB::raw('MAX(id) as id'))
            ->whereNotNull('snapshot_datetime')
            ->whereNotNull('absolute_product_volume')
            ->whereNotNull('fuel_grade_id')
            ->groupBy('station_id', 'tank')
            ->pluck('id');

        $latestInventories = TankInventory::whereIn('id', $latestIds)
            ->whereNotNull('absolute_product_volume')
            ->whereNotNull('fuel_grade_id')
            ->get();

        // Calculate daily consumption rate for each station and fuel grade (last 7 days average)
        $sevenDaysAgo = now()->subDays(7)->startOfDay();

        // Get consumption by station and fuel grade ID (matching tank_inventories.fuel_grade_id)
        $dailyConsumptionByStationAndFuelGrade = DB::table('pump_transactions')
            ->join('fuel_grades', function ($join) {
                $join->on('pump_transactions.pts_fuel_grade_id', '=', DB::raw('CAST(fuel_grades.pts_fuel_grade_id AS UNSIGNED)'));
            })
            ->where('pump_transactions.date_time_start', '>=', $sevenDaysAgo)
            ->whereNotNull('pump_transactions.station_id')
            ->whereNotNull('pump_transactions.pts_fuel_grade_id')
            ->whereNotNull('pump_transactions.volume')
            ->select(
                'pump_transactions.station_id',
                'fuel_grades.id as fuel_grade_id',
                DB::raw('SUM(pump_transactions.volume) as total_volume'),
                DB::raw('COUNT(DISTINCT DATE(pump_transactions.date_time_start)) as days_count')
            )
            ->groupBy('pump_transactions.station_id', 'fuel_grades.id')
            ->get()
            ->mapWithKeys(function ($item) {
                $daysCount = max(1, $item->days_count);
                $dailyAvg = (float) $item->total_volume / $daysCount;
                $key = $item->station_id . '_' . $item->fuel_grade_id;

                return [$key => $dailyAvg];
            });

        // Fallback: Calculate overall daily consumption by fuel grade ID
        $dailyConsumptionByFuelGrade = DB::table('pump_transactions')
            ->join('fuel_grades', function ($join) {
                $join->on('pump_transactions.pts_fuel_grade_id', '=', DB::raw('CAST(fuel_grades.pts_fuel_grade_id AS UNSIGNED)'));
            })
            ->where('pump_transactions.date_time_start', '>=', $sevenDaysAgo)
            ->whereNotNull('pump_transactions.pts_fuel_grade_id')
            ->whereNotNull('pump_transactions.volume')
            ->select(
                'fuel_grades.id as fuel_grade_id',
                DB::raw('SUM(pump_transactions.volume) as total_volume'),
                DB::raw('COUNT(DISTINCT DATE(pump_transactions.date_time_start)) as days_count')
            )
            ->groupBy('fuel_grades.id')
            ->get()
            ->mapWithKeys(function ($item) {
                $daysCount = max(1, $item->days_count);
                $dailyAvg = (float) $item->total_volume / $daysCount;

                return [$item->fuel_grade_id => $dailyAvg];
            });

        // Initialize categories
        $categories = [
            'dry_out' => 0,      // Already dry (0 days)
            'days_1_2' => 0,     // 1-2 days
            'days_3_5' => 0,     // 3-5 days
            'days_6_8' => 0,     // 6-8 days
            'days_9_12' => 0,   // 9-12 days
        ];

        // Process each tank inventory
        foreach ($latestInventories as $inventory) {
            $currentVolume = (float) ($inventory->absolute_product_volume ?? 0);

            // If tank is already dry or has no volume
            if ($currentVolume <= 0) {
                $categories['dry_out']++;

                continue;
            }

            // Get consumption rate for this station and fuel grade
            $stationId = $inventory->station_id;
            $fuelGradeBosId = $inventory->fuel_grade_id; // This is the BOS fuel_grade_id

            // Try to find matching fuel_grade by BOS ID
            $fuelGrade = DB::table('fuel_grades')
                ->where('station_id', $stationId)
                ->where(DB::raw('CAST(pts_fuel_grade_id AS UNSIGNED)'), $fuelGradeBosId)
                ->first();

            if (!$fuelGrade) {
                // Try without station match
                $fuelGrade = DB::table('fuel_grades')
                    ->where(DB::raw('CAST(pts_fuel_grade_id AS UNSIGNED)'), $fuelGradeBosId)
                    ->first();
            }

            if ($fuelGrade) {
                $fuelGradeId = $fuelGrade->id;
                $key = $stationId . '_' . $fuelGradeId;

                // Try station-specific consumption first, fallback to overall fuel grade consumption
                $dailyConsumption = $dailyConsumptionByStationAndFuelGrade->get($key, 0);

                if ($dailyConsumption <= 0) {
                    $dailyConsumption = $dailyConsumptionByFuelGrade->get($fuelGradeId, 0);
                }
            } else {
                $dailyConsumption = 0;
            }

            // If no consumption data, skip this tank
            if ($dailyConsumption <= 0) {
                continue;
            }

            // Calculate days until dry
            $daysUntilDry = $currentVolume / $dailyConsumption;

            // Categorize based on days until dry
            if ($daysUntilDry <= 0) {
                $categories['dry_out']++;
            } elseif ($daysUntilDry <= 2) {
                $categories['days_1_2']++;
            } elseif ($daysUntilDry <= 5) {
                $categories['days_3_5']++;
            } elseif ($daysUntilDry <= 8) {
                $categories['days_6_8']++;
            } elseif ($daysUntilDry <= 12) {
                $categories['days_9_12']++;
            }
        }

        // Calculate fill percentages for visual representation
        $maxCount = max($categories);
        $fillPercentages = [];

        foreach ($categories as $key => $count) {
            $fillPercentages[$key] = $maxCount > 0 ? ($count / $maxCount) * 100 : 0;
        }

        return [
            'categories' => $categories,
            'fill_percentages' => $fillPercentages,
            'labels' => [
                'dry_out' => 'DRY OUT',
                'days_1_2' => '1-2 DAYS',
                'days_3_5' => '3-5 DAYS',
                'days_6_8' => '6-8 DAYS',
                'days_9_12' => '9-12 DAYS',
            ],
        ];
    }
}
