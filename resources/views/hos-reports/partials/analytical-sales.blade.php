<!-- Filters Card -->
<div class="card custom-card mb-3">
    <div class="card-header custom-card-header">
        <h6 class="mb-0" style="color: #D7D7D7;"><i class="fas fa-filter"></i> Filters</h6>
    </div>
    <div class="card-body">
        <form id="analytical-sales-filter-form">
            <div class="row">
                <div class="col-md-2">
                    <div class="form-group">
                        <label for="analytical_sales_station_id">Station</label>
                        <select class="form-control" id="analytical_sales_station_id" name="station_id">
                            <option value="">All Stations</option>
                        </select>
                    </div>
                </div>
                <div class="col-md-2">
                    <div class="form-group">
                        <label for="analytical_sales_from_date">From Date</label>
                        <input type="date" class="form-control" id="analytical_sales_from_date" name="from_date">
                    </div>
                </div>
                <div class="col-md-2">
                    <div class="form-group">
                        <label for="analytical_sales_to_date">To Date</label>
                        <input type="date" class="form-control" id="analytical_sales_to_date" name="to_date">
                    </div>
                </div>
                <div class="col-md-2">
                    <div class="form-group">
                        <label for="analytical_sales_from_time">From Time</label>
                        <input type="time" class="form-control" id="analytical_sales_from_time" name="from_time">
                    </div>
                </div>
                <div class="col-md-2">
                    <div class="form-group">
                        <label for="analytical_sales_to_time">To Time</label>
                        <input type="time" class="form-control" id="analytical_sales_to_time" name="to_time">
                    </div>
                </div>
                <div class="col-md-2">
                    <div class="form-group">
                        <label for="analytical_sales_product_id">Product</label>
                        <select class="form-control" id="analytical_sales_product_id" name="product_id">
                            <option value="">All Products</option>
                        </select>
                    </div>
                </div>
            </div>
            <div class="row">
                <div class="col-md-12 d-flex justify-content-end" style="gap: 10px;">
                    <button type="button" id="analytical-sales-filter-btn" class="btn btn-dark">
                        <i class="fas fa-filter"></i> Search Filters
                    </button>
                    <button type="button" id="analytical-sales-reset-btn" class="btn btn-dark">
                        <i class="fas fa-redo"></i> Reset
                    </button>
                </div>
            </div>
        </form>
    </div>
</div>

<!-- Analytical Sales Data Table -->
<div class="card custom-card mb-3">
    <div class="card-header custom-card-header">
        <h6 class="mb-0" style="color: #D7D7D7;"><i class="fas fa-table"></i> Analytical Sales Data</h6>
    </div>
    <div class="card-body" style="padding: 0;">
        <div class="table-responsive">
            <table id="analytical-sales-table" class="table">
                <thead>
                    <tr>
                        <th>Date</th>
                        <th>Site</th>
                        <th>Product</th>
                        <th class="text-right">Liters Sold</th>
                        <th class="text-right">Amount (SAR)</th>
                        <th class="text-right">Transactions</th>
                        <th class="text-right">Avg Transaction (SAR)</th>
                    </tr>
                </thead>
                <tbody id="analytical-sales-tbody">
                    <tr>
                        <td colspan="7" class="text-center">No data available. Please apply filters.</td>
                    </tr>
                </tbody>
                <tfoot id="analytical-sales-tfoot" style="display: none;">
                    <tr style="background-color: #f5f5f5; font-weight: bold;">
                        <td colspan="3">Total</td>
                        <td class="text-right" id="analytical-total-liters">0.00</td>
                        <td class="text-right" id="analytical-total-amount">0.00</td>
                        <td class="text-right" id="analytical-total-transactions">0</td>
                        <td class="text-right" id="analytical-overall-avg-transaction">0.00</td>
                    </tr>
                </tfoot>
            </table>
        </div>
    </div>
</div>

