<?php

namespace App\Http\Controllers;

use App\Models\Station;
use App\Models\PumpTransaction;
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

        // Get sales summary data for the last 7 days
        $salesData = $this->getSalesSummaryData();

        // Get product distribution data
        $productDistributionData = $this->getProductDistributionData();

        // Top sites sales by volume and amount
        $topSitesSales = $this->getTopSitesSalesData();

        // Recent alerts (latest 5) for dashboard
        $recentAlerts = Alert::with('station')
            ->whereIn('device_type', ['Pump', 'Probe'])
            ->latest('datetime')
            ->limit(5)
            ->get();

        return view('dashboard', compact(
            'stations',
            'totalStations',
            'onlineStations',
            'warningStations',
            'offlineStations',
            'salesData',
            'productDistributionData',
            'topSitesSales',
            'recentAlerts'
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
    private function getSalesSummaryData(): array
    {
        $startDate = now()->subDays(6)->startOfDay();
        $endDate = now()->endOfDay();

        // Get daily sales data for the last 7 days
        $dailySales = PumpTransaction::whereBetween('date_time_start', [$startDate, $endDate])
            ->whereNotNull('volume')
            ->whereNotNull('amount')
            ->select(
                DB::raw('DATE(date_time_start) as date'),
                DB::raw('SUM(volume) as total_volume'),
                DB::raw('SUM(amount) as total_amount')
            )
            ->groupBy(DB::raw('DATE(date_time_start)'))
            ->orderBy('date')
            ->get();

        // Create arrays for chart data
        $labels = [];
        $volumeData = [];
        $amountData = [];

        // Generate labels for the last 7 days
        for ($i = 6; $i >= 0; $i--) {
            $date = now()->subDays($i);
            $labels[] = $date->format('D'); // Mon, Tue, Wed, etc.

            // Find sales data for this date
            $daySales = $dailySales->firstWhere('date', $date->format('Y-m-d'));

            $volumeData[] = $daySales ? (float) $daySales->total_volume : 0;
            $amountData[] = $daySales ? (float) $daySales->total_amount : 0;
        }

        return [
            'labels' => $labels,
            'volume' => $volumeData,
            'amount' => $amountData,
            'total_volume' => array_sum($volumeData),
            'total_amount' => array_sum($amountData)
        ];
    }

    /**
     * Get product distribution data for the last 7 days
     */
    private function getProductDistributionData(): array
    {
        $startDate = now()->subDays(6)->startOfDay();
        $endDate = now()->endOfDay();

        // Get product distribution data grouped by fuel grade
        $productData = PumpTransaction::whereBetween('pump_transactions.date_time_start', [$startDate, $endDate])
            ->whereNotNull('pump_transactions.volume')
            ->whereNotNull('pump_transactions.amount')
            ->whereNotNull('pump_transactions.pts_fuel_grade_id')
            ->join('fuel_grades', 'pump_transactions.pts_fuel_grade_id', '=', 'fuel_grades.id')
            ->select(
                'fuel_grades.name as fuel_grade_name',
                DB::raw('SUM(pump_transactions.volume) as total_volume'),
                DB::raw('SUM(pump_transactions.amount) as total_amount'),
                DB::raw('COUNT(*) as transaction_count')
            )
            ->groupBy('fuel_grades.id', 'fuel_grades.name')
            ->orderBy('total_volume', 'desc')
            ->get();

        // If no data found, return sample data
        if ($productData->isEmpty()) {
            return [
                'labels' => ['Gasoline 95', 'Gasoline 91', 'Diesel', 'Petrol'],
                'data' => [45, 25, 20, 10],
                'colors' => ['#007bff', '#28a745', '#ffc107', '#dc3545'],
                'total_volume' => 100,
                'total_amount' => 2500
            ];
        }

        // Prepare data for chart
        $labels = [];
        $data = [];
        $colors = ['#007bff', '#28a745', '#ffc107', '#dc3545', '#6f42c1', '#fd7e14', '#20c997', '#e83e8c'];
        $totalVolume = 0;
        $totalAmount = 0;

        foreach ($productData as $index => $product) {
            $labels[] = $product->fuel_grade_name;
            $data[] = (float) $product->total_volume;
            $totalVolume += (float) $product->total_volume;
            $totalAmount += (float) $product->total_amount;
        }

        return [
            'labels' => $labels,
            'data' => $data,
            'colors' => array_slice($colors, 0, count($labels)),
            'total_volume' => $totalVolume,
            'total_amount' => $totalAmount,
            'raw_data' => $productData
        ];
    }

    /**
     * Get top sites by sales (volume and amount) over the last 30 days.
     */
    private function getTopSitesSalesData(): array
    {
        $startDate = now()->subDays(30)->startOfDay();
        $endDate = now()->endOfDay();

        $rows = PumpTransaction::query()
            ->whereBetween('date_time_start', [$startDate, $endDate])
            ->whereNotNull('station_id')
            ->whereNotNull('volume')
            ->whereNotNull('amount')
            ->join('stations', 'pump_transactions.station_id', '=', 'stations.id')
            ->select(
                'stations.id as station_id',
                'stations.site_name as station_name',
                DB::raw('SUM(pump_transactions.volume) as total_volume'),
                DB::raw('SUM(pump_transactions.amount) as total_amount')
            )
            ->groupBy('stations.id', 'stations.site_name')
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

        $labels = $rows->pluck('station_name')->all();
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
}
