<x-app-layout>
    <x-slot name="header">
        @include('layouts.topnav')
    </x-slot>

    <div class="toast-container position-fixed top-0 start-50 translate-middle-x p-3" style="z-index: 1055;">
        @if(session('success'))
            <div class="toast show" role="alert" aria-live="assertive" aria-atomic="true" data-bs-autohide="true" data-bs-delay="10000">
                <div class="toast-header bg-success text-white">
                    <i class="bi bi-check-circle me-2"></i>
                    <strong class="me-auto">{{ __('messages.success') }}</strong>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="toast" aria-label="Close"></button>
                </div>
                <div class="toast-body">
                    {{ session('success') }}
                </div>
            </div>
        @endif

        @if(session('error'))
            <div class="toast show" role="alert" aria-live="assertive" aria-atomic="true" data-bs-autohide="true" data-bs-delay="10000">
                <div class="toast-header bg-danger text-white">
                    <i class="bi bi-exclamation-triangle me-2"></i>
                    <strong class="me-auto">{{ __('messages.error') }}</strong>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="toast" aria-label="Close"></button>
                </div>
                <div class="toast-body">
                    {{ session('error') }}
                </div>
            </div>
        @endif
    </div>

    <div class="container-fluid py-4 mt-4">
        <div class="row justify-content-center">
            <div class="col-lg-9">
                <div class="card border-0 shadow-sm">
                    <div class="card-header bg-white border-0 py-3">
                        <div class="d-flex justify-content-between align-items-center">
                            <h5 class="mb-0 text-dark fw-bold">
                                <i class="bi bi-pencil-square me-2 text-primary"></i>
                                {{ __('messages.edit') }} {{ __('messages.violation') }}
                            </h5>
                            <a href="{{ route('violations.show', $violation) }}" class="btn btn-outline-secondary">
                                <i class="bi bi-x-circle me-1"></i> {{ __('messages.cancel') }}
                            </a>
                        </div>
                    </div>
                    <div class="card-body">
                        <form action="{{ route('violations.update', $violation) }}" method="POST" enctype="multipart/form-data" novalidate>
                            @csrf
                            @method('PUT')

                            <div class="row g-4">
                                <div class="col-md-6">
                                    <label for="driver_id" class="form-label fw-semibold">{{ __('messages.driver') }} <span class="text-danger">*</span></label>
                                    <select
                                        id="driver_id"
                                        name="driver_id"
                                        class="form-select @error('driver_id') is-invalid @enderror"
                                        required
                                    >
                                        <option value="">{{ __('messages.select_driver') }}</option>
                                        @foreach($drivers as $driver)
                                            <option
                                                value="{{ $driver->id }}"
                                                data-vehicle="{{ $driver->assignedVehicle?->id ?? '' }}"
                                                @selected((int) old('driver_id', $violation->driver_id) === (int) $driver->id)
                                            >
                                                {{ $driver->full_name }}
                                            </option>
                                        @endforeach
                                    </select>
                                    @error('driver_id')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>

                                <div class="col-md-6">
                                    <label for="violation_date" class="form-label fw-semibold">{{ __('messages.violation_date') }} <span class="text-danger">*</span></label>
                                    <input
                                        type="date"
                                        id="violation_date"
                                        name="violation_date"
                                        class="form-control @error('violation_date') is-invalid @enderror"
                                        value="{{ old('violation_date', $violation->violation_date?->format('Y-m-d')) }}"
                                        required
                                    >
                                    @error('violation_date')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>

                                <div class="col-md-6">
                                    <label for="violation_time" class="form-label fw-semibold">{{ __('messages.violation_time') }}</label>
                                    <input
                                        type="time"
                                        id="violation_time"
                                        name="violation_time"
                                        class="form-control @error('violation_time') is-invalid @enderror"
                                        value="{{ old('violation_time', $violation->violation_time ? $violation->violation_time->format('H:i') : null) }}"
                                    >
                                    @error('violation_time')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>

                                <div class="col-md-6">
                                    <label for="violation_type_id" class="form-label fw-semibold">{{ __('messages.violation_type') }} <span class="text-danger">*</span></label>
                                    <select
                                        id="violation_type_id"
                                        name="violation_type_id"
                                        class="form-select @error('violation_type_id') is-invalid @enderror"
                                        required
                                    >
                                        <option value="">{{ __('messages.select_violation_type') }}</option>
                                        @foreach($violationTypes as $type)
                                            <option 
                                                value="{{ $type->id }}"
                                                @selected((int) old('violation_type_id', $violation->violation_type_id) === (int) $type->id)
                                            >
                                                {{ $type->name }}
                                            </option>
                                        @endforeach
                                    </select>
                                    @error('violation_type_id')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>

                                <div class="col-md-6">
                                    <label for="location" class="form-label fw-semibold">{{ __('messages.location') }}</label>
                                    <div class="input-group">
                                        <input
                                            type="text"
                                            id="location"
                                            name="location"
                                            class="form-control @error('location') is-invalid @enderror"
                                            value="{{ old('location', $violation->location) }}"
                                            placeholder="{{ __('messages.location_area_placeholder') }}"
                                        >
                                        <button
                                            type="button"
                                            class="btn btn-outline-secondary"
                                            data-bs-toggle="modal"
                                            data-bs-target="#violationLocationMapModal"
                                            id="open-location-map-btn"
                                        >
                                            <i class="bi bi-geo-alt"></i>
                                        </button>
                                    </div>
                                    @error('location')
                                        <div class="invalid-feedback d-block">{{ $message }}</div>
                                    @enderror

                                    @php
                                        $oldLat = old('location_lat', $violation->location_lat);
                                        $oldLng = old('location_lng', $violation->location_lng);
                                    @endphp
                                    <input type="hidden" name="location_lat" id="location_lat" value="{{ $oldLat }}">
                                    <input type="hidden" name="location_lng" id="location_lng" value="{{ $oldLng }}">

                                    <small class="text-muted d-block mt-1" id="location-coordinates-label">
                                        @if($oldLat && $oldLng)
                                            {{ __('messages.location_coords_label') }}:
                                            <span class="fw-semibold">{{ $oldLat }}, {{ $oldLng }}</span>
                                        @else
                                            {{ __('messages.location_help') }}
                                        @endif
                                    </small>
                                </div>


                                <div class="col-md-6">
                                    <label for="vehicle_id" class="form-label fw-semibold">{{ __('messages.vehicle') }}</label>
                                    <select
                                        id="vehicle_id"
                                        name="vehicle_id"
                                        class="form-select @error('vehicle_id') is-invalid @enderror"
                                    >
                                        <option value="">{{ __('messages.select_vehicle') }}</option>
                                        @foreach($vehicles as $vehicle)
                                            <option 
                                                value="{{ $vehicle->id }}"
                                                @selected((int) old('vehicle_id', $violation->vehicle_id) === (int) $vehicle->id)
                                            >
                                                {{ $vehicle->license_plate }}
                                            </option>
                                        @endforeach
                                    </select>
                                    @error('vehicle_id')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>

                                <div class="col-md-6">
                                    <label for="speed" class="form-label fw-semibold">{{ __('messages.violation_speed') }}</label>
                                    <input
                                        type="number"
                                        step="0.01"
                                        min="0"
                                        id="speed"
                                        name="speed"
                                        class="form-control @error('speed') is-invalid @enderror"
                                        value="{{ old('speed', $violation->speed) }}"
                                        placeholder="{{ __('messages.violation_speed_hint') }}"
                                    >
                                    @error('speed')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>

                                <div class="col-md-6">
                                    <label for="speed_limit" class="form-label fw-semibold">{{ __('messages.violation_speed_limit') }}</label>
                                    <input
                                        type="number"
                                        step="0.01"
                                        min="0"
                                        id="speed_limit"
                                        name="speed_limit"
                                        class="form-control @error('speed_limit') is-invalid @enderror"
                                        value="{{ old('speed_limit', $violation->speed_limit) }}"
                                        placeholder="{{ __('messages.violation_speed_limit_hint') }}"
                                    >
                                    @error('speed_limit')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>

                                <div class="col-md-6">
                                    <label for="violation_duration_seconds" class="form-label fw-semibold">{{ __('messages.violation_duration') }}</label>
                                    <input
                                        type="number"
                                        min="0"
                                        step="1"
                                        id="violation_duration_seconds"
                                        name="violation_duration_seconds"
                                        class="form-control @error('violation_duration_seconds') is-invalid @enderror"
                                        value="{{ old('violation_duration_seconds', $violation->violation_duration_seconds) }}"
                                        placeholder="{{ __('messages.violation_duration_hint') }}"
                                    >
                                    @error('violation_duration_seconds')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>

                                <div class="col-md-6">
                                    <label for="violation_distance_km" class="form-label fw-semibold">{{ __('messages.violation_distance') }}</label>
                                    <input
                                        type="number"
                                        min="0"
                                        step="0.01"
                                        id="violation_distance_km"
                                        name="violation_distance_km"
                                        class="form-control @error('violation_distance_km') is-invalid @enderror"
                                        value="{{ old('violation_distance_km', $violation->violation_distance_km) }}"
                                        placeholder="{{ __('messages.violation_distance_hint') }}"
                                    >
                                    @error('violation_distance_km')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>

                                <div class="col-md-6">
                                    <label for="status" class="form-label fw-semibold">{{ __('messages.status') }}</label>
                                    <select
                                        id="status"
                                        name="status"
                                        class="form-select @error('status') is-invalid @enderror"
                                    >
                                        <option value="pending" @selected(old('status', $violation->status) === 'pending')>{{ __('messages.pending') }}</option>
                                        <option value="confirmed" @selected(old('status', $violation->status) === 'confirmed')>{{ __('messages.confirmed') }}</option>
                                        <option value="rejected" @selected(old('status', $violation->status) === 'rejected')>{{ __('messages.rejected') }}</option>
                                    </select>
                                    @error('status')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>

                                <div class="col-12">
                                    <label for="description" class="form-label fw-semibold">{{ __('messages.description') }}</label>
                                    <textarea
                                        id="description"
                                        name="description"
                                        rows="4"
                                        class="form-control @error('description') is-invalid @enderror"
                                        placeholder="{{ __('messages.violation_description_hint') }}"
                                    >{{ old('description', $violation->description) }}</textarea>
                                    @error('description')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>

                                <div class="col-12">
                                    <label for="analysis" class="form-label fw-semibold">
                                        {{ __('messages.violation_analysis') }}
                                    </label>
                                    <textarea
                                        id="analysis"
                                        name="analysis"
                                        rows="3"
                                        class="form-control @error('analysis') is-invalid @enderror"
                                        placeholder="{{ __('messages.violation_analysis_hint') }}"
                                        required
                                    >{{ old('analysis', $violation->analysis) }}</textarea>
                                    @error('analysis')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>

                                <div class="col-12">
                                    <label for="action_plan" class="form-label fw-semibold">
                                        {{ __('messages.violation_action_plan') }}
                                    </label>
                                    <textarea
                                        id="action_plan"
                                        name="action_plan"
                                        rows="4"
                                        class="form-control @error('action_plan') is-invalid @enderror"
                                        placeholder="{{ __('messages.violation_action_plan_hint') }}"
                                        required
                                    >{{ old('action_plan', $violation->action_plan) }}</textarea>
                                    @error('action_plan')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>

                                <div class="col-12">
                                    <label for="evidence" class="form-label fw-semibold">{{ __('messages.violation_evidence') }}</label>
                                    @if($violation->evidence_path)
                                        <div class="mb-2 d-flex align-items-center gap-2">
                                            <a href="{{ route('violations.action-plan.evidence', $violation) }}" class="btn btn-sm btn-outline-primary">
                                                <i class="bi bi-download me-1"></i>{{ __('messages.download') }}
                                            </a>
                                            <span class="text-muted small">{{ $violation->evidence_original_name }}</span>
                                        </div>
                                    @endif
                                    <input
                                        type="file"
                                        id="evidence"
                                        name="evidence"
                                        class="form-control @error('evidence') is-invalid @enderror"
                                        accept=".pdf,.jpg,.jpeg,.png"
                                    >
                                    <small class="text-muted">{{ __('messages.violation_evidence_hint') }}</small>
                                    @error('evidence')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>

                                <div class="col-12">
                                    <label for="document" class="form-label fw-semibold">{{ __('messages.document') }}</label>
                                    @if($violation->document_path)
                                        <div class="mb-2">
                                            <a href="{{ route('violations.document', $violation) }}" target="_blank" class="btn btn-sm btn-outline-primary">
                                                <i class="bi bi-eye me-1"></i>{{ __('messages.view_current_document') }}
                                            </a>
                                        </div>
                                    @endif
                                    <input
                                        type="file"
                                        id="document"
                                        name="document[]"
                                        class="form-control @error('document') is-invalid @enderror @error('document.*') is-invalid @enderror"
                                        accept=".pdf,.jpg,.jpeg,.png"
                                        multiple
                                    >
                                    <small class="text-muted">{{ __('messages.max_file_size') }}: 10MB. {{ __('messages.leave_empty_to_keep_current') }}</small>
                                    @error('document')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                    @error('document.*')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>

                            <hr class="my-4">

                            <div class="d-flex justify-content-end mt-4">
                                <button type="submit" class="btn btn-dark">
                                    <i class="bi bi-save me-2"></i>{{ __('messages.update') }}
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>

