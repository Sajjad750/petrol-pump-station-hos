<?php

namespace App\Exports;

use App\Models\FuelGrade;
use Maatwebsite\Excel\Concerns\FromQuery;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use Illuminate\Support\Carbon;

class FuelGradeExport implements FromQuery, WithHeadings, WithMapping, WithStyles
{
    protected array $filters;

    public function __construct(array $filters = [])
    {
        $this->filters = $filters;
    }

    public function query()
    {
        $query = FuelGrade::query();

        // Apply filters
        if (!empty($this->filters['fuel_grade_name'])) {
            $query->where('name', 'like', '%' . $this->filters['fuel_grade_name'] . '%');
        }

        if (!empty($this->filters['min_price'])) {
            $query->where('price', '>=', $this->filters['min_price']);
        }

        if (!empty($this->filters['max_price'])) {
            $query->where('price', '<=', $this->filters['max_price']);
        }

        return $query->orderBy('order_number')->orderBy('created_at', 'desc');
    }

    public function headings(): array
    {
        return [
            'ID',
            'Device ID',
            'Code',
            'Name',
            'Price',
            'Price Status',
            'Blend Status',
            'Blend Components',
            'Created At',
            'Updated At',
        ];
    }

    public function map($fuelGrade): array
    {
        $blendInfo = 'No';

        if (!empty($fuelGrade->blend_components) && is_array($fuelGrade->blend_components)) {
            $blendInfo = 'Yes (' . count($fuelGrade->blend_components) . ' components)';
        }

        return [
            $fuelGrade->id,
            $fuelGrade->device_id,
            $fuelGrade->code ?? 'N/A',
            $fuelGrade->name,
            '₹' . number_format($fuelGrade->price, 2),
            ucfirst($fuelGrade->price_status ?? 'active'),
            ucfirst($fuelGrade->is_blend ? 'Yes' : 'No'),
            $blendInfo,
            Carbon::parse($fuelGrade->created_at)->format('Y-m-d H:i:s'),
            Carbon::parse($fuelGrade->updated_at)->format('Y-m-d H:i:s'),
        ];
    }

    public function styles(Worksheet $sheet): array
    {
        return [
            1 => ['font' => ['bold' => true]],
        ];
    }
}
