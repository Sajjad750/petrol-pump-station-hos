<!-- Filters Card -->
<div class="card custom-card mb-3">
    <div class="card-header custom-card-header">
        <h5 class="mb-0" style="color: #D7D7D7;"><i class="fas fa-filter"></i> Filters</h5>
    </div>
    <div class="card-body">
        <form id="shift-summary-filter-form">
            <div class="row">
                <div class="col-md-2">
                    <div class="form-group">
                        <label for="shift_summary_station_id">Station</label>
                        <select class="form-control" id="shift_summary_station_id" name="station_id">
                            <option value="">All Stations</option>
                        </select>
                    </div>
                </div>
                <div class="col-md-2">
                    <div class="form-group">
                        <label for="shift_summary_from_date">From Date</label>
                        <input type="date" class="form-control" id="shift_summary_from_date" name="from_date">
                    </div>
                </div>
                <div class="col-md-2">
                    <div class="form-group">
                        <label for="shift_summary_to_date">To Date</label>
                        <input type="date" class="form-control" id="shift_summary_to_date" name="to_date">
                    </div>
                </div>
                <div class="col-md-2">
                    <div class="form-group">
                        <label for="shift_summary_from_time">From Time</label>
                        <input type="time" class="form-control" id="shift_summary_from_time" name="from_time">
                    </div>
                </div>
                <div class="col-md-2">
                    <div class="form-group">
                        <label for="shift_summary_to_time">To Time</label>
                        <input type="time" class="form-control" id="shift_summary_to_time" name="to_time">
                    </div>
                </div>
                <div class="col-md-2">
                    <div class="form-group">
                        <label for="shift_summary_view_mode">View Mode</label>
                        <select class="form-control" id="shift_summary_view_mode" name="view_mode">
                            <option value="summary">Show Summary</option>
                            <option value="individual">Select All</option>
                        </select>
                    </div>
                </div>
            </div>
            <div class="row">
                <div class="col-md-12 d-flex justify-content-end" style="gap: 10px;">
                    <button type="button" id="shift-summary-filter-btn" class="btn btn-dark">
                        <i class="fas fa-filter"></i> Search Filters
                    </button>
                    <button type="button" id="shift-summary-reset-btn" class="btn btn-dark">
                        <i class="fas fa-redo"></i> Reset
                    </button>
                </div>
            </div>
        </form>
    </div>
</div>

<!-- Combined Summary Section (shown when view_mode is 'summary') -->
<div id="combined-summary-section">
    <!-- Payment Mode Wise Summary Table -->
    <div class="card custom-card mb-3">
        <div class="card-header custom-card-header">
            <h4 class="mb-0" style="color: #D7D7D7;"><i class="fas fa-table"></i> Payment Mode Wise Summary</h4>
        </div>
    <div class="card-body" style="padding: 0;">
        <div class="table-responsive">
            <table id="payment-mode-summary-table" class="table">
                <thead>
                    <tr>
                        <th>MOP</th>
                        <th class="text-right">Volume (L)</th>
                        <th class="text-right">Amount (SAR)</th>
                    </tr>
                </thead>
                <tbody id="payment-mode-summary-tbody">
                    <tr>
                        <td colspan="3" class="text-center">No data available. Please apply filters.</td>
                    </tr>
                </tbody>
                <tfoot id="payment-mode-summary-tfoot" style="display: none;">
                    <tr style="background-color: #f5f5f5; font-weight: bold;">
                        <td>Total</td>
                        <td class="text-right" id="payment-mode-total-volume">0.00</td>
                        <td class="text-right" id="payment-mode-total-amount">0.00</td>
                    </tr>
                </tfoot>
            </table>
        </div>
    </div>
</div>

