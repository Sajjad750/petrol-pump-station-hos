<!-- Filters Card -->
<div class="card custom-card mb-3">
    <div class="card-header custom-card-header">
        <h6 class="mb-0" style="color: #D7D7D7;"><i class="fas fa-filter"></i> Filters</h6>
    </div>
    <div class="card-body">
        <form id="tank-monitoring-filter-form">
            <div class="row">
                <div class="col-md-2">
                    <div class="form-group">
                        <label for="tank_monitoring_station_id">Station</label>
                        <select class="form-control" id="tank_monitoring_station_id" name="station_id">
                            <option value="">All Stations</option>
                        </select>
                    </div>
                </div>
                <div class="col-md-2">
                    <div class="form-group">
                        <label for="tank_monitoring_from_date">From Date</label>
                        <input type="date" class="form-control" id="tank_monitoring_from_date" name="from_date">
                    </div>
                </div>
                <div class="col-md-2">
                    <div class="form-group">
                        <label for="tank_monitoring_to_date">To Date</label>
                        <input type="date" class="form-control" id="tank_monitoring_to_date" name="to_date">
                    </div>
                </div>
                <div class="col-md-2">
                    <div class="form-group">
                        <label for="tank_monitoring_from_time">From Time</label>
                        <input type="time" class="form-control" id="tank_monitoring_from_time" name="from_time">
                    </div>
                </div>
                <div class="col-md-2">
                    <div class="form-group">
                        <label for="tank_monitoring_to_time">To Time</label>
                        <input type="time" class="form-control" id="tank_monitoring_to_time" name="to_time">
                    </div>
                </div>
                <div class="col-md-2">
                    <div class="form-group">
                        <label for="tank_monitoring_product_id">Product</label>
                        <select class="form-control" id="tank_monitoring_product_id" name="product_id">
                            <option value="">All Products</option>
                        </select>
                    </div>
                </div>
            </div>
            <div class="row">
                <div class="col-md-2">
                    <div class="form-group">
                        <label for="tank_monitoring_tank">Tank</label>
                        <select class="form-control" id="tank_monitoring_tank" name="tank">
                            <option value="">All Tanks</option>
                        </select>
                    </div>
                </div>
                <div class="col-md-2">
                    <div class="form-group">
                        <label for="tank_monitoring_status">Status</label>
                        <select class="form-control" id="tank_monitoring_status" name="status">
                            <option value="">All Status</option>
                            <option value="critical">Critical</option>
                            <option value="low">Low</option>
                            <option value="normal">Normal</option>
                        </select>
                    </div>
                </div>
                <div class="col-md-8 d-flex justify-content-end align-items-end" style="gap: 10px;">
                    <button type="button" id="tank-monitoring-filter-btn" class="btn btn-dark">
                        <i class="fas fa-filter"></i> Search Filters
                    </button>
                    <button type="button" id="tank-monitoring-reset-btn" class="btn btn-dark">
                        <i class="fas fa-redo"></i> Reset
                    </button>
                </div>
            </div>
        </form>
    </div>
</div>

<!-- Tank Monitoring Data Table -->
<div class="card custom-card mb-3">
    <div class="card-header custom-card-header">
        <h6 class="mb-0" style="color: #D7D7D7;"><i class="fas fa-table"></i> Tank Monitoring Data</h6>
    </div>
    <div class="card-body" style="padding: 0;">
        <div class="table-responsive">
            <table id="tank-monitoring-table" class="table">
                <thead>
                    <tr>
                        <th>Station</th>
                        <th>Date and Time</th>
                        <th>Product</th>
                        <th>Tank</th>
                        <th class="text-right">Product Volume (L)</th>
                        <th>Current & Percentage</th>
                        <th>Status</th>
                    </tr>
                </thead>
                <tbody id="tank-monitoring-tbody">
                    <tr>
                        <td colspan="7" class="text-center">No data available. Please apply filters.</td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>
</div>

@push('css')
<style>
.status-critical {
    color: #dc3545;
    font-weight: 600;
}

