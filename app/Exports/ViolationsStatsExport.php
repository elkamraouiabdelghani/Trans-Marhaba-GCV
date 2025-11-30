<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class ViolationsStatsExport implements FromArray, WithHeadings, WithStyles, ShouldAutoSize
{
    public function __construct(
        protected array $stats
    ) {}

    public function array(): array
    {
        $data = [];

        // Summary section
        $data[] = [__('messages.export_center_summary'), '', '', ''];
        $data[] = [__('messages.total'), $this->stats['total'], '', ''];
        $data[] = [__('messages.confirmed'), $this->stats['confirmed'], '', ''];
        $data[] = [__('messages.rejected'), $this->stats['rejected'], '', ''];
        $data[] = [__('messages.pending'), $this->stats['pending'], '', ''];
        $data[] = ['', '', '', '']; // Empty row

        // Top drivers section
        $data[] = [__('messages.export_center_top_drivers'), '', '', ''];
        $data[] = [
            __('messages.driver'),
            __('messages.total'),
            __('messages.confirmed'),
            __('messages.rejected'),
            __('messages.pending'),
        ];

        foreach ($this->stats['top_drivers'] as $driver) {
            $data[] = [
                $driver['driver_name'],
                $driver['total_count'],
                $driver['confirmed_count'],
                $driver['rejected_count'],
                $driver['pending_count'],
            ];
        }

        $data[] = ['', '', '', '', '']; // Empty row

        // By type section
        $data[] = [__('messages.export_center_violations_by_type'), '', '', ''];
        $data[] = [
            __('messages.violation_type'),
            __('messages.count'),
        ];

        foreach ($this->stats['by_type'] as $type) {
            $data[] = [
                $type['type_name'],
                $type['count'],
            ];
        }

        return $data;
    }

    public function headings(): array
    {
        return [
            __('messages.export_center_violations_statistics'),
            '',
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
        $topDriversRow = 7;
        $byTypeRow = $topDriversRow + count($this->stats['top_drivers']) + 3;

        $sheet->getStyle("A{$summaryRow}")->getFont()->setBold(true);
        $sheet->getStyle("A{$topDriversRow}")->getFont()->setBold(true);
        $sheet->getStyle("A{$byTypeRow}")->getFont()->setBold(true);

        // Style table headers
        $topDriversHeaderRow = $topDriversRow + 1;
        $byTypeHeaderRow = $byTypeRow + 1;

        $sheet->getStyle("A{$topDriversHeaderRow}:E{$topDriversHeaderRow}")->getFont()->setBold(true);
        $sheet->getStyle("A{$topDriversHeaderRow}:E{$topDriversHeaderRow}")->getFill()
            ->setFillType('solid')->getStartColor()->setRGB('E9ECEF');

        $sheet->getStyle("A{$byTypeHeaderRow}:B{$byTypeHeaderRow}")->getFont()->setBold(true);
        $sheet->getStyle("A{$byTypeHeaderRow}:B{$byTypeHeaderRow}")->getFill()
            ->setFillType('solid')->getStartColor()->setRGB('E9ECEF');

        return [];
    }
}

