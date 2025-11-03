@extends('layouts.adminlte')

@section('content')
<div class="container-fluid p-4">
    <!-- Station Header -->
    <div class="d-flex justify-content-between align-items-start mb-2">
        <div>
            <h4 class="fw-bold mb-1">{{ $station->site_name ?? $station->name ?? 'Station' }}</h4>
            <div class="text-muted small">{{ $address }}</div>
        </div>
        <div>
            @if($station_status === 'online')
                <span class="badge bg-success-light text-success" style="font-size:1rem;">Online</span>
            @elseif($station_status === 'offline')
                <span class="badge bg-danger-light text-danger" style="font-size:1rem;">Offline</span>
            @else
                <span class="badge bg-warning-light text-warning" style="font-size:1rem;">Warning</span>
            @endif
        </div>
    </div>
    <!-- Summary Cards -->
    <div class="row g-3 mb-2">
        <div class="col-md-4">
            <div class="card h-100 shadow-sm text-center p-3">
                <div class="small text-muted mb-1">Active Pumps</div>
                <div class="display-6">{{ $pump_online }}/{{ $pump_total }}</div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card h-100 shadow-sm text-center p-3">
                <div class="small text-muted mb-1">Tanks Online</div>
                <div class="display-6">{{ $tank_online }}/{{ $tank_total }}</div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card h-100 shadow-sm text-center p-3">
                <div class="small text-muted mb-1">Active Alerts</div>
                <div class="display-6">{{ count($alerts) }}</div>
            </div>
        </div>
    </div>

    <!-- Pump Status Table -->
    <div class="card shadow-sm my-4">
        <div class="card-header fw-semibold bg-white">Pump Status</div>
        <div class="card-body pb-0">
            <div class="table-responsive">
                <table class="table align-middle">
                    <thead class="small">
                        <tr>
                            <th>Pump Number</th>
                            <th>Product</th>
                            <th>Nozzles</th>
                            <th>Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($pumps as $pump)
                        <tr>
                            <td>{{ $pump['number'] ?? '-' }}</td>
                            <td>{{ $pump['product'] ?? '-' }}</td>
                            <td>{{ $pump['nozzles'] ?? '-' }}</td>
                            <td>
                                @if($pump['status'] == 'online')
                                    <span class="badge bg-success-light text-success">online</span>
                                @else
                                    <span class="badge bg-secondary-light text-muted">offline</span>
                                @endif
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- Tank Inventory Table -->
    <div class="card shadow-sm my-4">
        <div class="card-header fw-semibold bg-white">Tank Inventory</div>
        <div class="card-body pb-0">
            <div class="table-responsive">
                <table class="table align-middle">
                    <thead class="small">
                        <tr>
                            <th>Tank Number</th>
                            <th>Product</th>
                            <th>Capacity (L)</th>
                            <th>Current (L)</th>
                            <th>Percentage</th>
                            <th>Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($tanks as $tank)
                        <tr>
                            <td>{{ $tank['number'] ?? '-' }}</td>
                            <td>{{ $tank['product'] ?? '-' }}</td>
                            <td>{{ isset($tank['capacity']) ? number_format($tank['capacity']) : '-' }}</td>
                            <td>{{ isset($tank['current']) ? number_format($tank['current']) : '-' }}</td>
                            <td style="min-width:110px;">
                                <div class="progress" style="height:8px;">
                                    @php $barClass = ($tank['percentage'] ?? 0) >= 50 ? 'bg-success' : (($tank['percentage'] ?? 0) >= 15 ? 'bg-warning' : 'bg-danger'); @endphp
                                    <div class="progress-bar {{ $barClass }}" role="progressbar" style="width: {{ $tank['percentage'] ?? 0 }}%"></div>
                                </div>
                                <span class="small text-muted ms-1">{{ $tank['percentage'] ?? '-' }}%</span>
                            </td>
                            <td>
                                @if(isset($tank['status']) && $tank['status'] =='normal')
                                    <span class="badge bg-success-light text-success">normal</span>
                                @elseif(isset($tank['status']) && $tank['status'] =='low')
                                    <span class="badge bg-warning-light text-warning">low</span>
                                @elseif(isset($tank['status']) && $tank['status'] =='critical')
                                    <span class="badge bg-danger-light text-danger">critical</span>
                                @else
                                    <span class="badge bg-secondary-light text-muted">-</span>
                                @endif
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- Site Alerts -->
    <div class="card shadow-sm my-4">
        <div class="card-header fw-semibold bg-white">Site Alerts</div>
        <div class="card-body pb-0">
            @forelse($alerts as $alert)
            <div class="d-flex align-items-center mb-3">
                <div>
                    @if($alert['level'] == 'high')
                        <span class="me-2"><i class="fas fa-exclamation-triangle text-danger"></i></span>
                    @elseif($alert['level'] == 'medium')
                        <span class="me-2"><i class="fas fa-exclamation-circle text-warning"></i></span>
                    @else
                        <span class="me-2"><i class="fas fa-info-circle text-muted"></i></span>
                    @endif
                </div>
                <div class="flex-grow-1">
                    <span class="fw-semibold">Stock Alert</span><br>
                    <span class="small">{{ $alert['message'] }}</span><br>
                    <span class="text-muted small">{{ $alert['date'] }}</span>
                </div>
                <div>
                    @if($alert['level'] == 'high')
                        <span class="badge bg-danger-light text-danger">high</span>
                    @elseif($alert['level'] == 'medium')
                        <span class="badge bg-warning-light text-warning">medium</span>
                    @else
                        <span class="badge bg-secondary-light text-muted">info</span>
                    @endif
                </div>
            </div>
            @empty
                <div class="text-center text-muted">No alerts</div>
            @endforelse
        </div>
    </div>
</div>
@endsection
