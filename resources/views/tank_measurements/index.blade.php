@extends('layouts.adminlte')
@push('css')
@endpush

@section('content')
    <div class="container-fluid">
        <div class="row">
            <!-- Main Content -->
            <div class="col-md-12">
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




        });
    </script>
@endpush
