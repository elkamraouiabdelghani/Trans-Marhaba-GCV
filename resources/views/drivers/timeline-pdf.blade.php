<!DOCTYPE html>
<html lang="{{ app()->getLocale() }}">
<head>
    <meta charset="UTF-8">
    <title>{{ __('messages.timeline_activity') }} - {{ $driver->full_name }}</title>
    <style>
        body {
            font-family: DejaVu Sans, sans-serif;
            font-size: 10px;
            color: #1F2937;
            margin: 0;
            padding: 20px;
            background: #FFFFFF;
        }
        .header {
            text-align: center;
            margin-bottom: 20px;
            border-bottom: 2px solid #1F2937;
            padding-bottom: 15px;
        }
        h1 {
            font-size: 18px;
            margin: 0 0 10px 0;
            text-transform: uppercase;
            color: #111827;
        }
        .driver-info {
            font-size: 11px;
            color: #6B7280;
        }
        .period-info {
            font-size: 10px;
            color: #6B7280;
            margin-top: 5px;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 15px;
        }
        th, td {
            padding: 8px;
            border: 1px solid #E5E7EB;
            text-align: left;
        }
        th {
            background-color: #F3F4F6;
            font-weight: 600;
            font-size: 9px;
        }
        td {
            background-color: #FFFFFF;
            font-size: 9px;
        }
        .day-row {
            background-color: #F9FAFB;
        }
        .violation-row {
            background-color: #FEF2F2;
        }
        .compliant {
            color: #059669;
            font-weight: 600;
        }
        .non-compliant {
            color: #DC2626;
            font-weight: 600;
        }
        .summary {
            margin-top: 20px;
            padding: 15px;
            background-color: #F3F4F6;
            border-radius: 4px;
        }
        .summary-title {
            font-size: 12px;
            font-weight: 600;
            margin-bottom: 10px;
        }
        .summary-item {
            font-size: 10px;
            margin: 5px 0;
        }
        .footer {
            margin-top: 30px;
            text-align: right;
            font-size: 9px;
            color: #6B7280;
            border-top: 1px solid #E5E7EB;
            padding-top: 10px;
        }
    </style>
