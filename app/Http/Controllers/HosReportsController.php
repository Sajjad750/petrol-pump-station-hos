<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Barryvdh\Snappy\Facades\SnappyPdf;
use Illuminate\Support\Collection;
use Illuminate\Database\Eloquent\Builder;
use Maatwebsite\Excel\Facades\Excel;
use App\Models\PumpTransaction;
use App\Models\Station;
use App\Models\Pump;
use App\Models\TankInventory;
use App\Models\TankDelivery;
use App\Models\TankMeasurement;
use App\Models\Shift;
use App\Models\PaymentModeWiseSummary;
use App\Models\ProductWiseSummary;

class HosReportsController extends Controller
{
    public function __invoke()
    {
        return view('hos-reports.index');
    }

    /**
     * Load partial view for a specific tab.
     */
    public function loadPartial(string $tab): \Illuminate\View\View|\Illuminate\Http\Response
    {
        $validTabs = [
            'transactions',
            'sales',
            'tank-inventory',
            'tank-deliveries',
            'tank-monitoring',
            'sales-summary',
            'analytical-sales',
            'shift-summary',
        ];

        if (!in_array($tab, $validTabs)) {
            abort(404, 'Tab not found');
        }

        // Render the partial view using a wrapper that includes stacks
        // This allows @push directives in partials to be captured
        return view('hos-reports.partial-wrapper', [
            'tab' => $tab,
        ]);
    }

    /**
     * Get stations for dropdown.
     */
    public function getStations()
    {
        return response()->json([
            'stations' => Station::select('id', 'site_name')->orderBy('site_name')->get(),
        ]);
    }

    /**
     * Get fuel grades for dropdown.
     */
    public function getFuelGrades()
    {
        $fuelGrades = \App\Models\FuelGrade::whereNotNull('name')
            ->where('name', '!=', '')
            ->selectRaw('MIN(id) as id, name')
            ->groupBy('name')
            ->orderBy('order_number')
            ->orderBy('name')
            ->get();

        return response()->json([
            'fuel_grades' => $fuelGrades,
        ]);
    }

    /**
     * Get pumps for dropdown.
     */
    public function getPumps(Request $request)
    {
        $query = Pump::query()
            ->select('pts_pump_id')
            ->distinct()
            ->whereNotNull('pts_pump_id');

        // Filter by station if provided
        if ($request->filled('station_id')) {
            $query->where('station_id', $request->input('station_id'));
        }

        $pumps = $query->orderBy('pts_pump_id')->get()->map(function ($pump) {
            return [
                'id' => $pump->pts_pump_id,
                'name' => $pump->pts_pump_id,
            ];
        });

        return response()->json([
            'pumps' => $pumps,
        ]);
    }

    /**
     * Get transactions data for DataTable.
     */
    public function getTransactionsData(Request $request)
    {
        // Define sortable columns mapping (column index => database column)
        $columns = [
            0 => 'stations.pts_id', // Site ID
            1 => 'stations.site_name', // Site Name
            2 => 'transaction_number', // Trans ID
            3 => 'date_time_end', // Trans Date (end time)
            4 => 'pts_pump_id', // Pump
            5 => 'pts_nozzle_id', // Nozzle
            6 => 'fuel_grades.name', // Product
            7 => 'price', // Unit Price
            8 => 'volume', // Volume
            9 => 'amount', // Amount
            10 => 'starting_totalizer', // Start Totalizer
            11 => 'total_volume', // End Totalizer
            12 => 'mode_of_payment', // Payment Mode
            13 => 'pts_users.login', // Attendant
            14 => 'date_time_start', // Start Time
            15 => 'date_time_end', // End Time
            16 => 'tag', // Mobile No (not sortable)
            17 => 'tag', // Vehicle No (not sortable)
            18 => 'created_at', // HOS Received Date/Time
        ];

        $length = $request->input('length', 10);
        $start = $request->input('start', 0);
        $orderColumnIndex = $request->input('order.0.column', 3);
        $orderDir = $request->input('order.0.dir', 'desc');
        $orderColumn = $columns[$orderColumnIndex] ?? 'date_time_end';

        $filters = $request->only([
            'from_date',
            'to_date',
            'from_time',
            'to_time',
            'station_id',
            'pump_id',
            'mode_of_payment',
            'product_id',
        ]);

        // Eager load relationships
        $query = $this->baseTransactionsQuery(true);
        $this->applyTransactionFilters($query, $filters);

        // Get total count without search (but with base filters)
        $totalQuery = $this->baseTransactionsQuery(true);
        $this->applyTransactionFilters($totalQuery, $filters);
        $totalData = $totalQuery->count();

        // Global search for all columns
        if ($request->has('search') && $request->input('search.value') != '') {
            $search = $request->input('search.value');
            $query->where(function ($q) use ($search) {
                $q->where('stations.pts_id', 'like', "%{$search}%")
                    ->orWhere('stations.site_name', 'like', "%{$search}%")
                    ->orWhere('pump_transactions.transaction_number', 'like', "%{$search}%")
                    ->orWhere('pump_transactions.pts_pump_id', 'like', "%{$search}%")
                    ->orWhere('pump_transactions.pts_nozzle_id', 'like', "%{$search}%")
                    ->orWhere('fuel_grades.name', 'like', "%{$search}%")
                    ->orWhere('pump_transactions.mode_of_payment', 'like', "%{$search}%")
                    ->orWhere('pump_transactions.tag', 'like', "%{$search}%")
                    ->orWhereRaw('pts_users.login like ?', ["%{$search}%"]);

                // Also search numeric fields if search term is numeric
                if (is_numeric($search)) {
                    $q->orWhere('pump_transactions.price', '=', $search)
                        ->orWhere('pump_transactions.volume', '=', $search)
                        ->orWhere('pump_transactions.amount', '=', $search)
                        ->orWhere('pump_transactions.starting_totalizer', '=', $search)
                        ->orWhere('pump_transactions.total_volume', '=', $search)
                        ->orWhere('pump_transactions.pts_user_id', '=', $search);
                }
            });
        }

        // Get count before pagination but after filters
        $totalFiltered = $query->count();

        // Ordering - handle joined table columns
        if (str_contains($orderColumn, '.')) {
            $query->orderBy($orderColumn, $orderDir);
        } else {
            $query->orderBy('pump_transactions.'.$orderColumn, $orderDir);
        }

        // Pagination
        $data = $query->offset($start)
            ->limit($length)
            ->get();

        // Map data to match DataTable column structure
        $data = $data->map(function ($transaction) {
            // Parse tag field for mobile and vehicle ID
            $tag = $transaction->tag ?? '';
            $mobile = '';
            $vehicleId = '';

            if ($tag) {
                // Try to decode as JSON first
                $decodedTag = json_decode($tag, true);

                if (is_array($decodedTag)) {
                    // If it's JSON, extract mobile and vehicle_id
                    $mobile = $decodedTag['mobile'] ?? $decodedTag['phone'] ?? '';
                    $vehicleId = $decodedTag['vehicle_id'] ?? $decodedTag['vehicleId'] ?? '';
                }

                // If not JSON or fields are empty, parse as text
                if (empty($mobile) || empty($vehicleId)) {
                    $lines = explode("\n", $tag);

                    foreach ($lines as $line) {
                        $line = trim($line);

                        // Match mobile numbers (10-15 digits, may start with +)
                        if (preg_match('/^\+?\d{10,15}$/', $line)) {
                            if (empty($mobile)) {
                                $mobile = $line;
                            }
                        }
                        // Match "Vehicle ID: XXX" or "vehicle_id: XXX" patterns
                        elseif (preg_match('/vehicle[_\s]*id[_\s]*:?\s*([^\s\n]+)/i', $line, $matches)) {
                            $vehicleId = trim($matches[1]);
                        }
                        // Match alphanumeric codes that look like vehicle IDs
                        elseif (empty($vehicleId) && preg_match('/^[a-z0-9]{6,15}$/i', $line) && ! preg_match('/^\+?\d+$/', $line)) {
                            $vehicleId = $line;
                        }
                    }

                    // If no mobile found in lines, check if tag itself is a mobile number
                    if (empty($mobile)) {
                        $trimmedTag = trim($tag);

                        if (preg_match('/^\+?\d{10,15}$/', $trimmedTag)) {
                            $mobile = $trimmedTag;
                        } else {
                            // Use first part as mobile if it contains numbers
                            $parts = preg_split('/[\s\n]+/', $trimmedTag);

                            foreach ($parts as $part) {
                                if (preg_match('/\+?\d{10,15}/', $part, $matches)) {
                                    $mobile = $matches[0];

                                    break;
                                }
                            }

                            // Fallback: use tag as mobile if still empty
                            if (empty($mobile)) {
                                $mobile = $trimmedTag;
                            }
                        }
                    }
                }
            }

            // Get attendant name and username
            //            $attenName = '';
            //            $attenUsername = '';
            //
            //            if ($transaction->ptsUser) {
            //                $attenUsername = $transaction->ptsUser->login ?? '';
            //                // For display, use login as name (or first name if available)
            //                $attenName = $attenUsername;
            //            } elseif ($transaction->pts_user_id) {
            //                $attenName = (string) $transaction->pts_user_id;
            //            }

            return [
                'site_id' => $transaction->site_id ?? '',
                'site_name' => $transaction->site_name ?? '',
                'transaction_id' => $transaction->transaction_number ?? '',
                // Trans Date uses end time (date_time_end) not start time
                'trans_date' => $transaction->date_time_end ? $transaction->date_time_end->setTimezone('Asia/Riyadh')->format('Y-m-d H:i:s') : '',
                'pump' => $transaction->pts_pump_id ?? '',
                'nozzle' => $transaction->pts_nozzle_id ?? '',
                'product' => $transaction->fuel_grade_name ?? '',
                'unit_price' => $transaction->price ?? 0,
                'volume' => $transaction->volume ?? 0,
                'amount' => $transaction->amount ?? 0,
                'start_totalizer' => $transaction->starting_totalizer ?? 0,
                'end_totalizer' => $transaction->total_volume ?? 0,
                'payment_mode' => ucfirst($transaction->mode_of_payment ?? ''),
                'attendant' => $transaction->attendant_login ?? '',
                'start_time' => $transaction->date_time_start ? $transaction->date_time_start->setTimezone('Asia/Riyadh')->format('Y-m-d H:i:s') : '',
                'end_time' => $transaction->date_time_end ? $transaction->date_time_end->setTimezone('Asia/Riyadh')->format('Y-m-d H:i:s') : '',
                'mobile_no' => $mobile,
                'vehicle_no' => $vehicleId,
                'hos_received_time' => $transaction->created_at ? $transaction->created_at->setTimezone('Asia/Riyadh')->format('Y-m-d H:i:s') : '',
            ];
        });

        $json_data = [
            'draw' => intval($request->input('draw')),
            'recordsTotal' => $totalData,
            'recordsFiltered' => $totalFiltered,
            'data' => $data,
        ];

        return response()->json($json_data);
    }

