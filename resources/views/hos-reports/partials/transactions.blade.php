<!-- Filters Card -->
<div class="card custom-card mb-3">
    <div class="card-header custom-card-header">
        <h6 class="mb-0" style="color: #D7D7D7;"><i class="fas fa-filter"></i> Filters</h6>
    </div>
    <div class="card-body">
        <form id="transactions-filter-form">
            <div class="row">
                <div class="col-md-2">
                    <div class="form-group">
                        <label for="transaction_from_date">From Date</label>
                        <input type="date" class="form-control" id="transaction_from_date" name="from_date">
                    </div>
                </div>
                <div class="col-md-2">
                    <div class="form-group">
                        <label for="transaction_to_date">To Date</label>
                        <input type="date" class="form-control" id="transaction_to_date" name="to_date">
                    </div>
                </div>
                <div class="col-md-2">
                    <div class="form-group">
                        <label for="transaction_from_time">From Time</label>
                        <input type="time" class="form-control" id="transaction_from_time" name="from_time">
                    </div>
                </div>
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
            </div>
            <div class="row">
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
                <div class="col-md-2">
                    <div class="form-group">
                        <label for="transaction_product_id">Product</label>
                        <select class="form-control" id="transaction_product_id" name="product_id">
                            <option value="">All Products</option>
                        </select>
                    </div>
                </div>
            </div>
            <div class="row">
                <div class="col-md-12 d-flex justify-content-end" style="gap: 10px;">
                    <button type="button" id="transaction-filter-btn" class="btn btn-dark">
                        <i class="fas fa-filter"></i> Search Filters
                    </button>
                    <button type="button" id="transaction-reset-btn" class="btn btn-dark">
                        <i class="fas fa-redo"></i> Reset
                    </button>
                    <button type="button" id="transaction-export-pdf-btn" class="btn btn-dark">
                        <i class="fas fa-file-pdf"></i> Export PDF
                    </button>
                </div>
            </div>
        </form>
    </div>
</div>

