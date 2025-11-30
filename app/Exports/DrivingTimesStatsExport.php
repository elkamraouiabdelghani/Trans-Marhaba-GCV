<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class DrivingTimesStatsExport implements FromArray, WithHeadings, WithStyles, ShouldAutoSize
{
    public function __construct(
        protected array $stats
    ) {}

    public function array(): array
    {
        $data = [];

        // Summary section
        $data[] = [__('messages.export_center_summary'), '', ''];
        $data[] = [__('messages.export_center_total_driving_hours'), $this->stats['total_hours'], ''];
        $data[] = [__('messages.export_center_average_per_driver'), $this->stats['average_per_driver'], ''];
        $data[] = [__('messages.export_center_unique_drivers'), $this->stats['unique_drivers'], ''];
        $data[] = ['', '', '']; // Empty row

        // Top drivers section
        $data[] = [__('messages.export_center_top_drivers'), '', ''];
        $data[] = [
            __('messages.driver'),
            __('messages.export_center_total_hours'),
            __('messages.export_center_activity_count'),
        ];

        foreach ($this->stats['top_drivers'] as $driver) {
            $data[] = [
                $driver['driver_name'],
                $driver['total_hours'],
                $driver['activity_count'],
            ];
        }

        return $data;
    }

    public function headings(): array
    {
        return [
            __('messages.export_center_driving_times_statistics'),
            '',
            '',
        ];
    }

    public function styles(Worksheet $sheet)
    {
        // Style summary header
        $sheet->getStyle('A1')->getFont()->setBold(true)->getColor()->setRGB('FFFFFF');
        $sheet->getStyle('A1')->getFill()->setFillType('solid')->getStartColor()->setRGB('198754');

        // Style section headers
        $summaryRow = 1;
        $topDriversRow = 6;

        $sheet->getStyle("A{$summaryRow}")->getFont()->setBold(true);
        $sheet->getStyle("A{$topDriversRow}")->getFont()->setBold(true);

        // Style table headers
        $topDriversHeaderRow = $topDriversRow + 1;

        $sheet->getStyle("A{$topDriversHeaderRow}:C{$topDriversHeaderRow}")->getFont()->setBold(true);
        $sheet->getStyle("A{$topDriversHeaderRow}:C{$topDriversHeaderRow}")->getFill()
            ->setFillType('solid')->getStartColor()->setRGB('E9ECEF');

        return [];
    }
}

