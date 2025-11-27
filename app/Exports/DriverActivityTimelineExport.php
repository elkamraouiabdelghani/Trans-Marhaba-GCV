<?php

namespace App\Exports;

use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class DriverActivityTimelineExport implements FromCollection, WithHeadings, WithMapping, ShouldAutoSize, WithStyles
{
    protected Collection $timelineData;
    protected Collection $violations;

    public function __construct(Collection $timelineData, Collection $violations)
    {
        $this->timelineData = $timelineData;
        $this->violations = $violations;
    }

    public function collection(): Collection
    {
        // Combine timeline data with violations
        $data = collect();
        
        foreach ($this->timelineData as $day) {
            if (isset($day['violations']) && count($day['violations']) > 0) {
                foreach ($day['violations'] as $violation) {
                    $data->push([
                        'date' => $day['date_label'] ?? '',
                        'day_name' => $day['day_name'] ?? '',
                        'flotte' => $day['flotte'] ?? '',
                        'driver_name' => $day['driver_name'] ?? '',
                        'asset_description' => $day['asset_description'] ?? '',
                        'start_time' => $day['start_time'] ?? '',
                        'end_time' => $day['end_time'] ?? '',
                        'work_hours' => $day['work_hours'] ?? 0,
                        'driving_hours' => $day['driving_hours'] ?? 0,
                        'rest_hours' => $day['rest_hours'] ?? 0,
                        'rest_daily_hours' => $day['rest_daily_hours'] ?? 0,
                        'raison' => $day['raison'] ?? '',
                        'start_location' => $day['start_location'] ?? '',
                        'overnight_location' => $day['overnight_location'] ?? '',
                        'violation_time' => $violation['time'] ?? '',
                        'violation_type' => $violation['type_label'] ?? '',
                        'violation_rule' => $violation['rule'] ?? '',
                        'violation_severity' => $violation['severity_label'] ?? '',
                        'violation_location' => $violation['location'] ?? '',
                    ]);
                }
            } else {
                // Add day without violations
                $data->push([
                    'date' => $day['date_label'] ?? '',
                    'day_name' => $day['day_name'] ?? '',
                    'flotte' => $day['flotte'] ?? '',
                    'driver_name' => $day['driver_name'] ?? '',
                    'asset_description' => $day['asset_description'] ?? '',
                    'start_time' => $day['start_time'] ?? '',
                    'end_time' => $day['end_time'] ?? '',
                    'work_hours' => $day['work_hours'] ?? 0,
                    'driving_hours' => $day['driving_hours'] ?? 0,
                    'rest_hours' => $day['rest_hours'] ?? 0,
                    'rest_daily_hours' => $day['rest_daily_hours'] ?? 0,
                    'raison' => $day['raison'] ?? '',
                    'start_location' => $day['start_location'] ?? '',
                    'overnight_location' => $day['overnight_location'] ?? '',
                    'violation_time' => '',
                    'violation_type' => '',
                    'violation_rule' => '',
                    'violation_severity' => '',
                    'violation_location' => '',
                ]);
            }
        }
        
        return $data;
    }

    public function headings(): array
    {
        return [
            __('messages.date'),
            'Day',
            __('messages.flotte'),
            __('messages.asset_description') ?? 'Asset Description',
            __('messages.asset_description') ?? 'Asset Description',
            __('messages.driver'),
            __('messages.start_time'),
            __('messages.end_time'),
            __('messages.work_time') ?? 'Work Time',
            __('messages.driving_time') ?? __('messages.driving_hours'),
            __('messages.rest_time') ?? __('messages.rest_hours'),
            __('messages.rest_daily') ?? 'Daily Rest',
            __('messages.raison') ?? 'Reason',
            __('messages.start_location') ?? 'Start Location',
            __('messages.overnight_location') ?? 'Overnight Location',
            __('messages.time'),
            __('messages.type'),
            __('messages.rule_broken'),
            __('messages.severity'),
            __('messages.location'),
        ];
    }

    public function map($row): array
    {
        return [
            $row['date'],
            $row['day_name'],
            $row['flotte'],
            $row['asset_description'],
            $row['driver_name'],
            $row['start_time'],
            $row['end_time'],
            $this->formatHours($row['work_hours']),
            $this->formatHours($row['driving_hours']),
            $this->formatHours($row['rest_hours']),
            $this->formatHours($row['rest_daily_hours']),
            $row['raison'],
            $row['start_location'],
            $row['overnight_location'],
            $row['violation_time'],
            $row['violation_type'],
            $row['violation_rule'],
            $row['violation_severity'],
            $row['violation_location'],
        ];
    }

    public function styles(Worksheet $sheet)
    {
        $sheet->getStyle('A1:S1')->getFont()->setBold(true);
        return [];
    }

    private function formatHours($value): string
    {
        $hours = is_numeric($value) ? (float) $value : 0.0;
        return number_format($hours, 2) . 'h';
    }
}

