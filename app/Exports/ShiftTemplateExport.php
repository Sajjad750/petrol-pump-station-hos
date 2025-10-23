<?php

namespace App\Exports;

use App\Models\ShiftTemplate;
use Maatwebsite\Excel\Concerns\FromQuery;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use Illuminate\Support\Carbon;

class ShiftTemplateExport implements FromQuery, WithHeadings, WithMapping, WithStyles
{
    protected array $filters;

    public function __construct(array $filters = [])
    {
        $this->filters = $filters;
    }

    public function query()
    {
        $query = ShiftTemplate::query();

        // Apply filters
        if (!empty($this->filters['timezone'])) {
            $query->where('timezone', $this->filters['timezone']);
        }

        if (!empty($this->filters['device_id'])) {
            $query->where('device_id', 'like', '%' . $this->filters['device_id'] . '%');
        }

        return $query->orderBy('created_at', 'desc');
    }

    public function headings(): array
    {
        return [
            'ID',
            'Device ID',
            'Name',
            'Start Time (24h)',
            'End Time (24h)',
            'End Time (12h)',
            'Timezone',
            'Created At',
            'Updated At',
        ];
    }

    public function map($template): array
    {
        // Convert 24-hour time to 12-hour format
        $endTime12h = 'N/A';

        if ($template->end_time) {
            try {
                $endTime12h = Carbon::createFromFormat('H:i:s', $template->end_time)->format('h:i A');
            } catch (\Exception $e) {
                $endTime12h = $template->end_time;
            }
        }

        return [
            $template->id,
            $template->device_id,
            $template->name ?? 'N/A',
            $template->start_time ?? 'N/A',
            $template->end_time ?? 'N/A',
            $endTime12h,
            $template->timezone ?? 'N/A',
            Carbon::parse($template->created_at)->format('Y-m-d H:i:s'),
            Carbon::parse($template->updated_at)->format('Y-m-d H:i:s'),
        ];
    }

    public function styles(Worksheet $sheet): array
    {
        return [
            1 => ['font' => ['bold' => true]],
        ];
    }
}
