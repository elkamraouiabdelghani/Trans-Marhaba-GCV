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
            <div class="col-lg-10">
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
                                    <label for="flotte_id" class="form-label fw-semibold">{{ __('messages.flotte') }}</label>
                                    <select
                                        id="flotte_id"
                                        name="flotte_id"
                                        class="form-select @error('flotte_id') is-invalid @enderror"
                                    >
                                        <option value="">{{ __('messages.all_flottes') }}</option>
                                        @foreach($flottes as $flotte)
                                            <option
                                                value="{{ $flotte->id }}"
                                                @selected((int) old('flotte_id', $violation->flotte_id) === (int) $flotte->id)
                                            >
                                                {{ $flotte->name }}
                                            </option>
                                        @endforeach
                                    </select>
                                    @error('flotte_id')
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
                                    <label for="driver_id" class="form-label fw-semibold">{{ __('messages.driver') }} <span class="text-danger">*</span></label>
                                    <div class="position-relative">
                                        <!-- Hidden select for form submission -->
                                        <select
                                            id="driver_id"
                                            name="driver_id"
                                            class="d-none"
                                            required
                                        >
                                            <option value="">{{ __('messages.select_driver') }}</option>
                                            @foreach($drivers as $driver)
                                                <option
                                                    value="{{ $driver->id }}"
                                                    data-vehicle="{{ $driver->assignedVehicle?->id ?? '' }}"
                                                    data-flotte-id="{{ $driver->flotte_id ?? '' }}"
                                                    @selected((int) old('driver_id', $violation->driver_id) === (int) $driver->id)
                                                >
                                                    {{ $driver->full_name }}
                                                </option>
                                            @endforeach
                                        </select>
                                        
                                        <!-- Custom dropdown -->
                                        <div class="custom-driver-dropdown position-relative">
                                            <button
                                                type="button"
                                                id="driver_dropdown_toggle"
                                                class="form-select text-start @error('driver_id') is-invalid @enderror"
                                                data-bs-toggle="dropdown"
                                                aria-expanded="false"
                                            >
                                                <span id="driver_dropdown_display">
                                                    @php
                                                        $selectedDriver = $drivers->firstWhere('id', old('driver_id', $violation->driver_id));
                                                    @endphp
                                                    {{ $selectedDriver ? $selectedDriver->full_name : __('messages.select_driver') }}
                                                </span>
                                            </button>
                                            <ul class="dropdown-menu w-100 p-0" id="driver_dropdown_menu" style="max-height: 300px; overflow-y: auto;">
                                                <li class="p-2 border-bottom">
                                                    <div class="input-group input-group-sm">
                                                        <span class="input-group-text bg-light">
                                                            <i class="bi bi-search"></i>
                                                        </span>
                                                        <input
                                                            type="text"
                                                            id="driver_search"
                                                            class="form-control"
                                                            placeholder="{{ __('messages.search_driver') ?? 'Search driver...' }}"
                                                            autocomplete="off"
                                                        >
                                                    </div>
                                                </li>
                                                <li>
                                                    <div id="driver_dropdown_options" class="list-group list-group-flush">
                                                        <!-- Options will be populated by JavaScript -->
                                                    </div>
                                                </li>
                                            </ul>
                                        </div>
                                    </div>
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
                                        type="text"
                                        id="violation_time"
                                        name="violation_time"
                                        class="form-control @error('violation_time') is-invalid @enderror"
                                        value="{{ old('violation_time', $violation->violation_time ? $violation->violation_time->format('H:i:s') : null) }}"
                                        placeholder="17:30:00"
                                        pattern="^([0-1][0-9]|2[0-3]):[0-5][0-9]:[0-5][0-9]$"
                                        maxlength="8"
                                    >
                                    <small class="text-muted">{{ __('messages.time_format_hint') ?? 'Format: HH:MM:SS (24-hour format, e.g., 17:30:00)' }}</small>
                                    @error('violation_time')
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