@push('js')
<script>
$(document).ready(function() {
    // Load stations for dropdown
    $.ajax({
        url: "{{ route('hos-reports.stations') }}",
        method: 'GET',
        success: function(response) {
            if (response.stations) {
                var $stationSelect = $('#analytical_sales_station_id');

                // Remove previously added station options to avoid duplicates
                $stationSelect.find('option:not(:first)').remove();

                response.stations.forEach(function(station) {
                    $stationSelect.append(
                        $('<option></option>').val(station.id).text(station.site_name)
                    );
                });
            }
        }
    });

    // Load fuel grades for dropdown
    $.ajax({
        url: "{{ route('hos-reports.fuel-grades') }}",
        method: 'GET',
        success: function(response) {
            if (response.fuel_grades) {
                response.fuel_grades.forEach(function(fuelGrade) {
                    $('#analytical_sales_product_id').append(
                        $('<option></option>').val(fuelGrade.id).text(fuelGrade.name)
                    );
                });
            }
        }
    });

    // Function to render Analytical Sales Data
    function renderAnalyticalSales(data, totalLiters, totalAmount, totalTransactions, overallAvgTransaction) {
        const tbody = $('#analytical-sales-tbody');
        const tfoot = $('#analytical-sales-tfoot');
        tbody.empty();
        
        if (data && data.length > 0) {
            data.forEach(function(item) {
                const row = $('<tr>');
                row.append($('<td>').text(item.date || 'N/A'));
                row.append($('<td>').text(item.site || 'N/A'));
                row.append($('<td>').text(item.product || 'N/A'));
                row.append($('<td>').addClass('text-right').text(parseFloat(item.liters_sold || 0).toLocaleString('en-US', {minimumFractionDigits: 2, maximumFractionDigits: 2})));
                row.append($('<td>').addClass('text-right').text(parseFloat(item.amount || 0).toLocaleString('en-US', {minimumFractionDigits: 2, maximumFractionDigits: 2})));
                row.append($('<td>').addClass('text-right').text(parseInt(item.transactions || 0).toLocaleString()));
                row.append($('<td>').addClass('text-right').text(parseFloat(item.avg_transaction_amount || 0).toLocaleString('en-US', {minimumFractionDigits: 2, maximumFractionDigits: 2})));
                tbody.append(row);
            });
            tfoot.show();
            $('#analytical-total-liters').text(parseFloat(totalLiters || 0).toLocaleString('en-US', {minimumFractionDigits: 2, maximumFractionDigits: 2}));
            $('#analytical-total-amount').text(parseFloat(totalAmount || 0).toLocaleString('en-US', {minimumFractionDigits: 2, maximumFractionDigits: 2}));
            $('#analytical-total-transactions').text(parseInt(totalTransactions || 0).toLocaleString());
            $('#analytical-overall-avg-transaction').text(parseFloat(overallAvgTransaction || 0).toLocaleString('en-US', {minimumFractionDigits: 2, maximumFractionDigits: 2}));
        } else {
            tbody.append($('<tr>').append($('<td>').attr('colspan', 7).addClass('text-center').text('No data available')));
            tfoot.hide();
        }
    }

    // Load analytical sales data
    function loadAnalyticalSales() {
        const filters = {
            station_id: $('#analytical_sales_station_id').val(),
            from_date: $('#analytical_sales_from_date').val(),
            to_date: $('#analytical_sales_to_date').val(),
            from_time: $('#analytical_sales_from_time').val(),
            to_time: $('#analytical_sales_to_time').val(),
            product_id: $('#analytical_sales_product_id').val()
        };

        $.ajax({
            url: "{{ route('hos-reports.analytical-sales') }}",
            method: 'GET',
            data: filters,
            success: function(response) {
                // Populate Analytical Sales Data
                renderAnalyticalSales(
                    response.data || [], 
                    response.total_liters || 0, 
                    response.total_amount || 0, 
                    response.total_transactions || 0,
                    response.overall_avg_transaction || 0
                );
            },
            error: function(xhr, error, thrown) {
                var errorMsg = 'Error loading analytical sales data. Please try again.';
                if (xhr.responseJSON && xhr.responseJSON.message) {
                    errorMsg = 'Error: ' + xhr.responseJSON.message;
                }
                $('#analytical-sales-tbody').html('<tr><td colspan="7" class="text-center text-danger">' + errorMsg + '</td></tr>');
            }
        });
    }

    // Apply filters button
    $('#analytical-sales-filter-btn').on('click', function() {
        loadAnalyticalSales();
    });

    // Reset filters button
    $('#analytical-sales-reset-btn').on('click', function() {
        $('#analytical_sales_station_id').val('');
        $('#analytical_sales_from_date').val('');
        $('#analytical_sales_to_date').val('');
        $('#analytical_sales_from_time').val('');
        $('#analytical_sales_to_time').val('');
        $('#analytical_sales_product_id').val('');
        loadAnalyticalSales();
    });

    // Auto-filter on dropdown change
    $('#analytical_sales_station_id, #analytical_sales_product_id').on('change', function() {
        loadAnalyticalSales();
    });
});
</script>
@endpush
