<x-app-layout>
    <x-slot name="header">
        @include('layouts.topnav')
    </x-slot>

    <!-- Toast Container -->
    <div class="toast-container position-fixed top-0 start-50 translate-middle-x p-3" style="z-index: 1055;">
        @if(session('success'))
            <div class="toast show" role="alert" aria-live="assertive" aria-atomic="true" data-bs-autohide="true" data-bs-delay="5000">
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
            <div class="toast show" role="alert" aria-live="assertive" aria-atomic="true" data-bs-autohide="true" data-bs-delay="5000">
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
        <!-- Filters -->
        <div class="card border-0 shadow-sm mb-4">
            <div class="card-body p-4">
                <div class="d-flex justify-content-between align-items-center mb-2">
                    <h2 class="mb-0 fw-bold text-dark fs-4">
                        <i class="bi bi-geo-alt-fill me-2 text-primary"></i>
                        {{ __('messages.rest_points') ?? 'Rest Points' }}
                    </h2>
                    <div class="d-flex flex-column flex-md-row gap-2 gap-md-3">
                        <button type="button" id="exportPdfBtn" class="btn btn-sm btn-danger">
                            <i class="bi bi-file-earmark-pdf me-2"></i>
                            {{ __('messages.export_pdf') ?? 'Export Map PDF' }}
                        </button>
                        <a href="{{ route('rest-points.export', $filters) }}" class="btn btn-sm btn-success">
                            <i class="bi bi-file-earmark-excel me-2"></i>
                            {{ __('messages.export_excel') ?? 'Export to Excel' }}
                        </a>
                        <button type="button" class="btn btn-sm btn-dark" data-bs-toggle="modal" data-bs-target="#createRestPointModal">
                            <i class="bi bi-plus-circle me-2"></i>
                            {{ __('messages.add_rest_point') ?? 'Add Rest Point' }}
                        </button>
                    </div>
                </div>
                <form action="{{ route('rest-points.index') }}" method="GET" id="restPointsFiltersForm">
                    <div class="row g-3 align-items-end">
                        <div class="col-12 col-md-3">
                            <label for="typeFilter" class="form-label fw-semibold">{{ __('messages.type') ?? 'Type' }}</label>
                            <select name="type" id="typeFilter" class="form-select">
                                <option value="">{{ __('messages.all_types') ?? 'All Types' }}</option>
                                @foreach($types as $key => $label)
                                    <option value="{{ $key }}" @selected(($filters['type'] ?? '') === $key)>
                                        {{ $label }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-12 col-md-6">
                            <label for="searchRestPoints" class="form-label fw-semibold">{{ __('messages.search') ?? 'Search' }}</label>
                            <div class="input-group">
                                <span class="input-group-text"><i class="bi bi-search"></i></span>
                                <input type="text" class="form-control" id="searchRestPoints" name="search" value="{{ $filters['search'] ?? '' }}" placeholder="{{ __('messages.search_in_table') ?? 'Search by name or description...' }}">
                            </div>
                        </div>
                        <div class="col-12 col-md-3">
                            <div class="d-flex gap-2">
                                <button type="submit" class="btn btn-dark w-100">
                                    <i class="bi bi-funnel me-1"></i>{{ __('messages.filter') ?? 'Filter' }}
                                </button>
                                <a href="{{ route('rest-points.index') }}" class="btn btn-outline-secondary">
                                    <i class="bi bi-x-circle"></i>
                                </a>
                            </div>
                        </div>
                    </div>
                </form>
            </div>
        </div>

        <!-- Map Section -->
        <div class="card border-0 shadow-sm mb-4">
            <div class="card-header bg-white border-0 py-3">
                <h5 class="mb-0 text-dark fw-bold">
                    <i class="bi bi-map me-2 text-primary"></i>
                    {{ __('messages.map_view') ?? 'Map View' }}
                </h5>
            </div>
            <div class="card-body p-0">
                <div id="rest-points-map" style="width: 100%; height: 600px; border-radius: 0.5rem;"></div>
            </div>
        </div>

        <!-- Table Section -->
        <div class="card border-0 shadow-sm">
            <div class="card-header bg-white border-0 py-3 d-flex justify-content-between align-items-center">
                <h5 class="mb-0 text-dark fw-bold">
                    <i class="bi bi-table me-2 text-primary"></i>
                    {{ __('messages.rest_points') ?? 'Rest Points' }}
                </h5>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover mb-0 align-middle">
                        <thead class="table-light">
                            <tr>
                                <th class="ps-3">{{ __('messages.name') ?? 'Name' }}</th>
                                <th>{{ __('messages.type') ?? 'Type' }}</th>
                                <th>{{ __('messages.coordinates') ?? 'Coordinates' }}</th>
                                <th>{{ __('messages.created_at') ?? 'Created At' }}</th>
                                <th>{{ __('messages.updated_at') ?? 'Updated At' }}</th>
                                <th class="text-end pe-3">{{ __('messages.actions') ?? 'Actions' }}</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($restPoints as $restPoint)
                                <tr>
                                    <td class="ps-3">
                                        <div class="fw-semibold">{{ $restPoint->name }}</div>
                                        @if($restPoint->description)
                                            <small class="text-muted">{{ Str::limit($restPoint->description, 50) }}</small>
                                        @endif
                                    </td>
                                    <td>
                                        <span class="badge bg-primary bg-opacity-10 text-primary">
                                            {{ $restPoint->type_label }}
                                        </span>
                                    </td>
                                    <td>
                                        <small class="text-muted">
                                            {{ number_format($restPoint->latitude, 6) }}, {{ number_format($restPoint->longitude, 6) }}
                                        </small>
                                    </td>
                                    <td>
                                        <span class="text-muted">
                                            {{ $restPoint->created_at->format('d/m/Y H:i') }}
                                        </span>
                                    </td>
                                    <td>
                                        <span class="text-muted">
                                            {{ $restPoint->updated_at->format('d/m/Y H:i') }}
                                        </span>
                                    </td>
                                    <td class="text-end pe-3">
                                        <div class="btn-group" role="group">
                                            <button type="button" 
                                                    class="btn btn-sm btn-outline-primary" 
                                                    title="{{ __('messages.edit') ?? 'Edit' }}"
                                                    data-bs-toggle="modal" 
                                                    data-bs-target="#editRestPointModal"
                                                    data-rest-point-id="{{ $restPoint->id }}"
                                                    data-rest-point-name="{{ $restPoint->name }}"
                                                    data-rest-point-type="{{ $restPoint->type }}"
                                                    data-rest-point-latitude="{{ $restPoint->latitude }}"
                                                    data-rest-point-longitude="{{ $restPoint->longitude }}"
                                                    data-rest-point-description="{{ $restPoint->description ?? '' }}">
                                                <i class="bi bi-pencil"></i>
                                            </button>
                                            <button type="button" 
                                                    class="btn btn-sm btn-outline-danger" 
                                                    title="{{ __('messages.delete') ?? 'Delete' }}"
                                                    data-bs-toggle="modal" 
                                                    data-bs-target="#deleteRestPointModal"
                                                    data-rest-point-id="{{ $restPoint->id }}"
                                                    data-rest-point-name="{{ $restPoint->name }}"
                                                    data-delete-url="{{ route('rest-points.destroy', $restPoint) }}">
                                                <i class="bi bi-trash"></i>
                                            </button>
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="6" class="text-center py-5 text-muted">
                                        <i class="bi bi-inbox fs-1 d-block mb-2"></i>
                                        {{ __('messages.no_rest_points_found') ?? 'No rest points found' }}
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
            @if($restPoints->hasPages())
                <div class="card-footer bg-white border-0 py-3">
                    <div class="d-flex justify-content-center">
                        {{ $restPoints->links() }}
                    </div>
                </div>
            @endif
        </div>
    </div>
</x-app-layout>

<!-- Create Rest Point Modal -->
<div class="modal fade" id="createRestPointModal" tabindex="-1" aria-labelledby="createRestPointModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="createRestPointModalLabel">
                    <i class="bi bi-plus-circle me-2"></i>{{ __('messages.add_rest_point') ?? 'Add Rest Point' }}
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form action="{{ route('rest-points.store') }}" method="POST" id="createRestPointForm">
                @csrf
                @if($errors->any() && old('_token'))
                    <div class="alert alert-danger mx-3 mt-3 mb-0">
                        <ul class="mb-0">
                            @foreach($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif
                <div class="modal-body">
                    <div class="row g-3">
                        <div class="col-12">
                            <label for="create_name" class="form-label fw-semibold">{{ __('messages.name') ?? 'Name' }} <span class="text-danger">*</span></label>
                            <input type="text" class="form-control @error('name') is-invalid @enderror" id="create_name" name="name" value="{{ old('name') }}" required>
                            @error('name')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="col-12">
                            <label for="create_type" class="form-label fw-semibold">{{ __('messages.type') ?? 'Type' }} <span class="text-danger">*</span></label>
                            <select class="form-select @error('type') is-invalid @enderror" id="create_type" name="type" required>
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
                            <label class="form-label fw-semibold">{{ __('messages.location') ?? 'Location' }} <span class="text-danger">*</span></label>
                            <div id="create-location-map" style="width: 100%; height: 300px; border-radius: 0.5rem; border: 1px solid #dee2e6; margin-bottom: 0.5rem;"></div>
                            <input type="hidden" name="latitude" id="create_latitude" value="{{ old('latitude') }}" required>
                            <input type="hidden" name="longitude" id="create_longitude" value="{{ old('longitude') }}" required>
                            <small class="text-muted d-block mt-1" id="create-location-coordinates-label">
                                @if(old('latitude') && old('longitude'))
                                    {{ __('messages.location_coords_label') ?? 'Coordinates' }}: <span class="fw-semibold">{{ old('latitude') }}, {{ old('longitude') }}</span>
                                @else
                                    {{ __('messages.location_map_help') ?? 'Click on the map to set the coordinates.' }}
                                @endif
                            </small>
                            @error('latitude')
                                <div class="text-danger small mt-1">{{ $message }}</div>
                            @enderror
                            @error('longitude')
                                <div class="text-danger small mt-1">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="col-12">
                            <label for="create_description" class="form-label fw-semibold">{{ __('messages.description') ?? 'Description' }}</label>
                            <textarea class="form-control @error('description') is-invalid @enderror" id="create_description" name="description" rows="3">{{ old('description') }}</textarea>
                            @error('description')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">{{ __('messages.cancel') ?? 'Cancel' }}</button>
                    <button type="submit" class="btn btn-dark">{{ __('messages.create') ?? 'Create' }}</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Edit Rest Point Modal -->
<div class="modal fade" id="editRestPointModal" tabindex="-1" aria-labelledby="editRestPointModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="editRestPointModalLabel">
                    <i class="bi bi-pencil me-2"></i>{{ __('messages.edit_rest_point') ?? 'Edit Rest Point' }}
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form action="" method="POST" id="editRestPointForm">
                @csrf
                @method('PUT')
                @if($errors->any() && old('_token') && old('_method') === 'PUT')
                    <div class="alert alert-danger mx-3 mt-3 mb-0">
                        <ul class="mb-0">
                            @foreach($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif
                <div class="modal-body">
                    <div class="row g-3">
                        <div class="col-12">
                            <label for="edit_name" class="form-label fw-semibold">{{ __('messages.name') ?? 'Name' }} <span class="text-danger">*</span></label>
                            <input type="text" class="form-control @error('name') is-invalid @enderror" id="edit_name" name="name" required>
                            @error('name')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="col-12">
                            <label for="edit_type" class="form-label fw-semibold">{{ __('messages.type') ?? 'Type' }} <span class="text-danger">*</span></label>
                            <select class="form-select @error('type') is-invalid @enderror" id="edit_type" name="type" required>
                                <option value="">{{ __('messages.select_type') ?? 'Select Type' }}</option>
                                @foreach($types as $key => $label)
                                    <option value="{{ $key }}">{{ $label }}</option>
                                @endforeach
                            </select>
                            @error('type')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="col-12">
                            <label class="form-label fw-semibold">{{ __('messages.location') ?? 'Location' }} <span class="text-danger">*</span></label>
                            <div id="edit-location-map" style="width: 100%; height: 300px; border-radius: 0.5rem; border: 1px solid #dee2e6; margin-bottom: 0.5rem;"></div>
                            <input type="hidden" name="latitude" id="edit_latitude" required>
                            <input type="hidden" name="longitude" id="edit_longitude" required>
                            <small class="text-muted d-block mt-1" id="edit-location-coordinates-label">
                                {{ __('messages.location_map_help') ?? 'Click on the map to set the coordinates.' }}
                            </small>
                            @error('latitude')
                                <div class="text-danger small mt-1">{{ $message }}</div>
                            @enderror
                            @error('longitude')
                                <div class="text-danger small mt-1">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="col-12">
                            <label for="edit_description" class="form-label fw-semibold">{{ __('messages.description') ?? 'Description' }}</label>
                            <textarea class="form-control @error('description') is-invalid @enderror" id="edit_description" name="description" rows="3"></textarea>
                            @error('description')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">{{ __('messages.cancel') ?? 'Cancel' }}</button>
                    <button type="submit" class="btn btn-dark">{{ __('messages.update') ?? 'Update' }}</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Delete Confirmation Modal -->
<div class="modal fade" id="deleteRestPointModal" tabindex="-1" aria-labelledby="deleteRestPointModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header bg-danger text-white">
                <h5 class="modal-title" id="deleteRestPointModalLabel">
                    <i class="bi bi-exclamation-triangle me-2"></i>{{ __('messages.confirm_delete') ?? 'Confirm Delete' }}
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <p class="mb-0">
                    {{ __('messages.confirm_delete_rest_point') ?? 'Are you sure you want to delete this rest point?' }}
                    <strong id="delete-rest-point-name"></strong>?
                </p>
                <p class="text-muted small mt-2 mb-0">
                    {{ __('messages.delete_warning') ?? 'This action cannot be undone.' }}
                </p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                    {{ __('messages.cancel') ?? 'Cancel' }}
                </button>
                <form id="deleteRestPointForm" method="POST" class="d-inline">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="btn btn-danger">
                        <i class="bi bi-trash me-2"></i>{{ __('messages.delete') ?? 'Delete' }}
                    </button>
                </form>
            </div>
        </div>
    </div>
</div>

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
    // Auto-open create modal if there are validation errors
    @if($errors->any() && old('_token') && !old('_method'))
        const createModal = new bootstrap.Modal(document.getElementById('createRestPointModal'));
        createModal.show();
    @endif

    // Auto-open edit modal if there are validation errors
    @if($errors->any() && old('_token') && old('_method') === 'PUT')
        const editModal = new bootstrap.Modal(document.getElementById('editRestPointModal'));
        editModal.show();
    @endif

    // ========== Delete Confirmation Modal ==========
    const deleteRestPointModal = document.getElementById('deleteRestPointModal');
    if (deleteRestPointModal) {
        deleteRestPointModal.addEventListener('show.bs.modal', function (event) {
            const button = event.relatedTarget;
            const restPointId = button.getAttribute('data-rest-point-id');
            const restPointName = button.getAttribute('data-rest-point-name');
            const deleteUrl = button.getAttribute('data-delete-url');

            const form = document.getElementById('deleteRestPointForm');
            const nameElement = document.getElementById('delete-rest-point-name');

            if (form) {
                form.action = deleteUrl;
            }

            if (nameElement) {
                nameElement.textContent = restPointName ? ' "' + restPointName + '"' : '';
            }
        });
    }

    if (!window.L) {
        console.error('Leaflet library not loaded');
        return;
    }

    // ========== Main Map (Index Page) ==========
    const mapContainer = document.getElementById('rest-points-map');
    if (mapContainer) {
        const map = L.map(mapContainer).setView([33.5731, -7.5898], 7);
        L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
            maxZoom: 19,
            attribution: '&copy; OpenStreetMap contributors',
        }).addTo(map);

        const restPoints = @json($allRestPoints);
        const typeColors = {
            'area': '#28a745',
            'station': '#007bff',
            'parking': '#ffc107',
            'other': '#6c757d'
        };

        function createCustomIcon(type) {
            const color = typeColors[type] || typeColors['other'];
            return L.divIcon({
                className: 'custom-marker',
                html: `<div style="background-color: ${color}; width: 30px; height: 30px; border-radius: 50% 50% 50% 0; transform: rotate(-45deg); border: 3px solid white; box-shadow: 0 2px 5px rgba(0,0,0,0.3);"></div>`,
                iconSize: [30, 30],
                iconAnchor: [15, 30],
                popupAnchor: [0, -30]
            });
        }

        restPoints.forEach(function(point) {
            if (point.latitude && point.longitude) {
                const marker = L.marker([parseFloat(point.latitude), parseFloat(point.longitude)], {
                    icon: createCustomIcon(point.type)
                }).addTo(map);

                const popupContent = `
                    <div class="p-2">
                        <h6 class="fw-bold mb-2">${point.name}</h6>
                        <p class="mb-1"><strong>{{ __('messages.type') ?? 'Type' }}:</strong> ${point.type_label || point.type}</p>
                        ${point.description ? `<p class="mb-1"><strong>{{ __('messages.description') ?? 'Description' }}:</strong> ${point.description}</p>` : ''}
                        <p class="mb-0 text-muted small">
                            <strong>{{ __('messages.coordinates') ?? 'Coordinates' }}:</strong><br>
                            ${parseFloat(point.latitude).toFixed(6)}, ${parseFloat(point.longitude).toFixed(6)}
                        </p>
                    </div>
                `;
                marker.bindPopup(popupContent);
            }
        });

        if (restPoints.length > 0) {
            const bounds = restPoints
                .filter(p => p.latitude && p.longitude)
                .map(p => [parseFloat(p.latitude), parseFloat(p.longitude)]);
            
            if (bounds.length > 0) {
                if (bounds.length === 1) {
                    map.setView(bounds[0], 13);
                } else {
                    map.fitBounds(bounds, { padding: [50, 50] });
                }
            }
        }
    }

    // ========== Create Rest Point Modal Map ==========
    let createMap = null;
    let createMarker = null;

    const createRestPointModal = document.getElementById('createRestPointModal');
    if (createRestPointModal) {
        createRestPointModal.addEventListener('shown.bs.modal', function () {
            if (!createMap) {
                const mapContainer = document.getElementById('create-location-map');
                if (!mapContainer) return;
                
                createMap = L.map(mapContainer).setView([33.5731, -7.5898], 7);
                L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
                    maxZoom: 19,
                    attribution: '&copy; OpenStreetMap contributors',
                }).addTo(createMap);

                const latInput = document.getElementById('create_latitude');
                const lngInput = document.getElementById('create_longitude');
                const coordsLabel = document.getElementById('create-location-coordinates-label');

                if (latInput && lngInput && latInput.value && lngInput.value) {
                    const lat = parseFloat(latInput.value);
                    const lng = parseFloat(lngInput.value);
                    createMap.setView([lat, lng], 13);
                    createMarker = L.marker([lat, lng]).addTo(createMap);
                }

                createMap.on('click', function (e) {
                    const lat = e.latlng.lat;
                    const lng = e.latlng.lng;

                    if (createMarker) {
                        createMarker.setLatLng([lat, lng]);
                    } else {
                        createMarker = L.marker([lat, lng]).addTo(createMap);
                    }

                    if (latInput) {
                        latInput.value = lat.toFixed(6);
                        latInput.setAttribute('value', lat.toFixed(6));
                    }
                    if (lngInput) {
                        lngInput.value = lng.toFixed(6);
                        lngInput.setAttribute('value', lng.toFixed(6));
                    }
                    if (coordsLabel) {
                        const label = @json(__('messages.location_coords_label') ?? 'Coordinates');
                        coordsLabel.innerHTML = label + ': <span class="fw-semibold">' + lat.toFixed(6) + ', ' + lng.toFixed(6) + '</span>';
                    }
                    
                    console.log('Map clicked - Latitude:', lat.toFixed(6), 'Longitude:', lng.toFixed(6));
                });
            } else {
                createMap.invalidateSize();
            }
        });
    }

    // ========== Form Validation ==========
    const createForm = document.getElementById('createRestPointForm');
    if (createForm) {
        createForm.addEventListener('submit', function(e) {
            const lat = document.getElementById('create_latitude').value;
            const lng = document.getElementById('create_longitude').value;
            
            console.log('Form submission - Latitude:', lat, 'Longitude:', lng);
            
            if (!lat || !lng || lat === '' || lng === '') {
                e.preventDefault();
                alert(@json(__('messages.location_required') ?? 'Please select a location on the map before submitting.'));
                return false;
            }
        });
    }

    const editForm = document.getElementById('editRestPointForm');
    if (editForm) {
        editForm.addEventListener('submit', function(e) {
            const lat = document.getElementById('edit_latitude').value;
            const lng = document.getElementById('edit_longitude').value;
            
            if (!lat || !lng) {
                e.preventDefault();
                alert(@json(__('messages.location_required') ?? 'Please select a location on the map before submitting.'));
                return false;
            }
        });
    }

    // ========== Edit Rest Point Modal Map ==========
    let editMap = null;
    let editMarker = null;

    // ========== Edit Rest Point Modal ==========
    const editRestPointModal = document.getElementById('editRestPointModal');
    if (editRestPointModal) {
        editRestPointModal.addEventListener('show.bs.modal', function (event) {
            const button = event.relatedTarget;
            const restPointId = button.getAttribute('data-rest-point-id');
            const name = button.getAttribute('data-rest-point-name');
            const type = button.getAttribute('data-rest-point-type');
            const latitude = button.getAttribute('data-rest-point-latitude');
            const longitude = button.getAttribute('data-rest-point-longitude');
            const description = button.getAttribute('data-rest-point-description');

            const form = document.getElementById('editRestPointForm');
            form.action = @json(url('/rest-points')) + '/' + restPointId;

            document.getElementById('edit_name').value = name || '';
            document.getElementById('edit_type').value = type || '';
            document.getElementById('edit_latitude').value = latitude || '';
            document.getElementById('edit_longitude').value = longitude || '';
            document.getElementById('edit_description').value = description || '';

            const coordsLabel = document.getElementById('edit-location-coordinates-label');
            if (latitude && longitude && coordsLabel) {
                const label = @json(__('messages.location_coords_label') ?? 'Coordinates');
                coordsLabel.innerHTML = label + ': <span class="fw-semibold">' + parseFloat(latitude).toFixed(6) + ', ' + parseFloat(longitude).toFixed(6) + '</span>';
            } else if (coordsLabel) {
                coordsLabel.innerHTML = @json(__('messages.location_map_help') ?? 'Click on the map to set the coordinates.');
            }
        });

        editRestPointModal.addEventListener('shown.bs.modal', function () {
            if (!editMap) {
                const mapContainer = document.getElementById('edit-location-map');
                if (!mapContainer) return;
                
                editMap = L.map(mapContainer).setView([33.5731, -7.5898], 7);
                L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
                    maxZoom: 19,
                    attribution: '&copy; OpenStreetMap contributors',
                }).addTo(editMap);

                const latInput = document.getElementById('edit_latitude');
                const lngInput = document.getElementById('edit_longitude');
                const coordsLabel = document.getElementById('edit-location-coordinates-label');

                function updateMarkerAndInputs(lat, lng) {
                    if (editMarker) {
                        editMarker.setLatLng([lat, lng]);
                    } else {
                        editMarker = L.marker([lat, lng]).addTo(editMap);
                    }

                    if (latInput) latInput.value = lat.toFixed(6);
                    if (lngInput) lngInput.value = lng.toFixed(6);
                    if (coordsLabel) {
                        const label = @json(__('messages.location_coords_label') ?? 'Coordinates');
                        coordsLabel.innerHTML = label + ': <span class="fw-semibold">' + lat.toFixed(6) + ', ' + lng.toFixed(6) + '</span>';
                    }
                }

                if (latInput && lngInput && latInput.value && lngInput.value) {
                    const lat = parseFloat(latInput.value);
                    const lng = parseFloat(lngInput.value);
                    editMap.setView([lat, lng], 13);
                    updateMarkerAndInputs(lat, lng);
                }

                editMap.on('click', function (e) {
                    updateMarkerAndInputs(e.latlng.lat, e.latlng.lng);
                });
            } else {
                editMap.invalidateSize();
                const latInput = document.getElementById('edit_latitude');
                const lngInput = document.getElementById('edit_longitude');
                if (latInput && lngInput && latInput.value && lngInput.value) {
                    const lat = parseFloat(latInput.value);
                    const lng = parseFloat(lngInput.value);
                    editMap.setView([lat, lng], 13);
                    if (editMarker) {
                        editMarker.setLatLng([lat, lng]);
                    } else {
                        editMarker = L.marker([lat, lng]).addTo(editMap);
                    }
                }
            }
        });
    }
});

