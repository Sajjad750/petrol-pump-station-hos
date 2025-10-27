<!-- Filters Card -->
<div class="card mb-3">
    <div class="card-header custom-card-header">
        <h5 class="mb-0"><i class="fas fa-filter"></i> Filters</h5>
    </div>
    <div class="card-body">
        <form id="tank-deliveries-filter-form">
            <div class="row">
                <div class="col-md-2">
                    <div class="form-group">
                        <label for="deliveries_from_date">From Date</label>
                        <input type="date" class="form-control" id="deliveries_from_date" name="from_date">
                    </div>
                </div>
                <div class="col-md-2">
                    <div class="form-group">
                        <label for="deliveries_to_date">To Date</label>
                        <input type="date" class="form-control" id="deliveries_to_date" name="to_date">
                    </div>
                </div>
                <div class="col-md-2">
                    <div class="form-group">
                        <label for="deliveries_station_id">Station</label>
                        <select class="form-control" id="deliveries_station_id" name="station_id">
                            <option value="">All Stations</option>
                        </select>
                    </div>
                </div>
                <div class="col-md-2">
                    <div class="form-group">
                        <label for="deliveries_fuel_grade_id">Fuel Grade</label>
                        <select class="form-control" id="deliveries_fuel_grade_id" name="fuel_grade_id">
                            <option value="">All Fuel Grades</option>
                        </select>
                    </div>
                </div>
                <div class="col-md-2">
                    <div class="form-group">
                        <label for="deliveries_tank">Tank</label>
                        <input type="text" class="form-control" id="deliveries_tank" name="tank" placeholder="Tank ID">
                    </div>
                </div>
                <div class="col-md-2">
                    <div class="form-group">
                        <label for="deliveries_pts_delivery_id">PTS Delivery ID</label>
                        <input type="text" class="form-control" id="deliveries_pts_delivery_id" name="pts_delivery_id" placeholder="Delivery ID">
                    </div>
                </div>
            </div>
            <div class="row">
                <div class="col-md-12 d-flex justify-content-end" style="gap: 10px;">
                    <button type="button" id="deliveries-filter-btn" class="btn btn-primary">
                        <i class="fas fa-filter"></i> Apply Filters
                    </button>
                    <button type="button" id="deliveries-reset-btn" class="btn btn-secondary">
                        <i class="fas fa-redo"></i> Reset
                    </button>
                    <button type="button" id="deliveries-export-excel-btn" class="btn btn-success">
                        <i class="fas fa-file-excel"></i> Export Excel
                    </button>
                    <button type="button" id="deliveries-export-pdf-btn" class="btn btn-danger">
                        <i class="fas fa-file-pdf"></i> Export PDF
                    </button>
                </div>
            </div>
        </form>
    </div>
</div>

<!-- Tank Deliveries Table Card -->
<div class="card custom-card">
    <div class="card-header custom-card-header">
        <h4 class="mb-0"><i class="fas fa-table"></i> Tank Deliveries Data</h4>
    </div>
    <div class="card-body">
        <div class="table-responsive" style="max-height: 600px; overflow-y: auto;">
            <table id="tank-deliveries-table" class="table-bordered table-striped table-hover table">
                <thead class="custom-table-header" style="position: sticky; top: 0; z-index: 10;">
                    <tr>
                        <th>ID</th>
                        <th>UUID</th>
                        <th>Request ID</th>
                        <th>PTS ID</th>
                        <th>PTS Delivery ID</th>
                        <th>Tank</th>
                        <th>Fuel Grade ID</th>
                        <th>Fuel Grade Name</th>
                        <th>Config ID</th>
                        <th>Start DateTime</th>
                        <th>Start Product Height</th>
                        <th>Start Water Height</th>
                        <th>Start Temperature</th>
                        <th>Start Product Volume</th>
                        <th>Start Product TC Volume</th>
                        <th>Start Product Density</th>
                        <th>Start Product Mass</th>
                        <th>End DateTime</th>
                        <th>End Product Height</th>
                        <th>End Water Height</th>
                        <th>End Temperature</th>
                        <th>End Product Volume</th>
                        <th>End Product TC Volume</th>
                        <th>End Product Density</th>
                        <th>End Product Mass</th>
                        <th>Received Product Volume</th>
                        <th>Absolute Product Height</th>
                        <th>Absolute Water Height</th>
                        <th>Absolute Temperature</th>
                        <th>Absolute Product Volume</th>
                        <th>Absolute Product TC Volume</th>
                        <th>Absolute Product Density</th>
                        <th>Absolute Product Mass</th>
                        <th>Pumps Dispensed Volume</th>
                        <th>Probe Data</th>
                        <th>Station ID</th>
                        <th>BOS Tank Delivery ID</th>
                        <th>BOS UUID</th>
                        <th>Synced At</th>
                        <th>Created At BOS</th>
                        <th>Updated At BOS</th>
                        <th>Created At</th>
                        <th>Updated At</th>
                    </tr>
                </thead>
                <tbody>
                    <!-- DataTable will populate this -->
                </tbody>
            </table>
        </div>
    </div>