@push('css')
    <style>
        /* Remove all default DataTable styling */
        #transactions-table_wrapper {
            border: none !important;
        }

        #transactions-table_wrapper .dataTables_wrapper {
            border: none !important;
            padding: 0;
        }

        #transactions-table_wrapper .dataTables_scrollHead {
            border: none !important;
        }

        #transactions-table_wrapper .dataTables_scrollBody {
            border: none !important;
        }

        #transactions-table {
            width: 100% !important;
            border-collapse: collapse;
            background-color: white;
            border: none !important;
            margin: 0;
        }

        #transactions-table thead {
            background-color: #D7D7D7;
        }

        #transactions-table thead th {
            background-color: #D7D7D7 !important;
            color: #333 !important;
            font-weight: bold !important;
            padding: 8px 10px !important;
            text-align: left !important;
            border: none !important;
            border-top: none !important;
            border-bottom: none !important;
            border-left: none !important;
            border-right: none !important;
            font-size: 13px;
            white-space: nowrap;
        }

        #transactions-table thead th.text-right {
            text-align: right !important;
        }

        #transactions-table tbody {
            background-color: white;
        }

        #transactions-table tbody td {
            padding: 8px 10px !important;
            border: none !important;
            border-top: none !important;
            border-left: none !important;
            border-right: none !important;
            border-bottom: 1px solid #e0e0e0 !important;
            color: #555 !important;
            font-size: 13px;
            vertical-align: top;
            background-color: white !important;
        }

        /* Specific column widths for 19 columns */
        #transactions-table thead th:nth-child(1),
        #transactions-table tbody td:nth-child(1) {
            width: 5%; /* Site ID */
        }

        #transactions-table thead th:nth-child(2),
        #transactions-table tbody td:nth-child(2) {
            width: 7%; /* Site Name */
        }

        #transactions-table thead th:nth-child(3),
        #transactions-table tbody td:nth-child(3) {
            width: 6%; /* Trans ID */
        }

        #transactions-table thead th:nth-child(4),
        #transactions-table tbody td:nth-child(4) {
            width: 7%; /* Trans Date */
        }

        #transactions-table thead th:nth-child(5),
        #transactions-table tbody td:nth-child(5) {
            width: 4%; /* Pump */
        }

        #transactions-table thead th:nth-child(6),
        #transactions-table tbody td:nth-child(6) {
            width: 4%; /* Nozzle */
        }

        #transactions-table thead th:nth-child(7),
        #transactions-table tbody td:nth-child(7) {
            width: 6%; /* Product */
        }

        #transactions-table thead th:nth-child(8),
        #transactions-table tbody td:nth-child(8) {
            width: 5%; /* Unit Price */
        }

        #transactions-table thead th:nth-child(9),
        #transactions-table tbody td:nth-child(9) {
            width: 5%; /* Volume */
        }

        #transactions-table thead th:nth-child(10),
        #transactions-table tbody td:nth-child(10) {
            width: 6%; /* Amount */
        }

        #transactions-table thead th:nth-child(11),
        #transactions-table tbody td:nth-child(11) {
            width: 6%; /* Start Totalizer */
        }

        #transactions-table thead th:nth-child(12),
        #transactions-table tbody td:nth-child(12) {
            width: 6%; /* End Totalizer */
        }

        #transactions-table thead th:nth-child(13),
        #transactions-table tbody td:nth-child(13) {
            width: 5%; /* Payment Mode */
        }

        #transactions-table thead th:nth-child(14),
        #transactions-table tbody td:nth-child(14) {
            width: 5%; /* Attendant */
        }

        #transactions-table thead th:nth-child(15),
        #transactions-table tbody td:nth-child(15) {
            width: 7%; /* Start Time */
        }

        #transactions-table thead th:nth-child(16),
        #transactions-table tbody td:nth-child(16) {
            width: 7%; /* End Time */
        }

        #transactions-table thead th:nth-child(17),
        #transactions-table tbody td:nth-child(17) {
            width: 6%; /* Mobile No */
        }

        #transactions-table thead th:nth-child(18),
        #transactions-table tbody td:nth-child(18) {
            width: 5%; /* Vehicle No */
        }

        #transactions-table thead th:nth-child(19),
        #transactions-table tbody td:nth-child(19) {
            width: 7%; /* HOS Received Date/Time */
        }

        #transactions-table tbody tr {
            background-color: white !important;
            border: none !important;
        }

        #transactions-table tbody tr:hover {
            background-color: #f9f9f9 !important;
        }

        #transactions-table tbody tr:last-child td {
            border-bottom: none !important;
        }

        /* Blue links for clickable items */
        .transaction-link {
            color: #011332 !important;
            text-decoration: none;
            font-weight: 500;
            cursor: pointer;
        }

        .transaction-link:hover {
            color: #0056b3 !important;
            text-decoration: underline;
        }

        /* Secondary text (Ref, Vehicle ID, username) */
        .secondary-text {
            font-size: 12px;
            color: #999 !important;
            margin-top: 3px;
            display: block;
            line-height: 1.4;
        }

        /* Right-aligned numeric columns */
        #transactions-table tbody td.text-right {
            text-align: right !important;
        }

        /* Date & Time sort indicator */
        .sort-indicator {
            display: inline-block;
            margin-left: 5px;
            color: #999;
            font-size: 12px;
        }

        /* Override DataTable default styling */
        #transactions-table_wrapper .dataTables_length,
        #transactions-table_wrapper .dataTables_filter,
        #transactions-table_wrapper .dataTables_info,
        #transactions-table_wrapper .dataTables_paginate {
            padding: 10px 15px;
            color: #555;
        }

        #transactions-table_wrapper .dataTables_processing {
            background-color: rgba(255, 255, 255, 0.9);
            border: none;
            border-radius: 4px;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
        }

        /* Remove borders from DataTable wrapper elements */
        #transactions-table_wrapper table.dataTable {
            border: none !important;
            border-collapse: collapse !important;
        }

        #transactions-table_wrapper table.dataTable thead th,
        #transactions-table_wrapper table.dataTable tbody td {
            border: none !important;
        }

        #transactions-table_wrapper table.dataTable tbody td {
            border-bottom: 1px solid #e0e0e0 !important;
        }
    </style>
@endpush

