@extends('layouts.adminlte')

@push('css')
    <!-- Chart.js -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <!-- Leaflet CSS for Map -->
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />
    <style>
        .status-indicator {
            width: 12px;
            height: 12px;
            border-radius: 50%;
            display: inline-block;
            margin-right: 5px;
        }

        .status-online {
            background-color: #28a745;
        }

        .status-offline {
            background-color: #dc3545;
        }

        .status-warning {
            background-color: #ffc107;
        }

        .chart-container {
            position: relative;
            height: 300px;
            margin: 20px 0;
        }

        .map-container {
            height: 400px;
            border-radius: 8px;
            overflow: hidden;
        }

        .alert-item {
            padding: 10px;
            border-left: 4px solid;
            margin-bottom: 10px;
            /* background-color: #f8f9fa; */
            border-radius: 4px;
        }

        .alert-critical {
            border-left-color: #dc3545;
        }

        .alert-warning {
            border-left-color: #ffc107;
        }

        .alert-info {
            border-left-color: #17a2b8;
        }

        .site-card {
            transition: transform 0.2s;
            cursor: pointer;
        }

        .site-card:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
        }

        .metric-card {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            border-radius: 10px;
            padding: 20px;
            margin-bottom: 20px;
        }

        .metric-value {
            font-size: 2.5rem;
            font-weight: bold;
            margin-bottom: 5px;
        }

        .metric-label {
            font-size: 1rem;
            opacity: 0.9;
        }

        .chart-card {
            background: white;
            border-radius: 10px;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
            padding: 20px;
            margin-bottom: 20px;
        }

        .chart-title {
            font-size: 1.2rem;
            font-weight: 600;
            margin-bottom: 15px;
            color: #333;
        }
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
                            <h3>{{ $totalStations }}</h3>
                            <p>Total Sites</p>
                        </div>
                        <div class="icon">
                            <i class="fas fa-building"></i>
                        </div>
                        <a href="#" class="small-box-footer">View All <i class="fas fa-arrow-circle-right"></i></a>
                    </div>
                </div>

                <div class="col-lg-3 col-6">
                    <div class="small-box bg-success">
                        <div class="inner">
                            <h3>{{ $onlineStations }}</h3>
                            <p>Online Sites</p>
                        </div>
                        <div class="icon">
                            <i class="fas fa-wifi"></i>
                        </div>
                        <a href="#" class="small-box-footer">View Details <i class="fas fa-arrow-circle-right"></i></a>
                    </div>
                </div>

                <div class="col-lg-3 col-6">
                    <div class="small-box bg-warning">
                        <div class="inner">
                            <h3>{{ $warningStations }}</h3>
                            <p>Warning Sites</p>
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
                            <h3>{{ $offlineStations }}</h3>
                            <p>Offline Sites</p>
                        </div>
                        <div class="icon">
                            <i class="fas fa-times-circle"></i>
                        </div>
                        <a href="#" class="small-box-footer">View Details <i class="fas fa-arrow-circle-right"></i></a>
                    </div>
                </div>
            </div>

            <!-- Sales Summary Cards -->
            <div class="row">
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
            </div>

            <!-- Map View and Site Status -->
            <div class="row">
                <div class="col-lg-8">
                    <div class="card">
                        <div class="card-header">
                            <h3 class="card-title">
                                <i class="fas fa-map-marker-alt"></i> Site Locations Map
                            </h3>
                            <div class="card-tools">
                                <button type="button" class="btn btn-tool" data-card-widget="collapse">
                                    <i class="fas fa-minus"></i>
                                </button>
                            </div>
                        </div>
                        <div class="card-body">
                            <div id="map" class="map-container"></div>
                        </div>
                    </div>
                </div>

                <div class="col-lg-4">
                    <div class="card">
                        <div class="card-header">
                            <h3 class="card-title">
                                <i class="fas fa-exclamation-circle"></i> Recent Alerts
                            </h3>
                        </div>
                        <div class="card-body">
                            <div class="alert-item alert-critical">
                                <strong>Site #001 - Critical</strong><br>
                                <small>Pump 3 offline - 2 hours ago</small>
                            </div>
                            <div class="alert-item alert-warning">
                                <strong>Site #015 - Warning</strong><br>
                                <small>Low fuel level in Tank 2 - 1 hour ago</small>
                            </div>
                            <div class="alert-item alert-info">
                                <strong>Site #008 - Info</strong><br>
                                <small>Shift change completed - 30 min ago</small>
                            </div>
                            <div class="alert-item alert-warning">
                                <strong>Site #012 - Warning</strong><br>
                                <small>Temperature sensor offline - 45 min ago</small>
                            </div>
                            <a href="#" class="btn btn-primary btn-sm btn-block">View All Alerts</a>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Analytics Charts Row 1 -->
            <div class="row">
                <div class="col-lg-6">
                    <div class="chart-card">
                        <div class="chart-title">Inventory Forecast - Tank Dry Out Prediction</div>
                        <div class="chart-container">
                            <canvas id="inventoryForecastChart"></canvas>
                        </div>
                    </div>
                </div>

                <div class="col-lg-6">
                    <div class="chart-card">
                        <div class="chart-title">Sales Summary - Last 7 Days</div>
                        <div class="chart-container">
                            <canvas id="salesSummaryChart"></canvas>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Analytics Charts Row 2 -->
            <div class="row">
                <div class="col-lg-4">
                    <div class="chart-card">
                        <div class="chart-title">Product Sales Distribution</div>
                        <div class="chart-container">
                            <canvas id="productSalesChart"></canvas>
                        </div>
                    </div>
                </div>

                <div class="col-lg-4">
                    <div class="chart-card">
                        <div class="chart-title">Top Sites by Sales</div>
                        <div class="chart-container">
                            <canvas id="topSitesChart"></canvas>
                        </div>
                    </div>
                </div>

                <div class="col-lg-4">
                    <div class="chart-card">
                        <div class="chart-title">Low Stock Alert Distribution</div>
                        <div class="chart-container">
                            <canvas id="lowStockChart"></canvas>
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
                                <table class="table-bordered table-striped table" id="sitesTable">
                                    <thead>
                                        <tr>
                                            <th>Site Code</th>
                                            <th>Site Name</th>
                                            <th>Status</th>
                                            <th>Last Connected</th>
                                            <th>Pumps (Online/Total)</th>
                                            <th>Tanks (Online/Total)</th>
                                            <!-- <th>Last Transaction</th> -->
                                            <th>Alerts</th>
                                            <th>Actions</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @forelse($stations as $station)
                                            @php
                                                $status = $station->isOnline() ? ['class' => 'status-online', 'text' => 'Online'] : 
                                                         ($station->hasWarning() ? ['class' => 'status-warning', 'text' => 'Warning'] : 
                                                         ['class' => 'status-offline', 'text' => 'Offline']);
                                                $lastSync = $station->last_sync_at ? $station->last_sync_at->diffForHumans() : 'Never';
                                                $pumpCount = $station->pumps->count();
                                                $activePumps = $station->pumps->where('is_active', true)->count();
                                            @endphp
                                            <tr>
                                                <td>{{ $station->pts_id ?? 'N/A' }}</td>
                                                <td>{{ $station->site_name }}</td>
                                                <td><span class="status-indicator {{ $status['class'] }}"></span>{{ $status['text'] }}</td>
                                                <td>{{ $lastSync }}</td>
                                                <td>{{ $activePumps }}/{{ $pumpCount }}</td>
                                                <td>{{ $station->tankMeasurements->count() }}/{{ $station->tankMeasurements->count() }}</td>
                                                <td><span class="badge badge-success">0</span></td>
                                                <td>
                                                    <button class="btn btn-sm btn-primary station-details-btn" data-station-id="{{ $station->id }}">
                                                        <i class="fas fa-eye"></i> View
                                                    </button>
                                                </td>
                                            </tr>
                                        @empty
                                            <tr>
                                                <td colspan="9" class="text-center">No stations found</td>
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

            // Calculate dynamic low stock data based on tank measurements
            var criticalLowTanks = {{ $stations->sum(function($station) { 
                return $station->tankMeasurements->filter(function($measurement) { 
                    return $measurement->tank_filling_percentage !== null && $measurement->tank_filling_percentage < 20; 
                })->count(); 
            }) }};
            
            var lowStockTanks = {{ $stations->sum(function($station) { 
                return $station->tankMeasurements->filter(function($measurement) { 
                    return $measurement->tank_filling_percentage !== null && $measurement->tank_filling_percentage >= 20 && $measurement->tank_filling_percentage < 50; 
                })->count(); 
            }) }};
            
            var normalStockTanks = {{ $stations->sum(function($station) { 
                return $station->tankMeasurements->filter(function($measurement) { 
                    return $measurement->tank_filling_percentage !== null && $measurement->tank_filling_percentage >= 50; 
                })->count(); 
            }) }};

            // Inventory Forecast Chart (Horizontal Bar Chart) - Dynamic Data
            var inventoryCtx = document.getElementById('inventoryForecastChart').getContext('2d');
            console.log('Initializing inventory forecast chart...');
            
            // Use the same dynamic data as low stock chart
            new Chart(inventoryCtx, {
                type: 'bar',
                data: {
                    labels: ['Critical Low (<20%)', 'Low Stock (20-50%)', 'Normal Stock (50%+)'],
                    datasets: [{
                        label: 'Number of Tanks',
                        data: [criticalLowTanks, lowStockTanks, normalStockTanks],
                        backgroundColor: ['#dc3545', '#ffc107', '#28a745'],
                        borderColor: ['#dc3545', '#ffc107', '#28a745'],
                        borderWidth: 1
                    }]
                },
                options: {
                    indexAxis: 'y',
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: {
                            display: false
                        }
                    },
                    scales: {
                        x: {
                            beginAtZero: true
                        }
                    }
                }
            });

            // Sales Summary Chart (Line Chart) - Dynamic Data
            var salesCtx = document.getElementById('salesSummaryChart').getContext('2d');
            console.log('Initializing sales summary chart with dynamic data...');
            
            new Chart(salesCtx, {
                type: 'line',
                data: {
                    labels: @json($salesData['labels']),
                    datasets: [{
                        label: 'Volume (Liters)',
                        data: @json($salesData['volume']),
                        borderColor: '#007bff',
                        backgroundColor: 'rgba(0, 123, 255, 0.1)',
                        tension: 0.4,
                        fill: true
                    }, {
                        label: 'Amount ($)',
                        data: @json($salesData['amount']),
                        borderColor: '#28a745',
                        backgroundColor: 'rgba(40, 167, 69, 0.1)',
                        tension: 0.4,
                        fill: true
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: {
                            display: true,
                            position: 'top'
                        },
                        tooltip: {
                            callbacks: {
                                label: function(context) {
                                    let label = context.dataset.label || '';
                                    if (label) {
                                        label += ': ';
                                    }
                                    if (context.dataset.label.includes('Volume')) {
                                        label += context.parsed.y.toFixed(2) + ' L';
                                    } else {
                                        label += '$' + context.parsed.y.toFixed(2);
                                    }
                                    return label;
                                }
                            }
                        }
                    },
                    scales: {
                        y: {
                            beginAtZero: true,
                            ticks: {
                                callback: function(value) {
                                    return value.toLocaleString();
                                }
                            }
                        }
                    }
                }
            });

            // Product Sales Chart (Doughnut Chart) - Dynamic Data
            var productCtx = document.getElementById('productSalesChart').getContext('2d');
            console.log('Initializing product sales chart with dynamic data...');
            
            new Chart(productCtx, {
                type: 'doughnut',
                data: {
                    labels: @json($productDistributionData['labels']),
                    datasets: [{
                        data: @json($productDistributionData['data']),
                        backgroundColor: @json($productDistributionData['colors']),
                        borderWidth: 2,
                        borderColor: '#fff'
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: {
                            position: 'bottom',
                            labels: {
                                padding: 20,
                                usePointStyle: true
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
                                    const percentage = ((context.parsed / total) * 100).toFixed(1);
                                    label += context.parsed.toFixed(2) + ' L (' + percentage + '%)';
                                    return label;
                                }
                            }
                        }
                    }
                }
            });

            // Top Sites Chart (Horizontal Bar Chart)
            var topSitesCtx = document.getElementById('topSitesChart').getContext('2d');
            console.log('Initializing top sites chart...');
            new Chart(topSitesCtx, {
                type: 'bar',
                data: {
                    labels: ['Airport Station', 'Downtown Station', 'Highway Station', 'Mall Station', 'Suburb Station'],
                    datasets: [{
                        label: 'Sales Volume (Liters)',
                        data: [25000, 22000, 18000, 15000, 12000],
                        backgroundColor: '#17a2b8',
                        borderColor: '#17a2b8',
                        borderWidth: 1
                    }]
                },
                options: {
                    indexAxis: 'y',
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: {
                            display: false
                        }
                    },
                    scales: {
                        x: {
                            beginAtZero: true
                        }
                    }
                }
            });

            // Low Stock Chart (Doughnut Chart) - Dynamic Data
            var lowStockCtx = document.getElementById('lowStockChart').getContext('2d');
            console.log('Initializing low stock chart...');
            
            new Chart(lowStockCtx, {
                type: 'doughnut',
                data: {
                    labels: ['Critical Low (<20%)', 'Low Stock (20-50%)', 'Normal Stock (50%+)'],
                    datasets: [{
                        data: [criticalLowTanks, lowStockTanks, normalStockTanks],
                        backgroundColor: ['#dc3545', '#ffc107', '#28a745'],
                        borderWidth: 2
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: {
                            position: 'bottom'
                        }
                    }
                }
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
