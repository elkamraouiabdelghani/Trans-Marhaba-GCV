<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Violations Statistics Report</title>
    <style>
        @page { margin: 15px 10px; }
        body { 
            font-family: DejaVu Sans, sans-serif; 
            font-size: 10px; 
            color: #1f2937; 
        }
        .header { 
            margin-bottom: 15px; 
            padding-bottom: 10px;
            border-bottom: 2px solid #1f2937;
        }
        .header-top {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            margin-bottom: 10px;
        }
        .company-name {
            font-size: 18px;
            font-weight: bold;
            color: #000;
            text-transform: uppercase;
        }
        .report-title {
            text-align: center;
            font-size: 16px;
            font-weight: bold;
            color: #000;
            margin-top: 10px;
            text-transform: uppercase;
        }
        .period-info {
            text-align: center;
            font-size: 11px;
            color: #6b7280;
            margin-bottom: 15px;
        }
        .stats-cards {
            display: table;
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
        }
        .stats-row {
            display: table-row;
        }
        .stats-cell {
            display: table-cell;
            width: 25%;
            padding: 10px;
            border: 1px solid #e5e7eb;
            text-align: center;
            vertical-align: middle;
        }
        .stats-label {
            font-size: 9px;
            color: #6b7280;
            margin-bottom: 5px;
        }
        .stats-value {
            font-size: 20px;
            font-weight: bold;
            color: #000;
        }
        table { 
            width: 100%; 
            border-collapse: collapse; 
            margin-bottom: 15px; 
            font-size: 9px;
        }
        th, td { 
            border: 1px solid #94a3b8; 
            padding: 6px 4px; 
            text-align: left;
        }
        th { 
            background-color: #f3f4f6; 
            color: #000; 
            font-weight: bold; 
            font-size: 9px;
            text-align: center;
        }
        td {
            background-color: #ffffff;
            font-size: 8px;
        }
        .section-title {
            font-size: 12px;
            font-weight: bold;
            color: #000;
            margin-top: 20px;
            margin-bottom: 10px;
            padding-bottom: 5px;
            border-bottom: 1px solid #e5e7eb;
        }
        .footer {
            margin-top: 20px;
            padding-top: 10px;
            border-top: 1px solid #e5e7eb;
            font-size: 8px;
            color: #6b7280;
            text-align: center;
        }
    </style>
</head>
<body>
    <div class="header">
        <div class="header-top">
            <div class="company-name">TRANS-MARHABA</div>
            <div style="text-align: right; font-size: 8px;">
                <div>Generated: {{ now()->format('d/m/Y H:i') }}</div>
            </div>
        </div>
        <div class="report-title">{{ __('messages.export_center_violations_statistics') }}</div>
        <div class="period-info">
            {{ __('messages.export_center_period_from') }} {{ $startDate->format('d/m/Y') }} {{ __('messages.export_center_period_to') }} {{ $endDate->format('d/m/Y') }}
        </div>
    </div>

    {{-- Summary Statistics --}}
    <div class="section-title">{{ __('messages.export_center_summary') }}</div>
    <div class="stats-cards">
        <div class="stats-row">
            <div class="stats-cell">
                <div class="stats-label">{{ __('messages.total') }}</div>
                <div class="stats-value">{{ $stats['total'] ?? 0 }}</div>
            </div>
            <div class="stats-cell">
                <div class="stats-label">{{ __('messages.confirmed') }}</div>
                <div class="stats-value">{{ $stats['confirmed'] ?? 0 }}</div>
            </div>
            <div class="stats-cell">
                <div class="stats-label">{{ __('messages.rejected') }}</div>
                <div class="stats-value">{{ $stats['rejected'] ?? 0 }}</div>
            </div>
            <div class="stats-cell">
                <div class="stats-label">{{ __('messages.pending') }}</div>
                <div class="stats-value">{{ $stats['pending'] ?? 0 }}</div>
            </div>
        </div>
    </div>

    {{-- Top 5 Drivers --}}
    <div class="section-title">{{ __('messages.export_center_top_drivers') }}</div>
    <table>
        <thead>
            <tr>
                <th style="width: 40%;">{{ __('messages.driver') }}</th>
                <th style="width: 15%;">{{ __('messages.total') }}</th>
                <th style="width: 15%;">{{ __('messages.confirmed') }}</th>
                <th style="width: 15%;">{{ __('messages.rejected') }}</th>
                <th style="width: 15%;">{{ __('messages.pending') }}</th>
            </tr>
        </thead>
        <tbody>
            @forelse($stats['top_drivers'] ?? [] as $driver)
                <tr>
                    <td>{{ $driver['driver_name'] }}</td>
                    <td style="text-align: center;">{{ $driver['total_count'] }}</td>
                    <td style="text-align: center;">{{ $driver['confirmed_count'] }}</td>
                    <td style="text-align: center;">{{ $driver['rejected_count'] }}</td>
                    <td style="text-align: center;">{{ $driver['pending_count'] }}</td>
                </tr>
            @empty
                <tr>
                    <td colspan="5" style="text-align: center; color: #6b7280;">{{ __('messages.export_center_no_data') }}</td>
                </tr>
            @endforelse
        </tbody>
    </table>

    {{-- Violations by Type --}}
    @if(isset($stats['by_type']) && count($stats['by_type']) > 0)
        <div class="section-title">{{ __('messages.export_center_violations_by_type') }}</div>
        <table>
            <thead>
                <tr>
                    <th style="width: 70%;">{{ __('messages.violation_type') }}</th>
                    <th style="width: 30%;">{{ __('messages.total') }}</th>
                </tr>
            </thead>
            <tbody>
                @foreach($stats['by_type'] as $type)
                    <tr>
                        <td>{{ $type['type_name'] }}</td>
                        <td style="text-align: center;">{{ $type['count'] }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    @endif

    {{-- Footer --}}
    <div class="footer">
        <div>Document généré le {{ now()->format('d/m/Y H:i') }}</div>
        <div>Export Center - {{ __('messages.export_center_violations_statistics') }}</div>
    </div>
</body>
</html>

