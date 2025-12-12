<!-- Filters Card -->
<div class="card custom-card mb-3">
    <div class="card-header custom-card-header">
        <h6 class="mb-0" style="color: #D7D7D7;"><i class="fas fa-filter"></i> Filters</h6>
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
                        <label for="inventory_fuel_grade_id">Product</label>
                        <select class="form-control" id="inventory_fuel_grade_id" name="fuel_grade_id">
                            <option value="">All Products</option>
                        </select>
                    </div>
                </div>
            </div>
            <div class="row">
                <div class="col-md-2">
                    <div class="form-group">
                        <label for="inventory_tank">Tank</label>
                        <select class="form-control" id="inventory_tank" name="tank">
                            <option value="">All Tanks</option>
                        </select>
                    </div>
                </div>
            </div>
            <div class="row">
                <div class="col-md-12 d-flex justify-content-end" style="gap: 10px;">
                    <button type="button" id="inventory-filter-btn" class="btn btn-dark">
                        <i class="fas fa-filter"></i> Search Filters
                    </button>
                    <button type="button" id="inventory-reset-btn" class="btn btn-dark">
                        <i class="fas fa-redo"></i> Reset
                    </button>
                    <button type="button" id="inventory-export-excel-btn" class="btn btn-dark">
                        <i class="fas fa-file-excel"></i> Export Excel
                    </button>
                    <button type="button" id="inventory-export-pdf-btn" class="btn btn-dark">
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
        <h6 class="mb-0" style="color: #D7D7D7;"><i class="fas fa-table"></i> Tank Inventory Data</h4>
    </div>
    <div class="card-body" style="padding: 0;">
        <div class="table-responsive">
            <table id="tank-inventory-table" class="table">
                <thead>
                    <tr>
                        <th>Date & Time <span class="sort-indicator"><i class="fas fa-sort"></i></span></th>
                        <th>Station</th>
                        <th>Tank</th>
                        <th>Product</th>
                        <th class="text-right">Volume (L)</th>
                        <th class="text-right">Height (mm)</th>
                        <th class="text-right">Water (mm)</th>
                        <th class="text-right">Temperature (°C)</th>
                        <th class="text-right">Ullage (L)</th>
                    </tr>
                </thead>
                <tbody>
                    <!-- DataTable will populate this -->
                </tbody>
            </table>
        </div>
    </div>
</div>

@push('css')
<style>
    /* Tank Inventory Table Styles - Shared with transactions */
    #tank-inventory-table {
        width: 100%;
        border-collapse: collapse;
        margin: 0;
        background-color: white;
    }

    #tank-inventory-table thead {
        background-color: #D7D7D7;
    }

    #tank-inventory-table thead th {
        background-color: #D7D7D7 !important;
        color: #333 !important;
        font-weight: bold !important;
        padding: 12px 15px !important;
        text-align: left !important;
        border: none !important;
        font-size: 14px;
    }

    #tank-inventory-table tbody tr {
        background-color: white;
        border-bottom: 1px solid #e0e0e0;
    }

    #tank-inventory-table tbody tr:hover {
        background-color: #f5f5f5;
    }

    #tank-inventory-table tbody td {
        padding: 12px 15px !important;
        border: none !important;
        border-bottom: 1px solid #e0e0e0 !important;
        color: #555;
        font-size: 14px;
    }

    /* Blue links for clickable items */
    .tank-inventory-link {
        color: #011332 !important;
        text-decoration: none;
        font-weight: 500;
    }

    .tank-inventory-link:hover {
        text-decoration: underline;
        color: #0099ff !important;
    }

    /* Secondary text (Ref, etc.) */
    .secondary-text {
        font-size: 12px;
        color: #999 !important;
        margin-top: 3px;
        display: block;
        line-height: 1.4;
    }

    /* Right-aligned numeric columns */
    #tank-inventory-table tbody td.text-right {
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
    #tank-inventory-table_wrapper .dataTables_length,
    #tank-inventory-table_wrapper .dataTables_filter,
    #tank-inventory-table_wrapper .dataTables_info,
    #tank-inventory-table_wrapper .dataTables_paginate {
        padding: 10px 15px;
        color: #555;
    }

    #tank-inventory-table_wrapper .dataTables_processing {
        background-color: rgba(255, 255, 255, 0.9);
        border: none;
        border-radius: 4px;
        box-shadow: 0 2px 4px rgba(0,0,0,0.1);
    }

    /* Remove borders from DataTable wrapper elements */
    #tank-inventory-table_wrapper table.dataTable {
        border: none !important;
        border-collapse: collapse !important;
    }

    #tank-inventory-table_wrapper table.dataTable thead th,
    #tank-inventory-table_wrapper table.dataTable tbody td {
        border: none !important;
    }

    #tank-inventory-table_wrapper table.dataTable tbody td {
        border-bottom: 1px solid #e0e0e0 !important;
    }
</style>
@endpush