</head>
<body>
    <div class="header">
        <h1>{{ __('messages.timeline_activity') }}</h1>
        <div class="driver-info">
            <strong>{{ __('messages.driver') }}:</strong> {{ $driver->full_name }}<br>
            <strong>{{ __('messages.license_number') }}:</strong> {{ $driver->license_number ?? __('messages.not_available') }}<br>
            <strong>{{ __('messages.flotte') }}:</strong> {{ $driver->flotte->name ?? __('messages.not_available') }}
        </div>
        <div class="period-info">
            <strong>{{ __('messages.date_from') }}:</strong> {{ $dateFrom }} | 
            <strong>{{ __('messages.date_to') }}:</strong> {{ $dateTo }}
        </div>
    </div>

    @php
        $formatDuration = function ($decimalHours) {
            $decimalHours = $decimalHours ?? 0;
            $totalMinutes = (int) round($decimalHours * 60);
            $hours = intdiv($totalMinutes, 60);
            $minutes = $totalMinutes % 60;
            return sprintf('%02d:%02d', $hours, $minutes);
        };
    @endphp

    <table>
        <thead>
            <tr>
                <th>{{ __('messages.date') }}</th>
                <th>{{ __('messages.day_name') ?? 'Day' }}</th>
                <th>{{ __('messages.flotte') }}</th>
                <th>{{ __('messages.asset_description') ?? 'Asset Description' }}</th>
                <th>{{ __('messages.driver') }}</th>
                <th>{{ __('messages.start_time') }}</th>
                <th>{{ __('messages.end_time') }}</th>
                <th>{{ __('messages.work_time') ?? 'Work Time' }}</th>
                <th>{{ __('messages.driving_time') ?? __('messages.driving_hours') }}</th>
                <th>{{ __('messages.rest_time') ?? __('messages.rest_hours') }}</th>
                <th>{{ __('messages.rest_daily') ?? 'Daily Rest' }}</th>
                <th>{{ __('messages.raison') ?? 'Reason' }}</th>
                <th>{{ __('messages.start_location') ?? 'Start Location' }}</th>
                <th>{{ __('messages.overnight_location') ?? 'Overnight Location' }}</th>
                <th>{{ __('messages.status') }}</th>
                <th>{{ __('messages.violations') }}</th>
            </tr>
        </thead>
        <tbody>
            @forelse($timelineData as $day)
                @php
                    $hasViolations = isset($day['violations']) && count($day['violations']) > 0;
                    $isCompliant = isset($day['is_compliant']) ? $day['is_compliant'] : !$hasViolations;
                @endphp
                <tr class="{{ $hasViolations ? 'violation-row' : 'day-row' }}">
                    <td>{{ $day['date_label'] }}</td>
                    <td>{{ $day['day_name'] }}</td>
                    <td>{{ $day['flotte'] ?? '-' }}</td>
                    <td>{{ $day['asset_description'] ?? '-' }}</td>
                    <td>{{ $day['driver_name'] ?? ($driver->full_name ?? '-') }}</td>
                    <td>{{ $day['start_time'] ?? '-' }}</td>
                    <td>{{ $day['end_time'] ?? '-' }}</td>
                    <td>{{ $formatDuration($day['work_hours'] ?? 0) }}</td>
                    <td>{{ $formatDuration($day['driving_hours'] ?? 0) }}</td>
                    <td>{{ $formatDuration($day['rest_hours'] ?? 0) }}</td>
                    <td>{{ $formatDuration($day['rest_daily_hours'] ?? 0) }}</td>
                    <td>{{ $day['raison'] ?? '-' }}</td>
                    <td>{{ $day['start_location'] ?? '-' }}</td>
                    <td>{{ $day['overnight_location'] ?? '-' }}</td>
                    <td class="{{ $isCompliant ? 'compliant' : 'non-compliant' }}">
                        {{ $isCompliant ? __('messages.compliant') : __('messages.non_compliant') }}
                    </td>
                    <td>{{ count($day['violations'] ?? []) }}</td>
                </tr>
                @if($hasViolations)
                    @foreach($day['violations'] as $violation)
                        <tr class="violation-row">
                            <td colspan="2"></td>
                            <td colspan="8" style="font-size: 8px; padding-left: 20px;">
                                <strong>{{ $violation['time'] }}</strong> - 
                                {{ $violation['type_label'] }}: 
                                {{ $violation['rule'] }} 
                                ({{ __('messages.severity') }}: {{ $violation['severity_label'] }})
                            </td>
                        </tr>
                    @endforeach
                @endif
            @empty
                <tr>
                    <td colspan="16" style="text-align: center; padding: 20px;">
                        {{ __('messages.no_activity_data') }}
                    </td>
                </tr>
            @endforelse
        </tbody>
    </table>

    <div class="summary">
        <div class="summary-title">{{ __('messages.summary') ?? 'Summary' }}</div>
        <div class="summary-item">
            <strong>{{ __('messages.total_driving_hours') ?? 'Total Driving Hours' }}:</strong> {{ $formatDuration($totalDrivingHours) }}
        </div>
        <div class="summary-item">
            <strong>{{ __('messages.total_violations') }}:</strong> {{ count($violations) }}
        </div>
        <div class="summary-item">
            <strong>{{ __('messages.compliant_days') ?? 'Compliant Days' }}:</strong> 
            {{ collect($timelineData)->filter(fn($day) => ($day['is_compliant'] ?? false))->count() }} / {{ count($timelineData) }}
        </div>
    </div>

    <div class="footer">
        {{ __('messages.generated_at') ?? 'Generated at' }}: {{ now()->format('d/m/Y H:i') }}
    </div>
</body>
</html>