<!-- Product Wise Summary Table -->
<div class="card custom-card mb-3">
    <div class="card-header custom-card-header">
        <h4 class="mb-0" style="color: #D7D7D7;"><i class="fas fa-table"></i> Product Wise Summary</h4>
    </div>
    <div class="card-body" style="padding: 0;">
        <div class="table-responsive">
            <table id="product-summary-table" class="table">
                <thead>
                    <tr>
                        <th>Product</th>
                        <th class="text-right">TXN Volume</th>
                        <th class="text-right">Amount (SAR)</th>
                    </tr>
                </thead>
                <tbody id="product-summary-tbody">
                    <tr>
                        <td colspan="3" class="text-center">No data available. Please apply filters.</td>
                    </tr>
                </tbody>
                <tfoot id="product-summary-tfoot" style="display: none;">
                    <tr style="background-color: #f5f5f5; font-weight: bold;">
                        <td>Total</td>
                        <td class="text-right" id="product-total-volume">0.00</td>
                        <td class="text-right" id="product-total-amount">0.00</td>
                    </tr>
                </tfoot>
            </table>
        </div>
    </div>
</div>

<!-- Pump Wise Summary Table -->
<div class="card custom-card mb-3">
    <div class="card-header custom-card-header">
        <h4 class="mb-0" style="color: #D7D7D7;"><i class="fas fa-table"></i> Pump Wise Summary</h4>
    </div>
    <div class="card-body" style="padding: 0;">
        <div class="table-responsive">
            <table id="pump-summary-table" class="table">
                <thead>
                    <tr>
                        <th>Product</th>
                        <th class="text-center">Pump No</th>
                        <th class="text-center">Nozzle No</th>
                        <th class="text-right">Start Totalizer</th>
                        <th class="text-right">End Totalizer</th>
                        <th class="text-right">Totalizer Volume</th>
                        <th class="text-right">TXN Volume</th>
                        <th class="text-right">Amount (SAR)</th>
                    </tr>
                </thead>
                <tbody id="pump-summary-tbody">
                    <tr>
                        <td colspan="8" class="text-center">No data available. Please apply filters.</td>
                    </tr>
                </tbody>
                <tfoot id="pump-summary-tfoot" style="display: none;">
                    <tr style="background-color: #f5f5f5; font-weight: bold;">
                        <td colspan="5" class="text-right">Total</td>
                        <td class="text-right" id="pump-total-totalizer-volume">0.000</td>
                        <td class="text-right" id="pump-total-txn-volume">0.00</td>
                        <td class="text-right" id="pump-total-amount">0.00</td>
                    </tr>
                </tfoot>
            </table>
        </div>
    </div>
</div>

<!-- Individual Shifts Section (shown when view_mode is 'individual') -->
<div id="individual-shifts-section" style="display: none;" class="individual-shifts-container">
    <!-- Individual shifts will be dynamically inserted here -->
</div>

