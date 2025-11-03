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
                                        <label for="name">Fuel Grade Name</label>
                                        <input type="text" class="form-control" id="name" name="name" placeholder="Search by name">
                                    </div>
                                </div>
                                <div class="col-md-3">
                                    <div class="form-group">
                                        <label for="min_price">Minimum Price</label>
                                        <input type="number" step="0.01" class="form-control" id="min_price" name="min_price" placeholder="Min Price">
                                    </div>
                                </div>
                                <div class="col-md-3">
                                    <div class="form-group">
                                        <label for="max_price">Maximum Price</label>
                                        <input type="number" step="0.01" class="form-control" id="max_price" name="max_price" placeholder="Max Price">
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
                                    <th>Actions</th>
                                </tr>
                                </thead>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Update Price Modal -->
    <div class="modal fade" id="updatePriceModal" tabindex="-1" role="dialog">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Update Price</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <form id="updatePriceForm">
                    <div class="modal-body">
                        <div class="form-group">
                            <label for="update_price">Price</label>
                            <input type="number" step="0.01" min="0" class="form-control" name="price" id="update_price" required>
                        </div>
                        <div class="form-group">
                            <label for="update_scheduled_at">Scheduled At (Optional)</label>
                            <input type="datetime-local" class="form-control" name="scheduled_at" id="update_scheduled_at">
                            <small class="form-text text-muted">Leave empty to update immediately</small>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-primary">Update Price</button>
                    </div>
                </form>
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
            var table = $('#fuel-grades-table').DataTable({
                'processing': true,
                'serverSide': true,
                'ajax': {
                    'url': '{{ route('fuel_grades') }}',
                    'data': function (d) {
                        d.name = $('#name').val();
                        d.min_price = $('#min_price').val();
                        d.max_price = $('#max_price').val();
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
                        data: 'uuid',
                        defaultContent: '-'
                    },
                    {
                        data: 'pts_fuel_grade_id',
                        defaultContent: '-'
                    },
                    {
                        data: 'name',
                        defaultContent: '-'
                    },
                    {
                        data: 'price',
                        defaultContent: '0.00'
                    },
                    {
                        data: 'scheduled_price',
                        defaultContent: '-'
                    },
                    {
                        data: 'scheduled_at',
                        defaultContent: '-'
                    },
                    {
                        data: 'price_status',
                        defaultContent: '-'
                    },
                    {
                        data: 'expansion_coefficient',
                        defaultContent: '-'
                    },
                    {
                        data: 'blend_status',
                        defaultContent: '-'
                    },
                    {
                        data: 'blend_info',
                        defaultContent: '-'
                    },
                    {
                        data: 'bos_fuel_grade_id',
                        defaultContent: '-'
                    },
                    {
                        data: 'options',
                        orderable: false,
                        searchable: false
                    },
                ],
                'scrollX': true
            });

            // Apply filters button
            $('#filter-btn').on('click', function () {
                table.draw();
            });

            // Reset filters button
            $('#reset-btn').on('click', function () {
                $('#name').val('');
                $('#min_price').val('');
                $('#max_price').val('');
                $('#station_id').val('');
                table.draw();
            });

            // Export to Excel
            $('#export-excel-btn').on('click', function () {
                const filters = {
                    fuel_grade_name: $('#name').val(),
                    min_price: $('#min_price').val(),
                    max_price: $('#max_price').val(),
                    station_id: $('#station_id').val()
                };
                const queryString = $.param(filters);
                window.location.href = '{{ route('fuel_grades.export.excel') }}?' + queryString;
            });

            // Export to PDF
            $('#export-pdf-btn').on('click', function () {
                const filters = {
                    fuel_grade_name: $('#name').val(),
                    min_price: $('#min_price').val(),
                    max_price: $('#max_price').val(),
                    station_id: $('#station_id').val()
                };
                const queryString = $.param(filters);
                window.location.href = '{{ route('fuel_grades.export.pdf') }}?' + queryString;
            });

            // Allow Enter key to trigger filter
            $('#filter-form input, #filter-form select').on('keypress', function (e) {
                if (e.which === 13) {
                    e.preventDefault();
                    table.draw();
                }
            });

            // Load stations for dropdown
            $.ajax({
                url: '{{ route('fuel_grades') }}',
                method: 'GET',
                data: {get_stations: true},
                success: function (response) {
                    if (response.stations) {
                        response.stations.forEach(function (station) {
                            $('#station_id').append(
                                $('<option></option>').val(station.id).text(station.site_name)
                            );
                        });
                    }
                }
            });

            // Auto-filter on station dropdown change
            $('#station_id').on('change', function () {
                table.draw();
            });

            // Update price button
            $(document).on('click', '.update-price-btn', function () {
                const fuelGradeId = $(this).data('id');
                const currentPrice = $(this).data('price');
                const name = $(this).data('name');

                $('#updatePriceModal input[name="price"]').val(currentPrice);
                $('#updatePriceModal input[name="scheduled_at"]').val('');
                $('#updatePriceModal').find('.modal-title').text('Update Price - ' + name);
                $('#updatePriceForm').attr('action', '{{ route("fuel-grades.update-price", ":id") }}'.replace(':id', fuelGradeId));
                $('#updatePriceModal').modal('show');
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

            // Update price form submission
            $('#updatePriceForm').on('submit', function (e) {
                e.preventDefault();
                const form = $(this);
                const url = form.attr('action');
                const data = {
                    price: $('#updatePriceModal input[name="price"]').val(),
                    scheduled_at: $('#updatePriceModal input[name="scheduled_at"]').val() || null,
                    _token: '{{ csrf_token() }}',
                    _method: 'PUT'
                };

                $.ajax({
                    url: url,
                    type: 'POST',
                    data: data,
                    success: function (response) {
                        $('#updatePriceModal').modal('hide');
                        Swal.fire('Success', response.message || 'Price update command queued successfully', 'success');
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
