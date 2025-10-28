<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Models\PumpTransaction;
use App\Models\Station;
use App\Models\TankInventory;
use App\Models\TankDelivery;

class HosReportsController extends Controller
{
    public function __invoke()
    {
        return view('hos-reports.index');
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
            ->with(['station', 'fuelGrade', 'ptsUser'])
            ->leftJoin('stations', 'pump_transactions.station_id', '=', 'stations.id')
            ->leftJoin('fuel_grades', function ($join) {
                $join->on('pump_transactions.pts_fuel_grade_id', '=', 'fuel_grades.id')
                     ->on('pump_transactions.station_id', '=', 'fuel_grades.station_id');
            })
            ->leftJoin('pts_users', function ($join) {
                $join->on('pump_transactions.pts_user_id', '=', 'pts_users.pts_user_id')
                     ->on('pump_transactions.station_id', '=', 'pts_users.station_id');
            })
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
            $attenName = '';
            $attenUsername = '';

            if ($transaction->ptsUser) {
                $attenUsername = $transaction->ptsUser->login ?? '';
                // For display, use login as name (or first name if available)
                $attenName = $attenUsername;
            } elseif ($transaction->pts_user_id) {
                $attenName = (string) $transaction->pts_user_id;
            }

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
                'mop' => ucfirst($transaction->mode_of_payment ?? ''),
                'mobile_vehicle_id' => $mobile,
                'vehicle_id' => $vehicleId,
                'atten' => $attenName,
                'atten_username' => $attenUsername,
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
}
