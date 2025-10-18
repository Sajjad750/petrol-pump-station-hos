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
                        <h4 class="mb-0">Pump Transactions</h4>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table id="pump-transactions-table" class="table-striped table-hover custom-table table">
                                <thead class="custom-table-header">
                                <tr>
                                    <th>ID</th>
                                    <th>Request ID</th>
                                    <th>Start Time</th>
                                    <th>End Time</th>
                                    <th>Pump ID</th>
                                    <th>Nozzle ID</th>
                                    <th>Fuel Grade</th>
                                    <th>Fuel Grade Name</th>
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
                                    <th>Configuration ID</th>
                                    <th>MOP</th>
                                </tr>
                                </thead>
                            </table>
                        </div>
                        <!-- Pagination -->
                        <div class="d-flex justify-content-center mt-4">

                        </div>

                        <!-- <div class="py-4 text-center">
                                            <i class="bi bi-inbox display-1 text-muted"></i>
                                            <h5 class="mt-3">No transactions found</h5>
                                            <p class="text-muted">There are no pump transactions to display.</p>
                                        </div> -->

                    </div>
                </div>
            </div>
        </div>

        <!-- Transaction Details Modal -->
        <!-- <div class="modal fade" id="transactionModal" tabindex="-1" aria-labelledby="transactionModalLabel" aria-hidden="true">
                            <div class="modal-dialog modal-lg">
                                <div class="modal-content">
                                    <div class="modal-header">
                                        <h5 class="modal-title" id="transactionModalLabel">Transaction Details</h5>
                                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                    </div>
                                    <div class="modal-body" id="transactionDetails"> -->
        <!-- Transaction details will be loaded here -->
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
            var table = $('#pump-transactions-table').DataTable({
                'processing': true,
                'serverSide': true,
                'ajax': {
                    'url': '{{ route('pump_transactions') }}',
                    'data': function(d) {
                        // Enhanced date and time filtering parameters
                        d.from_date = $('#filter-from-date').val();
                        d.to_date = $('#filter-to-date').val();
                        d.from_time = $('#filter-from-time').val();
                        d.to_time = $('#filter-to-time').val();
                        d.start_time = $('#filter-start-time').val();
                        d.end_time = $('#filter-end-time').val();
                        d.fuel_grade = $('#filter-fuel-grade').val();
                        d.pump_id = $('#filter-pump-id').val();

                        // Log filter parameters for debugging
                        console.log('Filter parameters sent to backend:', {
                            from_date: d.from_date,
                            to_date: d.to_date,
                            from_time: d.from_time,
                            to_time: d.to_time,
                            pump_id: d.pump_id,
                            fuel_grade: d.fuel_grade
                        });
                    }
                },
                'order': [0, 'desc'],
                'columns': [{
                    data: 'id'
                },
                    {
                        data: 'request_id'
                    },
                    {
                        data: 'date_time_start'
                    },
                    {
                        data: 'date_time_end'
                    },
                    {
                        data: 'pts_pump_id'
                    },
                    {
                        data: 'pts_nozzle_id'
                    },
                    {
                        data: 'pts_fuel_grade_id'
                    },
                    {
                        data: 'fuel_grade_name'
                    },
                    {
                        data: 'pts_tank_id'
                    },
                    {
                        data: 'transaction_number'
                    },
                    {
                        data: 'volume'
                    },
                    {
                        data: 'tc_volume'
                    },
                    {
                        data: 'price'
                    },
                    {
                        data: 'amount'
                    },
                    {
                        data: 'starting_totalizer'
                    },
                    {
                        data: 'total_volume'
                    },
                    {
                        data: 'total_amount'
                    },
                    {
                        data: 'tag'
                    },
                    {
                        data: 'pts_user_id'
                    },
                    {
                        data: 'pts_configuration_id'
                    },
                    {
                        data: 'mode_of_payment',
                    }
                ]
            });




        });


    </script>
@endpush
