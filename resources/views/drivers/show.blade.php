<x-app-layout>
    <x-slot name="header">
        @include('layouts.topnav')
    </x-slot>

    <div class="container-fluid py-4">
        <!-- Breadcrumb -->
        <nav aria-label="breadcrumb" class="p-3 mb-4 rounded-3 shadow-sm bg-white">
            <div class="d-flex justify-content-between align-items-center">
                <ol class="breadcrumb mb-0">
                    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">{{ __('messages.dashboard') }}</a></li>
                    <li class="breadcrumb-item"><a href="{{ route('drivers.index') }}">{{ __('messages.drivers') }}</a></li>
                    <li class="breadcrumb-item active">{{ $driver->full_name ?? __('messages.driver_number') . $driver->id }}</li>
                </ol>
                
                <div class="d-flex gap-2">
                    <a href="javascript:history.back()" class="btn btn-outline-secondary btn-sm">
                        <i class="bi bi-arrow-left me-1"></i>
                        {{ __('messages.back') }}
                    </a>
                </div>
            </div>
        </nav>

        @php
            $violationExportParams = array_filter([
                'violation_type_id' => $violationFilters['violation_type_id'] ?? null,
                'status' => $violationFilters['status'] ?? null,
                'date_from' => $violationFilters['date_from'] ?? $dateFrom,
                'date_to' => $violationFilters['date_to'] ?? $dateTo,
            ], fn($value) => $value !== null && $value !== '');
        @endphp

        <div class="row g-4 mb-4">
            <div class="col-12 col-lg-8 col-xl-9">
                <!-- Driver Information Box -->
                <div class="card border-0 shadow-sm h-100">
                    <div class="card-body p-4">
                        <div class="row align-items-center">
                            <div class="col-md-2 text-center mb-3 mb-md-0">
                                <div class="position-relative d-inline-block">
                                    <div class="bg-primary bg-opacity-10 rounded-circle d-flex align-items-center justify-content-center" style="width: 120px; height: 120px;">
                                        <i class="bi bi-person-fill text-primary" style="font-size: 4rem;"></i>
                                    </div>
                                    @if(method_exists($driver, 'isActive') && $driver->isActive())
                                        <span class="badge bg-success position-absolute top-0 end-0 rounded-circle d-flex align-items-center justify-content-center" style="width: 24px; height: 24px; padding: 0;">
                                            <i class="bi bi-check"></i>
                                        </span>
                                    @elseif(strtolower((string)($driver->status ?? $driver->statu ?? $driver->state ?? '')) === 'terminated')
                                        <span class="badge bg-danger position-absolute top-0 end-0 rounded-circle d-flex align-items-center justify-content-center" style="width: 24px; height: 24px; padding: 0;">
                                            <i class="bi bi-x-lg"></i>
                                        </span>
                                    @endif
                                </div>
                            </div>
                            <div class="col-md-10">
                                <div class="row g-3">
                                    <div class="col-md-6">
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
                                    <div class="col-md-6">
                                        <div class="row g-3">
                                            <div class="col-md-6">
                                                <div class="bg-info bg-opacity-10 rounded p-3 text-center h-100">
                                                    <i class="bi bi-clock-history text-info fs-4 d-block mb-2"></i>
                                                    <small class="text-muted d-block">{{ __('messages.driving_hours') }}</small>
                                                    <h4 class="mb-0 fw-bold text-dark">{{ $totalDrivingHoursThisWeek }}h</h4>
                                                    <small class="text-muted">{{ __('messages.this_week') }}</small>
                                                </div>
                                            </div>
                                            <div class="col-md-6">
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
                                @if($driver->terminated_cause || $driver->terminated_date)
                                    <div class="alert alert-danger bg-danger bg-opacity-10 border-0 mt-3">
                                        <div class="d-flex align-items-start">
                                            <i class="bi bi-info-circle me-2 fs-4"></i>
                                            <div>
                                                <p class="mb-1 fw-semibold text-danger text-uppercase small">
                                                    {{ __('messages.terminated') }}
                                                    @if($driver->terminated_date)
                                                        · {{ __('messages.terminated_date') }}: {{ optional($driver->terminated_date)->format('d/m/Y') }}
                                                    @endif
                                                </p>
                                                @if($driver->terminated_cause)
                                                    <p class="mb-0 text-dark">{{ $driver->terminated_cause }}</p>
                                                @endif
                                            </div>
                                        </div>
                                    </div>
                                @endif
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Quick Actions --}}
            <div class="col-12 col-lg-4 col-xl-3">
                <aside class="position-sticky" style="top: 0.5rem;">
                    <div class="card border-0 shadow-sm">
                        <div class="card-header bg-white border-0 py-3">
                            <h6 class="mb-0 text-dark fw-bold text-uppercase small">
                                <i class="bi bi-lightning-charge text-warning me-2"></i>
                                {{ __('messages.quick_actions') }}
                            </h6>
                        </div>
                        <div class="card-body d-grid gap-2">
                            <a href="{{ route('drivers.edit', $driver) }}" class="btn btn-warning btn-sm">
                                <i class="bi bi-pencil me-1"></i>
                                {{ __('messages.edit_driver') ?? __('messages.edit') }}
                            </a>
                            @if($driver->integrationCandidate)
                                <a href="{{ route('integrations.show', $driver->integrationCandidate) }}" class="btn btn-primary btn-sm">
                                    <i class="bi bi-person-check me-1"></i>
                                    {{ __('messages.integration_process') }}
                                </a>
                            @endif
                            <hr class="my-2">
                            @php
                                $driverStatusValue = strtolower((string)($driver->status ?? $driver->statu ?? $driver->state ?? ''));
                            @endphp
                            @unless($driverStatusValue === 'terminated')
                                <button type="button" class="btn btn-outline-danger btn-sm" data-bs-toggle="modal" data-bs-target="#terminateDriverModal">
                                    <i class="bi bi-person-x me-1"></i>{{ __('messages.terminate_driver') }}
                                </button>
                            @endunless
                            @if($driverStatusValue !== 'terminated')
                                <a href="{{ route('violations.create', ['driver_id' => $driver->id]) }}"
                                class="btn btn-success btn-sm d-flex align-items-center justify-content-center">
                                    <i class="bi bi-flag-fill me-2"></i>
                                    {{ __('messages.add') }} {{ __('messages.violation') }}
                                </a>
                                <a href="{{ route('drivers.alerts') }}" class="btn btn-warning btn-sm d-flex align-items-center justify-content-center">
                                    <i class="bi bi-bell me-2"></i>
                                    {{ __('messages.formation_alerts') }}
                                </a>
                            @endif
                        </div>
                    </div>
                </aside>
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
                            <select name="violation_type_id" class="form-select form-select-sm">
                                <option value="">{{ __('messages.all_types') }}</option>
                                @foreach($violationTypes as $id => $label)
                                    <option value="{{ $id }}" {{ (string) $violationTypeId === (string) $id ? 'selected' : '' }}>
                                        {{ $label }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label small text-muted">{{ __('messages.status') }}</label>
                            <select name="status" class="form-select form-select-sm">
                                <option value="">{{ __('messages.all_status') }}</option>
                                @foreach($statusOptions as $key => $label)
                                    <option value="{{ $key }}" {{ $statusFilter === $key ? 'selected' : '' }}>
                                        {{ $label }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-2">
                            <label class="form-label small text-muted">{{ __('messages.date_from') }}</label>
                            <input type="date" name="date_from" class="form-control form-control-sm" value="{{ $dateFrom }}">
                        </div>
                        <div class="col-md-2">
                            <label class="form-label small text-muted">{{ __('messages.date_to') }}</label>
                            <input type="date" name="date_to" class="form-control form-control-sm" value="{{ $dateTo }}">
                        </div>
                        <div class="col-md-2">
                            <label class="form-label small text-muted d-block">&nbsp;</label>
                            <div class="d-flex gap-2">
                                <button type="submit" class="btn btn-primary btn-sm w-100">
                                    <i class="bi bi-search"></i>
                                </button>
                                <button type="button" class="btn btn-secondary btn-sm w-100" onclick="resetFilters()">
                                    <i class="bi bi-arrow-counterclockwise"></i>
                                </button>
                            </div>
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
                <div class="d-flex flex-column flex-md-row gap-3 justify-content-between align-items-md-center mb-3">
                    <h5 class="mb-0 text-dark fw-bold" style="width: 200px;">
                        <i class="bi bi-list-ul me-2 text-primary"></i>
                        {{ __('messages.violations_table') }}
                    </h5>
                    <div class="d-flex flex-column flex-md-row align-items-md-center justify-content-md-end gap-2 ms-md-auto w-100 w-md-auto">
                        <div class="input-group input-group-sm" style="width: 200px;">
                            <span class="input-group-text">
                                <i class="bi bi-search"></i>
                            </span>
                            <input type="text" class="form-control" id="violationSearch" placeholder="{{ __('messages.search_in_table') }}" onkeyup="searchViolations()">
                        </div>
                        <div class="d-flex gap-2 justify-content-end flex-wrap">
                            <a class="btn btn-sm btn-outline-danger"
                               href="{{ route('drivers.violations.export-pdf', ['driver' => $driver] + $violationExportParams) }}">
                                <i class="bi bi-file-pdf me-1"></i> {{ __('messages.export_pdf') }}
                            </a>
                            <a class="btn btn-sm btn-outline-success"
                               href="{{ route('drivers.violations.export-csv', ['driver' => $driver] + $violationExportParams) }}">
                                <i class="bi bi-file-earmark-spreadsheet me-1"></i> {{ __('messages.export_csv') }}
                            </a>
                        </div>
                    </div>
                </div>
            </div>
            <div class="card-body p-0">
                @php
                    $statusLabels = [
                        'pending' => __('messages.pending'),
                        'confirmed' => __('messages.confirmed'),
                        'rejected' => __('messages.rejected'),
                    ];
                    $statusBadges = [
                        'pending' => 'warning',
                        'confirmed' => 'success',
                        'rejected' => 'danger',
                    ];
                @endphp
                <div class="table-responsive">
                    <table class="table table-hover mb-0 align-middle" id="violationsTable">
                        <thead class="table-light">
                            <tr>
                                <th class="border-0 py-3 px-4">{{ __('messages.violation_date') }}</th>
                                <th class="border-0 py-3 px-4">{{ __('messages.violation_type') }}</th>
                                <th class="border-0 py-3 px-4">{{ __('messages.vehicle') }}</th>
                                <th class="border-0 py-3 px-4">{{ __('messages.location') }}</th>
                                <th class="border-0 py-3 px-4">{{ __('messages.status') }}</th>
                                <th class="border-0 py-3 px-4">{{ __('messages.description') }}</th>
                                <th class="border-0 py-3 px-4">{{ __('messages.violation_action_plan') }}</th>
                                <th class="border-0 py-3 px-4 text-center">{{ __('messages.actions') }}</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($driverViolations as $violation)
                                @php
                                    $status = $violation->status ?? 'pending';
                                    $badgeColor = $statusBadges[$status] ?? 'secondary';
                                    $actionPlan = $violation->actionPlan;
                                    $violationTime = optional($violation->violation_time)->format('H:i');
                                    $speedLabel = $violation->speed !== null ? number_format($violation->speed, 2) . ' km/h' : null;
                                    $speedLimitLabel = $violation->speed_limit !== null ? number_format($violation->speed_limit, 2) . ' km/h' : null;
                                    $durationSeconds = $violation->violation_duration_seconds;
                                    $durationLabel = $durationSeconds ? sprintf('%02dm %02ds', intdiv($durationSeconds, 60), $durationSeconds % 60) : null;
                                    $distanceLabel = $violation->violation_distance_km !== null ? number_format($violation->violation_distance_km, 2) . ' km' : null;
                                    $violationPayload = [
                                        'id' => $violation->id,
                                        'date' => optional($violation->violation_date)->format('d/m/Y'),
                                        'type' => $violation->violationType->name ?? __('messages.not_specified'),
                                        'status' => $status,
                                        'status_label' => $statusLabels[$status] ?? ucfirst($status),
                                        'location' => $violation->location ?? __('messages.not_available'),
                                        'description' => $violation->description ?? null,
                                        'notes' => $violation->notes ?? null,
                                        'analysis' => $actionPlan?->analysis,
                                        'action_plan' => $actionPlan?->action_plan,
                                        'vehicle' => $violation->vehicle->license_plate ?? null,
                                        'document_url' => $violation->document_path
                                            ? route('violations.document', $violation)
                                            : null,
                                        'evidence_url' => $actionPlan && $actionPlan->evidence_path
                                            ? route('violations.action-plan.evidence', $violation)
                                            : null,
                                        'evidence_name' => $actionPlan?->evidence_original_name,
                                        'show_url' => route('violations.show', $violation),
                                        'violation_time' => $violationTime,
                                        'speed_label' => $speedLabel,
                                        'speed_limit_label' => $speedLimitLabel,
                                        'duration_seconds' => $durationSeconds,
                                        'duration_label' => $durationLabel,
                                        'distance_label' => $distanceLabel,
                                    ];
                                @endphp
                                <tr class="border-bottom violation-row" data-violation-id="{{ $violation->id }}">
                                    <td class="py-3 px-4">
                                        {{ optional($violation->violation_date)->format('d/m/Y') ?? __('messages.not_available') }}
                                    </td>
                                    <td class="py-3 px-4">
                                        <span class="badge bg-primary bg-opacity-10 text-primary">
                                            {{ $violation->violationType->name ?? __('messages.not_specified') }}
                                        </span>
                                    </td>
                                    <td class="py-3 px-4">
                                        <small class="text-dark">
                                            {{ $violation->vehicle->license_plate ?? __('messages.not_available') }}
                                        </small>
                                    </td>
                                    <td class="py-3 px-4">
                                        <small class="text-muted">
                                            <i class="bi bi-geo-alt me-1"></i>
                                            {{ $violation->location ?? __('messages.not_available') }}
                                        </small>
                                    </td>
                                    <td class="py-3 px-4">
                                        <span class="badge bg-{{ $badgeColor }} bg-opacity-10 text-{{ $badgeColor }}">
                                            {{ $statusLabels[$status] ?? ucfirst($status) }}
                                        </span>
                                    </td>
                                    <td class="py-3 px-4">
                                        <div class="text-dark text-truncate" style="max-width: 220px;"
                                            title="{{ $violation->description ? strip_tags($violation->description) : __('messages.not_available') }}">
                                            {{ $violation->description
                                                ? strip_tags($violation->description)
                                                : __('messages.not_available') }}
                                        </div>
                                    </td>
                                    <td class="py-3 px-4">
                                        <div class="text-dark text-truncate" style="max-width: 220px;"
                                            title="{{ $actionPlan?->action_plan ? strip_tags($actionPlan->action_plan) : __('messages.not_available') }}">
                                            {{ $actionPlan?->action_plan
                                                ? strip_tags($actionPlan->action_plan)
                                                : __('messages.not_available') }}
                                        </div>
                                    </td>
                                    <td class="py-3 px-4 text-center">
                                        <div class="btn-group btn-group-sm" role="group">
                                            @if($violation->document_path)
                                                <a href="{{ route('violations.document', $violation) }}"
                                                   class="btn btn-outline-primary"
                                                   title="{{ __('messages.download') }}">
                                                    <i class="bi bi-download"></i>
                                                </a>
                                            @endif
                                            @if($actionPlan && $actionPlan->evidence_path)
                                                <a href="{{ route('violations.action-plan.evidence', $violation) }}"
                                                   class="btn btn-outline-info"
                                                   title="{{ __('messages.download_evidence') }}">
                                                    <i class="bi bi-paperclip"></i>
                                                </a>
                                            @endif
                                            <a href="{{ route('violations.show', $violation) }}"
                                                class="btn btn-outline-secondary"
                                                title="{{ __('messages.view') }}">
                                                <i class="bi bi-box-arrow-up-right"></i>
                                            </a>
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="9" class="text-center py-5">
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
                @if($driverViolations->count() > 10)
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
    
    <!-- Terminate Driver Modal -->
    <div class="modal fade" id="terminateDriverModal" tabindex="-1" aria-labelledby="terminateDriverModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <form method="POST" action="{{ route('drivers.terminate', $driver) }}" class="modal-content">
                @csrf
                <div class="modal-header">
                    <h5 class="modal-title" id="terminateDriverModalLabel">
                        <i class="bi bi-person-x text-danger me-2"></i>{{ __('messages.terminate_driver') }}
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <p class="text-muted">{{ __('messages.terminate_driver_description') }}</p>
                    <div class="mb-3">
                        <label for="terminatedAtInput" class="form-label">{{ __('messages.terminated_at') }}</label>
                        <input type="date" id="terminatedAtInput" name="terminated_at" class="form-control" value="{{ now()->format('Y-m-d') }}" required>
                    </div>
                    <div class="mb-3">
                        <label for="terminatedCauseInput" class="form-label">{{ __('messages.terminated_cause') }}</label>
                        <textarea
                            id="terminatedCauseInput"
                            name="terminated_cause"
                            class="form-control"
                            rows="3"
                            maxlength="500"
                            placeholder="{{ __('messages.terminated_cause_placeholder') }}"
                            required
                        >{{ old('terminated_cause') }}</textarea>
                        <small class="text-muted">{{ __('messages.terminated_cause_hint') }}</small>
                    </div>
                    <div class="alert alert-warning">
                        <i class="bi bi-exclamation-triangle me-2"></i>{{ __('messages.terminate_driver_warning') }}
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">{{ __('messages.cancel') }}</button>
                    <button type="submit" class="btn btn-danger">
                        <i class="bi bi-check2-circle me-1"></i>{{ __('messages.confirm_termination') }}
                    </button>
                </div>
            </form>
        </div>
    </div>

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

        // Basic HTML escaping for dynamic content
        function escapeHtml(value) {
            if (value === null || value === undefined) {
                return '';
            }
            return value
                .toString()
                .replace(/&/g, '&amp;')
                .replace(/</g, '&lt;')
                .replace(/>/g, '&gt;')
                .replace(/"/g, '&quot;')
                .replace(/'/g, '&#39;');
        }

        function resetFilters() {
            window.location.href = "{{ route('drivers.show', $driver) }}";
        }

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
                
                if (columnIndex === 0) {
                    // Date sorting
                    return isAscending ? aText.localeCompare(bText) : bText.localeCompare(aText);
                } else {
                    // Text sorting
                    return isAscending ? aText.localeCompare(bText) : bText.localeCompare(aText);
                }
            });
            
            rows.forEach(row => tbody.appendChild(row));
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

