@extends('layouts.adminlte')

@section('content')
<div class="container-fluid p-3">
    <div class="d-flex flex-wrap align-items-center justify-content-between mb-3">
        <div>
            <h2 class="fw-bold mb-1">Alerts</h2>
            <div class="text-muted small">Real-time monitoring of all sites, pumps, and tanks</div>
        </div>
        <div class="d-flex align-items-center gap-2">
            <input class="form-control me-2" type="search" placeholder="Search" style="width:18em;">
            <small class="text-secondary ms-2"><i class="far fa-clock"></i> Last updated at {{ now()->format('H:i') }}</small>
        </div>
    </div>

    <!-- Stats Row -->
    <div class="row g-3 mb-4">
        <div class="col">
            <div class="card h-100 shadow-sm text-center p-3">
                <div class="small text-muted">Unread</div>
                <div class="h3">{{ $unread }}</div>
            </div>
        </div>
        <div class="col">
            <div class="card h-100 shadow-sm text-center p-3">
                <div class="small text-muted">Critical</div>
                <div class="h3">{{ $critical }}</div>
            </div>
        </div>
        <div class="col">
            <div class="card h-100 shadow-sm text-center p-3">
                <div class="small text-muted">Warnings</div>
                <div class="h3">{{ $warning }}</div>
            </div>
        </div>
        <div class="col">
            <div class="card h-100 shadow-sm text-center p-3">
                <div class="small text-muted">Total Today</div>
                <div class="h3">{{ $totalToday }}</div>
            </div>
        </div>
    </div>

    <!-- Tabs -->
    <ul class="nav nav-tabs mb-3">
        <li class="nav-item">
            <a class="nav-link @if($tab==='unread') active @endif" href="?tab=unread">Unread</a>
        </li>
        <li class="nav-item">
            <a class="nav-link @if($tab==='all') active @endif" href="?tab=all">All Notifications</a>
        </li>
        <li class="nav-item">
            <a class="nav-link @if($tab==='hos') active @endif" href="?tab=hos">HOS</a>
        </li>
        <li class="nav-item">
            <a class="nav-link @if($tab==='bos') active @endif" href="?tab=bos">BOS</a>
        </li>
        <li class="nav-item">
            <a class="nav-link disabled">Controller</a>
        </li>
    </ul>

    <div class="card p-4">
        <div class="fw-bold mb-2">
            @if($tab === 'unread')
                Unread Notifications
            @elseif($tab === 'hos')
                HOS Alerts
            @elseif($tab === 'bosi')
                Back Office System Alerts
            @else
                All Notifications
            @endif
        </div>
        @forelse($alerts as $alert)
            @php
                $isCritical = in_array($alert->code, [3,6,8]);
                $isWarning = in_array($alert->code, [1,2,5,7]);
                $isMedium = in_array($alert->code, [2,4,5]);
                // Message generation
                $message = '';
                $level = '';
                if($alert->device_type == 'BOS') {
                    $message = 'Back Office System - ' . ($alert->description ?? 'New notification');
                    $level = 'info';
                } elseif($alert->device_type == 'Pump') {
                    if($alert->code == 1) { $message = 'Pump '.$alert->device_number.' offline state detected'; $level='high'; }
                    elseif($alert->code == 2) { $message = 'Pump '.$alert->device_number.' overfilling detected'; $level='medium'; }
                } elseif($alert->device_type == 'Probe') {
                    switch($alert->code) {
                        case 1: $message = 'Probe '.$alert->device_number.' offline state detected'; $level='high'; break;
                        case 2: $message = 'Probe '.$alert->device_number.' error detected'; $level='medium'; break;
                        case 3: $message = 'Probe '.$alert->device_number.' critical high product level'; $level='high'; break;
                        case 4: $message = 'Probe '.$alert->device_number.' high product level'; $level='medium'; break;
                        case 5: $message = 'Probe '.$alert->device_number.' low product level'; $level='medium'; break;
                        case 6: $message = 'Probe '.$alert->device_number.' critical low product level'; $level='high'; break;
                        case 7: $message = 'Probe '.$alert->device_number.' high water level'; $level='medium'; break;
                        case 8: $message = 'Probe '.$alert->device_number.' tank leakage detected'; $level='high'; break;
                        default: $message = 'Probe '.$alert->device_number.' unknown alert'; $level=''; break;
                    }
                }
                $badge = match($level) {
                    'high' => 'danger',
                    'medium' => 'warning',
                    'info' => 'info',
                    default => 'secondary'
                };
                $icon = $level === 'high' ? 'fa-exclamation-triangle text-danger' : ($level === 'medium' ? 'fa-exclamation-circle text-warning' : 'fa-info-circle text-muted');
            @endphp
            <div class="d-flex align-items-center mb-3 p-3 rounded" style="background:#fafbfc;">
                <div><i class="fas {{ $icon }} fa-lg me-2"></i></div>
                <div class="flex-grow-1">
                    <span class="fw-semibold">
                        @if($alert->station)
                            <a href="{{ route('operations-monitor.station', $alert->station->id) }}">{{ $alert->station->site_name }}</a>
                        @else
                            Station #{{ $alert->station_id ?? '-' }}
                        @endif
                    </span>
                    <br>
                    <span class="small">{{ $message }}</span>
                    <br><span class="text-muted small">{{ $alert->datetime?->format('Y-m-d H:i:s') ?? '-' }}</span>
                </div>
                <div>
                    <span class="badge bg-{{ $badge }}-light text-{{ $badge }}" style="text-transform:capitalize; font-size:0.95em; min-width:56px;">{{ $level }}</span>
                </div>
            </div>
        @empty
            <div class="text-center text-muted">No alerts found.</div>
        @endforelse
    </div>
</div>
@endsection
