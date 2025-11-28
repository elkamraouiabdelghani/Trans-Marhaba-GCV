<?php

namespace App\Exports;

use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class DriverHandoversExport implements FromCollection, WithHeadings, WithMapping, WithStyles, ShouldAutoSize
{
    public function __construct(
        protected Collection $handovers
    ) {}

    public function collection(): Collection
    {
        return $this->handovers->loadMissing(['driverFrom', 'driverTo', 'vehicle']);
    }

    public function headings(): array
    {
        return [
            __('messages.date'),
            __('messages.driver_replace'),
            __('messages.driver_replacement'),
            __('messages.vehicle'),
            __('messages.code'),
            __('messages.vehicle_km'),
            __('messages.gasoil'),
            __('messages.location'),
            __('messages.status'),
            __('messages.cause'),
        ];
    }

    public function map($handover): array
    {
        return [
            $handover->handover_date ? $handover->handover_date->format('d/m/Y') : __('messages.not_available'),
            $handover->driver_from_name ?? optional($handover->driverFrom)->full_name ?? __('messages.not_available'),
            $handover->driver_to_name ?? optional($handover->driverTo)->full_name ?? __('messages.not_available'),
            optional($handover->vehicle)->license_plate ?? __('messages.not_available'),
            $handover->code ?? __('messages.not_available'),
            $handover->vehicle_km ?? __('messages.not_available'),
            $handover->gasoil ? number_format($handover->gasoil, 2) . ' L' : __('messages.not_available'),
            $handover->location ?? __('messages.not_available'),
            $handover->status === 'confirmed' ? __('messages.confirmed') : __('messages.pending'),
            $handover->cause ?? __('messages.not_available'),
        ];
    }

    public function styles(Worksheet $sheet)
    {
        $sheet->getStyle('A1:J1')->getFont()->setBold(true)->getColor()->setRGB('FFFFFF');
        $sheet->getStyle('A1:J1')->getFill()->setFillType('solid')->getStartColor()->setRGB('198754'); // bootstrap success

        return [];
    }
}

