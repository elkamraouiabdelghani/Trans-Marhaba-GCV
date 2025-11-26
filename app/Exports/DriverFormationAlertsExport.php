<?php

namespace App\Exports;

use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class DriverFormationAlertsExport implements FromCollection, WithHeadings, WithMapping, ShouldAutoSize, WithStyles
{
    protected Collection $alerts;

    public function __construct(Collection $alerts)
    {
        $this->alerts = $alerts;
    }

    public function collection(): Collection
    {
        return $this->alerts;
    }

    public function headings(): array
    {
        return [
            __('messages.driver'),
            __('messages.flotte'),
            __('messages.formation_theme'),
            __('messages.formation_code'),
            __('messages.alert_level'),
            __('messages.formation_alert_elapsed_label'),
            __('messages.formation_alert_days_label'),
            __('messages.last_realized_date'),
            __('messages.reference_duration'),
            __('messages.warning_alert'),
            __('messages.critical_alert'),
        ];
    }

    public function map($driverFormation): array
    {
        // Use Formation model's alert calculation with latest completion date
        $formation = $driverFormation->formation;
        $latestCompletionDate = $driverFormation->done_at;
        
        $summary = $formation 
            ? $formation->getAlertSummary($latestCompletionDate)
            : ['state' => 'none', 'elapsed_percent' => null, 'days_remaining' => null, 'reference_value' => null, 'reference_unit' => null];
        
        $state = $summary['state'];
        $elapsed = isset($summary['elapsed_percent']) && $summary['elapsed_percent'] !== null
            ? round($summary['elapsed_percent']) . '%'
            : __('messages.not_available');

        $remaining = $summary['days_remaining'];
        if ($remaining === null) {
            $remainingText = __('messages.not_available');
        } elseif ($remaining < 0) {
            $remainingText = __('messages.formation_alert_overdue', ['days' => abs($remaining)]);
        } else {
            $remainingText = __('messages.formation_alert_days_remaining', ['days' => $remaining]);
        }

        $referenceValue = $summary['reference_value'];
        $referenceUnit = $summary['reference_unit'];
        $referenceLabel = $referenceValue && $referenceUnit
            ? sprintf('%d %s', $referenceValue, $referenceUnit === 'years' ? __('messages.years') : __('messages.months'))
            : __('messages.not_available');

        $warningThreshold = $formation ? $this->formatThreshold($formation->warning_alert_percent ?? null) : __('messages.not_available');
        $criticalThreshold = $formation ? $this->formatThreshold($formation->critical_alert_percent ?? null) : __('messages.not_available');

        return [
            $driverFormation->driver->full_name ?? __('messages.not_available'),
            $driverFormation->driver->flotte->name ?? __('messages.not_available'),
            $formation->theme ?? __('messages.not_available'),
            $formation->code ?? __('messages.not_available'),
            $state === 'critical' ? __('messages.formation_alert_critical') : __('messages.formation_alert_warning'),
            $elapsed,
            $remainingText,
            optional($latestCompletionDate)->format('d/m/Y') ?? __('messages.not_available'),
            $referenceLabel,
            $warningThreshold,
            $criticalThreshold,
        ];
    }

    public function styles(Worksheet $sheet)
    {
        $sheet->getStyle('A1:K1')->getFont()->setBold(true);
        return [];
    }

    private function formatThreshold(?int $percent): string
    {
        $parts = [];
        if ($percent !== null) {
            $parts[] = $percent . '%';
        }

        return $parts ? implode(' • ', $parts) : __('messages.not_available');
    }
}

