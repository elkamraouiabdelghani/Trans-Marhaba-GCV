<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="UTF-8">
    <style>
        body {
            font-family: DejaVu Sans, Arial, sans-serif;
            font-size: 12px;
            color: #1f2933;
        }
        h1, h2, h3, h4, h5, h6 {
            margin: 0 0 8px 0;
        }
        .meta {
            margin-bottom: 16px;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 12px;
        }
        th, td {
            border: 1px solid #d1d5db;
            padding: 8px;
            text-align: left;
        }
        th {
            background: #f8fafc;
            font-weight: bold;
        }
        .text-muted {
            color: #6b7280;
        }
    </style>
</head>
<body>
    <h2>{{ __('messages.violations_table') }} - {{ $driver->full_name }}</h2>
    <div class="meta">
        <div>{{ __('messages.date_from') }}: <strong>{{ $filters['date_from'] ?? __('messages.not_available') }}</strong></div>
        <div>{{ __('messages.date_to') }}: <strong>{{ $filters['date_to'] ?? __('messages.not_available') }}</strong></div>
        @if(!empty($filters['violation_type_id']) || !empty($filters['status']))
            <div class="text-muted">
                @if(!empty($filters['violation_type_id']))
                    {{ __('messages.violation_type') }}: {{ optional($violations->firstWhere('violation_type_id', $filters['violation_type_id']))->violationType->name ?? __('messages.not_specified') }}
                @endif
                @if(!empty($filters['status']))
                    | {{ __('messages.status') }}: {{ __(
                        $filters['status'] === 'confirmed' ? 'messages.confirmed' :
                        ($filters['status'] === 'rejected' ? 'messages.rejected' : 'messages.pending')
                    ) }}
                @endif
            </div>
        @endif
    </div>

    <table>
        <thead>
            <tr>
                <th>#</th>
                <th>{{ __('messages.violation_date') }}</th>
                <th>{{ __('messages.violation_type') }}</th>
                <th>{{ __('messages.status') }}</th>
                <th>{{ __('messages.location') }}</th>
                <th>{{ __('messages.violation_time') }}</th>
                <th>{{ __('messages.violation_speed') }}</th>
                <th>{{ __('messages.violation_speed_limit') }}</th>
                <th>{{ __('messages.violation_duration') }}</th>
                <th>{{ __('messages.violation_distance') }}</th>
                <th>{{ __('messages.vehicle') }}</th>
                <th>{{ __('messages.description') }}</th>
                <th>{{ __('messages.violation_analysis') }}</th>
                <th>{{ __('messages.violation_action_plan') }}</th>
                <th>{{ __('messages.violation_evidence') }}</th>
            </tr>
        </thead>
        <tbody>
            @foreach($violations as $violation)
                <tr>
                    <td>{{ $violation->id }}</td>
                    <td>{{ optional($violation->violation_date)->format('d/m/Y') ?? __('messages.not_available') }}</td>
                    <td>{{ $violation->violationType->name ?? __('messages.not_specified') }}</td>
                    <td>
                        @php
                            $statusLabels = [
                                'pending' => __('messages.pending'),
                                'confirmed' => __('messages.confirmed'),
                                'rejected' => __('messages.rejected'),
                            ];
                        @endphp
                        {{ $statusLabels[$violation->status] ?? ucfirst($violation->status ?? __('messages.not_available')) }}
                    </td>
                    <td>{{ $violation->location ?? __('messages.not_available') }}</td>
                    <td>{{ optional($violation->violation_time)->format('H:i') ?? __('messages.not_available') }}</td>
                    <td>{{ $violation->speed !== null ? number_format($violation->speed, 2) . ' km/h' : __('messages.not_available') }}</td>
                    <td>{{ $violation->speed_limit !== null ? number_format($violation->speed_limit, 2) . ' km/h' : __('messages.not_available') }}</td>
                    @php
                        $durationSeconds = $violation->violation_duration_seconds;
                        $durationLabel = $durationSeconds ? sprintf('%02dm %02ds', intdiv($durationSeconds, 60), $durationSeconds % 60) : null;
                    @endphp
                    <td>{{ $durationLabel ?? __('messages.not_available') }}</td>
                    <td>{{ $violation->violation_distance_km !== null ? number_format($violation->violation_distance_km, 2) . ' km' : __('messages.not_available') }}</td>
                    <td>{{ $violation->vehicle->license_plate ?? __('messages.not_available') }}</td>
                    <td>{{ $violation->description ?? __('messages.not_available') }}</td>
                    <td>{{ $violation->analysis ?? __('messages.not_available') }}</td>
                    <td>{{ $violation->action_plan ?? __('messages.not_available') }}</td>
                    <td>
                        @if($violation->evidence_path)
                            {{ __('messages.yes') }}
                            @if($violation->evidence_original_name)
                                <div class="text-muted small">{{ $violation->evidence_original_name }}</div>
                            @endif
                        @else
                            {{ __('messages.no') }}
                        @endif
                    </td>
                </tr>
            @endforeach
        </tbody>
    </table>
</body>
</html>

