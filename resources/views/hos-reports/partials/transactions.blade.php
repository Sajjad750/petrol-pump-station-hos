<!-- Filters Card -->
<div class="card mb-3">
    <div class="card-header custom-card-header">
        <h5 class="mb-0"><i class="fas fa-filter"></i> Filters</h5>
    </div>
    <div class="card-body">
        <form id="transactions-filter-form">
            <div class="row">
                <div class="col-md-3">
                    <div class="form-group">
                        <label for="transaction_from_date">From Date</label>
                        <input type="date" class="form-control" id="transaction_from_date" name="from_date">
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="form-group">
                        <label for="transaction_to_date">To Date</label>
                        <input type="date" class="form-control" id="transaction_to_date" name="to_date">
                    </div>
                </div>
                <!-- From Time -->
                <div class="col-md-2">
                    <div class="form-group">
                        <label for="transaction_from_time">From Time</label>
                        <input type="time" class="form-control" id="transaction_from_time" name="from_time">
                    </div>
                </div>
                <!-- To Time -->
                <div class="col-md-2">
                    <div class="form-group">
                        <label for="transaction_to_time">To Time</label>
                        <input type="time" class="form-control" id="transaction_to_time" name="to_time">
                    </div>
                </div>
                <div class="col-md-2">
                    <div class="form-group">
                        <label for="transaction_station_id">Station</label>
                        <select class="form-control" id="transaction_station_id" name="station_id">
                            <option value="">All Stations</option>
                        </select>
                    </div>
                </div>
                <div class="col-md-2">
                    <div class="form-group">
                        <label for="transaction_pump_id">Pump ID</label>
                        <input type="text" class="form-control" id="transaction_pump_id" name="pump_id" placeholder="Pump ID">
                    </div>
                </div>
                <div class="col-md-2">
                    <div class="form-group">
                        <label for="transaction_mop">Mode of Payment</label>
                        <select class="form-control" id="transaction_mop" name="mode_of_payment">
                            <option value="">All</option>
                            <option value="cash">Cash</option>
                            <option value="card">Card</option>
                            <option value="credit">Credit</option>
                        </select>
                    </div>
                </div>
            </div>
            <div class="row">
                <div class="col-md-12 d-flex justify-content-end" style="gap: 10px;">
                    <button type="button" id="transaction-filter-btn" class="btn btn-primary">
                        <i class="fas fa-filter"></i> Apply Filters
                    </button>
                    <button type="button" id="transaction-reset-btn" class="btn btn-secondary">
                        <i class="fas fa-redo"></i> Reset
                    </button>
                    <button type="button" id="transaction-export-excel-btn" class="btn btn-success">
                        <i class="fas fa-file-excel"></i> Export Excel
                    </button>
                    <button type="button" id="transaction-export-pdf-btn" class="btn btn-danger">
                        <i class="fas fa-file-pdf"></i> Export PDF
                    </button>
                </div>
            </div>
        </form>
    </div>
</div>

