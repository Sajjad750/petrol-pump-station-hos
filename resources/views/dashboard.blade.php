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
                            <h3>25</h3>
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
                            <h3>22</h3>
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
                            <h3>2</h3>
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
                            <h3>1</h3>
                            <p>Offline Sites</p>
                        </div>
                        <div class="icon">
                            <i class="fas fa-times-circle"></i>
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
                                            <th>Last Transaction</th>
                                            <th>Alerts</th>
                                            <th>Actions</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <tr>
                                            <td>ST001</td>
                                            <td>Downtown Station</td>
                                            <td><span class="status-indicator status-online"></span>Online</td>
                                            <td>2 min ago</td>
                                            <td>8/8</td>
                                            <td>4/4</td>
                                            <td>1 min ago</td>
                                            <td><span class="badge badge-success">0</span></td>
                                            <td>
                                                <button class="btn btn-sm btn-primary">View</button>
                                                <button class="btn btn-sm btn-warning">Close Shift</button>
                                            </td>
                                        </tr>
                                        <tr>
                                            <td>ST002</td>
                                            <td>Highway Station</td>
                                            <td><span class="status-indicator status-warning"></span>Warning</td>
                                            <td>5 min ago</td>
                                            <td>6/8</td>
                                            <td>3/4</td>
                                            <td>3 min ago</td>
                                            <td><span class="badge badge-warning">2</span></td>
                                            <td>
                                                <button class="btn btn-sm btn-primary">View</button>
                                                <button class="btn btn-sm btn-warning">Close Shift</button>
                                            </td>
                                        </tr>
                                        <tr>
                                            <td>ST003</td>
                                            <td>Airport Station</td>
                                            <td><span class="status-indicator status-online"></span>Online</td>
                                            <td>1 min ago</td>
                                            <td>12/12</td>
                                            <td>6/6</td>
                                            <td>30 sec ago</td>
                                            <td><span class="badge badge-success">0</span></td>
                                            <td>
                                                <button class="btn btn-sm btn-primary">View</button>
                                                <button class="btn btn-sm btn-warning">Close Shift</button>
                                            </td>
                                        </tr>
                                        <tr>
                                            <td>ST004</td>
                                            <td>Mall Station</td>
                                            <td><span class="status-indicator status-offline"></span>Offline</td>
                                            <td>2 hours ago</td>
                                            <td>0/6</td>
                                            <td>0/3</td>
                                            <td>2 hours ago</td>
                                            <td><span class="badge badge-danger">5</span></td>
                                            <td>
                                                <button class="btn btn-sm btn-primary">View</button>
                                                <button class="btn btn-sm btn-warning">Close Shift</button>
                                            </td>
                                        </tr>
                                        <tr>
                                            <td>ST005</td>
                                            <td>Suburb Station</td>
                                            <td><span class="status-indicator status-online"></span>Online</td>
                                            <td>30 sec ago</td>
                                            <td>4/4</td>
                                            <td>2/2</td>
                                            <td>1 min ago</td>
                                            <td><span class="badge badge-success">0</span></td>
                                            <td>
                                                <button class="btn btn-sm btn-primary">View</button>
                                                <button class="btn btn-sm btn-warning">Close Shift</button>
                                            </td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

        </div>
    </section>
@endsection

@push('js')
    <!-- Leaflet JS for Map -->
    <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>

    <script>
        $(document).ready(function() {
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

            // Initialize Map
            var map = L.map('map').setView([40.7128, -74.0060], 10);

            L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
                attribution: '© OpenStreetMap contributors'
            }).addTo(map);

            // Add sample markers for sites
            var sites = [{
                    name: "Downtown Station",
                    lat: 40.7589,
                    lng: -73.9851,
                    status: "online"
                },
                {
                    name: "Highway Station",
                    lat: 40.6892,
                    lng: -74.0445,
                    status: "warning"
                },
                {
                    name: "Airport Station",
                    lat: 40.6413,
                    lng: -73.7781,
                    status: "online"
                },
                {
                    name: "Mall Station",
                    lat: 40.7505,
                    lng: -73.9934,
                    status: "offline"
                },
                {
                    name: "Suburb Station",
                    lat: 40.6782,
                    lng: -73.9442,
                    status: "online"
                }
            ];

            sites.forEach(function(site) {
                var color = site.status === 'online' ? 'green' : site.status === 'warning' ? 'orange' : 'red';
                var marker = L.circleMarker([site.lat, site.lng], {
                    color: color,
                    fillColor: color,
                    fillOpacity: 0.7,
                    radius: 8
                }).addTo(map);

                marker.bindPopup('<b>' + site.name + '</b><br>Status: ' + site.status);
            });

            // Inventory Forecast Chart (Horizontal Bar Chart)
            var inventoryCtx = document.getElementById('inventoryForecastChart').getContext('2d');
            new Chart(inventoryCtx, {
                type: 'bar',
                data: {
                    labels: ['1-2 Days (Critical)', '3-5 Days (Warning)', '5+ Days (Normal)'],
                    datasets: [{
                        label: 'Number of Tanks',
                        data: [3, 8, 15],
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

            // Sales Summary Chart (Line Chart)
            var salesCtx = document.getElementById('salesSummaryChart').getContext('2d');
            new Chart(salesCtx, {
                type: 'line',
                data: {
                    labels: ['Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat', 'Sun'],
                    datasets: [{
                        label: 'Volume (Liters)',
                        data: [12000, 15000, 18000, 16000, 20000, 22000, 19000],
                        borderColor: '#007bff',
                        backgroundColor: 'rgba(0, 123, 255, 0.1)',
                        tension: 0.4,
                        fill: true
                    }, {
                        label: 'Amount ($)',
                        data: [24000, 30000, 36000, 32000, 40000, 44000, 38000],
                        borderColor: '#28a745',
                        backgroundColor: 'rgba(40, 167, 69, 0.1)',
                        tension: 0.4,
                        fill: true
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    scales: {
                        y: {
                            beginAtZero: true
                        }
                    }
                }
            });

            // Product Sales Chart (Doughnut Chart)
            var productCtx = document.getElementById('productSalesChart').getContext('2d');
            new Chart(productCtx, {
                type: 'doughnut',
                data: {
                    labels: ['Petrol 95', 'Petrol 92', 'Diesel', 'Kerosene'],
                    datasets: [{
                        data: [45, 25, 20, 10],
                        backgroundColor: ['#007bff', '#28a745', '#ffc107', '#dc3545'],
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

            // Top Sites Chart (Horizontal Bar Chart)
            var topSitesCtx = document.getElementById('topSitesChart').getContext('2d');
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

            // Low Stock Chart (Doughnut Chart)
            var lowStockCtx = document.getElementById('lowStockChart').getContext('2d');
            new Chart(lowStockCtx, {
                type: 'doughnut',
                data: {
                    labels: ['Critical (1-2 days)', 'Warning (3-5 days)', 'Normal (5+ days)'],
                    datasets: [{
                        data: [3, 8, 15],
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
    </script>
@endpush
