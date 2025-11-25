<!-- Filters Card -->
<div class="card custom-card mb-3">
    <div class="card-header custom-card-header">
        <h6 class="mb-0" style="color: #D7D7D7;"><i class="fas fa-filter"></i> Filters</h6>
    </div>
    <div class="card-body">
        <form id="sales-summary-filter-form">
            <div class="row">
                <div class="col-md-2">
                    <div class="form-group">
                        <label for="sales_summary_station_id">Station</label>
                        <select class="form-control" id="sales_summary_station_id" name="station_id">
                            <option value="">All Stations</option>
                        </select>
                    </div>
                </div>
                <div class="col-md-2">
                    <div class="form-group">
                        <label for="sales_summary_from_date">From Date</label>
                        <input type="date" class="form-control" id="sales_summary_from_date" name="from_date">
                    </div>
                </div>
                <div class="col-md-2">
                    <div class="form-group">
                        <label for="sales_summary_to_date">To Date</label>
                        <input type="date" class="form-control" id="sales_summary_to_date" name="to_date">
                    </div>
                </div>
                <div class="col-md-2">
                    <div class="form-group">
                        <label for="sales_summary_from_time">From Time</label>
                        <input type="time" class="form-control" id="sales_summary_from_time" name="from_time">
                    </div>
                </div>
                <div class="col-md-2">
                    <div class="form-group">
                        <label for="sales_summary_to_time">To Time</label>
                        <input type="time" class="form-control" id="sales_summary_to_time" name="to_time">
                    </div>
                </div>
                <div class="col-md-2">
                    <div class="form-group">
                        <label for="sales_summary_product_id">Product</label>
                        <select class="form-control" id="sales_summary_product_id" name="product_id">
                            <option value="">All Products</option>
                        </select>
                    </div>
                </div>
            </div>
            <div class="row">
                <div class="col-md-12 d-flex justify-content-end" style="gap: 10px;">
                    <button type="button" id="sales-summary-filter-btn" class="btn btn-dark">
                        <i class="fas fa-filter"></i> Search Filters
                    </button>
                    <button type="button" id="sales-summary-reset-btn" class="btn btn-dark">
                        <i class="fas fa-redo"></i> Reset
                    </button>
                </div>
            </div>
        </form>
    </div>
</div>

<!-- Sales Type Wise Summary Table -->
<div class="card custom-card mb-3">
    <div class="card-header custom-card-header">
        <h6 class="mb-0" style="color: #D7D7D7;"><i class="fas fa-table"></i> Sales Type Wise Summary</h6>
    </div>
    <div class="card-body" style="padding: 0;">
        <div class="table-responsive">
            <table id="sales-type-summary-table" class="table">
                <thead>
                    <tr>
                        <th>Sales Type</th>
                        <th class="text-right">Volume (L)</th>
                        <th class="text-right">Total Amount (SAR)</th>
                        <th class="text-right">Sales Count</th>
                    </tr>
                </thead>
                <tbody id="sales-type-summary-tbody">
                    <tr>
                        <td colspan="4" class="text-center">No data available. Please apply filters.</td>
                    </tr>
                </tbody>
                <tfoot id="sales-type-summary-tfoot" style="display: none;">
                    <tr style="background-color: #f5f5f5; font-weight: bold;">
                        <td>Total</td>
                        <td class="text-right" id="sales-type-total-volume">0.00</td>
                        <td class="text-right" id="sales-type-total-amount">0.00</td>
                        <td class="text-right" id="sales-type-total-count">0</td>
                    </tr>
                </tfoot>
            </table>
        </div>
    </div>
</div>

<!-- Product Wise Summary Table -->
<div class="card custom-card mb-3">
    <div class="card-header custom-card-header">
        <h6 class="mb-0" style="color: #D7D7D7;"><i class="fas fa-table"></i> Product Wise Summary</h6>
    </div>
    <div class="card-body" style="padding: 0;">
        <div class="table-responsive">
            <table id="product-summary-table" class="table">
                <thead>
                    <tr>
                        <th>Product Name</th>
                        <th class="text-right">Avg per Unit (SAR/L)</th>
                        <th class="text-right">Volume (L)</th>
                        <th class="text-right">Total Amount (SAR)</th>
                        <th class="text-right">No. of Sales</th>
                        <th class="text-right">Avg Sales Amount (SAR)</th>
                    </tr>
                </thead>
                <tbody id="product-summary-tbody">
                    <tr>
                        <td colspan="6" class="text-center">No data available. Please apply filters.</td>
                    </tr>
                </tbody>
                <tfoot id="product-summary-tfoot" style="display: none;">
                    <tr style="background-color: #f5f5f5; font-weight: bold;">
                        <td>Total</td>
                        <td class="text-right" id="product-total-avg-per-unit">0.00</td>
                        <td class="text-right" id="product-total-volume">0.00</td>
                        <td class="text-right" id="product-total-amount">0.00</td>
                        <td class="text-right" id="product-total-count">0</td>
                        <td class="text-right" id="product-total-avg-sales-amount">0.00</td>
                    </tr>
                </tfoot>
            </table>
        </div>
    </div>