    /**
     * Get sales data for DataTable.
     */
    public function sales(Request $request)
    {
        $query = PumpTransaction::query()
            ->leftJoin('stations', 'pump_transactions.station_id', '=', 'stations.id')
            ->leftJoin('fuel_grades', function ($join) {
                $join->on('pump_transactions.pts_fuel_grade_id', '=', 'fuel_grades.pts_fuel_grade_id')
                     ->whereColumn('pump_transactions.station_id', 'fuel_grades.station_id');
            });

        // Date and Time Filters
        if ($request->filled('from_date') || $request->filled('to_date') || $request->filled('from_time') || $request->filled('to_time')) {
            $from_date = $request->input('from_date');
            $to_date = $request->input('to_date');
            $from_time = $request->input('from_time') ?: '00:00:00';
            $to_time = $request->input('to_time') ?: '23:59:59';

            if ($from_date && $to_date) {
                // Both dates provided
                $from_datetime = $from_date.' '.$from_time;
                $to_datetime = $to_date.' '.$to_time;
                $query->whereBetween('pump_transactions.date_time_start', [$from_datetime, $to_datetime]);
            } elseif ($from_date) {
                // Only from_date provided
                $from_datetime = $from_date.' '.$from_time;
                $query->where('pump_transactions.date_time_start', '>=', $from_datetime);
            } elseif ($to_date) {
                // Only to_date provided
                $to_datetime = $to_date.' '.$to_time;
                $query->where('pump_transactions.date_time_start', '<=', $to_datetime);
            }
        }

        // Station Filter
        if ($request->filled('station_id')) {
            $query->where('pump_transactions.station_id', $request->input('station_id'));
        }

        // Global search
        if ($request->has('search') && $request->input('search.value') != '') {
            $search = $request->input('search.value');
            $query->where(function ($q) use ($search) {
                $q->where('stations.site_name', 'like', "%{$search}%")
                    ->orWhere('stations.pts_id', 'like', "%{$search}%")
                    ->orWhere('pump_transactions.transaction_number', 'like', "%{$search}%")
                    ->orWhere('pump_transactions.pts_pump_id', 'like', "%{$search}%")
                    ->orWhere('pump_transactions.pts_nozzle_id', 'like', "%{$search}%")
                    ->orWhere('fuel_grades.name', 'like', "%{$search}%")
                    ->orWhere('pump_transactions.mode_of_payment', 'like', "%{$search}%");

                // Also search numeric fields if search term is numeric
                if (is_numeric($search)) {
                    $q->orWhere('pump_transactions.price', '=', $search)
                        ->orWhere('pump_transactions.volume', '=', $search)
                        ->orWhere('pump_transactions.amount', '=', $search);
                }
            });
        }

        // Get total count before filters
        $totalData = PumpTransaction::count();

        // Get filtered count - clone query to count without pagination
        $countQuery = clone $query;
        $totalFiltered = $countQuery->select(DB::raw('count(distinct pump_transactions.id) as total'))->value('total') ?? 0;

        // Pagination
        $start = $request->input('start', 0);
        $length = $request->input('length', 10);

        // Ordering
        $orderColumnIndex = $request->input('order.0.column', 3);
        $orderColumns = ['site_id', 'site_name', 'transaction_id', 'trans_date', 'pump', 'nozzle', 'product', 'unit_price', 'volume', 'amount', 'payment_mode', 'hos_received_time'];
        $orderColumn = $orderColumns[$orderColumnIndex] ?? 'trans_date';
        $orderDir = $request->input('order.0.dir', 'desc');

        if ($orderColumn === 'site_id' || $orderColumn === 'site_name') {
            $query->orderBy('stations.site_name', $orderDir);
        } elseif ($orderColumn === 'product') {
            $query->orderBy('fuel_grades.order_number')->orderBy('fuel_grades.name', $orderDir);
        } elseif ($orderColumn === 'trans_date') {
            $query->orderBy('pump_transactions.date_time_end', $orderDir);
        } elseif (in_array($orderColumn, ['transaction_id', 'pump', 'nozzle', 'unit_price', 'volume', 'amount', 'payment_mode'])) {
            $columnMap = [
                'transaction_id' => 'transaction_number',
                'pump' => 'pts_pump_id',
                'nozzle' => 'pts_nozzle_id',
                'unit_price' => 'price',
                'volume' => 'volume',
                'amount' => 'amount',
                'payment_mode' => 'mode_of_payment',
            ];
            $query->orderBy('pump_transactions.'.$columnMap[$orderColumn], $orderDir);
        } else {
            $query->orderBy('pump_transactions.created_at', $orderDir);
        }

        $data = $query->select([
                'pump_transactions.*',
                'stations.site_name',
                'stations.pts_id as site_ref',
                'fuel_grades.name as fuel_grade_name',
            ])
            ->skip($start)
            ->limit($length)
            ->get();

        // Map data to match DataTable column structure
        $data = $data->map(function ($transaction) {
            // Format trans_date (end time)
            $transDate = '';

            if ($transaction->date_time_end) {
                $transDate = is_string($transaction->date_time_end)
                    ? $transaction->date_time_end
                    : $transaction->date_time_end->setTimezone('Asia/Riyadh')->format('Y-m-d H:i:s');
            }

            return [
                'site_id' => $transaction->site_ref ?? '',
                'site_name' => $transaction->site_name ?? '',
                'transaction_id' => $transaction->transaction_number ?? '',
                'trans_date' => $transDate,
                'pump' => $transaction->pts_pump_id ?? '',
                'nozzle' => $transaction->pts_nozzle_id ?? '',
                'product' => $transaction->fuel_grade_name ?? '',
                'unit_price' => $transaction->price ?? 0,
                'volume' => $transaction->volume ?? 0,
                'amount' => $transaction->amount ?? 0,
                'payment_mode' => ucfirst($transaction->mode_of_payment ?? ''),
                'hos_received_time' => $transaction->created_at ? $transaction->created_at->setTimezone('Asia/Riyadh')->format('Y-m-d H:i:s') : '',
            ];
        });

        $json_data = [
            'draw' => intval($request->input('draw')),
            'recordsTotal' => $totalData,
            'recordsFiltered' => $totalFiltered,
            'data' => $data,
        ];

        return response()->json($json_data);
    }

    /**
     * Export transactions to Excel.
     */
    public function exportExcel(Request $request)
    {
        // TODO: Implement Excel export
        return response()->json(['message' => 'Excel export not implemented yet']);
    }

    /**
     * Export transactions to CSV.
     */
    public function exportCsv(Request $request)
    {
        $tab = $request->input('tab');

        if ($tab === 'shift-summary') {
            return $this->exportShiftSummaryCsv($request);
        }

        if ($tab === 'transactions') {
            return $this->exportTransactionsCsv($request);
        }

        return response()->json(['message' => 'CSV export for this tab not implemented yet']);
    }

    /**
     * Export transactions to PDF.
     */
    public function exportPdf(Request $request)
    {
        $tab = $request->input('tab');

        if ($tab === 'shift-summary') {
            return $this->exportShiftSummaryPdf($request);
        }

        return $this->exportTransactionsPdf($request);
    }

    /**
     * Export shift summary to CSV.
     */
    protected function exportShiftSummaryCsv(Request $request)
    {
        // Get shift summary data using the same logic as getShiftSummary
        $viewMode = $request->input('view_mode', 'summary');

        // Build shift query
        $shiftQuery = Shift::query();

        if ($request->filled('station_id')) {
            $shiftQuery->where('station_id', $request->input('station_id'));
        }

        $fromBosShiftId = $request->input('from_bos_shift_id');
        $toBosShiftId = $request->input('to_bos_shift_id');
        $from_date = $request->input('from_date');
        $to_date = $request->input('to_date');
        $from_time = $request->input('from_time');
        $to_time = $request->input('to_time');

        if ($from_date && $from_time) {
            $windowStart = Carbon::parse($from_date.' '.$from_time);
            $shiftQuery->where('start_time', '>=', $windowStart);
        }

        if ($to_date && $to_time) {
            $windowEnd = Carbon::parse($to_date.' '.$to_time);
            $shiftQuery->where('start_time', '<=', $windowEnd);
        }

        if ($fromBosShiftId && $toBosShiftId) {
            $shiftQuery->whereBetween('bos_shift_id', [min($fromBosShiftId, $toBosShiftId), max($fromBosShiftId, $toBosShiftId)]);
        }

        $shifts = $shiftQuery->with('station')->orderBy('start_time')->get();

        if ($shifts->isEmpty()) {
            return response()->json(['message' => 'No shifts found for the selected filters'], 404);
        }

        // Get station info
        $station = Station::find($request->input('station_id'));
        $stationName = $station ? $station->site_name : 'All Stations';

        // Fetch transactions for these shifts
        $transactions = PumpTransaction::query()
            ->whereIn('station_id', $shifts->pluck('station_id')->unique())
            ->when($request->filled('station_id'), function ($q) use ($request) {
                $q->where('station_id', $request->input('station_id'));
            })
            ->when($from_date && $from_time, function ($q) use ($from_date, $from_time) {
                $q->where('date_time_start', '>=', $from_date.' '.$from_time);
            })
            ->when($to_date && $to_time, function ($q) use ($to_date, $to_time) {
                $q->where('date_time_end', '<=', $to_date.' '.$to_time);
            })
            ->with(['fuelGrade', 'ptsUser'])
            ->get();

        // Prepare CSV data
        $csvData = [];

        // Header row
        $csvData[] = ['Shift Summary Report'];
        $csvData[] = ['Station:', $stationName];
        $csvData[] = ['Period:', ($from_date ?? 'N/A').' to '.($to_date ?? 'N/A')];
        $csvData[] = ['Time Range:', ($from_time ?? '00:00:00').' to '.($to_time ?? '23:59:59')];
        $csvData[] = [];

        // Payment Mode Wise Summary
        $csvData[] = ['Payment Mode Wise Summary'];
        $csvData[] = ['MOP', 'Volume (L)', 'Amount (SAR)'];

        $paymentModeSummary = $transactions->groupBy('mode_of_payment')->map(function ($group) {
            return [
                'volume' => $group->sum('volume'),
                'amount' => $group->sum('amount'),
            ];
        });

        $totalPaymentVolume = 0;
        $totalPaymentAmount = 0;

        foreach ($paymentModeSummary as $mop => $data) {
            $csvData[] = [
                ucfirst($mop ?: 'N/A'),
                number_format($data['volume'], 2),
                number_format($data['amount'], 2),
            ];
            $totalPaymentVolume += $data['volume'];
            $totalPaymentAmount += $data['amount'];
        }

        $csvData[] = ['Total', number_format($totalPaymentVolume, 2), number_format($totalPaymentAmount, 2)];
        $csvData[] = [];

        // Product Wise Summary
        $csvData[] = ['Product Wise Summary'];
        $csvData[] = ['Product', 'TXN Volume', 'Amount (SAR)'];

        $productSummary = $transactions->groupBy(function ($txn) {
            return $txn->fuelGrade ? $txn->fuelGrade->name : 'Unknown';
        })->map(function ($group) {
            return [
                'volume' => $group->sum('volume'),
                'amount' => $group->sum('amount'),
            ];
        });

        // Sort products in correct order
        $productOrder = ['Gasoline91' => 1, 'Gasoline95' => 2, 'Gasoline98' => 3, 'Diesel' => 4];
        $sortedProducts = $productSummary->sortBy(function ($data, $product) use ($productOrder) {
            return $productOrder[$product] ?? 999;
        });

        $totalProductVolume = 0;
        $totalProductAmount = 0;

        foreach ($sortedProducts as $product => $data) {
            $csvData[] = [
                $product,
                number_format($data['volume'], 2),
                number_format($data['amount'], 2),
            ];
            $totalProductVolume += $data['volume'];
            $totalProductAmount += $data['amount'];
        }

        $csvData[] = ['Total', number_format($totalProductVolume, 2), number_format($totalProductAmount, 2)];
        $csvData[] = [];

        // Pump Wise Summary
        $csvData[] = ['Pump Wise Summary'];
        $csvData[] = ['Product', 'Pump No', 'Nozzle No', 'Start Totalizer', 'End Totalizer', 'Totalizer Volume', 'TXN Volume', 'Amount (SAR)'];

        $pumpSummary = $transactions->groupBy(function ($txn) {
            $product = $txn->fuelGrade ? $txn->fuelGrade->name : 'Unknown';
            $pump = $txn->pts_pump_id ?? 'N/A';
            $nozzle = $txn->pts_nozzle_id ?? 'N/A';

            return $product.'|'.$pump.'|'.$nozzle;
        })->map(function ($group) {
            return [
                'product' => $group->first()->fuelGrade ? $group->first()->fuelGrade->name : 'Unknown',
                'pump' => $group->first()->pts_pump_id ?? 'N/A',
                'nozzle' => $group->first()->pts_nozzle_id ?? 'N/A',
                'start_totalizer' => $group->min('starting_totalizer') ?? 0,
                'end_totalizer' => $group->max('total_volume') ?? 0,
                'totalizer_volume' => ($group->max('total_volume') ?? 0) - ($group->min('starting_totalizer') ?? 0),
                'txn_volume' => $group->sum('volume'),
                'amount' => $group->sum('amount'),
            ];
        });

        // Sort by product, then pump, then nozzle
        $sortedPumpSummary = $pumpSummary->sortBy(function ($item) use ($productOrder) {
            $productOrderVal = $productOrder[$item['product']] ?? 999;
            $pumpNum = is_numeric($item['pump']) ? (int) $item['pump'] : 9999;
            $nozzleNum = is_numeric($item['nozzle']) ? (int) $item['nozzle'] : 9999;

            return sprintf('%03d%04d%04d', $productOrderVal, $pumpNum, $nozzleNum);
        });

        $totalTotalizerVolume = 0;
        $totalTxnVolume = 0;
        $totalPumpAmount = 0;

        foreach ($sortedPumpSummary as $item) {
            $csvData[] = [
                $item['product'],
                $item['pump'],
                $item['nozzle'],
                number_format($item['start_totalizer'], 2),
                number_format($item['end_totalizer'], 2),
                number_format($item['totalizer_volume'], 2),
                number_format($item['txn_volume'], 2),
                number_format($item['amount'], 2),
            ];
            $totalTotalizerVolume += $item['totalizer_volume'];
            $totalTxnVolume += $item['txn_volume'];
            $totalPumpAmount += $item['amount'];
        }

        $csvData[] = [
            'Total',
            '',
            '',
            '',
            '',
            number_format($totalTotalizerVolume, 2),
            number_format($totalTxnVolume, 2),
            number_format($totalPumpAmount, 2),
        ];

        // Generate CSV file
        $filename = 'shift_summary_'.$stationName.'_'.date('Y-m-d_His').'.csv';

        $callback = function () use ($csvData) {
            $file = fopen('php://output', 'w');

            foreach ($csvData as $row) {
                fputcsv($file, $row);
            }

            fclose($file);
        };

        return response()->stream($callback, 200, [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => 'attachment; filename="'.$filename.'"',
        ]);
    }