@push('js')
    <script>
        $(document).ready(function() {
            var inventoryTable = $('#tank-inventory-table').DataTable({
                'processing': true,
                'serverSide': true,
                'responsive': false,
                'lengthChange': true,
                'autoWidth': false,
                'pageLength': 10,
                'dom': '<"row"<"col-sm-6"l><"col-sm-6"f>>rt<"row"<"col-sm-6"i><"col-sm-6"p>>',
                'order': [
                    [0, 'desc']
                ],
                'bInfo': true,
                'bFilter': true,
                'bLengthChange': true,
                'paging': true,
                'orderCellsTop': false,
                'ajax': {
                    'url': '{{ route('hos-reports.tank-inventory') }}',
                    'type': 'GET',
                    'error': function(xhr, error, thrown) {
                        console.error('AJAX Error:', error);
                        console.error('Response:', xhr.responseText);
                        alert('Error loading data. Please check the console for details.');
                    },
                    'data': function(d) {
                        d.from_date = $('#inventory_from_date').val();
                        d.to_date = $('#inventory_to_date').val();
                        d.from_time = $('#inventory_from_time').val();
                        d.to_time = $('#inventory_to_time').val();
                        d.station_id = $('#inventory_station_id').val();
                        d.fuel_grade_id = $('#inventory_fuel_grade_id').val();
                        d.tank = $('#inventory_tank').val();
                    }
                },
                'columns': [{
                        data: 'date_time',
                        name: 'date_time',
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
                                var seconds = String(date.getSeconds()).padStart(2, '0');
                                return month + '/' + day + '/' + year + ' ' + hours + ':' + minutes + ':' + seconds;
                            } catch (e) {
                                return data;
                            }
                        },
                        className: 'text-left'
                    },
                    {
                        data: 'site',
                        name: 'site',
                        orderable: true,
                        render: function(data, type) {
                            return data || '';
                        },
                        className: 'text-left'
                    },
                    {
                        data: 'tank',
                        name: 'tank',
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
                        data: 'volume',
                        name: 'volume',
                        orderable: true,
                        render: function(data, type) {
                            if (type !== 'display' || data === null || data === undefined) return '';
                            return parseFloat(data).toLocaleString('en-US', {minimumFractionDigits: 0, maximumFractionDigits: 0});
                        },
                        className: 'text-right'
                    },
                    {
                        data: 'height',
                        name: 'height',
                        orderable: true,
                        render: function(data, type) {
                            if (type !== 'display' || data === null || data === undefined) return '';
                            return parseFloat(data).toLocaleString('en-US', {minimumFractionDigits: 0, maximumFractionDigits: 0});
                        },
                        className: 'text-right'
                    },
                    {
                        data: 'water',
                        name: 'water',
                        orderable: true,
                        render: function(data, type) {
                            if (type !== 'display' || data === null || data === undefined) return '';
                            return parseFloat(data).toLocaleString('en-US', {minimumFractionDigits: 0, maximumFractionDigits: 0});
                        },
                        className: 'text-right'
                    },
                    {
                        data: 'temperature',
                        name: 'temperature',
                        orderable: true,
                        render: function(data, type) {
                            if (type !== 'display' || data === null || data === undefined) return '';
                            return parseFloat(data).toLocaleString('en-US', {minimumFractionDigits: 0, maximumFractionDigits: 0});
                        },
                        className: 'text-right'
                    },
                    {
                        data: 'ullage',
                        name: 'ullage',
                        orderable: true,
                        render: function(data, type) {
                            if (type !== 'display' || data === null || data === undefined) return '';
                            return parseFloat(data).toLocaleString('en-US', {minimumFractionDigits: 0, maximumFractionDigits: 0});
                        },
                        className: 'text-right'
                    }
                ],
                'language': {
                    'processing': '<i class="fas fa-spinner fa-spin"></i> Loading...',
                    'emptyTable': 'No tank inventory records found',
                    'zeroRecords': 'No matching tank inventory records found'
                },
                destroy: true,
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
                $('#inventory_tank').val('');
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
                    fuel_grade_id: $('#inventory_fuel_grade_id').val(),
                    tank: $('#inventory_tank').val()
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
                    fuel_grade_id: $('#inventory_fuel_grade_id').val(),
                    tank: $('#inventory_tank').val()
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
                        var $select = $('#inventory_station_id');
                        var firstOption = $select.find('option').first();
                        var optionsHtml = firstOption.length ? firstOption.prop('outerHTML') : '<option value=\"\">All Stations</option>';

                        response.stations.forEach(function(station) {
                            optionsHtml += '<option value=\"' + station.id + '\">' + station.site_name + '</option>';
                        });

                        $select.html(optionsHtml);
                    }
                }
            });

            // Load fuel grades for dropdown
            $.ajax({
                url: '{{ route('hos-reports.fuel-grades') }}',
                method: 'GET',
                success: function(response) {
                    if (response.fuel_grades) {
                        var $select = $('#inventory_fuel_grade_id');
                        var firstOption = $select.find('option').first();
                        var optionsHtml = firstOption.length ? firstOption.prop('outerHTML') : '<option value="">All Products</option>';

                        response.fuel_grades.forEach(function(grade) {
                            optionsHtml += '<option value="' + grade.id + '">' + grade.name + '</option>';
                        });

                        $select.html(optionsHtml);
                    }
                }
            });

            // Load tanks for dropdown
            function loadTanks() {
                const stationId = $('#inventory_station_id').val();
                $.ajax({
                    url: '{{ route('hos-reports.tanks') }}',
                    method: 'GET',
                    data: { station_id: stationId },
                    success: function(response) {
                        $('#inventory_tank').empty().append('<option value="">All Tanks</option>');
                        if (response.tanks) {
                            response.tanks.forEach(function(tank) {
                                $('#inventory_tank').append(
                                    $('<option></option>').val(tank.tank).text(tank.tank_formatted)
                                );
                            });
                        }
                    }
                });
            }

            // Initial load
            loadTanks();

            // Reload tanks when station changes
            $('#inventory_station_id').on('change', function() {
                loadTanks();
                inventoryTable.draw();
            });

            // Auto-filter on dropdown change
            $('#inventory_fuel_grade_id, #inventory_tank').on('change', function() {
                inventoryTable.draw();
            });
        });
    </script>
@endpush
