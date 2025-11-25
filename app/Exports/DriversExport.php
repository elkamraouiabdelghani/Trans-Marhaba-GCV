<?php

namespace App\Exports;

use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class DriversExport implements FromCollection, WithHeadings, WithMapping, WithStyles
{
    public function __construct(
        protected Collection $drivers
    ) {}

    public function collection(): Collection
    {
        return $this->drivers;
    }

    public function headings(): array
    {
        return [
            __('messages.name'),
            __('messages.phone_number'),
            __('messages.vehicle'),
            __('messages.flotte'),
            __('messages.status'),
            __('messages.email') ?? 'Email',
        ];
    }

    public function map($driver): array
    {
        $phone = data_get($driver, 'phone') ?? data_get($driver, 'phone_number') ?? data_get($driver, 'phone_numbre');
        $vehicle = optional($driver->assignedVehicle)->license_plate
            ?? data_get($driver, 'vehicle_matricule')
            ?? data_get($driver, 'matricule')
            ?? data_get($driver, 'assigned_vehicle_matricule')
            ?? __('messages.not_available');

        $status = data_get($driver, 'status') ?? data_get($driver, 'statu') ?? data_get($driver, 'state') ?? __('messages.not_available');

        return [
            data_get($driver, 'full_name') ?? 'N/A',
            $phone ?? __('messages.not_available'),
            $vehicle,
            optional($driver->flotte)->name ?? __('messages.not_available'),
            $status,
            data_get($driver, 'email') ?? __('messages.not_available'),
        ];
    }

    public function styles(Worksheet $sheet)
    {
        $sheet->getStyle('A1:F1')->getFont()->setBold(true)->getColor()->setRGB('FFFFFF');
        $sheet->getStyle('A1:F1')->getFill()->setFillType('solid')->getStartColor()->setRGB('198754'); // bootstrap success

        return [];
    }
}
