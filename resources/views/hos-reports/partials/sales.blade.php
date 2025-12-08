<!-- Filters Card -->
<div class="card custom-card mb-3">
    <div class="card-header custom-card-header">
        <h6 class="mb-0" style="color: #D7D7D7;"><i class="fas fa-filter"></i> Filters</h6>
    </div>
    <div class="card-body">
        <form id="sales-filter-form">
            <div class="row">
                <div class="col-md-3">
                    <div class="form-group">
                        <label for="sales_from_date">From Date</label>
                        <input type="date" class="form-control" id="sales_from_date" name="from_date">
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="form-group">
                        <label for="sales_to_date">To Date</label>
                        <input type="date" class="form-control" id="sales_to_date" name="to_date">
                    </div>
                </div>
                <div class="col-md-2">
                    <div class="form-group">
                        <label for="sales_from_time">From Time</label>
                        <input type="time" class="form-control" id="sales_from_time" name="from_time">
                    </div>
                </div>
                <div class="col-md-2">
                    <div class="form-group">
                        <label for="sales_to_time">To Time</label>
                        <input type="time" class="form-control" id="sales_to_time" name="to_time">
                    </div>
                </div>
                <div class="col-md-2">
                    <div class="form-group">
                        <label for="sales_station_id">Station</label>
                        <select class="form-control" id="sales_station_id" name="station_id">
                            <option value="">All Stations</option>
                        </select>
                    </div>
                </div>
            </div>
            <div class="row">
                <div class="col-md-12 d-flex justify-content-end" style="gap: 10px;">
                    <button type="button" id="sales-filter-btn" class="btn btn-dark">
                        <i class="fas fa-filter"></i> Search Filters
                    </button>
                    <button type="button" id="sales-reset-btn" class="btn btn-dark">
                        <i class="fas fa-redo"></i> Reset
                    </button>
                    <button type="button" id="sales-export-excel-btn" class="btn btn-dark">
                        <i class="fas fa-file-excel"></i> Export Excel
                    </button>
                    <button type="button" id="sales-export-pdf-btn" class="btn btn-dark">
                        <i class="fas fa-file-pdf"></i> Export PDF
                    </button>
                </div>
            </div>
        </form>
    </div>
</div>

@push('css')
    <style>
        #sales-table {
            width: 100% !important;
            table-layout: auto;
        }

        #sales-table thead th,
        #sales-table tbody td {
            padding: 8px 10px !important;
            white-space: nowrap;
            font-size: 13px;
        }

        /* Site ID */
        #sales-table thead th:nth-child(1),
        #sales-table tbody td:nth-child(1) {
            width: 7%;
        }

        /* Site Name */
        #sales-table thead th:nth-child(2),
        #sales-table tbody td:nth-child(2) {
            width: 10%;
        }

        /* Trans ID */
        #sales-table thead th:nth-child(3),
        #sales-table tbody td:nth-child(3) {
            width: 8%;
        }

        /* Trans Date */
        #sales-table thead th:nth-child(4),
        #sales-table tbody td:nth-child(4) {
            width: 10%;
        }

        /* Pump */
        #sales-table thead th:nth-child(5),
        #sales-table tbody td:nth-child(5) {
            width: 6%;
        }

        /* Nozzle */
        #sales-table thead th:nth-child(6),
        #sales-table tbody td:nth-child(6) {
            width: 6%;
        }

        /* Product */
        #sales-table thead th:nth-child(7),
        #sales-table tbody td:nth-child(7) {
            width: 9%;
        }

        /* Unit Price */
        #sales-table thead th:nth-child(8),
        #sales-table tbody td:nth-child(8) {
            width: 7%;
        }

        /* Volume */
        #sales-table thead th:nth-child(9),
        #sales-table tbody td:nth-child(9) {
            width: 7%;
        }

        /* Amount */
        #sales-table thead th:nth-child(10),
        #sales-table tbody td:nth-child(10) {
            width: 8%;
        }

        /* Payment Mode */
        #sales-table thead th:nth-child(11),
        #sales-table tbody td:nth-child(11) {
            width: 8%;
        }

        /* HOS Received Date/Time */
        #sales-table thead th:nth-child(12),
        #sales-table tbody td:nth-child(12) {
            width: 10%;
        }

        /* Sort indicator */
        .sort-indicator {
            display: inline-block;
            margin-left: 5px;
            color: #999;
            font-size: 12px;
        }
    </style>
@endpush

