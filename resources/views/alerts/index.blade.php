@extends('layouts.adminlte')

@section('content')
<div class="container-fluid p-3">
    <div class="d-flex flex-wrap align-items-center justify-content-between mb-3">
        <div>
            <h2 class="fw-bold mb-1">Alerts</h2>
            <div class="text-muted small">Real-time monitoring of all sites, pumps, and tanks</div>
        </div>
        <div class="d-flex align-items-center gap-2">
            <input class="form-control me-2" type="search" id="alertSearch" placeholder="Search alerts..." style="width:18em;">
            <small class="text-secondary ms-2"><i class="far fa-clock"></i> Last updated at <span id="lastUpdated">{{ now()->format('H:i') }}</span></small>
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
                <div class="h3 text-danger">{{ $critical }}</div>
            </div>
        </div>
        <div class="col">
            <div class="card h-100 shadow-sm text-center p-3">
                <div class="small text-muted">Warnings</div>
                <div class="h3 text-warning">{{ $warning }}</div>
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
    <ul class="nav nav-tabs mb-3" id="alertsTab" role="tablist">
        @foreach($tabs as $tabKey => $tabName)
            <li class="nav-item" role="presentation">
                <a class="nav-link @if($tab === $tabKey) active @endif" 
                   href="?tab={{ $tabKey }}"
                   role="tab">
                    {{ $tabName }}
                    @if($tabKey === 'bos' && $bosAlertsCount > 0)
                        <span class="badge bg-danger">{{ $bosAlertsCount }}</span>
                    @elseif($tabKey === 'hos' && $hosAlertsCount > 0)
                        <span class="badge bg-warning">{{ $hosAlertsCount }}</span>
                    @endif
                </a>
            </li>
        @endforeach
    </ul>
    </ul>

    <div class="card p-4">
        <div class="fw-bold mb-2">{{ $tab==='unread' ? 'Unread Notifications' : 'All Notifications' }}</div>
        @forelse($alerts as $alert)
            @php
                $isCritical = in_array($alert->code, [3,6,8]);
                $isWarning = in_array($alert->code, [1,2,5,7]);
                $isMedium = in_array($alert->code, [2,4,5]);
                // Message generation
                $message = '';
                $level = '';
                if($alert->device_type == 'Pump') {
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
                $badge = $level === 'high' ? 'danger' : ($level === 'medium' ? 'warning' : 'secondary');
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
    <div class="card p-4">
        <div class="fw-bold mb-3">
            {{ $tabs[$tab] ?? 'Alerts' }}
            <span class="badge bg-primary">{{ $alerts->total() }}</span>
        </div>
        
        @if($alerts->count() > 0)
            <div class="table-responsive">
                <table class="table table-hover align-middle">
                    <thead class="table-light">
                        <tr>
                            <th style="width: 40px;"></th>
                            <th>Alert</th>
                            <th>Device</th>
                            <th>Station</th>
                            <th>Time</th>
                            <th>Status</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody id="alertsTableBody">
                        @foreach($alerts as $alert)
                            @php
                                $isCritical = in_array($alert->code, [3,6,8]);
                                $alertType = $isCritical ? 'danger' : 'warning';
                                $iconClass = $isCritical ? 'fa-exclamation-circle' : 'fa-exclamation-triangle';
                                $deviceType = $alert->device_type === 'BOS' ? 'BOS' : 'HOS';
                            @endphp
                            
                            <tr class="alert-row {{ !$alert->is_read ? 'table-row-unread' : '' }}">
                                <td>
                                    <i class="fas {{ $iconClass }} text-{{ $alertType }}"></i>
                                </td>
                                <td>
                                    <div class="fw-bold">{{ $alert->description }}</div>
                                    <small class="text-muted">Code: {{ $alert->code ?? 'N/A' }}</small>
                                </td>
                                <td>
                                    <span class="badge bg-{{ $alert->device_type === 'BOS' ? 'danger' : 'info' }}">
                                        {{ $deviceType }}
                                    </span>
                                    @if($alert->device_number)
                                        <div class="small text-muted">#{{ $alert->device_number }}</div>
                                    @endif
                                </td>
                                <td>
                                    {{ $alert->station ? $alert->station->name : 'N/A' }}
                                </td>
                                <td>
                                    <div title="{{ $alert->created_at->format('Y-m-d H:i:s') }}">
                                        {{ $alert->created_at->diffForHumans() }}
                                    </div>
                                </td>
                                <td>
                                    @if($alert->is_read)
                                        <span class="badge bg-secondary">Read</span>
                                    @else
                                        <span class="badge bg-success">Unread</span>
                                    @endif
                                </td>
                                <td>
                                    <div class="btn-group btn-group-sm">
                                        @if(!$alert->is_read)
                                            <form action="{{ route('alerts.mark-read', $alert->id) }}" method="POST" class="d-inline">
                                                @csrf
                                                <button type="submit" class="btn btn-outline-secondary" title="Mark as read">
                                                    <i class="far fa-check-circle"></i> Mark Read
                                                </button>
                                            </form>
                                        @endif
                                        <button class="btn btn-outline-primary" title="View details" onclick="showAlertDetails({{ $alert->id }})">
                                            <i class="fas fa-eye"></i> View
                                        </button>
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            
            <div class="mt-3 d-flex justify-content-between align-items-center">
                <div class="text-muted small">
                    Showing {{ $alerts->firstItem() }} to {{ $alerts->lastItem() }} of {{ $alerts->total() }} entries
                </div>
                <div>
                    {{ $alerts->withQueryString()->links() }}
                </div>
            </div>
        @else
            <div class="text-center py-5">
                <i class="far fa-check-circle fa-4x text-success mb-3"></i>
                <h5>No alerts found</h5>
                <p class="text-muted">There are no alerts to display for the selected filter.</p>
            </div>
        @endif
    </div>
    
    <!-- Alert Details Modal -->
    <div class="modal fade" id="alertDetailsModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Alert Details</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body" id="alertDetailsContent">
                    <!-- Content will be loaded via AJAX -->
                    <div class="text-center py-5">
                        <div class="spinner-border text-primary" role="status">
                            <span class="visually-hidden">Loading...</span>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                </div>
            </div>
        </div>
    </div>
</div>
@push('styles')
<style>
    .table-row-unread {
        background-color: #f8f9fa;
    }
    .table-row-unread td {
        font-weight: 500;
    }
    .alert-item {
        transition: all 0.2s;
    }
    .alert-item:hover {
        transform: translateX(5px);
        box-shadow: 0 0.5rem 1rem rgba(0, 0, 0, 0.05);
    }
    .nav-tabs .nav-link {
        position: relative;
        padding: 0.75rem 1.25rem;
    }
    .nav-tabs .nav-link .badge {
        position: absolute;
        top: -5px;
        right: -5px;
        font-size: 0.6rem;
        padding: 0.25em 0.4em;
    }
</style>
@endpush

@push('scripts')
<script>
    // Auto-refresh the page every 60 seconds
    let refreshInterval = setInterval(updateLastUpdated, 60000);
    
    function updateLastUpdated() {
        document.getElementById('lastUpdated').textContent = new Date().toLocaleTimeString('en-US', { 
            hour: '2-digit', 
            minute: '2-digit',
            hour12: false
        });
    }
    
    // Search functionality
    document.getElementById('alertSearch').addEventListener('keyup', function(e) {
        const searchTerm = e.target.value.toLowerCase();
        const rows = document.querySelectorAll('#alertsTableBody tr');
        
        rows.forEach(row => {
            const text = row.textContent.toLowerCase();
            row.style.display = text.includes(searchTerm) ? '' : 'none';
        });
    });
    
    // Show alert details in modal
    function showAlertDetails(alertId) {
        const modal = new bootstrap.Modal(document.getElementById('alertDetailsModal'));
        const content = document.getElementById('alertDetailsContent');
        
        // Show loading state
        content.innerHTML = `
            <div class="text-center py-5">
                <div class="spinner-border text-primary" role="status">
                    <span class="visually-hidden">Loading...</span>
                </div>
            </div>`;
        
        // Load alert details via AJAX
        fetch(`/api/bos/alerts/${alertId}`)
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    const alert = data.data;
                    const date = new Date(alert.created_at);
                    const formattedDate = date.toLocaleString();
                    
                    content.innerHTML = `
                        <div class="alert alert-${alert.is_critical ? 'danger' : 'warning'}">
                            <h5 class="alert-heading">${alert.description}</h5>
                            <hr>
                            <div class="row mb-3">
                                <div class="col-md-6">
                                    <p class="mb-1"><strong>Alert Code:</strong> ${alert.code || 'N/A'}</p>
                                    <p class="mb-1"><strong>Device Type:</strong> ${alert.device_type}</p>
                                    <p class="mb-1"><strong>Device Number:</strong> ${alert.device_number || 'N/A'}</p>
                                </div>
                                <div class="col-md-6">
                                    <p class="mb-1"><strong>Station:</strong> ${alert.station ? alert.station.name : 'N/A'}</p>
                                    <p class="mb-1"><strong>Status:</strong> ${alert.is_read ? 'Read' : 'Unread'}</p>
                                    <p class="mb-1"><strong>Time:</strong> ${formattedDate}</p>
                                </div>
                            </div>
                            ${alert.meta ? `
                            <div class="mt-3">
                                <h6>Additional Information:</h6>
                                <pre class="bg-light p-2 rounded">${JSON.stringify(alert.meta, null, 2)}</pre>
                            </div>` : ''}
                        </div>`;
                } else {
                    content.innerHTML = `
                        <div class="alert alert-danger">
                            <i class="fas fa-exclamation-circle me-2"></i>
                            Failed to load alert details. Please try again.
                        </div>`;
                }
            })
            .catch(error => {
                console.error('Error:', error);
                content.innerHTML = `
                    <div class="alert alert-danger">
                        <i class="fas fa-exclamation-circle me-2"></i>
                        An error occurred while loading alert details.
                    </div>`;
            });
        
        modal.show();
    }
    
    // Auto-refresh the alerts every 2 minutes
    setInterval(() => {
        if (!document.hidden) {
            window.location.reload();
        }
    }, 120000);
</script>
@endpush

@endsection
