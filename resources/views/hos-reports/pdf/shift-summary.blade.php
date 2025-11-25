<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Shift Summary Report</title>
    <style>
        body { font-family: 'Inter', Arial, sans-serif; font-size: 11px; color: #1b1f3b; margin: 0; padding: 24px; }
        h1, h2, h3, h4 { font-family: 'DM Sans', Arial, sans-serif; margin: 0 0 10px; color: #1f2a72; }
        h1 { font-size: 24px; }
        h2 { font-size: 18px; margin-top: 25px; }
        h3 { font-size: 16px; margin-top: 20px; }
        h4 { font-size: 14px; margin-top: 15px; }
        .header { border-bottom: 3px solidrgb(0, 0, 0); padding-bottom: 10px; margin-bottom: 20px; }
        .meta { margin-bottom: 6px; color: #4b5563; }
        .filters, .shift-card { border: 1px solid #d1d5db; border-radius: 8px; padding: 15px; margin-bottom: 18px; }
        .filters { background-color:rgb(0, 0, 0); }
        .filter-item { display: inline-block; margin-right: 18px; margin-bottom: 6px; }
        .filter-label { font-weight: 600; color: #111827; }
        table { width: 100%; border-collapse: collapse; margin-top: 10px; font-size: 10px; }
        th { background-color:rgb(0, 0, 0); color: #fff; padding: 6px 5px; text-align: left; }
        td { padding: 5px; border-bottom: 1px solid #e5e7eb; }
        tr:nth-child(even) { background-color: #f9fafb; }
        .totals-row { background-color: #eef2ff; font-weight: 600; }
        .empty-state { text-align: center; font-style: italic; padding: 12px; color: #6b7280; }
        .shift-card { background-color: #fff; }
        .shift-header { display: flex; justify-content: space-between; flex-wrap: wrap; margin-bottom: 8px; font-weight: 600; }
    </style>
</head>
<body>
    @php
        $format = fn ($value, $decimals = 2) => number_format((float) $value, $decimals);
    @endphp

    <div class="header">
        <h1>Shift Summary Report</h1>
        <div class="meta">Generated on {{ $generatedAt->format('d M Y H:i:s') }} | Mode: {{ $viewModeLabel }}</div>
        @if(!empty($shiftsMeta))
            <div class="meta">Shifts included:
                {{ collect($shiftsMeta)->pluck('bos_shift_id')->filter()->implode(', ') ?: 'N/A' }}
            </div>
        @endif
    </div>

    @if(!empty($filters))
        <div class="filters">
            <h3>Applied Filters</h3>
            @foreach($filters as $label => $value)
                <span class="filter-item"><span class="filter-label">{{ $label }}:</span> {{ $value }}</span>
            @endforeach
        </div>
    @endif

    @if($viewMode === 'summary')
        <h2>Combined Payment Mode Summary</h2>
        <table>
            <thead>
                <tr>
                    <th style="width: 40%;">Mode of Payment</th>
                    <th style="width: 30%;">Volume (L)</th>
                    <th style="width: 30%;">Amount (SAR)</th>
                </tr>
            </thead>
            <tbody>
                @forelse($combinedPaymentSummary as $row)
                    <tr>
                        <td>{{ $row['mop'] ?? 'N/A' }}</td>
                        <td>{{ $format($row['volume'] ?? 0) }}</td>
                        <td>{{ $format($row['amount'] ?? 0) }}</td>
                    </tr>
                @empty
                    <tr><td colspan="3" class="empty-state">No payment data for the selected filters.</td></tr>
                @endforelse
            </tbody>
            <tfoot>
                <tr class="totals-row">
                    <td>Total</td>
                    <td>{{ $format($combinedTotals['payment_volume'] ?? 0) }}</td>
                    <td>{{ $format($combinedTotals['payment_amount'] ?? 0) }}</td>
                </tr>
            </tfoot>
        </table>

        <h2>Combined Product Wise Summary</h2>
        <table>
            <thead>
                <tr>
                    <th style="width: 40%;">Product</th>
                    <th style="width: 30%;">TXN Volume (L)</th>
                    <th style="width: 30%;">Amount (SAR)</th>
                </tr>
            </thead>
            <tbody>
                @forelse($combinedProductSummary as $row)
                    <tr>
                        <td>{{ $row['product'] ?? 'N/A' }}</td>
                        <td>{{ $format($row['txn_volume'] ?? 0) }}</td>
                        <td>{{ $format($row['amount'] ?? 0) }}</td>
                    </tr>
                @empty
                    <tr><td colspan="3" class="empty-state">No product data for the selected filters.</td></tr>
                @endforelse
            </tbody>
            <tfoot>
                <tr class="totals-row">
                    <td>Total</td>
                    <td>{{ $format($combinedTotals['product_volume'] ?? 0) }}</td>
                    <td>{{ $format($combinedTotals['product_amount'] ?? 0) }}</td>
                </tr>
            </tfoot>
        </table>

        <h2>Combined Pump Wise Summary</h2>
        <table>
            <thead>
                <tr>
                    <th>Product</th>
                    <th>Pump</th>
                    <th>Nozzle</th>
                    <th>Start Tot.</th>
                    <th>End Tot.</th>
                    <th>Totalizer Vol.</th>
                    <th>TXN Volume</th>
                    <th>Amount (SAR)</th>
                </tr>
            </thead>
            <tbody>
                @forelse($combinedPumpSummary as $row)
                    <tr>
                        <td>{{ $row['product'] ?? 'N/A' }}</td>
                        <td>{{ $row['pump_no'] ?? '-' }}</td>
                        <td>{{ $row['nozzle_no'] ?? '-' }}</td>
                        <td>{{ $format($row['start_totalizer'] ?? 0, 3) }}</td>
                        <td>{{ $format($row['end_totalizer'] ?? 0, 3) }}</td>
                        <td>{{ $format($row['totalizer_volume'] ?? 0, 3) }}</td>
                        <td>{{ $format($row['txn_volume'] ?? 0) }}</td>
                        <td>{{ $format($row['amount'] ?? 0) }}</td>
                    </tr>
                @empty
                    <tr><td colspan="8" class="empty-state">No pump data for the selected filters.</td></tr>
                @endforelse
            </tbody>
            <tfoot>
                <tr class="totals-row">
                    <td colspan="5">Totals</td>
                    <td>{{ $format($combinedTotals['pump_totalizer_volume'] ?? 0, 3) }}</td>
                    <td>{{ $format($combinedTotals['pump_txn_volume'] ?? 0) }}</td>
                    <td>{{ $format($combinedTotals['pump_amount'] ?? 0) }}</td>
                </tr>
            </tfoot>
        </table>
    @else
        @forelse($individualShifts as $shift)
            <div class="shift-card">
                <div class="shift-header">
                    <span>Shift {{ $shift['shift_number'] ?? '-' }} (BOS: {{ $shift['bos_shift_id'] ?? 'N/A' }})</span>
                    <span>{{ $shift['start_time'] ?? 'N/A' }} — {{ $shift['end_time'] ?? 'N/A' }}</span>
                </div>

                <h4>Payment Mode Wise Summary</h4>
                <table>
                    <thead>
                        <tr>
                            <th>Mode of Payment</th>
                            <th>Volume (L)</th>
                            <th>Amount (SAR)</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($shift['payment_mode_summary'] ?? [] as $row)
                            <tr>
                                <td>{{ $row['mop'] ?? 'N/A' }}</td>
                                <td>{{ $format($row['volume'] ?? 0) }}</td>
                                <td>{{ $format($row['amount'] ?? 0) }}</td>
                            </tr>
                        @empty
                            <tr><td colspan="3" class="empty-state">No payment data for this shift.</td></tr>
                        @endforelse
                    </tbody>
                    <tfoot>
                        <tr class="totals-row">
                            <td>Total</td>
                            <td>{{ $format($shift['total_payment_volume'] ?? 0) }}</td>
                            <td>{{ $format($shift['total_payment_amount'] ?? 0) }}</td>
                        </tr>
                    </tfoot>
                </table>

                <h4>Product Wise Summary</h4>
                <table>
                    <thead>
                        <tr>
                            <th>Product</th>
                            <th>TXN Volume (L)</th>
                            <th>Amount (SAR)</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($shift['product_summary'] ?? [] as $row)
                            <tr>
                                <td>{{ $row['product'] ?? 'N/A' }}</td>
                                <td>{{ $format($row['txn_volume'] ?? 0) }}</td>
                                <td>{{ $format($row['amount'] ?? 0) }}</td>
                            </tr>
                        @empty
                            <tr><td colspan="3" class="empty-state">No product data for this shift.</td></tr>
                        @endforelse
                    </tbody>
                    <tfoot>
                        <tr class="totals-row">
                            <td>Total</td>
                            <td>{{ $format($shift['total_product_volume'] ?? 0) }}</td>
                            <td>{{ $format($shift['total_product_amount'] ?? 0) }}</td>
                        </tr>
                    </tfoot>
                </table>

                @php
                    $pumpEntries = $shift['pump_summary'] ?? [];
                    $pumpTotalizerSum = !empty($pumpEntries) ? collect($pumpEntries)->sum('totalizer_volume') : 0;
                @endphp

                <h4>Pump Wise Summary</h4>
                <table>
                    <thead>
                        <tr>
                            <th>Product</th>
                            <th>Pump</th>
                            <th>Nozzle</th>
                            <th>Start Tot.</th>
                            <th>End Tot.</th>
                            <th>Totalizer Vol.</th>
                            <th>TXN Volume</th>
                            <th>Amount (SAR)</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($pumpEntries as $row)
                            <tr>
                                <td>{{ $row['product'] ?? 'N/A' }}</td>
                                <td>{{ $row['pump_no'] ?? '-' }}</td>
                                <td>{{ $row['nozzle_no'] ?? '-' }}</td>
                                <td>{{ $format($row['start_totalizer'] ?? 0, 3) }}</td>
                                <td>{{ $format($row['end_totalizer'] ?? 0, 3) }}</td>
                                <td>{{ $format($row['totalizer_volume'] ?? 0, 3) }}</td>
                                <td>{{ $format($row['txn_volume'] ?? 0) }}</td>
                                <td>{{ $format($row['amount'] ?? 0) }}</td>
                            </tr>
                        @empty
                            <tr><td colspan="8" class="empty-state">No pump data for this shift.</td></tr>
                        @endforelse
                    </tbody>
                    <tfoot>
                        <tr class="totals-row">
                            <td colspan="5">Totals</td>
                            <td>{{ $format($pumpTotalizerSum, 3) }}</td>
                            <td>{{ $format($shift['total_pump_txn_volume'] ?? 0) }}</td>
                            <td>{{ $format($shift['total_pump_amount'] ?? 0) }}</td>
                        </tr>
                    </tfoot>
                </table>
            </div>
        @empty
            <div class="empty-state">No shifts found for the selected filters.</div>
        @endforelse
    @endif
</body>
</html>