    /**
     * Export transactions to CSV.
     */
    protected function exportTransactionsCsv(Request $request)
    {
        $filters = $request->only([
            'from_date',
            'to_date',
            'from_time',
            'to_time',
            'station_id',
            'pump_id',
            'mode_of_payment',
            'product_id',
        ]);

        // Build query with same logic as getTransactionsData
        $query = $this->baseTransactionsQuery(true);
        $this->applyTransactionFilters($query, $filters);

        // Order by transaction date descending
        $query->orderBy('pump_transactions.date_time_end', 'desc');

        // Get all transactions (no pagination for export)
        $transactions = $query->get();

        // Prepare CSV data
        $csvData = [];

        // Header row
        $csvData[] = [
            'Site ID',
            'Site Name',
            'Transaction ID',
            'Trans Date',
            'Pump',
            'Nozzle',
            'Product',
            'Unit Price',
            'Volume',
            'Amount',
            'Start Totalizer',
            'End Totalizer',
            'Payment Mode',
            'Attendant',
            'Start Time',
            'End Time',
            'Mobile No',
            'Vehicle No',
            'HOS Received Date/Time',
        ];

        // Data rows
        foreach ($transactions as $transaction) {
            // Parse tag field for mobile and vehicle ID
            $tag = $transaction->tag ?? '';
            $mobile = '';
            $vehicleId = '';

            if ($tag) {
                // Try to decode as JSON first
                $decodedTag = json_decode($tag, true);

                if (is_array($decodedTag)) {
                    $mobile = $decodedTag['mobile'] ?? $decodedTag['phone'] ?? '';
                    $vehicleId = $decodedTag['vehicle_id'] ?? $decodedTag['vehicleId'] ?? '';
                }

                // If not JSON or fields are empty, parse as text
                if (empty($mobile) || empty($vehicleId)) {
                    $lines = explode("\n", $tag);

                    foreach ($lines as $line) {
                        $line = trim($line);

                        if (preg_match('/^\+?\d{10,15}$/', $line)) {
                            if (empty($mobile)) {
                                $mobile = $line;
                            }
                        } elseif (preg_match('/vehicle[_\s]*id[_\s]*:?\s*([^\s\n]+)/i', $line, $matches)) {
                            $vehicleId = trim($matches[1]);
                        } elseif (empty($vehicleId) && preg_match('/^[a-z0-9]{6,15}$/i', $line) && ! preg_match('/^\+?\d+$/', $line)) {
                            $vehicleId = $line;
                        }
                    }

                    if (empty($mobile)) {
                        $trimmedTag = trim($tag);

                        if (preg_match('/^\+?\d{10,15}$/', $trimmedTag)) {
                            $mobile = $trimmedTag;
                        } else {
                            $parts = preg_split('/[\s\n]+/', $trimmedTag);

                            foreach ($parts as $part) {
                                if (preg_match('/\+?\d{10,15}/', $part, $matches)) {
                                    $mobile = $matches[0];

                                    break;
                                }
                            }

                            if (empty($mobile)) {
                                $mobile = $trimmedTag;
                            }
                        }
                    }
                }
            }

            // Format dates
            $transDate = $transaction->date_time_end
                ? ($transaction->date_time_end instanceof Carbon
                    ? $transaction->date_time_end->setTimezone('Asia/Riyadh')->format('Y-m-d H:i:s')
                    : Carbon::parse($transaction->date_time_end)->setTimezone('Asia/Riyadh')->format('Y-m-d H:i:s'))
                : '';

            $startTime = $transaction->date_time_start
                ? ($transaction->date_time_start instanceof Carbon
                    ? $transaction->date_time_start->setTimezone('Asia/Riyadh')->format('Y-m-d H:i:s')
                    : Carbon::parse($transaction->date_time_start)->setTimezone('Asia/Riyadh')->format('Y-m-d H:i:s'))
                : '';

            $endTime = $transaction->date_time_end
                ? ($transaction->date_time_end instanceof Carbon
                    ? $transaction->date_time_end->setTimezone('Asia/Riyadh')->format('Y-m-d H:i:s')
                    : Carbon::parse($transaction->date_time_end)->setTimezone('Asia/Riyadh')->format('Y-m-d H:i:s'))
                : '';

            $hosReceivedTime = $transaction->created_at
                ? $transaction->created_at->setTimezone('Asia/Riyadh')->format('Y-m-d H:i:s')
                : '';

            $csvData[] = [
                $transaction->site_id ?? '',
                $transaction->site_name ?? '',
                $transaction->transaction_number ?? '',
                $transDate,
                $transaction->pts_pump_id ?? '',
                $transaction->pts_nozzle_id ?? '',
                $transaction->fuel_grade_name ?? '',
                $transaction->price !== null ? number_format((float) $transaction->price, 2) : '0.00',
                $transaction->volume !== null ? number_format((float) $transaction->volume, 2) : '0.00',
                $transaction->amount !== null ? number_format((float) $transaction->amount, 2) : '0.00',
                $transaction->starting_totalizer !== null ? number_format((float) $transaction->starting_totalizer, 2) : '0.00',
                $transaction->total_volume !== null ? number_format((float) $transaction->total_volume, 2) : '0.00',
                ucfirst($transaction->mode_of_payment ?? ''),
                $transaction->attendant_login ?? '',
                $startTime,
                $endTime,
                $mobile,
                $vehicleId,
                $hosReceivedTime,
            ];
        }

        // Generate CSV file
        $filename = 'transactions_'.date('Y-m-d_His').'.csv';

        $callback = function () use ($csvData) {
            $file = fopen('php://output', 'w');

            foreach ($csvData as $row) {
                fputcsv($file, $row);
            }

            fclose($file);
        };

        return response()->stream($callback, 200, [
            'Content-Type' => 'text/csv; charset=UTF-8',
            'Content-Disposition' => 'attachment; filename="'.$filename.'"',
        ]);
    }

    /**
     * Export pump transactions to PDF.
     */
    protected function exportTransactionsPdf(Request $request)
    {
        try {
            $filters = $request->only([
                'from_date',
                'to_date',
                'from_time',
                'to_time',
                'station_id',
                'pump_id',
                'mode_of_payment',
                'product_id',
            ]);

            $filename = 'pump_transactions_' . now()->format('Y-m-d_His') . '.pdf';

            // Dispatch the PDF generation job with user ID for notifications
            $userId = Auth::id();
            \App\Jobs\GeneratePumpTransactionsPdf::dispatch($filters, $filename, $userId);

            if ($request->expectsJson() || $request->ajax()) {
                return response()->json([
                    'success' => true,
                    'message' => 'PDF export started successfully. You will receive a notification when the file is ready for download.',
                    'download_url' => route('hos-reports.download', ['filename' => $filename]),
                ]);
            }

            return back()->with('success', 'PDF export started successfully. You will receive a notification when the file is ready for download.');
        } catch (\Exception $e) {
            Log::error('Error dispatching PDF export job: ' . $e->getMessage(), [
                'filters' => $request->all(),
                'exception' => $e,
            ]);

            if ($request->expectsJson() || $request->ajax()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Error initiating PDF export. Please try again or contact support.',
                ], 500);
            }