<!-- Transactions Table Card -->
<div class="card custom-card">
    <div class="card-header custom-card-header">
        <h4 class="mb-0"><i class="fas fa-table"></i> Transactions Data</h4>
    </div>
    <div class="card-body">
        <div class="table-responsive">
            <table id="transactions-table" class="table-bordered table-striped table-hover table">
                <thead class="custom-table-header">
                    <tr>
                        <th>ID</th>
                        <th>UUID</th>
                        <th>PTS Device ID</th>
                        <th>PTS ID</th>
                        <th>Request ID</th>
                        <th>Date Time Start</th>
                        <th>Date Time End</th>
                        <th>Pump ID</th>
                        <th>Nozzle ID</th>
                        <th>Fuel Grade ID</th>
                        <th>Tank ID</th>
                        <th>Transaction #</th>
                        <th>Volume</th>
                        <th>TC Volume</th>
                        <th>Price</th>
                        <th>Amount</th>
                        <th>Starting Totalizer</th>
                        <th>Total Volume</th>
                        <th>Total Amount</th>
                        <th>Tag</th>
                        <th>User ID</th>
                        <th>Config ID</th>
                        <th>Shift ID</th>
                        <th>Mode of Payment</th>
                        <th>Station</th>
                        <th>BOS Shift ID</th>
                        <th>BOS Transaction ID</th>
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
            var transactionsTable = $('#transactions-table').DataTable({
                'processing': true,
                'serverSide': true,
                'responsive': true,
                'lengthChange': true,
                'autoWidth': false,
                'pageLength': 10,
                'order': [
                    [0, 'desc']
                ],
                'ajax': {
                    'url': '{{ route('hos-reports') }}',
                    'data': function(d) {
                        d.from_date = $('#transaction_from_date').val();
                        d.to_date = $('#transaction_to_date').val();
                        d.from_time = $('#transaction_from_time').val();
                        d.to_time = $('#transaction_to_time').val();
                        d.station_id = $('#transaction_station_id').val();
                        d.pump_id = $('#transaction_pump_id').val();
                        d.mode_of_payment = $('#transaction_mop').val();
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
                        data: 'pts2_device_id',
                        name: 'pts2_device_id'
                    },
                    {
                        data: 'pts_id',
                        name: 'pts_id'
                    },
                    {
                        data: 'request_id',
                        name: 'request_id'
                    },
                    {
                        data: 'date_time_start',
                        name: 'date_time_start'
                    },
                    {
                        data: 'date_time_end',
                        name: 'date_time_end'
                    },
                    {
                        data: 'pts_pump_id',
                        name: 'pts_pump_id'
                    },
                    {
                        data: 'pts_nozzle_id',
                        name: 'pts_nozzle_id'
                    },
                    {
                        data: 'pts_fuel_grade_id',
                        name: 'pts_fuel_grade_id'
                    },
                    {
                        data: 'pts_tank_id',
                        name: 'pts_tank_id'
                    },
                    {
                        data: 'transaction_number',
                        name: 'transaction_number'
                    },
                    {
                        data: 'volume',
                        name: 'volume',
                        render: function(data) {
                            return data ? parseFloat(data).toFixed(2) : '';
                        }
                    },
                    {
                        data: 'tc_volume',
                        name: 'tc_volume',
                        render: function(data) {
                            return data ? parseFloat(data).toFixed(2) : '';
                        }
                    },
                    {
                        data: 'price',
                        name: 'price',
                        render: function(data) {
                            return data ? parseFloat(data).toFixed(2) : '';
                        }
                    },
                    {
                        data: 'amount',
                        name: 'amount',
                        render: function(data) {
                            return data ? parseFloat(data).toFixed(2) : '';
                        }
                    },
                    {
                        data: 'starting_totalizer',
                        name: 'starting_totalizer',
                        render: function(data) {
                            return data ? parseFloat(data).toFixed(2) : '';
                        }
                    },
                    {
                        data: 'total_volume',
                        name: 'total_volume',
                        render: function(data) {
                            return data ? parseFloat(data).toFixed(2) : '';
                        }
                    },
                    {
                        data: 'total_amount',
                        name: 'total_amount',
                        render: function(data) {
                            return data ? parseFloat(data).toFixed(2) : '';
                        }
                    },
                    {
                        data: 'tag',
                        name: 'tag'
                    },
                    {
                        data: 'pts_user_id',
                        name: 'pts_user_id'
                    },
                    {
                        data: 'pts_configuration_id',
                        name: 'pts_configuration_id'
                    },
                    {
                        data: 'shift_id',
                        name: 'shift_id'
                    },
                    {
                        data: 'mode_of_payment',
                        name: 'mode_of_payment'
                    },
                    {
                        data: 'station_id',
                        name: 'station_id'
                    },
                    {
                        data: 'bos_shift_id',
                        name: 'bos_shift_id'
                    },
                    {
                        data: 'bos_transaction_id',
                        name: 'bos_transaction_id'
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
                    'emptyTable': 'No transactions found',
                    'zeroRecords': 'No matching transactions found'
                }
            });

            // Apply filters button
            $('#transaction-filter-btn').on('click', function() {
                transactionsTable.draw();
            });

            // Reset filters button
            $('#transaction-reset-btn').on('click', function() {
                $('#transaction_from_date').val('');
                $('#transaction_to_date').val('');
                $('#transaction_from_time').val('');
                $('#transaction_to_time').val('');
                $('#transaction_station_id').val('');
                $('#transaction_pump_id').val('');
                $('#transaction_mop').val('');
                transactionsTable.draw();
            });

            // Allow Enter key to trigger filter
            $('#transactions-filter-form input, #transactions-filter-form select').on('keypress', function(e) {
                if (e.which === 13) {
                    e.preventDefault();
                    transactionsTable.draw();
                }
            });

            // Export to Excel
            $('#transaction-export-excel-btn').on('click', function() {
                const filters = {
                    from_date: $('#transaction_from_date').val(),
                    to_date: $('#transaction_to_date').val(),
                    from_time: $('#transaction_from_time').val(),
                    to_time: $('#transaction_to_time').val(),
                    station_id: $('#transaction_station_id').val(),
                    pump_id: $('#transaction_pump_id').val(),
                    mode_of_payment: $('#transaction_mop').val()
                };
                const queryString = $.param(filters);
                window.location.href = '{{ route('hos-reports.export.excel') }}?' + queryString;
            });

            // Export to PDF
            $('#transaction-export-pdf-btn').on('click', function() {
                const filters = {
                    from_date: $('#transaction_from_date').val(),
                    to_date: $('#transaction_to_date').val(),
                    from_time: $('#transaction_from_time').val(),
                    to_time: $('#transaction_to_time').val(),
                    station_id: $('#transaction_station_id').val(),
                    pump_id: $('#transaction_pump_id').val(),
                    mode_of_payment: $('#transaction_mop').val()
                };
                const queryString = $.param(filters);
                window.location.href = '{{ route('hos-reports.export.pdf') }}?' + queryString;
            });

            // Load stations for dropdown
            $.ajax({
                url: '{{ route('hos-reports.stations') }}',
                method: 'GET',
                success: function(response) {
                    if (response.stations) {
                        response.stations.forEach(function(station) {
                            $('#transaction_station_id').append(
                                $('<option></option>').val(station.id).text(station.site_name)
                            );
                        });
                    }
                }
            });

            // Auto-filter on dropdown change
            $('#transaction_station_id, #transaction_mop').on('change', function() {
                transactionsTable.draw();
            });
        });
    </script>
@endpush
