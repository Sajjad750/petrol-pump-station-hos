@extends('layouts.adminlte')
@push('css')
@endpush

@section('content')
    <div class="container-fluid">
        <div class="row">
            <div class="col-md-12">
                <div class="card custom-card mb-3">
                    <div class="card-header custom-card-header d-flex justify-content-between align-items-center">
                        <h5 class="mb-0">New Price Schedule</h5>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-3">
                                <div class="form-group">
                                    <label for="station_select">Station</label>
                                    <select id="station_select" class="form-control">
                                        <option value="">Select station</option>
                                        @foreach($stations as $station)
                                            <option value="{{ $station->id }}">{{ $station->site_name }}</option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="form-group">
                                    <label for="product_select">Product</label>
                                    <select id="product_select" class="form-control" disabled>
                                        <option value="">Select product</option>
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-2">
                                <div class="form-group">
                                    <label for="new_price">New Price</label>
                                    <input id="new_price" type="number" step="0.01" min="0" class="form-control" placeholder="0.00">
                                </div>
                            </div>
                            <div class="col-md-2">
                                <div class="form-group">
                                    <label for="effective_date">Effective Date</label>
                                    <input id="effective_date" type="date" class="form-control">
                                </div>
                            </div>
                            <div class="col-md-2">
                                <div class="form-group">
                                    <label for="effective_time">Effective Time</label>
                                    <input id="effective_time" type="time" class="form-control">
                                </div>
                            </div>
                        </div>
                        <div class="d-flex justify-content-end">
                            <button id="apply_prefill" type="button" class="btn btn-primary">Apply Prefill</button>
                        </div>
                        <small class="text-muted d-block mt-2">Apply Prefill will pre-fill the modal when you click Update/Schedule in the table below.</small>
                    </div>
                </div>

                <div class="card custom-card">
                    <div class="card-header custom-card-header">
                        <h4 class="mb-0">Fuel Grades</h4>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table id="fuel-grades-table" class="table-bordered table">
                                <thead class="custom-table-header">
                                <tr>
                                    <th>ID</th>
                                    <th>Station</th>
                                    <th>UUID</th>
                                    <th>PTS Fuel Grade ID</th>
                                    <th>Name</th>
                                    <th>Price</th>
                                    <th>Scheduled Price</th>
                                    <th>Scheduled At</th>
                                    <th>Price Status</th>
                                    <th>Expansion Coefficient</th>
                                    <th>Blend Status</th>
                                    <th>Blend Info</th>
                                    <th>BOS Fuel Grade ID</th>
                            
                                </tr>
                                </thead>
                            </table>
                        </div>
                    </div>
                </div>

                <div class="card custom-card mt-3">
                    <div class="card-header custom-card-header">
                        <h5 class="mb-0">Price Change History</h5>
                    </div>
                    <div class="card-body">
                        <div class="list-group">
                            @forelse($history as $item)
                                <div class="list-group-item d-flex justify-content-between align-items-center">
                                    <div>
                                        <strong>{{ $item->command_type === 'schedule_fuel_grade_price' ? 'Scheduled' : 'Updated' }}</strong>
                                        <span class="text-muted">#{{ $item->id }} · {{ $item->created_at->format('Y-m-d H:i') }}</span>
                                    </div>
                                    <code class="mb-0">@json($item->command_data)</code>
                                </div>
                            @empty
                                <div class="text-muted">No recent price changes.</div>
                            @endforelse
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    

    <!-- Schedule Price Modal -->
    <div class="modal fade" id="schedulePriceModal" tabindex="-1" role="dialog">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Schedule Price</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <form id="schedulePriceForm">
                    <div class="modal-body">
                        <div class="form-group">
                            <label for="schedule_price">Scheduled Price</label>
                            <input type="number" step="0.01" min="0" class="form-control" name="scheduled_price" id="schedule_price" required>
                        </div>
                        <div class="form-group">
                            <label for="schedule_scheduled_at">Scheduled At</label>
                            <input type="datetime-local" class="form-control" name="scheduled_at" id="schedule_scheduled_at" required>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-warning">Schedule Price</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endsection

