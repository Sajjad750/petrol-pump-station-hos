<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Tank Inventory Report</title>
    <style>
        body { font-family: 'Inter', Arial, sans-serif; font-size: 11px; color: #1b1f3b; margin: 0; padding: 18px; }
        h1 { font-size: 22px; margin: 0 0 10px; color: #0f172a; }
        .meta { margin-bottom: 8px; color: #475569; font-size: 11px; }
        .filters { background: #f8fafc; border: 1px solid #e2e8f0; border-radius: 8px; padding: 10px; margin-bottom: 12px; }
        .filter-item { display: inline-block; margin-right: 14px; margin-bottom: 6px; }
        .filter-label { font-weight: 600; color: #111827; }
        table { width: 100%; border-collapse: collapse; margin-top: 10px; font-size: 10px; }
        th { background: #0f172a; color: #fff; padding: 6px 5px; text-align: left; }
        td { padding: 5px; border-bottom: 1px solid #e5e7eb; }
        tr:nth-child(even) { background: #f9fafb; }
        .text-right { text-align: right; }
    </style>
</head>
<body>
    <h1>Tank Inventory Report</h1>
    <div class="meta">Generated on {{ $generatedAt->format('d M Y H:i:s') }}</div>

    @if(!empty($filters))
        <div class="filters">
            @foreach($filters as $label => $value)
                <span class="filter-item"><span class="filter-label">{{ $label }}:</span> {{ $value }}</span>
            @endforeach
        </div>
    @endif

    <table>
        <thead>
            <tr>
                <th>Date &amp; Time</th>
                <th>Site</th>
                <th>Tank</th>
                <th>Product</th>
                <th class="text-right">Volume (L)</th>
                <th class="text-right">Height (mm)</th>
                <th class="text-right">Water (mm)</th>
                <th class="text-right">Temp (°C)</th>
                <th class="text-right">Ullage (L)</th>
            </tr>
        </thead>
        <tbody>
            @forelse($records as $row)
                <tr>
                    <td>{{ $row['date_time'] }}</td>
                    <td>{{ $row['site'] }}</td>
                    <td>{{ $row['tank'] }}</td>
                    <td>{{ $row['product'] }}</td>
                    <td class="text-right">{{ number_format((float) $row['volume'], 2) }}</td>
                    <td class="text-right">{{ number_format((float) $row['height'], 2) }}</td>
                    <td class="text-right">{{ number_format((float) $row['water'], 2) }}</td>
                    <td class="text-right">{{ number_format((float) $row['temperature'], 2) }}</td>
                    <td class="text-right">{{ number_format((float) $row['ullage'], 2) }}</td>
                </tr>
            @empty
                <tr>
                    <td colspan="9" style="text-align:center; padding:12px;">No records found for the selected filters.</td>
                </tr>
            @endforelse
        </tbody>
    </table>
</body>
</html>