<!-- Sales Table Card -->
<div class="card custom-card">
    <div class="card-header custom-card-header">
        <h6 class="mb-0" style="color: #D7D7D7;"><i class="fas fa-table"></i> Sales Data</h6>
    </div>
    <div class="card-body" style="padding: 0;">
        <div class="table-responsive">
            <table id="sales-table" class="table">
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
                        <th>Payment Mode</th>
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
            var salesTable = $('#sales-table').DataTable({
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
                    'url': '{{ route('hos-reports.sales') }}',
                    'type': 'GET',
                    'error': function(xhr, error, thrown) {
                        console.error('AJAX Error:', error);
                        console.error('Response:', xhr.responseText);
                        alert('Error loading data. Please check the console for details.');
                    },
                    'data': function(d) {
                        d.from_date = $('#sales_from_date').val();
                        d.to_date = $('#sales_to_date').val();
                        d.from_time = $('#sales_from_time').val();
                        d.to_time = $('#sales_to_time').val();
                        d.station_id = $('#sales_station_id').val();
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
                        render: function(data) {
                            return data || '';
                        },
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
                            if (!data) return '';
                            // Check if it's Diesel to show in orange
                            var isDiesel = data.toLowerCase().includes('diesel');
                            if (isDiesel) {
                                return '<span style="color: #ff6600;">' + data + '</span>';
                            }
                            return data;
                        },
                        className: 'text-left'
                    },
                    {
                        data: 'unit_price',
                        name: 'unit_price',
                        orderable: true,
                        render: function(data, type) {
                            if (type !== 'display' || data === null || data === undefined) return '';
                            return parseFloat(data).toFixed(2);
                        },
                        className: 'text-right'
                    },
                    {
                        data: 'volume',
                        name: 'volume',
                        orderable: true,
                        render: function(data, type) {
                            if (type !== 'display' || data === null || data === undefined) return '';
                            return parseFloat(data).toFixed(2) + ' L';
                        },
                        className: 'text-right'
                    },
                    {
                        data: 'amount',
                        name: 'amount',
                        orderable: true,
                        render: function(data, type) {
                            if (type !== 'display' || data === null || data === undefined) return '';
                            return 'SAR ' + parseFloat(data).toFixed(2);
                        },
                        className: 'text-right'
                    },
                    {
                        data: 'payment_mode',
                        name: 'payment_mode',
                        orderable: true,
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
                    'emptyTable': 'No sales records found',
                    'zeroRecords': 'No matching sales records found'
                },
                destroy: true,
            });

            // Apply filters button
            $('#sales-filter-btn').on('click', function() {
                salesTable.draw();
            });

            // Reset filters button
            $('#sales-reset-btn').on('click', function() {
                $('#sales_from_date').val('');
                $('#sales_to_date').val('');
                $('#sales_from_time').val('');
                $('#sales_to_time').val('');
                $('#sales_station_id').val('');
                salesTable.draw();
            });

            // Allow Enter key to trigger filter
            $('#sales-filter-form input, #sales-filter-form select').on('keypress', function(e) {
                if (e.which === 13) {
                    e.preventDefault();
                    salesTable.draw();
                }
            });

            // Export to Excel
            $('#sales-export-excel-btn').on('click', function() {
                const filters = {
                    from_date: $('#sales_from_date').val(),
                    to_date: $('#sales_to_date').val(),
                    from_time: $('#sales_from_time').val(),
                    to_time: $('#sales_to_time').val(),
                    station_id: $('#sales_station_id').val()
                };
                const queryString = $.param(filters);
                window.location.href = '{{ route('hos-reports.sales.export.excel') }}?' + queryString;
            });

            // Export to PDF
            $('#sales-export-pdf-btn').on('click', function() {
                const filters = {
                    from_date: $('#sales_from_date').val(),
                    to_date: $('#sales_to_date').val(),
                    from_time: $('#sales_from_time').val(),
                    to_time: $('#sales_to_time').val(),
                    station_id: $('#sales_station_id').val()
                };
                const queryString = $.param(filters);
                window.location.href = '{{ route('hos-reports.sales.export.pdf') }}?' + queryString;
            });

            // Load stations for dropdown
            $.ajax({
                url: '{{ route('hos-reports.stations') }}',
                method: 'GET',
                success: function(response) {
                    if (response.stations) {
                        var $select = $('#sales_station_id');
                        var firstOption = $select.find('option').first();
                        var optionsHtml = firstOption.length ? firstOption.prop('outerHTML') : '<option value=\"\">All Stations</option>';

                        response.stations.forEach(function(station) {
                            optionsHtml += '<option value=\"' + station.id + '\">' + station.site_name + '</option>';
                        });

                        $select.html(optionsHtml);
                    }
                }
            });

            // Auto-filter on dropdown change
            $('#sales_station_id').on('change', function() {
                salesTable.draw();
            });
        });
    </script>
@endpush
