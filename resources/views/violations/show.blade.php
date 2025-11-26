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
        <div class="row justify-content-center">
            <div class="col-xl-9 col-lg-10">
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <a href="javascript:history.back()" class="btn btn-outline-secondary">
                        <i class="bi bi-arrow-left me-1"></i>{{ __('messages.back') }}
                    </a>
                    <div class="d-flex gap-2">
                        <a href="{{ route('violations.edit', $violation) }}" class="btn btn-primary">
                            <i class="bi bi-pencil-square me-1"></i>{{ __('messages.edit') }}
                        </a>
                        @if($violation->isConfirmed())
                            <a href="{{ route('violations.report', $violation) }}" class="btn btn-danger">
                                <i class="bi bi-file-earmark-arrow-down me-1"></i>{{ __('messages.download_report') }}
                            </a>
                        @endif
                        @if($violation->isPending())
                            <form action="{{ route('violations.mark-confirmed', $violation) }}" method="POST" class="d-inline">
                                @csrf
                                <button type="submit" class="btn btn-success">
                                    <i class="bi bi-check-circle me-1"></i>{{ __('messages.mark_as_confirmed') }}
                                </button>
                            </form>
                            <form action="{{ route('violations.mark-rejected', $violation) }}" method="POST" class="d-inline">
                                @csrf
                                <button type="submit" class="btn btn-outline-danger">
                                    <i class="bi bi-x-octagon me-1"></i>{{ __('messages.mark_as_rejected') }}
                                </button>
                            </form>
                        @endif
                    </div>
                </div>

                <div class="card border-0 shadow-sm mb-4">
                    <div class="card-body p-4">
                        <div class="d-flex flex-column flex-md-row justify-content-between align-items-start align-items-md-center mb-4">
                            <div>
                                <h4 class="fw-bold mb-1">{{ __('messages.violation') }} #{{ $violation->id }}</h4>
                                <p class="text-muted mb-0">
                                    {{ __('messages.violation_date') }}: {{ $violation->violation_date?->format('d/m/Y') }}
                                    @if($violation->driver)
                                        | {{ __('messages.driver') }}: {{ $violation->driver->full_name }}
                                    @endif
                                </p>
                            </div>
                            <span class="badge bg-{{ $statusBadges[$violation->status] ?? 'secondary' }} bg-opacity-10 text-{{ $statusBadges[$violation->status] ?? 'secondary' }} fs-6 px-4 py-2">
                                {{ $statusLabels[$violation->status] ?? ucfirst($violation->status) }}
                            </span>
                        </div>

                        <div class="row g-4">
                            <div class="col-md-6">
                                <div class="border rounded-3 p-3 h-100">
                                    <h6 class="text-uppercase text-muted small fw-semibold">{{ __('messages.driver') }}</h6>
                                    <p class="fw-semibold mb-1">{{ $violation->driver?->full_name ?? __('messages.not_available') }}</p>
                                    @if($violation->driver)
                                        <small class="text-muted">{{ $violation->driver->email ?? '' }}</small>
                                    @endif
                                </div>
                            </div>

                            <div class="col-md-6">
                                <div class="border rounded-3 p-3 h-100">
                                    <h6 class="text-uppercase text-muted small fw-semibold">{{ __('messages.vehicle') }}</h6>
                                    <p class="fw-semibold mb-0">{{ $violation->vehicle?->license_plate ?? __('messages.not_available') }}</p>
                                </div>
                            </div>

                            <div class="col-md-6">
                                <div class="border rounded-3 p-3 h-100">
                                    <h6 class="text-uppercase text-muted small fw-semibold">{{ __('messages.violation_type') }}</h6>
                                    <p class="fw-semibold mb-0">{{ $violation->type_name }}</p>
                                </div>
                            </div>
                            
                            <div class="col-md-6">
                                <div class="border rounded-3 p-3 h-100">
                                    <h6 class="text-uppercase text-muted small fw-semibold">{{ __('messages.location') }}</h6>
                                    <p class="fw-semibold mb-0">{{ $violation->location ?? __('messages.not_available') }}</p>
                                </div>
                            </div>

                            @if($violation->location_lat && $violation->location_lng)
                            <div class="col-12 mt-3">
                                <div class="border rounded-3 p-3">
                                    <h6 class="text-uppercase text-muted small fw-semibold mb-2">
                                        <i class="bi bi-geo-alt me-1"></i>{{ __('messages.location') }} — {{ __('messages.location_coords_label') }}
                                    </h6>
                                    <p class="small text-muted mb-2">
                                        {{ __('messages.location_coords_label') }}:
                                        <span class="fw-semibold">{{ $violation->location_lat }}, {{ $violation->location_lng }}</span>
                                    </p>
                                    <div id="violation-location-map"
                                         style="width: 100%; height: 260px; border-radius: 0.5rem; border: 1px solid #dee2e6;"></div>
                                </div>
                            </div>
                            @endif

                            <div class="col-md-6">
                                <div class="border rounded-3 p-3 h-100 d-flex justify-content-start align-items-center">
                                    <div class="me-3">
                                        <h6 class="text-uppercase text-muted small fw-semibold">{{ __('messages.violation_date') }}</h6>
                                        <p class="fw-semibold mb-0">{{ $violation->violation_date?->format('d/m/Y') }}</p>
                                    </div>
                                    @if($violation->violation_time)
                                        <div>
                                            <h6 class="text-uppercase text-muted small fw-semibold">{{ __('messages.violation_time') }}</h6>
                                            <p class="fw-semibold mb-0">{{ $violation->violation_time?->format('H:i') }}</p>
                                        </div>
                                    @endif
                                </div>
                            </div>
                            
                            @php
                                $durationSeconds = $violation->violation_duration_seconds;
                                $durationLabel = $durationSeconds ? sprintf('%02dm %02ds', intdiv($durationSeconds, 60), $durationSeconds % 60) : null;
                            @endphp

                            <div class="col-md-6">
                                <div class="border rounded-3 p-3 h-100">
                                    <h6 class="text-uppercase text-muted small fw-semibold">{{ __('messages.violation_duration') }}</h6>
                                    <p class="fw-semibold mb-0">{{ $durationLabel ?? __('messages.not_available') }}</p>
                                </div>
                            </div>

                            @if($violation->violation_time || $violation->speed !== null || $violation->speed_limit !== null || $durationLabel || $violation->violation_distance_km !== null)
                                <div class="col-12">
                                    <div class="border rounded-3 p-3">
                                        <h6 class="text-uppercase text-muted small fw-semibold mb-3">{{ __('messages.violation_metrics') }}</h6>
                                        <div class="row g-3">
                                            @if($violation->speed !== null)
                                                <div class="col-sm-6 col-lg-4">
                                                    <small class="text-muted d-block">{{ __('messages.violation_speed') }}</small>
                                                    <p class="fw-semibold mb-0">{{ number_format($violation->speed, 2) }} km/h</p>
                                                </div>
                                            @endif
                                            @if($violation->speed_limit !== null)
                                                <div class="col-sm-6 col-lg-4">
                                                    <small class="text-muted d-block">{{ __('messages.violation_speed_limit') }}</small>
                                                    <p class="fw-semibold mb-0">{{ number_format($violation->speed_limit, 2) }} km/h</p>
                                                </div>
                                            @endif
                                            <div class="col-sm-6 col-lg-4">
                                                <small class="text-muted d-block">{{ __('messages.violation_distance') }}</small>
                                                @if($violation->violation_distance_km !== null)
                                                    <p class="fw-semibold mb-0">{{ number_format($violation->violation_distance_km, 2) }} km</p>
                                                @else
                                                    <p class="fw-semibold mb-0">{{ __('messages.not_available') }}</p>
                                                @endif
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            @endif

                            @if($violation->description)
                            <div class="col-12">
                                <div class="border rounded-3 p-3">
                                    <h6 class="text-uppercase text-muted small fw-semibold">{{ __('messages.description') }}</h6>
                                    <p class="mb-0">{{ $violation->description }}</p>
                                </div>
                            </div>
                            @endif

                            @if($violation->analysis || $violation->action_plan)
                            <div class="col-12">
                                <div class="border rounded-3 p-3">
                                    <div class="d-flex justify-content-between align-items-start mb-3">
                                        <div>
                                            <h6 class="text-uppercase text-muted small fw-semibold mb-1">{{ __('messages.violation_action_plan_section') }}</h6>
                                            <p class="text-muted mb-0">{{ __('messages.violation_action_plan_subtitle') }}</p>
                                        </div>
                                        @if($violation->evidence_path)
                                            <a href="{{ route('violations.action-plan.evidence', $violation) }}" class="btn btn-outline-primary btn-sm">
                                                <i class="bi bi-paperclip me-1"></i>{{ __('messages.download_evidence') }}
                                            </a>
                                        @endif
                                    </div>
                                    <div class="row g-3">
                                        <div class="col-md-6">
                                            <h6 class="fw-semibold text-dark">{{ __('messages.violation_analysis') }}</h6>
                                            <p class="mb-0">{{ $violation->analysis }}</p>
                                        </div>
                                        <div class="col-md-6">
                                            <h6 class="fw-semibold text-dark">{{ __('messages.violation_action_plan') }}</h6>
                                            <p class="mb-0">{{ $violation->action_plan }}</p>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            @endif

                            @if($violation->document_path)
                            <div class="col-12">
                                <div class="border rounded-3 p-3">
                                    <h6 class="text-uppercase text-muted small fw-semibold">{{ __('messages.document') }}</h6>
                                    <a href="{{ route('violations.document', $violation) }}" class="btn btn-outline-primary btn-sm" target="_blank">
                                        <i class="bi bi-download me-1"></i>{{ __('messages.download') }} {{ __('messages.document') }}
                                    </a>
                                </div>
                            </div>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
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
