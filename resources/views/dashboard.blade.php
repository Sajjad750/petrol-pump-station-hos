@extends('layouts.adminlte')

@push('css')
    <!-- Chart.js -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <!-- Leaflet CSS for Map -->
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />
    <style>
        
    </style>
@endpush

@section('content')
    <!-- Content Header (Page header) -->
    <div class="content-header">
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-sm-6">
                    <h1 class="m-0">Head Office System Dashboard</h1>
                </div>
                <div class="col-sm-6">
                    <ol class="breadcrumb float-sm-right">
                        <li class="breadcrumb-item"><a href="#">Home</a></li>
                        <li class="breadcrumb-item active">Dashboard</li>
                    </ol>
                </div>
            </div>
        </div>
    </div>

    <!-- Main content -->
    <section class="content">
        <div class="container-fluid">

            <!-- Site Status Overview Cards -->
            <div class="row">
                <div class="col-lg-3 col-6">
                    <div class="small-box bg-info">
                        <div class="inner">
                            <p>Total Sites</p>
                            <h3>{{ $totalStations }}</h3>
                                <!-- <p class="subtitle text-success">
                                    <i class="fas fa-arrow-up"></i> +2 this month
                                </p> -->
                        </div>
                        <div class="icon">
                            <i class="fas fa-map-marker-alt"></i>
                        </div>
                        <a href="#" class="small-box-footer">View All <i class="fas fa-arrow-circle-right"></i></a>
                    </div>
                </div>

                <div class="col-lg-3 col-6">
                    <div class="small-box bg-success">
                        <div class="inner">
                            
                            <p>Connected Sites</p>
                            <h3>{{ $onlineStations }}</h3>
                            <!-- <p class="subtitle text-success">
                                <i class="fas fa-check-circle"></i> 93.3% uptime
                            </p> -->
                        </div>
                        <div class="icon">
                            <i class="fas fa-heartbeat"></i>
                        </div>
                        <a href="#" class="small-box-footer">View Details <i class="fas fa-arrow-circle-right"></i></a>
                    </div>
                </div>

                <div class="col-lg-3 col-6">
                    <div class="small-box bg-warning">
                        <div class="inner">
                            
                            <p>Warning Sites</p>
                            <h3>{{ $warningStations }}</h3>
                            <!-- <p class="subtitle text-warning">
                                <i class="fas fa-exclamation-triangle"></i> 2.7% warning
                            </p> -->
                        </div>
                        <div class="icon">
                            <i class="fas fa-exclamation-triangle"></i>
                        </div>
                        <a href="#" class="small-box-footer">View Alerts <i class="fas fa-arrow-circle-right"></i></a>
                    </div>
                </div>

                <div class="col-lg-3 col-6">
                    <div class="small-box bg-danger">
                        <div class="inner">
                            
                            <p>Offline Sites</p>
                            <h3>{{ $offlineStations }}</h3>
                            <!-- <p class="subtitle text-danger">
                                <i class="fas fa-arrow-down"></i> -1 from yesterday
                            </p> -->
                        </div>
                        <div class="icon">
                            <i class="fas fa-exclamation-triangle"></i>
                        </div>
                        <a href="#" class="small-box-footer">View Details <i class="fas fa-arrow-circle-right"></i></a>
                    </div>
                </div>
            </div>


            <!-- Sales Summary Cards -->
            <!-- <div class="row">
                <div class="col-lg-6">
                    <div class="small-box bg-primary">
                        <div class="inner">
                            <h3>{{ number_format($salesData['total_volume'], 2) }} L</h3>
                            <p>Total Volume (Last 7 Days)</p>
                        </div>
                        <div class="icon">
                            <i class="fas fa-tint"></i>
                        </div>
                        <a href="#" class="small-box-footer">View Details <i class="fas fa-arrow-circle-right"></i></a>
                    </div>
                </div>

                <div class="col-lg-6">
                    <div class="small-box bg-success">
                        <div class="inner">
                            <h3>${{ number_format($salesData['total_amount'], 2) }}</h3>
                            <p>Total Amount (Last 7 Days)</p>
                        </div>
                        <div class="icon">
                            <i class="fas fa-dollar-sign"></i>
                        </div>
                        <a href="#" class="small-box-footer">View Details <i class="fas fa-arrow-circle-right"></i></a>
                    </div>
                </div>
            </div> -->

            <!-- Map View and Site Status -->
            <div class="row">
                <div class="col-lg-8">
                    <div class="card modern-card">
                        <div class="card-header modern-card-header">
                            <h3 class="card-title modern-card-title">
                                <i class="fas fa-map-marker-alt"></i> Station Network Map
                            </h3>
                            <div class="card-tools">
                                <button type="button" class="btn btn-tool modern-expand-btn" data-card-widget="collapse">
                                    <i class="fas fa-expand-arrows-alt"></i>
                                </button>
                            </div>
                        </div>
                        <div class="card-body modern-card-body">
                            <div class="map-container">
                                <div id="map" class="map-container-inner"></div>
                                
                                <!-- Status Summary Box -->
                                <div class="status-summary-box">
                                    <div class="summary-item">
                                        <span class="summary-label">Active</span>
                                        <span class="summary-value">{{ $onlineStations }}</span>
                                    </div>
                                    <div class="summary-item">
                                        <span class="summary-label">Offline</span>
                                        <span class="summary-value">{{ $offlineStations }}</span>
                                    </div>
                                </div>
                                
                                <!-- Status Legend -->
                                <div class="status-legend">
                                    <div class="legend-item">
                                        <div class="legend-dot online"></div>
                                        <span>Online</span>
                                    </div>
                                    <div class="legend-item">
                                        <div class="legend-dot offline"></div>
                                        <span>Offline</span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col-lg-4">
                    <div class="card modern-card">
                        <div class="card-header modern-card-header">
                            <h3 class="card-title modern-card-title">
                                <i class="fas fa-exclamation-triangle"></i> Recent Alarms
                            </h3>
                        </div>
                        <div class="card-body modern-card-body">
                            <div class="alarm-list">
                                @forelse($recentAlerts as $alert)
                                    @php
                                        // Build message & level from alert
                                        $level = 'low';
                                        if ($alert->device_type === 'Pump') {
                                            if ($alert->code == 1) { $message = 'Pump '.$alert->device_number.' offline state detected'; $level = 'high'; }
                                            elseif ($alert->code == 2) { $message = 'Pump '.$alert->device_number.' overfilling detected'; $level = 'medium'; }
                                            else { $message = 'Pump '.$alert->device_number.' notification'; }
                                        } else { // Probe
                                            switch ($alert->code) {
                                                case 1: $message = 'Probe '.$alert->device_number.' offline state detected'; $level='high'; break;
                                                case 2: $message = 'Probe '.$alert->device_number.' error detected'; $level='medium'; break;
                                                case 3: $message = 'Probe '.$alert->device_number.' critical high product level'; $level='high'; break;
                                                case 4: $message = 'Probe '.$alert->device_number.' high product level'; $level='medium'; break;
                                                case 5: $message = 'Probe '.$alert->device_number.' low product level'; $level='medium'; break;
                                                case 6: $message = 'Probe '.$alert->device_number.' critical low product level'; $level='high'; break;
                                                case 7: $message = 'Probe '.$alert->device_number.' high water level'; $level='medium'; break;
                                                case 8: $message = 'Probe '.$alert->device_number.' tank leakage detected'; $level='high'; break;
                                                default: $message = 'Probe '.$alert->device_number.' notification'; $level='low'; break;
                                            }
                                        }
                                    @endphp
                                    <div class="alarm-item">
                                        <div class="alarm-content">
                                            <div class="alarm-station">
                                                @if($alert->station)
                                                    <a href="{{ route('operations-monitor.station', $alert->station->id) }}">{{ $alert->station->site_name }}</a>
                                                @else
                                                    Station #{{ $alert->station_id ?? '-' }}
                                                @endif
                                            </div>
                                            <div class="alarm-description">{{ $message }}</div>
                                            <div class="alarm-time">{{ optional($alert->datetime)->diffForHumans() ?? '-' }}</div>
                                        </div>
                                        <div class="alarm-priority {{ $level }}">{{ $level }}</div>
                                    </div>
                                @empty
                                    <div class="text-muted small">No recent alarms</div>
                                @endforelse
                            </div>
                        </div>
                    </div>
                </div>
            </div>

                        <!-- Live Activity Section -->
                        <div class="row" style="margin-top: 2rem;">
                <!-- Live Site Activity Card -->
                <div class="col-lg-8">
                    <div class="card" style="border: none; border-radius: 12px; box-shadow: 0 1px 3px rgba(0,0,0,0.1);">
                        <div class="card-body" style="padding: 24px;">
                            <div class="d-flex justify-content-between align-items-center mb-3" style="border-bottom: 1px solid #E1E4ED;">
                                <div style="padding-bottom: 30px;">
                                    <h5 style="font-size: 16px; font-family:'DM sans', sans-serif; font-weight: 400; color: #6D758F; margin: 0;">Live Site Activity</h5>
                                    <div style="margin-top: 8px;">
                                        <span style="font-size: 20px; font-family:'DM sans', sans-serif; font-weight: 700; color: #19213D;">Live</span>
                                        <i class="fas fa-chevron-down" style="color: #027a48; font-size: 12px; margin-left: 4px;"></i>
                                    </div>
                                </div>
                                <div style="padding-bottom: 30px;">
                                    <select id="activityPeriodSelector" class="form-control" style="border: 1px solid #d0d5dd; border-radius: 8px; padding: 8px 12px; font-size: 14px;">
                                        <option value="daily">Daily</option>
                                        <option value="weekly" selected>Weekly</option>
                                        <option value="monthly">Monthly</option>
                                    </select>
                                </div>
                            </div>
                            <div style="height: 300px; position: relative;">
                                <canvas id="liveActivityChart"></canvas>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Stats Cards Column -->
                <div class="col-lg-4">
                    <!-- Total Transactions Card -->
                    <div class="card mb-3" style="border: none; border-radius: 12px; box-shadow: 0 1px 3px rgba(0,0,0,0.1);">
                        <div class="card-body" style="padding: 24px;">
                            <div class="d-flex justify-content-between align-items-start">
                                <div>
                                    <p style="font-size: 14px; color: #667085; margin: 0 0 8px 0;">Total Transactions</p>
                                    <h3 id="totalTransactionsValue" style="font-size: 36px; font-weight: 600; color: #101828; margin: 0;">{{ number_format($totalTransactions) }}</h3>
                                    <p id="recentTransactionsChange" style="font-size: 14px; color: #667085; margin: 8px 0 0 0;">+{{ number_format($recentTransactions) }} in last hour</p>
                                </div>
                                <div>
                                    <svg width="48" height="48" viewBox="0 0 48 48" fill="none" style="opacity: 0.5;">
                                        <path d="M6 24L18 12L30 24L42 12" stroke="#3b82f6" stroke-width="3" stroke-linecap="round" stroke-linejoin="round"/>
                                        <path d="M6 36L18 24L30 36L42 24" stroke="#3b82f6" stroke-width="3" stroke-linecap="round" stroke-linejoin="round"/>
                                    </svg>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Liters Sold Card -->
                    <div class="card" style="border: none; border-radius: 12px; box-shadow: 0 1px 3px rgba(0,0,0,0.1);">
                        <div class="card-body" style="padding: 24px;">
                            <div class="d-flex justify-content-between align-items-start">
                                <div>
                                    <p style="font-size: 14px; color: #667085; margin: 0 0 8px 0;">Liters Sold</p>
                                    <h3 id="totalLitersSoldValue" style="font-size: 36px; font-weight: 600; color: #101828; margin: 0;">{{ number_format($totalLitersSold) }}</h3>
                                    <p id="recentLitersChange" style="font-size: 14px; color: #667085; margin: 8px 0 0 0;">+{{ number_format($recentLiters, 0) }}L in last hour</p>
                                </div>
                                <div>
                                    <svg width="48" height="48" viewBox="0 0 48 48" fill="none" style="opacity: 0.5;">
                                        <path d="M6 24L18 12L30 24L42 12" stroke="#10b981" stroke-width="3" stroke-linecap="round" stroke-linejoin="round"/>
                                        <path d="M6 36L18 24L30 36L42 24" stroke="#10b981" stroke-width="3" stroke-linecap="round" stroke-linejoin="round"/>
                                    </svg>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>


            <!-- Analytics Charts Row 1 -->
            <div class="row" style=" margin-bottom:2rem; margin-top:1rem;">
                <div class="col-lg-6">
                    <div class="card" style="border: none; border-radius: 12px; box-shadow: 0 1px 3px rgba(0,0,0,0.1);">
                        <div class="card-body" style="padding: 24px;">
                            <div style="margin-bottom: 24px; border-bottom: 1px solid #E1E4ED;">
                                <p style="font-size: 16px; font-family:'DM sans', sans-serif; font-weight: 400; margin-top: 10px; color: #6D758F; margin: 0 0 4px 0;">Activity</p>
                                <h5 style="font-size: 20px; font-family:'DM sans', sans-serif; font-weight: 700; color: #19213D; margin: 0; padding-bottom:30px">Inventory forecast</h5>
                            </div>
                            <div id="inventoryForecastChart" style="display: flex; gap: 14px; align-items: flex-end; justify-content: space-between; min-height: 300px; padding: 10px 0 20px 0;">
                                <!-- Columns will be generated by JavaScript -->
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col-lg-6">
                    <div class="card" style="border: none; border-radius: 12px; box-shadow: 0 1px 3px rgba(0,0,0,0.1);">
                        <div class="card-body" style="padding: 24px;">
                            <div class="d-flex justify-content-between align-items-center mb-3" style="border-bottom: 1px solid #E1E4ED;">
                                <div style="padding-bottom: 30px;">
                                    <p style="font-size: 16px; font-family:'DM sans', sans-serif; font-weight: 400; margin-top: 10px; color: #6D758F; margin: 0 0 4px 0;">Statistics</p>
                                    <h5 style="font-size: 20px; font-family:'DM sans', sans-serif; font-weight: 700; color: #19213D; margin: 0;">Sales summary over time</h5>
                                </div>
                                <div style="padding-bottom: 30px;">
                                    <select id="salesSummaryPeriodSelector" class="form-control" style="border: 1px solid #d0d5dd; border-radius: 8px; padding: 8px 12px; font-size: 14px;">
                                        <option value="daily">Daily</option>
                                        <option value="weekly" selected>Weekly</option>
                                        <option value="monthly">Monthly</option>
                                    </select>
                                </div>
                            </div>
                            <div style="position: relative; height: 300px;">
                                <canvas id="salesSummaryChart"></canvas>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Analytics Charts Row 2 -->
            <div class="row" style="margin-bottom:2rem; border-bottom: 1px solid #e5e7eb;">
                <div class="col-lg-6">
                    <div class="card" style="border: none; border-radius: 12px; box-shadow: 0 1px 3px rgba(0,0,0,0.1);">
                        <div class="card-body" style="padding: 24px;">
                            <div class="d-flex justify-content-between align-items-center mb-3" style="border-bottom: 1px solid #E1E4ED;">
                                <div style="padding-bottom: 30px;">
                                    <p style="font-size: 16px; font-family:'DM sans', sans-serif; font-weight: 400; margin-top: 10px; color: #6D758F; margin: 0 0 4px 0;">Statistics</p>
                                    <h5 style="font-size: 20px; font-family:'DM sans', sans-serif; font-weight: 700; color: #19213D; margin: 0;">Product Sales</h5>
                                </div>
                                <div style="padding-bottom: 30px;">
                                    <select id="productSalesPeriodSelector" class="form-control" style="border: 1px solid #d0d5dd; border-radius: 8px; padding: 8px 12px; font-size: 14px;">
                                        <option value="daily">Daily</option>
                                        <option value="weekly" selected>Weekly</option>
                                        <option value="monthly">Monthly</option>
                                    </select>
                                </div>
                            </div>
                            <div style="position: relative; height: 300px;">
                                <canvas id="productSalesChart"></canvas>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col-lg-6">
                    <div class="card" style="border: none; border-radius: 12px; box-shadow: 0 1px 3px rgba(0,0,0,0.1);">
                        <div class="card-body" style="padding: 24px;">
                            <div class="d-flex justify-content-between align-items-center mb-3" style="border-bottom: 1px solid #E1E4ED;">
                                <div style="padding-bottom: 30px;">
                                    <p style="font-size: 16px; font-family:'DM sans', sans-serif; font-weight: 400; margin-top: 10px; color: #6D758F; margin: 0 0 4px 0;">Statistics</p>
                                    <h5 style="font-size: 20px; font-family:'DM sans', sans-serif; font-weight: 700; color: #19213D; margin: 0;">Top Sites in Sales</h5>
                                </div>
                                <div style="padding-bottom: 30px;">
                                    <select id="topSitesPeriodSelector" class="form-control" style="border: 1px solid #d0d5dd; border-radius: 8px; padding: 8px 12px; font-size: 14px;">
                                        <option value="daily">Daily</option>
                                        <option value="weekly">Weekly</option>
                                        <option value="monthly" selected>Month</option>
                                    </select>
                                </div>
                            </div>
                            <div style="position: relative; height: 300px;">
                                <canvas id="topSitesChart"></canvas>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Site Details Table -->
            <div class="row">
                <div class="col-12">
                    <div class="card">
                        <div class="card-header">
                            <h3 class="card-title">
                                <i class="fas fa-list"></i> Site Status Overview
                            </h3>
                            <div class="card-tools">
                                <button type="button" class="btn btn-tool" data-card-widget="collapse">
                                    <i class="fas fa-minus"></i>
                                </button>
                            </div>
                        </div>
                        <div class="card-body">
                            <div class="table-responsive">
                                <table class="modern-sites-table" id="sitesTable">
                                    <thead>
                                        <tr>
                                            <th>Site Code</th>
                                            <th>Site Name</th>
                                            <th>Status</th>
                                            <th>Last Connected</th>
                                            <th>Pumps (Online/Total)</th>
                                            <th>Tanks (Online/Total)</th>
                                            <th>Alerts</th>
                                            <th>Actions</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @forelse($stations as $station)
                                            @php
                                                $status = $station->isOnline() ? ['class' => 'status-online', 'text' => 'Online', 'icon' => 'fas fa-clock'] : 
                                                         ($station->hasWarning() ? ['class' => 'status-warning', 'text' => 'Warning', 'icon' => 'fas fa-exclamation-triangle'] : 
                                                         ['class' => 'status-offline', 'text' => 'Offline', 'icon' => 'fas fa-eye-slash']);
                                                $lastSync = $station->last_sync_at ? $station->last_sync_at->diffForHumans() : 'Never';
                                                $pumpCount = $station->pumps->count();
                                                $activePumps = $station->pumps->where('is_active', true)->count();
                                                $pumpPercentage = $pumpCount > 0 ? round(($activePumps / $pumpCount) * 100) : 0;
                                                $progressColor = $pumpPercentage >= 80 ? '#3b82f6' : ($pumpPercentage >= 50 ? '#f59e0b' : '#ef4444');
                                            @endphp
                                            <tr>
                                                <td>
                                                    <div class="site-code">
                                                        <div class="site-code-primary">{{ $station->pts_id ?? 'N/A' }}</div>
                                                        <div class="site-code-ref">Ref: {{ str_pad($station->id, 3, '0', STR_PAD_LEFT) }}</div>
                                                    </div>
                                                </td>
                                                <td>{{ $station->site_name }}</td>
                                                <td>
                                                    <span class="status-badge {{ $status['class'] }}">
                                                        <i class="{{ $status['icon'] }}"></i>
                                                        {{ $status['text'] }}
                                                    </span>
                                                </td>
                                                <td>{{ $lastSync }}</td>
                                                <td>
                                                    <div class="pumps-info">
                                                        <span class="pumps-count">{{ $activePumps }}/{{ $pumpCount }}</span>
                                                        <div class="pumps-progress">
                                                            <div class="progress-bar" style="width: {{ $pumpPercentage }}%; background-color: {{ $progressColor }};"></div>
                                                        </div>
                                                        <span class="pumps-percentage">{{ $pumpPercentage }}%</span>
                                                    </div>
                                                </td>
                                                <td>{{ $station->tankMeasurements->count() }}/{{ $station->tankMeasurements->count() }}</td>
                                                @php
                                                    $alertsTotal = $station->alerts_count ?? 0;
                                                    $alertsUnread = $station->unread_alerts_count ?? 0;
                                                    $alertBadgeClass = $alertsUnread > 0
                                                        ? 'badge badge-danger'
                                                        : ($alertsTotal > 0 ? 'badge badge-warning' : 'badge badge-success');
                                                    $alertLabel = $alertsUnread > 0
                                                        ? "{$alertsUnread}/{$alertsTotal}"
                                                        : $alertsTotal;
                                                @endphp
                                                <td><span class="{{ $alertBadgeClass }}">{{ $alertLabel }}</span></td>
                                                <td>
                                                    <button class="btn btn-sm btn-primary station-details-btn" data-station-id="{{ $station->id }}">
                                                        <i class="fas fa-eye"></i> View
                                                    </button>
                                                </td>
                                            </tr>
                                        @empty
                                            <tr>
                                                <td colspan="8" class="text-center">No stations found</td>
                                            </tr>
                                        @endforelse
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

        </div>
    </section>

    <!-- Station Details Modal -->
    <div class="modal fade" id="stationDetailsModal" tabindex="-1" role="dialog" aria-labelledby="stationDetailsModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="stationDetailsModalLabel">Station Details</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <div id="stationDetailsContent">
                        <div class="text-center">
                            <div class="spinner-border" role="status">
                                <span class="sr-only">Loading...</span>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('js')
    <!-- Leaflet JS for Map -->
    <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>

    <script>
        $(document).ready(function() {
            console.log('Dashboard JavaScript initialized');
            
            // Initialize DataTable
            $('#sitesTable').DataTable({
                "responsive": true,
                "lengthChange": false,
                "autoWidth": false,
                "pageLength": 10,
                "order": [
                    [0, "asc"]
                ]
            });

            // Live Activity Chart
            let liveActivityChart = null;
            
            function initLiveActivityChart(data) {
                const ctx = document.getElementById('liveActivityChart').getContext('2d');
                
                // Destroy existing chart if it exists
                if (liveActivityChart) {
                    liveActivityChart.destroy();
                }
                
                liveActivityChart = new Chart(ctx, {
                    type: 'line',
                    data: {
                        labels: data.labels,
                        datasets: [{
                            label: 'Volume (Liters)',
                            data: data.volumes,
                            borderColor: '#3b82f6',
                            backgroundColor: 'rgba(59, 130, 246, 0.1)',
                            borderWidth: 2,
                            fill: true,
                            tension: 0.4,
                            pointRadius: 4,
                            pointHoverRadius: 6,
                            pointBackgroundColor: '#3b82f6',
                            pointBorderColor: '#fff',
                            pointBorderWidth: 2
                        }]
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        plugins: {
                            legend: {
                                display: false
                            },
                            tooltip: {
                                backgroundColor: '#1e40af',
                                titleColor: '#fff',
                                bodyColor: '#fff',
                                padding: 12,
                                cornerRadius: 8,
                                displayColors: false,
                                callbacks: {
                                    title: function(context) {
                                        return context[0].label;
                                    },
                                    label: function(context) {
                                        return context.parsed.y.toLocaleString('en-US', {minimumFractionDigits: 2, maximumFractionDigits: 2}) + ' L';
                                    }
                                }
                            }
                        },
                        scales: {
                            y: {
                                beginAtZero: true,
                                grid: {
                                    color: '#f3f4f6',
                                    drawBorder: false
                                },
                                ticks: {
                                    color: '#667085',
                                    font: {
                                        size: 12
                                    },
                                    callback: function(value) {
                                        if (value >= 1000) {
                                            return (value / 1000).toFixed(1) + 'k';
                                        }
                                        return value;
                                    }
                                }
                            },
                            x: {
                                grid: {
                                    display: false,
                                    drawBorder: false
                                },
                                ticks: {
                                    color: '#667085',
                                    font: {
                                        size: 12,
                                        weight: '500'
                                    }
                                }
                            }
                        },
                        interaction: {
                            intersect: false,
                            mode: 'index'
                        }
                    }
                });
            }
            
            // Initialize with weekly data
            initLiveActivityChart({
                labels: @json($liveActivityData['labels']),
                volumes: @json($liveActivityData['volumes']),
                amounts: @json($liveActivityData['amounts'])
            });
            
            // Handle period selector change
            $('#activityPeriodSelector').on('change', function() {
                const period = $(this).val();
                
                $.ajax({
                    url: '{{ route('dashboard.live-activity') }}',
                    method: 'GET',
                    data: { period: period },
                    success: function(response) {
                        // Update chart
                        initLiveActivityChart(response.chartData);
                        
                        // Update stats cards
                        $('#totalTransactionsValue').text(response.totalTransactions.toLocaleString());
                        $('#totalLitersSoldValue').text(response.totalLitersSold.toLocaleString());
                        $('#recentTransactionsChange').text('+' + response.recentTransactions.toLocaleString() + ' in last hour');
                        $('#recentLitersChange').text('+' + response.recentLiters.toLocaleString() + 'L in last hour');
                    },
                    error: function(xhr) {
                        console.error('Error loading live activity data:', xhr);
                    }
                });
            });

            // Initialize Map with dynamic center based on station locations
            @if($stations->filter(function($station) { return $station->hasCoordinates(); })->count() > 0)
                @php
                    $stationsWithCoords = $stations->filter(function($station) { return $station->hasCoordinates(); });
                    $avgLat = $stationsWithCoords->avg('latitude');
                    $avgLng = $stationsWithCoords->avg('longitude');
                @endphp
                var map = L.map('map').setView([{{ $avgLat }}, {{ $avgLng }}], 10);
            @else
                // Fallback to default location if no stations have coordinates
                var map = L.map('map').setView([40.7128, -74.0060], 10);
            @endif

            L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
                attribution: '© OpenStreetMap contributors'
            }).addTo(map);

            // Add dynamic markers for stations with coordinates
            var sites = [
                @foreach($stations as $station)
                    @if($station->hasCoordinates())
                    {
                        id: {{ $station->id }},
                        name: "{{ $station->site_name }}",
                        lat: {{ $station->latitude }},
                        lng: {{ $station->longitude }},
                        status: "{{ $station->isOnline() ? 'online' : ($station->hasWarning() ? 'warning' : 'offline') }}",
                        pts_id: "{{ $station->pts_id ?? 'N/A' }}",
                        lastSync: "{{ $station->last_sync_at ? $station->last_sync_at->diffForHumans() : 'Never' }}",
                        battery: {{ $station->battery_voltage ?? 'null' }},
                        cpuTemp: {{ $station->cpu_temperature ?? 'null' }}
                    },
                    @endif
                @endforeach
            ];

            console.log('Loading ' + sites.length + ' stations on map');

            sites.forEach(function(site) {
                var color = site.status === 'online' ? 'green' : site.status === 'warning' ? 'orange' : 'red';
                var marker = L.circleMarker([site.lat, site.lng], {
                    color: color,
                    fillColor: color,
                    fillOpacity: 0.7,
                    radius: 8
                }).addTo(map);

                marker.bindPopup(`
                    <div style="min-width: 200px;">
                        <h6><strong>${site.name}</strong></h6>
                        <p><strong>Site Code:</strong> ${site.pts_id}</p>
                        <p><strong>Status:</strong> <span style="color: ${color}; font-weight: bold;">${site.status.charAt(0).toUpperCase() + site.status.slice(1)}</span></p>
                        <p><strong>Last Sync:</strong> ${site.lastSync}</p>
                        ${site.battery ? `<p><strong>Battery:</strong> ${site.battery} mV</p>` : ''}
                        ${site.cpuTemp ? `<p><strong>CPU Temp:</strong> ${site.cpuTemp}°C</p>` : ''}
                        <button class="btn btn-sm btn-primary station-details-btn" data-station-id="${site.id}" style="margin-top: 5px; width: 100%;">
                            <i class="fas fa-eye"></i> View Full Details
                        </button>
                    </div>
                `);
            });

            // Inventory Forecast Chart - Funnel Layout (Matching Image Exactly)
            function renderInventoryForecastChart(data) {
                const container = document.getElementById('inventoryForecastChart');
                container.innerHTML = '';
                
                const categories = [
                    { key: 'dry_out', label: 'DRY OUT', highlight: false },
                    { key: 'days_1_2', label: '1-2 DAYS', highlight: false },
                    { key: 'days_3_5', label: '3-5 DAYS', highlight: false },
                    { key: 'days_6_8', label: '6-8 DAYS', highlight: true }, // Highlight this one
                    { key: 'days_9_12', label: '9-12 DAYS', highlight: false },
                ];
                
                const maxCount = Math.max(...Object.values(data.categories), 1);
                const baseHeight = 312; // Base height for the funnel bars
                
                categories.forEach(function(category, index) {
                    const count = data.categories[category.key] || 0;
                    const fillPercentage = maxCount > 0 ? (count / maxCount) * 100 : 0;
                    const isHighlighted = category.highlight && count > 0;
                    
                    // Create column wrapper
                    const column = document.createElement('div');
                    column.style.cssText = 'flex: 1; display: flex; flex-direction: column; align-items: center; justify-content: flex-end; min-width: 0; max-width: 110px;';
                    
                    // Funnel bar container - rounded rectangle with light gray border
                    const barContainer = document.createElement('div');
                    barContainer.style.cssText = 'width: 60%; height: ' + baseHeight + 'px; position: relative; background-color: #ffffff; border: 1px solid #e5e7eb; border-radius: 24px; overflow: hidden; display: flex; align-items: flex-end; box-shadow: 0 0 0 1px rgba(0,0,0,0.02);';
                    
                    // Fill bar - blue gradient fill (funnel effect)
                    const fillBar = document.createElement('div');
                    const fillHeight = Math.max(40, (fillPercentage / 100) * baseHeight); // Minimum 40px height for text visibility
                    
                    // Color scheme: use specified rgba colors with opacity
                    const gradientStart = 'rgba(195, 221, 255, 1)'; // Light blue
                    const gradientEnd = 'rgba(141, 193, 255, 1)';   // Darker blue
                    
                    fillBar.style.cssText = 'width: 100%; height: ' + fillHeight + 'px; background: linear-gradient(180deg, ' + gradientStart + ' 0%, ' + gradientEnd + ' 100%); border-radius: 0 0 11px 11px; transition: height 0.6s cubic-bezier(0.4, 0, 0.2, 1); position: relative; display: flex; align-items: flex-start; justify-content: center; padding-top: 8px; opacity: 0.8;';
                    
                    // Add subtle shine effect
                    fillBar.style.boxShadow = 'inset 0 1px 2px rgba(255,255,255,0.2), inset 0 -1px 2px rgba(0,0,0,0.1)';
                    
                    // Tank count text - at the top of the funnel bar
                    const tankCountText = document.createElement('div');
                    tankCountText.textContent = count + ' TANKS';
                    tankCountText.style.cssText = 'font-size: 12px; font-weight: 400; color: #000000; text-align: center; line-height: 1.2; white-space: nowrap; letter-spacing: -0.01em; text-shadow: 0 1px 2px rgba(0,0,0,0.2); width: 100%;';
                    
                    fillBar.appendChild(tankCountText);
                    barContainer.appendChild(fillBar);
                    
                    // Bottom text - Time period label
                    const bottomText = document.createElement('div');
                    bottomText.textContent = category.label;
                    bottomText.style.cssText = 'font-size: 11px; font-weight: 500; color: #667085; margin-top: 8px; text-align: center; line-height: 1.3; white-space: nowrap; letter-spacing: 0.01em;';
                    
                    // Assemble column
                    column.appendChild(barContainer);
                    column.appendChild(bottomText);
                    
                    container.appendChild(column);
                });
            }
            
            // Initialize inventory forecast chart
            const inventoryForecastData = @json($inventoryForecastData);
            renderInventoryForecastChart(inventoryForecastData);

            // Sales Summary Chart (Line Chart) - Dynamic Data
            let salesSummaryChart = null;
            
            function initSalesSummaryChart(data) {
                const ctx = document.getElementById('salesSummaryChart').getContext('2d');
                
                // Destroy existing chart if it exists
                if (salesSummaryChart) {
                    salesSummaryChart.destroy();
                }
                
                salesSummaryChart = new Chart(ctx, {
                    type: 'line',
                    data: {
                        labels: data.labels,
                        datasets: [{
                            label: 'Sales Volume',
                            data: data.volume,
                            borderColor: '#3b82f6',
                            backgroundColor: 'rgba(59, 130, 246, 0.1)',
                            borderWidth: 2,
                            fill: true,
                            tension: 0.4,
                            pointRadius: 4,
                            pointHoverRadius: 6,
                            pointBackgroundColor: '#3b82f6',
                            pointBorderColor: '#fff',
                            pointBorderWidth: 2
                        }]
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        plugins: {
                            legend: {
                                display: false
                            },
                            tooltip: {
                                backgroundColor: '#1e40af',
                                titleColor: '#fff',
                                bodyColor: '#fff',
                                padding: 12,
                                cornerRadius: 8,
                                displayColors: false,
                                callbacks: {
                                    title: function(context) {
                                        return context[0].label;
                                    },
                                    label: function(context) {
                                        const value = context.parsed.y || 0;
                                        // Format as "2.8K" style
                                        if (value >= 1000) {
                                            return (value / 1000).toFixed(1) + 'K';
                                        }
                                        return value.toFixed(2);
                                    }
                                }
                            }
                        },
                        scales: {
                            y: {
                                beginAtZero: true,
                                grid: {
                                    color: '#f3f4f6',
                                    drawBorder: false,
                                    borderDash: [5, 5]
                                },
                                ticks: {
                                    color: '#667085',
                                    font: {
                                        size: 12,
                                        family: "'DM Sans', sans-serif"
                                    },
                                    callback: function(value) {
                                        // Format Y-axis labels as "0.4K", "0.3K", etc.
                                        if (value >= 1000) {
                                            return (value / 1000).toFixed(1) + 'K';
                                        } else if (value >= 100) {
                                            return (value / 1000).toFixed(2) + 'K';
                                        } else if (value >= 10) {
                                            return (value / 1000).toFixed(3) + 'K';
                                        } else {
                                            return (value / 1000).toFixed(3) + 'K';
                                        }
                                    }
                                }
                            },
                            x: {
                                grid: {
                                    display: false,
                                    drawBorder: false
                                },
                                ticks: {
                                    color: '#667085',
                                    font: {
                                        size: 12,
                                        weight: '500',
                                        family: "'DM Sans', sans-serif"
                                    }
                                }
                            }
                        },
                        interaction: {
                            intersect: false,
                            mode: 'index'
                        }
                    }
                });
            }
            
            // Initialize with weekly data
            initSalesSummaryChart({
                labels: @json($salesData['labels']),
                volume: @json($salesData['volume'])
            });
            
            // Handle period selector change
            $('#salesSummaryPeriodSelector').on('change', function() {
                const period = $(this).val();
                
                $.ajax({
                    url: '{{ route('dashboard.sales-summary') }}',
                    method: 'GET',
                    data: { period: period },
                    success: function(response) {
                        initSalesSummaryChart(response);
                    },
                    error: function(xhr) {
                        console.error('Error loading sales summary data:', xhr);
                    }
                });
            });

            // Product Sales Chart (Donut Chart) - Dynamic Data with Center Text
            let productSalesChart = null;
            let selectedProduct = null; // Store selected product info
            
            function initProductSalesChart(data) {
                const ctx = document.getElementById('productSalesChart').getContext('2d');
                
                // Destroy existing chart if it exists
                if (productSalesChart) {
                    productSalesChart.destroy();
                }
                
                // Reset selected product when chart is reinitialized
                selectedProduct = null;
                
                // Handle empty data
                let chartLabels = data.labels || [];
                let chartData = data.data || [];
                let chartColors = data.colors || [];
                
                if (chartData.length === 0 || chartLabels.length === 0) {
                    // Show empty state with default data
                    chartLabels = [];
                    chartData = [];
                    chartColors = [];
                }
                
                productSalesChart = new Chart(ctx, {
                    type: 'doughnut',
                    data: {
                        labels: chartLabels,
                        datasets: [{
                            data: chartData.length > 0 ? chartData : [1], // Show a single segment if empty
                            backgroundColor: chartColors.length > 0 ? chartColors : ['#e5e7eb'], // Light gray if empty
                            borderWidth: 2,
                            borderColor: '#ffffff'
                        }]
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        cutout: '70%',
                        onClick: function(event, activeElements) {
                            if (activeElements.length > 0) {
                                const index = activeElements[0].index;
                                const dataset = this.data.datasets[0];
                                const label = this.data.labels[index];
                                const value = dataset.data[index];
                                
                                // Store selected product
                                selectedProduct = {
                                    label: label,
                                    value: value
                                };
                                
                                // Update chart to show selected product in center
                                this.update();
                            } else {
                                // Clicked outside - clear selection
                                selectedProduct = null;
                                this.update();
                            }
                        },
                        plugins: {
                            legend: {
                                display: chartLabels.length > 0, // Only show legend if there are labels
                                position: 'bottom',
                                labels: {
                                    padding: 15,
                                    usePointStyle: true,
                                    pointStyle: 'circle',
                                    font: {
                                        family: "'DM Sans', sans-serif",
                                        size: 12
                                    },
                                    color: '#19213D',
                                    generateLabels: function(chart) {
                                        const chartData = chart.data;
                                        if (chartData.labels && chartData.labels.length && chartData.datasets.length) {
                                            return chartData.labels.map((label, i) => {
                                                const dataset = chartData.datasets[0];
                                                const value = dataset.data[i];
                                                return {
                                                    text: label,
                                                    fillStyle: dataset.backgroundColor[i],
                                                    strokeStyle: dataset.borderColor,
                                                    lineWidth: dataset.borderWidth,
                                                    hidden: isNaN(value) || value === 0,
                                                    index: i
                                                };
                                            });
                                        }
                                        return [];
                                    }
                                }
                            },
                            tooltip: {
                                callbacks: {
                                    label: function(context) {
                                        let label = context.label || '';
                                        if (label) {
                                            label += ': ';
                                        }
                                        const total = context.dataset.data.reduce((a, b) => a + b, 0);
                                        const percentage = total > 0 ? ((context.parsed / total) * 100).toFixed(1) : 0;
                                        label += context.parsed.toFixed(2) + ' L (' + percentage + '%)';
                                        return label;
                                    }
                                }
                            }
                        },
                        onHover: (event, activeElements) => {
                            event.native.target.style.cursor = activeElements.length > 0 ? 'pointer' : 'default';
                        }
                    },
                    plugins: [{
                        id: 'centerText',
                        beforeDraw: function(chart) {
                            // Only show center text if a product is selected
                            if (!selectedProduct) {
                                return; // Don't show anything if nothing is selected
                            }
                            
                            const ctx = chart.ctx;
                            const centerX = chart.chartArea.left + (chart.chartArea.right - chart.chartArea.left) / 2;
                            const centerY = chart.chartArea.top + (chart.chartArea.bottom - chart.chartArea.top) / 2;
                            
                            // Get selected product value
                            const productValue = selectedProduct.value;
                            
                            // Format: if >= 1000, show as "XK", otherwise show exact value with 2 decimal places
                            let displayVal;
                            if (productValue >= 1000000) {
                                displayVal = (productValue / 1000000).toFixed(1) + 'M';
                            } else if (productValue >= 1000) {
                                displayVal = (productValue / 1000).toFixed(1) + 'K';
                            } else {
                                displayVal = productValue.toFixed(2);
                            }
                            
                            ctx.save();
                            // Product name
                            ctx.font = '400 14px "DM Sans", sans-serif';
                            ctx.fillStyle = '#6D758F';
                            ctx.textAlign = 'center';
                            ctx.textBaseline = 'middle';
                            ctx.fillText(selectedProduct.label, centerX, centerY - 25);
                            
                            // Product value
                            ctx.font = 'bold 24px "DM Sans", sans-serif';
                            ctx.fillStyle = '#19213D';
                            ctx.fillText(displayVal, centerX, centerY - 5);
                            
                            // Unit label
                            ctx.font = '400 12px "DM Sans", sans-serif';
                            ctx.fillStyle = '#6D758F';
                            ctx.fillText('Liters', centerX, centerY + 15);
                            ctx.restore();
                        }
                    }]
                });
            }
            
            // Initialize with weekly data
            initProductSalesChart({
                labels: @json($productDistributionData['labels']),
                data: @json($productDistributionData['data']),
                colors: @json($productDistributionData['colors']),
                total_volume: @json($productDistributionData['total_volume'] ?? 0),
                total_transactions: @json($productDistributionData['total_transactions'] ?? 0)
            });
            
            // Handle period selector change
            $('#productSalesPeriodSelector').on('change', function() {
                const period = $(this).val();
                
                $.ajax({
                    url: '{{ route('dashboard.product-sales') }}',
                    method: 'GET',
                    data: { period: period },
                    success: function(response) {
                        initProductSalesChart(response);
                    },
                    error: function(xhr) {
                        console.error('Error loading product sales data:', xhr);
                    }
                });
            });

            // Top Sites Chart (Horizontal Bar Chart) - Dynamic Data
            let topSitesChart = null;
            
            function initTopSitesChart(data) {
                const ctx = document.getElementById('topSitesChart').getContext('2d');
                
                // Destroy existing chart if it exists
                if (topSitesChart) {
                    topSitesChart.destroy();
                }
                
                const labels = data.labels || [];
                const volumes = data.volume || [];
                
                // Calculate max value for x-axis scale (100K or max volume, whichever is higher)
                const maxVolume = Math.max(...volumes, 0);
                const maxValue = Math.max(100000, maxVolume);
                
                topSitesChart = new Chart(ctx, {
                    type: 'bar',
                    data: {
                        labels: labels,
                        datasets: [{
                            label: 'Sales Volume (Liters)',
                            data: volumes,
                            backgroundColor: '#3b82f6',
                            borderColor: '#3b82f6',
                            borderWidth: 0,
                            borderRadius: 4,
                            barThickness: 16
                        }]
                    },
                    options: {
                        indexAxis: 'y',
                        responsive: true,
                        maintainAspectRatio: false,
                        plugins: {
                            legend: { display: false },
                            tooltip: {
                                enabled: true,
                                callbacks: {
                                    label: function(context) {
                                        const value = context.parsed.x || 0;
                                        // Format as "50.000" style (3 decimal places, no thousand separator)
                                        return value.toFixed(3);
                                    }
                                },
                                backgroundColor: '#1e40af',
                                titleColor: '#fff',
                                bodyColor: '#fff',
                                padding: 12,
                                cornerRadius: 8,
                                displayColors: false
                            }
                        },
                        scales: {
                            x: {
                                beginAtZero: true,
                                max: maxValue,
                                grid: { 
                                    display: true, 
                                    color: '#e5e7eb', 
                                    lineWidth: 1,
                                    drawBorder: false
                                },
                                ticks: {
                                    stepSize: maxValue / 4, // 0, 25K, 50K, 75K, 100K
                                    callback: function(value) {
                                        if (value >= 1000) {
                                            return (value / 1000).toFixed(0) + 'K';
                                        }
                                        return value;
                                    },
                                    color: '#667085',
                                    font: {
                                        size: 12,
                                        family: "'DM Sans', sans-serif"
                                    }
                                }
                            },
                            y: { 
                                grid: { display: false, drawBorder: false },
                                ticks: {
                                    color: '#19213D',
                                    font: {
                                        size: 12,
                                        family: "'DM Sans', sans-serif",
                                        weight: '500'
                                    }
                                }
                            }
                        }
                    }
                });
            }
            
            // Initialize with monthly data
            initTopSitesChart({
                labels: @json($topSitesSales['labels'] ?? []),
                volume: @json($topSitesSales['volume'] ?? []),
                amount: @json($topSitesSales['amount'] ?? [])
            });
            
            // Handle period selector change
            $('#topSitesPeriodSelector').on('change', function() {
                const period = $(this).val();
                
                $.ajax({
                    url: '{{ route('dashboard.top-sites') }}',
                    method: 'GET',
                    data: { period: period },
                    success: function(response) {
                        initTopSitesChart(response);
                    },
                    error: function(xhr) {
                        console.error('Error loading top sites data:', xhr);
                        // Fallback to default data
                        initTopSitesChart({
                            labels: @json($topSitesSales['labels'] ?? []),
                            volume: @json($topSitesSales['volume'] ?? []),
                            amount: @json($topSitesSales['amount'] ?? [])
                        });
                    }
                });
            });

            // Auto-refresh data every 30 seconds
            setInterval(function() {
                // Simulate real-time data updates
                console.log('Refreshing dashboard data...');
                // In a real implementation, this would make AJAX calls to update the data
            }, 30000);
        });

        // Station Details Modal functionality
        $(document).on('click', '.station-details-btn', function(e) {
            e.preventDefault();
            e.stopPropagation();
            
            const stationId = $(this).data('station-id');
            console.log('Station details button clicked for station ID:', stationId);
            
            $('#stationDetailsModal').modal('show');
            
            // Load station details
            $.ajax({
                url: '{{ route("dashboard.station.details", ":id") }}'.replace(':id', stationId),
                method: 'GET',
                success: function(response) {
                    console.log('Station details loaded:', response);
                    displayStationDetails(response);
                },
                error: function(xhr) {
                    console.error('Error loading station details:', xhr);
                    $('#stationDetailsContent').html(`
                        <div class="alert alert-danger">
                            <h5>Error Loading Station Details</h5>
                            <p>Unable to load station information. Please try again.</p>
                            <p>Error: ${xhr.status} - ${xhr.statusText}</p>
                        </div>
                    `);
                }
            });
        });

        function displayStationDetails(data) {
            const station = data.station;
            const status = data.status;
            
            let html = `
                <div class="row">
                    <div class="col-md-6">
                        <h6><strong>Basic Information</strong></h6>
                        <table class="table table-sm">
                            <tr><td><strong>Site Code:</strong></td><td>${station.pts_id || 'N/A'}</td></tr>
                            <tr><td><strong>Site Name:</strong></td><td>${station.site_name}</td></tr>
                            <tr><td><strong>Status:</strong></td><td><span class="status-indicator ${status.class}"></span>${status.text}</td></tr>
                            <tr><td><strong>Last Sync:</strong></td><td>${data.lastSync}</td></tr>
                            <tr><td><strong>Pumps:</strong></td><td>${data.activePumps}/${data.pumpCount}</td></tr>
                        </table>
                    </div>
                    <div class="col-md-6">
                        <h6><strong>Location Information</strong></h6>
                        <table class="table table-sm">
                            <tr><td><strong>Address:</strong></td><td>${station.address || 'N/A'}</td></tr>
                            <tr><td><strong>City:</strong></td><td>${station.city || 'N/A'}</td></tr>
                            <tr><td><strong>Region:</strong></td><td>${station.region || 'N/A'}</td></tr>
                            <tr><td><strong>Country:</strong></td><td>${station.country || 'N/A'}</td></tr>
                            <tr><td><strong>Phone:</strong></td><td>${station.phone || 'N/A'}</td></tr>
                        </table>
                    </div>
                </div>
            `;

            // Add device information if available
            if (station.battery_voltage || station.cpu_temperature || station.unique_identifier || station.utc_offset) {
                // Determine badge classes for battery and CPU
                const batteryClass = station.battery_voltage ? 
                    (station.battery_voltage > 12000 ? 'badge-success' : 
                     station.battery_voltage > 11000 ? 'badge-warning' : 'badge-danger') : '';
                
                const cpuClass = station.cpu_temperature ? 
                    (station.cpu_temperature < 60 ? 'badge-success' : 
                     station.cpu_temperature < 80 ? 'badge-warning' : 'badge-danger') : '';

                html += `
                    <hr>
                    <div class="row">
                        <div class="col-md-12">
                            <h6><strong>Device Information</strong></h6>
                            <table class="table table-sm">
                                <tr>
                                    <td><strong>Battery Voltage:</strong></td>
                                    <td>
                                        ${station.battery_voltage ? 
                                            `<span class="badge ${batteryClass}">${station.battery_voltage} mV</span>` : 
                                            '<span class="text-muted">N/A</span>'
                                        }
                                    </td>
                                </tr>
                                <tr>
                                    <td><strong>CPU Temperature:</strong></td>
                                    <td>
                                        ${station.cpu_temperature ? 
                                            `<span class="badge ${cpuClass}">${station.cpu_temperature}°C</span>` : 
                                            '<span class="text-muted">N/A</span>'
                                        }
                                    </td>
                                </tr>
                                <tr><td><strong>Unique ID:</strong></td><td>${station.unique_identifier || 'N/A'}</td></tr>
                                <tr><td><strong>UTC Offset:</strong></td><td>${station.utc_offset || 'N/A'}</td></tr>
                            </table>
                        </div>
                    </div>
                `;
            }

            // Add firmware information if available
            if (station.firmware_information) {
                html += `
                    <hr>
                    <div class="row">
                        <div class="col-md-12">
                            <h6><strong>Firmware Information</strong></h6>
                            <div class="card">
                                <div class="card-body">
                                    <pre class="mb-0" style="max-height: 200px; overflow-y: auto;">${JSON.stringify(station.firmware_information, null, 2)}</pre>
                                </div>
                            </div>
                        </div>
                    </div>
                `;
            }

            // Add network settings if available
            if (station.network_settings) {
                html += `
                    <hr>
                    <div class="row">
                        <div class="col-md-12">
                            <h6><strong>Network Settings</strong></h6>
                            <div class="card">
                                <div class="card-body">
                                    <pre class="mb-0" style="max-height: 200px; overflow-y: auto;">${JSON.stringify(station.network_settings, null, 2)}</pre>
                                </div>
                            </div>
                        </div>
                    </div>
                `;
            }

            // Add server configuration if available
            if (station.remote_server_configuration) {
                html += `
                    <hr>
                    <div class="row">
                        <div class="col-md-12">
                            <h6><strong>Remote Server Configuration</strong></h6>
                            <div class="card">
                                <div class="card-body">
                                    <pre class="mb-0" style="max-height: 200px; overflow-y: auto;">${JSON.stringify(station.remote_server_configuration, null, 2)}</pre>
                                </div>
                            </div>
                        </div>
                    </div>
                `;
            }

            $('#stationDetailsContent').html(html);
        }
    </script>
@endpush
