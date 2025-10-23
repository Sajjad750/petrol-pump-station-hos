<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Pump Transactions Report</title>
    <style>
        body {
            font-family: 'Inter', Arial, sans-serif;
            font-size: 12px;
            color: #333;
        }
        .header {
            text-align: center;
            margin-bottom: 30px;
            border-bottom: 3px solid #5051F9;
            padding-bottom: 15px;
        }
        .header h1 {
            font-family: 'DM Sans', Arial, sans-serif;
            color: #253F9C;
            margin: 0;
            font-size: 24px;
        }
        .header .subtitle {
            color: #666;
            margin-top: 5px;
        }
        .filters {
            background-color: #f8f9fa;
            padding: 15px;
            margin-bottom: 20px;
            border-radius: 5px;
            border-left: 4px solid #5051F9;
        }
        .filters h3 {
            margin-top: 0;
            color: #253F9C;
            font-size: 14px;
        }
        .filter-item {
            display: inline-block;
            margin-right: 20px;
            margin-bottom: 5px;
        }
        .filter-label {
            font-weight: bold;
            color: #555;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
        }
        th {
            background-color: #5051F9;
            color: white;
            padding: 10px;
            text-align: left;
            font-weight: 600;
        }
        td {
            padding: 8px;
            border-bottom: 1px solid #dee2e6;
        }
        tr:nth-child(even) {
            background-color: #f8f9fa;
        }
        .footer {
            margin-top: 30px;
            text-align: center;
            font-size: 10px;
            color: #666;
            border-top: 1px solid #dee2e6;
            padding-top: 10px;
        }
        .summary {
            background-color: #e3f2fd;
            padding: 15px;
            margin-bottom: 20px;
            border-radius: 5px;
        }
        .summary-item {
            display: inline-block;
            margin-right: 30px;
        }
        .summary-label {
            font-weight: bold;
            color: #253F9C;
        }
    </style>
</head>
<body>
    <div class="header">
        <h1>Pump Transactions Report</h1>
        <div class="subtitle">Generated on {{ now()->format('F d, Y - H:i:s') }}</div>
    </div>

    @if(!empty($filters))
    <div class="filters">
        <h3>Applied Filters:</h3>
        @if(!empty($filters['from_date']))
            <div class="filter-item"><span class="filter-label">From Date:</span> {{ $filters['from_date'] }}</div>
        @endif
        @if(!empty($filters['to_date']))
            <div class="filter-item"><span class="filter-label">To Date:</span> {{ $filters['to_date'] }}</div>
        @endif
        @if(!empty($filters['from_time']))
            <div class="filter-item"><span class="filter-label">From Time:</span> {{ $filters['from_time'] }}</div>
        @endif
        @if(!empty($filters['to_time']))
            <div class="filter-item"><span class="filter-label">To Time:</span> {{ $filters['to_time'] }}</div>
        @endif
    </div>
    @endif

    <div class="summary">
        <div class="summary-item"><span class="summary-label">Total Records:</span> {{ count($transactions) }}</div>
        <div class="summary-item"><span class="summary-label">Total Volume:</span> {{ number_format($transactions->sum('total_volume'), 2) }} L</div>
        <div class="summary-item"><span class="summary-label">Total Amount:</span> ₹{{ number_format($transactions->sum('total_amount'), 2) }}</div>
    </div>

    <table>
        <thead>
            <tr>
                <th>ID</th>
                <th>Device ID</th>
                <th>Pump Name</th>
                <th>Shift ID</th>
                <th>Volume</th>
                <th>Amount</th>
                <th>Unit Price</th>
                <th>Date</th>
                <th>Time</th>
            </tr>
        </thead>
        <tbody>
            @foreach($transactions as $transaction)
            <tr>
                <td>{{ $transaction->id }}</td>
                <td>{{ $transaction->device_id }}</td>
                <td>{{ $transaction->pump?->name ?? 'N/A' }}</td>
                <td>{{ $transaction->shift_id }}</td>
                <td>{{ number_format($transaction->total_volume, 2) }} L</td>
                <td>₹{{ number_format($transaction->total_amount, 2) }}</td>
                <td>₹{{ number_format($transaction->unit_price, 2) }}</td>
                <td>{{ \Carbon\Carbon::parse($transaction->created_at)->format('Y-m-d') }}</td>
                <td>{{ \Carbon\Carbon::parse($transaction->created_at)->format('H:i:s') }}</td>
            </tr>
            @endforeach
        </tbody>
    </table>

    <div class="footer">
        <p>© {{ now()->year }} Petrol Pump Station HOS - This is a system generated report</p>
    </div>
</body>
</html>

