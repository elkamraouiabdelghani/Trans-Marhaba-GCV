<?php

namespace App\Exports;

use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;

class DriverViolationsExport implements FromCollection, WithHeadings, WithMapping, ShouldAutoSize
{
    protected Collection $violations;

    public function __construct(Collection $violations)
    {
        $this->violations = $violations;
    }

    public function collection(): Collection
    {
        return $this->violations;
    }

    public function headings(): array
    {
        return [
            'ID',
            __('messages.violation_date'),
            __('messages.violation_type'),
            __('messages.status'),
            __('messages.location'),
            __('messages.violation_time'),
            __('messages.violation_speed'),
            __('messages.violation_speed_limit'),
            __('messages.violation_duration'),
            __('messages.violation_distance'),
            __('messages.vehicle'),
            __('messages.description'),
            __('messages.violation_analysis'),
            __('messages.violation_action_plan'),
            __('messages.violation_evidence_attached'),
        ];
    }

    public function map($violation): array
    {
        $statusLabels = [
            'pending' => __('messages.pending'),
            'confirmed' => __('messages.confirmed'),
            'rejected' => __('messages.rejected'),
        ];

        return [
            $violation->id,
            optional($violation->violation_date)->format('d/m/Y') ?? __('messages.not_available'),
            $violation->violationType->name ?? __('messages.not_specified'),
            $statusLabels[$violation->status] ?? ucfirst($violation->status ?? __('messages.not_available')),
            $violation->location ?? __('messages.not_available'),
            optional($violation->violation_time)->format('H:i') ?? __('messages.not_available'),
            $violation->speed !== null ? number_format($violation->speed, 2) : __('messages.not_available'),
            $violation->speed_limit !== null ? number_format($violation->speed_limit, 2) : __('messages.not_available'),
            $violation->violation_duration_seconds !== null ? $violation->violation_duration_seconds : __('messages.not_available'),
            $violation->violation_distance_km !== null ? number_format($violation->violation_distance_km, 2) : __('messages.not_available'),
            $violation->vehicle->license_plate ?? __('messages.not_available'),
            (string) ($violation->description ?? __('messages.not_available')),
            $violation->actionPlan->analysis ?? __('messages.not_available'),
            $violation->actionPlan->action_plan ?? __('messages.not_available'),
            $violation->actionPlan && $violation->actionPlan->evidence_path
                ? __('messages.yes')
                : __('messages.no'),
        ];
    }
}

