<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>{{ __('messages.formations_suivi_title') }}</title>
    <style>
        body { font-family: DejaVu Sans, sans-serif; margin: 24px; color: #111; }
        h1, h2, h3, h4 { margin: 0 0 12px 0; }
        .header { margin-bottom: 20px; }
        .small { font-size: 12px; color: #555; }
        .summary { margin: 10px 0 20px 0; }
        .summary div { margin: 4px 0; }
        table { width: 100%; border-collapse: collapse; margin-top: 12px; }
        th, td { border: 1px solid #ddd; padding: 6px; font-size: 12px; }
        th { background: #f5f5f5; text-align: left; }
        .mt-3 { margin-top: 12px; }
    </style>
</head>
<body>
    <div class="header">
        <h2>Trans Marhaba</h2>
        <h1>{{ __('messages.formations_suivi_title') }}</h1>
        <div class="small">{{ __('messages.formations_suivi_subtitle') }}</div>
    </div>

    <div class="summary">
        <div><strong>{{ __('messages.formations_suivi_type') }}:</strong> {{ $type === 'tbt' ? __('messages.formations_suivi_type_tbt') : __('messages.formations_suivi_type_formation') }}</div>
        <div><strong>{{ __('messages.year') ?? 'Year' }}:</strong> {{ $year }}</div>
        @if($driverId)
            <div><strong>{{ __('messages.driver') }}:</strong> {{ optional($drivers->firstWhere('id', $driverId))->full_name }}</div>
        @endif
        @if($flotteId)
            <div><strong>{{ __('messages.flotte') }}:</strong> {{ optional($flottes->firstWhere('id', $flotteId))->name }}</div>
        @endif
        @if($theme)
            <div><strong>{{ $type === 'tbt' ? __('messages.tbt_formation_title') : __('messages.theme') }}:</strong> {{ $theme }}</div>
        @endif
        @if($formationType && $type !== 'tbt')
            <div><strong>{{ __('messages.formation_type_label') }}:</strong> {{ $formationTypes[$formationType] ?? $formationType }}</div>
        @endif
        <div class="mt-3"><strong>{{ __('messages.planned') }}:</strong> {{ $chartData['totals']['planned'] ?? 0 }}</div>
        <div><strong>{{ __('messages.realized') }}:</strong> {{ $chartData['totals']['realized'] ?? 0 }}</div>
    </div>

    @if(isset($chartImage) && $chartImage)
        <div style="margin: 20px 0; text-align: center;">
            <h4>{{ __('messages.formations_suivi_chart_title') ?? 'Planned vs Realized per Driver' }}</h4>
            <img src="{{ $chartImage }}" alt="Chart" style="max-width: 100%; height: auto;" />
        </div>
    @endif

    <h4>{{ __('messages.formations_suivi_table_title') ?? 'Details' }}</h4>
    <table>
        <thead>
            <tr>
                <th>{{ __('messages.driver') }}</th>
                <th class="text-end">{{ __('messages.planned') }}</th>
                <th class="text-end">{{ __('messages.realized') }}</th>
            </tr>
        </thead>
        <tbody>
            @forelse($chartData['labels'] as $idx => $label)
                <tr>
                    <td>{{ $label }}</td>
                    <td class="text-end">{{ $chartData['planned'][$idx] ?? 0 }}</td>
                    <td class="text-end">{{ $chartData['realized'][$idx] ?? 0 }}</td>
                </tr>
            @empty
                <tr>
                    <td colspan="3" class="text-center text-muted py-3">{{ __('messages.no_data') ?? 'No data' }}</td>
                </tr>
            @endforelse
        </tbody>
    </table>
</body>
</html>

