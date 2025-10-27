<!-- Filters Card -->
<div class="card mb-3">
    <div class="card-header custom-card-header">
        <h5 class="mb-0"><i class="fas fa-filter"></i> Filters</h5>
    </div>
    <div class="card-body">
        <form id="tank-inventory-filter-form">
            <div class="row">
                <div class="col-md-2">
                    <div class="form-group">
                        <label for="inventory_from_date">From Date</label>
                        <input type="date" class="form-control" id="inventory_from_date" name="from_date">
                    </div>
                </div>
                <div class="col-md-2">
                    <div class="form-group">
                        <label for="inventory_to_date">To Date</label>
                        <input type="date" class="form-control" id="inventory_to_date" name="to_date">
                    </div>
                </div>
                <div class="col-md-2">
                    <div class="form-group">
                        <label for="inventory_from_time">From Time</label>
                        <input type="time" class="form-control" id="inventory_from_time" name="from_time">
                    </div>
                </div>
                <div class="col-md-2">
                    <div class="form-group">
                        <label for="inventory_to_time">To Time</label>
                        <input type="time" class="form-control" id="inventory_to_time" name="to_time">
                    </div>
                </div>
                <div class="col-md-2">
                    <div class="form-group">
                        <label for="inventory_station_id">Station</label>
                        <select class="form-control" id="inventory_station_id" name="station_id">
                            <option value="">All Stations</option>
                        </select>
                    </div>
                </div>
                <div class="col-md-2">
                    <div class="form-group">
                        <label for="inventory_fuel_grade_id">Fuel Grade</label>
                        <select class="form-control" id="inventory_fuel_grade_id" name="fuel_grade_id">
                            <option value="">All Fuel Grades</option>
                        </select>
                    </div>
                </div>
            </div>
            <div class="row">
                <div class="col-md-12 d-flex justify-content-end" style="gap: 10px;">
                    <button type="button" id="inventory-filter-btn" class="btn btn-primary">
                        <i class="fas fa-filter"></i> Apply Filters
                    </button>
                    <button type="button" id="inventory-reset-btn" class="btn btn-secondary">
                        <i class="fas fa-redo"></i> Reset
                    </button>
                    <button type="button" id="inventory-export-excel-btn" class="btn btn-success">
                        <i class="fas fa-file-excel"></i> Export Excel
                    </button>
                    <button type="button" id="inventory-export-pdf-btn" class="btn btn-danger">
                        <i class="fas fa-file-pdf"></i> Export PDF
                    </button>
                </div>
            </div>
        </form>
    </div>
</div>

