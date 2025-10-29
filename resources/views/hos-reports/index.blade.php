@extends('layouts.adminlte')

@push('css')
    <style>
        .nav-tabs .nav-link {
            font-size: 14px;
            padding: 1rem 1rem;
            font-weight: 500;
            color: #6c757d !important;
            border: none;
            background: transparent;
            transition: all 0.3s;
        }

        .nav-tabs .nav-link:hover {
            background-color: #f8f9fa;
            border-color: transparent;
        }

        .nav-tabs .nav-link.active {
            color:rgb(0, 0, 0) !important;
            background-color: #dedede;
            border-bottom: 2px solid #000 !important;
            border-color: transparent #dee2e6 #fff #dee2e6;
        }

        .custom-card {
            border-radius: 8px;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
            border: none;
        }

        .custom-card-header {
            background: linear-gradient(135deg,rgb(0, 0, 0) 0%,rgb(6, 6, 6) 100%);
            color: white;
            font-weight: 600;
            padding: 1rem 1.5rem;
            border-radius: 8px 8px 0 0;
        }

        .custom-table-header {
            background-color: #f8f9fa;
            font-weight: 600;
        }
    </style>
@endpush

@section('content')
    <!-- Content Header (Page header) -->
    <div class="content-header">
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-sm-6">
                    <h1 class="m-0">HOS Reports</h1>
                </div>
                <div class="col-sm-6">
                    <ol class="breadcrumb float-sm-right">
                        <li class="breadcrumb-item"><a href="{{ route('home') }}">Home</a></li>
                        <li class="breadcrumb-item active">HOS Reports</li>
                    </ol>
                </div>
            </div>
        </div>
    </div>

    <!-- Main content -->
    <section class="content">
        <div class="container-fluid">
            <div class="card custom-card">
                <div class="card-header">
                    <ul class="nav nav-tabs" role="tablist">
                        <li class="nav-item">
                            <a class="nav-link active" id="transactions-tab" data-toggle="tab" href="#transactions" role="tab" aria-controls="transactions" aria-selected="true">
                                <i class="fas fa-money-bill-wave"></i> Transactions
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" id="sales-tab" data-toggle="tab" href="#sales" role="tab" aria-controls="sales" aria-selected="false">
                                <i class="fas fa-dollar-sign"></i> Sales
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" id="tank-inventory-tab" data-toggle="tab" href="#tank-inventory" role="tab" aria-controls="tank-inventory" aria-selected="false">
                                <i class="fas fa-boxes"></i> Tank Inventory
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" id="tank-deliveries-tab" data-toggle="tab" href="#tank-deliveries" role="tab" aria-controls="tank-deliveries" aria-selected="false">
                                <i class="fas fa-truck"></i> Tank Deliveries
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" id="sales-summary-tab" data-toggle="tab" href="#sales-summary" role="tab" aria-controls="sales-summary" aria-selected="false">
                                <i class="fas fa-chart-bar"></i> Sales Summary
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" id="analytical-sales-tab" data-toggle="tab" href="#analytical-sales" role="tab" aria-controls="analytical-sales" aria-selected="false">
                                <i class="fas fa-chart-line"></i> Analytical Sales
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" id="shift-summary-tab" data-toggle="tab" href="#shift-summary" role="tab" aria-controls="shift-summary" aria-selected="false">
                                <i class="fas fa-clipboard-list"></i> Shift Summary
                            </a>
                        </li>
                    </ul>
                </div>
                <div class="card-body">
                    <div class="tab-content" id="hosReportsTabContent">
                        <!-- Transactions Tab -->
                        <div class="tab-pane fade show active" id="transactions" role="tabpanel" aria-labelledby="transactions-tab">
                            @include('hos-reports.partials.transactions')
                        </div>
                        <!-- Sales Tab -->
                        <div class="tab-pane fade" id="sales" role="tabpanel" aria-labelledby="sales-tab">
                            @include('hos-reports.partials.sales')
                        </div>
                        <!-- Tank Inventory Tab -->
                        <div class="tab-pane fade" id="tank-inventory" role="tabpanel" aria-labelledby="tank-inventory-tab">
                            @include('hos-reports.partials.tank-inventory')
                        </div>
                        <div class="tab-pane fade" id="tank-deliveries" role="tabpanel" aria-labelledby="tank-delivery-tab">
                            @include('hos-reports.partials.tank-deliveries')
                        </div>
                        <!-- Sales Summary Tab -->
                        <div class="tab-pane fade" id="sales-summary" role="tabpanel" aria-labelledby="sales-summary-tab">
                            @include('hos-reports.partials.sales-summary')
                        </div>
                        <!-- Analytical Sales Tab -->
                        <div class="tab-pane fade" id="analytical-sales" role="tabpanel" aria-labelledby="analytical-sales-tab">
                            @include('hos-reports.partials.analytical-sales')
                        </div>
                        <!-- Shift Summary Tab -->
                        <div class="tab-pane fade" id="shift-summary" role="tabpanel" aria-labelledby="shift-summary-tab">
                            @include('hos-reports.partials.shift-summary')
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>
@endsection

@push('js')
    <script>
        $(document).ready(function() {
            console.log('HOS Reports page initialized');
        });
    </script>
@endpush