            return back()->with('error', 'Error initiating PDF export. Please try again or contact support.');
        }
    }

    /**
     * Export sales to PDF using Snappy.
     */
    public function exportSalesPdf(Request $request): \Illuminate\Http\JsonResponse|\Illuminate\Http\RedirectResponse
    {
        $filters = $request->only([
            'from_date',
            'to_date',
            'from_time',
            'to_time',
            'station_id',
        ]);

        $filename = 'sales_' . now()->format('Y-m-d_His') . '.pdf';

        // Dispatch the same queued pattern as transactions for stability and large data
        $userId = Auth::id();
        \App\Jobs\GeneratePumpTransactionsPdf::dispatch($filters, $filename, $userId);

        if ($request->expectsJson() || $request->ajax()) {
            return response()->json([
                'success' => true,
                'message' => 'PDF export started successfully. You will receive a notification when the file is ready for download.',
                'download_url' => route('hos-reports.download', ['filename' => $filename]),
            ]);
        }

        return back()->with('success', 'PDF export started successfully. You will receive a notification when the file is ready for download.');
    }

    /**
     * Export sales summary to PDF using Snappy.
     */
    public function exportSalesSummaryPdf(Request $request): \Illuminate\Http\JsonResponse|\Illuminate\Http\RedirectResponse
    {
        try {
            $filters = $request->only([
                'station_id',
                'from_date',
                'to_date',
                'from_time',
                'to_time',
                'product_id',
            ]);

            $filename = 'sales_summary_' . now()->format('Y-m-d_His') . '.pdf';

            $userId = Auth::id();
            \App\Jobs\GenerateSalesSummaryPdf::dispatch($filters, $filename, $userId);

            if ($request->expectsJson() || $request->ajax()) {
                return response()->json([
                    'success' => true,
                    'message' => 'PDF export started successfully. You will receive a notification when the file is ready for download.',
                    'download_url' => route('hos-reports.download', ['filename' => $filename]),
                ]);
            }

            return back()->with('success', 'PDF export started successfully. You will receive a notification when the file is ready for download.');
        } catch (\Exception $e) {
            Log::error('Error dispatching Sales Summary PDF export job: ' . $e->getMessage(), [
                'filters' => $request->all(),
                'exception' => $e,
            ]);

            if ($request->expectsJson() || $request->ajax()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Error initiating PDF export. Please try again or contact support.',
                ], 500);
            }

            return back()->with('error', 'Error initiating PDF export. Please try again or contact support.');
        }
    }

    /**
     * Export analytical sales to PDF using Snappy.
     */
    public function exportAnalyticalSalesPdf(Request $request): \Illuminate\Http\JsonResponse|\Illuminate\Http\RedirectResponse
    {
        try {
            $filters = $request->only([
                'from_date',
                'to_date',
                'from_time',
                'to_time',
                'station_id',
                'product_id',
            ]);

            $filename = 'analytical_sales_' . now()->format('Y-m-d_His') . '.pdf';

            $userId = Auth::id();
            \App\Jobs\GenerateAnalyticalSalesPdf::dispatch($filters, $filename, $userId);

            if ($request->expectsJson() || $request->ajax()) {
                return response()->json([
                    'success' => true,
                    'message' => 'PDF export started successfully. You will receive a notification when the file is ready for download.',
                    'download_url' => route('hos-reports.download', ['filename' => $filename]),
                ]);
            }

            return back()->with('success', 'PDF export started successfully. You will receive a notification when the file is ready for download.');
        } catch (\Exception $e) {
            Log::error('Error dispatching Analytical Sales PDF export job: ' . $e->getMessage(), [
                'filters' => $request->all(),
                'exception' => $e,
            ]);

            if ($request->expectsJson() || $request->ajax()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Error initiating PDF export. Please try again or contact support.',
                ], 500);
            }

            return back()->with('error', 'Error initiating PDF export. Please try again or contact support.');
        }
    }

    /**
     * Export tank inventory to PDF using Snappy.
     */
    public function exportTankInventoryPdf(Request $request)
    {
        $query = TankInventory::query()
            ->leftJoin('stations', 'tank_inventories.station_id', '=', 'stations.id')
            ->leftJoin('fuel_grades', function ($join) {
                $join->on('tank_inventories.fuel_grade_id', '=', 'fuel_grades.id')
                    ->on('tank_inventories.station_id', '=', 'fuel_grades.station_id');
            });

        // Date/time filters on created_at_bos
        if ($request->filled('from_date') || $request->filled('to_date') || $request->filled('from_time') || $request->filled('to_time')) {
            $from_date = $request->input('from_date');
            $to_date = $request->input('to_date');
            $from_time = $request->input('from_time') ?: '00:00:00';
            $to_time = $request->input('to_time') ?: '23:59:59';
            $dateColumn = 'created_at_bos';

            if ($from_date && $to_date) {
                $from_datetime = $from_date . ' ' . $from_time;
                $to_datetime = $to_date . ' ' . $to_time;
                $query->whereBetween('tank_inventories.' . $dateColumn, [$from_datetime, $to_datetime]);
            } elseif ($from_date) {
                $from_datetime = $from_date . ' ' . $from_time;
                $query->where('tank_inventories.' . $dateColumn, '>=', $from_datetime);
            } elseif ($to_date) {
                $to_datetime = $to_date . ' ' . $to_time;
                $query->where('tank_inventories.' . $dateColumn, '<=', $to_datetime);
            }
        }

        if ($request->filled('station_id')) {
            $query->where('tank_inventories.station_id', $request->input('station_id'));
        }

        if ($request->filled('fuel_grade_id')) {
            $query->where('tank_inventories.fuel_grade_id', $request->input('fuel_grade_id'));
        }

        if ($request->filled('tank')) {
            $query->where('tank_inventories.tank', $request->input('tank'));
        }

        $rows = $query
            ->orderBy('tank_inventories.created_at_bos', 'desc')
            ->select([
                'tank_inventories.*',
                'stations.site_name',
                'stations.pts_id as site_ref',
                'fuel_grades.name as fuel_grade_name_from_table',
            ])
            ->get()
            ->map(function ($inv) {
                $dateTime = '';

                if ($inv->created_at_bos) {
                    $dateTime = is_string($inv->created_at_bos)
                        ? $inv->created_at_bos
                        : $inv->created_at_bos->format('Y-m-d H:i:s');
                }

                $tankFormatted = 'T-' . str_pad($inv->tank, 2, '0', STR_PAD_LEFT);
                $productName = $inv->fuel_grade_name_from_table ?? $inv->fuel_grade_name ?? '';

                return [
                    'date_time' => $dateTime,
                    'site' => $inv->site_name ?? '',
                    'site_ref' => $inv->site_ref ?? '',
                    'tank' => $tankFormatted,
                    'product' => $productName,
                    'volume' => $inv->product_volume ?? 0,
                    'height' => $inv->product_height ?? 0,
                    'water' => $inv->water_height ?? 0,
                    'temperature' => $inv->temperature ?? 0,
                    'ullage' => $inv->product_ullage ?? 0,
                ];
            });

        $filters = [
            'From Date' => $request->input('from_date'),
            'To Date' => $request->input('to_date'),
            'From Time' => $request->input('from_time'),
            'To Time' => $request->input('to_time'),
            'Station' => $request->filled('station_id') ? Station::query()->whereKey($request->input('station_id'))->value('site_name') : null,
            'Product' => $request->input('fuel_grade_id'),
            'Tank' => $request->input('tank'),
        ];

        $pdf = SnappyPdf::loadView('hos-reports.pdf.tank-inventory', [
            'filters' => array_filter($filters),
            'records' => $rows,
            'generatedAt' => now(),
        ])->setPaper('a4', 'landscape')->setTimeout(600);

        $filename = 'tank_inventory_' . now()->format('Y-m-d_His') . '.pdf';

        $tempPath = 'temp/' . Str::uuid() . '.pdf';
        $fullTempPath = Storage::disk('local')->path($tempPath);

        $pdf->save($fullTempPath);

        return response()->download($fullTempPath, $filename, [
            'Content-Type' => 'application/pdf',
        ])->deleteFileAfterSend(true);
    }

    /**
     * Export tank deliveries to PDF using Snappy.
     */
    public function exportTankDeliveriesPdf(Request $request): \Illuminate\Http\JsonResponse|\Illuminate\Http\RedirectResponse
    {
        try {
            $filters = $request->only([
                'from_date',
                'to_date',
                'from_time',
                'to_time',
                'fuel_grade_id',
                'tank',
                'volume_min',
                'volume_max',
            ]);

            $filename = 'tank_deliveries_' . now()->format('Y-m-d_His') . '.pdf';
            $userId = Auth::id();

            \App\Jobs\GenerateTankDeliveriesPdf::dispatch($filters, $filename, $userId);

            if ($request->expectsJson() || $request->ajax()) {
                return response()->json([
                    'success' => true,
                    'message' => 'PDF export started successfully. You will receive a notification when the file is ready for download.',
                    'download_url' => route('hos-reports.download', ['filename' => $filename]),
                ]);
            }

            return back()->with('success', 'PDF export started successfully. You will receive a notification when the file is ready for download.');
        } catch (\Exception $e) {
            Log::error('Error dispatching Tank Deliveries PDF export job: ' . $e->getMessage(), [
                'filters' => $request->all(),
                'exception' => $e,
            ]);

            if ($request->expectsJson() || $request->ajax()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Error initiating PDF export. Please try again or contact support.',
                ], 500);
            }

            return back()->with('error', 'Error initiating PDF export. Please try again or contact support.');
        }
    }

    /**
     * Download exported PDF file.
     */
    public function downloadExport(Request $request, string $filename)
    {
        $filePath = 'exports/' . $filename;

        if (!Storage::disk('public')->exists($filePath)) {
            abort(404, 'File not found');
        }

        return Storage::disk('public')->download($filePath, $filename);
    }

    /**
     * Get PDF export completion notifications.
     */
    public function getNotifications(Request $request)
    {
        $user = Auth::user();

        if (!$user) {
            return response()->json(['notifications' => []]);
        }

        // Get unread notifications related to PDF exports
        $notifications = $user->unreadNotifications()
            ->where('type', 'App\\Notifications\\PdfExportCompleted')
            ->get()
            ->map(function ($notification) {
                return [
                    'id' => $notification->id,
                    'message' => $notification->data['message'],
                    'filename' => $notification->data['filename'],
                    'download_url' => $notification->data['download_url'],
                    'created_at' => $notification->created_at->diffForHumans(),
                ];
            });

        return response()->json(['notifications' => $notifications]);
    }

    /**
     * Mark notification as read.
     */
    public function markNotificationAsRead(Request $request, string $notificationId)
    {
        $user = Auth::user();

        if (!$user) {
            return response()->json(['success' => false, 'message' => 'Unauthorized'], 401);
        }

        $notification = $user->notifications()->find($notificationId);

        if ($notification) {
            $notification->markAsRead();

            return response()->json(['success' => true, 'message' => 'Notification marked as read.']);
        }

        return response()->json(['success' => false, 'message' => 'Notification not found.'], 404);
    }

    protected function baseTransactionsQuery(bool $withJoins = true): Builder
    {
        $query = PumpTransaction::query();

        if ($withJoins) {
            $query->leftJoin('stations', 'pump_transactions.station_id', '=', 'stations.id')
                ->leftJoin('fuel_grades', function ($join) {
                    $join->on(DB::raw('CAST(pump_transactions.pts_fuel_grade_id AS CHAR)'), '=', DB::raw('CAST(fuel_grades.pts_fuel_grade_id AS CHAR)'))
                        ->on('pump_transactions.station_id', '=', 'fuel_grades.station_id');
                })
                ->leftJoin('pts_users', function ($join) {
                    $join->on('pump_transactions.pts_user_id', '=', 'pts_users.pts_user_id')
                         ->on('pump_transactions.station_id', '=', 'pts_users.station_id');
                })
                ->select(
                    'pump_transactions.*',
                    'stations.site_id as site_id',
                    'stations.site_name',
                    'fuel_grades.name as fuel_grade_name',
                    'pts_users.login as attendant_login'
                );
        }

        return $query;
    }

    protected function applyTransactionFilters(Builder $query, array $filters): void
    {
        $fromDate = $filters['from_date'] ?? null;
        $toDate = $filters['to_date'] ?? null;
        $fromTime = $filters['from_time'] ?? '00:00:00';
        $toTime = $filters['to_time'] ?? '23:59:59';

        if ($fromDate && $toDate) {
            $query->whereBetween('pump_transactions.date_time_start', [
                $fromDate . ' ' . $fromTime,
                $toDate . ' ' . $toTime,
            ]);
        } elseif ($fromDate) {
            $query->where('pump_transactions.date_time_start', '>=', $fromDate . ' ' . $fromTime);
        } elseif ($toDate) {
            $query->where('pump_transactions.date_time_start', '<=', $toDate . ' ' . $toTime);
        }

        if (!empty($filters['station_id'])) {
            $query->where('pump_transactions.station_id', $filters['station_id']);
        }

        if (!empty($filters['pump_id'])) {
            $query->where('pump_transactions.pts_pump_id', $filters['pump_id']);
        }

        if (!empty($filters['mode_of_payment'])) {
            $query->where('pump_transactions.mode_of_payment', $filters['mode_of_payment']);
        }

        if (!empty($filters['product_id'])) {
            $query->where('pump_transactions.pts_fuel_grade_id', $filters['product_id']);
        }
    }

    /**
     * Get tank inventory data for DataTable.
     */
    public function tankInventory(Request $request)
    {
        $query = TankInventory::query()
            ->leftJoin('stations', 'tank_inventories.station_id', '=', 'stations.id')
            ->leftJoin('fuel_grades', function ($join) {
                $join->on('tank_inventories.fuel_grade_id', '=', 'fuel_grades.id')
                     ->on('tank_inventories.station_id', '=', 'fuel_grades.station_id');
            });

        // Date and Time Filters
        if ($request->filled('from_date') || $request->filled('to_date') || $request->filled('from_time') || $request->filled('to_time')) {
            $from_date = $request->input('from_date');
            $to_date = $request->input('to_date');
            $from_time = $request->input('from_time') ?: '00:00:00';
            $to_time = $request->input('to_time') ?: '23:59:59';

            // Use created_at_bos for filtering
            $dateColumn = 'created_at_bos';

            if ($from_date && $to_date) {
                $from_datetime = $from_date.' '.$from_time;
                $to_datetime = $to_date.' '.$to_time;
                $query->whereBetween('tank_inventories.'.$dateColumn, [$from_datetime, $to_datetime]);
            } elseif ($from_date) {
                $from_datetime = $from_date.' '.$from_time;
                $query->where('tank_inventories.'.$dateColumn, '>=', $from_datetime);
            } elseif ($to_date) {
                $to_datetime = $to_date.' '.$to_time;
                $query->where('tank_inventories.'.$dateColumn, '<=', $to_datetime);
            }
        }

        // Station Filter
        if ($request->filled('station_id')) {
            $query->where('tank_inventories.station_id', $request->input('station_id'));
        }

        // Fuel Grade Filter
        if ($request->filled('fuel_grade_id')) {
            $query->where('tank_inventories.fuel_grade_id', $request->input('fuel_grade_id'));
        }

        // Tank Filter
        if ($request->filled('tank')) {
            $query->where('tank_inventories.tank', $request->input('tank'));
        }

        // Global search
        if ($request->has('search') && $request->input('search.value') != '') {
            $search = $request->input('search.value');
            $query->where(function ($q) use ($search) {
                $q->where('stations.site_name', 'like', "%{$search}%")
                    ->orWhere('tank_inventories.tank', 'like', "%{$search}%")
                    ->orWhere('tank_inventories.fuel_grade_name', 'like', "%{$search}%")
                    ->orWhere('fuel_grades.name', 'like', "%{$search}%");
            });
        }

        // Get total count before filters
        $totalData = TankInventory::count();

        // Get filtered count
        $countQuery = clone $query;
        $totalFiltered = $countQuery->select(DB::raw('count(distinct tank_inventories.id) as total'))->value('total') ?? 0;

        // Pagination
        $start = $request->input('start', 0);
        $length = $request->input('length', 10);

        // Ordering
        $orderColumnIndex = $request->input('order.0.column', 0);
        $orderColumns = ['date_time', 'site', 'tank', 'product', 'volume', 'height', 'water', 'temperature', 'ullage'];
        $orderColumn = $orderColumns[$orderColumnIndex] ?? 'date_time';
        $orderDir = $request->input('order.0.dir', 'desc');

        if ($orderColumn === 'site') {
            $query->orderBy('stations.site_name', $orderDir);
        } elseif ($orderColumn === 'tank') {
            $query->orderBy('tank_inventories.tank', $orderDir);
        } elseif ($orderColumn === 'product') {
            $query->orderBy('fuel_grades.order_number')
                  ->orderBy('fuel_grades.name', $orderDir)
                  ->orderBy('tank_inventories.fuel_grade_name', $orderDir);
        } elseif ($orderColumn === 'date_time') {
            $query->orderBy('tank_inventories.created_at_bos', $orderDir);
        } elseif ($orderColumn === 'volume') {
            $query->orderBy('tank_inventories.product_volume', $orderDir);
        } elseif ($orderColumn === 'height') {
            $query->orderBy('tank_inventories.product_height', $orderDir);
        } elseif ($orderColumn === 'water') {
            $query->orderBy('tank_inventories.water_height', $orderDir);
        } elseif ($orderColumn === 'temperature') {
            $query->orderBy('tank_inventories.temperature', $orderDir);
        } elseif ($orderColumn === 'ullage') {
            $query->orderBy('tank_inventories.product_ullage', $orderDir);
        }

        $data = $query->select([
                'tank_inventories.*',
                'stations.site_name',
                'stations.pts_id as site_ref',
                'fuel_grades.name as fuel_grade_name_from_table',
                'tank_inventories.fuel_grade_name as fuel_grade_name_from_field',
            ])
            ->skip($start)
            ->limit($length)
            ->get();

        // Map data to match DataTable column structure
        $data = $data->map(function ($inventory) {
            // Format tank number as T-01, T-02, etc.
            $tankNumber = str_pad($inventory->tank, 2, '0', STR_PAD_LEFT);
            $tankFormatted = 'T-'.$tankNumber;

            // Get product name (prefer fuel_grade_name from fuel_grades join, fallback to tank_inventories.fuel_grade_name field)
            $productName = $inventory->fuel_grade_name_from_table ?? $inventory->fuel_grade_name_from_field ?? '';

            // Format created_at_bos as date_time
            $dateTime = '';

            if ($inventory->created_at_bos) {
                $dateTime = is_string($inventory->created_at_bos)
                    ? $inventory->created_at_bos
                    : $inventory->created_at_bos->format('Y-m-d H:i:s');
            }

            return [
                'date_time' => $dateTime,
                'site' => $inventory->site_name ?? '',
                'site_ref' => $inventory->site_ref ?? '',
                'tank' => $tankFormatted,
                'product' => $productName,
                'volume' => $inventory->product_volume ?? 0,
                'height' => $inventory->product_height ?? 0,
                'water' => $inventory->water_height ?? 0,
                'temperature' => $inventory->temperature ?? 0,
                'ullage' => $inventory->product_ullage ?? 0,
            ];
        });

        $json_data = [
            'draw' => intval($request->input('draw')),
            'recordsTotal' => $totalData,
            'recordsFiltered' => $totalFiltered,
            'data' => $data,
        ];

        return response()->json($json_data);
    }

    /**
     * Get unique tanks for dropdown.
     */
    public function getTanks(Request $request)
    {
        $query = TankInventory::query()
            ->select('tank')
            ->distinct()
            ->orderBy('tank');

        if ($request->filled('station_id')) {
            $query->where('station_id', $request->input('station_id'));
        }

        $tanks = $query->get()->map(function ($item) {
            return [
                'tank' => $item->tank,
                'tank_formatted' => 'T-'.str_pad($item->tank, 2, '0', STR_PAD_LEFT),
            ];
        });

        return response()->json(['tanks' => $tanks]);
    }

    /**
     * Get tank deliveries data for DataTable.
     */
    public function tankDeliveries(Request $request)
    {
        $query = TankDelivery::query()
            ->leftJoin('stations', 'tank_deliveries.station_id', '=', 'stations.id')
            ->leftJoin('fuel_grades', function ($join) {
                $join->on('tank_deliveries.fuel_grade_id', '=', 'fuel_grades.id')
                     ->on('tank_deliveries.station_id', '=', 'fuel_grades.station_id');
            });

        // Date and Time Filters (using synced_at)
        if ($request->filled('from_date') || $request->filled('to_date') || $request->filled('from_time') || $request->filled('to_time')) {
            $from_date = $request->input('from_date');
            $to_date = $request->input('to_date');
            $from_time = $request->input('from_time') ?: '00:00:00';
            $to_time = $request->input('to_time') ?: '23:59:59';

            // Use synced_at for date filtering
            $dateColumn = 'synced_at';

            if ($from_date && $to_date) {
                $from_datetime = $from_date.' '.$from_time;
                $to_datetime = $to_date.' '.$to_time;
                $query->whereBetween('tank_deliveries.'.$dateColumn, [$from_datetime, $to_datetime]);
            } elseif ($from_date) {
                $from_datetime = $from_date.' '.$from_time;
                $query->where('tank_deliveries.'.$dateColumn, '>=', $from_datetime);
            } elseif ($to_date) {
                $to_datetime = $to_date.' '.$to_time;
                $query->where('tank_deliveries.'.$dateColumn, '<=', $to_datetime);
            }
        }

        // Product (Fuel Grade) Filter
        if ($request->filled('fuel_grade_id')) {
            $query->where('tank_deliveries.fuel_grade_id', $request->input('fuel_grade_id'));
        }

        // Tank Filter
        if ($request->filled('tank')) {
            $query->where('tank_deliveries.tank', $request->input('tank'));
        }

        // Volume Filter
        if ($request->filled('volume_min')) {
            $query->where('tank_deliveries.absolute_product_volume', '>=', $request->input('volume_min'));
        }

        if ($request->filled('volume_max')) {
            $query->where('tank_deliveries.absolute_product_volume', '<=', $request->input('volume_max'));
        }

        // Global search
        if ($request->has('search') && $request->input('search.value') != '') {
            $search = $request->input('search.value');
            $query->where(function ($q) use ($search) {
                $q->where('stations.site_name', 'like', "%{$search}%")
                    ->orWhere('tank_deliveries.tank', 'like', "%{$search}%")
                    ->orWhere('tank_deliveries.fuel_grade_name', 'like', "%{$search}%")
                    ->orWhere('fuel_grades.name', 'like', "%{$search}%");
            });
        }

        // Get total count before filters
        $totalData = TankDelivery::count();

        // Get filtered count
        $countQuery = clone $query;
        $totalFiltered = $countQuery->select(DB::raw('count(distinct tank_deliveries.id) as total'))->value('total') ?? 0;

        // Pagination
        $start = $request->input('start', 0);
        $length = $request->input('length', 10);

        // Ordering
        $orderColumnIndex = $request->input('order.0.column', 0);
        $orderColumns = ['date_time', 'site', 'tank', 'product', 'volume'];
        $orderColumn = $orderColumns[$orderColumnIndex] ?? 'date_time';
        $orderDir = $request->input('order.0.dir', 'desc');

        if ($orderColumn === 'site') {
            $query->orderBy('stations.site_name', $orderDir);
        } elseif ($orderColumn === 'tank') {
            $query->orderBy('tank_deliveries.tank', $orderDir);
        } elseif ($orderColumn === 'product') {
            $query->orderBy('fuel_grades.order_number')
                  ->orderBy('fuel_grades.name', $orderDir)
                  ->orderBy('tank_deliveries.fuel_grade_name', $orderDir);
        } elseif ($orderColumn === 'date_time') {
            $query->orderBy('tank_deliveries.synced_at', $orderDir);
        } elseif ($orderColumn === 'volume') {
            $query->orderBy('tank_deliveries.absolute_product_volume', $orderDir);
        }

        $data = $query->select([
                'tank_deliveries.*',
                'stations.site_name',
                'stations.pts_id as site_ref',
                'fuel_grades.name as fuel_grade_name_from_table',
                'tank_deliveries.fuel_grade_name as fuel_grade_name_from_field',
            ])
            ->skip($start)
            ->limit($length)
            ->get();

        // Map data to match DataTable column structure
        $data = $data->map(function ($delivery) {
            // Format tank number as T-01, T-02, etc.
            $tankNumber = str_pad($delivery->tank, 2, '0', STR_PAD_LEFT);
            $tankFormatted = 'T-'.$tankNumber;

            // Get product name (prefer fuel_grade_name from fuel_grades join, fallback to tank_deliveries.fuel_grade_name field)
            $productName = $delivery->fuel_grade_name_from_table ?? $delivery->fuel_grade_name_from_field ?? '';

            // Format synced_at as date_time
            $dateTime = '';

            if ($delivery->synced_at) {
                $dateTime = is_string($delivery->synced_at)
                    ? $delivery->synced_at
                    : $delivery->synced_at->format('Y-m-d H:i:s');
            }

            return [
                'site' => $delivery->site_name ?? '',
                'site_ref' => $delivery->site_ref ?? '',
                'date_time' => $dateTime,
                'tank' => $tankFormatted,
                'product' => $productName,
                'volume' => $delivery->absolute_product_volume ?? 0,
            ];
        });

        $json_data = [
            'draw' => intval($request->input('draw')),
            'recordsTotal' => $totalData,
            'recordsFiltered' => $totalFiltered,
            'data' => $data,
        ];

        return response()->json($json_data);
    }

    /**
     * Get tank monitoring data with status calculation.
     */
    public function getTankMonitoring(Request $request)
    {
        $query = TankMeasurement::query()
            ->leftJoin('stations', 'tank_measurements.station_id', '=', 'stations.id')
            ->leftJoin('fuel_grades', function ($join) {
                $join->on('tank_measurements.fuel_grade_id', '=', 'fuel_grades.id')
                     ->on('tank_measurements.station_id', '=', 'fuel_grades.station_id');
            });

        // Station Filter
        if ($request->filled('station_id')) {
            $query->where('tank_measurements.station_id', $request->input('station_id'));
        }

        // Date and Time Filters
        if ($request->filled('from_date') || $request->filled('to_date') || $request->filled('from_time') || $request->filled('to_time')) {
            $from_date = $request->input('from_date');
            $to_date = $request->input('to_date');
            $from_time = $request->input('from_time') ?: '00:00:00';
            $to_time = $request->input('to_time') ?: '23:59:59';

            if ($from_date && $to_date) {
                $from_datetime = $from_date.' '.$from_time;
                $to_datetime = $to_date.' '.$to_time;
                $query->whereBetween('tank_measurements.date_time', [$from_datetime, $to_datetime]);
            } elseif ($from_date) {
                $from_datetime = $from_date.' '.$from_time;
                $query->where('tank_measurements.date_time', '>=', $from_datetime);
            } elseif ($to_date) {
                $to_datetime = $to_date.' '.$to_time;
                $query->where('tank_measurements.date_time', '<=', $to_datetime);
            }
        }

        // Product Filter
        if ($request->filled('product_id')) {
            $query->where('tank_measurements.fuel_grade_id', $request->input('product_id'));
        }

        // Tank Filter
        if ($request->filled('tank')) {
            $query->where('tank_measurements.tank', $request->input('tank'));
        }

        // Status Filter - Calculate based on tank_filling_percentage
        if ($request->filled('status')) {
            $status = $request->input('status');

            switch ($status) {
                case 'critical':
                    $query->where(function ($q) {
                        $q->whereNotNull('tank_measurements.tank_filling_percentage')
                          ->where('tank_measurements.tank_filling_percentage', '<', 30);
                    });

                    break;

                case 'low':
                    $query->where(function ($q) {
                        $q->whereNotNull('tank_measurements.tank_filling_percentage')
                          ->where('tank_measurements.tank_filling_percentage', '>=', 30)
                          ->where('tank_measurements.tank_filling_percentage', '<', 70);
                    });

                    break;

                case 'normal':
                    $query->where(function ($q) {
                        $q->whereNotNull('tank_measurements.tank_filling_percentage')
                          ->where('tank_measurements.tank_filling_percentage', '>=', 70);
                    });

                    break;
            }
        }

        // Get data
        $measurements = $query->select([
            'tank_measurements.*',
            'stations.site_name',
            'stations.pts_id as site_ref',
            'fuel_grades.name as fuel_grade_name_from_table',
            'tank_measurements.fuel_grade_name as fuel_grade_name_from_field',
        ])
        ->orderBy('tank_measurements.date_time', 'desc')
        ->orderBy('stations.site_name')
        ->orderBy('tank_measurements.tank')
        ->get();

        // Process data and add status
        $monitoringData = $measurements->map(function ($measurement) {
            $percentage = (float) ($measurement->tank_filling_percentage ?? 0);

            // Determine status based on percentage
            $status = 'normal';
            $statusClass = 'normal';

            if ($percentage < 30) {
                $status = 'critical';
                $statusClass = 'critical';
            } elseif ($percentage < 70) {
                $status = 'low';
                $statusClass = 'low';
            }

            // Format tank number
            $tankFormatted = 'T-'.str_pad($measurement->tank, 2, '0', STR_PAD_LEFT);

            // Get product name (prefer joined fuel_grade name, fallback to fuel_grade_name field)
            $productName = $measurement->fuel_grade_name_from_table ?? $measurement->fuel_grade_name_from_field ?? 'Unknown Product';

            return [
                'station' => $measurement->site_name ?? 'Unknown Station',
                'site_ref' => $measurement->site_ref ?? '',
                'date_time' => $measurement->date_time ? $measurement->date_time->format('Y-m-d H:i:s') : 'N/A',
                'product' => $productName,
                'tank' => $tankFormatted,
                'product_volume' => (float) ($measurement->product_volume ?? 0),
                'tank_filling_percentage' => $percentage,
                'status' => ucfirst($status),
                'status_class' => $statusClass,
            ];
        });

        return response()->json([
            'data' => $monitoringData,
            'total_records' => $monitoringData->count(),
        ]);
    }

    /**
     * Get tank list for dropdown (from tank measurements).
     */
    public function getTanksFromMeasurements(Request $request)
    {
        $query = TankMeasurement::query()
            ->select('tank')
            ->distinct()
            ->orderBy('tank');

        if ($request->filled('station_id')) {
            $query->where('station_id', $request->input('station_id'));
        }

        $tanks = $query->get()->map(function ($item) {
            return [
                'tank' => $item->tank,
                'tank_formatted' => 'T-'.str_pad($item->tank, 2, '0', STR_PAD_LEFT),
            ];
        });

        return response()->json(['tanks' => $tanks]);
    }

    /**
     * Get analytical sales data with daily aggregation.
     */
    public function getAnalyticalSales(Request $request)
    {
        // Build query to get pump transactions based on filters
        $query = PumpTransaction::query()
            ->leftJoin('stations', 'pump_transactions.station_id', '=', 'stations.id')
            ->leftJoin('fuel_grades', function ($join) {
                $join->on('pump_transactions.pts_fuel_grade_id', '=', 'fuel_grades.id')
                     ->on('pump_transactions.station_id', '=', 'fuel_grades.station_id');
            });

        // Station Filter
        if ($request->filled('station_id')) {
            $query->where('pump_transactions.station_id', $request->input('station_id'));
        }

        // Date and Time Filters
        if ($request->filled('from_date') || $request->filled('to_date') || $request->filled('from_time') || $request->filled('to_time')) {
            $from_date = $request->input('from_date');
            $to_date = $request->input('to_date');
            $from_time = $request->input('from_time') ?: '00:00:00';
            $to_time = $request->input('to_time') ?: '23:59:59';

            if ($from_date && $to_date) {
                $from_datetime = $from_date.' '.$from_time;
                $to_datetime = $to_date.' '.$to_time;
                $query->whereBetween('pump_transactions.date_time_start', [$from_datetime, $to_datetime]);
            } elseif ($from_date) {
                $from_datetime = $from_date.' '.$from_time;
                $query->where('pump_transactions.date_time_start', '>=', $from_datetime);
            } elseif ($to_date) {
                $to_datetime = $to_date.' '.$to_time;
                $query->where('pump_transactions.date_time_start', '<=', $to_datetime);
            }
        }

        // Product Filter
        if ($request->filled('product_id')) {
            $query->where('pump_transactions.pts_fuel_grade_id', $request->input('product_id'));
        }

        // Get aggregated data grouped by date, site, and product
        $analyticalData = $query->select([
                DB::raw('DATE(pump_transactions.date_time_start) as date'),
                'stations.site_name',
                'stations.pts_id as site_ref',
                'fuel_grades.name as product_name',
                DB::raw('SUM(pump_transactions.volume) as liters_sold'),
                DB::raw('SUM(pump_transactions.amount) as total_amount'),
                DB::raw('COUNT(pump_transactions.id) as transactions_count'),
            ])
            ->groupBy('date', 'stations.id', 'stations.site_name', 'stations.pts_id', 'fuel_grades.id', 'fuel_grades.name')
            ->orderBy('date', 'desc')
            ->orderBy('stations.site_name')
            ->orderBy('fuel_grades.order_number')
            ->orderBy('fuel_grades.name')
            ->get();

        // Calculate average transaction amount for each row
        $analyticalData = $analyticalData->map(function ($item) {
            $avgTransactionAmount = $item->transactions_count > 0 ? $item->total_amount / $item->transactions_count : 0;

            return [
                'date' => $item->date,
                'site' => $item->site_name ?? 'Unknown Site',
                'site_ref' => $item->site_ref ?? '',
                'product' => $item->product_name ?? 'Unknown Product',
                'liters_sold' => (float) $item->liters_sold,
                'amount' => (float) $item->total_amount,
                'transactions' => (int) $item->transactions_count,
                'avg_transaction_amount' => (float) $avgTransactionAmount,
            ];
        });

        // Calculate totals
        $totalLiters = $analyticalData->sum('liters_sold');
        $totalAmount = $analyticalData->sum('amount');
        $totalTransactions = $analyticalData->sum('transactions');
        $overallAvgTransaction = $totalTransactions > 0 ? $totalAmount / $totalTransactions : 0;

        return response()->json([
            'data' => $analyticalData,
            'total_liters' => $totalLiters,
            'total_amount' => $totalAmount,
            'total_transactions' => $totalTransactions,
            'overall_avg_transaction' => $overallAvgTransaction,
        ]);
    }

    /**
     * Get sales summary data (Sales Type Wise and Product Wise Summaries).
     */
    public function getSalesSummary(Request $request)
    {
        // Build query to get pump transactions based on filters
        $query = PumpTransaction::query()
            ->leftJoin('stations', 'pump_transactions.station_id', '=', 'stations.id')
            ->leftJoin('fuel_grades', function ($join) {
                $join->on('pump_transactions.pts_fuel_grade_id', '=', 'fuel_grades.id')
                     ->on('pump_transactions.station_id', '=', 'fuel_grades.station_id');
            });

        // Station Filter
        if ($request->filled('station_id')) {
            $query->where('pump_transactions.station_id', $request->input('station_id'));
        }

        // Date and Time Filters
        if ($request->filled('from_date') || $request->filled('to_date') || $request->filled('from_time') || $request->filled('to_time')) {
            $from_date = $request->input('from_date');
            $to_date = $request->input('to_date');
            $from_time = $request->input('from_time') ?: '00:00:00';
            $to_time = $request->input('to_time') ?: '23:59:59';

            if ($from_date && $to_date) {
                $from_datetime = $from_date.' '.$from_time;
                $to_datetime = $to_date.' '.$to_time;
                $query->whereBetween('pump_transactions.date_time_start', [$from_datetime, $to_datetime]);
            } elseif ($from_date) {
                $from_datetime = $from_date.' '.$from_time;
                $query->where('pump_transactions.date_time_start', '>=', $from_datetime);
            } elseif ($to_date) {
                $to_datetime = $to_date.' '.$to_time;
                $query->where('pump_transactions.date_time_start', '<=', $to_datetime);
            }
        }

        // Product Filter
        if ($request->filled('product_id')) {
            $query->where('pump_transactions.pts_fuel_grade_id', $request->input('product_id'));
        }

        // Join pts_users to avoid composite-key eager load issue
        $query->leftJoin('pts_users', function ($join) {
            $join->on('pump_transactions.pts_user_id', '=', 'pts_users.pts_user_id')
                 ->on('pump_transactions.station_id', '=', 'pts_users.station_id');
        });

        // Get transactions data
        $transactions = $query->select([
            'pump_transactions.*',
            'stations.site_name',
            'fuel_grades.name as fuel_grade_name',
            'pts_users.login as attendant_login',
        ])->get();

        // Sales Type Wise Summary (Cash, MOP)
        $salesTypeSummary = $transactions->groupBy('mode_of_payment')
            ->map(function ($group) {
                return [
                    'sales_type' => ucfirst($group->first()->mode_of_payment ?? 'Unknown'),
                    'volume' => $group->sum('volume'),
                    'total_amount' => $group->sum('amount'),
                    'sales_count' => $group->count(),
                ];
            })
            ->values();

        // Product Wise Summary
        $productSummary = $transactions->groupBy(function ($transaction) {
            return $transaction->fuel_grade_name ?? 'Unknown Product';
        })
        ->map(function ($group) {
            $totalVolume = $group->sum('volume');
            $totalAmount = $group->sum('amount');
            $salesCount = $group->count();

            return [
                'product_name' => $group->first()->fuel_grade_name ?? 'Unknown Product',
                'avg_per_unit' => $totalVolume > 0 ? $totalAmount / $totalVolume : 0,
                'volume' => $totalVolume,
                'total_amount' => $totalAmount,
                'sales_count' => $salesCount,
                'avg_sales_amount' => $salesCount > 0 ? $totalAmount / $salesCount : 0,
            ];
        })
        ->values();

        // Attendant Wise Summary
        $attendantSummary = $transactions->groupBy(function ($transaction) {
            return $transaction->pts_user_id ?? 'Unknown Attendant';
        })
        ->map(function ($group) {
            $firstTransaction = $group->first();

            // Get attendant name from joined data
            $attendantName = 'Unknown Attendant';

            if ($firstTransaction->attendant_login) {
                $attendantName = $firstTransaction->attendant_login;
            } elseif ($firstTransaction->pts_user_id) {
                $attendantName = 'Attendant #' . $firstTransaction->pts_user_id;
            }

            return [
                'attendant_name' => $attendantName,
                'attendant_id' => $firstTransaction->pts_user_id ?? 'N/A',
                'volume' => $group->sum('volume'),
                'total_amount' => $group->sum('amount'),
                'transactions_count' => $group->count(),
            ];
        })
        ->values();

        // Calculate totals
        $totalVolume = $transactions->sum('volume');
        $totalAmount = $transactions->sum('amount');
        $totalSalesCount = $transactions->count();

        return response()->json([
            'sales_type_summary' => $salesTypeSummary,
            'product_summary' => $productSummary,
            'attendant_summary' => $attendantSummary,
            'total_volume' => $totalVolume,
            'total_amount' => $totalAmount,
            'total_sales_count' => $totalSalesCount,
        ]);
    }

    /**
     * Get shift summary data (Payment Mode, Product, and Pump Wise Summaries).
     */
    public function getShiftSummary(Request $request)
    {
        $viewMode = $request->input('view_mode', 'individual');

        $requiredFilters = collect([
            'station_id',
            'from_date',
            'to_date',
            'from_time',
            'to_time',
            'view_mode',
        ]);

        $missingFilters = $requiredFilters->filter(function ($key) use ($request) {
            $value = $request->input($key);

            return $value === null || $value === '';
        })->values();

        if ($missingFilters->isNotEmpty()) {
            return response()->json(array_merge(
                $this->emptyShiftSummaryPayload($viewMode),
                [
                    'filters_complete' => false,
                    'missing_filters' => $missingFilters,
                    'message' => 'Please select station, date range, shift and mode filters to view the summary.',
                ]
            ));
        }

        // Build query to get shifts based on filters
        $shiftQuery = Shift::query();

        // Station Filter
        if ($request->filled('station_id')) {
            $shiftQuery->where('station_id', $request->input('station_id'));
        }

        $fromShiftId = $request->filled('from_shift_id') ? (int) $request->input('from_shift_id') : null;
        $toShiftId = $request->filled('to_shift_id') ? (int) $request->input('to_shift_id') : null;
        $fromBosShiftId = $request->filled('from_bos_shift_id') ? (int) $request->input('from_bos_shift_id') : null;
        $toBosShiftId = $request->filled('to_bos_shift_id') ? (int) $request->input('to_bos_shift_id') : null;

        $from_date = $request->input('from_date');
        $to_date = $request->input('to_date');
        $from_time = $request->input('from_time');
        $to_time = $request->input('to_time');

        $windowStart = null;
        $windowEnd = null;

        if ($from_date || $from_time) {
            $startDate = $from_date ?: $to_date;

            if ($startDate) {
                $windowStart = Carbon::parse($startDate.' '.($from_time ?: '00:00:00'));
            }
        }

        if ($to_date || $to_time) {
            $endDate = $to_date ?: $from_date;

            if ($endDate) {
                $windowEnd = Carbon::parse($endDate.' '.($to_time ?: '23:59:59'));
            }
        }

        if ($windowStart && !$windowEnd) {
            $windowEnd = $windowStart->copy()->endOfDay();
        }

        if ($windowEnd && !$windowStart) {
            $windowStart = $windowEnd->copy()->startOfDay();
        }

        if ($windowStart) {
            $shiftQuery->where('start_time', '>=', $windowStart);
        }

        if ($windowEnd) {
            $shiftQuery->where('start_time', '<=', $windowEnd);
        }

        if (!is_null($fromBosShiftId) || !is_null($toBosShiftId)) {
            $minBos = min(array_filter([$fromBosShiftId, $toBosShiftId], fn ($value) => !is_null($value))) ?? null;
            $maxBos = max(array_filter([$fromBosShiftId, $toBosShiftId], fn ($value) => !is_null($value))) ?? null;

            if (!is_null($minBos) && !is_null($maxBos)) {
                $shiftQuery->whereBetween('bos_shift_id', [$minBos, $maxBos]);
            } elseif (!is_null($minBos)) {
                $shiftQuery->where('bos_shift_id', '>=', $minBos);
            } elseif (!is_null($maxBos)) {
                $shiftQuery->where('bos_shift_id', '<=', $maxBos);
            }
        } elseif (!is_null($fromShiftId) || !is_null($toShiftId)) {
            $minId = min(array_filter([$fromShiftId, $toShiftId], fn ($value) => !is_null($value))) ?? null;
            $maxId = max(array_filter([$fromShiftId, $toShiftId], fn ($value) => !is_null($value))) ?? null;

            if (!is_null($minId) && !is_null($maxId)) {
                $shiftQuery->whereBetween('id', [$minId, $maxId]);
            } elseif (!is_null($minId)) {
                $shiftQuery->where('id', '>=', $minId);
            } elseif (!is_null($maxId)) {
                $shiftQuery->where('id', '<=', $maxId);
            }
        }

        // Get matching shifts with station info
        $shifts = $shiftQuery->with('station')->orderBy('start_time')->get();

        // Get shift IDs and BOS shift IDs grouped by station
        $shiftData = [];

        foreach ($shifts as $shift) {
            $shiftData[] = [
                'id' => $shift->id,
                'bos_shift_id' => $shift->bos_shift_id,
                'station_id' => $shift->station_id,
                'shift_number' => $shift->bos_shift_id ?? $shift->id,
                'start_time' => $shift->start_time ? $shift->start_time->setTimezone('Asia/Riyadh')->format('Y-m-d H:i:s') : null,
                'end_time' => $shift->end_time ? $shift->end_time->setTimezone('Asia/Riyadh')->format('Y-m-d H:i:s') : null,
            ];
        }

        if (empty($shiftData)) {
            return response()->json(array_merge(
                $this->emptyShiftSummaryPayload($viewMode),
                [
                    'filters_complete' => true,
                    'missing_filters' => [],
                ]
            ));
        }

        $bosShiftIds = collect($shiftData)->pluck('bos_shift_id')->filter()->unique()->values();
        $stationIds = collect($shiftData)->pluck('station_id')->filter()->unique()->values();

        $paymentSummariesByShift = collect();
        $productSummariesByShift = collect();

        if ($bosShiftIds->isNotEmpty() && $stationIds->isNotEmpty()) {
            $paymentSummaryRows = PaymentModeWiseSummary::query()
                ->select('station_id', 'bos_shift_id', 'mop', DB::raw('SUM(volume) as total_volume'), DB::raw('SUM(amount) as total_amount'))
                ->whereIn('station_id', $stationIds)
                ->whereIn('bos_shift_id', $bosShiftIds)
                ->groupBy('station_id', 'bos_shift_id', 'mop')
                ->get();

            $paymentSummariesByShift = $paymentSummaryRows->groupBy(function ($row) {
                return $this->stationShiftKey($row->station_id, $row->bos_shift_id);
            });

            $productSummaryRows = ProductWiseSummary::query()
                ->leftJoin('fuel_grades', function ($join) {
                    $join->on('product_wise_summaries.fuel_grade_id', '=', 'fuel_grades.bos_fuel_grade_id')
                        ->on('product_wise_summaries.station_id', '=', 'fuel_grades.station_id');
                })
                ->select(
                    'product_wise_summaries.station_id',
                    'product_wise_summaries.bos_shift_id',
                    'product_wise_summaries.fuel_grade_id',
                    DB::raw('COALESCE(fuel_grades.name, "N/A") as product_name'),
                    DB::raw('SUM(product_wise_summaries.volume) as total_volume'),
                    DB::raw('SUM(product_wise_summaries.amount) as total_amount')
                )
                ->whereIn('product_wise_summaries.station_id', $stationIds)
                ->whereIn('product_wise_summaries.bos_shift_id', $bosShiftIds)
                ->groupBy(
                    'product_wise_summaries.station_id',
                    'product_wise_summaries.bos_shift_id',
                    'product_wise_summaries.fuel_grade_id',
                    'fuel_grades.name'
                )
                ->get();

            $productSummariesByShift = $productSummaryRows->groupBy(function ($row) {
                return $this->stationShiftKey($row->station_id, $row->bos_shift_id);
            });
        }

        // Prepare individual shifts data (always generated for 'individual' mode)
        $individualShifts = [];

        // Prepare combined summary data (for 'summary' mode)
        $combinedPaymentSummary = [];
        $combinedProductSummary = [];
        $combinedPumpSummary = [];

        foreach ($shiftData as $shiftInfo) {
            $shiftTransactions = PumpTransaction::query()
                ->leftJoin('fuel_grades', function ($join) use ($shiftInfo) {
                    $join->on('pump_transactions.pts_fuel_grade_id', '=', 'fuel_grades.id')
                         ->on('pump_transactions.station_id', '=', 'fuel_grades.station_id');
                })
                ->where('pump_transactions.bos_shift_id', $shiftInfo['bos_shift_id'])
                ->where('pump_transactions.station_id', $shiftInfo['station_id'])
                ->select('pump_transactions.*', 'fuel_grades.name as fuel_grade_name')
                ->get();

            $transactionPaymentSummary = $this->summarizePaymentsFromTransactions($shiftTransactions);
            $transactionProductSummary = $this->summarizeProductsFromTransactions($shiftTransactions);

            $shiftKey = $this->stationShiftKey($shiftInfo['station_id'], $shiftInfo['bos_shift_id']);

            $paymentSummariesFromTable = ($paymentSummariesByShift->get($shiftKey) ?? collect())->map(function ($row) {
                return [
                    'mop' => $row->mop ?: 'N/A',
                    'volume' => (float) $row->total_volume,
                    'amount' => (float) $row->total_amount,
                ];
            });

            $paymentSummaries = $transactionPaymentSummary->values();

            if ($paymentSummaries->isEmpty() && $paymentSummariesFromTable->isNotEmpty()) {
                $paymentSummaries = $paymentSummariesFromTable->values();
            }

            $paymentSummaries = $paymentSummaries->sortBy('mop')->values();

            $productSummaries = $transactionProductSummary->values();

            // Group by pump, nozzle, fuel grade for this shift
            $shiftGrouped = $shiftTransactions->groupBy(function ($t) {
                return implode('|', [
                    $t->pts_pump_id ?? '0',
                    $t->pts_nozzle_id ?? '0',
                    $t->pts_fuel_grade_id ?? '0',
                ]);
            });

            $pumpSummaries = $shiftGrouped->map(function ($group) {
                $first = $group->first();

                $startTotalizer = $group->pluck('starting_totalizer')->filter(fn ($v) => $v !== null)->min();
                $endTotalizer = $group->pluck('total_volume')->filter(fn ($v) => $v !== null)->max();
                $totalizerVolume = null;

                if (!is_null($startTotalizer) && !is_null($endTotalizer)) {
                    $totalizerVolume = max(0, (float) $endTotalizer - (float) $startTotalizer);
                }

                $transactionVolume = (float) $group->sum('volume');
                $amountSar = (float) $group->sum('amount');

                $productName = $first->fuel_grade_name ?? 'Unknown';

                return [
                    'product' => $productName,
                    'product_name' => $productName,
                    'pump_no' => $first->pts_pump_id ?? '-',
                    'nozzle_no' => $first->pts_nozzle_id ?? '-',
                    'start_totalizer' => $startTotalizer ?? 0,
                    'end_totalizer' => $endTotalizer ?? 0,
                    'totalizer_volume' => $totalizerVolume ?? 0,
                    'txn_volume' => $transactionVolume,
                    'amount' => $amountSar,
                ];
            })->values();

            // Add to individual shifts array
            $individualShifts[] = [
                'shift_id' => $shiftInfo['id'],
                'shift_number' => $shiftInfo['shift_number'],
                'bos_shift_id' => $shiftInfo['bos_shift_id'],
                'start_time' => $shiftInfo['start_time'],
                'end_time' => $shiftInfo['end_time'],
                'payment_mode_summary' => $paymentSummaries->toArray(),
                'product_summary' => $productSummaries->toArray(),
                'pump_summary' => $pumpSummaries->toArray(),
                'total_payment_volume' => $paymentSummaries->sum('volume'),
                'total_payment_amount' => $paymentSummaries->sum('amount'),
                'total_product_volume' => $productSummaries->sum('txn_volume'),
                'total_product_amount' => $productSummaries->sum('amount'),
                'total_pump_txn_volume' => $pumpSummaries->sum('txn_volume'),
                'total_pump_amount' => $pumpSummaries->sum('amount'),
            ];

            // Aggregate for combined summary (for 'summary' mode)
            foreach ($paymentSummaries as $item) {
                $key = $item['mop'] ?? 'N/A';

                if (!array_key_exists($key, $combinedPaymentSummary)) {
                    $combinedPaymentSummary[$key] = [
                        'mop' => $key,
                        'volume' => 0,
                        'amount' => 0,
                    ];
                }

                $combinedPaymentSummary[$key]['volume'] += $item['volume'] ?? 0;
                $combinedPaymentSummary[$key]['amount'] += $item['amount'] ?? 0;
            }

            foreach ($productSummaries as $item) {
                $key = $item['product'] ?? 'N/A';

                if (!array_key_exists($key, $combinedProductSummary)) {
                    $combinedProductSummary[$key] = [
                        'product' => $key,
                        'txn_volume' => 0,
                        'amount' => 0,
                    ];
                }

                $combinedProductSummary[$key]['txn_volume'] += $item['txn_volume'] ?? 0;
                $combinedProductSummary[$key]['amount'] += $item['amount'] ?? 0;
            }

            foreach ($pumpSummaries as $item) {
                $pumpNo = $item['pump_no'] ?? '-';

                if (!array_key_exists($pumpNo, $combinedPumpSummary)) {
                    $combinedPumpSummary[$pumpNo] = [
                        'product' => $item['product'] ?? 'N/A',
                        'product_name' => $item['product_name'] ?? $item['product'] ?? 'N/A',
                        'pump_no' => $pumpNo,
                        'nozzle_no' => $item['nozzle_no'] ?? '-',
                        'start_totalizer' => $item['start_totalizer'] ?? null,
                        'end_totalizer' => $item['end_totalizer'] ?? null,
                        'totalizer_volume' => 0,
                        'txn_volume' => 0,
                        'amount' => 0,
                    ];
                }

                $existing = $combinedPumpSummary[$pumpNo];

                if (!is_null($item['start_totalizer'])) {
                    if (is_null($existing['start_totalizer'])) {
                        $existing['start_totalizer'] = $item['start_totalizer'];
                    } else {
                        $existing['start_totalizer'] = min($existing['start_totalizer'], $item['start_totalizer']);
                    }
                }

                if (!is_null($item['end_totalizer'])) {
                    if (is_null($existing['end_totalizer'])) {
                        $existing['end_totalizer'] = $item['end_totalizer'];
                    } else {
                        $existing['end_totalizer'] = max($existing['end_totalizer'], $item['end_totalizer']);
                    }
                }

                if (!empty($item['product_name']) && ($existing['product_name'] === 'N/A' || $existing['product_name'] === null)) {
                    $existing['product_name'] = $item['product_name'];
                    $existing['product'] = $item['product_name'];
                }

                if (!empty($item['nozzle_no']) && $existing['nozzle_no'] !== $item['nozzle_no']) {
                    $existing['nozzle_no'] = 'Multiple';
                }

                $existing['txn_volume'] += $item['txn_volume'] ?? 0;
                $existing['amount'] += $item['amount'] ?? 0;

                $startValue = $existing['start_totalizer'] ?? 0;
                $endValue = $existing['end_totalizer'] ?? 0;
                $existing['totalizer_volume'] = max(0, (float) $endValue - (float) $startValue);

                $combinedPumpSummary[$pumpNo] = $existing;
            }
        }

        // Calculate totals for combined summary
        $combinedPaymentTotalVolume = array_sum(array_column($combinedPaymentSummary, 'volume'));
        $combinedPaymentTotalAmount = array_sum(array_column($combinedPaymentSummary, 'amount'));
        $combinedProductTotalVolume = array_sum(array_column($combinedProductSummary, 'txn_volume'));
        $combinedProductTotalAmount = array_sum(array_column($combinedProductSummary, 'amount'));
        $combinedPumpTotalTotalizerVolume = array_sum(array_column($combinedPumpSummary, 'totalizer_volume'));
        $combinedPumpTotalTxnVolume = array_sum(array_column($combinedPumpSummary, 'txn_volume'));
        $combinedPumpTotalAmount = array_sum(array_column($combinedPumpSummary, 'amount'));

        return response()->json([
            'view_mode' => $viewMode,
            'filters_complete' => true,
            'missing_filters' => [],
            'shifts' => collect($shiftData)->map(function ($s) {
                return [
                    'id' => $s['id'],
                    'shift_number' => $s['shift_number'],
                    'bos_shift_id' => $s['bos_shift_id'],
                    'start_time' => $s['start_time'],
                    'end_time' => $s['end_time'],
                ];
            }),
            'individual_shifts' => $individualShifts,
            // Combined summary data (for 'summary' mode)
            // Remove 'key' from pump summary before returning
            'payment_mode_summary' => array_values($combinedPaymentSummary),
            'product_summary' => array_values($combinedProductSummary),
            'pump_summary' => array_values($combinedPumpSummary),
            'payment_mode_total_volume' => $combinedPaymentTotalVolume,
            'payment_mode_total_amount' => $combinedPaymentTotalAmount,
            'product_total_volume' => $combinedProductTotalVolume,
            'product_total_amount' => $combinedProductTotalAmount,
            'pump_total_totalizer_volume' => $combinedPumpTotalTotalizerVolume,
            'pump_total_txn_volume' => $combinedPumpTotalTxnVolume,
            'pump_total_amount' => $combinedPumpTotalAmount,
        ]);
    }

    private function emptyShiftSummaryPayload(string $viewMode): array
    {
        return [
            'view_mode' => $viewMode,
            'shifts' => [],
            'individual_shifts' => [],
            'payment_mode_summary' => [],
            'product_summary' => [],
            'pump_summary' => [],
            'payment_mode_total_volume' => 0,
            'payment_mode_total_amount' => 0,
            'product_total_volume' => 0,
            'product_total_amount' => 0,
            'pump_total_totalizer_volume' => 0,
            'pump_total_txn_volume' => 0,
            'pump_total_amount' => 0,
        ];
    }

    private function stationShiftKey(?int $stationId, ?int $bosShiftId): string
    {
        return ($stationId ?? '0').'|'.($bosShiftId ?? '0');
    }

    /**
     * Generate a PDF export for the shift summary report.
     */
    private function exportShiftSummaryPdf(Request $request): \Illuminate\Http\Response
    {
        $summaryResponse = $this->getShiftSummary($request);
        $payload = $summaryResponse->getData(true);

        $stationName = null;

        if ($request->filled('station_id')) {
            $stationName = Station::query()->whereKey($request->input('station_id'))->value('site_name');
        }

        $filters = array_filter([
            'Station' => $stationName,
            'From Date' => $request->input('from_date'),
            'From Time' => $request->input('from_time'),
            'To Date' => $request->input('to_date'),
            'To Time' => $request->input('to_time'),
        ], fn ($value) => $value !== null && $value !== '');

        $viewMode = $payload['view_mode'] ?? 'individual';
        $viewModeLabel = $viewMode === 'summary' ? 'Show Summary' : 'Select All';
        $filters['View Mode'] = $viewModeLabel;

        $pdf = SnappyPdf::loadView('hos-reports.pdf.shift-summary', [
            'filters' => $filters,
            'viewMode' => $viewMode,
            'viewModeLabel' => $viewModeLabel,
            'individualShifts' => $payload['individual_shifts'] ?? [],
            'combinedPaymentSummary' => $payload['payment_mode_summary'] ?? [],
            'combinedProductSummary' => $payload['product_summary'] ?? [],
            'combinedPumpSummary' => $payload['pump_summary'] ?? [],
            'combinedTotals' => [
                'payment_volume' => $payload['payment_mode_total_volume'] ?? 0,
                'payment_amount' => $payload['payment_mode_total_amount'] ?? 0,
                'product_volume' => $payload['product_total_volume'] ?? 0,
                'product_amount' => $payload['product_total_amount'] ?? 0,
                'pump_totalizer_volume' => $payload['pump_total_totalizer_volume'] ?? 0,
                'pump_txn_volume' => $payload['pump_total_txn_volume'] ?? 0,
                'pump_amount' => $payload['pump_total_amount'] ?? 0,
            ],
            'shiftsMeta' => $payload['shifts'] ?? [],
            'generatedAt' => now(),
        ])->setPaper('a4', 'landscape');

        return $pdf->download('shift_summary_'.now()->format('Y-m-d_His').'.pdf');
    }

    /**
     * Build payment mode summary from raw transactions.
     */
    private function summarizePaymentsFromTransactions(Collection $transactions): Collection
    {
        return $transactions->groupBy(function ($transaction) {
            return $transaction->mode_of_payment ?: 'N/A';
        })->map(function ($group, $mop) {
            return [
                'mop' => $mop,
                'volume' => (float) $group->sum('volume'),
                'amount' => (float) $group->sum('amount'),
            ];
        });
    }

    /**
     * Build product wise summary from raw transactions.
     */
    private function summarizeProductsFromTransactions(Collection $transactions): Collection
    {
        return $transactions->groupBy(function ($transaction) {
            return $transaction->fuel_grade_name
                ?? ($transaction->pts_fuel_grade_id ? 'Product '.$transaction->pts_fuel_grade_id : 'N/A');
        })->map(function ($group, $productName) {
            return [
                'product' => $productName,
                'product_name' => $productName,
                'txn_volume' => (float) $group->sum('volume'),
                'amount' => (float) $group->sum('amount'),
            ];
        });
    }

    /**
     * Get available shift times (HH:MM:SS) for a given date range, split by start and end times.
     */
    public function getShiftTimes(Request $request)
    {
        $from_date = $request->input('from_date');
        $to_date = $request->input('to_date');
        $station_id = $request->input('station_id');

        $startTimes = collect();
        $endTimes = collect();

        if ($from_date) {
            $startQuery = \App\Models\Shift::query()->select('id', 'bos_shift_id', 'start_time');

            if (!empty($station_id)) {
                $startQuery->where('station_id', $station_id);
            }

            $startQuery->whereDate('start_time', $from_date);

            $startTimes = $startQuery->orderBy('start_time')
                ->get()
                ->filter(fn ($shift) => !is_null($shift->start_time))
                ->map(static function ($shift) {
                    // Convert to local timezone (Asia/Riyadh UTC+3)
                    $carbonTime = $shift->start_time instanceof Carbon
                        ? $shift->start_time
                        : Carbon::parse($shift->start_time);

                    $time = $carbonTime->setTimezone('Asia/Riyadh')->format('H:i:s');

                    return [
                        'time' => $time,
                        'shift_id' => $shift->id,
                        'bos_shift_id' => $shift->bos_shift_id,
                    ];
                })
                ->values();
        }

        if ($to_date) {
            $endQuery = \App\Models\Shift::query()->select('id', 'bos_shift_id', 'end_time');

            if (!empty($station_id)) {
                $endQuery->where('station_id', $station_id);
            }

            $endQuery->whereDate('end_time', $to_date);

            $endTimes = $endQuery->orderBy('end_time')
                ->get()
                ->filter(fn ($shift) => !is_null($shift->end_time))
                ->map(static function ($shift) {
                    // Convert to local timezone (Asia/Riyadh UTC+3)
                    $carbonTime = $shift->end_time instanceof Carbon
                        ? $shift->end_time
                        : Carbon::parse($shift->end_time);

                    $time = $carbonTime->setTimezone('Asia/Riyadh')->format('H:i:s');

                    return [
                        'time' => $time,
                        'shift_id' => $shift->id,
                        'bos_shift_id' => $shift->bos_shift_id,
                    ];
                })
                ->values();
        }

        return response()->json([
            'start_times' => $startTimes,
            'end_times' => $endTimes,
        ]);
    }
}
