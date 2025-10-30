@extends('layouts.adminlte')

@section('content')
<div class="container-fluid py-3">
    <!-- Page Title and Bar -->
    <div class="d-flex flex-wrap align-items-center justify-content-between mb-3">
        <div>
            <h2 class="fw-bold mb-1">Operations Monitor</h2>
            <div class="text-muted small">Real-time monitoring of all sites, pumps, and tanks</div>
        </div>
        <div class="d-flex align-items-center gap-2">
            <input class="form-control me-2" type="search" placeholder="Search" style="width:18em;">
            <small class="text-secondary ms-2"><i class="far fa-clock"></i> Last updated at {{ now()->format('H:i') }}</small>
        </div>
    </div>

    <!-- Summary Cards -->
    <div class="row g-3 mb-4">
        <div class="col-md-3">
            <div class="card h-100 shadow-sm">
                <div class="card-body">
                    <div class="fw-semibold mb-2">Sites Summary</div>
                    <div class="d-flex justify-content-between align-items-center">
                        <span class="text-muted small">Total Sites</span>
                        <span class="fs-4">{{ $totalSites }}</span>
                    </div>
                    <div class="d-flex justify-content-between mt-2">
                        <span class="text-success small"><i class="fas fa-circle me-1" style="font-size:8px;"></i>Online</span>
                        <span class="fw-medium">{{ $onlineSites }}</span>
                    </div>
                    <div class="d-flex justify-content-between">
                        <span class="text-danger small"><i class="fas fa-circle me-1" style="font-size:8px;"></i>Offline</span>
                        <span class="fw-medium">{{ $offlineSites }}</span>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card h-100 shadow-sm">
                <div class="card-body">
                    <div class="fw-semibold mb-2">Pumps Summary</div>
                    <div class="d-flex justify-content-between align-items-center">
                        <span class="text-muted small">Total Pumps</span>
                        <span class="fs-4">{{ $totalPumps }}</span>
                    </div>
                    <div class="d-flex justify-content-between mt-2">
                        <span class="text-success small"><i class="fas fa-circle me-1" style="font-size:8px;"></i>Online</span>
                        <span class="fw-medium">{{ $onlinePumps }}</span>
                    </div>
                    <div class="d-flex justify-content-between">
                        <span class="text-danger small"><i class="fas fa-circle me-1" style="font-size:8px;"></i>Offline</span>
                        <span class="fw-medium">{{ $offlinePumps }}</span>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card h-100 shadow-sm">
                <div class="card-body">
                    <div class="fw-semibold mb-2">Tanks Summary</div>
                    <div class="d-flex justify-content-between align-items-center">
                        <span class="text-muted small">Total Tanks</span>
                        <span class="fs-4">{{ $totalTanks }}</span>
                    </div>
                    <div class="d-flex justify-content-between mt-2">
                        <span class="text-success small"><i class="fas fa-circle me-1" style="font-size:8px;"></i>Online</span>
                        <span class="fw-medium">{{ $onlineTanks }}</span>
                    </div>
                    <div class="d-flex justify-content-between">
                        <span class="text-danger small"><i class="fas fa-circle me-1" style="font-size:8px;"></i>Offline</span>
                        <span class="fw-medium">{{ $offlineTanks }}</span>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card h-100 shadow-sm">
                <div class="card-body">
                    <div class="fw-semibold mb-2">Alerts Summary</div>
                    <div class="d-flex justify-content-between align-items-center">
                        <span class="text-muted small">Total Alerts</span>
                        <span class="fs-4">{{ $totalAlerts }}</span>
                    </div>
                    <div class="d-flex justify-content-between mt-2">
                        <span class="text-success small"><i class="fas fa-circle me-1" style="font-size:8px;"></i>Sites Normal</span>
                        <span class="fw-medium">{{ $normalSites }}</span>
                    </div>
                    <div class="d-flex justify-content-between">
                        <span class="text-danger small"><i class="fas fa-circle me-1" style="font-size:8px;"></i>Sites with Alerts</span>
                        <span class="fw-medium">{{ $sitesWithAlerts }}</span>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- All Sites Table -->
    <div class="card shadow-sm">
        <div class="card-header bg-white border-bottom-0">
            <h5 class="mb-0 fw-bold">All Sites</h5>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table align-middle mb-0">
                    <thead class="table-light">
                        <tr>
                            <th>Site Code</th>
                            <th>Site Name</th>
                            <th>Status</th>
                            <th>Last Connected</th>
                            <th>Last Transaction</th>
                            <th>Pumps</th>
                            <th>Tanks</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($allSites as $site)
                        <tr>
                            <td>
                                <a href="{{ route('operations-monitor.station', $site['id']) }}" class="text-decoration-underline fw-bold">
                                    {{ $site['code'] }}
                                </a>
                            </td>
                            <td>{{ $site['name'] }}</td>
                            <td>
                                @if($site['status'] == 'online')
                                    <span class="badge bg-success-light text-success"><i class="fas fa-circle me-1" style="font-size:8px;"></i>Online</span>
                                @else
                                    <span class="badge bg-danger-light text-danger"><i class="fas fa-circle me-1" style="font-size:8px;"></i>Offline</span>
                                @endif
                            </td>
                            <td>{{ $site['last_connected'] }}</td>
                            <td>{{ $site['last_transaction'] }}</td>
                            <!-- PUMPS column -->
                            <td style="min-width:170px; position:relative;">
                                <div class="d-flex align-items-center gap-2 pump-bar-trigger" style="position:relative;">
                                    <span>{{ $site['pump_online'] }}</span>
                                    <div class="progress flex-grow-1" style="height:8px; cursor:pointer;">
                                        @php
                                        $pumpBarClass = $site['pump_percent'] >= 75 ? 'bg-primary' : ($site['pump_percent'] >= 50 ? 'bg-warning' : 'bg-danger');
                                        @endphp
                                        <div class="progress-bar {{ $pumpBarClass }}"
                                             role="progressbar"
                                             style="width: {{ $site['pump_percent'] }}%"
                                             aria-valuenow="{{ $site['pump_percent'] }}" aria-valuemin="0" aria-valuemax="100">
                                        </div>
                                    </div>
                                    <span class="text-muted small">{{ $site['pump_percent'] }}%</span>
                                    <!-- Pump Breakdown Toast -->
                                    <div class="pump-breakdown-toast monitor-toast" style="display:none; position:absolute; top:38px; left:50%; transform:translateX(-50%); min-width:220px; z-index:1000; background:#041432; color:#fff; border-radius:24px; padding:24px 18px 12px 18px; box-shadow:0 4px 16px rgba(4,16,46,.10); font-size:1rem; font-family:inherit;">
                                        <div style="font-weight:600; font-size:1.2rem; margin-bottom:8px;">Pump Breakdown</div>
                                        <div style="display:flex; justify-content:space-between; margin-bottom:4px;">
                                            <div>Online</div>
                                            <div style="color:#3461ee; font-weight:500;">{{ $site['pump_online'] }} ({{ number_format($site['pump_total'] > 0 ? 100 * $site['pump_online'] / $site['pump_total'] : 0, 1) }}%)</div>
                                        </div>
                                        <div style="display:flex; justify-content:space-between; margin-bottom:6px;">
                                            <div>Offline</div>
                                            <div style="color:#22c55e; font-weight:500;">{{ $site['pump_total'] - $site['pump_online'] }} ({{ number_format($site['pump_total'] > 0 ? 100 * ($site['pump_total'] - $site['pump_online']) / $site['pump_total'] : 0, 1) }}%)</div>
                                        </div>
                                        <hr style="margin:7px 0 10px 0; opacity:.15; border-color:#fff;">
                                        <div style="display:flex; justify-content:space-between; font-weight:700;">
                                            <div>Total</div>
                                            <div>{{ $site['pump_total'] }}</div>
                                        </div>
                                    </div>
                                </div>
                            </td>
                            <!-- TANKS column -->
                            <td style="min-width:170px; position:relative;">
                                <div class="d-flex align-items-center gap-2 tank-bar-trigger" style="position:relative;">
                                    <span>{{ $site['tank_online'] }}</span>
                                    <div class="progress flex-grow-1" style="height:8px; cursor:pointer;">
                                        @php
                                        $tankBarClass = $site['tank_percent'] >= 75 ? 'bg-primary' : ($site['tank_percent'] >= 50 ? 'bg-warning' : 'bg-danger');
                                        @endphp
                                        <div class="progress-bar {{ $tankBarClass }}"
                                             role="progressbar"
                                             style="width: {{ $site['tank_percent'] }}%"
                                             aria-valuenow="{{ $site['tank_percent'] }}" aria-valuemin="0" aria-valuemax="100">
                                        </div>
                                    </div>
                                    <span class="text-muted small">{{ $site['tank_percent'] }}%</span>
                                    <!-- Tank Breakdown Toast -->
                                    <div class="tank-breakdown-toast monitor-toast" style="display:none; position:absolute; top:38px; left:50%; transform:translateX(-50%); min-width:220px; z-index:1000; background:#041432; color:#fff; border-radius:24px; padding:24px 18px 12px 18px; box-shadow:0 4px 16px rgba(4,16,46,.10); font-size:1rem; font-family:inherit;">
                                        <div style="font-weight:600; font-size:1.2rem; margin-bottom:8px;">Tank Breakdown</div>
                                        <div style="display:flex; justify-content:space-between; margin-bottom:4px;">
                                            <div>Online</div>
                                            <div style="color:#3461ee; font-weight:500;">{{ $site['tank_online'] }} ({{ number_format($site['tank_total'] > 0 ? 100 * $site['tank_online'] / $site['tank_total'] : 0, 1) }}%)</div>
                                        </div>
                                        <div style="display:flex; justify-content:space-between; margin-bottom:6px;">
                                            <div>Offline</div>
                                            <div style="color:#22c55e; font-weight:500;">{{ $site['tank_total'] - $site['tank_online'] }} ({{ number_format($site['tank_total'] > 0 ? 100 * ($site['tank_total'] - $site['tank_online']) / $site['tank_total'] : 0, 1) }}%)</div>
                                        </div>
                                        <hr style="margin:7px 0 10px 0; opacity:.15; border-color:#fff;">
                                        <div style="display:flex; justify-content:space-between; font-weight:700;">
                                            <div>Total</div>
                                            <div>{{ $site['tank_total'] }}</div>
                                        </div>
                                    </div>
                                </div>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="6" class="text-center">No sites found.</td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
function setupMonitorToasts(triggerClass, toastClass) {
    document.querySelectorAll('.' + triggerClass).forEach(function(el) {
        el.addEventListener('mouseenter', function() {
            var toast = el.querySelector('.' + toastClass);
            if (toast) toast.style.display = 'block';
        });
        el.addEventListener('mouseleave', function() {
            var toast = el.querySelector('.' + toastClass);
            if (toast) toast.style.display = 'none';
        });
    });
}
document.addEventListener('DOMContentLoaded', function() {
    setupMonitorToasts('pump-bar-trigger', 'pump-breakdown-toast');
    setupMonitorToasts('tank-bar-trigger', 'tank-breakdown-toast');
});
</script>
@endpush
