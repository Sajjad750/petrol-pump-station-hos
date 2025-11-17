<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Carbon;
use App\Models\PumpTransaction;
use App\Models\Station;
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
        return response()->json([
            'fuel_grades' => \App\Models\FuelGrade::select('id', 'name')->orderBy('name')->get(),
        ]);
    }

    /**
     * Get transactions data for DataTable.
     */
    public function getTransactionsData(Request $request)
    {
        // Define sortable columns mapping (column index => database column)
        $columns = [
            0 => 'stations.site_name', // Site
            1 => 'transaction_number', // Transaction ID
            2 => 'date_time_start', // Date & Time
            3 => 'pts_pump_id', // Pump
            4 => 'pts_nozzle_id', // Nozzle
            5 => 'fuel_grades.name', // Product
            6 => 'price', // Unit Price
            7 => 'volume', // Litres
            8 => 'amount', // Amount
            9 => 'mode_of_payment', // MOP
            10 => 'tag', // Mobile & Vehicle ID (not sortable)
            11 => 'pts_users.login', // Atten
        ];

        $length = $request->input('length', 10);
        $start = $request->input('start', 0);
        $orderColumnIndex = $request->input('order.0.column', 2);
        $orderDir = $request->input('order.0.dir', 'desc');
        $orderColumn = $columns[$orderColumnIndex] ?? 'date_time_start';

        // Eager load relationships
        $query = PumpTransaction::query()
            ->with(['station', 'fuelGrade'])
            ->leftJoin('stations', 'pump_transactions.station_id', '=', 'stations.id')
            ->leftJoin('fuel_grades', function ($join) {
                $join->on('pump_transactions.pts_fuel_grade_id', '=', 'fuel_grades.id')
                     ->on('pump_transactions.station_id', '=', 'fuel_grades.station_id');
            })
//            ->leftJoin('pts_users', function ($join) {
//                $join->on('pump_transactions.pts_user_id', '=', 'pts_users.pts_user_id')
//                     ->on('pump_transactions.station_id', '=', 'pts_users.station_id');
//            })
            ->select('pump_transactions.*');

        // Date and Time Filters
        if ($request->filled('from_date') || $request->filled('to_date') || $request->filled('from_time') || $request->filled('to_time')) {
            $from_date = $request->input('from_date');
            $to_date = $request->input('to_date');
            $from_time = $request->input('from_time', '00:00:00');
            $to_time = $request->input('to_time', '23:59:59');

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

        // Pump ID Filter
        if ($request->filled('pump_id')) {
            $query->where('pump_transactions.pts_pump_id', 'like', '%'.$request->input('pump_id').'%');
        }

        // Mode of Payment Filter
        if ($request->filled('mode_of_payment')) {
            $query->where('pump_transactions.mode_of_payment', $request->input('mode_of_payment'));
        }

        // Product (Fuel Grade) Filter
        if ($request->filled('product_id')) {
            $query->where('pump_transactions.pts_fuel_grade_id', $request->input('product_id'));
        }

        // Global search for all columns
        if ($request->has('search') && $request->input('search.value') != '') {
            $search = $request->input('search.value');
            $query->where(function ($q) use ($search) {
                $q->where('stations.site_name', 'like', "%{$search}%")
                    ->orWhere('transaction_number', 'like', "%{$search}%")
                    ->orWhere('pts_pump_id', 'like', "%{$search}%")
                    ->orWhere('pts_nozzle_id', 'like', "%{$search}%")
                    ->orWhere('fuel_grades.name', 'like', "%{$search}%")
                    ->orWhere('mode_of_payment', 'like', "%{$search}%")
                    ->orWhere('tag', 'like', "%{$search}%")
                    ->orWhere('pts_users.login', 'like', "%{$search}%");
            });
        }

        $totalData = PumpTransaction::count();

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
                'site' => $transaction->station ? $transaction->station->site_name : '',
                'site_ref' => $transaction->station ? ($transaction->station->pts_id ?? '') : '',
                'transaction_id' => $transaction->transaction_number ?? '',
                'date_time' => $transaction->date_time_start ? $transaction->date_time_start->format('Y-m-d H:i:s') : '',
                'pump' => $transaction->pts_pump_id ?? '',
                'nozzle' => $transaction->pts_nozzle_id ?? '',
                'product' => $transaction->fuelGrade ? $transaction->fuelGrade->name : '',
                'unit_price' => $transaction->price ?? 0,
                'litres' => $transaction->volume ?? 0,
                'amount' => $transaction->amount ?? 0,
                'mode_of_payment' => ucfirst($transaction->mode_of_payment ?? ''),
                'mobile_vehicle_id' => $mobile,
                'vehicle_id' => $vehicleId,
                'atten' => null,
                'atten_username' => null,
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
                $join->on('pump_transactions.pts_fuel_grade_id', '=', 'fuel_grades.id')
                     ->on('pump_transactions.station_id', '=', 'fuel_grades.station_id');
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
                    ->orWhere('transaction_number', 'like', "%{$search}%")
                    ->orWhere('fuel_grades.name', 'like', "%{$search}%");
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
        $orderColumnIndex = $request->input('order.0.column', 2);
        $orderColumns = ['site', 'transaction_id', 'date_time', 'product', 'liters', 'amount', 'hos_received_time'];
        $orderColumn = $orderColumns[$orderColumnIndex] ?? 'date_time';
        $orderDir = $request->input('order.0.dir', 'desc');

        if ($orderColumn === 'site') {
            $query->orderBy('stations.site_name', $orderDir);
        } elseif ($orderColumn === 'product') {
            $query->orderBy('fuel_grades.name', $orderDir);
        } else {
            $query->orderBy('pump_transactions.date_time_start', $orderDir);
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
            // Format date_time_start
            $dateTime = '';

            if ($transaction->date_time_start) {
                $dateTime = is_string($transaction->date_time_start)
                    ? $transaction->date_time_start
                    : $transaction->date_time_start->format('Y-m-d H:i:s');
            }

            return [
                'site' => $transaction->site_name ?? '',
                'site_ref' => $transaction->site_ref ?? '',
                'transaction_id' => $transaction->transaction_number ?? '',
                'date_time' => $dateTime,
                'product' => $transaction->fuel_grade_name ?? '',
                'liters' => $transaction->volume ?? 0,
                'amount' => $transaction->amount ?? 0,
                'hos_received_time' => $transaction->created_at ? $transaction->created_at->format('Y-m-d H:i:s') : '',
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
     * Export transactions to PDF.
     */
    public function exportPdf(Request $request)
    {
        // TODO: Implement PDF export
        return response()->json(['message' => 'PDF export not implemented yet']);
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
            $query->orderBy('fuel_grades.name', $orderDir)
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
            $query->orderBy('fuel_grades.name', $orderDir)
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
        // Build query to get shifts based on filters
        $shiftQuery = Shift::query();

        // Station Filter
        if ($request->filled('station_id')) {
            $shiftQuery->where('station_id', $request->input('station_id'));
        }

        $from_date = $request->input('from_date');
        $to_date = $request->input('to_date');
        $from_time = $request->input('from_time');
        $to_time = $request->input('to_time');

        $startBoundary = null;
        $endBoundary = null;

        if ($from_date) {
            $startBoundary = Carbon::parse($from_date.' '.($from_time ?: '00:00:00'));
        }

        if ($to_date) {
            $endBoundary = Carbon::parse($to_date.' '.($to_time ?: '23:59:59'));
        }

        if ($startBoundary && $endBoundary) {
            $shiftQuery->where(function ($query) use ($startBoundary, $endBoundary) {
                $query->whereBetween('start_time', [$startBoundary, $endBoundary])
                    ->orWhere(function ($subQuery) use ($startBoundary, $endBoundary) {
                        $subQuery->whereNotNull('end_time')
                            ->whereBetween('end_time', [$startBoundary, $endBoundary]);
                    });
            });
        } else {
            if ($from_date) {
                if ($from_time) {
                    $exactStart = Carbon::parse($from_date.' '.$from_time);
                    $shiftQuery->whereBetween('start_time', [
                        $exactStart->copy()->startOfMinute(),
                        $exactStart->copy()->endOfMinute(),
                    ]);
                } else {
                    $shiftQuery->whereDate('start_time', $from_date);
                }
            }

            if ($to_date) {
                if ($to_time) {
                    $exactEnd = Carbon::parse($to_date.' '.$to_time);
                    $shiftQuery->whereBetween('end_time', [
                        $exactEnd->copy()->startOfMinute(),
                        $exactEnd->copy()->endOfMinute(),
                    ]);
                } else {
                    $shiftQuery->whereDate('end_time', $to_date);
                }
            }
        }

        // Get matching shifts with station info
        $shifts = $shiftQuery->with('station')->orderBy('start_time', 'desc')->get();

        // Get shift IDs and BOS shift IDs grouped by station
        $shiftData = [];

        foreach ($shifts as $shift) {
            $shiftData[] = [
                'id' => $shift->id,
                'bos_shift_id' => $shift->bos_shift_id,
                'station_id' => $shift->station_id,
                'shift_number' => $shift->bos_shift_id ?? $shift->id,
                'start_time' => $shift->start_time ? $shift->start_time->format('Y-m-d H:i:s') : null,
                'end_time' => $shift->end_time ? $shift->end_time->format('Y-m-d H:i:s') : null,
            ];
        }

        $viewMode = $request->input('view_mode', 'individual'); // Default to 'individual'

        if (empty($shiftData)) {
            return response()->json([
                'view_mode' => $viewMode,
                'shifts' => [],
                'individual_shifts' => [],
                'payment_mode_summary' => [],
                'product_summary' => [],
                'pump_summary' => [],
            ]);
        }

        // Get all shift IDs
        $shiftIds = collect($shiftData)->pluck('id')->toArray();

        // Prepare individual shifts data (always generated for 'individual' mode)
        $individualShifts = [];

        // Prepare combined summary data (for 'summary' mode)
        $combinedPaymentSummary = collect();
        $combinedProductSummary = collect();
        $combinedPumpSummary = collect();

        foreach ($shiftData as $shiftInfo) {
            // Get Payment Mode Wise Summary for this shift using bos_shift_id
            $paymentSummaries = PaymentModeWiseSummary::where('bos_shift_id', $shiftInfo['bos_shift_id'])
                ->where('station_id', $shiftInfo['station_id'])
                ->orderBy('mop')
                ->get()
                ->groupBy('mop')
                ->map(function ($group) {
                    return [
                        'mop' => $group->first()->mop,
                        'volume' => $group->sum('volume'),
                        'amount' => $group->sum('amount'),
                    ];
                })
                ->values();

            // Get Product Wise Summary for this shift using bos_shift_id
            $productSummaries = ProductWiseSummary::with('fuelGrade')
                ->where('bos_shift_id', $shiftInfo['bos_shift_id'])
                ->where('station_id', $shiftInfo['station_id'])
                ->get()
                ->map(function ($item) {
                    return [
                        'product' => optional($item->fuelGrade)->name ?? 'N/A',
                        'txn_volume' => $item->volume ?? 0,
                        'amount' => $item->amount ?? 0,
                    ];
                })
                ->groupBy('product')
                ->map(function ($group) {
                    return [
                        'product' => $group->first()['product'],
                        'txn_volume' => $group->sum('txn_volume'),
                        'amount' => $group->sum('amount'),
                    ];
                })
                ->values();

            // Get Pump Wise Summary for this shift using bos_shift_id
            $shiftTransactions = PumpTransaction::where('bos_shift_id', $shiftInfo['bos_shift_id'])
                ->where('station_id', $shiftInfo['station_id'])
                ->with('fuelGrade')
                ->get();

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

                return [
                    'product' => $first->fuel_grade_name ?? optional($first->fuelGrade)->name ?? 'Unknown',
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
                $existing = $combinedPaymentSummary->firstWhere('mop', $item['mop']);

                if ($existing) {
                    $existing['volume'] += $item['volume'];
                    $existing['amount'] += $item['amount'];
                } else {
                    $combinedPaymentSummary->push([
                        'mop' => $item['mop'],
                        'volume' => $item['volume'],
                        'amount' => $item['amount'],
                    ]);
                }
            }

            foreach ($productSummaries as $item) {
                $existing = $combinedProductSummary->firstWhere('product', $item['product']);

                if ($existing) {
                    $existing['txn_volume'] += $item['txn_volume'];
                    $existing['amount'] += $item['amount'];
                } else {
                    $combinedProductSummary->push([
                        'product' => $item['product'],
                        'txn_volume' => $item['txn_volume'],
                        'amount' => $item['amount'],
                    ]);
                }
            }

            foreach ($pumpSummaries as $item) {
                $key = $item['product'].'|'.$item['pump_no'].'|'.$item['nozzle_no'];
                $existing = $combinedPumpSummary->firstWhere('key', $key);

                if ($existing) {
                    // Take minimum start and maximum end across all shifts
                    $existing['start_totalizer'] = min($existing['start_totalizer'], $item['start_totalizer']);
                    $existing['end_totalizer'] = max($existing['end_totalizer'], $item['end_totalizer']);
                    // Recalculate totalizer volume based on new min/max
                    $existing['totalizer_volume'] = max(0, (float) $existing['end_totalizer'] - (float) $existing['start_totalizer']);
                    // Sum transaction volume and amount
                    $existing['txn_volume'] += $item['txn_volume'];
                    $existing['amount'] += $item['amount'];
                } else {
                    $combinedPumpSummary->push(array_merge($item, ['key' => $key]));
                }
            }
        }

        // Calculate totals for combined summary
        $combinedPaymentTotalVolume = $combinedPaymentSummary->sum('volume');
        $combinedPaymentTotalAmount = $combinedPaymentSummary->sum('amount');
        $combinedProductTotalVolume = $combinedProductSummary->sum('txn_volume');
        $combinedProductTotalAmount = $combinedProductSummary->sum('amount');
        // For pump summary, totalizer volume is sum of individual totalizer volumes (each pump/nozzle/product combination)
        $combinedPumpTotalTotalizerVolume = $combinedPumpSummary->sum('totalizer_volume');
        $combinedPumpTotalTxnVolume = $combinedPumpSummary->sum('txn_volume');
        $combinedPumpTotalAmount = $combinedPumpSummary->sum('amount');

        return response()->json([
            'view_mode' => $viewMode,
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
            'payment_mode_summary' => $combinedPaymentSummary->values()->toArray(),
            'product_summary' => $combinedProductSummary->values()->toArray(),
            'pump_summary' => $combinedPumpSummary->map(function ($item) {
                unset($item['key']);

                return $item;
            })->values()->toArray(),
            'payment_mode_total_volume' => $combinedPaymentTotalVolume,
            'payment_mode_total_amount' => $combinedPaymentTotalAmount,
            'product_total_volume' => $combinedProductTotalVolume,
            'product_total_amount' => $combinedProductTotalAmount,
            'pump_total_totalizer_volume' => $combinedPumpTotalTotalizerVolume,
            'pump_total_txn_volume' => $combinedPumpTotalTxnVolume,
            'pump_total_amount' => $combinedPumpTotalAmount,
        ]);
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
                    $time = $shift->start_time instanceof Carbon
                        ? $shift->start_time->format('H:i:s')
                        : Carbon::parse($shift->start_time)->format('H:i:s');

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
                    $time = $shift->end_time instanceof Carbon
                        ? $shift->end_time->format('H:i:s')
                        : Carbon::parse($shift->end_time)->format('H:i:s');

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
