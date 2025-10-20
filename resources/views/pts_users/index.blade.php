@extends('layouts.adminlte')
@push('css')
@endpush

@section('content')
    <div class="container-fluid">
        <div class="row">
            <!-- Main Content -->
            <div class="col-md-12">
                <!-- Filters Card -->
                <div class="card custom-card mb-3">
                    <div class="card-header custom-card-header">
                        <h5 class="mb-0">Filters</h5>
                    </div>
                    <div class="card-body">
                        <form id="filter-form">
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label for="login">Login</label>
                                        <input type="text" class="form-control" id="login" name="login" placeholder="Search by login">
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label for="is_active">Status</label>
                                        <select class="form-control" id="is_active" name="is_active">
                                            <option value="">All Status</option>
                                            <option value="1">Active</option>
                                            <option value="0">Inactive</option>
                                        </select>
                                    </div>
                                </div>
                            </div>
                      
                            <div class="row">
                                <div class="col-md-12 d-flex justify-content-md-end justify-content-start" style="gap: 10px;">
                                    <button type="button" id="filter-btn" class="btn btn-primary">
                                        <i class="fas fa-filter"></i> Apply Filters
                                    </button>
                                    <button type="button" id="reset-btn" class="btn btn-secondary">
                                        <i class="fas fa-redo"></i> Reset
                                    </button>
                                    <button type="button" id="export-excel-btn" class="btn btn-success">
                                        <i class="fas fa-file-excel"></i> Export Excel
                                    </button>
                                    <button type="button" id="export-pdf-btn" class="btn btn-danger">
                                        <i class="fas fa-file-pdf"></i> Export PDF
                                    </button>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>

                <div class="card custom-card">
                    <div class="card-header custom-card-header">
                        <h4 class="mb-0">PTS Users</h4>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table id="pts-users-table" class="table-bordered table">
                                <thead class="custom-table-header">
                                    <tr>
                                        <th>ID</th>
                                        <th>Station</th>
                                        <th>PTS User ID</th>
                                        <th>Login</th>
                                        <th>Status</th>
                                        <th>Permissions</th>
                                        <th>Configuration</th>
                                        <th>Control</th>
                                        <th>Monitoring</th>
                                        <th>Reports</th>
                                        <th>BOS PTS User ID</th>
                                        <th>Synced At</th>
                                        <th>Created At BOS</th>
                                        <th>Updated At BOS</th>
                                        <th>Created At</th>
                                        <th>Updated At</th>
                                    </tr>
                                </thead>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('js')
    <script>
        $(document).ready(function() {
            var table = $('#pts-users-table').DataTable({
                'processing': true,
                'serverSide': true,
                'ajax': {
                    'url': '{{ route('pts_users') }}',
                    'data': function(d) {
                        d.login = $('#login').val();
                        d.is_active = $('#is_active').val();
                    }
                },
                'order': [0, 'desc'],
                'columns': [{
                        data: 'id'
                    },
                    {
                        data: 'station.site_name',
                        defaultContent: '-'
                    },
                    {
                        data: 'pts_user_id',
                        defaultContent: '-'
                    },
                    {
                        data: 'login',
                        defaultContent: '-'
                    },
                    {
                        data: 'active_badge',
                        defaultContent: '-'
                    },
                    {
                        data: 'permissions_summary',
                        defaultContent: '-'
                    },
                    {
                        data: 'config_badge',
                        defaultContent: '-'
                    },
                    {
                        data: 'control_badge',
                        defaultContent: '-'
                    },
                    {
                        data: 'monitoring_badge',
                        defaultContent: '-'
                    },
                    {
                        data: 'reports_badge',
                        defaultContent: '-'
                    },
                    {
                        data: 'bos_pts_user_id',
                        defaultContent: '-'
                    },
                    {
                        data: 'synced_at'
                    },
                    {
                        data: 'created_at_bos'
                    },
                    {
                        data: 'updated_at_bos'
                    },
                    {
                        data: 'created_at'
                    },
                    {
                        data: 'updated_at'
                    }
                ],
                'scrollX': true
            });

            // Apply filters button
            $('#filter-btn').on('click', function() {
                table.draw();
            });

            // Reset filters button
            $('#reset-btn').on('click', function() {
                $('#login').val('');
                $('#is_active').val('');
                table.draw();
            });

            // Generate Report button
            // Export to Excel
            $('#export-excel-btn').on('click', function() {
                const filters = {
                    login: $('#login').val(),
                    active_status: $('#is_active').val(),
                    permissions: $('input[name="permissions[]"]:checked').map(function() {
                        return $(this).val();
                    }).get()
                };
                const queryString = $.param(filters);
                window.location.href = '{{ route('pts_users.export.excel') }}?' + queryString;
            });

            // Export to PDF
            $('#export-pdf-btn').on('click', function() {
                const filters = {
                    login: $('#login').val(),
                    active_status: $('#is_active').val(),
                    permissions: $('input[name="permissions[]"]:checked').map(function() {
                        return $(this).val();
                    }).get()
                };
                const queryString = $.param(filters);
                window.location.href = '{{ route('pts_users.export.pdf') }}?' + queryString;
            });

            // Allow Enter key to trigger filter
            $('#filter-form input, #filter-form select').on('keypress', function(e) {
                if (e.which === 13) {
                    e.preventDefault();
                    table.draw();
                }
            });

            // Auto-filter on status dropdown change
            $('#is_active').on('change', function() {
                table.draw();
            });
        });
    </script>
@endpush

