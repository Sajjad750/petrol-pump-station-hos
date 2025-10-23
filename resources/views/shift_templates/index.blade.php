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
                                <div class="col-md-4">
                                    <div class="form-group">
                                        <label for="timezone">Timezone</label>
                                        <select class="form-control" id="timezone" name="timezone">
                                            <option value="">All Timezones</option>
                                        </select>
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="form-group">
                                        <label for="device_id">Device ID</label>
                                        <input type="text" class="form-control" id="device_id" name="device_id" placeholder="Search Device ID">
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="form-group">
                                        <label for="station_id">Station</label>
                                        <select class="form-control" id="station_id" name="station_id">
                                            <option value="">All Stations</option>
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
                        <h4 class="mb-0">Shift Templates</h4>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table id="shift-templates-table" class="table-bordered table">
                                <thead class="custom-table-header">
                                    <tr>
                                        <th>ID</th>
                                        <th>Station</th>
                                        <th>UUID</th>
                                        <th>Device ID</th>
                                        <th>End Time (24h)</th>
                                        <th>End Time (12h)</th>
                                        <th>Timezone</th>
                                        <th>BOS Template ID</th>
                                    
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
            var table = $('#shift-templates-table').DataTable({
                'processing': true,
                'serverSide': true,
                'ajax': {
                    'url': '{{ route('shift_templates') }}',
                    'data': function(d) {
                        d.timezone = $('#timezone').val();
                        d.device_id = $('#device_id').val();
                        d.station_id = $('#station_id').val();
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
                        data: 'uuid',
                        defaultContent: '-'
                    },
                    {
                        data: 'pts2_device_id',
                        defaultContent: '-'
                    },
                    {
                        data: 'end_time_24h',
                        defaultContent: '-'
                    },
                    {
                        data: 'end_time_12h',
                        defaultContent: '-'
                    },
                    {
                        data: 'timezone_badge',
                        defaultContent: '-'
                    },
                    {
                        data: 'bos_shift_template_id',
                        defaultContent: '-'
                    },
                    
        
                ],
                'scrollX': true
            });

            // Load filter options
            $.ajax({
                url: '{{ route('shift_templates') }}',
                type: 'GET',
                data: {
                    get_filter_options: true
                },
                success: function(response) {
                    // Populate timezone dropdown
                    if (response.timezones) {
                        response.timezones.forEach(function(timezone) {
                            if (timezone) {
                                $('#timezone').append('<option value="' + timezone + '">' + timezone + '</option>');
                            }
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
                $('#timezone').val('');
                $('#device_id').val('');
                $('#station_id').val('');
                table.draw();
            });

            // Generate Report button
            // Export to Excel
            $('#export-excel-btn').on('click', function() {
                const filters = {
                    timezone: $('#timezone').val(),
                    device_id: $('#device_id').val(),
                    station_id: $('#station_id').val()
                };
                const queryString = $.param(filters);
                window.location.href = '{{ route('shift_templates.export.excel') }}?' + queryString;
            });

            // Export to PDF
            $('#export-pdf-btn').on('click', function() {
                const filters = {
                    timezone: $('#timezone').val(),
                    device_id: $('#device_id').val(),
                    station_id: $('#station_id').val()
                };
                const queryString = $.param(filters);
                window.location.href = '{{ route('shift_templates.export.pdf') }}?' + queryString;
            });

            // Allow Enter key to trigger filter
            $('#filter-form input, #filter-form select').on('keypress', function(e) {
                if (e.which === 13) {
                    e.preventDefault();
                    table.draw();
                }
            });

            // Auto-filter on timezone dropdown change
            $('#timezone').on('change', function() {
                table.draw();
            });

            // Load stations for dropdown
            $.ajax({
                url: '{{ route('shift_templates') }}',
                method: 'GET',
                data: { get_stations: true },
                success: function(response) {
                    if (response.stations) {
                        response.stations.forEach(function(station) {
                            $('#station_id').append(
                                $('<option></option>').val(station.id).text(station.site_name)
                            );
                        });
                    }
                }
            });

            // Auto-filter on station dropdown change
            $('#station_id').on('change', function() {
                table.draw();
            });
        });
    </script>
@endpush