<!-- Location Map Modal (same behavior as create) -->
<div class="modal fade" id="violationLocationMapModal" tabindex="-1" aria-labelledby="violationLocationMapModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="violationLocationMapModalLabel">
                    <i class="bi bi-geo-alt me-2"></i>{{ __('messages.select_location_on_map') }}
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div id="violation-location-map" style="width: 100%; height: 400px; border-radius: 0.5rem; border: 1px solid #dee2e6;"></div>
                <small class="text-muted d-block mt-2">
                    {{ __('messages.location_map_help') }}
                </small>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">{{ __('messages.close') }}</button>
            </div>
        </div>
    </div>
</div>

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
    const driverSelect = document.getElementById('driver_id');
    const vehicleSelect = document.getElementById('vehicle_id');

    // Update vehicle based on driver selection
    if (driverSelect && vehicleSelect) {
        driverSelect.addEventListener('change', function() {
            const selectedOption = driverSelect.options[driverSelect.selectedIndex];
            const assignedVehicleId = selectedOption ? selectedOption.getAttribute('data-vehicle') : '';
            if (assignedVehicleId && !vehicleSelect.value) {
                vehicleSelect.value = assignedVehicleId;
            }
        });
    }

    // Map handling for location (same as create)
    let violationMap = null;
    let violationMarker = null;

    function initViolationMap() {
        if (!window.L) {
            return;
        }

        const mapContainer = document.getElementById('violation-location-map');
        if (!mapContainer) return;

        if (!violationMap) {
            const latInput = document.getElementById('location_lat');
            const lngInput = document.getElementById('location_lng');
            const coordsLabel = document.getElementById('location-coordinates-label');

            // Default center (Morocco)
            let initialLat = 33.5731;
            let initialLng = -7.5898;
            let initialZoom = 7;

            // If we already have stored coordinates, use them and zoom in
            if (latInput && lngInput && latInput.value && lngInput.value) {
                const storedLat = parseFloat(latInput.value);
                const storedLng = parseFloat(lngInput.value);
                if (!isNaN(storedLat) && !isNaN(storedLng)) {
                    initialLat = storedLat;
                    initialLng = storedLng;
                    initialZoom = 13;
                }
            }

            violationMap = L.map(mapContainer).setView([initialLat, initialLng], initialZoom);

            L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
                maxZoom: 19,
                attribution: '&copy; OpenStreetMap contributors',
            }).addTo(violationMap);


            function updateMarkerAndInputs(lat, lng) {
                if (violationMarker) {
                    violationMarker.setLatLng([lat, lng]);
                } else {
                    violationMarker = L.marker([lat, lng]).addTo(violationMap);
                }

                if (latInput) latInput.value = lat.toFixed(6);
                if (lngInput) lngInput.value = lng.toFixed(6);
                if (coordsLabel) {
                    const label = @json(__('messages.location_coords_label') ?? 'Coordinates');
                    coordsLabel.innerHTML = label + ': ' +
                        '<span class="fw-semibold">' + lat.toFixed(6) + ', ' + lng.toFixed(6) + '</span>';
                }
            }

            // If coordinates were already present, drop a marker there
            if (latInput && lngInput && latInput.value && lngInput.value) {
                const lat = parseFloat(latInput.value);
                const lng = parseFloat(lngInput.value);
                if (!isNaN(lat) && !isNaN(lng)) {
                    updateMarkerAndInputs(lat, lng);
                }
            }
            violationMap.on('click', function (e) {
                updateMarkerAndInputs(e.latlng.lat, e.latlng.lng);
            });
        } else {
            violationMap.invalidateSize();
        }
    }

    const mapModal = document.getElementById('violationLocationMapModal');
    if (mapModal) {
        mapModal.addEventListener('shown.bs.modal', function () {
            initViolationMap();
            setTimeout(function () {
                if (violationMap) {
                    violationMap.invalidateSize();
                }
            }, 150);
        });
    }
});
</script>

