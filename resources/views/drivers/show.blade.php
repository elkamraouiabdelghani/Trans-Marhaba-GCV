<x-app-layout>
    <x-slot name="header">
        @include('layouts.topnav')
    </x-slot>

    <div class="container-fluid py-4">
        <!-- Breadcrumb -->
        <nav aria-label="breadcrumb" class="mb-4">
            <div class="d-flex justify-content-between align-items-center">
                <ol class="breadcrumb mb-0">
                    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">{{ __('messages.dashboard') }}</a></li>
                    <li class="breadcrumb-item"><a href="{{ route('drivers.index') }}">{{ __('messages.drivers') }}</a></li>
                    <li class="breadcrumb-item active">{{ $driver->full_name ?? __('messages.driver_number') . $driver->id }}</li>
                </ol>
                
                <div class="d-flex gap-2">
                    <a href="{{ route('drivers.index') }}" class="btn btn-outline-secondary btn-sm">
                        <i class="bi bi-arrow-left me-1"></i>
                        {{ __('messages.back') }}
                    </a>
                    <a href="{{ route('drivers.edit', $driver) }}" class="btn btn-warning btn-sm">
                        <i class="bi bi-pencil me-1"></i>
                        {{ __('messages.edit_driver') ?? __('messages.edit') }}
                    </a>
                </div>
            </div>
        </nav>

        <!-- Driver Information Box -->
        <div class="card border-0 shadow-sm mb-4">
            <div class="card-body p-4">
                <div class="row align-items-center">
                    <div class="col-md-2 text-center mb-3 mb-md-0">
                        <div class="position-relative d-inline-block">
                            <div class="bg-primary bg-opacity-10 rounded-circle d-flex align-items-center justify-content-center" style="width: 120px; height: 120px;">
                                <i class="bi bi-person-fill text-primary" style="font-size: 4rem;"></i>
                            </div>
                            @if($driver->isActive())
                                <span class="badge bg-success position-absolute top-0 end-0 rounded-circle d-flex align-items-center justify-content-center" style="width: 24px; height: 24px; padding: 0;">
                                    <i class="bi bi-check"></i>
                                </span>
                            @endif
                        </div>
                    </div>
                    <div class="col-md-10">
                        <div class="row g-3">
                            <div class="col-md-3">
                                <h3 class="mb-2 text-dark fw-bold">{{ $driver->full_name ?? __('messages.driver_number') . $driver->id }}</h3>
                                <div class="row g-3">
                                    <div class="col-6">
                                        <small class="text-muted d-block">{{ __('messages.license_number') }}</small>
                                        <strong class="text-dark">{{ $driver->license_number ?? 'N/A' }}</strong>
                                    </div>
                                    <div class="col-6">
                                        <small class="text-muted d-block">{{ __('messages.phone') }}</small>
                                        <span class="text-dark">
                                            {{ $driver->phone ?? $driver->phone_number ?? $driver->phone_numbre ?? 'N/A' }}
                                        </span>
                                    </div>
                                    <div class="col-6">
                                        <small class="text-muted d-block">{{ __('messages.assigned_vehicle') }}</small>
                                        <span class="text-dark">
                                            @if($driver->assignedVehicle && $driver->assignedVehicle->license_plate)
                                                {{ $driver->assignedVehicle->license_plate }}
                                            @else
                                                {{ $driver->vehicle_matricule ?? $driver->matricule ?? __('messages.not_assigned') }}
                                            @endif
                                        </span>
                                    </div>
                                    <div class="col-6">
                                        <small class="text-muted d-block">{{ __('messages.flotte') }}</small>
                                        <span class="text-dark">
                                            {{ $driver->flotte->name ?? 'N/A' }}
                                        </span>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-9">
                                <div class="row g-3">
                                    <div class="col-md-4">
                                        <div class="bg-warning bg-opacity-10 rounded p-3 text-center h-100">
                                            <i class="bi bi-bell text-warning fs-4 d-block mb-2"></i>
                                            <small class="text-muted d-block">{{ __('messages.driver_alerts_total') }}</small>
                                            <h4 class="mb-0 fw-bold text-dark">
                                                {{ $criticalAlerts + $warningAlerts }}
                                            </h4>
                                            <div class="small text-muted">
                                                {{ __('messages.driver_alerts_detail', ['critical' => $criticalAlerts, 'warning' => $warningAlerts]) }}
                                            </div>
                                            <a href="{{ route('drivers.alerts') }}" class="small fw-semibold text-warning text-decoration-none mt-2 d-inline-block">
                                                {{ __('messages.view_alerts_overview') }}
                                                <i class="bi bi-arrow-right-circle ms-1"></i>
                                            </a>
                                        </div>
                                    </div>
                                    <div class="col-md-4">
                                        <div class="bg-info bg-opacity-10 rounded p-3 text-center h-100">
                                            <i class="bi bi-clock-history text-info fs-4 d-block mb-2"></i>
                                            <small class="text-muted d-block">{{ __('messages.driving_hours') }}</small>
                                            <h4 class="mb-0 fw-bold text-dark">{{ $totalDrivingHoursThisWeek }}h</h4>
                                            <small class="text-muted">{{ __('messages.this_week') }}</small>
                                        </div>
                                    </div>
                                    <div class="col-md-4">
                                        <div class="bg-danger bg-opacity-10 rounded p-3 text-center h-100">
                                            <i class="bi bi-exclamation-triangle text-danger fs-4 d-block mb-2"></i>
                                            <small class="text-muted d-block">{{ __('messages.total_violations') }}</small>
                                            <h4 class="mb-0 fw-bold text-dark">{{ $totalViolations }}</h4>
                                            <small class="text-muted">{{ __('messages.total') }}</small>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Filter Panel -->
        <div class="card border-0 shadow-sm mb-4">
            <div class="card-header bg-white border-0 py-3">
                <h5 class="mb-0 text-dark fw-bold">
                    <i class="bi bi-funnel me-2 text-primary"></i>
                    {{ __('messages.filters') }}
                </h5>
            </div>
            <div class="card-body">
                <form method="GET" action="{{ route('drivers.show', $driver) }}" id="filterForm">
                    <div class="row g-3">
                        <div class="col-md-3">
                            <label class="form-label small text-muted">{{ __('messages.violation_type') }}</label>
                            <select name="violation_type" class="form-select form-select-sm">
                                <option value="">{{ __('messages.all_types') }}</option>
                                @foreach($violationTypes as $key => $label)
                                    <option value="{{ $key }}" {{ $violationType === $key ? 'selected' : '' }}>
                                        {{ $label }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-2">
                            <label class="form-label small text-muted">{{ __('messages.severity') }}</label>
                            <select name="severity" class="form-select form-select-sm">
                                <option value="">{{ __('messages.all_severities') }}</option>
                                @foreach($severityOptions as $key => $label)
                                    <option value="{{ $key }}" {{ $severity === $key ? 'selected' : '' }}>
                                        {{ $label }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label small text-muted">{{ __('messages.date_from') }}</label>
                            <input type="date" name="date_from" class="form-control form-control-sm" value="{{ $dateFrom }}">
                        </div>
                        <div class="col-md-3">
                            <label class="form-label small text-muted">{{ __('messages.date_to') }}</label>
                            <input type="date" name="date_to" class="form-control form-control-sm" value="{{ $dateTo }}">
                        </div>
                        <div class="col-md-1">
                            <label class="form-label small text-muted d-block">&nbsp;</label>
                            <button type="submit" class="btn btn-primary btn-sm w-100">
                                <i class="bi bi-search"></i>
                            </button>
                        </div>
                    </div>
                </form>
            </div>
        </div>

        <!-- Timeline/Gantt Chart -->
        <div class="card border-0 shadow-sm mb-4">
            <div class="card-header bg-white border-0 py-3">
                <div class="d-flex justify-content-between align-items-center">
                    <h5 class="mb-0 text-dark fw-bold">
                        <i class="bi bi-calendar-range me-2 text-primary"></i>
                        {{ __('messages.timeline_activity') }}
                    </h5>
                </div>
            </div>
            <div class="card-body">
                <div class="text-center text-muted py-5">
                    <i class="bi bi-calendar-x display-6 d-block mb-3"></i>
                    <p class="mb-0">{{ __('messages.timeline_placeholder') ?? 'Driver Activity Timeline - Coming Soon' }}</p>
                </div>
            </div>
        </div>


        <!-- Violations Table -->
        <div class="card border-0 shadow-sm mb-4">
            <div class="card-header bg-white border-0 py-3">
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <h5 class="mb-0 text-dark fw-bold">
                        <i class="bi bi-list-ul me-2 text-primary"></i>
                        {{ __('messages.violations_table') }}
                    </h5>
                    <div class="d-flex gap-2">
                        <button class="btn btn-sm btn-outline-primary" onclick="exportViolationsPDF()">
                            <i class="bi bi-file-pdf me-1"></i> {{ __('messages.export_pdf') }}
                        </button>
                        <button class="btn btn-sm btn-outline-success" onclick="exportViolationsCSV()">
                            <i class="bi bi-file-earmark-spreadsheet me-1"></i> {{ __('messages.export_csv') }}
                        </button>
                    </div>
                </div>
                <div class="row">
                    <div class="col-md-4">
                        <div class="input-group input-group-sm">
                            <span class="input-group-text">
                                <i class="bi bi-search"></i>
                            </span>
                            <input type="text" class="form-control" id="violationSearch" placeholder="{{ __('messages.search_in_table') }}" onkeyup="searchViolations()">
                        </div>
                    </div>
                </div>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover mb-0 align-middle" id="violationsTable">
                        <thead class="table-light">
                            <tr>
                                <th class="border-0 py-3 px-4" style="cursor: pointer;" onclick="sortTable(0)">
                                    {{ __('messages.date') }} <i class="bi bi-arrow-down-up"></i>
                                </th>
                                <th class="border-0 py-3 px-4" style="cursor: pointer;" onclick="sortTable(1)">
                                    {{ __('messages.time') }} <i class="bi bi-arrow-down-up"></i>
                                </th>
                                <th class="border-0 py-3 px-4" style="cursor: pointer;" onclick="sortTable(2)">
                                    {{ __('messages.type') }} <i class="bi bi-arrow-down-up"></i>
                                </th>
                                <th class="border-0 py-3 px-4">{{ __('messages.rule_broken') }}</th>
                                <th class="border-0 py-3 px-4" style="cursor: pointer;" onclick="sortTable(4)">
                                    {{ __('messages.severity') }} <i class="bi bi-arrow-down-up"></i>
                                </th>
                                <th class="border-0 py-3 px-4">{{ __('messages.location') }}</th>
                                <th class="border-0 py-3 px-4 text-center">{{ __('messages.action') }}</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($violations as $violation)
                                @php
                                    $severityBadges = [
                                        'low' => 'warning',
                                        'medium' => 'warning',
                                        'high' => 'danger'
                                    ];
                                    $badgeColor = $severityBadges[$violation['severity']] ?? 'secondary';
                                @endphp
                                <tr class="border-bottom violation-row" data-violation-id="{{ $violation['id'] }}">
                                    <td class="py-3 px-4">{{ $violation['date'] }}</td>
                                    <td class="py-3 px-4">{{ $violation['time'] }}</td>
                                    <td class="py-3 px-4">
                                        <span class="badge bg-primary bg-opacity-10 text-primary">
                                            {{ $violation['type_label'] }}
                                        </span>
                                    </td>
                                    <td class="py-3 px-4">
                                        <small class="text-dark">{{ $violation['rule'] }}</small>
                                    </td>
                                    <td class="py-3 px-4">
                                        <span class="badge bg-{{ $badgeColor }} bg-opacity-10 text-{{ $badgeColor }}">
                                            {{ $violation['severity_label'] }}
                                        </span>
                                    </td>
                                    <td class="py-3 px-4">
                                        <small class="text-muted">
                                            <i class="bi bi-geo-alt me-1"></i>
                                            {{ $violation['location'] }}
                                        </small>
                                    </td>
                                    <td class="py-3 px-4 text-center">
                                        <button class="btn btn-sm btn-outline-info" 
                                                onclick="showViolationDetails({{ json_encode($violation) }})"
                                                title="{{ __('messages.view_details') }}">
                                            <i class="bi bi-eye"></i>
                                        </button>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="7" class="text-center py-5">
                                        <div class="text-muted">
                                            <i class="bi bi-check-circle display-1 mb-3"></i>
                                            <h5 class="mb-2">{{ __('messages.no_violations_found') }}</h5>
                                            <p class="mb-0">{{ __('messages.no_violations_filter') }}</p>
                                        </div>
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
                @if(count($violations) > 10)
                    <div class="card-footer bg-white border-0 py-3">
                        <nav aria-label="Violations pagination">
                            <ul class="pagination pagination-sm justify-content-center mb-0">
                                <!-- Pagination will be added here -->
                            </ul>
                        </nav>
                    </div>
                @endif
            </div>
        </div>

        <!-- Formations Table -->
        <div class="card border-0 shadow-sm mb-4">
            <div class="card-header bg-white border-0 py-3">
                <div class="d-flex justify-content-between align-items-center">
                    <h5 class="mb-0 text-dark fw-bold">
                        <i class="bi bi-book me-2 text-primary"></i>
                        {{ __('messages.formations') }}
                    </h5>
                </div>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover mb-0 align-middle">
                        <thead class="table-light">
                            <tr>
                                <th class="border-0 py-3 px-4">{{ __('messages.formation_name') }}</th>
                                <th class="border-0 py-3 px-4">{{ __('messages.last_realized_date') }}</th>
                                <th class="border-0 py-3 px-4">{{ __('messages.status') }}</th>
                                <th class="border-0 py-3 px-4">{{ __('messages.next_realizing_date') }}</th>
                                <th class="border-0 py-3 px-4">{{ __('messages.formation_alert') ?? 'Alert' }}</th>
                                <th class="border-0 py-3 px-4 text-center">{{ __('messages.formation_count') }}</th>
                                <th class="border-0 py-3 px-4 text-center">{{ __('messages.action') }}</th>
                            </tr>
                        </thead>
                        <tbody>
                            @php
                                $formationsByFormation = ($formations ?? collect())->groupBy('formation_id');
                            @endphp
                            @forelse(($formationsCatalog ?? collect()) as $formationItem)
                                @php
                                    $driverFormationsForItem = $formationsByFormation->get($formationItem->id, collect());
                                    $lastDone = $driverFormationsForItem
                                        ->filter(fn($formation) => $formation->status === 'done' && $formation->done_at)
                                        ->sortByDesc(fn($formation) => $formation->done_at)
                                        ->first();
                                    $nextPlanned = $driverFormationsForItem
                                        ->filter(fn($formation) => $formation->status === 'planned' && $formation->planned_at)
                                        ->sortBy(fn($formation) => $formation->planned_at)
                                        ->first();
                                    $lastRealizedDate = $lastDone?->done_at?->format('d/m/Y') ?? __('messages.not_available');

                                    // Calculate next realizing date based on formation reference
                                    if ($lastDone) {
                                        $nextRealizingDate = $lastDone->getNextRealizingDateFormatted('d/m/Y') ?? __('messages.not_available');
                                    } elseif ($nextPlanned && $nextPlanned->planned_at) {
                                        $nextRealizingDate = $nextPlanned->planned_at->format('d/m/Y');
                                    } else {
                                        $nextRealizingDate = __('messages.not_available');
                                    }
                                    if ($lastDone) {
                                        $statusColor = 'success';
                                        $statusLabel = __('messages.status_realized');
                                    } elseif ($nextPlanned) {
                                        $statusColor = 'info';
                                        $statusLabel = __('messages.status_planned');
                                    } else {
                                        $statusColor = 'secondary';
                                        $statusLabel = __('messages.not_started');
                                    }
                                    $currentProcessFormation = $driverFormationsForItem->first(function ($formation) {
                                        return $formation->formationProcess && $formation->formationProcess->isValidated();
                                    }) ?? $driverFormationsForItem->first(function ($formation) {
                                        return $formation->formationProcess !== null;
                                    });
                                    $currentProcess = $currentProcessFormation?->formationProcess;
                                    $categoryName = optional($formationItem->category)->name;
                                    $normalizedCategoryName = $categoryName ? \Illuminate\Support\Str::of($categoryName)->lower()->trim()->__toString() : null;
                                    $isQuickFormation = in_array($normalizedCategoryName, ['tmd', '16 module'], true);
                                    
                                    // Calculate alert using Formation model with latest completion date
                                    $latestCompletionDate = $lastDone?->done_at;
                                    $alertSummary = $formationItem ? $formationItem->getAlertSummary($latestCompletionDate) : ['state' => 'none'];
                                    $alertState = $alertSummary['state'] ?? 'none';
                                    $elapsedPercent = isset($alertSummary['elapsed_percent']) && $alertSummary['elapsed_percent'] !== null
                                        ? round($alertSummary['elapsed_percent'])
                                        : null;
                                    $daysRemaining = $alertSummary['days_remaining'] ?? null;
                                    $alertBadgeClass = [
                                        'warning' => 'warning',
                                        'critical' => 'danger',
                                        'none' => 'secondary',
                                    ][$alertState] ?? 'secondary';
                                    $alertIcon = [
                                        'warning' => 'bi-exclamation-triangle',
                                        'critical' => 'bi-exclamation-octagon',
                                        'none' => 'bi-info-circle',
                                    ][$alertState] ?? 'bi-info-circle';
                                    $isOverdue = $daysRemaining !== null && $daysRemaining < 0;
                                    $formationFlotteId = $formationItem->flotte_id ?? null;
                                    $formationFlotteName = optional($formationItem->flotte)->name;
                                    $driverFlotteId = $driver->flotte_id ?? null;
                                    $canStartForFlotte = !($formationFlotteId && $driverFlotteId && (int) $formationFlotteId !== (int) $driverFlotteId);
                                @endphp
                                <tr class="border-bottom">
                                    <td class="py-3 px-4">
                                        <div class="d-flex align-items-center">
                                            <div class="bg-primary bg-opacity-10 rounded-circle px-2 py-1 me-3">
                                                <i class="bi bi-book text-primary"></i>
                                            </div>
                                            <div>
                                                <strong class="text-dark">{{ $formationItem->name ?? __('messages.not_available') }}</strong>
                                            </div>
                                        </div>
                                    </td>
                                    <td class="py-3 px-4">
                                        <span class="text-dark">{{ $lastRealizedDate }}</span>
                                    </td>
                                    <td class="py-3 px-4">
                                        <span class="badge bg-{{ $statusColor }} bg-opacity-10 text-{{ $statusColor }}">
                                            {{ $statusLabel }}
                                        </span>
                                    </td>
                                    <td class="py-3 px-4">
                                        <span class="text-dark">{{ $nextRealizingDate }}</span>
                                    </td>
                                    <td class="py-3 px-4">
                                        @if($alertState !== 'none')
                                            <span class="badge bg-{{ $alertBadgeClass }} bg-opacity-10 text-{{ $alertBadgeClass }} d-inline-flex align-items-center gap-1">
                                                <i class="bi {{ $alertIcon }}"></i>
                                                {{ $alertState === 'critical' ? __('messages.formation_alert_critical') : __('messages.formation_alert_warning') }}
                                            </span>
                                            <div class="small text-muted mt-1">
                                                @if($elapsedPercent !== null)
                                                    {{ __('messages.formation_alert_elapsed', ['percent' => $elapsedPercent]) }}
                                                @endif
                                                @if($daysRemaining !== null)
                                                    <span class="d-block">
                                                        {{ $isOverdue
                                                            ? __('messages.formation_alert_overdue', ['days' => abs($daysRemaining)])
                                                            : __('messages.formation_alert_days_remaining', ['days' => $daysRemaining]) }}
                                                    </span>
                                                @endif
                                            </div>
                                        @else
                                            <span class="text-muted">
                                                {{ __('messages.formation_alert_none') }}
                                            </span>
                                        @endif
                                    </td>
                                    <td class="py-3 px-4 text-center">
                                        <span class="badge bg-secondary bg-opacity-10 text-secondary">
                                            {{ $driverFormationsForItem->count() }}
                                        </span>
                                    </td>
                                    @php
                                        $showStartButton = true;
                                        if ($lastDone && $lastDone->done_at) {
                                            $monthsDiff = $lastDone->done_at->diffInMonths(now());
                                            $showStartButton = $monthsDiff >= 5;
                                        }
                                    @endphp
                                    <td class="py-3 px-4 text-center">
                                        <div class="btn-group" role="group">
                                            @if($lastDone && $lastDone->certificate_path)
                                                <a href="{{ route('drivers.formations.download-certificate', $lastDone) }}" class="btn btn-outline-success btn-sm" title="{{ __('messages.download_certificate') }}">
                                                    <i class="bi bi-download"></i>
                                                </a>
                                            @endif
                                            {{-- @if($currentProcess && $currentProcess->isValidated())
                                                <button type="button"
                                                        class="btn btn-outline-success btn-sm btn-generate-report"
                                                        data-process-id="{{ $currentProcess->id }}"
                                                        data-report-url="{{ route('formation-processes.download-report', $currentProcess) }}"
                                                        title="{{ __('messages.download_report') }}">
                                                    <i class="bi bi-download"></i>
                                                </button>
                                            @endif --}}
                                            @if($currentProcess)
                                                <a href="{{ route('formation-processes.show', $currentProcess) }}" class="btn btn-outline-primary btn-sm" title="{{ __('messages.view') }}">
                                                    <i class="bi bi-eye"></i>
                                                </a>
                                            @endif
                                            @if($showStartButton)
                                                @if($canStartForFlotte)
                                                    @if($isQuickFormation)
                                                        @php
                                                            $calculatedNextDate = $lastDone ? $lastDone->getNextRealizingDate() : null;
                                                            $nextDateValue = $calculatedNextDate ? $calculatedNextDate->format('Y-m-d') : '';
                                                        @endphp
                                                        <button type="button"
                                                                class="btn btn-dark btn-sm btn-quick-formation-start"
                                                                data-formation-id="{{ $formationItem->id }}"
                                                                data-formation-name="{{ $formationItem->name ?? '' }}"
                                                                data-next-date="{{ $nextDateValue }}">
                                                            <i class="bi bi-play-circle me-1"></i>
                                                            {{ __('messages.start') }}
                                                        </button>
                                                    @else
                                                        <a href="{{ route('formation-processes.create') }}?driver_id={{ $driver->id }}&formation_id={{ $formationItem->id }}" class="btn btn-dark btn-sm" title="{{ __('messages.start_formation_process') }}">
                                                            <i class="bi bi-play-circle me-1"></i>
                                                            {{ __('messages.start') }}
                                                        </a>
                                                    @endif
                                                @else
                                                    <button type="button"
                                                            class="btn btn-outline-secondary btn-sm"
                                                            disabled
                                                            data-bs-toggle="tooltip"
                                                            data-bs-placement="top"
                                                            title="{{ $formationFlotteName ? __('messages.formation_start_restricted_with_flotte', ['flotte' => $formationFlotteName]) : __('messages.formation_start_restricted') }}">
                                                        <i class="bi bi-lock"></i>
                                                    </button>
                                                @endif
                                            @endif
                                        </div>
                                        @if($showStartButton && !$canStartForFlotte)
                                            <div class="small text-muted mt-2">
                                                {{ $formationFlotteName ? __('messages.formation_start_restricted_with_flotte', ['flotte' => $formationFlotteName]) : __('messages.formation_start_restricted') }}
                                            </div>
                                        @endif
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="6" class="text-center py-5">
                                        <div class="text-muted">
                                            <i class="bi bi-book display-1 mb-3"></i>
                                            <h5 class="mb-2">{{ __('messages.no_formations_found') }}</h5>
                                            <p class="mb-0">{{ __('messages.no_formations_message') }}</p>
                                            <a href="{{ route('formation-processes.create') }}?driver_id={{ $driver->id }}" class="btn btn-dark mt-3">
                                                <i class="bi bi-plus-circle me-1"></i>
                                                {{ __('messages.start_formation_process') }}
                                            </a>
                                        </div>
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <!-- Quick Formation Modal -->
        <div class="modal fade" id="quickFormationModal" tabindex="-1" aria-labelledby="quickFormationModalLabel" aria-hidden="true">
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="quickFormationModalLabel">
                            {{ __('messages.start') }} {{ 'Formation' }}
                            <small class="d-block text-muted" id="quick-formation-name"></small>
                        </h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="{{ __('messages.close') }}"></button>
                    </div>
                    <form method="POST"
                          action="{{ route('drivers.formations.quick-store', $driver) }}"
                          enctype="multipart/form-data">
                        @csrf
                        <div class="modal-body">
                            <input type="hidden" name="formation_id" id="quick-formation-id" value="{{ old('formation_id') }}">

                            <div class="mb-3">
                                <label for="quick-due-at" class="form-label">
                                    {{ __('messages.next_realizing_date') ?? 'Date d\'échéance' }}
                                    <span class="text-danger">*</span>
                                </label>
                                <input type="date"
                                       class="form-control @error('due_at') is-invalid @enderror"
                                       id="quick-due-at"
                                       name="due_at"
                                       value="{{ old('due_at') }}">
                                @error('due_at')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="mb-3">
                                <label for="quick-report-file" class="form-label">
                                    {{ __('messages.completion_certificate') ?? 'Rapport' }}
                                    <span class="text-danger">*</span>
                                </label>
                                <input type="file"
                                       class="form-control @error('report_file') is-invalid @enderror"
                                       id="quick-report-file"
                                       name="report_file"
                                       accept=".pdf,.doc,.docx,.xlsx">
                                @error('report_file')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                                <small class="text-muted">{{ __('messages.upload_certificate_hint') ?? 'Formats acceptés : PDF, DOC, DOCX, XLSX (5 Mo max).' }}</small>
                            </div>

                            @error('formation_id')
                                <div class="alert alert-danger py-2">{{ $message }}</div>
                            @enderror
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">
                                {{ __('messages.cancel') }}
                            </button>
                            <button type="submit" class="btn btn-dark">
                                <i class="bi bi-save me-1"></i>
                                {{ __('messages.save') ?? 'Enregistrer' }}
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <!-- Violation Details Modal -->
    <div class="modal fade" id="violationModal" tabindex="-1" aria-labelledby="violationModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="violationModalLabel">{{ __('messages.violation_details') }}</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body" id="violationModalBody">
                    <!-- Content will be populated by JavaScript -->
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">{{ __('messages.close') }}</button>
                </div>
            </div>
        </div>
    </div>

    @push('styles')
    <style>
        /* Timeline styles removed - placeholder only */
        @if(false)
        .timeline-day {
            transition: all 0.3s ease;
        }
        .timeline-day:hover {
            box-shadow: 0 4px 12px rgba(0,0,0,0.1);
            transform: translateY(-2px);
        }
        .timeline-day.border-danger {
            animation: pulse-border 2s ease-in-out infinite;
        }
        @keyframes pulse-border {
            0%, 100% {
                border-color: #dc3545;
            }
            50% {
                border-color: #ff6b7a;
            }
        }
        .violation-marker {
            cursor: pointer;
            transition: transform 0.2s ease;
        }
        .violation-marker:hover {
            transform: translate(-50%, -50%) scale(1.3);
            z-index: 20 !important;
        }
        .gantt-bar-container {
            position: relative;
            overflow: visible;
        }
        .driving-hours, .rest-hours {
            transition: all 0.3s ease;
            min-height: 100%;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        .driving-hours:hover {
            opacity: 0.9;
            filter: brightness(1.1);
        }
        .rest-hours:hover {
            opacity: 0.9;
            filter: brightness(1.1);
        }
        @endif
        .comment-item {
            border-left: 3px solid #0d6efd;
        }
        .table-hover tbody tr:hover {
            background-color: rgba(0, 0, 0, 0.02);
        }
        .violations-list {
            border-left: 3px solid #dc3545;
        }
    </style>
    @endpush

    @push('scripts')
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.6/dist/js/bootstrap.bundle.min.js" 
    crossorigin="anonymous" defer></script>
    <script>
        // Initialize tooltips
        document.addEventListener('DOMContentLoaded', function() {
            var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
            var tooltipList = tooltipTriggerList.map(function (tooltipTriggerEl) {
                return new bootstrap.Tooltip(tooltipTriggerEl);
            });
        });

        document.addEventListener('DOMContentLoaded', function() {
            const quickModalEl = document.getElementById('quickFormationModal');
            if (!quickModalEl || typeof bootstrap === 'undefined') {
                return;
            }

            const quickModal = new bootstrap.Modal(quickModalEl);
            const formationIdInput = document.getElementById('quick-formation-id');
            const formationNameEl = document.getElementById('quick-formation-name');
            const dueAtInput = document.getElementById('quick-due-at');
            const fileInput = document.getElementById('quick-report-file');
            const formationLookup = @json($formationsCatalog->pluck('name', 'id'));

            document.querySelectorAll('.btn-quick-formation-start').forEach(function(button) {
                button.addEventListener('click', function() {
                    const formationId = this.dataset.formationId;
                    const formationName = this.dataset.formationName || formationLookup[formationId] || '';
                    const nextDate = this.dataset.nextDate || '';

                    if (formationIdInput) {
                        formationIdInput.value = formationId;
                    }
                    if (formationNameEl) {
                        formationNameEl.textContent = formationName;
                    }
                    if (dueAtInput) {
                        dueAtInput.value = nextDate;
                    }
                    if (fileInput) {
                        fileInput.value = '';
                    }

                    quickModal.show();
                });
            });

            const shouldOpenQuickModal = @json(session('open_quick_modal', false));
            if (shouldOpenQuickModal) {
                const oldFormationId = @json(old('formation_id'));
                if (oldFormationId && formationIdInput) {
                    formationIdInput.value = oldFormationId;
                }
                if (formationNameEl) {
                    const oldName = oldFormationId ? (formationLookup[oldFormationId] || '') : '';
                    formationNameEl.textContent = oldName;
                }
                const oldDueAt = @json(old('due_at'));
                if (oldDueAt && dueAtInput) {
                    dueAtInput.value = oldDueAt;
                }
                quickModal.show();
            }
        });

        // Activity modal removed - timeline system rejected

        // Table sorting
        let sortDirection = {};
        function sortTable(columnIndex) {
            const table = document.getElementById('violationsTable');
            const tbody = table.querySelector('tbody');
            const rows = Array.from(tbody.querySelectorAll('tr'));
            
            const isAscending = !sortDirection[columnIndex];
            sortDirection[columnIndex] = isAscending;
            
            rows.sort((a, b) => {
                const aText = a.cells[columnIndex].textContent.trim();
                const bText = b.cells[columnIndex].textContent.trim();
                
                if (columnIndex === 0 || columnIndex === 1) {
                    // Date/Time sorting
                    return isAscending ? aText.localeCompare(bText) : bText.localeCompare(aText);
                } else {
                    // Text sorting
                    return isAscending ? aText.localeCompare(bText) : bText.localeCompare(aText);
                }
            });
            
            rows.forEach(row => tbody.appendChild(row));
        }

        // Show violation details
        function showViolationDetails(violation) {
            const modal = new bootstrap.Modal(document.getElementById('violationModal'));
            const body = document.getElementById('violationModalBody');
            
            const severityColors = {
                'low': 'warning',
                'medium': 'warning',
                'high': 'danger'
            };
            const badgeColor = severityColors[violation.severity] || 'secondary';
            
            body.innerHTML = `
                <div class="row g-3">
                    <div class="col-md-6">
                        <strong class="text-muted d-block mb-1">{{ __('messages.date') }}</strong>
                        <p class="mb-0">${violation.date}</p>
                    </div>
                    <div class="col-md-6">
                        <strong class="text-muted d-block mb-1">{{ __('messages.time') }}</strong>
                        <p class="mb-0">${violation.time}</p>
                    </div>
                    <div class="col-md-6">
                        <strong class="text-muted d-block mb-1">{{ __('messages.violation_type') }}</strong>
                        <span class="badge bg-primary bg-opacity-10 text-primary">${violation.type_label}</span>
                    </div>
                    <div class="col-md-6">
                        <strong class="text-muted d-block mb-1">{{ __('messages.severity') }}</strong>
                        <span class="badge bg-${badgeColor} bg-opacity-10 text-${badgeColor}">${violation.severity_label}</span>
                    </div>
                    <div class="col-12">
                        <strong class="text-muted d-block mb-1">{{ __('messages.rule_broken') }}</strong>
                        <p class="mb-0">${violation.rule}</p>
                    </div>
                    <div class="col-12">
                        <strong class="text-muted d-block mb-1">{{ __('messages.location') }}</strong>
                        <p class="mb-0"><i class="bi bi-geo-alt me-1"></i>${violation.location}</p>
                    </div>
                </div>
            `;
            
            modal.show();
        }

        // Export functions (placeholders)
        function exportViolationsPDF() {
            alert('{{ __('messages.export_violations_pdf') }}');
            // TODO: Implement PDF export
        }

        function exportViolationsCSV() {
            alert('{{ __('messages.export_violations_csv') }}');
            // TODO: Implement CSV export
        }


        // Comment form submission
        document.getElementById('commentForm').addEventListener('submit', function(e) {
            e.preventDefault();
            const commentText = document.getElementById('commentText').value;
            if (!commentText.trim()) {
                alert('{{ __('messages.please_enter_comment') }}');
                return;
            }
            
            // TODO: Implement actual API call
            const commentsList = document.getElementById('commentsList');
            const newComment = document.createElement('div');
            newComment.className = 'comment-item mb-3 p-3 bg-light rounded';
            newComment.innerHTML = `
                <div class="d-flex justify-content-between align-items-start mb-2">
                    <div>
                        <strong class="text-dark">{{ __('messages.supervisor') }}</strong>
                        <small class="text-muted ms-2">${new Date().toLocaleString('{{ app()->getLocale() }}')}</small>
                    </div>
                </div>
                <p class="mb-0 text-dark">${commentText}</p>
            `;
            commentsList.insertBefore(newComment, commentsList.firstChild);
            document.getElementById('commentText').value = '';
            alert('{{ __('messages.note_saved_success') }}');
        });

        // Table search functionality
        function searchViolations() {
            const input = document.getElementById('violationSearch');
            const filter = input.value.toLowerCase();
            const table = document.getElementById('violationsTable');
            const tr = table.getElementsByTagName('tr');
            
            for (let i = 1; i < tr.length; i++) {
                const td = tr[i].getElementsByTagName('td');
                let found = false;
                for (let j = 0; j < td.length; j++) {
                    if (td[j]) {
                        const txtValue = td[j].textContent || td[j].innerText;
                        if (txtValue.toLowerCase().indexOf(filter) > -1) {
                            found = true;
                            break;
                        }
                    }
                }
                tr[i].style.display = found ? '' : 'none';
            }
        }
        
        document.addEventListener('DOMContentLoaded', function() {
            // Initialize search
            const searchInput = document.getElementById('violationSearch');
            if (searchInput) {
                searchInput.addEventListener('input', searchViolations);
            }

            // Report generation modal logic
            const modalElement = document.getElementById('reportGenerationModal');
            const reportButtons = document.querySelectorAll('.btn-generate-report');

            if (modalElement && typeof bootstrap !== 'undefined') {
                const modalInstance = new bootstrap.Modal(modalElement);
                const progressSection = modalElement.querySelector('#reportGenerationProgress');
                const successAlert = modalElement.querySelector('#reportGenerationSuccess');
                const errorAlert = modalElement.querySelector('#reportGenerationError');
                const errorMessage = modalElement.querySelector('#reportGenerationErrorMessage');
                const downloadButton = modalElement.querySelector('#reportDownloadButton');
                let currentObjectUrl = null;

                const resetModalState = () => {
                    progressSection?.classList.remove('d-none');
                    successAlert?.classList.add('d-none');
                    errorAlert?.classList.add('d-none');
                    if (downloadButton) {
                        downloadButton.classList.add('d-none');
                        downloadButton.setAttribute('href', '#');
                        downloadButton.removeAttribute('download');
                    }
                    if (currentObjectUrl) {
                        URL.revokeObjectURL(currentObjectUrl);
                        currentObjectUrl = null;
                    }
                };

                modalElement.addEventListener('hidden.bs.modal', resetModalState);

                reportButtons.forEach(button => {
                    button.addEventListener('click', async function () {
                        resetModalState();
                        modalInstance.show();

                        const reportUrl = this.dataset.reportUrl;

                        try {
                            const response = await fetch(reportUrl, {
                                headers: {
                                    'X-Requested-With': 'XMLHttpRequest',
                                    'Accept': 'application/pdf',
                                },
                                credentials: 'same-origin',
                            });

                            const contentType = response.headers.get('Content-Type') || '';
                            if (!response.ok || !contentType.includes('application/pdf')) {
                                throw new Error('INVALID_RESPONSE');
                            }

                            const blob = await response.blob();
                            const disposition = response.headers.get('Content-Disposition') || '';
                            const matches = disposition.match(/filename="?([^"]+)"?/);
                            const filename = matches ? matches[1] : 'formation-report.pdf';

                            currentObjectUrl = URL.createObjectURL(blob);
                            if (downloadButton) {
                                downloadButton.classList.remove('d-none');
                                downloadButton.setAttribute('href', currentObjectUrl);
                                downloadButton.setAttribute('download', filename);
                            }

                            progressSection?.classList.add('d-none');
                            successAlert?.classList.remove('d-none');
                        } catch (error) {
                            console.error('Report generation failed:', error);
                            progressSection?.classList.add('d-none');
                            errorAlert?.classList.remove('d-none');
                            if (errorMessage) {
                                errorMessage.textContent = '{{ __('messages.report_generation_failed') }}';
                            }
                        }
                    });
                });
            }
        });
    </script>
    @endpush
</x-app-layout>

