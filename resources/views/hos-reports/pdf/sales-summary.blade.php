<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Sales Summary Report</title>
    <style>
        body { font-family: 'Inter', Arial, sans-serif; font-size: 11px; color: #1b1f3b; margin: 0; padding: 18px; }
        h1 { font-size: 22px; margin: 0 0 10px; color: #0f172a; }
        h2 { font-size: 16px; margin: 18px 0 10px; color: #0f172a; }
        .meta { margin-bottom: 8px; color: #475569; font-size: 11px; }
        .filters { background: #f8fafc; border: 1px solid #e2e8f0; border-radius: 8px; padding: 10px; margin-bottom: 12px; }
        .filter-item { display: inline-block; margin-right: 14px; margin-bottom: 6px; }
        .filter-label { font-weight: 600; color: #111827; }
        table { width: 100%; border-collapse: collapse; margin-top: 10px; font-size: 10px; }
        th { background: #0f172a; color: #fff; padding: 6px 5px; text-align: left; }
        td { padding: 5px; border-bottom: 1px solid #e5e7eb; }
        tr:nth-child(even) { background: #f9fafb; }
        .text-right { text-align: right; }
        .totals-row { background: #eef2ff; font-weight: 600; }
    </style>
</head>
<body>
    <h1>Sales Summary Report</h1>
    <div class="meta">Generated on {{ $generatedAt->format('d M Y H:i:s') }}</div>

    @if(!empty($filters))
        <div class="filters">
            @foreach($filters as $label => $value)
                <span class="filter-item"><span class="filter-label">{{ $label }}:</span> {{ $value }}</span>
            @endforeach
        </div>
    @endif

    <h2>Payment Mode Summary</h2>
    <table>
        <thead>
            <tr>
                <th>Sales Type</th>
                <th class="text-right">Volume (L)</th>
                <th class="text-right">Total Amount (SAR)</th>
                <th class="text-right">Sales Count</th>
            </tr>
        </thead>
        <tbody>
            @forelse($paymentSummary as $row)
                <tr>
                    <td>{{ $row['sales_type'] ?? $row['sales_type'] ?? 'N/A' }}</td>
                    <td class="text-right">{{ number_format((float) ($row['volume'] ?? 0), 2) }}</td>
                    <td class="text-right">{{ number_format((float) ($row['total_amount'] ?? 0), 2) }}</td>
                    <td class="text-right">{{ number_format((float) ($row['sales_count'] ?? 0), 0) }}</td>
                </tr>
            @empty
                <tr><td colspan="4" style="text-align:center; padding:12px;">No data.</td></tr>
            @endforelse
        </tbody>
        <tfoot>
            <tr class="totals-row">
                <td>Total</td>
                <td class="text-right">{{ number_format((float) $totalVolume, 2) }}</td>
                <td class="text-right">{{ number_format((float) $totalAmount, 2) }}</td>
                <td class="text-right">{{ number_format((float) $totalSalesCount, 0) }}</td>
            </tr>
        </tfoot>
    </table>

    <h2>Product Wise Summary</h2>
    <table>
        <thead>
            <tr>
                <th>Product Name</th>
                <th class="text-right">Volume (L)</th>
                <th class="text-right">Total Amount (SAR)</th>
                <th class="text-right">No. of Sales</th>
                <th class="text-right">Avg Sales Amount (SAR)</th>
            </tr>
        </thead>
        <tbody>
            @forelse($productSummary as $row)
                <tr>
                    <td>{{ $row['product_name'] ?? 'N/A' }}</td>
                    <td class="text-right">{{ number_format((float) ($row['volume'] ?? 0), 2) }}</td>
                    <td class="text-right">{{ number_format((float) ($row['total_amount'] ?? 0), 2) }}</td>
                    <td class="text-right">{{ number_format((float) ($row['sales_count'] ?? 0), 0) }}</td>
                    <td class="text-right">{{ number_format((float) ($row['avg_sales_amount'] ?? 0), 2) }}</td>
                </tr>
            @empty
                <tr><td colspan="5" style="text-align:center; padding:12px;">No data.</td></tr>
            @endforelse
        </tbody>
    </table>

    <h2>Attendant Wise Summary</h2>
    <table>
        <thead>
            <tr>
                <th>Attendant Name</th>
                <th class="text-right">Volume (L)</th>
                <th class="text-right">Total Amount (SAR)</th>
                <th class="text-right">Total Transactions</th>
            </tr>
        </thead>
        <tbody>
            @forelse($attendantSummary as $row)
                <tr>
                    <td>{{ $row['attendant_name'] ?? 'N/A' }}</td>
                    <td class="text-right">{{ number_format((float) ($row['volume'] ?? 0), 2) }}</td>
                    <td class="text-right">{{ number_format((float) ($row['total_amount'] ?? 0), 2) }}</td>
                    <td class="text-right">{{ number_format((float) ($row['transactions_count'] ?? 0), 0) }}</td>
                </tr>
            @empty
                <tr><td colspan="4" style="text-align:center; padding:12px;">No data.</td></tr>
            @endforelse
        </tbody>
    </table>
</body>
</html>

