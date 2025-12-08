<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Pump Transactions Report</title>
    <style>
        body {
            font-family: 'Inter', Arial, sans-serif;
            font-size: 10px;
            color: #333;
            margin: 0;
            padding: 20px;
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
            padding: 8px;
            text-align: left;
            font-weight: 600;
            font-size: 10px;
        }
        td {
            padding: 6px;
            border-bottom: 1px solid #dee2e6;
            font-size: 9px;
        }
        tr:nth-child(even) {
            background-color: #f8f9fa;
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
        @if(!empty($filters['station_id']))
            <div class="filter-item"><span class="filter-label">Station:</span> {{ \App\Models\Station::find($filters['station_id'])->site_name ?? 'N/A' }}</div>
        @endif
    </div>
    @endif

    <table>
        <thead>
            <tr>
                <th>Site</th>
                <th>Date/Time</th>
                <th>Pump</th>
                <th>Nozzle</th>
                <th>Product</th>
                <th>Unit Price</th>
                <th>Liters</th>
                <th>Amount</th>
                <th>MOP</th>
            </tr>
        </thead>
        <tbody>


