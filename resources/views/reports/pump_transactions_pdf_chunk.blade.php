@foreach($transactions as $transaction)
<tr>
    <td>{{ $transaction['site_name'] ?? 'N/A' }}</td>
    <td>
        @if($transaction['date_time_start'])
            {{ \Carbon\Carbon::parse($transaction['date_time_start'])->format('Y-m-d H:i:s') }}
        @else
            N/A
        @endif
    </td>
    <td>{{ $transaction['pts_pump_id'] ?? 'N/A' }}</td>
    <td>{{ $transaction['pts_nozzle_id'] ?? 'N/A' }}</td>
    <td>{{ $transaction['product_name'] ?? 'N/A' }}</td>
    <td>{{ $transaction['price'] !== null ? number_format($transaction['price'], 2) : '0.00' }}</td>
    <td>{{ $transaction['volume'] !== null ? number_format($transaction['volume'], 2) : '0.00' }}</td>
    <td>{{ $transaction['amount'] !== null ? number_format($transaction['amount'], 2) : '0.00' }}</td>
    <td>{{ ucfirst($transaction['mode_of_payment'] ?? 'N/A') }}</td>
</tr>
@endforeach