</div>

<!-- Attendant Wise Summary Table -->
<div class="card custom-card mb-3">
    <div class="card-header custom-card-header">
        <h6 class="mb-0" style="color: #D7D7D7;"><i class="fas fa-table"></i> Attendant Wise Summary</h6>
    </div>
    <div class="card-body" style="padding: 0;">
        <div class="table-responsive">
            <table id="attendant-summary-table" class="table">
                <thead>
                    <tr>
                        <th>Attendant Name</th>
                        <th class="text-right">Volume (L)</th>
                        <th class="text-right">Total Amount (SAR)</th>
                        <th class="text-right">Total Transactions</th>
                    </tr>
                </thead>
                <tbody id="attendant-summary-tbody">
                    <tr>
                        <td colspan="4" class="text-center">No data available. Please apply filters.</td>
                    </tr>
                </tbody>
                <tfoot id="attendant-summary-tfoot" style="display: none;">
                    <tr style="background-color: #f5f5f5; font-weight: bold;">
                        <td>Total</td>
                        <td class="text-right" id="attendant-total-volume">0.00</td>
                        <td class="text-right" id="attendant-total-amount">0.00</td>
                        <td class="text-right" id="attendant-total-transactions">0</td>
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
                var $stationSelect = $('#sales_summary_station_id');

                // Remove previously added station options to prevent duplicates
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
                    $('#sales_summary_product_id').append(
                        $('<option></option>').val(fuelGrade.id).text(fuelGrade.name)
                    );
                });
            }
        }
    });

    // Function to render Sales Type Wise Summary
    function renderSalesTypeSummary(data, totalVolume, totalAmount, totalCount) {
        const tbody = $('#sales-type-summary-tbody');
        const tfoot = $('#sales-type-summary-tfoot');
        tbody.empty();
        
        if (data && data.length > 0) {
            data.forEach(function(item) {
                const row = $('<tr>');
                row.append($('<td>').text(item.sales_type || 'N/A'));
                row.append($('<td>').addClass('text-right').text(parseFloat(item.volume || 0).toLocaleString('en-US', {minimumFractionDigits: 2, maximumFractionDigits: 2})));
                row.append($('<td>').addClass('text-right').text(parseFloat(item.total_amount || 0).toLocaleString('en-US', {minimumFractionDigits: 2, maximumFractionDigits: 2})));
                row.append($('<td>').addClass('text-right').text(parseInt(item.sales_count || 0).toLocaleString()));
                tbody.append(row);
            });
            tfoot.show();
            $('#sales-type-total-volume').text(parseFloat(totalVolume || 0).toLocaleString('en-US', {minimumFractionDigits: 2, maximumFractionDigits: 2}));
            $('#sales-type-total-amount').text(parseFloat(totalAmount || 0).toLocaleString('en-US', {minimumFractionDigits: 2, maximumFractionDigits: 2}));
            $('#sales-type-total-count').text(parseInt(totalCount || 0).toLocaleString());
        } else {
            tbody.append($('<tr>').append($('<td>').attr('colspan', 4).addClass('text-center').text('No data available')));
            tfoot.hide();
        }
    }

    // Function to render Product Wise Summary
    function renderProductSummary(data, totalVolume, totalAmount, totalCount) {
        const tbody = $('#product-summary-tbody');
        const tfoot = $('#product-summary-tfoot');
        tbody.empty();
        
        if (data && data.length > 0) {
            data.forEach(function(item) {
                const row = $('<tr>');
                row.append($('<td>').text(item.product_name || 'N/A'));
                row.append($('<td>').addClass('text-right').text(parseFloat(item.avg_per_unit || 0).toLocaleString('en-US', {minimumFractionDigits: 2, maximumFractionDigits: 2})));
                row.append($('<td>').addClass('text-right').text(parseFloat(item.volume || 0).toLocaleString('en-US', {minimumFractionDigits: 2, maximumFractionDigits: 2})));
                row.append($('<td>').addClass('text-right').text(parseFloat(item.total_amount || 0).toLocaleString('en-US', {minimumFractionDigits: 2, maximumFractionDigits: 2})));
                row.append($('<td>').addClass('text-right').text(parseInt(item.sales_count || 0).toLocaleString()));
                row.append($('<td>').addClass('text-right').text(parseFloat(item.avg_sales_amount || 0).toLocaleString('en-US', {minimumFractionDigits: 2, maximumFractionDigits: 2})));
                tbody.append(row);
            });
            tfoot.show();
            
            // Calculate overall averages
            const overallAvgPerUnit = totalVolume > 0 ? totalAmount / totalVolume : 0;
            const overallAvgSalesAmount = totalCount > 0 ? totalAmount / totalCount : 0;
            
            $('#product-total-avg-per-unit').text(parseFloat(overallAvgPerUnit).toLocaleString('en-US', {minimumFractionDigits: 2, maximumFractionDigits: 2}));
            $('#product-total-volume').text(parseFloat(totalVolume || 0).toLocaleString('en-US', {minimumFractionDigits: 2, maximumFractionDigits: 2}));
            $('#product-total-amount').text(parseFloat(totalAmount || 0).toLocaleString('en-US', {minimumFractionDigits: 2, maximumFractionDigits: 2}));
            $('#product-total-count').text(parseInt(totalCount || 0).toLocaleString());
            $('#product-total-avg-sales-amount').text(parseFloat(overallAvgSalesAmount).toLocaleString('en-US', {minimumFractionDigits: 2, maximumFractionDigits: 2}));
        } else {
            tbody.append($('<tr>').append($('<td>').attr('colspan', 6).addClass('text-center').text('No data available')));
            tfoot.hide();
        }
    }

    // Function to render Attendant Wise Summary
    function renderAttendantSummary(data, totalVolume, totalAmount, totalTransactions) {
        const tbody = $('#attendant-summary-tbody');
        const tfoot = $('#attendant-summary-tfoot');
        tbody.empty();
        
        if (data && data.length > 0) {
            data.forEach(function(item) {
                const row = $('<tr>');
                row.append($('<td>').text(item.attendant_name || 'N/A'));
                row.append($('<td>').addClass('text-right').text(parseFloat(item.volume || 0).toLocaleString('en-US', {minimumFractionDigits: 2, maximumFractionDigits: 2})));
                row.append($('<td>').addClass('text-right').text(parseFloat(item.total_amount || 0).toLocaleString('en-US', {minimumFractionDigits: 2, maximumFractionDigits: 2})));
                row.append($('<td>').addClass('text-right').text(parseInt(item.transactions_count || 0).toLocaleString()));
                tbody.append(row);
            });
            tfoot.show();
            $('#attendant-total-volume').text(parseFloat(totalVolume || 0).toLocaleString('en-US', {minimumFractionDigits: 2, maximumFractionDigits: 2}));
            $('#attendant-total-amount').text(parseFloat(totalAmount || 0).toLocaleString('en-US', {minimumFractionDigits: 2, maximumFractionDigits: 2}));
            $('#attendant-total-transactions').text(parseInt(totalTransactions || 0).toLocaleString());
        } else {
            tbody.append($('<tr>').append($('<td>').attr('colspan', 4).addClass('text-center').text('No data available')));
            tfoot.hide();
        }
    }

    // Load sales summary data
    function loadSalesSummary() {
        const filters = {
            station_id: $('#sales_summary_station_id').val(),
            from_date: $('#sales_summary_from_date').val(),
            to_date: $('#sales_summary_to_date').val(),
            from_time: $('#sales_summary_from_time').val(),
            to_time: $('#sales_summary_to_time').val(),
            product_id: $('#sales_summary_product_id').val()
        };

        $.ajax({
            url: "{{ route('hos-reports.sales-summary') }}",
            method: 'GET',
            data: filters,
            success: function(response) {
                // Populate Sales Type Wise Summary
                renderSalesTypeSummary(
                    response.sales_type_summary || [], 
                    response.total_volume || 0, 
                    response.total_amount || 0, 
                    response.total_sales_count || 0
                );
                
                // Populate Product Wise Summary
                renderProductSummary(
                    response.product_summary || [], 
                    response.total_volume || 0, 
                    response.total_amount || 0, 
                    response.total_sales_count || 0
                );
                
                // Populate Attendant Wise Summary
                renderAttendantSummary(
                    response.attendant_summary || [], 
                    response.total_volume || 0, 
                    response.total_amount || 0, 
                    response.total_sales_count || 0
                );
            },
            error: function(xhr, error, thrown) {
                var errorMsg = 'Error loading sales summary. Please try again.';
                if (xhr.responseJSON && xhr.responseJSON.message) {
                    errorMsg = 'Error: ' + xhr.responseJSON.message;
                }
                $('#sales-type-summary-tbody, #product-summary-tbody, #attendant-summary-tbody').html('<tr><td colspan="6" class="text-center text-danger">' + errorMsg + '</td></tr>');
            }
        });
    }

    // Apply filters button
    $('#sales-summary-filter-btn').on('click', function() {
        loadSalesSummary();
    });

    // Reset filters button
    $('#sales-summary-reset-btn').on('click', function() {
        $('#sales_summary_station_id').val('');
        $('#sales_summary_from_date').val('');
        $('#sales_summary_to_date').val('');
        $('#sales_summary_from_time').val('');
        $('#sales_summary_to_time').val('');
        $('#sales_summary_product_id').val('');
        loadSalesSummary();
    });

    // Auto-filter on dropdown change
    $('#sales_summary_station_id, #sales_summary_product_id').on('change', function() {
        loadSalesSummary();
    });
});
</script>
@endpush
