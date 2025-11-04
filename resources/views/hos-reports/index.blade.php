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
            color: rgb(0, 0, 0) !important;
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
            background: linear-gradient(135deg, rgb(0, 0, 0) 0%, rgb(6, 6, 6) 100%);
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
                            <a class="nav-link" id="tank-monitoring-tab" data-toggle="tab" href="#tank-monitoring" role="tab" aria-controls="tank-monitoring" aria-selected="false">
                                <i class="fas fa-tachometer-alt"></i> Tank Monitoring
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
                            <div class="tab-content-loader" data-tab="transactions">
                                <div class="py-5 text-center">
                                    <i class="fas fa-spinner fa-spin fa-2x text-muted"></i>
                                    <p class="text-muted mt-2">Loading transactions...</p>
                                </div>
                            </div>
                        </div>
                        <!-- Sales Tab -->
                        <div class="tab-pane fade" id="sales" role="tabpanel" aria-labelledby="sales-tab">
                            <div class="tab-content-loader" data-tab="sales">
                                <div class="py-5 text-center">
                                    <i class="fas fa-spinner fa-spin fa-2x text-muted"></i>
                                    <p class="text-muted mt-2">Loading sales data...</p>
                                </div>
                            </div>
                        </div>
                        <!-- Tank Inventory Tab -->
                        <div class="tab-pane fade" id="tank-inventory" role="tabpanel" aria-labelledby="tank-inventory-tab">
                            <div class="tab-content-loader" data-tab="tank-inventory">
                                <div class="py-5 text-center">
                                    <i class="fas fa-spinner fa-spin fa-2x text-muted"></i>
                                    <p class="text-muted mt-2">Loading tank inventory...</p>
                                </div>
                            </div>
                        </div>
                        <!-- Tank Deliveries Tab -->
                        <div class="tab-pane fade" id="tank-deliveries" role="tabpanel" aria-labelledby="tank-deliveries-tab">
                            <div class="tab-content-loader" data-tab="tank-deliveries">
                                <div class="py-5 text-center">
                                    <i class="fas fa-spinner fa-spin fa-2x text-muted"></i>
                                    <p class="text-muted mt-2">Loading tank deliveries...</p>
                                </div>
                            </div>
                        </div>
                        <!-- Tank Monitoring Tab -->
                        <div class="tab-pane fade" id="tank-monitoring" role="tabpanel" aria-labelledby="tank-monitoring-tab">
                            <div class="tab-content-loader" data-tab="tank-monitoring">
                                <div class="py-5 text-center">
                                    <i class="fas fa-spinner fa-spin fa-2x text-muted"></i>
                                    <p class="text-muted mt-2">Loading tank monitoring...</p>
                                </div>
                            </div>
                        </div>
                        <!-- Sales Summary Tab -->
                        <div class="tab-pane fade" id="sales-summary" role="tabpanel" aria-labelledby="sales-summary-tab">
                            <div class="tab-content-loader" data-tab="sales-summary">
                                <div class="py-5 text-center">
                                    <i class="fas fa-spinner fa-spin fa-2x text-muted"></i>
                                    <p class="text-muted mt-2">Loading sales summary...</p>
                                </div>
                            </div>
                        </div>
                        <!-- Analytical Sales Tab -->
                        <div class="tab-pane fade" id="analytical-sales" role="tabpanel" aria-labelledby="analytical-sales-tab">
                            <div class="tab-content-loader" data-tab="analytical-sales">
                                <div class="py-5 text-center">
                                    <i class="fas fa-spinner fa-spin fa-2x text-muted"></i>
                                    <p class="text-muted mt-2">Loading analytical sales...</p>
                                </div>
                            </div>
                        </div>
                        <!-- Shift Summary Tab -->
                        <div class="tab-pane fade" id="shift-summary" role="tabpanel" aria-labelledby="shift-summary-tab">
                            <div class="tab-content-loader" data-tab="shift-summary">
                                <div class="py-5 text-center">
                                    <i class="fas fa-spinner fa-spin fa-2x text-muted"></i>
                                    <p class="text-muted mt-2">Loading shift summary...</p>
                                </div>
                            </div>
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

            // Track which tabs have been loaded
            const loadedTabs = new Set();

            /**
             * Load tab content via AJAX
             */
            function loadTabContent(tabName, loaderDiv) {
                console.log('loadTabContent called for:', tabName);

                // Show loading indicator
                loaderDiv.html(
                    '<div class="text-center py-5">' +
                    '<i class="fas fa-spinner fa-spin fa-2x text-muted"></i>' +
                    '<p class="mt-2 text-muted">Loading ' + tabName.replace('-', ' ') + '...</p>' +
                    '</div>'
                );

                // Make AJAX request to load partial
                const baseUrl = '{{ url('hos-reports/partial') }}';
                const partialUrl = baseUrl + '/' + tabName;
                console.log('Making AJAX request to:', partialUrl);

                $.ajax({
                    url: partialUrl,
                    method: 'GET',
                    success: function(response) {
                        console.log('AJAX success for tab:', tabName);
                        // Replace loader with content
                        loaderDiv.html(response);

                        // Execute any script tags in the loaded content
                        loaderDiv.find('script').each(function() {
                            if ($(this).attr('src')) {
                                // If script has src, create new script tag and append
                                const script = document.createElement('script');
                                script.src = $(this).attr('src');
                                script.async = false;
                                document.body.appendChild(script);
                            } else {
                                // Inline script - execute it
                                try {
                                    eval($(this).html());
                                } catch (e) {
                                    console.error('Error executing script:', e);
                                }
                            }
                        });

                        // Mark as loaded
                        loadedTabs.add(tabName);

                        // Trigger custom event for any additional initialization
                        $(document).trigger('tabContentLoaded', [tabName]);
                    },
                    error: function(xhr, status, error) {
                        console.error('AJAX Error loading tab content:', {
                            tab: tabName,
                            url: partialUrl,
                            status: status,
                            error: error,
                            responseText: xhr.responseText,
                            statusCode: xhr.status
                        });
                        loaderDiv.html(
                            '<div class="alert alert-danger">' +
                            '<i class="fas fa-exclamation-triangle"></i> ' +
                            'Error loading content. Status: ' + xhr.status + '. Please check the console for details.' +
                            '</div>'
                        );
                    }
                });
            }

            /**
             * Extract tab name from href (handles both #tab and full URLs)
             */
            function extractTabName(href) {
                if (!href) return null;

                // If href starts with #, remove it
                if (href.startsWith('#')) {
                    return href.substring(1);
                }

                // If href contains #, extract the part after #
                const hashIndex = href.indexOf('#');
                if (hashIndex !== -1) {
                    return href.substring(hashIndex + 1);
                }

                // If no # found, return null
                return null;
            }

            // Load tab content when tab is shown
            $('a[data-toggle="tab"]').on('shown.bs.tab', function(e) {
                const href = $(e.target).attr('href');
                const targetTab = extractTabName(href);

                if (targetTab) {
                    const loaderDiv = $('#' + targetTab).find('.tab-content-loader');

                    // Only load if not already loaded
                    if (!loadedTabs.has(targetTab) && loaderDiv.length) {
                        loadTabContent(targetTab, loaderDiv);
                    }
                }
            });

            // Load the active tab (transactions) immediately on page load
            // Direct approach: load transactions tab by default
            function loadActiveTab() {
                // First try to find active tab from nav link
                const activeTabLink = $('.nav-link.active');
                let tabName = 'transactions'; // Default to transactions

                if (activeTabLink.length) {
                    const activeTabHref = activeTabLink.attr('href');
                    const extractedTabName = extractTabName(activeTabHref);
                    if (extractedTabName) {
                        tabName = extractedTabName;
                    }
                }

                console.log('Loading active tab:', tabName);
                const tabPane = document.getElementById(tabName);

                if (tabPane) {
                    const loaderDiv = $(tabPane).find('.tab-content-loader');
                    if (loaderDiv.length) {
                        console.log('Loader div found, loading content for:', tabName);
                        loadTabContent(tabName, loaderDiv);
                    } else {
                        console.error('Loader div not found in tab pane:', tabName);
                    }
                } else {
                    console.error('Tab pane not found:', tabName);
                }
            }

            // Load immediately and also try after a short delay as fallback
            loadActiveTab();
        });
    </script>
@endpush
