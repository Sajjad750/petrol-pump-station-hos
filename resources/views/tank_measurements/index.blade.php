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
                                <div class="col-md-3">
                                    <div class="form-group">
                                        <label for="from_date">From Date</label>
                                        <input type="date" class="form-control" id="from_date" name="from_date">
                                    </div>
                                </div>
                                <div class="col-md-3">
                                    <div class="form-group">
                                        <label for="to_date">To Date</label>
                                        <input type="date" class="form-control" id="to_date" name="to_date">
                                    </div>
                                </div>
                                <div class="col-md-3">
                                    <div class="form-group">
                                        <label for="status">Status</label>
                                        <select class="form-control" id="status" name="status">
                                            <option value="">All Statuses</option>
                                        </select>
                                    </div>
                                </div>
                                <div class="col-md-3">
                                    <div class="form-group">
                                        <label for="tank_id">Tank ID</label>
                                        <input type="text" class="form-control" id="tank_id" name="tank_id" placeholder="Enter Tank ID">
                                    </div>
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-md-12">
                                    <button type="button" id="filter-btn" class="btn btn-primary">
                                        <i class="fas fa-filter"></i> Apply Filters
                                    </button>
                                    <button type="button" id="reset-btn" class="btn btn-secondary">
                                        <i class="fas fa-redo"></i> Reset
                                    </button>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>

                <div class="card custom-card">
                    <div class="card-header custom-card-header">
                        <h4 class="mb-0">Tank Measurements</h4>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table id="tank-measurements-table" class="table-bordered table">
                                <thead class="custom-table-header">
                                    <tr>
                                        <th>ID</th>
                                        <th>Station</th>
                                        <th>UUID</th>
                                        <th>Request ID</th>
                                        <th>PTS ID</th>
                                        <th>Date Time</th>
                                        <th>Tank</th>
                                        <th>Fuel Grade ID</th>
                                        <th>Fuel Grade Name</th>
                                        <th>Status</th>
                                        <th>Alarms</th>
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
                                        <th>Configuration ID</th>
                                        <th>BOS Tank Measurement ID</th>
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
                        <!-- Pagination -->
                        <div class="d-flex justify-content-center mt-4">

                        </div>

                        <!-- <div class="py-4 text-center">
                                                <i class="bi bi-inbox display-1 text-muted"></i>
                                                <h5 class="mt-3">No tank measurements found</h5>
                                                <p class="text-muted">There are no tank measurements to display.</p>
                                            </div> -->

                    </div>
                </div>
            </div>
        </div>

        <!-- Tank Measurement Details Modal -->
        <!-- <div class="modal fade" id="tankMeasurementModal" tabindex="-1" aria-labelledby="tankMeasurementModalLabel" aria-hidden="true">
                                <div class="modal-dialog modal-lg">
                                    <div class="modal-content">
                                        <div class="modal-header">
                                            <h5 class="modal-title" id="tankMeasurementModalLabel">Tank Measurement Details</h5>
                                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                        </div>
                                        <div class="modal-body" id="tankMeasurementDetails"> -->
        <!-- Tank measurement details will be loaded here -->
        <!-- </div>
                                        <div class="modal-footer">
                                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                                        </div>
                                    </div>
                                </div>
                            </div> -->
    </div>
@endsection

@push('js')
    <script>
        $(document).ready(function() {
            var table = $('#tank-measurements-table').DataTable({
                'processing': true,
                'serverSide': true,
                'ajax': {
                    'url': '{{ route('tank_measurements') }}',
                    'data': function(d) {
                        d.from_date = $('#from_date').val();
                        d.to_date = $('#to_date').val();
                        d.status = $('#status').val();
                        d.tank_id = $('#tank_id').val();
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
                        data: 'date_time'
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
                        data: 'status',
                        defaultContent: '-'
                    },
                    {
                        data: 'alarms'
                    },
                    {
                        data: 'product_height'
                    },
                    {
                        data: 'water_height'
                    },
                    {
                        data: 'temperature'
                    },
                    {
                        data: 'product_volume'
                    },
                    {
                        data: 'water_volume'
                    },
                    {
                        data: 'product_ullage'
                    },
                    {
                        data: 'product_tc_volume'
                    },
                    {
                        data: 'product_density'
                    },
                    {
                        data: 'product_mass'
                    },
                    {
                        data: 'tank_filling_percentage'
                    },
                    {
                        data: 'configuration_id',
                        defaultContent: '-'
                    },
                    {
                        data: 'bos_tank_measurement_id',
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
                ]
            });

            // Load filter options
            $.ajax({
                url: '{{ route('tank_measurements') }}',
                type: 'GET',
                data: {
                    get_filter_options: true
                },
                success: function(response) {
                    // Populate status dropdown
                    if (response.statuses) {
                        response.statuses.forEach(function(status) {
                            if (status) {
                                $('#status').append('<option value="' + status + '">' + status + '</option>');
                            }
                        });
                    }
                }
            });

            // Apply filters button
            $('#filter-btn').on('click', function() {
                table.draw();
            });

            // Reset filters button
            $('#reset-btn').on('click', function() {
                $('#from_date').val('');
                $('#to_date').val('');
                $('#status').val('');
                $('#tank_id').val('');
                table.draw();
            });

            // Allow Enter key to trigger filter
            $('#filter-form input, #filter-form select').on('keypress', function(e) {
                if (e.which === 13) {
                    e.preventDefault();
                    table.draw();
                }
            });

            // Auto-filter on status dropdown change
            $('#status').on('change', function() {
                table.draw();
            });
        });
    </script>
@endpush