@push('js')
    <script>
        $(document).ready(function () {
            // Load products on station change
            $('#station_select').on('change', function () {
                const stationId = $(this).val();
                $('#product_select').prop('disabled', true).empty().append('<option value="">Select product</option>');
                if (!stationId) {
                    // also refresh table filter
                    $('#fuel-grades-table').DataTable().draw();
                    return;
                }
                $.get("{{ route('price-updates.products') }}", {station_id: stationId}, function (response) {
                    const products = response.products || [];
                    products.forEach(function (p) {
                        $('#product_select').append($('<option></option>').val(p.id).text(p.name));
                    });
                    $('#product_select').prop('disabled', false);
                    $('#fuel-grades-table').DataTable().draw();
                });
            });

            // DataTable (same source as fuel grades, but filtered by station/product)
            var table = $('#fuel-grades-table').DataTable({
                'processing': true,
                'serverSide': true,
                'ajax': {
                    'url': "{{ route('fuel_grades') }}",
                    'data': function (d) {
                        d.name = '';
                        d.min_price = '';
                        d.max_price = '';
                        d.station_id = $('#station_select').val();
                    }
                },
                'order': [0, 'desc'],
                'columns': [
                    {data: 'id'},
                    {data: 'station.site_name', defaultContent: '-'},
                    {data: 'uuid', defaultContent: '-'},
                    {data: 'pts_fuel_grade_id', defaultContent: '-'},
                    {data: 'name', defaultContent: '-'},
                    {data: 'price', defaultContent: '0.00'},
                    {data: 'scheduled_price', defaultContent: '-'},
                    {data: 'scheduled_at', defaultContent: '-'},
                    {data: 'price_status', defaultContent: '-'},
                    {data: 'expansion_coefficient', defaultContent: '-'},
                    {data: 'blend_status', defaultContent: '-'},
                    {data: 'blend_info', defaultContent: '-'},
                    {data: 'bos_fuel_grade_id', defaultContent: '-'},
                   
                ]
            });

            // Prefill logic: when user clicks Update/Schedule in table, populate modals from top form
            $('#apply_prefill').on('click', function () {
                const price = $('#new_price').val();
                const date = $('#effective_date').val();
                const time = $('#effective_time').val();
                const scheduledAt = date && time ? (date + 'T' + time) : '';

                if (price) {
                    $('#schedulePriceModal input[name="scheduled_price"]').val(price);
                }
                $('#schedulePriceModal input[name="scheduled_at"]').val(scheduledAt);
            });

            // Override Update button to behave like Schedule button on this page
            $(document).on('click', '.update-price-btn', function () {
                const fuelGradeId = $(this).data('id');
                const currentPrice = $(this).data('price');
                const name = $(this).data('name');

                $('#schedulePriceModal input[name="scheduled_price"]').val(currentPrice || $('#new_price').val());
                $('#schedulePriceModal input[name="scheduled_at"]').val('');
                $('#schedulePriceModal').find('.modal-title').text('Schedule Price - ' + name);
                $('#schedulePriceForm').attr('action', '{{ route("fuel-grades.schedule-price", ":id") }}'.replace(':id', fuelGradeId));
                $('#schedulePriceModal').modal('show');
            });

            // Schedule price button
            $(document).on('click', '.schedule-price-btn', function () {
                const fuelGradeId = $(this).data('id');
                const currentPrice = $(this).data('price');
                const name = $(this).data('name');

                $('#schedulePriceModal input[name="scheduled_price"]').val(currentPrice);
                $('#schedulePriceModal input[name="scheduled_at"]').val('');
                $('#schedulePriceModal').find('.modal-title').text('Schedule Price - ' + name);
                $('#schedulePriceForm').attr('action', '{{ route("fuel-grades.schedule-price", ":id") }}'.replace(':id', fuelGradeId));
                $('#schedulePriceModal').modal('show');
            });

            // Note: No direct update flow on this page; all actions use schedule route

            // Schedule price form submission
            $('#schedulePriceForm').on('submit', function (e) {
                e.preventDefault();
                const form = $(this);
                const url = form.attr('action');
                const data = {
                    scheduled_price: $('#schedulePriceModal input[name="scheduled_price"]').val(),
                    scheduled_at: $('#schedulePriceModal input[name="scheduled_at"]').val(),
                    _token: '{{ csrf_token() }}',
                    _method: 'PUT'
                };

                $.ajax({
                    url: url,
                    type: 'POST',
                    data: data,
                    success: function (response) {
                        $('#schedulePriceModal').modal('hide');
                        Swal.fire('Success', response.message || 'Price schedule command queued successfully', 'success');
                        table.draw();
                    },
                    error: function (xhr) {
                        let errorMsg = 'An error occurred';
                        if (xhr.responseJSON && xhr.responseJSON.message) {
                            errorMsg = xhr.responseJSON.message;
                        } else if (xhr.responseJSON && xhr.responseJSON.errors) {
                            errorMsg = Object.values(xhr.responseJSON.errors).flat().join('<br>');
                        }
                        Swal.fire('Error', errorMsg, 'error');
                    }
                });
            });
        });
    </script>
@endpush


