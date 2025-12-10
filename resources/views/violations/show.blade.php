<x-app-layout>
    <x-slot name="header">
        @include('layouts.topnav')
    </x-slot>

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

    <div class="container-fluid py-4 mt-4">
        @if (session('success'))
            <div class="alert alert-success alert-dismissible fade show" role="alert">
                <i class="bi bi-check-circle me-2"></i>
                {{ session('success') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        @endif

        @if (session('error'))
            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                <i class="bi bi-exclamation-triangle me-2"></i>
                {{ session('error') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        @endif

        <!-- Header -->
        <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center mb-4 gap-3 bg-white p-4 rounded-3 shadow-sm">
            <div>
                <h2 class="mb-0 fw-bold text-dark fs-4">
                    <i class="bi bi-exclamation-triangle me-2 text-danger"></i>
                    {{ __('messages.violation') }} #{{ $violation->id }}
                </h2>
                <p class="text-muted mb-0 mt-1">
                    {{ __('messages.violation_date') }}: {{ $violation->violation_date?->format('d/m/Y') }}
                    @if($violation->driver)
                        | {{ __('messages.driver') }}: {{ $violation->driver->full_name }}
                    @endif
                </p>
            </div>
            <div>
                <a href="{{ route('violations.index') }}" class="btn btn-sm btn-outline-secondary">
                    <i class="bi bi-arrow-left me-1"></i>
                    {{ __('messages.back_to_list') ?? 'Back to List' }}
                </a>
            </div>
        </div>

        <!-- Main Content and Sidebar -->
        <div class="row g-4">
            <!-- Main Section -->
            <div class="col-lg-8">
                <!-- Violation Details Card -->
                <div class="card border-0 shadow-sm mb-4">
                    <div class="card-header bg-white border-0 py-3">
                        <h5 class="mb-0 fw-bold text-dark">
                            <i class="bi bi-info-circle me-2 text-primary"></i>
                            {{ __('messages.violation_information') ?? 'Violation Information' }}
                        </h5>
                    </div>
                    <div class="card-body p-4">
                        <div class="row g-3">
                            <div class="col-md-6">
                                <label class="form-label fw-semibold text-muted small">
                                    {{ __('messages.driver') }}
                                </label>
                                <p class="mb-0">
                                    <span class="fw-semibold">{{ $violation->driver?->full_name ?? __('messages.not_available') }}</span>
                                    @if($violation->driver && $violation->driver->email)
                                        <br>
                                        <small class="text-muted">{{ $violation->driver->email }}</small>
                                    @endif
                                </p>
                            </div>

                            <div class="col-md-6">
                                <label class="form-label fw-semibold text-muted small">
                                    {{ __('messages.vehicle') }}
                                </label>
                                <p class="mb-0">
                                    <span class="fw-semibold">{{ $violation->vehicle?->license_plate ?? __('messages.not_available') }}</span>
                                </p>
                            </div>

                            <div class="col-md-6">
                                <label class="form-label fw-semibold text-muted small">
                                    {{ __('messages.violation_type') }}
                                </label>
                                <p class="mb-0">
                                    <span class="fw-semibold">{{ $violation->type_name }}</span>
                                </p>
                            </div>

                            <div class="col-md-6">
                                <label class="form-label fw-semibold text-muted small">
                                    {{ __('messages.violation_date') }}
                                </label>
                                <p class="mb-0">
                                    <span class="fw-semibold">{{ $violation->violation_date?->format('d/m/Y') }}</span>
                                    @if($violation->violation_time)
                                        <br>
                                        <small class="text-muted">{{ __('messages.violation_time') }}: {{ $violation->violation_time->format('H:i:s') }}</small>
                                    @endif
                                </p>
                            </div>

                            <div class="col-md-6">
                                <label class="form-label fw-semibold text-muted small">
                                    {{ __('messages.location') }}
                                </label>
                                <p class="mb-0">
                                    <span class="fw-semibold">{{ $violation->location ?? __('messages.not_available') }}</span>
                                    @if($violation->location_lat && $violation->location_lng)
                                        <br>
                                        <small class="text-muted">{{ $violation->location_lat }}, {{ $violation->location_lng }}</small>
                                    @endif
                                </p>
                            </div>

                            @php
                                $durationSeconds = $violation->violation_duration_seconds;
                                $durationLabel = $durationSeconds ? sprintf('%02dm %02ds', intdiv($durationSeconds, 60), $durationSeconds % 60) : null;
                            @endphp

                            <div class="col-md-6">
                                <label class="form-label fw-semibold text-muted small">
                                    {{ __('messages.violation_duration') }}
                                </label>
                                <p class="mb-0">
                                    <span class="fw-semibold">{{ $durationLabel ?? __('messages.not_available') }}</span>
                                </p>
                            </div>

                            @if($violation->speed !== null || $violation->speed_limit !== null || $violation->violation_distance_km !== null)
                                <div class="col-12">
                                    <hr class="my-2">
                                    <h6 class="fw-semibold mb-3">{{ __('messages.violation_metrics') ?? 'Violation Metrics' }}</h6>
                                    <div class="row g-3">
                                        @if($violation->speed !== null)
                                            <div class="col-sm-6 col-lg-4">
                                                <label class="form-label fw-semibold text-muted small">
                                                    {{ __('messages.violation_speed') }}
                                                </label>
                                                <p class="mb-0">
                                                    <span class="badge bg-primary fs-6">{{ number_format($violation->speed, 2) }} km/h</span>
                                                </p>
                                            </div>
                                        @endif
                                        @if($violation->speed_limit !== null)
                                            <div class="col-sm-6 col-lg-4">
                                                <label class="form-label fw-semibold text-muted small">
                                                    {{ __('messages.violation_speed_limit') }}
                                                </label>
                                                <p class="mb-0">
                                                    <span class="badge bg-warning fs-6">{{ number_format($violation->speed_limit, 2) }} km/h</span>
                                                </p>
                                            </div>
                                        @endif
                                        @if($violation->violation_distance_km !== null)
                                            <div class="col-sm-6 col-lg-4">
                                                <label class="form-label fw-semibold text-muted small">
                                                    {{ __('messages.violation_distance') }}
                                                </label>
                                                <p class="mb-0">
                                                    <span class="badge bg-info fs-6">{{ number_format($violation->violation_distance_km, 2) }} km</span>
                                                </p>
                                            </div>
                                        @endif
                                    </div>
                                </div>
                            @endif
                        </div>
                    </div>
                </div>

                <!-- Description Card -->
                @if($violation->description)
                <div class="card border-0 shadow-sm mb-4">
                    <div class="card-header bg-white border-0 py-3">
                        <h5 class="mb-0 fw-bold text-dark">
                            <i class="bi bi-file-text me-2 text-primary"></i>
                            {{ __('messages.description') }}
                        </h5>
                    </div>
                    <div class="card-body p-4">
                        <div class="card border bg-light rounded-3 shadow-sm">
                            <div class="card-body p-3">
                                <p class="mb-0">{{ $violation->description }}</p>
                            </div>
                        </div>
                    </div>
                </div>
                @endif

                <!-- Analysis and Action Plan Card -->
                @if($violation->analysis || $violation->action_plan)
                <div class="card border-0 shadow-sm mb-4">
                    <div class="card-header bg-white border-0 py-3 d-flex justify-content-between align-items-center">
                        <h5 class="mb-0 fw-bold text-dark">
                            <i class="bi bi-clipboard-check me-2 text-primary"></i>
                            {{ __('messages.violation_action_plan_section') ?? 'Analysis & Action Plan' }}
                        </h5>
                        @if($violation->evidence_path)
                            <a href="{{ route('violations.action-plan.evidence', $violation) }}" class="btn btn-outline-primary btn-sm">
                                <i class="bi bi-paperclip me-1"></i>{{ __('messages.download_evidence') }}
                            </a>
                        @endif
                    </div>
                    <div class="card-body p-4">
                        <div class="row g-4">
                            @if($violation->analysis)
                            <div class="col-md-6">
                                <h6 class="fw-semibold text-dark mb-3">
                                    <i class="bi bi-search me-2 text-info"></i>
                                    {{ __('messages.violation_analysis') }}
                                </h6>
                                <div class="card border bg-light rounded-3 shadow-sm">
                                    <div class="card-body p-3">
                                        <p class="mb-0">{{ $violation->analysis }}</p>
                                    </div>
                                </div>
                            </div>
                            @endif
                            @if($violation->action_plan)
                            <div class="col-md-6">
                                <h6 class="fw-semibold text-dark mb-3">
                                    <i class="bi bi-list-check me-2 text-success"></i>
                                    {{ __('messages.violation_action_plan') }}
                                </h6>
                                <div class="card border bg-light rounded-3 shadow-sm">
                                    <div class="card-body p-3">
                                        <p class="mb-0">{{ $violation->action_plan }}</p>
                                    </div>
                                </div>
                            </div>
                            @endif
                        </div>
                    </div>
                </div>
                @endif

                <!-- Location Map Card -->
                @if($violation->location_lat && $violation->location_lng)
                <div class="card border-0 shadow-sm mb-4">
                    <div class="card-header bg-white border-0 py-3">
                        <h5 class="mb-0 fw-bold text-dark">
                            <i class="bi bi-geo-alt me-2 text-primary"></i>
                            {{ __('messages.location') }} — {{ __('messages.location_coords_label') }}
                        </h5>
                    </div>
                    <div class="card-body p-0">
                        <div class="p-3 border-bottom">
                            <small class="text-muted">
                                {{ __('messages.location_coords_label') }}:
                                <span class="fw-semibold">{{ $violation->location_lat }}, {{ $violation->location_lng }}</span>
                            </small>
                        </div>
                        <div id="violation-location-map" style="width: 100%; height: 400px; border-radius: 0.5rem;"></div>
                    </div>
                </div>
                @endif

                <!-- Documents Card -->
                @if($violation->document_path)
                <div class="card border-0 shadow-sm mb-4">
                    <div class="card-header bg-white border-0 py-3">
                        <h5 class="mb-0 fw-bold text-dark">
                            <i class="bi bi-file-earmark me-2 text-primary"></i>
                            {{ __('messages.document') }}
                        </h5>
                    </div>
                    <div class="card-body p-4">
                        <a href="{{ route('violations.document', $violation) }}" class="btn btn-outline-primary" target="_blank">
                            <i class="bi bi-download me-1"></i>{{ __('messages.download') }} {{ __('messages.document') }}
                        </a>
                    </div>
                </div>
                @endif
            </div>

            <!-- Sidebar -->
            <div class="col-lg-4">
                <!-- Quick Actions Card -->
                <div class="card border-0 shadow-sm">
                    <div class="card-header bg-white border-0 py-3">
                        <h5 class="mb-0 fw-bold text-dark">
                            <i class="bi bi-lightning-charge me-2 text-warning"></i>
                            {{ __('messages.quick_actions') ?? 'Quick Actions' }}
                        </h5>
                    </div>
                    <div class="card-body p-3">
                        <div class="d-grid gap-2">
                            <a href="{{ route('violations.edit', $violation) }}" class="btn btn-warning">
                                <i class="bi bi-pencil me-2"></i>
                                {{ __('messages.edit') }}
                            </a>
                            @if($violation->isConfirmed())
                                <a href="{{ route('violations.report', $violation) }}" class="btn btn-danger">
                                    <i class="bi bi-file-earmark-arrow-down me-2"></i>
                                    {{ __('messages.download_report') }}
                                </a>
                            @endif
                            @if($violation->isPending())
                                <form action="{{ route('violations.mark-confirmed', $violation) }}" method="POST" class="d-inline">
                                    @csrf
                                    <button type="submit" class="btn btn-success w-100">
                                        <i class="bi bi-check-circle me-2"></i>
                                        {{ __('messages.mark_as_confirmed') }}
                                    </button>
                                </form>
                                <form action="{{ route('violations.mark-rejected', $violation) }}" method="POST" class="d-inline">
                                    @csrf
                                    <button type="submit" class="btn btn-outline-danger w-100">
                                        <i class="bi bi-x-octagon me-2"></i>
                                        {{ __('messages.mark_as_rejected') }}
                                    </button>
                                </form>
                            @endif
                        </div>
                    </div>
                </div>

                <!-- Violation Status Card -->
                <div class="card border-0 shadow-sm mt-4">
                    <div class="card-header bg-white border-0 py-3">
                        <h5 class="mb-0 fw-bold text-dark">
                            <i class="bi bi-info-circle me-2 text-primary"></i>
                            {{ __('messages.status') ?? 'Status' }}
                        </h5>
                    </div>
                    <div class="card-body p-3">
                        <div class="d-flex flex-column gap-3">
                            <div>
                                <label class="form-label fw-semibold text-muted small mb-1">
                                    {{ __('messages.status') ?? 'Status' }}
                                </label>
                                <p class="mb-0">
                                    <span class="badge bg-{{ $statusBadges[$violation->status] ?? 'secondary' }} bg-opacity-10 text-{{ $statusBadges[$violation->status] ?? 'secondary' }} fs-6 px-3 py-2">
                                        {{ $statusLabels[$violation->status] ?? ucfirst($violation->status) }}
                                    </span>
                                </p>
                            </div>

                            @if($violation->created_at)
                            <div>
                                <label class="form-label fw-semibold text-muted small mb-1">
                                    {{ __('messages.created_at') ?? 'Created At' }}
                                </label>
                                <p class="mb-0">
                                    <span class="fw-semibold">{{ $violation->created_at->format('d/m/Y H:i') }}</span>
                                </p>
                            </div>
                            @endif

                            @if($violation->updated_at && $violation->updated_at != $violation->created_at)
                            <div>
                                <label class="form-label fw-semibold text-muted small mb-1">
                                    {{ __('messages.updated_at') ?? 'Updated At' }}
                                </label>
                                <p class="mb-0">
                                    <span class="fw-semibold">{{ $violation->updated_at->format('d/m/Y H:i') }}</span>
                                </p>
                            </div>
                            @endif

                            @if($violation->createdBy)
                            <div>
                                <label class="form-label fw-semibold text-muted small mb-1">
                                    {{ __('messages.created_by') ?? 'Created By' }}
                                </label>
                                <p class="mb-0">
                                    <span class="fw-semibold">{{ $violation->createdBy->name ?? $violation->createdBy->email ?? '-' }}</span>
                                </p>
                            </div>
                            @endif
                        </div>
                    </div>
                </div>

                <!-- Driver Information Card -->
                @if($violation->driver)
                <div class="card border-0 shadow-sm mt-4">
                    <div class="card-header bg-white border-0 py-3">
                        <h5 class="mb-0 fw-bold text-dark">
                            <i class="bi bi-person me-2 text-primary"></i>
                            {{ __('messages.driver_information') ?? 'Driver Information' }}
                        </h5>
                    </div>
                    <div class="card-body p-3">
                        <div class="d-flex flex-column gap-3">
                            <div>
                                <label class="form-label fw-semibold text-muted small mb-1">
                                    {{ __('messages.driver') }}
                                </label>
                                <p class="mb-0">
                                    <span class="fw-semibold">{{ $violation->driver->full_name }}</span>
                                </p>
                            </div>

                            @if($violation->driver->email)
                            <div>
                                <label class="form-label fw-semibold text-muted small mb-1">
                                    {{ __('messages.email') }}
                                </label>
                                <p class="mb-0">
                                    <span class="fw-semibold">{{ $violation->driver->email }}</span>
                                </p>
                            </div>
                            @endif

                            @if($violation->driver->phone)
                            <div>
                                <label class="form-label fw-semibold text-muted small mb-1">
                                    {{ __('messages.phone') }}
                                </label>
                                <p class="mb-0">
                                    <span class="fw-semibold">{{ $violation->driver->phone }}</span>
                                </p>
                            </div>
                            @endif

                            <div>
                                <a href="{{ route('drivers.show', $violation->driver) }}" class="btn btn-sm btn-outline-primary w-100">
                                    <i class="bi bi-eye me-1"></i>
                                    {{ __('messages.view_driver') ?? 'View Driver' }}
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
                @endif
            </div>
        </div>
    </div>
</x-app-layout>

@if($violation->location_lat && $violation->location_lng)
<link
    rel="stylesheet"
    href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css"
    integrity="sha256-p4NxAoJBhIIN+hmNHrzRCf9tD/miZyoHS5obTRR9BMY="
    crossorigin=""
/>
<script
    src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"
    integrity="sha256-20nQCchB9co0qIjJZRGuk2/Z9VM+kNiyxNV1lvTlZBo="
    crossorigin=""
></script>

<script>
document.addEventListener('DOMContentLoaded', function () {
    if (!window.L) {
        return;
    }

    const mapContainer = document.getElementById('violation-location-map');
    if (!mapContainer) {
        return;
    }

    const lat = parseFloat(@json($violation->location_lat));
    const lng = parseFloat(@json($violation->location_lng));

    if (isNaN(lat) || isNaN(lng)) {
        return;
    }

    const map = L.map(mapContainer).setView([lat, lng], 13);

    L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
        maxZoom: 19,
        attribution: '&copy; OpenStreetMap contributors',
    }).addTo(map);

    L.marker([lat, lng]).addTo(map);
});
</script>
@endif
