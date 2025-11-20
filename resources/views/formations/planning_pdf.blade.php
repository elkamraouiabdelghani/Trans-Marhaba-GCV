<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <title>{{ __('messages.formation_planning_title') }} - {{ $selectedYear }}</title>
    <style>
        body {
            font-family: "DejaVu Sans", Arial, sans-serif;
            margin: 20px;
            color: #1f2937;
        }
        h1 {
            font-size: 24px;
            margin-bottom: 5px;
        }
        .subtitle {
            color: #6b7280;
            margin-bottom: 20px;
        }
        .grid {
            width: 100%;
            border-collapse: separate;
            border-spacing: 12px;
        }
        .grid td {
            width: 25%;
            vertical-align: top;
            padding: 0;
        }
        .month-card {
            border: 1px solid #e5e7eb;
            border-radius: 8px;
            padding: 10px 12px;
            box-sizing: border-box;
            min-height: 140px;
        }
        .month-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            font-weight: 600;
            margin-bottom: 8px;
        }
        .badge {
            display: inline-block;
            padding: 2px 8px;
            border-radius: 999px;
            font-size: 11px;
            text-transform: uppercase;
            letter-spacing: 0.04em;
        }
        .badge-success {
            background: rgba(34, 197, 94, 0.15);
            color: #15803d;
        }
        .badge-warning {
            background: rgba(250, 204, 21, 0.2);
            color: #92400e;
        }
        .formation-card {
            border: 1px solid #f3f4f6;
            border-radius: 6px;
            padding: 6px 8px;
            margin-bottom: 6px;
            background: #f9fafb;
        }
        .formation-title {
            font-size: 13px;
            font-weight: 600;
            margin: 0 0 4px 0;
            color: #1d4ed8;
        }
        .meta {
            font-size: 11px;
            color: #4b5563;
            display: flex;
            gap: 8px;
            flex-wrap: wrap;
        }
        .empty {
            text-align: center;
            color: #9ca3af;
            padding: 16px 0;
            font-size: 12px;
        }
    </style>
</head>
<body>
    <h1>{{ __('messages.formation_planning_title') }} – {{ $selectedYear }}</h1>
    <div class="subtitle">
        {{ __('messages.formation_planning_subtitle') }}
        &nbsp;|&nbsp;
        {{ __('messages.total') }}: {{ $planningTotals['total'] ?? 0 }}
        &nbsp;•&nbsp;
        {{ __('messages.realized') }}: {{ $planningTotals['realized'] ?? 0 }}
        &nbsp;•&nbsp;
        {{ __('messages.planned') }}: {{ $planningTotals['planned'] ?? 0 }}
    </div>

    <table class="grid">
        @foreach(array_chunk(range(1, 12), 4) as $monthRow)
            <tr>
                @foreach($monthRow as $month)
                    @php
                        $monthFormations = $formationsByMonth->get((string)$month) ?? collect();
                        $monthName = \Carbon\Carbon::create()->month($month)->translatedFormat('F');
                    @endphp
                    <td>
                        <div class="month-card">
                            <div class="month-header">
                                <span>{{ ucfirst($monthName) }}</span>
                                <span class="badge" style="background:#e5e7eb;color:#374151;">{{ $monthFormations->count() }}</span>
                            </div>

                            @forelse($monthFormations as $formation)
                                @php
                                    $isRealized = $formation->status === 'realized';
                                    $badgeClass = $isRealized ? 'badge-success' : 'badge-warning';
                                    $statusLabel = $isRealized ? __('messages.realized') : __('messages.planned');
                                    $realizingDate = $formation->realizing_date
                                        ? \Carbon\Carbon::parse($formation->realizing_date)->translatedFormat('d F')
                                        : __('messages.date_not_defined');
                                @endphp
                                <div class="formation-card">
                                    <div class="formation-title">{{ $formation->name }}</div>
                                    <div class="meta">
                                        <span>{{ $realizingDate }}</span>
                                        @if($formation->duree)
                                            <span>• {{ $formation->duree }} {{ __('messages.days_short') }}</span>
                                        @endif
                                    </div>
                                    <div class="meta" style="margin-top:4px;">
                                        <span class="badge {{ $badgeClass }}">{{ $statusLabel }}</span>
                                    </div>
                                </div>
                            @empty
                                <div class="empty">{{ __('messages.no_formations_month') }}</div>
                            @endforelse
                        </div>
                    </td>
                @endforeach
            </tr>
        @endforeach
    </table>
</body>
</html>

