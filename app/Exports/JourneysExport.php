<?php

namespace App\Exports;

use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class JourneysExport implements FromCollection, WithHeadings, WithMapping, WithStyles, ShouldAutoSize
{
    public function __construct(
        protected Collection $journeys
    ) {}

    public function collection(): Collection
    {
        return $this->journeys;
    }

    public function headings(): array
    {
        return [
            __('messages.name') ?? 'Name',
            __('messages.from_location') ?? 'From Location',
            __('messages.from_location_name') ?? 'From Location Name',
            __('messages.to_location') ?? 'To Location',
            __('messages.to_location_name') ?? 'To Location Name',
            __('messages.total_score') ?? 'Total Score',
            __('messages.status') ?? 'Status',
            __('messages.details') ?? 'Details',
            __('messages.created_at') ?? 'Created At',
            __('messages.updated_at') ?? 'Updated At',
        ];
    }

    public function map($journey): array
    {
        $status = $journey->status ?? 'less';
        $statusLabels = [
            'excellent' => __('messages.journey_status_excellent') ?? 'Excellent',
            'good' => __('messages.journey_status_good') ?? 'Good',
            'average' => __('messages.journey_status_average') ?? 'Average',
            'less' => __('messages.journey_status_less') ?? 'Less',
        ];
        
        return [
            $journey->name ?? '',
            ($journey->from_latitude && $journey->from_longitude) 
                ? number_format($journey->from_latitude, 6) . ', ' . number_format($journey->from_longitude, 6)
                : '',
            $journey->from_location_name ?? '',
            ($journey->to_latitude && $journey->to_longitude) 
                ? number_format($journey->to_latitude, 6) . ', ' . number_format($journey->to_longitude, 6)
                : '',
            $journey->to_location_name ?? '',
            number_format($journey->total_score ?? 0, 2),
            $statusLabels[$status] ?? ucfirst($status),
            $journey->details ?? '',
            $journey->created_at ? $journey->created_at->format('d/m/Y H:i') : '',
            $journey->updated_at ? $journey->updated_at->format('d/m/Y H:i') : '',
        ];
    }

    public function styles(Worksheet $sheet)
    {
        $sheet->getStyle('A1:J1')->getFont()->setBold(true);
        $sheet->getStyle('A1:J1')->getFill()
            ->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)
            ->getStartColor()->setRGB('10B981');
        $sheet->getStyle('A1:J1')->getFont()->getColor()->setRGB('FFFFFF');
        
        return $sheet;
    }
}