<!-- Tank Inventory Table Card -->
<div class="card custom-card">
    <div class="card-header custom-card-header">
        <h4 class="mb-0"><i class="fas fa-table"></i> Tank Inventory Data</h4>
    </div>
    <div class="card-body">
        <div class="table-responsive">
            <table id="tank-inventory-table" class="table-bordered table-striped table-hover table">
                <thead class="custom-table-header">
                    <tr>
                        <th>Site</th>
                        <th>Fuel Grade ID</th>
                        <th>Fuel Grade Name</th>
                        <th>Configuration ID</th>
                        <th>Snapshot DateTime</th>
                        <th>Date Time</th>
                        <th>Status</th>
                        <th>Alarms</th>
                        <th>Absolute Product Height</th>
                        <th>Absolute Water Height</th>
                        <th>Absolute Temperature</th>
                        <th>Absolute Product Volume</th>
                        <th>Absolute Product TC Volume</th>
                        <th>Absolute Product Density</th>
                        <th>Absolute Product Mass</th>
                        <th>Pumps Dispensed Volume</th>
                        <th>Probe Data</th>
                        <th>Product Height</th>
                        <th>Water Height</th>
                        <th>Temperature</th>
                        <th>Product Volume</th>
                        <th>Water Volume</th>
                        <th>Product Ullage</th>
                        <th>Product TC Volume</th>
                        <th>Product Density</th>
                        <th>Product Mass</th>
                        <th>Tank Filling %</th>
                        <th>Created At</th>
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
            var inventoryTable = $('#tank-inventory-table').DataTable({
                'processing': true,
                'serverSide': true,
                'responsive': true,
                'lengthChange': true,
                'autoWidth': false,
                'pageLength': 10,
                'order': [
                    [4, 'desc']
                ],
                'ajax': {
                    'url': '{{ route('hos-reports.tank-inventory') }}',
                    'data': function(d) {
                        d.from_date = $('#inventory_from_date').val();
                        d.to_date = $('#inventory_to_date').val();
                        d.from_time = $('#inventory_from_time').val();
                        d.to_time = $('#inventory_to_time').val();
                        d.station_id = $('#inventory_station_id').val();
                        d.fuel_grade_id = $('#inventory_fuel_grade_id').val();
                    }
                },
                'columns': [{
                        data: 'station_id',
                        name: 'station_id'
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
                        data: 'snapshot_datetime',
                        name: 'snapshot_datetime'
                    },
                    {
                        data: 'date_time',
                        name: 'date_time'
                    },
                    {
                        data: 'status',
                        name: 'status'
                    },
                    {
                        data: 'alarms',
                        name: 'alarms'
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
                        data: 'product_height',
                        name: 'product_height',
                        render: function(data) {
                            return data ? parseFloat(data).toFixed(2) : '';
                        }
                    },
                    {
                        data: 'water_height',
                        name: 'water_height',
                        render: function(data) {
                            return data ? parseFloat(data).toFixed(2) : '';
                        }
                    },
                    {
                        data: 'temperature',
                        name: 'temperature',
                        render: function(data) {
                            return data ? parseFloat(data).toFixed(2) : '';
                        }
                    },
                    {
                        data: 'product_volume',
                        name: 'product_volume',
                        render: function(data) {
                            return data ? parseFloat(data).toFixed(2) : '';
                        }
                    },
                    {
                        data: 'water_volume',
                        name: 'water_volume',
                        render: function(data) {
                            return data ? parseFloat(data).toFixed(2) : '';
                        }
                    },
                    {
                        data: 'product_ullage',
                        name: 'product_ullage',
                        render: function(data) {
                            return data ? parseFloat(data).toFixed(2) : '';
                        }
                    },
                    {
                        data: 'product_tc_volume',
                        name: 'product_tc_volume',
                        render: function(data) {
                            return data ? parseFloat(data).toFixed(2) : '';
                        }
                    },
                    {
                        data: 'product_density',
                        name: 'product_density',
                        render: function(data) {
                            return data ? parseFloat(data).toFixed(2) : '';
                        }
                    },
                    {
                        data: 'product_mass',
                        name: 'product_mass',
                        render: function(data) {
                            return data ? parseFloat(data).toFixed(2) : '';
                        }
                    },
                    {
                        data: 'tank_filling_percentage',
                        name: 'tank_filling_percentage',
                        render: function(data) {
                            return data ? parseFloat(data).toFixed(2) + '%' : '';
                        }
                    },
                    {
                        data: 'created_at',
                        name: 'created_at'
                    }
                ],
                'language': {
                    'processing': '<i class="fas fa-spinner fa-spin"></i> Loading...',
                    'emptyTable': 'No tank inventory records found',
                    'zeroRecords': 'No matching tank inventory records found'
                }
            });

            // Apply filters button
            $('#inventory-filter-btn').on('click', function() {
                inventoryTable.draw();
            });

            // Reset filters button
            $('#inventory-reset-btn').on('click', function() {
                $('#inventory_from_date').val('');
                $('#inventory_to_date').val('');
                $('#inventory_from_time').val('');
                $('#inventory_to_time').val('');
                $('#inventory_station_id').val('');
                $('#inventory_fuel_grade_id').val('');
                inventoryTable.draw();
            });

            // Allow Enter key to trigger filter
            $('#tank-inventory-filter-form input, #tank-inventory-filter-form select').on('keypress', function(e) {
                if (e.which === 13) {
                    e.preventDefault();
                    inventoryTable.draw();
                }
            });

            // Export to Excel
            $('#inventory-export-excel-btn').on('click', function() {
                const filters = {
                    from_date: $('#inventory_from_date').val(),
                    to_date: $('#inventory_to_date').val(),
                    from_time: $('#inventory_from_time').val(),
                    to_time: $('#inventory_to_time').val(),
                    station_id: $('#inventory_station_id').val(),
                    fuel_grade_id: $('#inventory_fuel_grade_id').val()
                };
                const queryString = $.param(filters);
                window.location.href = '{{ route('hos-reports.tank-inventory.export.excel') }}?' + queryString;
            });

            // Export to PDF
            $('#inventory-export-pdf-btn').on('click', function() {
                const filters = {
                    from_date: $('#inventory_from_date').val(),
                    to_date: $('#inventory_to_date').val(),
                    from_time: $('#inventory_from_time').val(),
                    to_time: $('#inventory_to_time').val(),
                    station_id: $('#inventory_station_id').val(),
                    fuel_grade_id: $('#inventory_fuel_grade_id').val()
                };
                const queryString = $.param(filters);
                window.location.href = '{{ route('hos-reports.tank-inventory.export.pdf') }}?' + queryString;
            });

            // Load stations for dropdown
            $.ajax({
                url: '{{ route('hos-reports.stations') }}',
                method: 'GET',
                success: function(response) {
                    if (response.stations) {
                        response.stations.forEach(function(station) {
                            $('#inventory_station_id').append(
                                $('<option></option>').val(station.id).text(station.site_name)
                            );
                        });
                    }
                }
            });

            // Auto-filter on dropdown change
            $('#inventory_station_id, #inventory_fuel_grade_id').on('change', function() {
                inventoryTable.draw();
            });
        });
    </script>
@endpush
