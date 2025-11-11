@extends('layouts.app')

@push('css')
<link href="{{ asset('css/alerts.css') }}" rel="stylesheet">
@endpush

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
    <div class="d-flex justify-content-between align-items-center mb-4">
        <ul class="nav nav-tabs" id="alertsTab" role="tablist">
            <li class="nav-item" role="presentation">
                <a class="nav-link @if($tab==='unread') active @endif" href="?tab=unread" id="unread-tab" data-bs-toggle="tab" data-bs-target="#unread" type="button" role="tab" aria-controls="unread" aria-selected="{{ $tab === 'unread' ? 'true' : 'false' }}">
                    <i class="fas fa-envelope me-1"></i> Unread
                    @if($unread > 0)
                        <span class="badge bg-danger ms-1">{{ $unread }}</span>
                    @endif
                </a>
            </li>
            <li class="nav-item" role="presentation">
                <a class="nav-link @if($tab==='all') active @endif" href="?tab=all" id="all-tab" data-bs-toggle="tab" data-bs-target="#all" type="button" role="tab" aria-controls="all" aria-selected="{{ $tab === 'all' ? 'true' : 'false' }}">
                    <i class="fas fa-list me-1"></i> All Notifications
                </a>
            </li>
            <li class="nav-item" role="presentation">
                <a class="nav-link @if($tab==='hos') active @endif" href="?tab=hos" id="hos-tab" data-bs-toggle="tab" data-bs-target="#hos" type="button" role="tab" aria-controls="hos" aria-selected="{{ $tab === 'hos' ? 'true' : 'false' }}">
                    <i class="fas fa-gas-pump me-1"></i> HOS Alerts
                </a>
            </li>
            <li class="nav-item" role="presentation">
                <a class="nav-link @if($tab==='bos') active @endif" href="?tab=bos" id="bos-tab" data-bs-toggle="tab" data-bs-target="#bos" type="button" role="tab" aria-controls="bos" aria-selected="{{ $tab === 'bos' ? 'true' : 'false' }}">
                    <i class="fas fa-desktop me-1"></i> BOS Alerts
                </a>
            </li>
            <li class="nav-item" role="presentation">
                <a class="nav-link disabled" href="#" id="controller-tab" data-bs-toggle="tab" data-bs-target="#controller" type="button" role="tab" aria-controls="controller" aria-selected="false">
                    <i class="fas fa-microchip me-1"></i> Controller
                </a>
            </li>
        </ul>
        
        <div class="d-flex align-items-center">
            <div class="input-group me-2" style="width: 250px;">
                <span class="input-group-text bg-transparent"><i class="fas fa-search text-muted"></i></span>
                <input type="text" class="form-control" id="searchAlerts" placeholder="Search alerts...">
            </div>
            <button class="btn btn-outline-secondary" id="markAllRead" title="Mark all as read">
                <i class="fas fa-check-double me-1"></i> Mark All as Read
            </button>
        </div>
    </div>

    <div class="card custom-card">
        <div class="card-header custom-card-header">
            <h4 class="mb-0">
                @if($tab === 'unread')
                    Unread Notifications
                @elseif($tab === 'hos')
                    HOS Alerts
                @elseif($tab === 'bos')
                    Back Office System Alerts
                @else
                    All Notifications
                @endif
            </h4>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="custom-table">
                    <thead class="custom-table-header">
                        <tr>
                            <th>Alert Description</th>
                            <th class="text-center">Priority Level</th>
                            <th class="text-center">Device Type</th>
                            <th class="text-center">Date</th>
                            <th class="text-center">Time</th>
                            <th class="text-center">Status</th>
                            <th class="text-center">Options</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($formattedAlerts as $alert)
                            <tr>
                                <td>
                                    <div class="fw-medium">{{ $alert['message'] }}</div>
                                    <small class="text-muted">{{ $alert['device_type'] }} - Code {{ $alert['code'] }}</small>
                                </td>
                                <td class="text-center">
                                    <span class="badge bg-{{ $alert['priority_class'] }}">
                                        {{ $alert['priority'] }}
                                    </span>
                                </td>
                                <td class="text-center">
                                    <span class="badge bg-secondary">{{ $alert['device_type'] }}</span>
                                </td>
                                <td class="text-center">{{ $alert['date_time']->format('Y-m-d') }}</td>
                                <td class="text-center">{{ $alert['date_time']->format('H:i:s') }}</td>
                                <td class="text-center">
                                    <span class="badge {{ $alert['read'] ? 'bg-success' : 'bg-warning' }}">
                                        {{ $alert['read'] ? 'Read' : 'Unread' }}
                                    </span>
                                </td>
                                <td class="text-center">
                                    <button class="action-btn delete" onclick="deleteAlert({{ $alert['id'] }})" title="Delete">
                                        <i class="fas fa-trash"></i>
                                    </button>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="text-center">
                                    <div class="empty-state">
                                        <i class="fas fa-inbox"></i>
                                        <h5>No alerts found</h5>
                                        <p>There are no alerts to display.</p>
                                    </div>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            
            <!-- Pagination -->
            <div class="d-flex justify-content-center mt-4">
                {{ $alerts->links() }}
            </div>
        </div>
    </div>
    
@push('scripts')
<script>
    function deleteAlert(alertId) {
        if (confirm('Are you sure you want to delete this alert?')) {
            fetch(`/alerts/${alertId}`, {
                method: 'DELETE',
                headers: {
                    'X-CSRF-TOKEN': '{{ csrf_token() }}',
                    'Accept': 'application/json',
                    'Content-Type': 'application/json'
                }
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    window.location.reload();
                } else {
                    alert('Failed to delete alert: ' + (data.message || 'Unknown error'));
                }
            })
            .catch(error => {
                console.error('Error:', error);
                alert('An error occurred while deleting the alert');
            });
        }
    }
    
    // Mark all as read
    document.getElementById('markAllRead')?.addEventListener('click', function() {
        fetch('{{ route("alerts.mark-all-read") }}', {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': '{{ csrf_token() }}',
                'Accept': 'application/json',
                'Content-Type': 'application/json'
            }
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                window.location.reload();
            } else {
                alert('Failed to mark all as read: ' + (data.message || 'Unknown error'));
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('An error occurred while marking alerts as read');
        });
    });

    // Search functionality
    document.getElementById('searchAlerts')?.addEventListener('keyup', function() {
        const searchTerm = this.value.toLowerCase();
        const rows = document.querySelectorAll('.custom-table tbody tr');
        
        rows.forEach(row => {
            const text = row.textContent.toLowerCase();
            row.style.display = text.includes(searchTerm) ? '' : 'none';
        });
    });
</script>
@endpush
                } else {
                    alert('Failed to mark all as read: ' + (data.message || 'Unknown error'));
                }
            })
            .catch(error => {
                console.error('Error:', error);
                alert('An error occurred while marking alerts as read');
            });
        });
    </script>
    @endpush
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
