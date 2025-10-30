<?php

namespace App\Http\Controllers;

use App\Models\HosCommand;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class HosCommandController extends Controller
{
    /**
     * Display a listing of commands
     */
    public function index(Request $request): View|JsonResponse
    {
        if (request()->ajax()) {
            return response()->json($this->showData(request()));
        }

        return view('hos_commands.index');
    }

    /**
     * Display the specified command
     */
    public function show(HosCommand $hosCommand): JsonResponse
    {
        return response()->json([
            'success' => true,
            'data' => [
                'id' => $hosCommand->id,
                'station_id' => $hosCommand->station_id,
                'station_name' => $hosCommand->station->site_name ?? null,
                'command_type' => $hosCommand->command_type,
                'command_data' => $hosCommand->command_data,
                'status' => $hosCommand->status,
                'error_message' => $hosCommand->error_message,
                'executed_at' => $hosCommand->executed_at?->toIso8601String(),
                'acknowledged_at' => $hosCommand->acknowledged_at?->toIso8601String(),
                'retry_count' => $hosCommand->retry_count,
                'created_at' => $hosCommand->created_at->toIso8601String(),
                'updated_at' => $hosCommand->updated_at->toIso8601String(),
            ],
        ]);
    }

    protected function showData($request): array
    {
        $columns = [
            'id',
            'station_id',
            'command_type',
            'status',
            'error_message',
            'retry_count',
            'created_at',
            'acknowledged_at',
            'options',
        ];

        $length = $request->input('length', 10);
        $start = $request->input('start', 0);
        $orderIndex = $request->input('order.0.column', 0);
        $order = $columns[$orderIndex] ?? 'id';
        $orderDir = $request->input('order.0.dir', 'desc');

        $query = HosCommand::with('station');

        // Station filter
        if ($request->filled('station_id')) {
            $query->where('station_id', $request->input('station_id'));
        }

        // Status filter
        if ($request->filled('status')) {
            $query->where('status', $request->input('status'));
        }

        // Command type filter
        if ($request->filled('command_type')) {
            $query->where('command_type', $request->input('command_type'));
        }

        // Global search
        if ($request->has('search') && $request->search['value'] != '') {
            $search = $request->search['value'];
            $query->where(function ($q) use ($search) {
                $q->where('id', 'like', "%{$search}%")
                    ->orWhere('command_type', 'like', "%{$search}%")
                    ->orWhere('status', 'like', "%{$search}%")
                    ->orWhereHas('station', function ($q) use ($search) {
                        $q->where('site_name', 'like', "%{$search}%");
                    });
            });
        }

        $totalData = HosCommand::count();
        $totalFiltered = $query->count();

        // Ordering and Pagination
        $data = $query->orderBy($order, $orderDir)
            ->offset($start)
            ->limit($length)
            ->get();

        $data = $data->map(function ($row) {
            $row = $row->toArray();

            // Format timestamps
            $row['created_at'] = \Carbon\Carbon::parse($row['created_at'])->format('Y-m-d H:i:s');
            $row['updated_at'] = \Carbon\Carbon::parse($row['updated_at'])->format('Y-m-d H:i:s');
            $row['executed_at'] = $row['executed_at'] ? \Carbon\Carbon::parse($row['executed_at'])->format('Y-m-d H:i:s') : '-';
            $row['acknowledged_at'] = $row['acknowledged_at'] ? \Carbon\Carbon::parse($row['acknowledged_at'])->format('Y-m-d H:i:s') : '-';

            // Format status badge
            $status_badges = [
                'pending' => '<span class="badge badge-warning">Pending</span>',
                'processing' => '<span class="badge badge-info">Processing</span>',
                'completed' => '<span class="badge badge-success">Completed</span>',
                'failed' => '<span class="badge badge-danger">Failed</span>',
            ];
            $row['status_badge'] = $status_badges[$row['status']] ?? '<span class="badge badge-secondary">' . $row['status'] . '</span>';

            // Format command type
            $command_types = [
                'update_fuel_grade_price' => 'Update Price',
                'schedule_fuel_grade_price' => 'Schedule Price',
            ];
            $row['command_type_label'] = $command_types[$row['command_type']] ?? $row['command_type'];

            // Add view button
            $row['options'] = '<a href="' . route('hos-commands.show', $row['id']) . '" class="btn btn-sm btn-info view-command" data-id="' . $row['id'] . '">View</a>';

            return $row;
        });

        return [
            'draw' => intval($request->input('draw', 1)),
            'recordsTotal' => $totalData,
            'recordsFiltered' => $totalFiltered,
            'data' => $data,
        ];
    }
}
