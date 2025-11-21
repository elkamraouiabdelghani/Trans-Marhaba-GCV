<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="UTF-8">
    <title>{{ __('messages.this_month_actions_calendar') }} - {{ $currentMonthLabel ?? '' }}</title>
    <style>
        body {
            font-family: DejaVu Sans, sans-serif;
            font-size: 12px;
            color: #1f2933;
            margin: 0;
            padding: 20px;
        }
        h1 {
            font-size: 20px;
            margin-bottom: 5px;
        }
        .subtitle {
            color: #6b7280;
            margin-bottom: 20px;
        }
        .legend {
            margin-bottom: 15px;
            display: flex;
            gap: 20px;
        }
        .legend-item {
            display: flex;
            align-items: center;
            gap: 6px;
        }
        .legend-dot {
            width: 12px;
            height: 12px;
            border-radius: 50%;
            display: inline-block;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            table-layout: fixed;
        }
        th {
            text-transform: uppercase;
            font-size: 11px;
            color: #4b5563;
            padding: 8px;
            background: #f4f6f9;
            border: 1px solid #e5e7eb;
        }
        td {
            border: 1px solid #e5e7eb;
            padding: 6px;
            vertical-align: top;
            height: 110px;
        }
        .day-number {
            font-weight: 600;
            margin-bottom: 4px;
        }
        .day-muted {
            background-color: #f9fafb;
            color: #9ca3af;
        }
        .event {
            border-radius: 4px;
            padding: 4px 6px;
            margin-bottom: 4px;
            font-size: 10px;
        }
        .event-primary {
            background-color: #dbeafe;
            color: #1e3a8a;
        }
        .event-warning {
            background-color: #fef3c7;
            color: #92400e;
        }
        .event-success {
            background-color: #d1fae5;
            color: #065f46;
        }
        .meta {
            display: block;
            font-size: 9px;
            color: #6b7280;
        }
    </style>
</head>
<body>
    <h1>{{ __('messages.this_month_actions_calendar') }}</h1>
    <div class="subtitle">{{ $currentMonthLabel ?? '' }}</div>

    <div class="legend">
        <div class="legend-item">
            <span class="legend-dot" style="background:#0d6efd;"></span>
            <span>{{ __('messages.calendar_legend_formations') }}</span>
        </div>
        <div class="legend-item">
            <span class="legend-dot" style="background:#fbbf24;"></span>
            <span>{{ __('messages.calendar_legend_tbt') }}</span>
        </div>
        <div class="legend-item">
            <span class="legend-dot" style="background:#10b981;"></span>
            <span>{{ __('messages.calendar_legend_coaching') }}</span>
        </div>
    </div>

    <table>
        <thead>
            <tr>
                @foreach(($weekdayLabels ?? []) as $weekday)
                    <th>{{ $weekday }}</th>
                @endforeach
            </tr>
        </thead>
        <tbody>
            @foreach(($calendarWeeks ?? collect()) as $week)
                <tr>
                    @foreach($week as $day)
                        @php
                            $dayEvents = collect($day['events'] ?? []);
                        @endphp
                        <td class="{{ $day['isCurrentMonth'] ? '' : 'day-muted' }}">
                            <div class="day-number">{{ $day['date']->format('j') }}</div>
                            @foreach($dayEvents as $event)
                                <div class="event event-{{ $event['color'] }}">
                                    <strong>{{ $event['title'] }}</strong>
                                    @if(!empty($event['details']))
                                        <span class="meta">{{ $event['details'] }}</span>
                                    @endif
                                    @if(!empty($event['meta']))
                                        <span class="meta">{{ $event['meta'] }}</span>
                                    @endif
                                </div>
                            @endforeach
                        </td>
                    @endforeach
                </tr>
            @endforeach
        </tbody>
    </table>
</body>
</html>