@push('js')
    <script>
        $(document).ready(function() {
            // Function to format date/time
            function formatDateTime(dateTimeStr) {
                if (!dateTimeStr) return '';
                try {
                    var date = new Date(dateTimeStr.replace(' ', 'T'));
                    if (isNaN(date.getTime())) return dateTimeStr;
                    var day = String(date.getDate()).padStart(2, '0');
                    var month = date.toLocaleString('en-US', { month: 'short' });
                    var year = date.getFullYear();
                    var hours = String(date.getHours()).padStart(2, '0');
                    var minutes = String(date.getMinutes()).padStart(2, '0');
                    var seconds = String(date.getSeconds()).padStart(2, '0');
                    return day + ' ' + month + ' ' + year + ' ' + hours + ':' + minutes + ':' + seconds;
                } catch (e) {
                    return dateTimeStr;
                }
            }

            // Function to render individual shift summaries
            function renderIndividualShift(shiftData, shiftIndex) {
                var shiftHtml = '<div class="shift-card mb-4" style="border: 2px solid #D7D7D7; border-radius: 8px; padding: 20px; background: white; box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);">';
                shiftHtml += '<h3 style="color: #333; margin-bottom: 20px; padding-bottom: 10px; border-bottom: 2px solid #D7D7D7; font-size: 20px;">';
                shiftHtml += '<i class="fas fa-calendar-alt"></i> Shift ' + (shiftIndex + 1) + ' (ID: #' + shiftData.shift_number + ')';
                shiftHtml += '</h3>';
                shiftHtml += '<div class="row mb-3">';
                shiftHtml += '<div class="col-md-6"><strong>Start Time:</strong> ' + formatDateTime(shiftData.start_time) + '</div>';
                shiftHtml += '<div class="col-md-6"><strong>End Time:</strong> ' + formatDateTime(shiftData.end_time) + '</div>';
                shiftHtml += '</div>';

                // Payment Mode Wise Summary
                shiftHtml += '<div class="card custom-card mb-3 mt-3">';
                shiftHtml += '<div class="card-header custom-card-header">';
                shiftHtml += '<h5 class="mb-0" style="color: #D7D7D7;">Payment Mode Wise Summary</h5>';
                shiftHtml += '</div>';
                shiftHtml += '<div class="card-body" style="padding: 0;">';
                shiftHtml += '<div class="table-responsive">';
                shiftHtml += '<table class="table">';
                shiftHtml += '<thead><tr><th>MOP</th><th class="text-right">Volume (L)</th><th class="text-right">Amount (SAR)</th></tr></thead>';
                shiftHtml += '<tbody>';
                if (shiftData.payment_mode_summary && shiftData.payment_mode_summary.length > 0) {
                    shiftData.payment_mode_summary.forEach(function(item) {
                        shiftHtml += '<tr>';
                        shiftHtml += '<td>' + (item.mop || 'N/A') + '</td>';
                        shiftHtml += '<td class="text-right">' + parseFloat(item.volume || 0).toLocaleString('en-US', {minimumFractionDigits: 2, maximumFractionDigits: 2}) + '</td>';
                        shiftHtml += '<td class="text-right">' + parseFloat(item.amount || 0).toLocaleString('en-US', {minimumFractionDigits: 2, maximumFractionDigits: 2}) + '</td>';
                        shiftHtml += '</tr>';
                    });
                    shiftHtml += '<tfoot style="background-color: #f5f5f5; font-weight: bold;"><tr>';
                    shiftHtml += '<td>Total</td>';
                    shiftHtml += '<td class="text-right">' + parseFloat(shiftData.total_payment_volume || 0).toLocaleString('en-US', {minimumFractionDigits: 2, maximumFractionDigits: 2}) + '</td>';
                    shiftHtml += '<td class="text-right">' + parseFloat(shiftData.total_payment_amount || 0).toLocaleString('en-US', {minimumFractionDigits: 2, maximumFractionDigits: 2}) + '</td>';
                    shiftHtml += '</tr></tfoot>';
                } else {
                    shiftHtml += '<tr><td colspan="3" class="text-center">No data available</td></tr>';
                }
                shiftHtml += '</tbody></table></div></div></div>';

                // Product Wise Summary
                shiftHtml += '<div class="card custom-card mb-3">';
                shiftHtml += '<div class="card-header custom-card-header">';
                shiftHtml += '<h5 class="mb-0" style="color: #D7D7D7;">Product Wise Summary</h5>';
                shiftHtml += '</div>';
                shiftHtml += '<div class="card-body" style="padding: 0;">';
                shiftHtml += '<div class="table-responsive">';
                shiftHtml += '<table class="table">';
                shiftHtml += '<thead><tr><th>Product</th><th class="text-right">TXN Volume</th><th class="text-right">Amount (SAR)</th></tr></thead>';
                shiftHtml += '<tbody>';
                if (shiftData.product_summary && shiftData.product_summary.length > 0) {
                    shiftData.product_summary.forEach(function(item) {
                        shiftHtml += '<tr>';
                        shiftHtml += '<td>' + (item.product || 'N/A') + '</td>';
                        shiftHtml += '<td class="text-right">' + parseFloat(item.txn_volume || 0).toLocaleString('en-US', {minimumFractionDigits: 2, maximumFractionDigits: 2}) + '</td>';
                        shiftHtml += '<td class="text-right">' + parseFloat(item.amount || 0).toLocaleString('en-US', {minimumFractionDigits: 2, maximumFractionDigits: 2}) + '</td>';
                        shiftHtml += '</tr>';
                    });
                    shiftHtml += '<tfoot style="background-color: #f5f5f5; font-weight: bold;"><tr>';
                    shiftHtml += '<td>Total</td>';
                    shiftHtml += '<td class="text-right">' + parseFloat(shiftData.total_product_volume || 0).toLocaleString('en-US', {minimumFractionDigits: 2, maximumFractionDigits: 2}) + '</td>';
                    shiftHtml += '<td class="text-right">' + parseFloat(shiftData.total_product_amount || 0).toLocaleString('en-US', {minimumFractionDigits: 2, maximumFractionDigits: 2}) + '</td>';
                    shiftHtml += '</tr></tfoot>';
                } else {
                    shiftHtml += '<tr><td colspan="3" class="text-center">No data available</td></tr>';
                }
                shiftHtml += '</tbody></table></div></div></div>';

                // Pump Wise Summary
                shiftHtml += '<div class="card custom-card mb-3">';
                shiftHtml += '<div class="card-header custom-card-header">';
                shiftHtml += '<h5 class="mb-0" style="color: #D7D7D7;">Pump Wise Summary</h5>';
                shiftHtml += '</div>';
                shiftHtml += '<div class="card-body" style="padding: 0;">';
                shiftHtml += '<div class="table-responsive">';
                shiftHtml += '<table class="table">';
                shiftHtml += '<thead><tr><th>Product</th><th class="text-center">Pump No</th><th class="text-center">Nozzle No</th><th class="text-right">Start Totalizer</th><th class="text-right">End Totalizer</th><th class="text-right">Totalizer Volume</th><th class="text-right">TXN Volume</th><th class="text-right">Amount (SAR)</th></tr></thead>';
                shiftHtml += '<tbody>';
                if (shiftData.pump_summary && shiftData.pump_summary.length > 0) {
                    var totalTotalizerVolume = 0;
                    shiftData.pump_summary.forEach(function(item) {
                        var totalizerVolume = parseFloat(item.totalizer_volume || 0);
                        totalTotalizerVolume += totalizerVolume;
                        shiftHtml += '<tr>';
                        shiftHtml += '<td>' + (item.product || 'N/A') + '</td>';
                        shiftHtml += '<td class="text-center">' + (item.pump_no || 'N/A') + '</td>';
                        shiftHtml += '<td class="text-center">' + (item.nozzle_no || 'N/A') + '</td>';
                        shiftHtml += '<td class="text-right">' + parseFloat(item.start_totalizer || 0).toLocaleString('en-US', {minimumFractionDigits: 3, maximumFractionDigits: 3}) + '</td>';
                        shiftHtml += '<td class="text-right">' + parseFloat(item.end_totalizer || 0).toLocaleString('en-US', {minimumFractionDigits: 3, maximumFractionDigits: 3}) + '</td>';
                        shiftHtml += '<td class="text-right">' + totalizerVolume.toLocaleString('en-US', {minimumFractionDigits: 3, maximumFractionDigits: 3}) + '</td>';
                        shiftHtml += '<td class="text-right">' + parseFloat(item.txn_volume || 0).toLocaleString('en-US', {minimumFractionDigits: 2, maximumFractionDigits: 2}) + '</td>';
                        shiftHtml += '<td class="text-right">' + parseFloat(item.amount || 0).toLocaleString('en-US', {minimumFractionDigits: 2, maximumFractionDigits: 2}) + '</td>';
                        shiftHtml += '</tr>';
                    });
                    shiftHtml += '<tfoot style="background-color: #f5f5f5; font-weight: bold;"><tr>';
                    shiftHtml += '<td colspan="5" class="text-right">Total</td>';
                    shiftHtml += '<td class="text-right">' + totalTotalizerVolume.toLocaleString('en-US', {minimumFractionDigits: 3, maximumFractionDigits: 3}) + '</td>';
                    shiftHtml += '<td class="text-right">' + parseFloat(shiftData.total_pump_txn_volume || 0).toLocaleString('en-US', {minimumFractionDigits: 2, maximumFractionDigits: 2}) + '</td>';
                    shiftHtml += '<td class="text-right">' + parseFloat(shiftData.total_pump_amount || 0).toLocaleString('en-US', {minimumFractionDigits: 2, maximumFractionDigits: 2}) + '</td>';
                    shiftHtml += '</tr></tfoot>';
                } else {
                    shiftHtml += '<tr><td colspan="8" class="text-center">No data available</td></tr>';
                }
                shiftHtml += '</tbody></table></div></div></div>';
                shiftHtml += '</div>';

                return shiftHtml;
            }

            // Load stations for dropdown
            $.ajax({
                url: '{{ route('hos-reports.stations') }}',
                method: 'GET',
                success: function(response) {
                    if (response.stations) {
                        response.stations.forEach(function(station) {
                            $('#shift_summary_station_id').append(
                                $('<option></option>').val(station.id).text(station.site_name)
                            );
                        });
                    }
                }
            });

            // Function to render combined Payment Mode Wise Summary
            function renderCombinedPaymentSummary(data, totalVolume, totalAmount) {
                const tbody = $('#payment-mode-summary-tbody');
                const tfoot = $('#payment-mode-summary-tfoot');
                tbody.empty();
                
                if (data && data.length > 0) {
                    data.forEach(function(item) {
                        const row = $('<tr>');
                        row.append($('<td>').text(item.mop || 'N/A'));
                        row.append($('<td>').addClass('text-right').text(parseFloat(item.volume || 0).toLocaleString('en-US', {minimumFractionDigits: 2, maximumFractionDigits: 2})));
                        row.append($('<td>').addClass('text-right').text(parseFloat(item.amount || 0).toLocaleString('en-US', {minimumFractionDigits: 2, maximumFractionDigits: 2})));
                        tbody.append(row);
                    });
                    tfoot.show();
                    $('#payment-mode-total-volume').text(parseFloat(totalVolume || 0).toLocaleString('en-US', {minimumFractionDigits: 2, maximumFractionDigits: 2}));
                    $('#payment-mode-total-amount').text(parseFloat(totalAmount || 0).toLocaleString('en-US', {minimumFractionDigits: 2, maximumFractionDigits: 2}));
                } else {
                    tbody.append($('<tr>').append($('<td>').attr('colspan', 3).addClass('text-center').text('No data available')));
                    tfoot.hide();
                }
            }

            // Function to render combined Product Wise Summary
            function renderCombinedProductSummary(data, totalVolume, totalAmount) {
                const tbody = $('#product-summary-tbody');
                const tfoot = $('#product-summary-tfoot');
                tbody.empty();
                
                if (data && data.length > 0) {
                    data.forEach(function(item) {
                        const row = $('<tr>');
                        row.append($('<td>').text(item.product || 'N/A'));
                        row.append($('<td>').addClass('text-right').text(parseFloat(item.txn_volume || 0).toLocaleString('en-US', {minimumFractionDigits: 2, maximumFractionDigits: 2})));
                        row.append($('<td>').addClass('text-right').text(parseFloat(item.amount || 0).toLocaleString('en-US', {minimumFractionDigits: 2, maximumFractionDigits: 2})));
                        tbody.append(row);
                    });
                    tfoot.show();
                    $('#product-total-volume').text(parseFloat(totalVolume || 0).toLocaleString('en-US', {minimumFractionDigits: 2, maximumFractionDigits: 2}));
                    $('#product-total-amount').text(parseFloat(totalAmount || 0).toLocaleString('en-US', {minimumFractionDigits: 2, maximumFractionDigits: 2}));
                } else {
                    tbody.append($('<tr>').append($('<td>').attr('colspan', 3).addClass('text-center').text('No data available')));
                    tfoot.hide();
                }
            }

            // Function to render combined Pump Wise Summary
            function renderCombinedPumpSummary(data, totalTotalizerVolume, totalTxnVolume, totalAmount) {
                const tbody = $('#pump-summary-tbody');
                const tfoot = $('#pump-summary-tfoot');
                tbody.empty();
                
                if (data && data.length > 0) {
                    data.forEach(function(item) {
                        const row = $('<tr>');
                        row.append($('<td>').text(item.product || 'N/A'));
                        row.append($('<td>').addClass('text-center').text(item.pump_no || 'N/A'));
                        row.append($('<td>').addClass('text-center').text(item.nozzle_no || 'N/A'));
                        row.append($('<td>').addClass('text-right').text(parseFloat(item.start_totalizer || 0).toLocaleString('en-US', {minimumFractionDigits: 3, maximumFractionDigits: 3})));
                        row.append($('<td>').addClass('text-right').text(parseFloat(item.end_totalizer || 0).toLocaleString('en-US', {minimumFractionDigits: 3, maximumFractionDigits: 3})));
                        row.append($('<td>').addClass('text-right').text(parseFloat(item.totalizer_volume || 0).toLocaleString('en-US', {minimumFractionDigits: 3, maximumFractionDigits: 3})));
                        row.append($('<td>').addClass('text-right').text(parseFloat(item.txn_volume || 0).toLocaleString('en-US', {minimumFractionDigits: 2, maximumFractionDigits: 2})));
                        row.append($('<td>').addClass('text-right').text(parseFloat(item.amount || 0).toLocaleString('en-US', {minimumFractionDigits: 2, maximumFractionDigits: 2})));
                        tbody.append(row);
                    });
                    tfoot.show();
                    $('#pump-total-totalizer-volume').text(parseFloat(totalTotalizerVolume || 0).toLocaleString('en-US', {minimumFractionDigits: 3, maximumFractionDigits: 3}));
                    $('#pump-total-txn-volume').text(parseFloat(totalTxnVolume || 0).toLocaleString('en-US', {minimumFractionDigits: 2, maximumFractionDigits: 2}));
                    $('#pump-total-amount').text(parseFloat(totalAmount || 0).toLocaleString('en-US', {minimumFractionDigits: 2, maximumFractionDigits: 2}));
                } else {
                    tbody.append($('<tr>').append($('<td>').attr('colspan', 8).addClass('text-center').text('No data available')));
                    tfoot.hide();
                }
            }

            // Load shift summary data
            function loadShiftSummary() {
                const filters = {
                    station_id: $('#shift_summary_station_id').val(),
                    from_date: $('#shift_summary_from_date').val(),
                    to_date: $('#shift_summary_to_date').val(),
                    from_time: $('#shift_summary_from_time').val(),
                    to_time: $('#shift_summary_to_time').val(),
                    view_mode: $('#shift_summary_view_mode').val()
                };

                $.ajax({
                    url: '{{ route('hos-reports.shift-summary') }}',
                    method: 'GET',
                    data: filters,
                    success: function(response) {
                        const viewMode = response.view_mode || filters.view_mode || 'individual';
                        
                        if (viewMode === 'summary') {
                            // Show combined summary section
                            $('#individual-shifts-section').hide().removeClass('show-individual-shifts');
                            $('#combined-summary-section').show();
                            
                            // Populate Payment Mode Wise Summary
                            renderCombinedPaymentSummary(response.payment_mode_summary || [], response.payment_mode_total_volume || 0, response.payment_mode_total_amount || 0);
                            
                            // Populate Product Wise Summary
                            renderCombinedProductSummary(response.product_summary || [], response.product_total_volume || 0, response.product_total_amount || 0);
                            
                            // Populate Pump Wise Summary
                            renderCombinedPumpSummary(response.pump_summary || [], response.pump_total_totalizer_volume || 0, response.pump_total_txn_volume || 0, response.pump_total_amount || 0);
                        } else {
                            // Show individual shifts section (each shift with its own 3 tables)
                            $('#combined-summary-section').hide();
                            
                            const individualSection = $('#individual-shifts-section');
                            individualSection.empty();
                            
                            // Force show the section by removing inline style and adding display block
                            individualSection.removeAttr('style');
                            individualSection.css({
                                'display': 'block',
                                'visibility': 'visible',
                                'opacity': '1'
                            });
                            individualSection.show().addClass('show-individual-shifts');
                            
                            // Also ensure parent containers are visible
                            individualSection.parent().show().css('display', 'block');
                            
                            if (response.individual_shifts && Array.isArray(response.individual_shifts) && response.individual_shifts.length > 0) {
                                var shiftCount = response.individual_shifts.length;
                                var headerHtml = '<div class="alert alert-info mb-4"><strong><i class="fas fa-list"></i> ' + shiftCount + ' shift(s) found</strong><br>Each shift is displayed separately below with its own <strong>Payment Mode Wise Summary</strong>, <strong>Product Wise Summary</strong>, and <strong>Pump Wise Summary</strong> tables.</div>';
                                individualSection.append(headerHtml);
                                
                                var allShiftHtml = '';
                                
                                response.individual_shifts.forEach(function(shiftData, index) {
                                    try {
                                        var shiftHtml = renderIndividualShift(shiftData, index);
                                        allShiftHtml += shiftHtml;
                                    } catch (e) {
                                        allShiftHtml += '<div class="alert alert-danger mb-3">Error rendering shift ' + (index + 1) + ': ' + e.message + '</div>';
                                    }
                                });
                                
                                // Append all shifts at once
                                if (allShiftHtml) {
                                    individualSection.append(allShiftHtml);
                                }
                                
                                // Force show and make visible - use timeout to ensure DOM is updated
                                setTimeout(function() {
                                    // Remove inline style first
                                    individualSection.removeAttr('style');
                                    
                                    // Apply styles via jQuery
                                    individualSection.css({
                                        'display': 'block',
                                        'visibility': 'visible',
                                        'opacity': '1',
                                        'height': 'auto',
                                        'min-height': '50px'
                                    });
                                    
                                    // Also add an important class to override any other CSS
                                    individualSection.addClass('show-individual-shifts');
                                    
                                    // Trigger a reflow to force browser to recalculate
                                    if (individualSection[0]) {
                                        individualSection[0].offsetHeight;
                                    }
                                    
                                    // Scroll to results
                                    if (individualSection.length && individualSection.offset()) {
                                        $('html, body').animate({
                                            scrollTop: individualSection.offset().top - 100
                                        }, 500);
                                    }
                                }, 50);
                            } else {
                                var errorMsg = 'No shifts found matching your filters. Please try different date/time range.';
                                if (response.individual_shifts === undefined) {
                                    errorMsg = 'Response does not contain individual_shifts data.';
                                } else if (!Array.isArray(response.individual_shifts)) {
                                    errorMsg = 'individual_shifts is not an array. Type: ' + typeof response.individual_shifts;
                                } else if (response.individual_shifts.length === 0) {
                                    errorMsg = 'No shifts found matching your filters. Please try different date/time range.';
                                }
                                individualSection.html('<div class="alert alert-warning text-center">' + errorMsg + '</div>');
                            }
                        }
                    },
                    error: function(xhr, error, thrown) {
                        var errorMsg = 'Error loading shift summary. Please try again.';
                        if (xhr.responseJSON && xhr.responseJSON.message) {
                            errorMsg = 'Error: ' + xhr.responseJSON.message;
                        }
                        $('#individual-shifts-section, #combined-summary-section').html('<div class="alert alert-danger text-center">' + errorMsg + '</div>');
                    }
                });
            }

            // Apply filters button
            $('#shift-summary-filter-btn').on('click', function() {
                loadShiftSummary();
            });

            // Reset filters button
            $('#shift-summary-reset-btn').on('click', function() {
                $('#shift_summary_station_id').val('');
                $('#shift_summary_from_date').val('');
                $('#shift_summary_to_date').val('');
                $('#shift_summary_from_time').val('');
                $('#shift_summary_to_time').val('');
                $('#shift_summary_view_mode').val('summary');
                loadShiftSummary();
            });

            // Auto-filter on dropdown change
            $('#shift_summary_station_id, #shift_summary_view_mode').on('change', function() {
                loadShiftSummary();
            });
        });
    </script>
@endpush

