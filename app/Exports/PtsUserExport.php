<?php

namespace App\Exports;

use App\Models\PtsUser;
use Maatwebsite\Excel\Concerns\FromQuery;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use Illuminate\Support\Carbon;

class PtsUserExport implements FromQuery, WithHeadings, WithMapping, WithStyles
{
    protected array $filters;

    public function __construct(array $filters = [])
    {
        $this->filters = $filters;
    }

    public function query()
    {
        $query = PtsUser::query();

        // Apply filters
        if (!empty($this->filters['login'])) {
            $query->where('login', 'like', '%' . $this->filters['login'] . '%');
        }

        if (!empty($this->filters['active_status'])) {
            $activeStatus = $this->filters['active_status'] === 'active';
            $query->where('is_active', $activeStatus);
        }

        if (!empty($this->filters['permissions'])) {
            $permissions = is_array($this->filters['permissions'])
                ? $this->filters['permissions']
                : [$this->filters['permissions']];

            $query->where(function ($q) use ($permissions) {
                foreach ($permissions as $permission) {
                    $q->orWhere('permission_type', 'like', '%' . $permission . '%');
                }
            });
        }

        return $query->orderBy('created_at', 'desc');
    }

    public function headings(): array
    {
        return [
            'ID',
            'Device ID',
            'Login',
            'Name',
            'Active Status',
            'Permission Type',
            'Role',
            'Created At',
            'Updated At',
        ];
    }

    public function map($user): array
    {
        return [
            $user->id,
            $user->device_id,
            $user->login,
            $user->name ?? 'N/A',
            $user->is_active ? 'Active' : 'Inactive',
            ucfirst($user->permission_type ?? 'N/A'),
            ucfirst($user->role ?? 'N/A'),
            Carbon::parse($user->created_at)->format('Y-m-d H:i:s'),
            Carbon::parse($user->updated_at)->format('Y-m-d H:i:s'),
        ];
    }

    public function styles(Worksheet $sheet): array
    {
        return [
            1 => ['font' => ['bold' => true]],
        ];
    }
}
