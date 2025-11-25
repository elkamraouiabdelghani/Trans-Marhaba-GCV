<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="UTF-8">
    <style>
        body {
            font-family: DejaVu Sans, Arial, sans-serif;
            font-size: 12px;
            color: #111827;
        }
        h1, h2, h3, h4, h5, h6 {
            margin: 0 0 10px;
        }
        .section {
            margin-bottom: 18px;
            padding: 12px;
            border: 1px solid #e5e7eb;
            border-radius: 8px;
        }
        .section-title {
            font-size: 13px;
            text-transform: uppercase;
            color: #6b7280;
            margin-bottom: 6px;
        }
        table {
            width: 100%;
            border-collapse: collapse;
        }
        th, td {
            text-align: left;
            padding: 6px 8px;
            border: 1px solid #e5e7eb;
        }
        th {
            background: #f3f4f6;
            font-weight: 600;
            text-transform: uppercase;
            font-size: 11px;
        }
    </style>
</head>
<body>
    <h2>{{ __('messages.violation') }} #{{ $violation->id }}</h2>
    <p>{{ __('messages.generated_at') ?? 'Generated at' }}: {{ now()->format('d/m/Y H:i') }}</p>

    @php
        $durationSeconds = $violation->violation_duration_seconds;
        $durationLabel = $durationSeconds ? sprintf('%02dm %02ds', intdiv($durationSeconds, 60), $durationSeconds % 60) : null;

        $violationTimeValue = $violation->violation_time;
        $violationTimeLabel = null;
        if ($violationTimeValue instanceof \Carbon\Carbon) {
            $violationTimeLabel = $violationTimeValue->format('H:i');
        } elseif (is_string($violationTimeValue) && $violationTimeValue !== '') {
            try {
                $violationTimeLabel = \Carbon\Carbon::createFromFormat('H:i:s', $violationTimeValue)->format('H:i');
            } catch (\Throwable $e) {
                $violationTimeLabel = $violationTimeValue;
            }
        }
    @endphp

    <div class="section">
        <div class="section-title">{{ __('messages.general_information') ?? 'General information' }}</div>
        <table>
            <tbody>
                <tr>
                    <th>{{ __('messages.driver') }}</th>
                    <td>{{ $violation->driver?->full_name ?? __('messages.not_available') }}</td>
                </tr>
                <tr>
                    <th>{{ __('messages.vehicle') }}</th>
                    <td>{{ $violation->vehicle?->license_plate ?? __('messages.not_available') }}</td>
                </tr>
                <tr>
                    <th>{{ __('messages.violation_type') }}</th>
                    <td>{{ $violation->violationType?->name ?? __('messages.not_specified') }}</td>
                </tr>
                <tr>
                    <th>{{ __('messages.location') }}</th>
                    <td>{{ $violation->location ?? __('messages.not_available') }}</td>
                </tr>
                <tr>
                    <th>{{ __('messages.violation_date') }}</th>
                    <td>{{ $violation->violation_date?->format('d/m/Y') ?? __('messages.not_available') }}</td>
                </tr>
                @if($violationTimeLabel)
                    <tr>
                        <th>{{ __('messages.violation_time') }}</th>
                        <td>{{ $violationTimeLabel }}</td>
                    </tr>
                @endif
                @if($durationLabel)
                    <tr>
                        <th>{{ __('messages.violation_duration') }}</th>
                        <td>{{ $durationLabel }}</td>
                    </tr>
                @endif
                <tr>
                    <th>{{ __('messages.status') }}</th>
                    <td>{{ __(
                        $violation->status === 'confirmed' ? 'messages.confirmed' :
                        ($violation->status === 'rejected' ? 'messages.rejected' : 'messages.pending')
                    ) }}</td>
                </tr>
            </tbody>
        </table>
    </div>

    @if($violationTimeLabel || $violation->speed !== null || $violation->speed_limit !== null || $durationLabel || $violation->violation_distance_km !== null)
        <div class="section">
            <div class="section-title">{{ __('messages.violation_metrics') }}</div>
            <table>
                <tbody>
                    @if($violation->speed !== null)
                        <tr>
                            <th>{{ __('messages.violation_speed') }}</th>
                            <td>{{ number_format($violation->speed, 2) }} km/h</td>
                        </tr>
                    @endif
                    @if($violation->speed_limit !== null)
                        <tr>
                            <th>{{ __('messages.violation_speed_limit') }}</th>
                            <td>{{ number_format($violation->speed_limit, 2) }} km/h</td>
                        </tr>
                    @endif
                    @if($violation->violation_distance_km !== null)
                        <tr>
                            <th>{{ __('messages.violation_distance') }}</th>
                            <td>{{ number_format($violation->violation_distance_km, 2) }} km</td>
                        </tr>
                    @endif
                </tbody>
            </table>
        </div>
    @endif

    @if($violation->description)
        <div class="section">
            <div class="section-title">{{ __('messages.description') }}</div>
            <p>{{ $violation->description }}</p>
        </div>
    @endif

    @if($violation->notes)
        <div class="section">
            <div class="section-title">{{ __('messages.notes') }}</div>
            <p>{{ $violation->notes }}</p>
        </div>
    @endif

    @if($violation->actionPlan)
        <div class="section">
            <div class="section-title">{{ __('messages.violation_actions') }}</div>
            <p style="margin: 0 0 10px; color:#6b7280;">{{ __('messages.violation_action_plan_subtitle') }}</p>
            <table>
                <tbody>
                    <tr>
                        <th>{{ __('messages.violation_analysis') }}</th>
                        <td>{{ $violation->actionPlan->analysis ?? __('messages.not_available') }}</td>
                    </tr>
                    <tr>
                        <th>{{ __('messages.violation_action_plan') }}</th>
                        <td>{{ $violation->actionPlan->action_plan ?? __('messages.not_available') }}</td>
                    </tr>
                    <tr>
                        <th>{{ __('messages.violation_evidence') }}</th>
                        <td>
                            @if($violation->actionPlan->evidence_path)
                                {{ __('messages.yes') }}
                                @if($violation->actionPlan->evidence_original_name)
                                    – {{ $violation->actionPlan->evidence_original_name }}
                                @endif
                                @php
                                    $evidenceUrl = route('violations.action-plan.evidence', $violation);
                                    $extension = strtolower(pathinfo($violation->actionPlan->evidence_original_name ?? '', PATHINFO_EXTENSION));
                                    $imageExtensions = ['jpg', 'jpeg', 'png', 'gif', 'bmp', 'webp'];
                                @endphp
                                <div style="margin-top:4px;">
                                    <a href="{{ $evidenceUrl }}" target="_blank" style="color:#1d4ed8; text-decoration:none;">
                                        <i>{{ __('messages.view') }}</i>
                                    </a>
                                    @if($extension && in_array($extension, $imageExtensions, true))
                                        <span style="color:#6b7280;">({{ __('messages.preview_available') ?? 'Image preview available' }})</span>
                                    @endif
                                </div>
                            @else
                                {{ __('messages.no') }}
                            @endif
                        </td>
                    </tr>
                    <tr>
                        <th>{{ __('messages.created_at') }}</th>
                        <td>{{ $violation->actionPlan->created_at?->format('d/m/Y H:i') ?? __('messages.not_available') }}</td>
                    </tr>
                    <tr>
                        <th>{{ __('messages.updated_at') }}</th>
                        <td>{{ $violation->actionPlan->updated_at?->format('d/m/Y H:i') ?? __('messages.not_available') }}</td>
                    </tr>
                </tbody>
            </table>
        </div>
    @endif

    <div class="section">
        <div class="section-title">{{ __('messages.metadata') ?? 'Metadata' }}</div>
        <table>
            <tbody>
                <tr>
                    <th>{{ __('messages.created_at') }}</th>
                    <td>{{ $violation->created_at?->format('d/m/Y H:i') ?? __('messages.not_available') }}</td>
                </tr>
                <tr>
                    <th>{{ __('messages.created_by') }}</th>
                    <td>{{ $violation->createdBy?->name ?? __('messages.not_available') }}</td>
                </tr>
                <tr>
                    <th>{{ __('messages.updated_at') }}</th>
                    <td>{{ $violation->updated_at?->format('d/m/Y H:i') ?? __('messages.not_available') }}</td>
                </tr>
                @if($violation->document_path)
                    <tr>
                        <th>{{ __('messages.document') }}</th>
                        <td>
                            <a href="{{ route('violations.document', $violation) }}" target="_blank" style="color:#1d4ed8; text-decoration:none;">
                                {{ __('messages.download') }}
                            </a>
                        </td>
                    </tr>
                @endif
            </tbody>
        </table>
    </div>
</body>
</html>