.status-low {
    color: #fd7e14;
    font-weight: 600;
}

.status-normal {
    color: #28a745;
    font-weight: 600;
}

.progress-bar-critical {
    background-color: #dc3545;
}

.progress-bar-low {
    background-color: #fd7e14;
}

.progress-bar-normal {
    background-color: #28a745;
}

.percentage-display {
    display: flex;
    align-items: center;
    gap: 10px;
}

.percentage-value {
    font-weight: 600;
    min-width: 45px;
}

.tank-percentage-progress {
    flex: 1;
    height: 20px;
    background-color: #e9ecef;
    border-radius: 4px;
    overflow: hidden;
    position: relative;
}

.tank-percentage-progress-bar {
    height: 100%;
    transition: width 0.3s ease;
    border-radius: 4px;
}
</style>
@endpush

@push('js')
<script>
$(document).ready(function() {
    // Load stations for dropdown
    $.ajax({
        url: '{{ route('hos-reports.stations') }}',
        method: 'GET',
        success: function(response) {
            if (response.stations) {
                response.stations.forEach(function(station) {
                    $('#tank_monitoring_station_id').append(
                        $('<option></option>').val(station.id).text(station.site_name)
                    );
                });
            }
        }
    });

    // Load fuel grades for dropdown
    $.ajax({
        url: '{{ route('hos-reports.fuel-grades') }}',
        method: 'GET',
        success: function(response) {
            if (response.fuel_grades) {
                response.fuel_grades.forEach(function(fuelGrade) {
                    $('#tank_monitoring_product_id').append(
                        $('<option></option>').val(fuelGrade.id).text(fuelGrade.name)
                    );
                });
            }
        }
    });

    // Load tanks for dropdown
    function loadTanks() {
        const stationId = $('#tank_monitoring_station_id').val();
        $.ajax({
            url: '{{ route('hos-reports.tanks-from-measurements') }}',
            method: 'GET',
            data: { station_id: stationId },
            success: function(response) {
                $('#tank_monitoring_tank').empty().append('<option value="">All Tanks</option>');
                if (response.tanks) {
                    response.tanks.forEach(function(tank) {
                        $('#tank_monitoring_tank').append(
                            $('<option></option>').val(tank.tank).text(tank.tank_formatted)
                        );
                    });
                }
            }
        });
    }

    // Load tanks when station changes
    $('#tank_monitoring_station_id').on('change', function() {
        loadTanks();
    });

    // Initial load of tanks
    loadTanks();

    // Function to get status class and color
    function getStatusClass(status) {
        return 'status-' + status.toLowerCase();
    }

    function getProgressBarClass(status) {
        return 'progress-bar-' + status.toLowerCase();
    }

    // Function to render Tank Monitoring Data
    function renderTankMonitoring(data) {
        const tbody = $('#tank-monitoring-tbody');
        tbody.empty();
        
        if (data && data.length > 0) {
            data.forEach(function(item) {
                const row = $('<tr>');
                
                // Station
                row.append($('<td>').html(
                    item.station + (item.site_ref ? '<br><small style="color: #6c757d;">Ref: ' + item.site_ref + '</small>' : '')
                ));
                
                // Date and Time
                row.append($('<td>').text(item.date_time || 'N/A'));
                
                // Product - color code based on product type
                const productCell = $('<td>').text(item.product || 'N/A');
                if (item.product) {
                    const productLower = item.product.toLowerCase();
                    if (productLower.includes('gasoline') || productLower.includes('petrol')) {
                        productCell.css('color', '#007bff');
                    } else if (productLower.includes('diesel')) {
                        productCell.css('color', '#fd7e14');
                    } else {
                        productCell.css('color', '#28a745');
                    }
                }
                row.append(productCell);
                
                // Tank
                row.append($('<td>').text(item.tank || 'N/A'));
                
                // Product Volume
                row.append($('<td>').addClass('text-right').text(
                    parseFloat(item.product_volume || 0).toLocaleString('en-US', {minimumFractionDigits: 2, maximumFractionDigits: 2})
                ));
                
                // Current & Percentage with progress bar
                const percentage = parseFloat(item.tank_filling_percentage || 0);
                const percentageDisplay = $('<td>');
                const percentageDiv = $('<div>').addClass('percentage-display');
                
                const progressBar = $('<div>').addClass('tank-percentage-progress');
                const progressBarInner = $('<div>')
                    .addClass('tank-percentage-progress-bar ' + getProgressBarClass(item.status_class))
                    .css('width', percentage + '%');
                progressBar.append(progressBarInner);
                
                const percentageValue = $('<span>').addClass('percentage-value').text(
                    parseFloat(percentage).toFixed(0) + '%'
                );
                
                percentageDiv.append(progressBar);
                percentageDiv.append(percentageValue);
                percentageDisplay.append(percentageDiv);
                row.append(percentageDisplay);
                
                // Status with icon
                const statusCell = $('<td>');
                const statusText = $('<span>').addClass(getStatusClass(item.status_class)).text(item.status || 'N/A');
                
                // Add icon based on status
                let icon = '';
                if (item.status_class === 'critical') {
                    icon = '<i class="fas fa-exclamation-circle" style="color: #dc3545; margin-right: 5px;"></i>';
                } else if (item.status_class === 'low') {
                    icon = '<i class="fas fa-clock" style="color: #fd7e14; margin-right: 5px;"></i>';
                } else if (item.status_class === 'normal') {
                    icon = '<i class="fas fa-check-circle" style="color: #28a745; margin-right: 5px;"></i>';
                }
                
                statusCell.html(icon);
                statusCell.append(statusText);
                row.append(statusCell);
                
                tbody.append(row);
            });
        } else {
            tbody.append($('<tr>').append($('<td>').attr('colspan', 7).addClass('text-center').text('No data available')));
        }
    }

    // Load tank monitoring data
    function loadTankMonitoring() {
        const filters = {
            station_id: $('#tank_monitoring_station_id').val(),
            from_date: $('#tank_monitoring_from_date').val(),
            to_date: $('#tank_monitoring_to_date').val(),
            from_time: $('#tank_monitoring_from_time').val(),
            to_time: $('#tank_monitoring_to_time').val(),
            product_id: $('#tank_monitoring_product_id').val(),
            tank: $('#tank_monitoring_tank').val(),
            status: $('#tank_monitoring_status').val()
        };

        $.ajax({
            url: '{{ route('hos-reports.tank-monitoring') }}',
            method: 'GET',
            data: filters,
            success: function(response) {
                renderTankMonitoring(response.data || []);
            },
            error: function(xhr, error, thrown) {
                var errorMsg = 'Error loading tank monitoring data. Please try again.';
                if (xhr.responseJSON && xhr.responseJSON.message) {
                    errorMsg = 'Error: ' + xhr.responseJSON.message;
                }
                $('#tank-monitoring-tbody').html('<tr><td colspan="7" class="text-center text-danger">' + errorMsg + '</td></tr>');
            }
        });
    }

    // Apply filters button
    $('#tank-monitoring-filter-btn').on('click', function() {
        loadTankMonitoring();
    });

    // Reset filters button
    $('#tank-monitoring-reset-btn').on('click', function() {
        $('#tank_monitoring_station_id').val('');
        $('#tank_monitoring_from_date').val('');
        $('#tank_monitoring_to_date').val('');
        $('#tank_monitoring_from_time').val('');
        $('#tank_monitoring_to_time').val('');
        $('#tank_monitoring_product_id').val('');
        $('#tank_monitoring_tank').val('');
        $('#tank_monitoring_status').val('');
        loadTanks();
        loadTankMonitoring();
    });

    // Auto-filter on dropdown change
    $('#tank_monitoring_station_id, #tank_monitoring_product_id, #tank_monitoring_tank, #tank_monitoring_status').on('change', function() {
        if ($(this).attr('id') === 'tank_monitoring_station_id') {
            loadTanks();
        }
        loadTankMonitoring();
    });
});
</script>
@endpush
