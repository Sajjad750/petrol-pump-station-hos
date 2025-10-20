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
                                        <label for="shift_id">Shift ID</label>
                                        <input type="text" class="form-control" id="shift_id" name="shift_id" placeholder="Search Shift ID">
                                    </div>
                                </div>
                                <div class="col-md-2">
                                    <div class="form-group">
                                        <label for="mop">Payment Mode</label>
                                        <input type="text" class="form-control" id="mop" name="mop" placeholder="Search MOP">
                                    </div>
                                </div>
                                <div class="col-md-3">
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
                        <h4 class="mb-0">Payment Mode Wise Summaries</h4>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table id="payment-mode-wise-summaries-table" class="table-bordered table">
                                <thead class="custom-table-header">
                                    <tr>
                                        <th>ID</th>
                                        <th>Station</th>
                                        <th>Shift ID</th>
                                        <th>Shift Start Time</th>
                                        <th>Payment Mode</th>
                                        <th>Volume</th>
                                        <th>Amount</th>
                                        <th>Avg Price/L</th>
                                        <th>BOS Summary ID</th>
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
            var table = $('#payment-mode-wise-summaries-table').DataTable({
                'processing': true,
                'serverSide': true,
                'ajax': {
                    'url': '{{ route('payment_mode_wise_summaries') }}',
                    'data': function(d) {
                        d.from_date = $('#from_date').val();
                        d.to_date = $('#to_date').val();
                        d.from_time = $('#from_time').val();
                        d.to_time = $('#to_time').val();
                        d.shift_id = $('#shift_id').val();
                        d.mop = $('#mop').val();
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
                        data: 'shift_id',
                        defaultContent: '-'
                    },
                    {
                        data: 'shift_start_time',
                        defaultContent: '-'
                    },
                    {
                        data: 'mop',
                        defaultContent: '-'
                    },
                    {
                        data: 'volume',
                        defaultContent: '0.00'
                    },
                    {
                        data: 'amount',
                        defaultContent: '0.00'
                    },
                    {
                        data: 'avg_price',
                        defaultContent: '-'
                    },
                    {
                        data: 'bos_payment_mode_wise_summary_id',
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
                $('#shift_id').val('');
                $('#mop').val('');
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
                    shift_id: $('#shift_id').val(),
                    payment_mode: $('#mop').val(),
                    station_id: $('#station_id').val()
                };
                const queryString = $.param(filters);
                window.location.href = '{{ route('payment_mode_wise_summaries.export.excel') }}?' + queryString;
            });

            // Export to PDF
            $('#export-pdf-btn').on('click', function() {
                const filters = {
                    start_date: $('#from_date').val(),
                    end_date: $('#to_date').val(),
                    start_time: $('#from_time').val(),
                    end_time: $('#to_time').val(),
                    shift_id: $('#shift_id').val(),
                    payment_mode: $('#mop').val(),
                    station_id: $('#station_id').val()
                };
                const queryString = $.param(filters);
                window.location.href = '{{ route('payment_mode_wise_summaries.export.pdf') }}?' + queryString;
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
                url: '{{ route('payment_mode_wise_summaries') }}',
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

