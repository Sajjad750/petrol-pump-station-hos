<!-- Filters Card -->
<div class="card custom-card mb-3">
    <div class="card-header custom-card-header">
        <h5 class="mb-0" style="color: #D7D7D7;"><i class="fas fa-filter"></i> Filters</h5>
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
                        <label for="deliveries_from_time">From Time</label>
                        <input type="time" class="form-control" id="deliveries_from_time" name="from_time">
                    </div>
                </div>
                <div class="col-md-2">
                    <div class="form-group">
                        <label for="deliveries_to_time">To Time</label>
                        <input type="time" class="form-control" id="deliveries_to_time" name="to_time">
                    </div>
                </div>
                <div class="col-md-2">
                    <div class="form-group">
                        <label for="deliveries_fuel_grade_id">Product</label>
                        <select class="form-control" id="deliveries_fuel_grade_id" name="fuel_grade_id">
                            <option value="">All Products</option>
                        </select>
                    </div>
                </div>
                <div class="col-md-2">
                    <div class="form-group">
                        <label for="deliveries_tank">Tank</label>
                        <select class="form-control" id="deliveries_tank" name="tank">
                            <option value="">All Tanks</option>
                        </select>
                    </div>
                </div>
            </div>
            <div class="row">
                <div class="col-md-3">
                    <div class="form-group">
                        <label for="deliveries_volume_min">Volume Min (L)</label>
                        <input type="number" step="0.01" class="form-control" id="deliveries_volume_min" name="volume_min" placeholder="Min Volume">
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="form-group">
                        <label for="deliveries_volume_max">Volume Max (L)</label>
                        <input type="number" step="0.01" class="form-control" id="deliveries_volume_max" name="volume_max" placeholder="Max Volume">
                    </div>
                </div>
            </div>
            <div class="row">
                <div class="col-md-12 d-flex justify-content-end" style="gap: 10px;">
                    <button type="button" id="deliveries-filter-btn" class="btn btn-dark">
                        <i class="fas fa-filter"></i> Search Filters
                    </button>
                    <button type="button" id="deliveries-reset-btn" class="btn btn-dark">
                        <i class="fas fa-redo"></i> Reset
                    </button>
                    <button type="button" id="deliveries-export-excel-btn" class="btn btn-dark">
                        <i class="fas fa-file-excel"></i> Export Excel
                    </button>
                    <button type="button" id="deliveries-export-pdf-btn" class="btn btn-dark">
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
        <h4 class="mb-0" style="color: #D7D7D7;"><i class="fas fa-table"></i> Tank Deliveries Data</h4>
    </div>
    <div class="card-body" style="padding: 0;">
        <div class="table-responsive">
            <table id="tank-deliveries-table" class="table">
                <thead>
                    <tr>
                        <th>Site</th>
                        <th>Date & Time <span class="sort-indicator"><i class="fas fa-sort"></i></span></th>
                        <th>Tank</th>
                        <th>Product</th>
                        <th class="text-right">Volume (L)</th>
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
                'responsive': false,
                'lengthChange': true,
                'autoWidth': false,
                'pageLength': 10,
                'dom': '<"row"<"col-sm-6"l><"col-sm-6"f>>rt<"row"<"col-sm-6"i><"col-sm-6"p>>',
                'order': [
                    [1, 'desc']
                ],
                'bInfo': true,
                'bFilter': true,
                'bLengthChange': true,
                'paging': true,
                'orderCellsTop': false,
                'ajax': {
                    'url': '{{ route('hos-reports.tank-deliveries') }}',
                    'type': 'GET',
                    'error': function(xhr, error, thrown) {
                        console.error('AJAX Error:', error);
                        console.error('Response:', xhr.responseText);
                        alert('Error loading data. Please check the console for details.');
                    },
                    'data': function(d) {
                        d.from_date = $('#deliveries_from_date').val();
                        d.to_date = $('#deliveries_to_date').val();
                        d.from_time = $('#deliveries_from_time').val();
                        d.to_time = $('#deliveries_to_time').val();
                        d.fuel_grade_id = $('#deliveries_fuel_grade_id').val();
                        d.tank = $('#deliveries_tank').val();
                        d.volume_min = $('#deliveries_volume_min').val();
                        d.volume_max = $('#deliveries_volume_max').val();
                    }
                },
                'columns': [{
                        data: 'site',
                        name: 'site',
                        orderable: true,
                        render: function(data, type, row) {
                            if (type === 'display') {
                                var siteHtml = '<a href="#" class="tank-delivery-link">' + (data || '') + '</a>';
                                if (row.site_ref) {
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
                        data: 'tank',
                        name: 'tank',
                        orderable: true,
                        render: function(data) {
                            return '<a href="#" class="tank-delivery-link">' + (data || '') + '</a>';
                        },
                        className: 'text-left'
                    },
                    {
                        data: 'product',
                        name: 'product',
                        orderable: true,
                        render: function(data) {
                            if (!data) return '';
                            // Check if it's Diesel to show in orange, otherwise blue
                            var isDiesel = data.toLowerCase().includes('diesel');
                            var colorClass = isDiesel ? 'style="color: #ff6600;"' : '';
                            return '<a href="#" class="tank-delivery-link" ' + colorClass + '>' + data + '</a>';
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
                $('#deliveries_from_time').val('');
                $('#deliveries_to_time').val('');
                $('#deliveries_fuel_grade_id').val('');
                $('#deliveries_tank').val('');
                $('#deliveries_volume_min').val('');
                $('#deliveries_volume_max').val('');
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
                    from_time: $('#deliveries_from_time').val(),
                    to_time: $('#deliveries_to_time').val(),
                    fuel_grade_id: $('#deliveries_fuel_grade_id').val(),
                    tank: $('#deliveries_tank').val(),
                    volume_min: $('#deliveries_volume_min').val(),
                    volume_max: $('#deliveries_volume_max').val()
                };
                const queryString = $.param(filters);
                window.location.href = '{{ route('hos-reports.tank-deliveries.export.excel') }}?' + queryString;
            });

            // Export to PDF
            $('#deliveries-export-pdf-btn').on('click', function() {
                const filters = {
                    from_date: $('#deliveries_from_date').val(),
                    to_date: $('#deliveries_to_date').val(),
                    from_time: $('#deliveries_from_time').val(),
                    to_time: $('#deliveries_to_time').val(),
                    fuel_grade_id: $('#deliveries_fuel_grade_id').val(),
                    tank: $('#deliveries_tank').val(),
                    volume_min: $('#deliveries_volume_min').val(),
                    volume_max: $('#deliveries_volume_max').val()
                };
                const queryString = $.param(filters);
                window.location.href = '{{ route('hos-reports.tank-deliveries.export.pdf') }}?' + queryString;
            });

            // Load fuel grades for dropdown
            $.ajax({
                url: '{{ route('hos-reports.fuel-grades') }}',
                method: 'GET',
                success: function(response) {
                    if (response.fuel_grades) {
                        response.fuel_grades.forEach(function(grade) {
                            $('#deliveries_fuel_grade_id').append(
                                $('<option></option>').val(grade.id).text(grade.name)
                            );
                        });
                    }
                }
            });

            // Load tanks for dropdown
            function loadTanksForDeliveries() {
                $.ajax({
                    url: '{{ route('hos-reports.tanks') }}',
                    method: 'GET',
                    success: function(response) {
                        $('#deliveries_tank').empty().append('<option value="">All Tanks</option>');
                        if (response.tanks) {
                            response.tanks.forEach(function(tank) {
                                $('#deliveries_tank').append(
                                    $('<option></option>').val(tank.tank).text(tank.tank_formatted)
                                );
                            });
                        }
                    }
                });
            }

            // Initial load
            loadTanksForDeliveries();

            // Auto-filter on dropdown change
            $('#deliveries_fuel_grade_id, #deliveries_tank').on('change', function() {
                deliveriesTable.draw();
            });

            // Auto-filter on volume inputs
            $('#deliveries_volume_min, #deliveries_volume_max').on('blur', function() {
                deliveriesTable.draw();
            });
        });
    </script>
@endpush
