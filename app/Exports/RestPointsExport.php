<?php

namespace App\Exports;

use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class RestPointsExport implements FromCollection, WithHeadings, WithMapping, WithStyles, ShouldAutoSize
{
    public function __construct(
        protected Collection $restPoints
    ) {}

    public function collection(): Collection
    {
        return $this->restPoints;
    }

    public function headings(): array
    {
        return [
            __('messages.name') ?? 'Name',
            __('messages.type') ?? 'Type',
            __('messages.latitude') ?? 'Latitude',
            __('messages.longitude') ?? 'Longitude',
            __('messages.coordinates') ?? 'Coordinates',
            __('messages.description') ?? 'Description',
            __('messages.created_at') ?? 'Created At',
            __('messages.updated_at') ?? 'Updated At',
        ];
    }

    public function map($restPoint): array
    {
        return [
            $restPoint->name ?? '',
            $restPoint->type_label ?? $restPoint->type ?? '',
            $restPoint->latitude ?? '',
            $restPoint->longitude ?? '',
            ($restPoint->latitude && $restPoint->longitude) 
                ? number_format($restPoint->latitude, 6) . ', ' . number_format($restPoint->longitude, 6)
                : '',
            $restPoint->description ?? '',
            $restPoint->created_at ? $restPoint->created_at->format('d/m/Y H:i') : '',
            $restPoint->updated_at ? $restPoint->updated_at->format('d/m/Y H:i') : '',
        ];
    }

    public function styles(Worksheet $sheet)
    {
        $sheet->getStyle('A1:H1')->getFont()->setBold(true);
        $sheet->getStyle('A1:H1')->getFill()
            ->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)
            ->getStartColor()->setRGB('28A745');
        $sheet->getStyle('A1:H1')->getFont()->getColor()->setRGB('FFFFFF');
        
        return $sheet;
    }
}

