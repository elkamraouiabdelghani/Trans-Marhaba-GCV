<x-app-layout>
    <x-slot name="header">
        @include('layouts.topnav')
    </x-slot>

    <div class="container-fluid py-4 mt-4">
        <div class="d-flex justify-content-between align-items-center mb-3 bg-white p-4 rounded-3 shadow-sm">
            <div>
                <h2 class="mb-1 fw-bold text-dark fs-4">
                    <i class="bi bi-plus-circle me-2 text-primary"></i>
                    {{ __('messages.add_rest_point') ?? 'Add Rest Point' }}
                </h2>
            </div>
            <div>
                <a href="{{ $backUrl ?? route('rest-points.index') }}" class="btn btn-sm btn-outline-secondary">
                    <i class="bi bi-arrow-left"></i>
                    {{ __('messages.back_to_list') ?? 'Back to list' }}
                </a>
            </div>
        </div>
        
        @if(session('success'))
            <div class="alert alert-success">
                {{ session('success') }}
            </div>
        @endif
        @if(session('error'))
            <div class="alert alert-danger">
                {{ session('error') }}
            </div>
        @endif

        <div class="row justify-content-center">
            <div class="col-lg-10 col-xl-10">
                <div class="card border-0 shadow-sm">
                    <form action="{{ route('rest-points.store') }}" method="POST" id="createRestPointForm" enctype="multipart/form-data">
                        @csrf
                        <div class="card-body p-4">
                            @if($errors->any())
                                <div class="alert alert-danger">
                                    <ul class="mb-0">
                                        @foreach($errors->all() as $error)
                                            <li>{{ $error }}</li>
                                        @endforeach
                                    </ul>
                                </div>
                            @endif

                            <div class="row g-3">
                                <div class="col-md-6">
                                    <label for="create_name" class="form-label fw-semibold">
                                        {{ __('messages.name') ?? 'Name' }} <span class="text-danger">*</span>
                                    </label>
                                    <input type="text" class="form-control @error('name') is-invalid @enderror"
                                           id="create_name" name="name" value="{{ old('name') }}" required>
                                    @error('name')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>

                                <div class="col-md-6">
                                    <label for="create_type" class="form-label fw-semibold">
                                        {{ __('messages.type') ?? 'Type' }} <span class="text-danger">*</span>
                                    </label>
                                    <select class="form-select @error('type') is-invalid @enderror"
                                            id="create_type" name="type" required>
                                        <option value="">{{ __('messages.select_type') ?? 'Select Type' }}</option>
                                        @foreach($types as $key => $label)
                                            <option value="{{ $key }}" @selected(old('type') === $key)>{{ $label }}</option>
                                        @endforeach
                                    </select>
                                    @error('type')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>

                                <div class="col-12">
                                    <label class="form-label fw-semibold">
                                        {{ __('messages.location') ?? 'Location' }} <span class="text-danger">*</span>
                                    </label>
                                    <div class="d-flex justify-content-between align-items-center mb-2">
                                        <div>
                                            <input type="hidden" name="latitude" id="create_latitude" value="{{ old('latitude') }}" required>
                                            <input type="hidden" name="longitude" id="create_longitude" value="{{ old('longitude') }}" required>
                                            <small class="text-muted d-block" id="create-location-coordinates-label">
                                                @if(old('latitude') && old('longitude'))
                                                    {{ __('messages.location_coords_label') ?? 'Coordinates' }}:
                                                    <span class="fw-semibold">{{ old('latitude') }}, {{ old('longitude') }}</span>
                                                @else
                                                    {{ __('messages.location_map_help') ?? 'Use the button below to select location on map.' }}
                                                @endif
                                            </small>
                                        </div>
                                        <button type="button" 
                                                class="btn btn-sm btn-primary" 
                                                id="selectLocationBtn"
                                                data-bs-toggle="modal"
                                                data-bs-target="#selectLocationModal">
                                            <i class="bi bi-geo-alt me-1"></i>
                                            {{ __('messages.select_on_map') ?? 'Select on Map' }}
                                        </button>
                                    </div>
                                    @error('latitude')
                                        <div class="text-danger small mt-1">{{ $message }}</div>
                                    @enderror
                                    @error('longitude')
                                        <div class="text-danger small mt-1">{{ $message }}</div>
                                    @enderror
                                </div>

                                <div class="col-12">
                                    <label for="create_description" class="form-label fw-semibold">
                                        {{ __('messages.description') ?? 'Description' }}
                                    </label>
                                    <textarea class="form-control @error('description') is-invalid @enderror"
                                              id="create_description" name="description" rows="3">{{ old('description') }}</textarea>
                                    @error('description')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>

                            {{-- Checklist Section --}}
                            @if(isset($categories) && $categories->count() > 0)
                                <hr class="my-4">
                                <div class="row">
                                    <div class="col-12">
                                        <h5 class="fw-bold mb-3">
                                            <i class="bi bi-list-check me-2 text-primary"></i>
                                            {{ __('messages.checklist') ?? 'Checklist' }}
                                        </h5>
                                        <p class="text-muted mb-4">
                                            {{ __('messages.checklist_optional_help') ?? 'Checklist is optional. Fill only the items you want for this rest point.' }}
                                        </p>

                                        <div class="accordion" id="checklistAccordion">
                                            @foreach($categories as $index => $category)
                                                <div class="accordion-item mb-2 border rounded">
                                                    <h2 class="accordion-header" id="heading{{ $category->id }}">
                                                        <button class="accordion-button {{ $index === 0 ? '' : 'collapsed' }}" 
                                                                type="button" 
                                                                data-bs-toggle="collapse" 
                                                                data-bs-target="#collapse{{ $category->id }}" 
                                                                aria-expanded="{{ $index === 0 ? 'true' : 'false' }}" 
                                                                aria-controls="collapse{{ $category->id }}">
                                                            <i class="bi bi-list-check me-2 text-primary"></i>
                                                            <strong>{{ $category->name }}</strong>
                                                            <span class="badge bg-info bg-opacity-10 text-info ms-2">
                                                                {{ $category->items->count() }} {{ __('messages.items') ?? 'items' }}
                                                            </span>
                                                        </button>
                                                    </h2>
                                                    <div id="collapse{{ $category->id }}" 
                                                         class="accordion-collapse collapse {{ $index === 0 ? 'show' : '' }}" 
                                                         aria-labelledby="heading{{ $category->id }}" 
                                                         data-bs-parent="#checklistAccordion">
                                                        <div class="accordion-body p-4">
                                                            @if($category->items->count() > 0)
                                                                <div class="table-responsive">
                                                                    <table class="table table-bordered table-hover">
                                                                        <thead class="table-light">
                                                                            <tr>
                                                                                <th style="width: 50%;">{{ __('messages.item') ?? 'Item' }}</th>
                                                                                <th style="width: 25%;" class="text-center">{{ __('messages.yes') ?? 'Yes' }} / {{ __('messages.no') ?? 'No' }}</th>
                                                                                <th style="width: 25%;">{{ __('messages.comment') ?? 'Comment' }}</th>
                                                                            </tr>
                                                                        </thead>
                                                                        <tbody>
                                                                            @foreach($category->items as $item)
                                                                                <tr>
                                                                                    <td class="align-middle">
                                                                                        <label class="mb-0 fw-semibold">{{ $item->label }}</label>
                                                                                    </td>
                                                                                    <td class="text-center align-middle">
                                                                                        <div class="btn-group" role="group" data-toggle="buttons">
                                                                                            <input type="radio" 
                                                                                                   class="btn-check" 
                                                                                                   name="checklist[{{ $item->id }}][is_checked]" 
                                                                                                   id="item_{{ $item->id }}_yes" 
                                                                                                   value="1" 
                                                                                                   {{ old("checklist.{$item->id}.is_checked") === '1' ? 'checked' : '' }}>
                                                                                            <label class="btn btn-outline-success btn-sm" for="item_{{ $item->id }}_yes">
                                                                                                <i class="bi bi-check-circle"></i> {{ __('messages.yes') ?? 'Yes' }}
                                                                                            </label>

                                                                                            <input type="radio" 
                                                                                                   class="btn-check" 
                                                                                                   id="item_{{ $item->id }}_no" 
                                                                                                   name="checklist[{{ $item->id }}][is_checked]" 
                                                                                                   value="0" 
                                                                                                   {{ old("checklist.{$item->id}.is_checked") === '0' ? 'checked' : '' }}>
                                                                                            <label class="btn btn-outline-danger btn-sm" for="item_{{ $item->id }}_no">
                                                                                                <i class="bi bi-x-circle"></i> {{ __('messages.no') ?? 'No' }}
                                                                                            </label>
                                                                                        </div>
                                                                                        @error("checklist.{$item->id}.is_checked")
                                                                                            <div class="text-danger small mt-1">{{ $message }}</div>
                                                                                        @enderror
                                                                                    </td>
                                                                                    <td class="align-middle">
                                                                                        <textarea class="form-control form-control-sm" 
                                                                                                  name="checklist[{{ $item->id }}][comment]" 
                                                                                                  id="item_{{ $item->id }}_comment" 
                                                                                                  rows="2" 
                                                                                                  placeholder="{{ __('messages.comment_optional') ?? 'Comment (optional)' }}">{{ old("checklist.{$item->id}.comment") }}</textarea>
                                                                                        @error("checklist.{$item->id}.comment")
                                                                                            <div class="text-danger small mt-1">{{ $message }}</div>
                                                                                        @enderror
                                                                                    </td>
                                                                                </tr>
                                                                            @endforeach
                                                                        </tbody>
                                                                    </table>
                                                                </div>
                                                            @else
                                                                <div class="alert alert-info mb-0">
                                                                    <i class="bi bi-info-circle me-2"></i>
                                                                    {{ __('messages.no_items_in_category') ?? 'No active items in this category.' }}
                                                                </div>
                                                            @endif
                                                        </div>
                                                    </div>
                                                </div>
                                            @endforeach
                                        </div>
                                    </div>
                                </div>
                            @else
                                <hr class="my-4">
                                <div class="alert alert-warning">
                                    <i class="bi bi-exclamation-triangle me-2"></i>
                                    {{ __('messages.no_checklist_categories') ?? 'No active checklist categories found. Please create categories first.' }}
                                </div>
                            @endif

                            {{-- Checklist Status and General Comment --}}
                            @if(isset($categories) && $categories->count() > 0)
                                <hr class="my-4">
                                <div class="row">
                                    <div class="col-12">
                                        <div class="row g-3 mb-3">
                                            <div class="col-12">
                                                <label for="checklist_notes" class="form-label fw-semibold">
                                                    {{ __('messages.general_comment') ?? 'General Comment' }}
                                                </label>
                                                <textarea class="form-control @error('checklist_notes') is-invalid @enderror"
                                                          id="checklist_notes" 
                                                          name="checklist_notes" 
                                                          rows="4" 
                                                          placeholder="{{ __('messages.general_comment_placeholder') ?? 'Enter any general comments or notes about this checklist...' }}">{{ old('checklist_notes') }}</textarea>
                                                @error('checklist_notes')
                                                    <div class="invalid-feedback">{{ $message }}</div>
                                                @enderror
                                            </div>
                                        </div>
                                        
                                        <div class="row g-3">
                                            <div class="col-md-6">
                                                <label class="form-label fw-semibold">
                                                    {{ __('messages.status') ?? 'Status' }} <span class="text-danger">*</span>
                                                </label>
                                                <div class="btn-group w-100" role="group">
                                                    <input type="radio" 
                                                           class="btn-check" 
                                                           name="checklist_status" 
                                                           id="checklist_status_accepted" 
                                                           value="accepted" 
                                                           required
                                                           {{ old('checklist_status', 'accepted') === 'accepted' ? 'checked' : '' }}>
                                                    <label class="btn btn-outline-success" for="checklist_status_accepted">
                                                        <i class="bi bi-check-circle me-1"></i>
                                                        {{ __('messages.accepted') ?? 'Accepted' }}
                                                    </label>

                                                    <input type="radio" 
                                                           class="btn-check" 
                                                           name="checklist_status" 
                                                           id="checklist_status_rejected" 
                                                           value="rejected" 
                                                           required
                                                           {{ old('checklist_status') === 'rejected' ? 'checked' : '' }}>
                                                    <label class="btn btn-outline-danger" for="checklist_status_rejected">
                                                        <i class="bi bi-x-circle me-1"></i>
                                                        {{ __('messages.rejected') ?? 'Rejected' }}
                                                    </label>
                                                </div>
                                                @error('checklist_status')
                                                    <div class="text-danger small mt-1">{{ $message }}</div>
                                                @enderror
                                            </div>
                                        </div>

                                        {{-- Documents/Pictures Upload --}}
                                        <div class="row g-3 mt-2">
                                            <div class="col-12">
                                                <label for="checklist_documents" class="form-label fw-semibold">
                                                    <i class="bi bi-images me-2"></i>
                                                    {{ __('messages.pictures') ?? 'Pictures' }}
                                                </label>
                                                <input type="file" 
                                                       class="form-control @error('checklist_documents') is-invalid @enderror" 
                                                       id="checklist_documents" 
                                                       name="checklist_documents[]" 
                                                       multiple 
                                                       accept="image/*">
                                                <small class="text-muted">
                                                    {{ __('messages.pictures_help') ?? 'You can select multiple pictures. Accepted formats: JPG, PNG, GIF.' }}
                                                </small>
                                                @error('checklist_documents')
                                                    <div class="invalid-feedback">{{ $message }}</div>
                                                @enderror
                                                @error('checklist_documents.*')
                                                    <div class="text-danger small mt-1">{{ $message }}</div>
                                                @enderror
                                                
                                                {{-- Preview area for selected images --}}
                                                <div id="checklist_documents_preview" class="mt-3 row g-2"></div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            @endif
                        </div>
                        <hr class="my-4">
                        <div class="card-footer bg-white border-0 d-flex justify-content-end gap-2 pb-3 px-4">
                            <a href="{{ $backUrl ?? route('rest-points.index') }}" class="btn btn-outline-secondary">
                                {{ __('messages.cancel') ?? 'Cancel' }}
                            </a>
                            <button type="submit" class="btn btn-dark">
                                <i class="bi bi-save me-1"></i>
                                {{ __('messages.create') ?? 'Create' }}
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <!-- Select Location Modal -->
    <div class="modal fade" id="selectLocationModal" tabindex="-1" aria-labelledby="selectLocationModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="selectLocationModalLabel">
                        {{ __('messages.select_location') ?? 'Select Location' }}
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <!-- Mode selector inside modal -->
                    <div class="mb-3">
                        <div class="btn-group btn-group-sm mb-2" role="group" aria-label="Location input mode">
                            <button type="button" class="btn btn-outline-secondary active" data-location-mode="address">
                                {{ __('messages.address') ?? 'Address' }}
                            </button>
                            <button type="button" class="btn btn-outline-secondary" data-location-mode="place">
                                {{ __('messages.location') ?? 'Location' }}
                            </button>
                            <button type="button" class="btn btn-outline-secondary" data-location-mode="latlng">
                                {{ __('messages.coordinates') ?? 'Lat / Lng' }}
                            </button>
                        </div>

                        <!-- Address mode -->
                        <div class="mb-2 location-mode-block" id="address-block">
                            <label for="address_input" class="form-label small mb-1">
                                {{ __('messages.address') ?? 'Address' }}
                            </label>
                            <div class="input-group input-group-sm">
                                <input type="text"
                                       id="address_input"
                                       class="form-control"
                                       placeholder="Ex: Boulevard Mohammed V, Casablanca">
                                <button class="btn btn-outline-primary" type="button" id="address_search">
                                    <i class="bi bi-search"></i>
                                </button>
                            </div>
                            <small class="text-muted">
                                {{ __('messages.location_help') ?? 'Type an address then search, or click on the map.' }}
                            </small>
                            <div class="text-danger small mt-1 d-none" id="address_error"></div>
                        </div>

                        <!-- Place / free-text location mode -->
                        <div class="mb-2 location-mode-block d-none" id="place-block">
                            <label for="place_input" class="form-label small mb-1">
                                {{ __('messages.location') ?? 'Location' }}
                            </label>
                            <div class="input-group input-group-sm">
                                <input type="text"
                                       id="place_input"
                                       class="form-control"
                                       placeholder="Ex: Rest area near Rabat, highway km 22">
                                <button class="btn btn-outline-primary" type="button" id="place_search">
                                    <i class="bi bi-search"></i>
                                </button>
                            </div>
                            <small class="text-muted">
                                {{ __('messages.location_help') ?? 'Type a location name then search, or click on the map.' }}
                            </small>
                            <div class="text-danger small mt-1 d-none" id="place_error"></div>
                        </div>

                        <!-- Lat/Lng mode -->
                        <div class="mb-2 location-mode-block d-none" id="latlng-block">
                            <label class="form-label small mb-1">
                                {{ __('messages.coordinates') ?? 'Coordinates' }}
                            </label>
                            <div class="row g-2">
                                <div class="col-6">
                                    <input type="number"
                                           step="0.000001"
                                           class="form-control form-control-sm"
                                           id="lat_input"
                                           placeholder="Lat (e.g. 33.573100)">
                                </div>
                                <div class="col-6">
                                    <input type="number"
                                           step="0.000001"
                                           class="form-control form-control-sm"
                                           id="lng_input"
                                           placeholder="Lng (e.g. -7.589800)">
                                </div>
                            </div>
                            <div class="mt-1">
                                <button type="button" class="btn btn-outline-primary btn-sm" id="latlng_apply">
                                    {{ __('messages.apply') ?? 'Apply' }}
                                </button>
                            </div>
                            <small class="text-muted d-block">
                                {{ __('messages.location_coords_hint') ?? 'Latitude between -90 and 90, longitude between -180 and 180.' }}
                            </small>
                            <div class="text-danger small mt-1 d-none" id="latlng_error"></div>
                        </div>
                    </div>
                    
                    <div class="mt-3">
                        <small class="text-muted" id="location-coordinates-label">
                            {{ __('messages.location_map_help') ?? 'Click on the map to set the coordinates.' }}
                        </small>
                    </div>

                    <div id="location-map" style="width: 100%; height: 400px; border-radius: 0.5rem;"></div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                        {{ __('messages.cancel') ?? 'Cancel' }}
                    </button>
                    <button type="button" class="btn btn-primary" id="confirmLocation" data-bs-dismiss="modal">
                        {{ __('messages.confirm') ?? 'Confirm' }}
                    </button>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>

