<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Analytical Sales Report</title>
    <style>
        body {
            font-family: 'Inter', Arial, sans-serif;
            font-size: 12px;
            color: #333;
        }
        .header {
            text-align: center;
            margin-bottom: 30px;
            border-bottom: 3px solid rgb(0, 0, 0);
            padding-bottom: 15px;
        }
        .header h1 {
            font-family: 'DM Sans', Arial, sans-serif;
            color: rgb(0, 0, 0);
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
            border-left: 4px solid rgb(0, 0, 0);
        }
        .filters h3 {
            margin-top: 0;
            color: rgb(0, 0, 0);
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
            background-color: rgb(0, 0, 0);
            color: white;
            padding: 10px;
            text-align: left;
            font-weight: 600;
        }
        th.text-right {
            text-align: right;
        }
        td {
            padding: 8px;
            border-bottom: 1px solid #dee2e6;
        }
        td.text-right {
            text-align: right;
        }
        tr:nth-child(even) {
            background-color: #f8f9fa;
        }
        tfoot tr {
            background-color: #e9ecef;
            font-weight: bold;
        }
        tfoot td {
            padding: 12px 8px;
            border-top: 2px solid rgb(0, 0, 0);
            border-bottom: 2px solid rgb(0, 0, 0);
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
            color: rgb(0, 0, 0);
        }
    </style>
</head>
<body>
    <div class="header">
        <h1>Analytical Sales Report</h1>
        <div class="subtitle">Generated on {{ now()->format('F d, Y - H:i:s') }}</div>
    </div>

    @if(!empty($filters) && (isset($filters['from_date']) || isset($filters['to_date']) || isset($filters['station_id']) || isset($filters['product_id'])))
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
        @if(!empty($filters['station_id']))
            <div class="filter-item"><span class="filter-label">Station:</span> {{ \App\Models\Station::find($filters['station_id'])->site_name ?? 'N/A' }}</div>
        @endif
        @if(!empty($filters['product_id']))
            <div class="filter-item"><span class="filter-label">Product:</span> {{ \App\Models\FuelGrade::find($filters['product_id'])->name ?? 'N/A' }}</div>
        @endif
    </div>
    @endif

    <div class="summary">
        <div class="summary-item"><span class="summary-label">Total Records:</span> {{ count($data) }}</div>
        <div class="summary-item"><span class="summary-label">Total Liters:</span> {{ number_format($total_liters, 2) }} L</div>
        <div class="summary-item"><span class="summary-label">Total Amount:</span> SAR {{ number_format($total_amount, 2) }}</div>
        <div class="summary-item"><span class="summary-label">Total Transactions:</span> {{ number_format($total_transactions, 0) }}</div>
        <div class="summary-item"><span class="summary-label">Avg Transaction:</span> SAR {{ number_format($overall_avg_transaction, 2) }}</div>
    </div>

    <table>
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
        <tbody>
            @forelse($data as $row)
            <tr>
                <td>{{ $row['date'] ?? 'N/A' }}</td>
                <td>{{ $row['site'] ?? 'N/A' }}</td>
                <td>{{ $row['product'] ?? 'N/A' }}</td>
                <td class="text-right">{{ number_format($row['liters_sold'] ?? 0, 2) }}</td>
                <td class="text-right">{{ number_format($row['amount'] ?? 0, 2) }}</td>
                <td class="text-right">{{ number_format($row['transactions'] ?? 0, 0) }}</td>
                <td class="text-right">{{ number_format($row['avg_transaction_amount'] ?? 0, 2) }}</td>
            </tr>
            @empty
            <tr>
                <td colspan="7" style="text-align: center; padding: 20px; color: #999;">No data available</td>
            </tr>
            @endforelse
        </tbody>
        @if(count($data) > 0)
        <tfoot>
            <tr>
                <td colspan="3"><strong>TOTAL</strong></td>
                <td class="text-right"><strong>{{ number_format($total_liters, 2) }}</strong></td>
                <td class="text-right"><strong>{{ number_format($total_amount, 2) }}</strong></td>
                <td class="text-right"><strong>{{ number_format($total_transactions, 0) }}</strong></td>
                <td class="text-right"><strong>{{ number_format($overall_avg_transaction, 2) }}</strong></td>
            </tr>
        </tfoot>
        @endif
    </table>

    <div class="footer">
        <p>© {{ now()->year }} Petrol Pump Station HOS - This is a system generated report</p>
    </div>
</body>
</html>

