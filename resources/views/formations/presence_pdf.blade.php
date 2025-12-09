<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>{{ __('messages.presence_list') }} - {{ $formation->theme }}</title>
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
        <h1>{{ __('messages.presence_list') }}</h1>
        <div class="small">{{ __('messages.formation_theme') }}: {{ $formation->theme }}</div>
    </div>

    <div class="meta"><strong>{{ __('messages.formation_type_label') }}:</strong> {{ $formation->type_label }}</div>
    <div class="meta"><strong>{{ __('messages.flotte') }}:</strong> {{ $formation->flotte->name ?? __('messages.not_assigned') }}</div>
    <div class="meta"><strong>{{ __('messages.formation_delivery_type') }}:</strong> {{ $formation->delivery_type === 'interne' ? __('messages.formation_delivery_internal') : __('messages.formation_delivery_external') }}</div>
    <div class="meta"><strong>{{ __('messages.formation_realizing_date') }}:</strong> {{ $formation->realizing_date ? $formation->realizing_date->format('d/m/Y') : '-' }}</div>

    <h3 class="section-title">{{ __('messages.drivers') }}</h3>
    <table class="table">
        <thead>
            <tr>
                <th>#</th>
                <th>{{ __('messages.driver') }}</th>
                <th>{{ __('messages.flotte') }}</th>
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
                    <td>{{ $df->driver->flotte->name ?? '-' }}</td>
                    <td>{{ $df->status === 'done' ? __('messages.completed') : ($df->status === 'planned' ? __('messages.planned') : $df->status) }}</td>
                    <td>{{ $df->done_at ? \Carbon\Carbon::parse($df->done_at)->format('d/m/Y') : '-' }}</td>
                    <td></td>
                </tr>
            @empty
                <tr>
                    <td colspan="6">{{ __('messages.no_drivers_found') }}</td>
                </tr>
            @endforelse
        </tbody>
    </table>

    <h3 class="section-title">{{ __('messages.administratives') ?? 'Administrative Participants' }}</h3>
    <table class="table">
        <thead>
            <tr>
                <th>#</th>
                <th>{{ __('messages.name') }}</th>
                <th>{{ __('messages.role') }}</th>
                <th>Signature</th>
            </tr>
        </thead>
        <tbody>
            @forelse($administratives as $index => $admin)
                <tr>
                    <td>{{ $index + 1 }}</td>
                    <td>{{ $admin->name ?? '-' }}</td>
                    <td>{{ $admin->role ?? '-' }}</td>
                    <td></td>
                </tr>
            @empty
                <tr>
                    <td colspan="4">{{ __('messages.no_administration_roles_found') ?? 'No administrative staff found.' }}</td>
                </tr>
            @endforelse
        </tbody>
    </table>
</body>
</html>

