<?php

namespace App\Exports;

use App\Models\User;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class AdministrationRolesExport implements FromCollection, WithHeadings, WithMapping, ShouldAutoSize, WithStyles
{
    private string $status;
    private string $search;

    public function __construct(string $status = 'all', string $search = '')
    {
        $this->status = $status ?: 'all';
        $this->search = trim($search);
    }

    public function collection(): Collection
    {
        $query = User::query()
            ->where('role', '!=', 'admin');

        if ($this->status !== 'all') {
            $query->where('status', $this->status);
        } else {
            $query->where(function ($q) {
                $q->whereNull('status')
                    ->orWhere('status', '!=', 'terminated');
            });
        }

        if ($this->search !== '') {
            $query->where(function ($q) {
                $q->where('name', 'like', "%{$this->search}%")
                    ->orWhere('email', 'like', "%{$this->search}%")
                    ->orWhere('phone', 'like', "%{$this->search}%");
            });
        }

        return $query->orderBy('name')->get();
    }

    public function headings(): array
    {
        return [
            __('messages.name'),
            __('messages.email'),
            __('messages.phone'),
            __('messages.department'),
            __('messages.role'),
            __('messages.status'),
            __('messages.date_integration'),
        ];
    }

    public function map($user): array
    {
        $statusKey = 'status_' . ($user->status ?? 'inactive');
        $statusLabel = trans('messages.' . $statusKey);

        if ($statusLabel === 'messages.' . $statusKey) {
            $statusLabel = trans('messages.not_available');
        }

        return [
            $user->name,
            $user->email,
            $user->phone ?? __('messages.not_available'),
            $user->department ?? __('messages.not_available'),
            Str::upper($user->role ?? __('messages.not_available')),
            $statusLabel,
            optional($user->date_integration)->format(__('messages.date_format_short')) ?? __('messages.not_available'),
        ];
    }

    public function styles(Worksheet $sheet): array
    {
        return [
            1 => [
                'font' => [
                    'bold' => true,
                    'color' => ['rgb' => 'FFFFFF'],
                ],
                'fill' => [
                    'fillType' => Fill::FILL_SOLID,
                    'startColor' => ['rgb' => '198754'], // Bootstrap success color
                ],
            ],
        ];
    }
}

