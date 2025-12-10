<x-app-layout>
    <x-slot name="header">
        @include('layouts.topnav')
    </x-slot>

    <div class="container-fluid py-4 mt-4">
        <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center mb-4 gap-3 mx-auto bg-white p-4 rounded-3 shadow-sm">
            <div>
                <h1 class="h4 mb-1">{{ __('messages.coaching_cabines_show_title') }}</h1>
                <p class="text-muted mb-0">
                    {{ __('messages.driver') }}: {{ $coachingCabine->driver->full_name ?? '-' }}
                </p>
            </div>
            <div class="d-flex gap-2">
                <a href="{{ route('coaching-cabines.index') }}" class="btn btn-outline-secondary">
                    <i class="bi bi-arrow-left me-1"></i> {{ __('messages.coaching_cabines_back_to_list') }}
                </a>
            </div>
        </div>

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

        <div class="row g-4">
            <div class="col-lg-8">
                <div class="card border-0 shadow-sm mb-4">
                    <div class="card-header bg-white border-bottom py-3">
                        <h5 class="mb-0">{{ __('messages.coaching_session') }} - {{ __('messages.information') ?? 'Informations' }}</h5>
                    </div>
                    <div class="card-body">
                        <div class="row g-3">
                            <div class="col-6 col-md-4">
                                <label class="form-label text-muted small">{{ __('messages.driver') }}</label>
                                <div class="fw-semibold">
                                    <a href="{{ route('drivers.show', $coachingCabine->driver) }}" class="text-decoration-none">
                                        {{ $coachingCabine->driver->full_name ?? '-' }}
                                    </a>
                                </div>
                            </div>
                            <div class="col-6 col-md-4">
                                <label class="form-label text-muted small">{{ __('messages.flotte') }}</label>
                                <div class="fw-semibold">
                                    @if($coachingCabine->flotte)
                                        <span class="badge bg-info bg-opacity-10 text-info">
                                            {{ $coachingCabine->flotte->name }}
                                        </span>
                                    @else
                                        <span class="text-muted">-</span>
                                    @endif
                                </div>
                            </div>
                            <div class="col-6 col-md-4">
                                <label class="form-label text-muted small">{{ __('messages.type') ?? 'Type' }}</label>
                                <div>
                                    <span class="badge bg-{{ $coachingCabine->getTypeColor() }}-opacity-10 text-{{ $coachingCabine->getTypeColor() }}">
                                        {{ $coachingCabine->getTypeTitle() }}
                                    </span>
                                </div>
                            </div>
                            <div class="col-6 col-md-4">
                                <label class="form-label text-muted small">{{ __('messages.from_date') }}</label>
                                <div class="fw-semibold">
                                    {{ $coachingCabine->date ? $coachingCabine->date->format('d/m/Y') : '-' }}
                                </div>
                            </div>
                            <div class="col-6 col-md-4">
                                <label class="form-label text-muted small">{{ __('messages.date_fin') }}</label>
                                <div class="fw-semibold">
                                    {{ $coachingCabine->date_fin ? $coachingCabine->date_fin->format('d/m/Y') : '-' }}
                                </div>
                            </div>
                            <div class="col-6 col-md-4">
                                <label class="form-label text-muted small">{{ __('messages.validity_days') }}</label>
                                <div class="fw-semibold">
                                    {{ $coachingCabine->validity_days ?? '-' }} {{ __('messages.days') ?? 'jours' }}
                                </div>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label text-muted small">{{ __('messages.next_planning_session') }}</label>
                                <div class="fw-semibold">
                                    {{ $coachingCabine->next_planning_session ? $coachingCabine->next_planning_session->format('d/m/Y') : '-' }}
                                </div>
                            </div>
                            <div class="col-6 col-md-4">
                                <label class="form-label text-muted small">{{ __('messages.moniteur') }}</label>
                                <div class="fw-semibold">
                                    {{ $coachingCabine->moniteur ?? '-' }}
                                </div>
                            </div>
                            <div class="col-6 col-md-4">
                                <label class="form-label text-muted small">{{ __('messages.status') }}</label>
                                <div>
                                    @php
                                        $statusLabels = [
                                            'planned' => __('messages.status_planned'),
                                            'in_progress' => __('messages.status_in_progress'),
                                            'completed' => __('messages.status_completed'),
                                            'cancelled' => __('messages.status_cancelled')
                                        ];
                                        $statusColors = [
                                            'planned' => 'primary',
                                            'in_progress' => 'warning',
                                            'completed' => 'success',
                                            'cancelled' => 'danger'
                                        ];
                                    @endphp
                                    <span class="badge bg-{{ $statusColors[$coachingCabine->status] ?? 'secondary' }}-opacity-10 text-{{ $statusColors[$coachingCabine->status] ?? 'secondary' }}">
                                        {{ $statusLabels[$coachingCabine->status] ?? $coachingCabine->status }}
                                    </span>
                                </div>
                            </div>
                            <div class="col-6 col-md-4">
                                <label class="form-label text-muted small">{{ __('messages.score') ?? 'Score' }}</label>
                                <div>
                                    @if($coachingCabine->score !== null)
                                        @php
                                            $scoreColor = $coachingCabine->score >= 70 ? 'success' : ($coachingCabine->score >= 50 ? 'warning' : 'danger');
                                            $scoreLabel = $coachingCabine->score >= 70 ? __('messages.score_excellent') : ($coachingCabine->score >= 50 ? __('messages.score_average') : __('messages.score_poor'));
                                        @endphp
                                        <span class="badge bg-{{ $scoreColor }}-opacity-10 text-{{ $scoreColor }}">
                                            {{ $coachingCabine->score }}/100 - {{ $scoreLabel }}
                                        </span>
                                    @else
                                        <span class="text-muted">-</span>
                                    @endif
                                </div>
                            </div>
                            <div class="col-6 col-md-4">
                                <label class="form-label text-muted small">{{ __('messages.from_location_name') ?? 'From Location Name' }}</label>
                                <div class="fw-semibold">
                                    {{ $coachingCabine->from_location_name ?? '-' }}
                                </div>
                            </div>
                            <div class="col-6 col-md-4">
                                <label class="form-label text-muted small">{{ __('messages.to_location_name') ?? 'To Location Name' }}</label>
                                <div class="fw-semibold">
                                    {{ $coachingCabine->to_location_name ?? '-' }}
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                @if($coachingCabine->from_latitude && $coachingCabine->from_longitude && $coachingCabine->to_latitude && $coachingCabine->to_longitude)
                <div class="card border-0 shadow-sm mb-4">
                    <div class="card-header bg-white border-bottom py-3">
                        <div class="d-flex justify-content-between align-items-center">
                            <h5 class="mb-0">{{ __('messages.route_taken') }}</h5>
                            <div id="route-distance" class="text-muted small">
                                <i class="bi bi-arrow-right-circle me-1"></i>
                                <span id="distance-value">-</span>
                            </div>
                        </div>
                    </div>
                    <div class="card-body p-0">
                        <div id="route-map" style="height: 500px; width: 100%;"></div>
                    </div>
                
                    @if($coachingCabine->route_taken)
                    <div class="card border-0">
                        <div class="card-header bg-white border-bottom py-3">
                            <h5 class="mb-0">{{ __('messages.description') }}</h5>
                        </div>
                        <div class="card-body bg-light p-3">
                            {{ $coachingCabine->route_taken }}
                        </div>
                    </div>
                    @endif
                </div>
                @endif
                @if($coachingCabine->assessment)
                <div class="card border-0 shadow-sm mb-4">
                    <div class="card-header bg-white border-bottom py-3">
                        <h5 class="mb-0">{{ __('messages.assessment') }}</h5>
                    </div>
                    <div class="card-body">
                        {{ $coachingCabine->assessment }}
                    </div>
                </div>   
                @endif
                @if($coachingCabine->notes)
                <div class="card border-0 shadow-sm mb-4">
                    <div class="card-header bg-white border-bottom py-3">
                        <h5 class="mb-0">{{ __('messages.notes') ?? 'Notes' }}</h5>
                    </div>
                    <div class="card-body">
                        {{ $coachingCabine->notes }}
                    </div>
                </div>
                @endif
            </div>

            <div class="col-lg-4">
                <div class="card border-0 shadow-sm">
                    <div class="card-header bg-white border-bottom py-3">
                        <h5 class="mb-0">
                            <i class="bi bi-lightning-charge me-2 text-warning"></i>
                            {{ __('messages.quick_actions') ?? 'Quick Actions' }}
                        </h5>
                    </div>
                    <div class="card-body">
                        <div class="d-grid gap-2">
                            <a href="{{ route('coaching-cabines.edit', $coachingCabine) }}" class="btn btn-warning">
                                <i class="bi bi-pencil me-1"></i> {{ __('messages.edit') }}
                            </a>
                            @if ($coachingCabine->status != 'completed')
                                <button type="button" class="btn btn-success" data-bs-toggle="modal" data-bs-target="#completeModal{{ $coachingCabine->id }}">
                                    <i class="bi bi-check-circle me-1"></i> {{ __('messages.complete_session') ?? 'Compléter la session' }}
                                </button>
                            @endif
                            @if ($coachingCabine->checklist)
                                <a href="{{ route('coaching.checklists.show', [$coachingCabine, $coachingCabine->checklist]) }}" class="btn btn-outline-info btn-sm">
                                    <i class="bi bi-eye me-1"></i> {{ __('messages.view_checklist') ?? 'View Checklist' }}
                                </a>
                                <a href="{{ route('coaching.checklists.pdf', [$coachingCabine, $coachingCabine->checklist]) }}" class="btn btn-outline-danger btn-sm" target="_blank" rel="noopener">
                                    <i class="bi bi-file-earmark-text me-1"></i> {{ __('messages.download_checklist_pdf') ?? 'Download Checklist PDF' }}
                                </a>
                            @else
                                <a href="{{ route('coaching.checklists.create', $coachingCabine) }}" class="btn btn-outline-primary btn-sm">
                                    <i class="bi bi-clipboard-plus me-1"></i> {{ __('messages.add_checklist') ?? 'Add Checklist' }}
                                </a>
                            @endif
                            <hr class="my-2">
                            <a href="{{ route('drivers.show', $coachingCabine->driver) }}" class="btn btn-outline-primary btn-sm">
                                <i class="bi bi-person me-1"></i> {{ __('messages.view_driver') ?? 'Voir le chauffeur' }}
                            </a>
                            @if ($coachingCabine->status == 'completed')
                                <a href="{{ route('coaching-cabines.pdf', $coachingCabine) }}" class="btn btn-danger btn-sm" target="_blank" rel="noopener">
                                    <i class="bi bi-file-pdf me-1"></i> {{ __('messages.export_pdf') ?? 'Exporter en PDF' }}
                                </a>
                            @endif
                        </div>
                    </div>
                </div>

                @if($coachingCabine->rest_places && count($coachingCabine->rest_places) > 0)
                <div class="card border-0 shadow-sm mt-4">
                    <div class="card-header bg-white border-bottom py-3">
                        <h5 class="mb-0">
                            <i class="bi bi-geo-alt me-2 text-primary"></i>
                            {{ __('messages.rest_places') ?? 'Rest Places' }}
                        </h5>
                    </div>
                    <div class="card-body">
                        <ul class="list-unstyled mb-0">
                            @foreach($coachingCabine->rest_places as $index => $place)
                                <li class="mb-2 pb-2 border-bottom">
                                    <div class="d-flex align-items-center">
                                        <span class="badge bg-primary bg-opacity-10 text-primary me-2">
                                            {{ __('messages.day') ?? 'Day' }} {{ $index + 1 }}
                                        </span>
                                        <span class="fw-semibold">{{ $place }}</span>
                                    </div>
                                </li>
                            @endforeach
                        </ul>
                    </div>
                </div>
                @endif
            </div>
        </div>
    </div>

    <!-- Leaflet CSS and JS -->
    @if($coachingCabine->from_latitude && $coachingCabine->from_longitude && $coachingCabine->to_latitude && $coachingCabine->to_longitude)
    <link
        rel="stylesheet"
        href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css"
        integrity="sha256-p4NxAoJBhIIN+hmNHrzRCf9tD/miZyoHS5obTRR9BMY="
        crossorigin=""
    />
    <style>
        .custom-marker {
            background: transparent;
            border: none;
        }
        .custom-marker-pin {
            width: 30px;
            height: 30px;
            border-radius: 50% 50% 50% 0;
            transform: rotate(-45deg);
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-weight: bold;
            font-size: 14px;
        }
        .custom-marker-pin .bi {
            transform: rotate(45deg);
        }
        .custom-marker-letter {
            transform: rotate(45deg);
        }
    </style>
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

            const mapContainer = document.getElementById('route-map');
            if (!mapContainer) {
                return;
            }

            const fromLat = {{ $coachingCabine->from_latitude }};
            const fromLng = {{ $coachingCabine->from_longitude }};
            const toLat = {{ $coachingCabine->to_latitude }};
            const toLng = {{ $coachingCabine->to_longitude }};

            // Calculate center point between from and to
            const centerLat = (fromLat + toLat) / 2;
            const centerLng = (fromLng + toLng) / 2;

            const map = L.map(mapContainer).setView([centerLat, centerLng], 8);
            L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
                maxZoom: 19,
                attribution: '&copy; OpenStreetMap contributors',
            }).addTo(map);

            let fromMarker = null;
            let toMarker = null;
            let routeLine = null;
            let routeShadow = null;
            let restPlaceMarkers = [];
            const restPlaces = @json($coachingCabine->rest_places ?? []);

            // Calculate distance using Haversine formula (straight line distance)
            function calculateHaversineDistance(lat1, lon1, lat2, lon2) {
                const R = 6371; // Earth's radius in kilometers
                const dLat = (lat2 - lat1) * Math.PI / 180;
                const dLon = (lon2 - lon1) * Math.PI / 180;
                const a = 
                    Math.sin(dLat / 2) * Math.sin(dLat / 2) +
                    Math.cos(lat1 * Math.PI / 180) * Math.cos(lat2 * Math.PI / 180) *
                    Math.sin(dLon / 2) * Math.sin(dLon / 2);
                const c = 2 * Math.atan2(Math.sqrt(a), Math.sqrt(1 - a));
                return R * c;
            }

            // Update distance display
            function updateDistanceDisplay(distanceKm) {
                const distanceElement = document.getElementById('distance-value');
                if (distanceElement) {
                    distanceElement.textContent = distanceKm.toFixed(1) + ' km';
                }
            }

            // Helper: fetch and draw professional road route using OSRM
            async function fetchRouteAndDraw(fromLat, fromLng, toLat, toLng) {
                if (!map) return;

                try {
                    const osrmUrl = `https://router.project-osrm.org/route/v1/driving/${fromLng},${fromLat};${toLng},${toLat}?overview=full&geometries=geojson`;
                    const response = await fetch(osrmUrl);

                    if (!response.ok) {
                        throw new Error('Route service unavailable');
                    }

                    const data = await response.json();
                    if (data.code !== 'Ok' || !data.routes || data.routes.length === 0) {
                        throw new Error('No route found');
                    }

                    const route = data.routes[0];
                    const coordinates = route.geometry.coordinates.map(coord => [coord[1], coord[0]]); // [lng, lat] → [lat, lng]

                    // Note: Distance is already calculated using Haversine for consistency with index page
                    // OSRM distance (road distance) would be longer, but we use Haversine for consistency

                    // Remove existing route layers
                    if (routeLine) {
                        map.removeLayer(routeLine);
                    }
                    if (routeShadow) {
                        map.removeLayer(routeShadow);
                    }

                    // Shadow (below)
                    routeShadow = L.polyline(coordinates, {
                        color: '#1e40af',
                        weight: 7,
                        opacity: 0.3,
                        lineJoin: 'round',
                        lineCap: 'round',
                    }).addTo(map);

                    // Main route (on top)
                    routeLine = L.polyline(coordinates, {
                        color: '#2563eb',
                        weight: 5,
                        opacity: 0.85,
                        lineJoin: 'round',
                        lineCap: 'round',
                    }).addTo(map);
                    
                    // Make routeLine accessible globally for PDF export check
                    window.routeLine = routeLine;

                    // Fit map to show from/to and route
                    const group = new L.featureGroup([fromMarker, toMarker, routeLine]);
                    map.fitBounds(group.getBounds().pad(0.1));
                } catch (error) {
                    console.warn('Failed to fetch OSRM route, using straight line:', error);

                    // Fallback: calculate straight line distance using Haversine
                    const straightDistanceKm = calculateHaversineDistance(fromLat, fromLng, toLat, toLng);
                    updateDistanceDisplay(straightDistanceKm);

                    // Fallback: simple straight line
                    if (routeLine) {
                        map.removeLayer(routeLine);
                    }
                    if (routeShadow) {
                        map.removeLayer(routeShadow);
                    }

                    routeLine = L.polyline([[fromLat, fromLng], [toLat, toLng]], {
                        color: '#2563eb',
                        weight: 4,
                        opacity: 0.75,
                        dashArray: '10,5',
                    }).addTo(map);
                    
                    // Make routeLine accessible globally for PDF export check
                    window.routeLine = routeLine;

                    const group = new L.featureGroup([fromMarker, toMarker, routeLine]);
                    map.fitBounds(group.getBounds().pad(0.1));
                }
            }

            // Calculate and display distance using Haversine (consistent with index page)
            const haversineDistance = calculateHaversineDistance(fromLat, fromLng, toLat, toLng);
            updateDistanceDisplay(haversineDistance);

            // Add from marker
            const fromLocationName = @json($coachingCabine->from_location_name);
            let fromPopupContent = '<strong>{{ __('messages.from_location') ?? 'From Location' }}</strong>';
            if (fromLocationName) {
                fromPopupContent += '<br><span class="text-primary fw-semibold">' + fromLocationName + '</span>';
            } else {
                fromPopupContent += '<br><span class="text-primary fw-semibold">' + fromLat.toFixed(6) + ', ' + fromLng.toFixed(6) + '</span>';
            }
            fromPopupContent += '<br><small class="text-muted">' + fromLat.toFixed(6) + ', ' + fromLng.toFixed(6) + '</small>';
            
            fromMarker = L.marker([fromLat, fromLng], {
                icon: L.divIcon({
                    className: 'custom-marker',
                    html: '<div class="custom-marker-pin" style="background-color: #2563eb;"><span class="custom-marker-letter">F</span></div>',
                    iconSize: [30, 30],
                    iconAnchor: [15, 30]
                })
            }).addTo(map).bindPopup(fromPopupContent);

            // Add to marker
            const toLocationName = @json($coachingCabine->to_location_name);
            let toPopupContent = '<strong>{{ __('messages.to_location') ?? 'To Location' }}</strong>';
            if (toLocationName) {
                toPopupContent += '<br><span class="text-primary fw-semibold">' + toLocationName + '</span>';
            } else {
                toPopupContent += '<br><span class="text-primary fw-semibold">' + toLat.toFixed(6) + ', ' + toLng.toFixed(6) + '</span>';
            }
            toPopupContent += '<br><small class="text-muted">' + toLat.toFixed(6) + ', ' + toLng.toFixed(6) + '</small>';
            
            toMarker = L.marker([toLat, toLng], {
                icon: L.divIcon({
                    className: 'custom-marker',
                    html: '<div class="custom-marker-pin" style="background-color: #2563eb;"><span class="custom-marker-letter">T</span></div>',
                    iconSize: [30, 30],
                    iconAnchor: [15, 30]
                })
            }).addTo(map).bindPopup(toPopupContent);

            // Geocode and add rest places markers
            async function geocodeAndAddRestPlace(placeName, dayNumber) {
                try {
                    const url = 'https://nominatim.openstreetmap.org/search?format=json&limit=1&q=' + encodeURIComponent(placeName);
                    const response = await fetch(url, {
                        headers: {
                            'Accept': 'application/json',
                            'User-Agent': 'GCV Coaching System'
                        }
                    });
                    
                    if (!response.ok) {
                        console.warn('Failed to geocode rest place:', placeName);
                        return null;
                    }
                    
                    const data = await response.json();
                    if (!Array.isArray(data) || data.length === 0) {
                        console.warn('No results for rest place:', placeName);
                        return null;
                    }
                    
                    const result = data[0];
                    const lat = parseFloat(result.lat);
                    const lng = parseFloat(result.lon);
                    
                    if (isNaN(lat) || isNaN(lng)) {
                        return null;
                    }
                    
                    const popupContent = '<strong>{{ __('messages.rest_place') ?? 'Rest Place' }} - {{ __('messages.day') ?? 'Day' }} ' + dayNumber + '</strong><br><span class="text-primary fw-semibold">' + placeName + '</span><br><small class="text-muted">' + lat.toFixed(6) + ', ' + lng.toFixed(6) + '</small>';
                    
                    const marker = L.marker([lat, lng], {
                        icon: L.divIcon({
                            className: 'custom-marker',
                            html: '<div class="custom-marker-pin" style="background-color: #10b981;"><span class="custom-marker-letter">' + dayNumber + '</span></div>',
                            iconSize: [30, 30],
                            iconAnchor: [15, 30]
                        })
                    }).addTo(map).bindPopup(popupContent);
                    
                    restPlaceMarkers.push(marker);
                    return { lat, lng };
                } catch (error) {
                    console.warn('Error geocoding rest place:', placeName, error);
                    return null;
                }
            }

            // Geocode all rest places and add markers
            async function addRestPlacesToMap() {
                if (!restPlaces || restPlaces.length === 0) {
                    // Draw route without rest places
                    fetchRouteAndDraw(fromLat, fromLng, toLat, toLng);
                    return;
                }
                
                const waypoints = [[fromLat, fromLng]];
                const restPlaceCoords = [];
                
                // Geocode all rest places
                for (let i = 0; i < restPlaces.length; i++) {
                    const coords = await geocodeAndAddRestPlace(restPlaces[i], i + 1);
                    if (coords) {
                        waypoints.push([coords.lat, coords.lng]);
                        restPlaceCoords.push(coords);
                    }
                }
                
                waypoints.push([toLat, toLng]);
                
                // Draw route with waypoints
                if (waypoints.length > 2) {
                    await fetchRouteWithWaypoints(waypoints);
                } else {
                    fetchRouteAndDraw(fromLat, fromLng, toLat, toLng);
                }
                
                // Fit bounds to include all markers
                const allMarkers = [fromMarker, toMarker, ...restPlaceMarkers].filter(m => m !== null);
                if (allMarkers.length > 0) {
                    const group = new L.featureGroup(allMarkers);
                    map.fitBounds(group.getBounds().pad(0.1));
                }
            }

            // Fetch route with multiple waypoints
            async function fetchRouteWithWaypoints(waypoints) {
                if (!map || waypoints.length < 2) return;
                
                try {
                    // Build OSRM URL with waypoints
                    const coordinates = waypoints.map(wp => `${wp[1]},${wp[0]}`).join(';');
                    const osrmUrl = `https://router.project-osrm.org/route/v1/driving/${coordinates}?overview=full&geometries=geojson`;
                    const response = await fetch(osrmUrl);
                    
                    if (!response.ok) {
                        throw new Error('Route service unavailable');
                    }
                    
                    const data = await response.json();
                    if (data.code !== 'Ok' || !data.routes || data.routes.length === 0) {
                        throw new Error('No route found');
                    }
                    
                    const route = data.routes[0];
                    const routeCoordinates = route.geometry.coordinates.map(coord => [coord[1], coord[0]]);
                    
                    // Remove existing route layers
                    if (routeLine) {
                        map.removeLayer(routeLine);
                    }
                    if (routeShadow) {
                        map.removeLayer(routeShadow);
                    }
                    
                    // Shadow (below)
                    routeShadow = L.polyline(routeCoordinates, {
                        color: '#1e40af',
                        weight: 7,
                        opacity: 0.3,
                        lineJoin: 'round',
                        lineCap: 'round',
                    }).addTo(map);
                    
                    // Main route (on top)
                    routeLine = L.polyline(routeCoordinates, {
                        color: '#2563eb',
                        weight: 5,
                        opacity: 0.85,
                        lineJoin: 'round',
                        lineCap: 'round',
                    }).addTo(map);
                    
                    // Make routeLine accessible globally for PDF export check
                    window.routeLine = routeLine;
                } catch (error) {
                    console.warn('Failed to fetch route with waypoints, using straight lines:', error);
                    
                    // Fallback: draw straight lines between waypoints
                    if (routeLine) {
                        map.removeLayer(routeLine);
                    }
                    if (routeShadow) {
                        map.removeLayer(routeShadow);
                    }
                    
                    routeLine = L.polyline(waypoints, {
                        color: '#2563eb',
                        weight: 4,
                        opacity: 0.75,
                        dashArray: '10,5',
                    }).addTo(map);
                }
            }

            // Add rest places to map
            addRestPlacesToMap();

            // Fix rendering
            setTimeout(function() {
                map.invalidateSize();
            }, 100);
        });
    </script>
    @endif

    {{-- Complete Session Modal --}}
    @if($coachingCabine->status != 'completed')
    <div class="modal fade" id="completeModal{{ $coachingCabine->id }}" tabindex="-1" aria-labelledby="completeModalLabel{{ $coachingCabine->id }}" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <form action="{{ route('coaching-cabines.complete', $coachingCabine) }}" method="POST">
                    @csrf
                    @method('PUT')
                    <div class="modal-header">
                        <h5 class="modal-title" id="completeModalLabel{{ $coachingCabine->id }}">
                            <i class="bi bi-check-circle me-2 text-success"></i>
                            {{ __('messages.complete_session') ?? 'Compléter la session' }} - {{ $coachingCabine->driver->full_name ?? '' }}
                        </h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <div class="row g-3">
                            <div class="col-md-6">
                                <label for="score{{ $coachingCabine->id }}" class="form-label fw-semibold">{{ __('messages.score') }} <span class="text-danger">*</span></label>
                                <input type="number" name="score" id="score{{ $coachingCabine->id }}" class="form-control @error('score') is-invalid @enderror" value="{{ old('score', $coachingCabine->score) }}" min="0" max="100" required>
                                <small class="text-muted">{{ __('messages.score_range') ?? 'Entre 0 et 100' }}</small>
                                @error('score')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-md-6">
                                <label for="next_planning_session{{ $coachingCabine->id }}" class="form-label fw-semibold">{{ __('messages.next_planning_session') }}</label>
                                <input type="date" name="next_planning_session" id="next_planning_session{{ $coachingCabine->id }}" class="form-control @error('next_planning_session') is-invalid @enderror" value="{{ old('next_planning_session', $coachingCabine->next_planning_session?->format('Y-m-d')) }}">
                                @error('next_planning_session')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-12">
                                <label for="route_taken{{ $coachingCabine->id }}" class="form-label fw-semibold">{{ __('messages.route_taken') }}</label>
                                <textarea name="route_taken" id="route_taken{{ $coachingCabine->id }}" rows="3" class="form-control @error('route_taken') is-invalid @enderror">{{ old('route_taken', $coachingCabine->route_taken) }}</textarea>
                                @error('route_taken')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-12">
                                <label for="assessment{{ $coachingCabine->id }}" class="form-label fw-semibold">{{ __('messages.assessment') }}</label>
                                <textarea name="assessment" id="assessment{{ $coachingCabine->id }}" rows="4" class="form-control @error('assessment') is-invalid @enderror">{{ old('assessment', $coachingCabine->assessment) }}</textarea>
                                @error('assessment')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-12">
                                <label for="notes{{ $coachingCabine->id }}" class="form-label fw-semibold">{{ __('messages.notes') ?? 'Notes' }}</label>
                                <textarea name="notes" id="notes{{ $coachingCabine->id }}" rows="3" class="form-control @error('notes') is-invalid @enderror">{{ old('notes', $coachingCabine->notes) }}</textarea>
                                @error('notes')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            {{-- Rest Places Section --}}
                            <div class="col-12">
                                <label class="form-label fw-semibold">{{ __('messages.rest_places') ?? 'Rest Places' }}</label>
                                <small class="text-muted d-block mb-2">
                                    {{ __('messages.rest_places_help', ['days' => $coachingCabine->validity_days - 1]) ?? 'Add rest places from Day 1 to Day ' . ($coachingCabine->validity_days - 1) . ' (maximum ' . ($coachingCabine->validity_days - 1) . ' rest places)' }}
                                </small>
                                <div id="rest-places-container-{{ $coachingCabine->id }}" data-max-places="{{ $coachingCabine->validity_days - 1 }}">
                                    @php
                                        $restPlaces = old('rest_places', $coachingCabine->rest_places ?? []);
                                        $currentCount = count($restPlaces);
                                    @endphp
                                    @if($currentCount > 0)
                                        @foreach($restPlaces as $i => $place)
                                            <div class="rest-place-item mb-2" data-index="{{ $i }}">
                                                <div class="input-group input-group-sm">
                                                    <span class="input-group-text">{{ __('messages.day') ?? 'Day' }} {{ $i + 1 }}</span>
                                                    <input type="text" 
                                                           name="rest_places[]" 
                                                           class="form-control rest-place-input" 
                                                           value="{{ $place }}"
                                                           placeholder="{{ __('messages.rest_place_placeholder') ?? 'Enter city or village name' }}"
                                                           data-session-id="{{ $coachingCabine->id }}"
                                                           data-place-index="{{ $i }}">
                                                    <button type="button" 
                                                            class="btn btn-outline-primary rest-place-search-btn" 
                                                            data-session-id="{{ $coachingCabine->id }}"
                                                            data-place-index="{{ $i }}"
                                                            title="{{ __('messages.search_rest_place_city') ?? 'Search city' }}">
                                                        <i class="bi bi-search"></i>
                                                    </button>
                                                    <button type="button" 
                                                            class="btn btn-outline-danger remove-rest-place-btn" 
                                                            data-session-id="{{ $coachingCabine->id }}"
                                                            title="{{ __('messages.remove_rest_place') ?? 'Remove' }}">
                                                        <i class="bi bi-trash"></i>
                                                    </button>
                                                    </div>
                                                    <div class="rest-place-error text-danger small mt-1 d-none" id="rest-place-error-{{ $coachingCabine->id }}-{{ $i }}"></div>
                                                    <div class="rest-place-map-container mt-2" id="rest-place-map-{{ $coachingCabine->id }}-{{ $i }}" style="height: 200px; width: 100%; background: #f5f5f5; display: none;"></div>
                                                </div>
                                            @endforeach
                                        @endif
                                    </div>
                                <button type="button" 
                                        class="btn btn-outline-success btn-sm mt-2 add-rest-place-btn" 
                                        data-session-id="{{ $coachingCabine->id }}"
                                        data-max-places="{{ $coachingCabine->validity_days - 1 }}"
                                        @if($currentCount >= ($coachingCabine->validity_days - 1)) style="display: none;" @endif>
                                    <i class="bi bi-plus-circle me-1"></i> {{ __('messages.add_rest_place') ?? 'Add Rest Place' }}
                                </button>
                                @error('rest_places')
                                    <div class="text-danger small mt-1">{{ $message }}</div>
                                @enderror
                                @error('rest_places.*')
                                    <div class="text-danger small mt-1">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">{{ __('messages.cancel') }}</button>
                        <button type="submit" class="btn btn-success">
                            <i class="bi bi-check-circle me-1"></i> {{ __('messages.complete') ?? 'Compléter' }}
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    @endif

    @push('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Geocoding function for rest places
            async function geocodeRestPlace(query) {
                const url = 'https://nominatim.openstreetmap.org/search?format=json&limit=1&q=' + encodeURIComponent(query);
                const response = await fetch(url, {
                    headers: {
                        'Accept': 'application/json',
                        'User-Agent': 'GCV Coaching System'
                    }
                });
                if (!response.ok) {
                    throw new Error('Geocoding request failed');
                }
                const data = await response.json();
                if (!Array.isArray(data) || data.length === 0) {
                    throw new Error('No results found');
                }
                const first = data[0];
                
                // Extract short city/village name
                let shortName = query;
                if (first.address) {
                    shortName = first.address.city || 
                               first.address.town || 
                               first.address.village || 
                               first.address.municipality ||
                               first.address.county ||
                               query;
                } else {
                    const displayName = first.display_name || '';
                    const parts = displayName.split(',');
                    if (parts.length > 0) {
                        shortName = parts[0].trim();
                    }
                }
                
                return {
                    name: shortName,
                    lat: parseFloat(first.lat),
                    lng: parseFloat(first.lon),
                    displayName: first.display_name || shortName
                };
            }

            // Initialize map for rest place
            function initRestPlaceMap(containerId, lat, lng, placeName) {
                // Clear any existing timeout for this container
                if (window._restPlaceMapTimeouts && window._restPlaceMapTimeouts[containerId]) {
                    clearTimeout(window._restPlaceMapTimeouts[containerId]);
                    delete window._restPlaceMapTimeouts[containerId];
                }
                if (!window._restPlaceMapTimeouts) {
                    window._restPlaceMapTimeouts = {};
                }

                function waitForLeaflet(callback) {
                    if (window.L && window.L.map) {
                        callback();
                    } else {
                        setTimeout(function() {
                            waitForLeaflet(callback);
                        }, 100);
                    }
                }

                waitForLeaflet(function() {
                    if (!window.L || !window.L.map) {
                        console.error('Leaflet not loaded');
                        return null;
                    }

                    const mapContainer = document.getElementById(containerId);
                    if (!mapContainer) {
                        console.error('Map container not found:', containerId);
                        return null;
                    }

                    // Wait for container to be visible (important for modals)
                    function initMapWhenVisible() {
                        // Check if container still exists
                        const container = document.getElementById(containerId);
                        if (!container) {
                            return; // Container was removed, abort
                        }

                        // Check if container is visible
                        const isVisible = container.offsetWidth > 0 && container.offsetHeight > 0;
                        if (!isVisible) {
                            // Container not visible yet, try again after a short delay
                            const timeoutId = setTimeout(initMapWhenVisible, 200);
                            window._restPlaceMapTimeouts[containerId] = timeoutId;
                            return;
                        }

                        // Double-check container still exists before proceeding
                        const containerCheck = document.getElementById(containerId);
                        if (!containerCheck || containerCheck !== container) {
                            return; // Container changed or removed, abort
                        }

                        // Remove existing map if any
                        if (container._leaflet_id) {
                            try {
                                const existingMap = L.Map.prototype.getContainer.call({ _container: container });
                                if (existingMap && existingMap.remove) {
                                    existingMap.remove();
                                }
                            } catch (e) {
                                // Ignore errors when removing
                            }
                            container._leaflet_id = null;
                        }
                        container.innerHTML = '';

                        // Validate coordinates
                        if (isNaN(lat) || isNaN(lng) || lat < -90 || lat > 90 || lng < -180 || lng > 180) {
                            console.error('Invalid coordinates:', lat, lng);
                            return;
                        }

                        // Final check before initializing map
                        const finalContainer = document.getElementById(containerId);
                        if (!finalContainer || finalContainer !== container) {
                            return; // Container changed, abort
                        }

                        try {
                            // Initialize map with center and zoom first
                            const map = L.map(containerId, {
                                center: [lat, lng],
                                zoom: 10
                            });

                            // Add tile layer
                            L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
                                maxZoom: 19,
                                attribution: '&copy; OpenStreetMap contributors',
                            }).addTo(map);

                            // Add marker
                            const marker = L.marker([lat, lng]).addTo(map);
                            marker.bindPopup('<strong>' + placeName + '</strong><br><small>' + lat.toFixed(6) + ', ' + lng.toFixed(6) + '</small>').openPopup();

                            // Fix rendering after map is created
                            setTimeout(() => {
                                try {
                                    const mapCheck = document.getElementById(containerId);
                                    if (mapCheck && mapCheck._leaflet_id) {
                                        map.invalidateSize();
                                    }
                                } catch (e) {
                                    console.warn('Error invalidating map size:', e);
                                }
                            }, 300);
                        } catch (error) {
                            console.error('Error initializing map:', error);
                        }
                    }

                    initMapWhenVisible();
                });
            }

            // Handle rest place search (using event delegation for modals)
            document.addEventListener('click', async function(e) {
                if (!e.target.closest('.rest-place-search-btn')) return;
                
                const btn = e.target.closest('.rest-place-search-btn');
                const sessionId = btn.getAttribute('data-session-id');
                const placeIndex = btn.getAttribute('data-place-index');
                const input = document.querySelector(`input[data-session-id="${sessionId}"][data-place-index="${placeIndex}"]`);
                const errorEl = document.getElementById(`rest-place-error-${sessionId}-${placeIndex}`);
                
                if (!input || btn.disabled) return;
                
                const query = input.value.trim();
                if (!query) {
                    if (errorEl) {
                        errorEl.textContent = '{{ __('messages.please_enter_city_name') ?? 'Please enter a city or village name' }}';
                        errorEl.classList.remove('d-none');
                    }
                    return;
                }

                const originalHtml = btn.innerHTML;
                btn.disabled = true;
                btn.innerHTML = '<span class="spinner-border spinner-border-sm"></span>';

                if (errorEl) {
                    errorEl.classList.add('d-none');
                    errorEl.textContent = '';
                }

                try {
                    const result = await geocodeRestPlace(query);
                    
                    // Validate result structure
                    if (!result || typeof result !== 'object') {
                        throw new Error('Invalid geocoding result');
                    }
                    
                    if (!result.name || result.lat === undefined || result.lng === undefined) {
                        console.error('Invalid result structure:', result);
                        throw new Error('Geocoding returned invalid data');
                    }
                    
                    // Validate coordinates
                    const lat = parseFloat(result.lat);
                    const lng = parseFloat(result.lng);
                    
                    if (isNaN(lat) || isNaN(lng)) {
                        throw new Error('Invalid coordinates received');
                    }
                    
                    input.value = result.name;
                    if (errorEl) {
                        errorEl.classList.add('d-none');
                    }
                    
                    // Show map with location
                    const mapContainerId = `rest-place-map-${sessionId}-${placeIndex}`;
                    const mapContainer = document.getElementById(mapContainerId);
                    if (mapContainer) {
                        mapContainer.style.display = 'block';
                        initRestPlaceMap(mapContainerId, lat, lng, result.name);
                    }
                } catch (err) {
                    console.error('Geocoding error:', err);
                    if (errorEl) {
                        errorEl.textContent = err.message || '{{ __('messages.unable_to_find_location') ?? 'Unable to find this location' }}';
                        errorEl.classList.remove('d-none');
                    }
                } finally {
                    btn.disabled = false;
                    btn.innerHTML = originalHtml;
                }
            });

            // Function to update add button visibility
            function updateAddRestPlaceButton(sessionId) {
                const btn = document.querySelector(`.add-rest-place-btn[data-session-id="${sessionId}"]`);
                if (!btn) return;
                
                const maxPlaces = parseInt(btn.getAttribute('data-max-places')) || 0;
                const container = document.getElementById(`rest-places-container-${sessionId}`);
                if (!container) return;
                
                const currentItems = container.querySelectorAll('.rest-place-item');
                const currentCount = currentItems.length;
                
                if (currentCount >= maxPlaces) {
                    btn.style.display = 'none';
                } else {
                    btn.style.display = 'inline-block';
                }
            }

            // Update add rest place button when complete modal is shown
            const completeModal = document.getElementById('completeModal{{ $coachingCabine->id }}');
            if (completeModal) {
                completeModal.addEventListener('shown.bs.modal', function() {
                    const sessionId = '{{ $coachingCabine->id }}';
                    updateAddRestPlaceButton(sessionId);
                });
            }

            // Legacy handler (backup)
            document.querySelectorAll('.rest-place-search-btn').forEach(btn => {
                btn.addEventListener('click', async function() {
                    const sessionId = this.getAttribute('data-session-id');
                    const placeIndex = this.getAttribute('data-place-index');
                    const input = document.querySelector(`input[data-session-id="${sessionId}"][data-place-index="${placeIndex}"]`);
                    const errorEl = document.getElementById(`rest-place-error-${sessionId}-${placeIndex}`);
                    
                    if (!input) return;
                    
                    const query = input.value.trim();
                    if (!query) {
                        if (errorEl) {
                            errorEl.textContent = '{{ __('messages.please_enter_city_name') ?? 'Please enter a city or village name' }}';
                            errorEl.classList.remove('d-none');
                        }
                        return;
                    }

                    const originalHtml = this.innerHTML;
                    this.disabled = true;
                    this.innerHTML = '<span class="spinner-border spinner-border-sm"></span>';

                    if (errorEl) {
                        errorEl.classList.add('d-none');
                        errorEl.textContent = '';
                    }

                    try {
                        const result = await geocodeRestPlace(query);
                        
                        // Validate result structure
                        if (!result || typeof result !== 'object') {
                            throw new Error('Invalid geocoding result');
                        }
                        
                        if (!result.name || result.lat === undefined || result.lng === undefined) {
                            console.error('Invalid result structure:', result);
                            throw new Error('Geocoding returned invalid data');
                        }
                        
                        // Validate coordinates
                        const lat = parseFloat(result.lat);
                        const lng = parseFloat(result.lng);
                        
                        if (isNaN(lat) || isNaN(lng)) {
                            throw new Error('Invalid coordinates received');
                        }
                        
                        input.value = result.name;
                        if (errorEl) {
                            errorEl.classList.add('d-none');
                        }
                        
                        // Show map with location
                        const mapContainerId = `rest-place-map-${sessionId}-${placeIndex}`;
                        const mapContainer = document.getElementById(mapContainerId);
                        if (mapContainer) {
                            mapContainer.style.display = 'block';
                            initRestPlaceMap(mapContainerId, lat, lng, result.name);
                        }
                    } catch (err) {
                        console.error('Geocoding error:', err);
                        if (errorEl) {
                            errorEl.textContent = err.message || '{{ __('messages.unable_to_find_location') ?? 'Unable to find this location' }}';
                            errorEl.classList.remove('d-none');
                        }
                    } finally {
                        this.disabled = false;
                        this.innerHTML = originalHtml;
                    }
                });
            });

            // Handle add rest place button
            document.querySelectorAll('.add-rest-place-btn').forEach(btn => {
                btn.addEventListener('click', function() {
                    const sessionId = this.getAttribute('data-session-id');
                    const container = document.getElementById(`rest-places-container-${sessionId}`);
                    if (!container) return;
                    
                    const maxPlaces = parseInt(this.getAttribute('data-max-places')) || parseInt(container.getAttribute('data-max-places')) || 0;
                    const currentItems = container.querySelectorAll('.rest-place-item');
                    const currentCount = currentItems.length;
                    
                    if (currentCount >= maxPlaces) {
                        alert('{{ __('messages.rest_places_max_reached') ?? 'Maximum number of rest places reached' }}');
                        return;
                    }
                    
                    const newIndex = currentCount;
                    const dayNumber = newIndex + 1;
                    
                    const newItem = document.createElement('div');
                    newItem.className = 'rest-place-item mb-2';
                    newItem.setAttribute('data-index', newIndex);
                    newItem.innerHTML = `
                        <div class="input-group input-group-sm">
                            <span class="input-group-text">{{ __('messages.day') ?? 'Day' }} ${dayNumber}</span>
                            <input type="text" 
                                   name="rest_places[]" 
                                   class="form-control rest-place-input" 
                                   value=""
                                   placeholder="{{ __('messages.rest_place_placeholder') ?? 'Enter city or village name' }}"
                                   data-session-id="${sessionId}"
                                   data-place-index="${newIndex}">
                            <button type="button" 
                                    class="btn btn-outline-primary rest-place-search-btn" 
                                    data-session-id="${sessionId}"
                                    data-place-index="${newIndex}"
                                    title="{{ __('messages.search_rest_place_city') ?? 'Search city' }}">
                                <i class="bi bi-search"></i>
                            </button>
                            <button type="button" 
                                    class="btn btn-outline-danger remove-rest-place-btn" 
                                    data-session-id="${sessionId}"
                                    title="{{ __('messages.remove_rest_place') ?? 'Remove' }}">
                                <i class="bi bi-trash"></i>
                            </button>
                        </div>
                        <div class="rest-place-error text-danger small mt-1 d-none" id="rest-place-error-${sessionId}-${newIndex}"></div>
                        <div class="rest-place-map-container mt-2" id="rest-place-map-${sessionId}-${newIndex}" style="height: 200px; width: 100%; background: #f5f5f5; display: none;"></div>
                    `;
                    
                    container.appendChild(newItem);
                    
                    // Attach event listeners to new elements
                    const newSearchBtn = newItem.querySelector('.rest-place-search-btn');
                    if (newSearchBtn) {
                        newSearchBtn.addEventListener('click', async function() {
                            const placeIndex = this.getAttribute('data-place-index');
                            const input = document.querySelector(`input[data-session-id="${sessionId}"][data-place-index="${placeIndex}"]`);
                            const errorEl = document.getElementById(`rest-place-error-${sessionId}-${placeIndex}`);
                            
                            if (!input) return;
                            
                            const query = input.value.trim();
                            if (!query) {
                                if (errorEl) {
                                    errorEl.textContent = '{{ __('messages.please_enter_city_name') ?? 'Please enter a city or village name' }}';
                                    errorEl.classList.remove('d-none');
                                }
                                return;
                            }

                            const originalHtml = this.innerHTML;
                            this.disabled = true;
                            this.innerHTML = '<span class="spinner-border spinner-border-sm"></span>';

                            if (errorEl) {
                                errorEl.classList.add('d-none');
                                errorEl.textContent = '';
                            }

                            try {
                                const result = await geocodeRestPlace(query);
                                
                                // Validate result structure
                                if (!result || typeof result !== 'object') {
                                    throw new Error('Invalid geocoding result');
                                }
                                
                                if (!result.name || result.lat === undefined || result.lng === undefined) {
                                    console.error('Invalid result structure:', result);
                                    throw new Error('Geocoding returned invalid data');
                                }
                                
                                // Validate coordinates
                                const lat = parseFloat(result.lat);
                                const lng = parseFloat(result.lng);
                                
                                if (isNaN(lat) || isNaN(lng)) {
                                    throw new Error('Invalid coordinates received');
                                }
                                
                                input.value = result.name;
                                
                                // Show map with location
                                const mapContainerId = `rest-place-map-${sessionId}-${placeIndex}`;
                                const mapContainer = document.getElementById(mapContainerId);
                                if (mapContainer) {
                                    mapContainer.style.display = 'block';
                                    initRestPlaceMap(mapContainerId, lat, lng, result.name);
                                }
                                
                                if (errorEl) {
                                    errorEl.classList.add('d-none');
                                }
                            } catch (err) {
                                console.error('Geocoding error:', err);
                                if (errorEl) {
                                    errorEl.textContent = err.message || '{{ __('messages.unable_to_find_location') ?? 'Unable to find this location' }}';
                                    errorEl.classList.remove('d-none');
                                }
                            } finally {
                                this.disabled = false;
                                this.innerHTML = originalHtml;
                            }
                        });
                    }
                    
                    const newRemoveBtn = newItem.querySelector('.remove-rest-place-btn');
                    if (newRemoveBtn) {
                        newRemoveBtn.addEventListener('click', function() {
                            const item = this.closest('.rest-place-item');
                            if (item) {
                                item.remove();
                                updateDayLabels(sessionId);
                                updateAddRestPlaceButton(sessionId);
                            }
                        });
                    }
                    
                    updateDayLabels(sessionId);
                    updateAddRestPlaceButton(sessionId);
                });
            });

            // Handle remove rest place (using event delegation)
            document.addEventListener('click', function(e) {
                if (e.target.closest('.remove-rest-place-btn')) {
                    const btn = e.target.closest('.remove-rest-place-btn');
                    const sessionId = btn.getAttribute('data-session-id');
                    const item = btn.closest('.rest-place-item');
                    if (item) {
                        item.remove();
                        updateDayLabels(sessionId);
                        updateAddRestPlaceButton(sessionId);
                    }
                }
            });

            // Update day labels after removal
            function updateDayLabels(sessionId) {
                const container = document.getElementById(`rest-places-container-${sessionId}`);
                if (!container) return;
                
                const items = container.querySelectorAll('.rest-place-item');
                items.forEach((item, index) => {
                    const label = item.querySelector('.input-group-text');
                    if (label) {
                        label.textContent = '{{ __('messages.day') ?? 'Day' }} ' + (index + 1);
                    }
                    const input = item.querySelector('input');
                    if (input) {
                        input.setAttribute('data-place-index', index);
                    }
                    const searchBtn = item.querySelector('.rest-place-search-btn');
                    if (searchBtn) {
                        searchBtn.setAttribute('data-place-index', index);
                    }
                    const errorEl = item.querySelector('.rest-place-error');
                    if (errorEl) {
                        errorEl.id = `rest-place-error-${sessionId}-${index}`;
                    }
                });
            }
        });

        // PDF Export with Map Screenshot
        document.addEventListener('DOMContentLoaded', function() {
            const exportPdfBtn = document.getElementById('exportPdfBtn');
            if (!exportPdfBtn) return;

            // Load html2canvas library
            if (!window.html2canvas) {
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
                exportPdfBtn.addEventListener('click', function() {
                    const pdfUrl = this.getAttribute('data-pdf-url');
                    const mapContainer = document.getElementById('route-map');

                    // If no map container, just redirect to PDF
                    if (!mapContainer) {
                        window.location.href = pdfUrl;
                        return;
                    }

                    // Disable button
                    this.disabled = true;
                    const originalHtml = this.innerHTML;
                    this.innerHTML = '<span class="spinner-border spinner-border-sm me-1"></span> {{ __('messages.generating_pdf') ?? 'Génération...' }}';

                    // Wait for route to be fully rendered
                    function waitForRoute(callback, maxAttempts = 30) {
                        let attempts = 0;
                        const checkRoute = function() {
                            attempts++;
                            // Check if route line exists in the map
                            // Leaflet draws routes as SVG paths, so check for SVG elements
                            const svgElements = mapContainer.querySelectorAll('svg');
                            const hasRoute = svgElements.length > 0 && 
                                           (window.routeLine !== null || 
                                            mapContainer.querySelector('svg path') !== null);
                            
                            if (hasRoute || attempts >= maxAttempts) {
                                // Additional wait to ensure everything is fully rendered (tiles, route, markers)
                                setTimeout(callback, 3000); // Wait 3 more seconds for full rendering
                            } else {
                                setTimeout(checkRoute, 500); // Check every 500ms
                            }
                        };
                        // Start checking after initial delay
                        setTimeout(checkRoute, 1000);
                    }

                    // Start waiting for route
                    waitForRoute(function() {
                        // Capture the map as image with high quality
                        html2canvas(mapContainer, {
                            backgroundColor: '#ffffff',
                            scale: 3, // Very high quality (3x for excellent resolution)
                            logging: false,
                            useCORS: true,
                            allowTaint: false,
                            width: mapContainer.offsetWidth,
                            height: mapContainer.offsetHeight,
                            windowWidth: mapContainer.offsetWidth,
                            windowHeight: mapContainer.offsetHeight,
                            onclone: function(clonedDoc) {
                                // Ensure map is fully visible in cloned document
                                const clonedMap = clonedDoc.getElementById('route-map');
                                if (clonedMap) {
                                    clonedMap.style.visibility = 'visible';
                                    clonedMap.style.display = 'block';
                                }
                            }
                        }).then(function(canvas) {
                            // Convert canvas to base64
                            const mapImageBase64 = canvas.toDataURL('image/png', 1.0);

                            // Create a form to submit the image
                            const form = document.createElement('form');
                            form.method = 'POST';
                            form.action = pdfUrl;
                            
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

                            // Submit form
                            document.body.appendChild(form);
                            form.submit();
                        }).catch(function(error) {
                            console.error('Error capturing map:', error);
                            // Fallback: redirect to PDF without map image
                            window.location.href = pdfUrl;
                        }).finally(function() {
                            // Re-enable button
                            exportPdfBtn.disabled = false;
                            exportPdfBtn.innerHTML = originalHtml;
                        });
                    });
                });
            }
        });
    </script>
    @endpush
</x-app-layout>

