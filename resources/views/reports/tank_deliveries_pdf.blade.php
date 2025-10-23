<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Tank Deliveries Report</title>
    <style>
        body { font-family: 'Inter', Arial, sans-serif; font-size: 11px; color: #333; }
        .header { text-align: center; margin-bottom: 30px; border-bottom: 3px solid #5051F9; padding-bottom: 15px; }
        .header h1 { font-family: 'DM Sans', Arial, sans-serif; color: #253F9C; margin: 0; font-size: 24px; }
        .header .subtitle { color: #666; margin-top: 5px; }
        .filters { background-color: #f8f9fa; padding: 15px; margin-bottom: 20px; border-radius: 5px; border-left: 4px solid #5051F9; }
        .filters h3 { margin-top: 0; color: #253F9C; font-size: 14px; }
        .filter-item { display: inline-block; margin-right: 20px; margin-bottom: 5px; }
        .filter-label { font-weight: bold; color: #555; }
        table { width: 100%; border-collapse: collapse; margin-top: 20px; }
        th { background-color: #5051F9; color: white; padding: 10px; text-align: left; font-weight: 600; font-size: 11px; }
        td { padding: 8px; border-bottom: 1px solid #dee2e6; }
        tr:nth-child(even) { background-color: #f8f9fa; }
        .footer { margin-top: 30px; text-align: center; font-size: 10px; color: #666; border-top: 1px solid #dee2e6; padding-top: 10px; }
        .summary { background-color: #e3f2fd; padding: 15px; margin-bottom: 20px; border-radius: 5px; }
        .summary-item { display: inline-block; margin-right: 30px; }
        .summary-label { font-weight: bold; color: #253F9C; }
    </style>
</head>
<body>
    <div class="header">
        <h1>Tank Deliveries Report</h1>
        <div class="subtitle">Generated on {{ now()->format('F d, Y - H:i:s') }}</div>
    </div>

    @if(!empty($filters))
    <div class="filters">
        <h3>Applied Filters:</h3>
        @foreach($filters as $key => $value)
            @if(!empty($value))
                <div class="filter-item"><span class="filter-label">{{ ucwords(str_replace('_', ' ', $key)) }}:</span> {{ $value }}</div>
            @endif
        @endforeach
    </div>
    @endif

    <div class="summary">
        <div class="summary-item"><span class="summary-label">Total Records:</span> {{ count($deliveries) }}</div>
        <div class="summary-item"><span class="summary-label">Total Delivered Volume:</span> {{ number_format($deliveries->sum('received_product_volume'), 2) }} L</div>
    </div>

    <table>
        <thead>
            <tr>
                <th>ID</th>
                <th>Device ID</th>
                <th>Tank ID</th>
                <th>Station</th>
                <th>Delivered Volume</th>
                <th>Start Time</th>
                <th>End Time</th>
                <th>Invoice</th>
            </tr>
        </thead>
        <tbody>
            @foreach($deliveries as $delivery)
            <tr>
                <td>{{ $delivery->id }}</td>
                <td>{{ $delivery->pts_id }}</td>
                <td>{{ $delivery->tank }}</td>
                <td>{{ $delivery->station?->name ?? 'N/A' }}</td>
                <td>{{ number_format($delivery->received_product_volume ?? 0, 2) }} L</td>
                <td>{{ $delivery->start_datetime ? \Carbon\Carbon::parse($delivery->start_datetime)->format('Y-m-d H:i:s') : 'N/A' }}</td>
                <td>{{ $delivery->end_datetime ? \Carbon\Carbon::parse($delivery->end_datetime)->format('Y-m-d H:i:s') : 'N/A' }}</td>
                <td>{{ $delivery->pts_delivery_id ?? 'N/A' }}</td>
            </tr>
            @endforeach
        </tbody>
    </table>

    <div class="footer">
        <p>© {{ now()->year }} Petrol Pump Station HOS - This is a system generated report</p>
    </div>
</body>
</html>

