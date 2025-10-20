<?php

namespace App\Exports;

use App\Models\TankInventory;
use Maatwebsite\Excel\Concerns\FromQuery;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use Illuminate\Support\Carbon;

class TankInventoryExport implements FromQuery, WithHeadings, WithMapping, WithStyles
{
    protected array $filters;

    public function __construct(array $filters = [])
    {
        $this->filters = $filters;
    }

    public function query()
    {
        $query = TankInventory::query();

        // Apply filters
        if (!empty($this->filters['start_date'])) {
            $query->whereDate('timestamp', '>=', $this->filters['start_date']);
        }

        if (!empty($this->filters['end_date'])) {
            $query->whereDate('timestamp', '<=', $this->filters['end_date']);
        }

        if (!empty($this->filters['start_time']) && !empty($this->filters['end_time'])) {
            $query->whereTime('timestamp', '>=', $this->filters['start_time'])
                  ->whereTime('timestamp', '<=', $this->filters['end_time']);
        }

        if (!empty($this->filters['tank_id'])) {
            $query->where('tank_id', 'like', '%' . $this->filters['tank_id'] . '%');
        }

        return $query->orderBy('timestamp', 'desc');
    }

    public function headings(): array
    {
        return [
            'ID',
            'Device ID',
            'Tank ID',
            'Opening Stock',
            'Closing Stock',
            'Deliveries',
            'Total Sales',
            'Timestamp',
        ];
    }

    public function map($inventory): array
    {
        return [
            $inventory->id,
            $inventory->device_id,
            $inventory->tank_id,
            number_format($inventory->opening_stock, 2) . ' L',
            number_format($inventory->closing_stock, 2) . ' L',
            number_format($inventory->deliveries, 2) . ' L',
            number_format($inventory->total_sales, 2) . ' L',
            Carbon::parse($inventory->timestamp)->format('Y-m-d H:i:s'),
        ];
    }

    public function styles(Worksheet $sheet): array
    {
        return [
            1 => ['font' => ['bold' => true]],
        ];
    }
}
