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
                                    <label for="product_select">Product (Fuel grade)</label>
                                    <select id="product_select" class="form-control">
                                        <option value="">Select product</option>
                                        @foreach ($fuel_grades as $fuel_grade_name)
                                            <option value="{{ $fuel_grade_name }}">{{ $fuel_grade_name }}</option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="form-group">
                                    <label for="new_price">New Price</label>
                                    <input id="new_price" type="number" step="0.01" min="0" class="form-control" placeholder="0.00">
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="form-group">
                                    <label for="effective_date">Effective Date</label>
                                    <input id="effective_date" type="date" class="form-control">
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="form-group">
                                    <label for="effective_time">Effective Time</label>
                                    <input id="effective_time" type="time" class="form-control">
                                </div>
                            </div>
                        </div>
                        <div class="d-flex justify-content-end">
                            <button id="apply_prefill" type="button" class="btn btn-primary">Schedule Price</button>
                        </div>
                        <small class="text-muted d-block mt-2">Schedules the selected product price for the chosen date and
                            time.</small>
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
                                        <th class="checkbox-column" style="display: none;"></th>
                                        <th>ID</th>
                                        <th>Site Name</th>
                                        <th>BOS Fuel Grade ID</th>
                                        <th>Name</th>
                                        <th>Price</th>
                                        <th>Scheduled Price</th>
                                        <th>Scheduled At</th>
                                        <th>Status</th>
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
                                        <div class="font-weight-bold">{{ $item['product_name'] }}</div>
                                        <div class="text-muted">
                                            @php
                                                $dateToConvert = $item['effective_at'] ?? $item['created_at'];
                                                $utcTimestamp =
                                                    $dateToConvert instanceof \Carbon\Carbon
                                                        ? $dateToConvert->utc()->toIso8601String()
                                                        : \Illuminate\Support\Carbon::parse($dateToConvert)->utc()->toIso8601String();
                                            @endphp
                                            <span class="price-history-date" data-utc="{{ $utcTimestamp }}">
                                                {{ $dateToConvert instanceof \Carbon\Carbon
                                                    ? $dateToConvert->format('Y-m-d
                                                                                        H:i')
                                                    : \Illuminate\Support\Carbon::parse($dateToConvert)->format('Y-m-d H:i') }}
                                            </span>
                                        </div>
                                        @if ($item['changed_by_user_name'])
                                            <div class="text-muted small">
                                                Changed by:
                                                {{ $item['changed_by_user_name'] }}
                                            </div>
                                        @endif
                                        @if ($item['status'])
                                            <div class="text-muted small">Status: {{ $item['status'] }}</div>
                                        @endif
                                        @if ($item['source_system'])
                                            <div class="text-muted small">Source system: {{ $item['source_system'] }}</div>
                                        @endif
                                        <div class="text-muted small">Change type: {{ $item['change_type'] ?? '' }}</div>
                                    </div>
                                    <div class="text-right">
                                        <div>
                                            @php
                                                $from = $item['price_from'];
                                                $to = $item['price_to'];
                                            @endphp
                                            @if (!is_null($from) && !is_null($to) && $from != $to)
                                                ${{ number_format((float) $from, 2) }} → ${{ number_format((float) $to, 2) }}
                                            @elseif(!is_null($to))
                                                ${{ number_format((float) $to, 2) }}
                                            @else
                                                N/A
                                            @endif
                                        </div>
                                    </div>
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
@endsection

@push('js')
    <script>
        $(document).ready(function() {
            const USER_TIMEZONE = moment.tz.guess();

            // DataTable for price updates
            var table = $('#fuel-grades-table').DataTable({
                'processing': true,
                'serverSide': true,
                'ajax': {
                    'url': "{{ route('price-updates.datatable') }}",
                    'data': function(d) {
                        d.name = $('#product_select').val() || '';
                    }
                },
                'order': [
                    [1, 'desc']
                ],
                'columns': [{
                        data: 'checkbox',
                        name: 'checkbox',
                        orderable: false,
                        searchable: false,
                        className: 'checkbox-column'
                    },
                    {
                        data: 'id',
                        name: 'id'
                    },
                    {
                        data: 'station_name',
                        name: 'station.site_name',
                        defaultContent: '-'
                    },
                    {
                        data: 'bos_fuel_grade_id',
                        name: 'bos_fuel_grade_id',
                        defaultContent: '-'
                    },
                    {
                        data: 'name',
                        name: 'name',
                        defaultContent: '-'
                    },
                    {
                        data: 'price',
                        name: 'price',
                        defaultContent: '0.00'
                    },
                    {
                        data: 'scheduled_price',
                        name: 'scheduled_price',
                        defaultContent: '-'
                    },
                    {
                        data: 'scheduled_at',
                        name: 'scheduled_at',
                        defaultContent: '-',
                        render: function(data, type, row) {
                            if (data && data !== '-' && type === 'display') {
                                // Parse UTC datetime and convert to user timezone
                                const utcMoment = moment.utc(data, 'YYYY-MM-DD HH:mm:ss');
                                if (utcMoment.isValid()) {
                                    return utcMoment.tz(USER_TIMEZONE).format('YYYY-MM-DD HH:mm:ss');
                                }
                            }
                            return data || '-';
                        }
                    },
                    {
                        data: 'status',
                        name: 'status',
                        orderable: false,
                        searchable: false,
                        defaultContent: '-'
                    }
                ],
                'columnDefs': [{
                    'targets': 0,
                    'visible': false,
                    'orderable': false
                }],
                destroy: true,
            });

            // Show/hide checkboxes based on product selection
            function toggleCheckboxes() {
                const productSelected = $('#product_select').val() !== '';

                if (productSelected) {
                    table.column(0).visible(true);
                    $('th.checkbox-column').show();
                } else {
                    table.column(0).visible(false);
                    $('th.checkbox-column').hide();
                    // Uncheck all checkboxes when product is deselected
                    $('.fuel-grade-checkbox').prop('checked', false);
                }
            }

            // Update table when product selection changes
            $('#product_select').on('change', function() {
                toggleCheckboxes();
                table.ajax.reload();
            });

            // Initial state - hide checkboxes
            toggleCheckboxes();

            // Direct schedule on click (no modal)
            $('#apply_prefill').on('click', function() {
                const productName = $('#product_select').val();
                const price = $('#new_price').val();
                const date = $('#effective_date').val();
                const time = $('#effective_time').val();
                const scheduledAt = date && time ? (date + 'T' + time) : '';

                if (!productName) {
                    alert('Please select a product.');
                    return;
                }
                if (!price) {
                    alert('Please enter the new price.');
                    return;
                }
                if (!scheduledAt) {
                    alert('Please select both effective date and time.');
                    return;
                }

                // Note: This will need to be updated based on your next requirements
                // For now, showing an alert that this needs implementation
                alert('Schedule functionality will be implemented based on your next requirements.');
            });

            // Override Update button to behave like direct Schedule on this page
            $(document).on('click', '.update-price-btn', function() {
                const fuelGradeId = $(this).data('id');
                const currentPrice = $(this).data('price');
                const name = $(this).data('name');
                const date = $('#effective_date').val();
                const time = $('#effective_time').val();
                const scheduledAt = date && time ? (date + 'T' + time) : '';

                if (!scheduledAt) {
                    alert('Please select both effective date and time.');
                    return;
                }

                const url = '{{ route('fuel-grades.schedule-price', ':id') }}'.replace(':id', fuelGradeId);
                $.ajax({
                    url: url,
                    type: 'POST',
                    data: {
                        scheduled_price: currentPrice || $('#new_price').val(),
                        scheduled_at: scheduledAt,
                        user_timezone: USER_TIMEZONE,
                        _token: '{{ csrf_token() }}',
                        _method: 'PUT'
                    },
                    success: function(response) {
                        Swal.fire('Success', response.message || 'Price schedule command queued successfully', 'success');
                        $('#fuel-grades-table').DataTable().draw();
                    },
                    error: function(xhr) {
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

            // Schedule price button from table acts as direct schedule
            $(document).on('click', '.schedule-price-btn', function() {
                const fuelGradeId = $(this).data('id');
                const currentPrice = $(this).data('price');
                const name = $(this).data('name');
                const date = $('#effective_date').val();
                const time = $('#effective_time').val();
                const scheduledAt = date && time ? (date + 'T' + time) : '';

                if (!scheduledAt) {
                    alert('Please select both effective date and time.');
                    return;
                }

                const url = '{{ route('fuel-grades.schedule-price', ':id') }}'.replace(':id', fuelGradeId);
                $.ajax({
                    url: url,
                    type: 'POST',
                    data: {
                        scheduled_price: currentPrice,
                        scheduled_at: scheduledAt,
                        user_timezone: USER_TIMEZONE,
                        _token: '{{ csrf_token() }}',
                        _method: "PUT"
                    },
                    success: function(response) {
                        Swal.fire('Success', response.message || 'Price schedule command queued successfully', 'success');
                        $('#fuel-grades-table').DataTable().draw();
                    },
                    error: function(xhr) {
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

            // Note: No direct update flow on this page; all actions use schedule route

            // No modal submission handler needed

            // Convert UTC dates to user timezone
            $('.price-history-date').each(function() {
                const $element = $(this);
                const utcTimestamp = $element.data('utc');
                if (utcTimestamp) {
                    const userTime = moment.tz(utcTimestamp, USER_TIMEZONE);
                    $element.text(userTime.format('Y-MM-DD HH:mm'));
                }
            });
        });
    </script>
@endpush