<!-- Leaflet CSS and JS -->
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
        console.error('Leaflet library not loaded');
        return;
    }

    // Geocoding function using Nominatim (OpenStreetMap)
    async function geocodeQuery(query) {
        const url = `https://nominatim.openstreetmap.org/search?format=json&q=${encodeURIComponent(query)}&limit=1`;
        const response = await fetch(url, {
            headers: {
                'User-Agent': 'GCV Application'
            }
        });
        if (!response.ok) {
            throw new Error('Geocoding service unavailable');
        }
        const data = await response.json();
        if (!data || data.length === 0) {
            throw new Error('Location not found');
        }
        return {
            lat: parseFloat(data[0].lat),
            lng: parseFloat(data[0].lon)
        };
    }

    // Modal map and marker
    let locationMap = null;
    let locationMarker = null;
    const latInput = document.getElementById('create_latitude');
    const lngInput = document.getElementById('create_longitude');
    const coordsLabel = document.getElementById('create-location-coordinates-label');

    // Initialize modal map when modal is shown
    const selectLocationModal = document.getElementById('selectLocationModal');
    if (selectLocationModal) {
        selectLocationModal.addEventListener('shown.bs.modal', function () {
            if (!locationMap) {
                const mapContainer = document.getElementById('location-map');
                if (!mapContainer) return;

                let initialLat = 33.5731;
                let initialLng = -7.5898;
                let initialZoom = 7;

                if (latInput && lngInput && latInput.value && lngInput.value) {
                    initialLat = parseFloat(latInput.value);
                    initialLng = parseFloat(lngInput.value);
                    initialZoom = 13;
                }

                locationMap = L.map(mapContainer).setView([initialLat, initialLng], initialZoom);
                L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
                    maxZoom: 19,
                    attribution: '&copy; OpenStreetMap contributors',
                }).addTo(locationMap);

                if (latInput && lngInput && latInput.value && lngInput.value) {
                    const lat = parseFloat(latInput.value);
                    const lng = parseFloat(lngInput.value);
                    locationMarker = L.marker([lat, lng]).addTo(locationMap);
                }

                locationMap.on('click', function (e) {
                    setLocationCoordinates(e.latlng.lat, e.latlng.lng);
                });
            } else {
                // Update map view if coordinates exist
                if (latInput && lngInput && latInput.value && lngInput.value) {
                    const lat = parseFloat(latInput.value);
                    const lng = parseFloat(lngInput.value);
                    locationMap.setView([lat, lng], 13);
                    if (locationMarker) {
                        locationMarker.setLatLng([lat, lng]);
                    } else {
                        locationMarker = L.marker([lat, lng]).addTo(locationMap);
                    }
                }
            }
        });
    }

    // Function to set coordinates
    function setLocationCoordinates(lat, lng) {
        if (locationMarker) {
            locationMarker.setLatLng([lat, lng]);
        } else if (locationMap) {
            locationMarker = L.marker([lat, lng]).addTo(locationMap);
        }

        if (latInput) {
            latInput.value = lat.toFixed(6);
        }
        if (lngInput) {
            lngInput.value = lng.toFixed(6);
        }
        if (coordsLabel) {
            const label = @json(__('messages.location_coords_label') ?? 'Coordinates');
            coordsLabel.innerHTML = label + ': <span class="fw-semibold">' + lat.toFixed(6) + ', ' + lng.toFixed(6) + '</span>';
        }

        // Update label in modal
        const modalLabel = document.getElementById('location-coordinates-label');
        if (modalLabel) {
            const labelText = @json(__('messages.location_coords_label') ?? 'Coordinates');
            modalLabel.innerHTML = labelText + ': <span class="fw-semibold">' + lat.toFixed(6) + ', ' + lng.toFixed(6) + '</span>';
        }
    }

    // Location mode switching
    document.addEventListener('click', function (e) {
        const btn = e.target.closest('button[data-location-mode]');
        if (!btn) return;

        const mode = btn.getAttribute('data-location-mode');
        if (!mode) return;

        const scope = btn.closest('.modal');
        if (!scope) return;

        // Toggle active class
        const group = btn.parentElement;
        if (group) {
            Array.from(group.querySelectorAll('button[data-location-mode]')).forEach(b => b.classList.remove('active'));
            btn.classList.add('active');
        }

        // Hide all blocks, show selected one
        const blocks = scope.querySelectorAll('.location-mode-block');
        blocks.forEach(block => block.classList.add('d-none'));

        const selectedId = mode + '-block';
        const selectedBlock = scope.querySelector('#' + selectedId);
        if (selectedBlock) {
            selectedBlock.classList.remove('d-none');
        }
    });

    // Geocoding handlers
    async function handleGeocodeClick(inputId, errorId) {
        const input = document.getElementById(inputId);
        const errorEl = document.getElementById(errorId);
        if (errorEl) {
            errorEl.classList.add('d-none');
            errorEl.textContent = '';
        }
        if (!input) return;
        const query = input.value.trim();
        if (!query) return;

        try {
            const { lat, lng } = await geocodeQuery(query);
            setLocationCoordinates(lat, lng);
            if (locationMap) {
                locationMap.setView([lat, lng], 13);
            }
        } catch (err) {
            if (errorEl) {
                errorEl.textContent = err.message || 'Unable to find this location.';
                errorEl.classList.remove('d-none');
            }
        }
    }

    document.getElementById('address_search')?.addEventListener('click', function () {
        handleGeocodeClick('address_input', 'address_error');
    });

    document.getElementById('place_search')?.addEventListener('click', function () {
        handleGeocodeClick('place_input', 'place_error');
    });

    // Lat/Lng Apply handler
    function validateLatLng(lat, lng) {
        if (Number.isNaN(lat) || Number.isNaN(lng)) {
            return 'Both latitude and longitude are required.';
        }
        if (lat < -90 || lat > 90) {
            return 'Latitude must be between -90 and 90.';
        }
        if (lng < -180 || lng > 180) {
            return 'Longitude must be between -180 and 180.';
        }
        return null;
    }

    document.getElementById('latlng_apply')?.addEventListener('click', function () {
        const latInputEl = document.getElementById('lat_input');
        const lngInputEl = document.getElementById('lng_input');
        const errorEl = document.getElementById('latlng_error');
        if (errorEl) {
            errorEl.classList.add('d-none');
            errorEl.textContent = '';
        }
        if (!latInputEl || !lngInputEl) return;
        const lat = parseFloat(latInputEl.value);
        const lng = parseFloat(lngInputEl.value);
        const err = validateLatLng(lat, lng);
        if (err) {
            if (errorEl) {
                errorEl.textContent = err;
                errorEl.classList.remove('d-none');
            }
            return;
        }
        setLocationCoordinates(lat, lng);
        if (locationMap) {
            locationMap.setView([lat, lng], 13);
        }
    });

    // Confirm button handler
    document.getElementById('confirmLocation')?.addEventListener('click', function () {
        if (!latInput || !lngInput || !latInput.value || !lngInput.value) {
            alert(@json(__('messages.location_required') ?? 'Please select a location on the map before confirming.'));
            return;
        }
    });

    // Form validation
    const form = document.getElementById('createRestPointForm');
    if (form) {
        form.addEventListener('submit', function (e) {
            const lat = latInput ? latInput.value : '';
            const lng = lngInput ? lngInput.value : '';

            if (!lat || !lng) {
                e.preventDefault();
                alert(@json(__('messages.location_required') ?? 'Please select a location on the map before submitting.'));
                return false;
            }
        });
    }

    // Handle checklist documents preview
    const documentsInput = document.getElementById('checklist_documents');
    const documentsPreview = document.getElementById('checklist_documents_preview');
    
    if (documentsInput && documentsPreview) {
        documentsInput.addEventListener('change', function(e) {
            documentsPreview.innerHTML = '';
            
            if (e.target.files && e.target.files.length > 0) {
                Array.from(e.target.files).forEach((file, index) => {
                    if (file.type.startsWith('image/')) {
                        const reader = new FileReader();
                        reader.onload = function(e) {
                            const col = document.createElement('div');
                            col.className = 'col-md-3 col-sm-4 col-6';
                            col.innerHTML = `
                                <div class="position-relative">
                                    <img src="${e.target.result}" 
                                         alt="Preview ${index + 1}" 
                                         class="img-thumbnail w-100" 
                                         style="height: 150px; object-fit: cover;">
                                    <button type="button" 
                                            class="btn btn-sm btn-danger position-absolute top-0 end-0 m-1 remove-preview-image" 
                                            data-index="${index}"
                                            title="${@json(__('messages.remove_picture') ?? 'Remove')}">
                                        <i class="bi bi-x"></i>
                                    </button>
                                </div>
                            `;
                            documentsPreview.appendChild(col);
                        };
                        reader.readAsDataURL(file);
                    }
                });
            }
        });
        
        // Handle remove preview image
        documentsPreview.addEventListener('click', function(e) {
            if (e.target.closest('.remove-preview-image')) {
                const button = e.target.closest('.remove-preview-image');
                const index = parseInt(button.getAttribute('data-index'));
                const dt = new DataTransfer();
                const files = Array.from(documentsInput.files);
                files.forEach((file, i) => {
                    if (i !== index) {
                        dt.items.add(file);
                    }
                });
                documentsInput.files = dt.files;
                button.closest('.col-md-3').remove();
            }
        });
    }
});
</script>


