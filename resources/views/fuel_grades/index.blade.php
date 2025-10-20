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
                                <div class="col-md-4">
                                    <div class="form-group">
                                        <label for="name">Fuel Grade Name</label>
                                        <input type="text" class="form-control" id="name" name="name" placeholder="Search by name">
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="form-group">
                                        <label for="min_price">Minimum Price</label>
                                        <input type="number" step="0.01" class="form-control" id="min_price" name="min_price" placeholder="Min Price">
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="form-group">
                                        <label for="max_price">Maximum Price</label>
                                        <input type="number" step="0.01" class="form-control" id="max_price" name="max_price" placeholder="Max Price">
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
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('js')
    <script>
        $(document).ready(function() {
            var table = $('#fuel-grades-table').DataTable({
                'processing': true,
                'serverSide': true,
                'ajax': {
                    'url': '{{ route('fuel_grades') }}',
                    'data': function(d) {
                        d.name = $('#name').val();
                        d.min_price = $('#min_price').val();
                        d.max_price = $('#max_price').val();
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
                ],
                'scrollX': true
            });

            // Apply filters button
            $('#filter-btn').on('click', function() {
                table.draw();
            });

            // Reset filters button
            $('#reset-btn').on('click', function() {
                $('#name').val('');
                $('#min_price').val('');
                $('#max_price').val('');
                table.draw();
            });

            // Export to Excel
            $('#export-excel-btn').on('click', function() {
                const filters = {
                    fuel_grade_name: $('#name').val(),
                    min_price: $('#min_price').val(),
                    max_price: $('#max_price').val()
                };
                const queryString = $.param(filters);
                window.location.href = '{{ route('fuel_grades.export.excel') }}?' + queryString;
            });

            // Export to PDF
            $('#export-pdf-btn').on('click', function() {
                const filters = {
                    fuel_grade_name: $('#name').val(),
                    min_price: $('#min_price').val(),
                    max_price: $('#max_price').val()
                };
                const queryString = $.param(filters);
                window.location.href = '{{ route('fuel_grades.export.pdf') }}?' + queryString;
            });

            // Allow Enter key to trigger filter
            $('#filter-form input, #filter-form select').on('keypress', function(e) {
                if (e.which === 13) {
                    e.preventDefault();
                    table.draw();
                }
            });
        });
    </script>
@endpush