// ========== PDF Export with Map Screenshot ==========
// Load html2canvas library
if (typeof html2canvas === 'undefined') {
    const script = document.createElement('script');
    script.src = 'https://cdnjs.cloudflare.com/ajax/libs/html2canvas/1.4.1/html2canvas.min.js';
    script.onload = function() {
        initPdfExport();
    };
    document.head.appendChild(script);
} else {
    initPdfExport();
}

function initPdfExport() {
    const exportPdfBtn = document.getElementById('exportPdfBtn');
    if (!exportPdfBtn) return;

    exportPdfBtn.addEventListener('click', function() {
        const btn = this;
        const originalText = btn.innerHTML;
        btn.disabled = true;
        btn.innerHTML = '<i class="bi bi-hourglass-split me-2"></i>{{ __('messages.generating_pdf') ?? 'Generating PDF...' }}';

        // Get the map container
        const mapContainer = document.getElementById('rest-points-map');
        if (!mapContainer) {
            alert('{{ __('messages.map_not_found') ?? 'Map not found' }}');
            btn.disabled = false;
            btn.innerHTML = originalText;
            return;
        }

        // Wait a bit for map to fully render
        setTimeout(function() {
            // Capture the map as image
            html2canvas(mapContainer, {
                backgroundColor: '#ffffff',
                scale: 2, // Higher quality
                logging: false,
                useCORS: true,
                allowTaint: false,
                width: mapContainer.offsetWidth,
                height: mapContainer.offsetHeight
            }).then(function(canvas) {
                // Convert canvas to base64
                const mapImageBase64 = canvas.toDataURL('image/png');

                // Create a form to submit the image
                const form = document.createElement('form');
                form.method = 'POST';
                form.action = '{{ route("rest-points.export-pdf", $filters) }}';
                
                // Add CSRF token
                const csrfInput = document.createElement('input');
                csrfInput.type = 'hidden';
                csrfInput.name = '_token';
                csrfInput.value = '{{ csrf_token() }}';
                form.appendChild(csrfInput);

                // Add the map image
                const imageInput = document.createElement('input');
                imageInput.type = 'hidden';
                imageInput.name = 'map_image';
                imageInput.value = mapImageBase64;
                form.appendChild(imageInput);

                // Add filters
                @if(request()->filled('type'))
                const typeInput = document.createElement('input');
                typeInput.type = 'hidden';
                typeInput.name = 'type';
                typeInput.value = '{{ request()->input("type") }}';
                form.appendChild(typeInput);
                @endif

                @if(request()->filled('search'))
                const searchInput = document.createElement('input');
                searchInput.type = 'hidden';
                searchInput.name = 'search';
                searchInput.value = '{{ request()->input("search") }}';
                form.appendChild(searchInput);
                @endif

                document.body.appendChild(form);
                form.submit();
            }).catch(function(error) {
                console.error('Error capturing map:', error);
                alert('{{ __('messages.error_capturing_map') ?? 'Error capturing map. Please try again.' }}');
                btn.disabled = false;
                btn.innerHTML = originalText;
            });
        }, 500); // Wait 500ms for map to render
    });
}
</script>

<style>
.custom-marker {
    background: transparent !important;
    border: none !important;
}
</style>

