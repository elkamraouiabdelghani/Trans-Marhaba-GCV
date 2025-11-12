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
                        'driving_hours' => $day['driving_hours'] ?? 0,
                        'rest_hours' => $day['rest_hours'] ?? 0,
                        'total_hours' => $day['total_hours'] ?? 0,
                        'start_time' => $day['start_time'] ?? '',
                        'end_time' => $day['end_time'] ?? '',
                        'route' => $day['route_description'] ?? '',
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
                    'driving_hours' => $day['driving_hours'] ?? 0,
                    'rest_hours' => $day['rest_hours'] ?? 0,
                    'total_hours' => $day['total_hours'] ?? 0,
                    'start_time' => $day['start_time'] ?? '',
                    'end_time' => $day['end_time'] ?? '',
                    'route' => $day['route_description'] ?? '',
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
            __('messages.driving_hours'),
            __('messages.rest_hours'),
            __('messages.total') ?? 'Total Hours',
            __('messages.start_time'),
            __('messages.end_time'),
            __('messages.route'),
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
            number_format($row['driving_hours'], 1) . 'h',
            number_format($row['rest_hours'], 1) . 'h',
            number_format($row['total_hours'], 1) . 'h',
            $row['start_time'],
            $row['end_time'],
            $row['route'],
            $row['violation_time'],
            $row['violation_type'],
            $row['violation_rule'],
            $row['violation_severity'],
            $row['violation_location'],
        ];
    }

    public function styles(Worksheet $sheet)
    {
        $sheet->getStyle('A1:M1')->getFont()->setBold(true);
        return [];
    }
}

