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
                                        <label for="tank_search">Tank ID Search</label>
                                        <input type="text" class="form-control" id="tank_search" name="tank_search" placeholder="Search Tank ID">
                                    </div>
                                </div>
                                
                                <div class="col-md-2">
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
                        <h4 class="mb-0">Tank Inventories</h4>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table id="tank-inventories-table" class="table-bordered table">
                                <thead class="custom-table-header">
                                    <tr>
                                        <th>ID</th>
                                        <th>Station</th>
                                        <th>UUID</th>
                                        <th>Request ID</th>
                                        <th>PTS ID</th>
                                        <th>Tank</th>
                                        <th>Fuel Grade ID</th>
                                        <th>Fuel Grade Name</th>
                                        <th>Configuration ID</th>
                                        <th>Snapshot DateTime</th>
                                        <th>Product Height</th>
                                        <th>Water Height</th>
                                        <th>Temperature</th>
                                        <th>Product Volume</th>
                                        <th>Product TC Volume</th>
                                        <th>Product Density</th>
                                        <th>Product Mass</th>
                                        <th>Pumps Dispensed Volume</th>
                                        <th>Probe Data</th>
                                        <th>BOS Inventory ID</th>
                                        <th>BOS UUID</th>
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
            var table = $('#tank-inventories-table').DataTable({
                'processing': true,
                'serverSide': true,
                'ajax': {
                    'url': '{{ route('tank_inventories') }}',
                    'data': function(d) {
                        d.from_date = $('#from_date').val();
                        d.to_date = $('#to_date').val();
                        d.from_time = $('#from_time').val();
                        d.to_time = $('#to_time').val();
                        d.tank_search = $('#tank_search').val();
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
                        data: 'request_id',
                        defaultContent: '-'
                    },
                    {
                        data: 'pts_id',
                        defaultContent: '-'
                    },
                    {
                        data: 'tank',
                        defaultContent: '-'
                    },
                    {
                        data: 'fuel_grade_id',
                        defaultContent: '-'
                    },
                    {
                        data: 'fuel_grade_name',
                        defaultContent: '-'
                    },
                    {
                        data: 'configuration_id',
                        defaultContent: '-'
                    },
                    {
                        data: 'snapshot_datetime'
                    },
                    {
                        data: 'absolute_product_height'
                    },
                    {
                        data: 'absolute_water_height'
                    },
                    {
                        data: 'absolute_temperature'
                    },
                    {
                        data: 'absolute_product_volume'
                    },
                    {
                        data: 'absolute_product_tc_volume'
                    },
                    {
                        data: 'absolute_product_density'
                    },
                    {
                        data: 'absolute_product_mass'
                    },
                    {
                        data: 'pumps_dispensed_volume'
                    },
                    {
                        data: 'probe_data',
                        defaultContent: '-'
                    },
                    {
                        data: 'bos_tank_inventory_id',
                        defaultContent: '-'
                    },
                    {
                        data: 'bos_uuid',
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
                $('#from_date').val('');
                $('#to_date').val('');
                $('#from_time').val('');
                $('#to_time').val('');
                $('#tank_search').val('');
                $('#station_id').val('');
                table.draw();
            });

            // Export to Excel
            $('#export-excel-btn').on('click', function() {
                const filters = {
                    start_date: $('#from_date').val(),
                    end_date: $('#to_date').val(),
                    start_time: $('#from_time').val(),
                    end_time: $('#to_time').val(),
                    tank_id: $('#tank_search').val(),
                    station_id: $('#station_id').val()
                };
                const queryString = $.param(filters);
                window.location.href = '{{ route('tank_inventories.export.excel') }}?' + queryString;
            });

            // Export to PDF
            $('#export-pdf-btn').on('click', function() {
                const filters = {
                    start_date: $('#from_date').val(),
                    end_date: $('#to_date').val(),
                    start_time: $('#from_time').val(),
                    end_time: $('#to_time').val(),
                    tank_id: $('#tank_search').val(),
                    station_id: $('#station_id').val()
                };
                const queryString = $.param(filters);
                window.location.href = '{{ route('tank_inventories.export.pdf') }}?' + queryString;
            });

            // Allow Enter key to trigger filter
            $('#filter-form input, #filter-form select').on('keypress', function(e) {
                if (e.which === 13) {
                    e.preventDefault();
                    table.draw();
                }
            });

            // Load stations for dropdown
            $.ajax({
                url: '{{ route('tank_inventories') }}',
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

