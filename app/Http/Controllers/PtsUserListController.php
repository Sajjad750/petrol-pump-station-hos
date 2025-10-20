<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Exports\PtsUserExport;
use Maatwebsite\Excel\Facades\Excel;
use Barryvdh\DomPDF\Facade\Pdf;
use App\Models\PtsUser;

class PtsUserListController extends Controller
{
    /**
     * Handle the incoming request.
     */
    public function __invoke(Request $request)
    {
        if (request()->ajax()) {
            return response()->json($this->showData(request()));
        }

        return view('pts_users.index');
    }

    public function exportExcel(Request $request): \Symfony\Component\HttpFoundation\BinaryFileResponse
    {
        $filters = $request->only(['login', 'active_status', 'permissions']);

        return Excel::download(new PtsUserExport($filters), 'pts_users_' . now()->format('Y-m-d_His') . '.xlsx');
    }

    public function exportPdf(Request $request): \Illuminate\Http\Response
    {
        $filters = $request->only(['login', 'active_status', 'permissions']);
        $query = PtsUser::query();

        if (!empty($filters['login'])) {
            $query->where('login', 'like', '%' . $filters['login'] . '%');
        }

        if (!empty($filters['active_status'])) {
            $query->where('is_active', $filters['active_status'] === 'active');
        }
        $users = $query->orderBy('created_at', 'desc')->get();
        $pdf = Pdf::loadView('reports.generic_pdf', [
            'title' => 'PTS Users Report',
            'filters' => $filters,
            'summary' => ['Total Records' => count($users), 'Active Users' => $users->where('is_active', true)->count()],
            'headers' => ['ID', 'Device ID', 'Login', 'Name', 'Active Status', 'Permission Type', 'Role'],
            'data' => $users->map(fn ($u) => [$u->id, $u->device_id, $u->login, $u->name ?? 'N/A', $u->is_active ? 'Active' : 'Inactive', ucfirst($u->permission_type ?? 'N/A'), ucfirst($u->role ?? 'N/A')]),
        ]);

        return $pdf->download('pts_users_' . now()->format('Y-m-d_His') . '.pdf');
    }

    protected function showData($request)
    {
        $columns = [
            'id',
            'pts_user_id',
            'login',
            'configuration_permission',
            'control_permission',
            'monitoring_permission',
            'reports_permission',
            'is_active',
            'station_id',
            'bos_pts_user_id',
            'synced_at',
            'created_at_bos',
            'updated_at_bos',
            'created_at',
            'updated_at',
            'options'
        ];

        $length = $request->input('length');
        $start = $request->input('start');
        $order = $columns[$request->input('order.0.column')];
        $orderDir = $request->input('order.0.dir');

        $query = \App\Models\PtsUser::with('station');

        // Login Filter
        if ($request->filled('login')) {
            $query->where('login', 'like', '%'.$request->input('login').'%');
        }

        // Active Status Filter
        if ($request->filled('is_active')) {
            $is_active = $request->input('is_active');

            if ($is_active === '1') {
                $query->where('is_active', true);
            } elseif ($is_active === '0') {
                $query->where('is_active', false);
            }
        }

        // Global search for all columns
        if ($request->has('search') && $request->search['value'] != '') {
            $search = $request->search['value'];
            $query->where(function ($q) use ($search) {
                $q->where('id', 'like', "%{$search}%")
                    ->orWhere('pts_user_id', 'like', "%{$search}%")
                    ->orWhere('login', 'like', "%{$search}%");
            });
        }

        $totalData = \App\Models\PtsUser::count();
        $totalFiltered = $query->count();

        // Ordering and Pagination
        $data = $query->orderBy($order, $orderDir)
            ->offset($start)
            ->limit($length)
            ->get();

        $data = $data->map(function ($row) {
            $row = $row->toArray();

            // Format timestamps
            $row['synced_at'] = $row['synced_at'] ? \Carbon\Carbon::parse($row['synced_at'])->format('Y-m-d H:i:s') : '-';
            $row['created_at_bos'] = $row['created_at_bos'] ? \Carbon\Carbon::parse($row['created_at_bos'])->format('Y-m-d H:i:s') : '-';
            $row['updated_at_bos'] = $row['updated_at_bos'] ? \Carbon\Carbon::parse($row['updated_at_bos'])->format('Y-m-d H:i:s') : '-';
            $row['created_at'] = \Carbon\Carbon::parse($row['created_at'])->format('Y-m-d H:i:s');
            $row['updated_at'] = \Carbon\Carbon::parse($row['updated_at'])->format('Y-m-d H:i:s');

            // Add active status badge
            $row['active_badge'] = $row['is_active']
                ? '<span class="badge badge-success">Active</span>'
                : '<span class="badge badge-danger">Inactive</span>';

            // Add permission badges
            $row['config_badge'] = $row['configuration_permission']
                ? '<span class="badge badge-primary"><i class="fas fa-check"></i></span>'
                : '<span class="badge badge-secondary"><i class="fas fa-times"></i></span>';

            $row['control_badge'] = $row['control_permission']
                ? '<span class="badge badge-primary"><i class="fas fa-check"></i></span>'
                : '<span class="badge badge-secondary"><i class="fas fa-times"></i></span>';

            $row['monitoring_badge'] = $row['monitoring_permission']
                ? '<span class="badge badge-primary"><i class="fas fa-check"></i></span>'
                : '<span class="badge badge-secondary"><i class="fas fa-times"></i></span>';

            $row['reports_badge'] = $row['reports_permission']
                ? '<span class="badge badge-primary"><i class="fas fa-check"></i></span>'
                : '<span class="badge badge-secondary"><i class="fas fa-times"></i></span>';

            // Count permissions
            $permissions_count = 0;

            if ($row['configuration_permission']) {
                $permissions_count++;
            }

            if ($row['control_permission']) {
                $permissions_count++;
            }

            if ($row['monitoring_permission']) {
                $permissions_count++;
            }

            if ($row['reports_permission']) {
                $permissions_count++;
            }

            $row['permissions_summary'] = '<span class="badge badge-info">' . $permissions_count . ' / 4</span>';

            return $row;
        });

        $json_data = [
            "draw" => intval($request->input('draw')),
            "recordsTotal" => $totalData,
            "recordsFiltered" => $totalFiltered,
            "data" => $data,
        ];

        return $json_data;
    }
}