</div>

@push('js')
    <script>
        $(document).ready(function() {
            var deliveriesTable = $('#tank-deliveries-table').DataTable({
                'processing': true,
                'serverSide': true,
                'responsive': true,
                'lengthChange': true,
                'autoWidth': false,
                'pageLength': 10,
                'order': [
                    [9, 'desc']
                ],
                'ajax': {
                    'url': '{{ route('hos-reports.tank-deliveries') }}',
                    'data': function(d) {
                        d.from_date = $('#deliveries_from_date').val();
                        d.to_date = $('#deliveries_to_date').val();
                        d.station_id = $('#deliveries_station_id').val();
                        d.fuel_grade_id = $('#deliveries_fuel_grade_id').val();
                        d.tank = $('#deliveries_tank').val();
                        d.pts_delivery_id = $('#deliveries_pts_delivery_id').val();
                    }
                },
                'columns': [{
                        data: 'id',
                        name: 'id'
                    },
                    {
                        data: 'uuid',
                        name: 'uuid'
                    },
                    {
                        data: 'request_id',
                        name: 'request_id'
                    },
                    {
                        data: 'pts_id',
                        name: 'pts_id'
                    },
                    {
                        data: 'pts_delivery_id',
                        name: 'pts_delivery_id'
                    },
                    {
                        data: 'tank',
                        name: 'tank'
                    },
                    {
                        data: 'fuel_grade_id',
                        name: 'fuel_grade_id'
                    },
                    {
                        data: 'fuel_grade_name',
                        name: 'fuel_grade_name'
                    },
                    {
                        data: 'configuration_id',
                        name: 'configuration_id'
                    },
                    {
                        data: 'start_datetime',
                        name: 'start_datetime'
                    },
                    {
                        data: 'start_product_height',
                        name: 'start_product_height',
                        render: function(data) {
                            return data ? parseFloat(data).toFixed(2) : '';
                        }
                    },
                    {
                        data: 'start_water_height',
                        name: 'start_water_height',
                        render: function(data) {
                            return data ? parseFloat(data).toFixed(2) : '';
                        }
                    },
                    {
                        data: 'start_temperature',
                        name: 'start_temperature',
                        render: function(data) {
                            return data ? parseFloat(data).toFixed(2) : '';
                        }
                    },
                    {
                        data: 'start_product_volume',
                        name: 'start_product_volume',
                        render: function(data) {
                            return data ? parseFloat(data).toFixed(2) : '';
                        }
                    },
                    {
                        data: 'start_product_tc_volume',
                        name: 'start_product_tc_volume',
                        render: function(data) {
                            return data ? parseFloat(data).toFixed(2) : '';
                        }
                    },
                    {
                        data: 'start_product_density',
                        name: 'start_product_density',
                        render: function(data) {
                            return data ? parseFloat(data).toFixed(2) : '';
                        }
                    },
                    {
                        data: 'start_product_mass',
                        name: 'start_product_mass',
                        render: function(data) {
                            return data ? parseFloat(data).toFixed(2) : '';
                        }
                    },
                    {
                        data: 'end_datetime',
                        name: 'end_datetime'
                    },
                    {
                        data: 'end_product_height',
                        name: 'end_product_height',
                        render: function(data) {
                            return data ? parseFloat(data).toFixed(2) : '';
                        }
                    },
                    {
                        data: 'end_water_height',
                        name: 'end_water_height',
                        render: function(data) {
                            return data ? parseFloat(data).toFixed(2) : '';
                        }
                    },
                    {
                        data: 'end_temperature',
                        name: 'end_temperature',
                        render: function(data) {
                            return data ? parseFloat(data).toFixed(2) : '';
                        }
                    },
                    {
                        data: 'end_product_volume',
                        name: 'end_product_volume',
                        render: function(data) {
                            return data ? parseFloat(data).toFixed(2) : '';
                        }
                    },
                    {
                        data: 'end_product_tc_volume',
                        name: 'end_product_tc_volume',
                        render: function(data) {
                            return data ? parseFloat(data).toFixed(2) : '';
                        }
                    },
                    {
                        data: 'end_product_density',
                        name: 'end_product_density',
                        render: function(data) {
                            return data ? parseFloat(data).toFixed(2) : '';
                        }
                    },
                    {
                        data: 'end_product_mass',
                        name: 'end_product_mass',
                        render: function(data) {
                            return data ? parseFloat(data).toFixed(2) : '';
                        }
                    },
                    {
                        data: 'received_product_volume',
                        name: 'received_product_volume',
                        render: function(data) {
                            return data ? parseFloat(data).toFixed(2) : '';
                        }
                    },
                    {
                        data: 'absolute_product_height',
                        name: 'absolute_product_height',
                        render: function(data) {
                            return data ? parseFloat(data).toFixed(2) : '';
                        }
                    },
                    {
                        data: 'absolute_water_height',
                        name: 'absolute_water_height',
                        render: function(data) {
                            return data ? parseFloat(data).toFixed(2) : '';
                        }
                    },
                    {
                        data: 'absolute_temperature',
                        name: 'absolute_temperature',
                        render: function(data) {
                            return data ? parseFloat(data).toFixed(2) : '';
                        }
                    },
                    {
                        data: 'absolute_product_volume',
                        name: 'absolute_product_volume',
                        render: function(data) {
                            return data ? parseFloat(data).toFixed(2) : '';
                        }
                    },
                    {
                        data: 'absolute_product_tc_volume',
                        name: 'absolute_product_tc_volume',
                        render: function(data) {
                            return data ? parseFloat(data).toFixed(2) : '';
                        }
                    },
                    {
                        data: 'absolute_product_density',
                        name: 'absolute_product_density',
                        render: function(data) {
                            return data ? parseFloat(data).toFixed(2) : '';
                        }
                    },
                    {
                        data: 'absolute_product_mass',
                        name: 'absolute_product_mass',
                        render: function(data) {
                            return data ? parseFloat(data).toFixed(2) : '';
                        }
                    },
                    {
                        data: 'pumps_dispensed_volume',
                        name: 'pumps_dispensed_volume',
                        render: function(data) {
                            return data ? parseFloat(data).toFixed(2) : '';
                        }
                    },
                    {
                        data: 'probe_data',
                        name: 'probe_data'
                    },
                    {
                        data: 'station_id',
                        name: 'station_id'
                    },
                    {
                        data: 'bos_tank_delivery_id',
                        name: 'bos_tank_delivery_id'
                    },
                    {
                        data: 'bos_uuid',
                        name: 'bos_uuid'
                    },
                    {
                        data: 'synced_at',
                        name: 'synced_at'
                    },
                    {
                        data: 'created_at_bos',
                        name: 'created_at_bos'
                    },
                    {
                        data: 'updated_at_bos',
                        name: 'updated_at_bos'
                    },
                    {
                        data: 'created_at',
                        name: 'created_at'
                    },
                    {
                        data: 'updated_at',
                        name: 'updated_at'
                    }
                ],
                'language': {
                    'processing': '<i class="fas fa-spinner fa-spin"></i> Loading...',
                    'emptyTable': 'No tank delivery records found',
                    'zeroRecords': 'No matching tank delivery records found'
                }
            });

            // Apply filters button
            $('#deliveries-filter-btn').on('click', function() {
                deliveriesTable.draw();
            });

            // Reset filters button
            $('#deliveries-reset-btn').on('click', function() {
                $('#deliveries_from_date').val('');
                $('#deliveries_to_date').val('');
                $('#deliveries_station_id').val('');
                $('#deliveries_fuel_grade_id').val('');
                $('#deliveries_tank').val('');
                $('#deliveries_pts_delivery_id').val('');
                deliveriesTable.draw();
            });

            // Allow Enter key to trigger filter
            $('#tank-deliveries-filter-form input, #tank-deliveries-filter-form select').on('keypress', function(e) {
                if (e.which === 13) {
                    e.preventDefault();
                    deliveriesTable.draw();
                }
            });

            // Export to Excel
            $('#deliveries-export-excel-btn').on('click', function() {
                const filters = {
                    from_date: $('#deliveries_from_date').val(),
                    to_date: $('#deliveries_to_date').val(),
                    station_id: $('#deliveries_station_id').val(),
                    fuel_grade_id: $('#deliveries_fuel_grade_id').val(),
                    tank: $('#deliveries_tank').val(),
                    pts_delivery_id: $('#deliveries_pts_delivery_id').val()
                };
                const queryString = $.param(filters);
                window.location.href = '{{ route('hos-reports.tank-deliveries.export.excel') }}?' + queryString;
            });

            // Export to PDF
            $('#deliveries-export-pdf-btn').on('click', function() {
                const filters = {
                    from_date: $('#deliveries_from_date').val(),
                    to_date: $('#deliveries_to_date').val(),
                    station_id: $('#deliveries_station_id').val(),
                    fuel_grade_id: $('#deliveries_fuel_grade_id').val(),
                    tank: $('#deliveries_tank').val(),
                    pts_delivery_id: $('#deliveries_pts_delivery_id').val()
                };
                const queryString = $.param(filters);
                window.location.href = '{{ route('hos-reports.tank-deliveries.export.pdf') }}?' + queryString;
            });

            // Load stations for dropdown
            $.ajax({
                url: '{{ route('hos-reports.stations') }}',
                method: 'GET',
                success: function(response) {
                    if (response.stations) {
                        response.stations.forEach(function(station) {
                            $('#deliveries_station_id').append(
                                $('<option></option>').val(station.id).text(station.site_name)
                            );
                        });
                    }
                }
            });

            // Auto-filter on dropdown change
            $('#deliveries_station_id, #deliveries_fuel_grade_id').on('change', function() {
                deliveriesTable.draw();
            });
        });
    </script>
@endpush
