<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>{{ __('messages.presence_list') }} - {{ $tbtFormation->title }}</title>
    <style>
        body { font-family: DejaVu Sans, sans-serif; margin: 24px; color: #111; }
        h1, h2, h3 { margin: 0 0 12px 0; }
        .header { text-align: center; margin-bottom: 20px; }
        .small { font-size: 12px; color: #555; }
        .table { width: 100%; border-collapse: collapse; margin-top: 12px; }
        .table th, .table td { border: 1px solid #ddd; padding: 8px; font-size: 12px; }
        .table th { background: #f5f5f5; text-align: left; }
        .section-title { margin-top: 24px; margin-bottom: 8px; }
        .meta { margin: 6px 0; }
    </style>
</head>
<body>
    <div class="header">
        <h2>Trans Marhaba</h2>
        <h1>{{ __('messages.presence_list') }} - TBT Formation</h1>
        <div class="small">{{ $tbtFormation->title }}</div>
    </div>

    <div class="meta"><strong>{{ __('messages.tbt_formation_title') ?? 'Title' }}:</strong> {{ $tbtFormation->title }}</div>
    <div class="meta"><strong>{{ __('messages.tbt_formation_year') }}:</strong> {{ $tbtFormation->year }}</div>
    <div class="meta"><strong>{{ __('messages.tbt_formation_month') }}:</strong> {{ \Carbon\Carbon::create($tbtFormation->year, $tbtFormation->month, 1)->locale('fr')->monthName }}</div>
    <div class="meta"><strong>{{ __('messages.tbt_formation_week') }}:</strong> {{ \Carbon\Carbon::parse($tbtFormation->week_start_date)->format('d/m') }} - {{ \Carbon\Carbon::parse($tbtFormation->week_end_date)->format('d/m') }}</div>
    @if($tbtFormation->participant)
        <div class="meta"><strong>{{ __('messages.formation_participant') }}:</strong> {{ $tbtFormation->participant }}</div>
    @endif

    <h3 class="section-title">{{ __('messages.drivers') }}</h3>
    <table class="table">
        <thead>
            <tr>
                <th>#</th>
                <th>{{ __('messages.driver') }}</th>
                <th>{{ __('messages.status') }}</th>
                <th>{{ __('messages.done_at') ?? 'Done at' }}</th>
                <th>Signature</th>
            </tr>
        </thead>
        <tbody>
            @forelse($driverFormations as $index => $df)
                <tr>
                    <td>{{ $index + 1 }}</td>
                    <td>{{ $df->driver->full_name ?? '-' }}</td>
                    <td>{{ __('messages.completed') ?? 'Completed' }}</td>
                    <td>{{ $df->done_at ? \Carbon\Carbon::parse($df->done_at)->format('d/m/Y') : '-' }}</td>
                    <td></td>
                </tr>
            @empty
                <tr>
                    <td colspan="5">{{ __('messages.no_drivers_found') }}</td>
                </tr>
            @endforelse
        </tbody>
    </table>
</body>
</html>

