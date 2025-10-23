<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>{{ $title }}</title>
    <style>
        body { font-family: 'Inter', Arial, sans-serif; font-size: 11px; color: #333; }
        .header { text-align: center; margin-bottom: 30px; border-bottom: 3px solid #5051F9; padding-bottom: 15px; }
        .header h1 { font-family: 'DM Sans', Arial, sans-serif; color: #253F9C; margin: 0; font-size: 24px; }
        .header .subtitle { color: #666; margin-top: 5px; }
        .filters { background-color: #f8f9fa; padding: 15px; margin-bottom: 20px; border-radius: 5px; border-left: 4px solid #5051F9; }
        .filters h3 { margin-top: 0; color: #253F9C; font-size: 14px; }
        .filter-item { display: inline-block; margin-right: 20px; margin-bottom: 5px; }
        .filter-label { font-weight: bold; color: #555; }
        table { width: 100%; border-collapse: collapse; margin-top: 20px; font-size: 10px; }
        th { background-color: #5051F9; color: white; padding: 8px 5px; text-align: left; font-weight: 600; }
        td { padding: 6px 5px; border-bottom: 1px solid #dee2e6; }
        tr:nth-child(even) { background-color: #f8f9fa; }
        .footer { margin-top: 30px; text-align: center; font-size: 10px; color: #666; border-top: 1px solid #dee2e6; padding-top: 10px; }
        .summary { background-color: #e3f2fd; padding: 15px; margin-bottom: 20px; border-radius: 5px; }
        .summary-item { display: inline-block; margin-right: 30px; margin-bottom: 5px; }
        .summary-label { font-weight: bold; color: #253F9C; }
    </style>
</head>
<body>
    <div class="header">
        <h1>{{ $title }}</h1>
        <div class="subtitle">Generated on {{ now()->format('F d, Y - H:i:s') }}</div>
    </div>

    @if(!empty($filters) && count(array_filter($filters)) > 0)
    <div class="filters">
        <h3>Applied Filters:</h3>
        @foreach($filters as $key => $value)
            @if(!empty($value) && !is_array($value))
                <div class="filter-item"><span class="filter-label">{{ ucwords(str_replace('_', ' ', $key)) }}:</span> {{ $value }}</div>
            @endif
        @endforeach
    </div>
    @endif

    @if(isset($summary))
    <div class="summary">
        @foreach($summary as $label => $value)
            <div class="summary-item"><span class="summary-label">{{ $label }}:</span> {{ $value }}</div>
        @endforeach
    </div>
    @endif

    <table>
        <thead>
            <tr>
                @foreach($headers as $header)
                    <th>{{ $header }}</th>
                @endforeach
            </tr>
        </thead>
        <tbody>
            @foreach($data as $row)
            <tr>
                @foreach($row as $cell)
                    <td>{!! $cell !!}</td>
                @endforeach
            </tr>
            @endforeach
        </tbody>
    </table>

    <div class="footer">
        <p>© {{ now()->year }} Petrol Pump Station HOS - This is a system generated report</p>
    </div>
</body>
</html>

