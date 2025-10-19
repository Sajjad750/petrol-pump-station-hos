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
                        <h4 class="mb-0">Pumps</h4>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table id="pumps-table" class="table-bordered table">
                                <thead class="custom-table-header">
                                    <tr>
                                        <th>ID</th>
                                        <th>Station</th>
                                        <th>BOS Pump ID</th>
                                        <th>BOS UUID</th>
                                        <th>Name</th>
                                        <th>Pump ID</th>
                                        <th>Self Service</th>
                                        <th>Nozzles Count</th>
                                        <th>Status</th>
                                        <th>PTS Pump ID</th>
                                        <th>PTS Port ID</th>
                                        <th>PTS Address ID</th>
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
                                                <h5 class="mt-3">No pumps found</h5>
                                                <p class="text-muted">There are no pumps to display.</p>
                                            </div> -->

                    </div>
                </div>
            </div>
        </div>

        <!-- Pump Details Modal -->
        <!-- <div class="modal fade" id="pumpModal" tabindex="-1" aria-labelledby="pumpModalLabel" aria-hidden="true">
                                <div class="modal-dialog modal-lg">
                                    <div class="modal-content">
                                        <div class="modal-header">
                                            <h5 class="modal-title" id="pumpModalLabel">Pump Details</h5>
                                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                        </div>
                                        <div class="modal-body" id="pumpDetails"> -->
        <!-- Pump details will be loaded here -->
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
            var table = $('#pumps-table').DataTable({
                'processing': true,
                'serverSide': true,
                'ajax': {
                    'url': '{{ route('pumps') }}',
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
                        data: 'bos_pump_id'
                    },
                    {
                        data: 'bos_uuid',
                        defaultContent: '-'
                    },
                    {
                        data: 'name',
                        defaultContent: '-'
                    },
                    {
                        data: 'pump_id',
                        defaultContent: '-'
                    },
                    {
                        data: 'is_self_service'
                    },
                    {
                        data: 'nozzles_count',
                        defaultContent: '-'
                    },
                    {
                        data: 'status',
                        defaultContent: '-'
                    },
                    {
                        data: 'pts_pump_id'
                    },
                    {
                        data: 'pts_port_id',
                        defaultContent: '-'
                    },
                    {
                        data: 'pts_address_id',
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
