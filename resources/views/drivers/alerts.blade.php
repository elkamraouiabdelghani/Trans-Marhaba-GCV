<x-app-layout>
    <x-slot name="header">
        @include('layouts.topnav')
    </x-slot>

    <div class="container-fluid py-4">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <div>
                <h4 class="fw-bold text-dark mb-1">
                    <i class="bi bi-bell-fill text-warning me-2"></i>
                    {{ __('messages.formation_alerts_title') }}
                </h4>
                <p class="text-muted mb-0">
                    {{ __('messages.formation_alerts_description') }}
                </p>
            </div>
            <a href="{{ route('drivers.index') }}" class="btn btn-outline-secondary">
                <i class="bi bi-arrow-left me-1"></i>
                {{ __('messages.back') }}
            </a>
        </div>

        <div class="row g-3 mb-4">
            <div class="col-md-4">
                <div class="card border-0 shadow-sm h-100">
                    <div class="card-body">
                        <div class="d-flex align-items-center">
                            <div class="bg-warning bg-opacity-10 rounded-circle d-flex align-items-center justify-content-center me-3" style="width: 50px; height: 50px;">
                                <i class="bi bi-people text-warning fs-5"></i>
                            </div>
                            <div>
                                <h6 class="text-muted mb-1">{{ __('messages.drivers_with_alerts') }}</h6>
                                <h4 class="mb-0 fw-bold text-dark">{{ $driversCount }}</h4>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="card border-0 shadow-sm h-100">
                    <div class="card-body">
                        <div class="d-flex align-items-center">
                            <div class="bg-warning bg-opacity-10 rounded-circle d-flex align-items-center justify-content-center me-3" style="width: 50px; height: 50px;">
                                <i class="bi bi-exclamation-triangle text-warning fs-5"></i>
                            </div>
                            <div>
                                <h6 class="text-muted mb-1">{{ __('messages.warning_alerts') }}</h6>
                                <h4 class="mb-0 fw-bold text-dark">{{ $warningCount }}</h4>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="card border-0 shadow-sm h-100">
                    <div class="card-body">
                        <div class="d-flex align-items-center">
                            <div class="bg-danger bg-opacity-10 rounded-circle d-flex align-items-center justify-content-center me-3" style="width: 50px; height: 50px;">
                                <i class="bi bi-exclamation-octagon text-danger fs-5"></i>
                            </div>
                            <div>
                                <h6 class="text-muted mb-1">{{ __('messages.critical_alerts') }}</h6>
                                <h4 class="mb-0 fw-bold text-dark">{{ $criticalCount }}</h4>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="card border-0 shadow-sm">
            <div class="card-header bg-white border-0 py-3">
                <div class="d-flex justify-content-between align-items-center">
                    <h5 class="mb-0 fw-bold text-dark">
                        <i class="bi bi-list-check me-2 text-warning"></i>
                        {{ __('messages.formation_alerts_list') }}
                    </h5>
                    <a href="{{ route('drivers.alerts.export') }}" class="btn btn-sm btn-outline-success">
                        <i class="bi bi-download me-1"></i>
                        {{ __('messages.export_excel') }}
                    </a>
                </div>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead class="table-light">
                            <tr>
                                <th class="py-3 px-4">{{ __('messages.driver') }}</th>
                                <th class="py-3 px-4">{{ __('messages.formation_name') }}</th>
                                <th class="py-3 px-4">{{ __('messages.alert_level') }}</th>
                                <th class="py-3 px-4">{{ __('messages.formation_alert_elapsed_label') }}</th>
                                <th class="py-3 px-4">{{ __('messages.formation_alert_days_label') }}</th>
                                <th class="py-3 px-4">{{ __('messages.last_realized_date') }}</th>
                                <th class="py-3 px-4 text-center">{{ __('messages.action') }}</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($alerts as $driverFormation)
                                @php
                                    // Use Formation model's alert calculation with latest completion date
                                    $latestCompletionDate = $driverFormation->done_at;
                                    $summary = $driverFormation->formation 
                                        ? $driverFormation->formation->getAlertSummary($latestCompletionDate)
                                        : ['state' => 'none', 'elapsed_percent' => null, 'days_remaining' => null];
                                    $state = $summary['state'];
                                    $elapsed = isset($summary['elapsed_percent']) && $summary['elapsed_percent'] !== null
                                        ? round($summary['elapsed_percent'])
                                        : null;
                                    $remaining = $summary['days_remaining'];
                                    $badgeClass = [
                                        'warning' => 'warning',
                                        'critical' => 'danger',
                                        'none' => 'secondary',
                                    ][$state] ?? 'secondary';
                                    $icon = [
                                        'warning' => 'bi-exclamation-triangle',
                                        'critical' => 'bi-exclamation-octagon',
                                        'none' => 'bi-info-circle',
                                    ][$state] ?? 'bi-info-circle';
                                    $completionDate = optional($latestCompletionDate)->format('d/m/Y');
                                    $isOverdue = $remaining !== null && $remaining < 0;
                                @endphp
                                <tr>
                                    <td class="py-3 px-4">
                                        <div class="d-flex align-items-center gap-2">
                                            <div class="bg-primary bg-opacity-10 rounded-circle px-2 py-1">
                                                <i class="bi bi-person text-primary"></i>
                                            </div>
                                            <div>
                                                <strong class="text-dark">{{ $driverFormation->driver->full_name ?? __('messages.not_available') }}</strong>
                                                @if($driverFormation->driver->flotte)
                                                    <div class="small text-muted">
                                                        {{ $driverFormation->driver->flotte->name }}
                                                    </div>
                                                @endif
                                            </div>
                                        </div>
                                    </td>
                                    <td class="py-3 px-4">
                                        <div class="d-flex flex-column">
                                            <strong class="text-dark">{{ $driverFormation->formation->name ?? __('messages.not_available') }}</strong>
                                            @if($driverFormation->formation && $driverFormation->formation->code)
                                                <span class="small text-muted">{{ $driverFormation->formation->code }}</span>
                                            @endif
                                        </div>
                                    </td>
                                    <td class="py-3 px-4">
                                        <span class="badge bg-{{ $badgeClass }} bg-opacity-10 text-{{ $badgeClass }} d-inline-flex align-items-center gap-1">
                                            <i class="bi {{ $icon }}"></i>
                                            {{ $state === 'critical' ? __('messages.formation_alert_critical') : __('messages.formation_alert_warning') }}
                                        </span>
                                    </td>
                                    <td class="py-3 px-4">
                                        @if($elapsed !== null)
                                            {{ __('messages.formation_alert_elapsed', ['percent' => $elapsed]) }}
                                        @else
                                            <span class="text-muted">—</span>
                                        @endif
                                    </td>
                                    <td class="py-3 px-4">
                                        @if($remaining !== null)
                                            {{ $isOverdue
                                                ? __('messages.formation_alert_overdue', ['days' => abs($remaining)])
                                                : __('messages.formation_alert_days_remaining', ['days' => $remaining]) }}
                                        @else
                                            <span class="text-muted">—</span>
                                        @endif
                                    </td>
                                    <td class="py-3 px-4">
                                        {{ $completionDate ?? __('messages.not_available') }}
                                    </td>
                                    <td class="py-3 px-4 text-center">
                                        {{-- add a button for view the alert details --}}
                                        <a href="{{ route('drivers.show', $driverFormation->driver_id) }}" class="btn btn-outline-primary btn-sm">
                                            <i class="bi bi-eye me-1"></i>
                                        </a>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="7" class="text-center py-5">
                                        <div class="text-muted">
                                            <i class="bi bi-bell-slash display-1 mb-3"></i>
                                            <h5 class="mb-2">{{ __('messages.no_formation_alerts') }}</h5>
                                        </div>
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>

