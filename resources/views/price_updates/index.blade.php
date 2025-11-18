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
                                        @foreach ($stations as $station)
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
                            <button id="apply_prefill" type="button" class="btn btn-primary">Schedule Price</button>
                        </div>
                        <small class="text-muted d-block mt-2">Schedules the selected product price for the chosen station,
                            date and time.</small>
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
            // Load products on station change
            $('#station_select').on('change', function() {
                const stationId = $(this).val();
                $('#product_select').prop('disabled', true).empty().append('<option value="">Select product</option>');
                if (!stationId) {
                    // also refresh table filter
                    $('#fuel-grades-table').DataTable().draw();
                    return;
                }
                $.get("{{ route('price-updates.products') }}", {
                    station_id: stationId
                }, function(response) {
                    const products = response.products || [];
                    products.forEach(function(p) {
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
                    'data': function(d) {
                        d.name = '';
                        d.min_price = '';
                        d.max_price = '';
                        d.station_id = $('#station_select').val();
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

                ]
            });

            // Direct schedule on click (no modal)
            $('#apply_prefill').on('click', function() {
                const stationId = $('#station_select').val();
                const productId = $('#product_select').val();
                const productName = $('#product_select option:selected').text();
                const price = $('#new_price').val();
                const date = $('#effective_date').val();
                const time = $('#effective_time').val();
                const scheduledAt = date && time ? (date + 'T' + time) : '';

                if (!stationId) {
                    alert('Please select a station first.');
                    return;
                }
                if (!productId) {
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

                // Direct submit to schedule endpoint
                const url = '{{ route('fuel-grades.schedule-price', ':id') }}'.replace(':id', productId);
                $.ajax({
                    url: url,
                    type: 'POST',
                    data: {
                        scheduled_price: price,
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