<!-- Custom Driver Dropdown Styles -->
<style>
    .custom-driver-dropdown .dropdown-menu {
        min-width: 100%;
    }
    
    .custom-driver-dropdown .list-group-item {
        cursor: pointer;
        border-left: none;
        border-right: none;
    }
    
    .custom-driver-dropdown .list-group-item:hover {
        background-color: #f8f9fa;
    }
    
    .custom-driver-dropdown .list-group-item.active {
        background-color: #0d6efd;
        color: white;
    }
    
    .custom-driver-dropdown .list-group-item:first-child {
        border-top: none;
    }
    
    .custom-driver-dropdown .list-group-item:last-child {
        border-bottom: none;
    }
    
    .custom-driver-dropdown #driver_search {
        border: none;
        box-shadow: none;
    }
    
    .custom-driver-dropdown #driver_search:focus {
        border: none;
        box-shadow: none;
    }
</style>

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
    const flotteSelect = document.getElementById('flotte_id');
    const driverDropdownToggle = document.getElementById('driver_dropdown_toggle');
    const driverDropdownDisplay = document.getElementById('driver_dropdown_display');
    const driverDropdownOptions = document.getElementById('driver_dropdown_options');
    const driverSearchInput = document.getElementById('driver_search');
    const driverDropdownMenu = document.getElementById('driver_dropdown_menu');

    // Store all driver options for filtering
    let allDriverOptions = [];
    if (driverSelect) {
        for (let i = 0; i < driverSelect.options.length; i++) {
            const option = driverSelect.options[i];
            if (option.value) { // Skip placeholder
                allDriverOptions.push({
                    value: option.value,
                    text: option.text,
                    flotteId: option.getAttribute('data-flotte-id') || '',
                    vehicleId: option.getAttribute('data-vehicle') || ''
                });
            }
        }
    }

    let currentSearchTerm = '';
    let selectedDriverValue = driverSelect ? driverSelect.value : '';

    // Function to render driver options in dropdown
    function renderDriverOptions(selectedFlotteId, searchTerm) {
        if (!driverDropdownOptions) return;

        // Normalize search term for case-insensitive matching
        const normalizedSearch = (searchTerm || '').toLowerCase().trim();

        // Clear current options
        driverDropdownOptions.innerHTML = '';

        // Filter and render options
        let hasOptions = false;
        allDriverOptions.forEach(function(option) {
            // Check fleet filter
            const matchesFleet = !selectedFlotteId || selectedFlotteId === '' || option.flotteId === selectedFlotteId;
            
            // Check search filter
            const matchesSearch = !normalizedSearch || option.text.toLowerCase().includes(normalizedSearch);

            // If both filters pass, add the option
            if (matchesFleet && matchesSearch) {
                const listItem = document.createElement('a');
                listItem.href = '#';
                listItem.className = 'list-group-item list-group-item-action' + (option.value === selectedDriverValue ? ' active' : '');
                listItem.setAttribute('data-value', option.value);
                listItem.setAttribute('data-vehicle', option.vehicleId);
                listItem.setAttribute('data-flotte-id', option.flotteId);
                listItem.textContent = option.text;
                
                listItem.addEventListener('click', function(e) {
                    e.preventDefault();
                    selectDriver(option.value, option.text, option.vehicleId, option.flotteId);
                    
                    // Close dropdown
                    const dropdown = bootstrap.Dropdown.getInstance(driverDropdownToggle);
                    if (dropdown) {
                        dropdown.hide();
                    }
                });
                
                driverDropdownOptions.appendChild(listItem);
                hasOptions = true;
            }
        });

        // Show message if no options
        if (!hasOptions) {
            const noResults = document.createElement('div');
            noResults.className = 'list-group-item text-muted text-center';
            noResults.textContent = '{{ __('messages.no_drivers_found') ?? 'No drivers found' }}';
            driverDropdownOptions.appendChild(noResults);
        }
    }

    // Function to select a driver
    function selectDriver(value, text, vehicleId, flotteId) {
        selectedDriverValue = value;
        
        // Update hidden select
        if (driverSelect) {
            driverSelect.value = value;
            // Trigger change event
            driverSelect.dispatchEvent(new Event('change', { bubbles: true }));
        }
        
        // Update display
        if (driverDropdownDisplay) {
            driverDropdownDisplay.textContent = text || '{{ __('messages.select_driver') }}';
        }
        
        // Update vehicle
        if (vehicleSelect && vehicleId) {
            vehicleSelect.value = vehicleId;
        }
        
        // Auto-select flotte based on selected driver
        if (flotteSelect && flotteId) {
            for (let i = 0; i < flotteSelect.options.length; i++) {
                if (flotteSelect.options[i].value === flotteId) {
                    flotteSelect.value = flotteId;
                    // Re-render drivers after fleet change
                    renderDriverOptions(flotteId, currentSearchTerm);
                    break;
                }
            }
        }
        
        // Clear search
        if (driverSearchInput) {
            driverSearchInput.value = '';
            currentSearchTerm = '';
        }
    }

    // Initialize display with current selection
    if (driverSelect && driverSelect.value && driverDropdownDisplay) {
        const selectedOption = driverSelect.options[driverSelect.selectedIndex];
        if (selectedOption) {
            driverDropdownDisplay.textContent = selectedOption.text;
            selectedDriverValue = driverSelect.value;
        }
    }

    // Filter drivers when fleet selection changes
    if (flotteSelect) {
        flotteSelect.addEventListener('change', function() {
            const selectedFlotteId = this.value;
            renderDriverOptions(selectedFlotteId, currentSearchTerm);
        });
    }

    // Filter drivers when search term changes
    if (driverSearchInput) {
        driverSearchInput.addEventListener('input', function() {
            currentSearchTerm = this.value;
            const selectedFlotteId = flotteSelect ? flotteSelect.value : '';
            renderDriverOptions(selectedFlotteId, currentSearchTerm);
        });
        
        // Prevent dropdown from closing when clicking inside
        if (driverDropdownMenu) {
            driverDropdownMenu.addEventListener('click', function(e) {
                e.stopPropagation();
            });
        }
        
        // Focus search when dropdown opens
        if (driverDropdownToggle) {
            driverDropdownToggle.addEventListener('shown.bs.dropdown', function() {
                setTimeout(function() {
                    if (driverSearchInput) {
                        driverSearchInput.focus();
                    }
                }, 100);
            });
        }
    }

    // Initial render
    const initialFlotteId = flotteSelect ? flotteSelect.value : '';
    renderDriverOptions(initialFlotteId, currentSearchTerm);

    // Format violation time input (HH:MM:SS) - text input with auto-formatting
    const violationTimeInput = document.getElementById('violation_time');
    if (violationTimeInput) {
        // Auto-format on blur to ensure proper format
        violationTimeInput.addEventListener('blur', function() {
            if (!this.value) return;
            
            // Remove any non-digit characters except colons
            let cleaned = this.value.replace(/[^\d:]/g, '');
            
            // Parse the time value
            const timeMatch = cleaned.match(/(\d{1,2}):?(\d{0,2}):?(\d{0,2})/);
            if (timeMatch) {
                let hours = parseInt(timeMatch[1] || '0', 10);
                let minutes = parseInt(timeMatch[2] || '0', 10);
                let seconds = parseInt(timeMatch[3] || '0', 10);
                
                // Validate and clamp values
                if (hours < 0) hours = 0;
                if (hours > 23) hours = 23;
                if (minutes < 0) minutes = 0;
                if (minutes > 59) minutes = 59;
                if (seconds < 0) seconds = 0;
                if (seconds > 59) seconds = 59;
                
                // Format as HH:MM:SS
                const formattedTime = String(hours).padStart(2, '0') + ':' + 
                                    String(minutes).padStart(2, '0') + ':' + 
                                    String(seconds).padStart(2, '0');
                
                this.value = formattedTime;
            }
        });
        
        // Auto-insert colons while typing
        violationTimeInput.addEventListener('input', function(e) {
            let value = this.value.replace(/[^\d]/g, ''); // Remove non-digits
            
            // Auto-format as user types (HH:MM:SS)
            if (value.length > 0) {
                let formatted = value;
                if (value.length > 2) {
                    formatted = value.substring(0, 2) + ':' + value.substring(2);
                }
                if (value.length > 4) {
                    formatted = value.substring(0, 2) + ':' + value.substring(2, 4) + ':' + value.substring(4, 6);
                }
                // Only update if the formatted value is different to avoid cursor jumping
                if (formatted !== this.value && formatted.length <= 8) {
                    const cursorPos = this.selectionStart;
                    this.value = formatted;
                    // Try to maintain cursor position
                    this.setSelectionRange(Math.min(cursorPos + (formatted.length - value.length), formatted.length), Math.min(cursorPos + (formatted.length - value.length), formatted.length));
                }
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
            const locationInput = document.getElementById('location');
            const coordsLabel = document.getElementById('location-coordinates-label');

            // Reverse geocoding function to get location name from coordinates
            async function reverseGeocode(lat, lng) {
                try {
                    const url = `https://nominatim.openstreetmap.org/reverse?format=json&lat=${lat}&lon=${lng}&zoom=18&addressdetails=1`;
                    const response = await fetch(url, {
                        headers: {
                            'Accept': 'application/json',
                            'User-Agent': 'GCV Violation System'
                        }
                    });
                    
                    if (!response.ok) {
                        throw new Error('Reverse geocoding request failed');
                    }
                    
                    const data = await response.json();
                    
                    if (data && data.address) {
                        // Try to get a meaningful location name
                        const address = data.address;
                        let locationName = '';
                        
                        // Priority: city/town/village > municipality > county > state
                        if (address.city) {
                            locationName = address.city;
                        } else if (address.town) {
                            locationName = address.town;
                        } else if (address.village) {
                            locationName = address.village;
                        } else if (address.municipality) {
                            locationName = address.municipality;
                        } else if (address.county) {
                            locationName = address.county;
                        } else if (address.state) {
                            locationName = address.state;
                        }
                        
                        // If we have a road/street, add it
                        if (address.road && locationName) {
                            locationName = address.road + ', ' + locationName;
                        } else if (address.road && !locationName) {
                            locationName = address.road;
                        }
                        
                        // Fallback to display_name if nothing found
                        if (!locationName && data.display_name) {
                            const parts = data.display_name.split(',');
                            locationName = parts[0].trim();
                        }
                        
                        return locationName || '';
                    }
                    
                    return '';
                } catch (error) {
                    console.error('Reverse geocoding error:', error);
                    return '';
                }
            }

            async function updateMarkerAndInputs(lat, lng) {
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
                
                // Reverse geocode to get location name and fill the location input
                if (locationInput) {
                    // Show loading state
                    const originalValue = locationInput.value;
                    locationInput.value = '{{ __('messages.loading_location') ?? 'Loading location...' }}';
                    locationInput.disabled = true;
                    
                    try {
                        const locationName = await reverseGeocode(lat, lng);
                        if (locationName) {
                            locationInput.value = locationName;
                        } else {
                            // If reverse geocoding fails, keep original value or use coordinates
                            locationInput.value = originalValue || `${lat.toFixed(6)}, ${lng.toFixed(6)}`;
                        }
                    } catch (error) {
                        // On error, keep original value or use coordinates
                        locationInput.value = originalValue || `${lat.toFixed(6)}, ${lng.toFixed(6)}`;
                    } finally {
                        locationInput.disabled = false;
                    }
                }
            }

            violationMap = L.map(mapContainer).setView([33.5731, -7.5898], 7); // Default center: Morocco

            L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
                maxZoom: 19,
                attribution: '&copy; OpenStreetMap contributors',
            }).addTo(violationMap);

            // Initialize from existing values if any
            if (latInput && lngInput && latInput.value && lngInput.value) {
                const lat = parseFloat(latInput.value);
                const lng = parseFloat(lngInput.value);
                if (!isNaN(lat) && !isNaN(lng)) {
                    updateMarkerAndInputs(lat, lng);
                    violationMap.setView([lat, lng], 13);
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