<!-- Transactions Table Card -->
<div class="card custom-card">
    <div class="card-header custom-card-header">
        <h6 class="mb-0" style="color: #D7D7D7;"><i class="fas fa-table"></i> Transactions Data</h6>
    </div>
    <div class="card-body" style="padding: 0;">
        <div class="table-responsive">
            <table id="transactions-table" class="table">
                <thead>
                    <tr>
                        <th>Site ID</th>
                        <th>Site Name</th>
                        <th>Trans ID</th>
                        <th>Trans Date <span class="sort-indicator"><i class="fas fa-sort"></i></span></th>
                        <th>Pump</th>
                        <th>Nozzle</th>
                        <th>Product</th>
                        <th class="text-right">Unit Price</th>
                        <th class="text-right">Volume</th>
                        <th class="text-right">Amount</th>
                        <th class="text-right">Start Totalizer</th>
                        <th class="text-right">End Totalizer</th>
                        <th>Payment Mode</th>
                        <th>Attendant</th>
                        <th>Start Time</th>
                        <th>End Time</th>
                        <th>Mobile No</th>
                        <th>Vehicle No</th>
                        <th>HOS Received Date/Time</th>
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
                'responsive': false,
                'lengthChange': true,
                'autoWidth': false,
                'pageLength': 10,
                'dom': '<"row"<"col-sm-6"l><"col-sm-6"f>>rt<"row"<"col-sm-6"i><"col-sm-6"p>>',
                'order': [
                    [3, 'desc']
                ],
                'bInfo': true,
                'bFilter': true,
                'bLengthChange': true,
                'paging': true,
                'orderCellsTop': false,
                'ajax': {
                    'url': '{{ route('hos-reports.transactions.data') }}',
                    'type': 'GET',
                    'error': function(xhr, error, thrown) {
                        console.error('AJAX Error:', error);
                        console.error('Response:', xhr.responseText);
                        alert('Error loading data. Please check the console for details.');
                    },
                    'data': function(d) {
                        d.from_date = $('#transaction_from_date').val();
                        d.to_date = $('#transaction_to_date').val();
                        d.from_time = $('#transaction_from_time').val();
                        d.to_time = $('#transaction_to_time').val();
                        d.station_id = $('#transaction_station_id').val();
                        d.pump_id = $('#transaction_pump_id').val();
                        d.mode_of_payment = $('#transaction_mop').val();
                        d.product_id = $('#transaction_product_id').val();
                    }
                },
                'columns': [{
                        data: 'site_id',
                        name: 'site_id',
                        orderable: true,
                        render: function(data) {
                            return data || '';
                        },
                        className: 'text-left'
                    },
                    {
                        data: 'site_name',
                        name: 'site_name',
                        orderable: true,
                        render: function(data) {
                            return data || '';
                        },
                        className: 'text-left'
                    },
                    {
                        data: 'transaction_id',
                        name: 'transaction_id',
                        orderable: true,
                        className: 'text-left'
                    },
                    {
                        data: 'trans_date',
                        name: 'trans_date',
                        orderable: true,
                        render: function(data, type) {
                            if (type !== 'display' || !data) return data || '';
                            try {
                                var date = new Date(data.replace(' ', 'T'));
                                if (isNaN(date.getTime())) return data;
                                var month = String(date.getMonth() + 1).padStart(2, '0');
                                var day = String(date.getDate()).padStart(2, '0');
                                var year = date.getFullYear();
                                var hours = String(date.getHours()).padStart(2, '0');
                                var minutes = String(date.getMinutes()).padStart(2, '0');
                                return month + '/' + day + '/' + year + ' ' + hours + ':' + minutes;
                            } catch (e) {
                                return data;
                            }
                        },
                        className: 'text-left'
                    },
                    {
                        data: 'pump',
                        name: 'pump',
                        orderable: true,
                        render: function(data) {
                            return data || '';
                        },
                        className: 'text-left'
                    },
                    {
                        data: 'nozzle',
                        name: 'nozzle',
                        orderable: true,
                        render: function(data) {
                            return data || '';
                        },
                        className: 'text-left'
                    },
                    {
                        data: 'product',
                        name: 'product',
                        orderable: true,
                        render: function(data) {
                            return data || '';
                        },
                        className: 'text-left'
                    },
                    {
                        data: 'unit_price',
                        name: 'unit_price',
                        orderable: true,
                        render: function(data) {
                            return data ? parseFloat(data).toFixed(2) : '0.00';
                        },
                        className: 'text-right'
                    },
                    {
                        data: 'volume',
                        name: 'volume',
                        orderable: true,
                        render: function(data) {
                            return data ? parseFloat(data).toFixed(2) : '0.00';
                        },
                        className: 'text-right'
                    },
                    {
                        data: 'amount',
                        name: 'amount',
                        orderable: true,
                        render: function(data) {
                            return data ? 'SAR ' + parseFloat(data).toFixed(2) : 'SAR 0.00';
                        },
                        className: 'text-right'
                    },
                    {
                        data: 'start_totalizer',
                        name: 'start_totalizer',
                        orderable: true,
                        render: function(data) {
                            return data ? parseFloat(data).toFixed(2) : '0.00';
                        },
                        className: 'text-right'
                    },
                    {
                        data: 'end_totalizer',
                        name: 'end_totalizer',
                        orderable: true,
                        render: function(data) {
                            return data ? parseFloat(data).toFixed(2) : '0.00';
                        },
                        className: 'text-right'
                    },
                    {
                        data: 'payment_mode',
                        name: 'payment_mode',
                        orderable: true,
                        className: 'text-left'
                    },
                    {
                        data: 'attendant',
                        name: 'attendant',
                        orderable: true,
                        render: function(data) {
                            return data || '';
                        },
                        className: 'text-left'
                    },
                    {
                        data: 'start_time',
                        name: 'start_time',
                        orderable: true,
                        render: function(data, type) {
                            if (type !== 'display' || !data) return data || '';
                            try {
                                var date = new Date(data.replace(' ', 'T'));
                                if (isNaN(date.getTime())) return data;
                                var month = String(date.getMonth() + 1).padStart(2, '0');
                                var day = String(date.getDate()).padStart(2, '0');
                                var year = date.getFullYear();
                                var hours = String(date.getHours()).padStart(2, '0');
                                var minutes = String(date.getMinutes()).padStart(2, '0');
                                return month + '/' + day + '/' + year + ' ' + hours + ':' + minutes;
                            } catch (e) {
                                return data;
                            }
                        },
                        className: 'text-left'
                    },
                    {
                        data: 'end_time',
                        name: 'end_time',
                        orderable: true,
                        render: function(data, type) {
                            if (type !== 'display' || !data) return data || '';
                            try {
                                var date = new Date(data.replace(' ', 'T'));
                                if (isNaN(date.getTime())) return data;
                                var month = String(date.getMonth() + 1).padStart(2, '0');
                                var day = String(date.getDate()).padStart(2, '0');
                                var year = date.getFullYear();
                                var hours = String(date.getHours()).padStart(2, '0');
                                var minutes = String(date.getMinutes()).padStart(2, '0');
                                return month + '/' + day + '/' + year + ' ' + hours + ':' + minutes;
                            } catch (e) {
                                return data;
                            }
                        },
                        className: 'text-left'
                    },
                    {
                        data: 'mobile_no',
                        name: 'mobile_no',
                        orderable: false,
                        render: function(data) {
                            return data || '';
                        },
                        className: 'text-left'
                    },
                    {
                        data: 'vehicle_no',
                        name: 'vehicle_no',
                        orderable: false,
                        render: function(data) {
                            return data || '';
                        },
                        className: 'text-left'
                    },
                    {
                        data: 'hos_received_time',
                        name: 'hos_received_time',
                        orderable: true,
                        render: function(data, type) {
                            if (type !== 'display' || !data) return data || '';
                            try {
                                var date = new Date(data.replace(' ', 'T'));
                                if (isNaN(date.getTime())) return data;
                                var month = String(date.getMonth() + 1).padStart(2, '0');
                                var day = String(date.getDate()).padStart(2, '0');
                                var year = date.getFullYear();
                                var hours = String(date.getHours()).padStart(2, '0');
                                var minutes = String(date.getMinutes()).padStart(2, '0');
                                return month + '/' + day + '/' + year + ' ' + hours + ':' + minutes;
                            } catch (e) {
                                return data;
                            }
                        },
                        className: 'text-left'
                    }
                ],
                'language': {
                    'processing': '<i class="fas fa-spinner fa-spin"></i> Loading...',
                    'emptyTable': 'No transactions found',
                    'zeroRecords': 'No matching transactions found'
                },
                destroy: true,
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
                $('#transaction_product_id').val('');
                transactionsTable.draw();
            });

            // Allow Enter key to trigger filter
            $('#transactions-filter-form input, #transactions-filter-form select').on('keypress', function(e) {
                if (e.which === 13) {
                    e.preventDefault();
                    transactionsTable.draw();
                }
            });

            // Export to PDF
            $('#transaction-export-pdf-btn').on('click', function() {
                const $btn = $(this);
                const originalHtml = $btn.html();

                const filters = {
                    from_date: $('#transaction_from_date').val(),
                    to_date: $('#transaction_to_date').val(),
                    from_time: $('#transaction_from_time').val(),
                    to_time: $('#transaction_to_time').val(),
                    station_id: $('#transaction_station_id').val(),
                    pump_id: $('#transaction_pump_id').val(),
                    mode_of_payment: $('#transaction_mop').val(),
                    product_id: $('#transaction_product_id').val(),
                    tab: 'transactions'
                };

                // Start notification polling immediately
                if (typeof window.startNotificationPolling === 'function') {
                    window.startNotificationPolling();
                }

                // Disable button and call export via AJAX to avoid page refresh
                $btn.prop('disabled', true).html('<i class="fas fa-spinner fa-spin"></i> Exporting...');

                // Fallback to re-enable button if request hangs for any reason
                const resetButton = function() {
                    $btn.prop('disabled', false).html(originalHtml);
                };

                $.ajax({
                    url: '{{ route('hos-reports.export.pdf') }}',
                    method: 'GET',
                    data: filters,
                    dataType: 'json',
                    cache: false,
                    headers: {
                        'X-Requested-With': 'XMLHttpRequest',
                        'Accept': 'application/json'
                    },
                    success: function(response) {
                        if (response && response.success) {
                            alert('Export started. You will get a notification when it is ready.');
                        } else {
                            alert(response && response.message ? response.message : 'Export could not be started.');
                        }
                    },
                    error: function(xhr) {
                        console.error('Export error', xhr);
                        alert('Error starting export. Please try again.');
                    },
                    complete: resetButton
                });

                // Safety timeout to ensure button is re-enabled even if AJAX never completes
                setTimeout(resetButton, 15000);
            });

            // Load stations for dropdown
            $.ajax({
                url: '{{ route('hos-reports.stations') }}',
                method: 'GET',
                success: function(response) {
                    if (response.stations) {
                        var $select = $('#transaction_station_id');
                        var firstOption = $select.find('option').first();
                        var optionsHtml = firstOption.length ? firstOption.prop('outerHTML') : '<option value=\"\">All Stations</option>';

                        response.stations.forEach(function(station) {
                            optionsHtml += '<option value=\"' + station.id + '\">' + station.site_name + '</option>';
                        });

                        $select.html(optionsHtml);
                    }
                }
            });

            // Load fuel grades/products for dropdown
            $.ajax({
                url: '{{ route('hos-reports.fuel-grades') }}',
                method: 'GET',
                success: function(response) {
                    if (response.fuel_grades) {
                        var $select = $('#transaction_product_id');
                        var firstOption = $select.find('option').first();
                        var optionsHtml = firstOption.length ? firstOption.prop('outerHTML') : '<option value="">All Products</option>';

                        response.fuel_grades.forEach(function(grade) {
                            optionsHtml += '<option value="' + grade.id + '">' + grade.name + '</option>';
                        });

                        $select.html(optionsHtml);
                    }
                }
            });

            // Auto-filter on dropdown change
            $('#transaction_station_id, #transaction_mop, #transaction_product_id').on('change', function() {
                transactionsTable.draw();
            });
        });
    </script>
@endpush
