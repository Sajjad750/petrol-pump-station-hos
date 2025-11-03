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
                    <button type="button" id="transaction-export-excel-btn" class="btn btn-dark">
                        <i class="fas fa-file-excel"></i> Export Excel
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
        padding: 12px 15px !important;
        text-align: left !important;
        border: none !important;
        border-top: none !important;
        border-bottom: none !important;
        border-left: none !important;
        border-right: none !important;
        font-size: 14px;
    }
    
    #transactions-table thead th.text-right {
        text-align: right !important;
    }
    
    #transactions-table tbody {
        background-color: white;
    }
    
    #transactions-table tbody td {
        padding: 12px 15px !important;
        border: none !important;
        border-top: none !important;
        border-left: none !important;
        border-right: none !important;
        border-bottom: 1px solid #e0e0e0 !important;
        color: #555 !important;
        font-size: 14px;
        vertical-align: top;
        background-color: white !important;
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
        box-shadow: 0 2px 4px rgba(0,0,0,0.1);
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
        <h6 class="mb-0" style="color: #D7D7D7;"><i class="fas fa-table"></i> Transactions Data</h4>
    </div>
    <div class="card-body" style="padding: 0;">
        <div class="table-responsive">
            <table id="transactions-table" class="table">
                <thead>
                    <tr>
                        <th>Site</th>
                        <th>Transaction ID</th>
                        <th>Date & Time <span class="sort-indicator"><i class="fas fa-sort"></i></span></th>
                        <th>Pump</th>
                        <th>Nozzle</th>
                        <th>Product</th>
                        <th class="text-right">Unit Price</th>
                        <th class="text-right">Litres</th>
                        <th class="text-right">Amount</th>
                        <th>MOP</th>
                        <th>Mobile & Vehicle ID</th>
                        <th>Attendant</th>
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
                    [2, 'desc']
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
                        data: 'site',
                        name: 'site',
                        orderable: true,
                        render: function(data, type, row) {
                            if (type === 'display') {
                                var siteHtml = '<a href="#" class="transaction-link">' + (data || '') + '</a>';
                                if (row.site_ref) {
                                    // Format reference (if numeric, pad to 3 digits, otherwise show as-is)
                                    var ref = row.site_ref;
                                    if (/^\d+$/.test(ref)) {
                                        ref = String(ref).padStart(3, '0');
                                    }
                                    siteHtml += '<span class="secondary-text">Ref: ' + ref + '</span>';
                                }
                                return siteHtml;
                            }
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
                        data: 'date_time',
                        name: 'date_time',
                        orderable: true,
                        render: function(data, type) {
                            if (type !== 'display' || !data) return data || '';
                            // Format date: convert from YYYY-MM-DD HH:mm:ss to MM/DD/YYYY HH:mm
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
                            if (!data) return '';
                            return '<a href="#" class="transaction-link">P-' + String(data).padStart(2, '0') + '</a>';
                        },
                        className: 'text-left'
                    },
                    {
                        data: 'nozzle',
                        name: 'nozzle',
                        orderable: true,
                        render: function(data) {
                            if (!data) return '';
                            return '<a href="#" class="transaction-link">N-' + String(data).padStart(2, '0') + '</a>';
                        },
                        className: 'text-left'
                    },
                    {
                        data: 'product',
                        name: 'product',
                        orderable: true,
                        render: function(data) {
                            if (!data) return '';
                            return '<a href="#" class="transaction-link">' + data + '</a>';
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
                        data: 'litres',
                        name: 'litres',
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
                            return data ? '$' + parseFloat(data).toFixed(2) : '$0.00';
                        },
                        className: 'text-right'
                    },
                    {
                        data: 'mop',
                        name: 'mop',
                        orderable: true,
                        className: 'text-left'
                    },
                    {
                        data: 'mobile_vehicle_id',
                        name: 'mobile_vehicle_id',
                        orderable: false,
                        render: function(data, type, row) {
                            if (type === 'display') {
                                var html = data || '';
                                if (row.vehicle_id) {
                                    html += '<span class="secondary-text">Vehicle ID: ' + row.vehicle_id + '</span>';
                                }
                                return html;
                            }
                            return data || '';
                        },
                        className: 'text-left'
                    },
                    {
                        data: 'atten',
                        name: 'atten',
                        orderable: true,
                        render: function(data, type, row) {
                            if (type === 'display') {
                                var html = data || '';
                                if (row.atten_username) {
                                    html += '<span class="secondary-text">@' + row.atten_username + '</span>';
                                }
                                return html;
                            }
                            return data || '';
                        },
                        className: 'text-left'
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

            // Export to Excel
            $('#transaction-export-excel-btn').on('click', function() {
                const filters = {
                    from_date: $('#transaction_from_date').val(),
                    to_date: $('#transaction_to_date').val(),
                    from_time: $('#transaction_from_time').val(),
                    to_time: $('#transaction_to_time').val(),
                    station_id: $('#transaction_station_id').val(),
                    pump_id: $('#transaction_pump_id').val(),
                    mode_of_payment: $('#transaction_mop').val(),
                    product_id: $('#transaction_product_id').val()
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
                    mode_of_payment: $('#transaction_mop').val(),
                    product_id: $('#transaction_product_id').val()
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

            // Load fuel grades/products for dropdown
            $.ajax({
                url: '{{ route('hos-reports.fuel-grades') }}',
                method: 'GET',
                success: function(response) {
                    if (response.fuel_grades) {
                        response.fuel_grades.forEach(function(grade) {
                            $('#transaction_product_id').append(
                                $('<option></option>').val(grade.id).text(grade.name)
                            );
                        });
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
