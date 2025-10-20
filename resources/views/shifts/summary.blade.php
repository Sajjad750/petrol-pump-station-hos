@extends('layouts.adminlte')

@section('title', 'Shift Summary #' . $shift->id)

@section('content_header')
    <div class="d-flex justify-content-between align-items-center">
        <h1>Shift Summary #{{ $shift->id }}</h1>
        <div>
            <a href="{{ route('shifts.index') }}" class="btn btn-secondary">
                <i class="fas fa-arrow-left"></i> Back to Shifts
            </a>
        </div>
    </div>
@stop

@section('content')
    {{-- Shift Information Card --}}
    <div class="card">
        <div class="card-header bg-primary">
            <h3 class="card-title"><i class="fas fa-info-circle"></i> Shift Information</h3>
        </div>
        <div class="card-body">
            <div class="row">
                <div class="col-md-3">
                    <strong>Shift ID:</strong> {{ $shift->id }}
                </div>
                <div class="col-md-3">
                    <strong>Station:</strong> {{ $shift->station->site_name ?? 'N/A' }}
                </div>
                <div class="col-md-3">
                    <strong>Start Time:</strong> {{ $shift->start_time ? $shift->start_time->format('Y-m-d H:i:s') : 'N/A' }}
                </div>
                <div class="col-md-3">
                    <strong>End Time:</strong> {{ $shift->end_time ? $shift->end_time->format('Y-m-d H:i:s') : 'N/A' }}
                </div>
            </div>
            <div class="row mt-2">
                <div class="col-md-3">
                    <strong>Status:</strong> 
                    <span class="badge badge-{{ $shift->status == 1 ? 'success' : 'warning' }}">
                        {{ $shift->getStatusDisplay() }}
                    </span>
                </div>
                <div class="col-md-3">
                    <strong>Close Type:</strong> {{ $shift->getCloseTypeDisplay() }}
                </div>
                <div class="col-md-3">
                    <strong>Duration:</strong> {{ $shift->getDurationFormatted() }}
                </div>
            </div>
        </div>
    </div>

    {{-- Product Wise Summary --}}
    <div class="card">
        <div class="card-header bg-info">
            <h3 class="card-title"><i class="fas fa-gas-pump"></i> Product Wise Summary</h3>
        </div>
        <div class="card-body">
            <table class="table table-bordered table-striped table-hover">
                <thead class="thead-dark">
                    <tr>
                        <th>Product</th>
                        <th class="text-right">Volume TXN (L)</th>
                        <th class="text-right">Amount (SAR)</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($productSummaries as $summary)
                        <tr>
                            <td>{{ $summary->fuelGrade->name ?? 'N/A' }}</td>
                            <td class="text-right">{{ number_format($summary->volume, 3) }}</td>
                            <td class="text-right">{{ number_format($summary->amount, 2) }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="3" class="text-center">No product summaries found</td>
                        </tr>
                    @endforelse
                </tbody>
                <tfoot class="bg-light font-weight-bold">
                    <tr>
                        <td>Total</td>
                        <td class="text-right">{{ number_format($productSummaries->sum('volume'), 3) }}</td>
                        <td class="text-right">{{ number_format($productSummaries->sum('amount'), 2) }}</td>
                    </tr>
                </tfoot>
            </table>
        </div>
    </div>

    {{-- Payment Mode Wise Summary --}}
    <div class="card">
        <div class="card-header bg-success">
            <h3 class="card-title"><i class="fas fa-credit-card"></i> Payment Mode Wise Summary</h3>
        </div>
        <div class="card-body">
            <table class="table table-bordered table-striped table-hover">
                <thead class="thead-dark">
                    <tr>
                        <th>MOP</th>
                        <th class="text-right">Volume (L)</th>
                        <th class="text-right">Amount (SAR)</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($paymentSummaries as $summary)
                        <tr>
                            <td>{{ $summary->mop ?? 'N/A' }}</td>
                            <td class="text-right">{{ number_format($summary->volume, 3) }}</td>
                            <td class="text-right">{{ number_format($summary->amount, 2) }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="3" class="text-center">No payment summaries found</td>
                        </tr>
                    @endforelse
                </tbody>
                <tfoot class="bg-light font-weight-bold">
                    <tr>
                        <td>Total</td>
                        <td class="text-right">{{ number_format($paymentSummaries->sum('volume'), 3) }}</td>
                        <td class="text-right">{{ number_format($paymentSummaries->sum('amount'), 2) }}</td>
                    </tr>
                </tfoot>
            </table>
        </div>
    </div>

    {{-- Pump Wise Summary --}}
    <div class="card">
        <div class="card-header bg-warning">
            <h3 class="card-title"><i class="fas fa-pump"></i> Pump Wise Summary</h3>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-bordered table-striped table-hover table-sm">
                    <thead class="thead-dark">
                        <tr>
                            <th>Product</th>
                            <th class="text-center">Pump No</th>
                            <th class="text-center">Nozzle No</th>
                            <th class="text-right">Start Totalizer</th>
                            <th class="text-right">End Totalizer</th>
                            <th class="text-right">Totalizer Volume</th>
                            <th class="text-right">Txn Volume</th>
                            <th class="text-right">Amount (SAR)</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($pumpSummaries as $summary)
                            <tr>
                                <td>{{ $summary->product }}</td>
                                <td class="text-center">{{ $summary->pump_no ?? 'N/A' }}</td>
                                <td class="text-center">{{ $summary->nozzle_no ?? 'N/A' }}</td>
                                <td class="text-right">{{ number_format($summary->start_totalizer, 3) }}</td>
                                <td class="text-right">{{ number_format($summary->end_totalizer, 3) }}</td>
                                <td class="text-right">{{ number_format($summary->totalizer_volume, 3) }}</td>
                                <td class="text-right">{{ number_format($summary->txn_volume, 3) }}</td>
                                <td class="text-right">{{ number_format($summary->amount, 2) }}</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="8" class="text-center">No pump summaries found</td>
                            </tr>
                        @endforelse
                    </tbody>
                    <tfoot class="bg-light font-weight-bold">
                        <tr>
                            <td colspan="5" class="text-right">Total</td>
                            <td class="text-right">{{ number_format($pumpSummaries->sum('totalizer_volume'), 3) }}</td>
                            <td class="text-right">{{ number_format($pumpSummaries->sum('txn_volume'), 3) }}</td>
                            <td class="text-right">{{ number_format($pumpSummaries->sum('amount'), 2) }}</td>
                        </tr>
                    </tfoot>
                </table>
            </div>
        </div>
    </div>

    {{-- Other Shifts from Same Station --}}
    @if($otherShifts->count() > 0)
    <div class="card">
        <div class="card-header bg-secondary">
            <h3 class="card-title"><i class="fas fa-history"></i> Other Shifts from {{ $shift->station->site_name ?? 'Station' }}</h3>
        </div>
        <div class="card-body">
            <div class="list-group">
                @foreach($otherShifts as $otherShift)
                    <a href="{{ route('shifts.summary', $otherShift->id) }}" class="list-group-item list-group-item-action">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <strong>Shift #{{ $otherShift->id }}</strong> - 
                                {{ $otherShift->start_time ? $otherShift->start_time->format('Y-m-d H:i') : 'N/A' }}
                            </div>
                            <span class="badge badge-{{ $otherShift->status == 1 ? 'success' : 'warning' }}">
                                {{ $otherShift->getStatusDisplay() }}
                            </span>
                        </div>
                    </a>
                @endforeach
            </div>
        </div>
    </div>
    @endif
@stop

@section('css')
    <style>
        .card {
            margin-bottom: 20px;
        }
        .table-responsive {
            overflow-x: auto;
        }
        .table thead th {
            vertical-align: middle;
        }
        .bg-light {
            background-color: #f8f9fa !important;
        }
    </style>
@stop

@section('js')
    <script>
        console.log('Shift Summary Page Loaded');
    </script>
@stop

