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
                                <div class="col-md-2">
                                    <div class="form-group">
                                        <label for="from_date">Start Date</label>
                                        <input type="date" class="form-control" id="from_date" name="from_date">
                                    </div>
                                </div>
                                <div class="col-md-2">
                                    <div class="form-group">
                                        <label for="to_date">End Date</label>
                                        <input type="date" class="form-control" id="to_date" name="to_date">
                                    </div>
                                </div>
                                <div class="col-md-2">
                                    <div class="form-group">
                                        <label for="from_time">Start Time</label>
                                        <input type="time" class="form-control" id="from_time" name="from_time">
                                    </div>
                                </div>
                                <div class="col-md-2">
                                    <div class="form-group">
                                        <label for="to_time">End Time</label>
                                        <input type="time" class="form-control" id="to_time" name="to_time">
                                    </div>
                                </div>
                                <div class="col-md-2">
                                    <div class="form-group">
                                        <label for="status">Status</label>
                                        <select class="form-control" id="status" name="status">
                                            <option value="">All Status</option>
                                        </select>
                                    </div>
                                </div>
                                <div class="col-md-2">
                                    <div class="form-group">
                                        <label for="close_type">Close Type</label>
                                        <select class="form-control" id="close_type" name="close_type">
                                            <option value="">All Types</option>
                                        </select>
                                    </div>
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-md-3">
                                    <div class="form-group">
                                        <label for="user_id">User ID</label>
                                        <input type="text" class="form-control" id="user_id" name="user_id" placeholder="Search User ID">
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
                        <h4 class="mb-0">Shifts</h4>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table id="shifts-table" class="table-bordered table">
                                <thead class="custom-table-header">
                                    <tr>
                                        <th>ID</th>
                                        <th>Station</th>
                                        <th>Start Time</th>
                                        <th>Start Time UTC</th>
                                        <th>End Time</th>
                                        <th>End Time UTC</th>
                                        <th>User ID</th>
                                        <th>Status</th>
                                        <th>Close Type</th>
                                        <th>Auto Close Time</th>
                                        <th>Auto Close Time UTC</th>
                                        <th>Notes</th>
                                        <th>BOS Shift ID</th>
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
            var table = $('#shifts-table').DataTable({
                'processing': true,
                'serverSide': true,
                'ajax': {
                    'url': '{{ route('shifts') }}',
                    'data': function(d) {
                        d.from_date = $('#from_date').val();
                        d.to_date = $('#to_date').val();
                        d.from_time = $('#from_time').val();
                        d.to_time = $('#to_time').val();
                        d.status = $('#status').val();
                        d.close_type = $('#close_type').val();
                        d.user_id = $('#user_id').val();
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
                        data: 'start_time'
                    },
                    {
                        data: 'start_time_utc'
                    },
                    {
                        data: 'end_time'
                    },
                    {
                        data: 'end_time_utc'
                    },
                    {
                        data: 'user_id',
                        defaultContent: '-'
                    },
                    {
                        data: 'status_badge',
                        defaultContent: '-'
                    },
                    {
                        data: 'close_type_badge',
                        defaultContent: '-'
                    },
                    {
                        data: 'auto_close_time'
                    },
                    {
                        data: 'auto_close_time_utc'
                    },
                    {
                        data: 'notes',
                        defaultContent: '-',
                        render: function(data, type, row) {
                            if (data && data.length > 50) {
                                return data.substring(0, 50) + '...';
                            }
                            return data || '-';
                        }
                    },
                    {
                        data: 'bos_shift_id',
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

            // Load filter options
            $.ajax({
                url: '{{ route('shifts') }}',
                type: 'GET',
                data: {
                    get_filter_options: true
                },
                success: function(response) {
                    // Populate status dropdown
                    if (response.statuses) {
                        response.statuses.forEach(function(status) {
                            var statusText = status.charAt(0).toUpperCase() + status.slice(1);
                            $('#status').append('<option value="' + status + '">' + statusText + '</option>');
                        });
                    }

                    // Populate close type dropdown
                    if (response.close_types) {
                        response.close_types.forEach(function(closeType) {
                            var closeTypeText = closeType.charAt(0).toUpperCase() + closeType.slice(1);
                            $('#close_type').append('<option value="' + closeType + '">' + closeTypeText + '</option>');
                        });
                    }
                }
            });

            // Apply filters button
            $('#filter-btn').on('click', function() {
                table.draw();
            });

            // Reset filters button
            $('#reset-btn').on('click', function() {
                $('#from_date').val('');
                $('#to_date').val('');
                $('#from_time').val('');
                $('#to_time').val('');
                $('#status').val('');
                $('#close_type').val('');
                $('#user_id').val('');
                table.draw();
            });

            // Export to Excel
            $('#export-excel-btn').on('click', function() {
                const filters = {
                    start_date: $('#from_date').val(),
                    end_date: $('#to_date').val(),
                    start_time: $('#from_time').val(),
                    end_time: $('#to_time').val(),
                    status: $('#status').val(),
                    close_type: $('#close_type').val(),
                    user_id: $('#user_id').val()
                };
                const queryString = $.param(filters);
                window.location.href = '{{ route('shifts.export.excel') }}?' + queryString;
            });

            // Export to PDF
            $('#export-pdf-btn').on('click', function() {
                const filters = {
                    start_date: $('#from_date').val(),
                    end_date: $('#to_date').val(),
                    start_time: $('#from_time').val(),
                    end_time: $('#to_time').val(),
                    status: $('#status').val(),
                    close_type: $('#close_type').val(),
                    user_id: $('#user_id').val()
                };
                const queryString = $.param(filters);
                window.location.href = '{{ route('shifts.export.pdf') }}?' + queryString;
            });

            // Allow Enter key to trigger filter
            $('#filter-form input, #filter-form select').on('keypress', function(e) {
                if (e.which === 13) {
                    e.preventDefault();
                    table.draw();
                }
            });

            // Auto-filter on dropdown change
            $('#status, #close_type').on('change', function() {
                table.draw();
            });
        });
    </script>
@endpush

