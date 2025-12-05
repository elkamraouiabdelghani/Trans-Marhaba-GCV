<x-app-layout>
    <x-slot name="header">
        @include('layouts.topnav')
    </x-slot>

    <div class="container-fluid py-4 mt-4">
        @if (session('success'))
            <div class="alert alert-success alert-dismissible fade show" role="alert">
                {{ session('success') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        @endif

        @if (session('error'))
            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                {{ session('error') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        @endif

        <!-- Header -->
        <div class="d-flex flex-column flex-md-row justify-content-between align-items-start align-items-md-center mb-4 bg-white p-4 rounded-3 shadow-sm">
            <h2 class="mb-0 fw-bold text-dark fs-4">
                <i class="bi bi-signpost-split me-2 text-primary"></i>
                {{ __('messages.journeys') ?? 'Journeys' }}
            </h2>
            <div class="d-flex flex-column flex-md-row gap-2 gap-md-3 mt-2 mt-md-0">
                <button type="button" id="exportPdfBtn" class="btn btn-sm btn-danger">
                    <i class="bi bi-file-earmark-pdf me-2"></i>
                    {{ __('messages.export_pdf') ?? 'Export Map PDF' }}
                </button>
                <a href="{{ route('journeys.planning') }}" class="btn btn-sm btn-info">
                    <i class="bi bi-calendar3 me-2"></i>
                    {{ __('messages.planning') ?? 'Planning' }}
                </a>
                <a href="{{ route('journeys.export', request()->query()) }}" class="btn btn-sm btn-success">
                    <i class="bi bi-file-earmark-excel me-2"></i>
                    {{ __('messages.export_excel') ?? 'Export to Excel' }}
                </a>
                <a href="{{ route('journeys.create') }}" class="btn btn-sm btn-dark">
                    <i class="bi bi-plus-circle me-2"></i>
                    {{ __('messages.add_journey') ?? 'Add Journey' }}
                </a>
            </div>
        </div>

        <!-- Filters -->
        <div class="card border-0 shadow-sm mb-4">
            <div class="card-body p-4">
                <form action="{{ route('journeys.index') }}" method="GET">
                    <div class="row g-3 align-items-end">
                        <div class="col-12 col-md-4">
                            <label for="journey_id" class="form-label fw-semibold">{{ __('messages.journey') ?? 'Journey' }}</label>
                            <select name="journey_id" id="journey_id" class="form-select">
                                <option value="">{{ __('messages.all_journeys') ?? 'All Journeys' }}</option>
                                @foreach($allJourneysForFilter ?? [] as $journey)
                                    <option value="{{ $journey->id }}" @selected(($filters['journey_id'] ?? '') == $journey->id)>
                                        {{ $journey->name }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-12 col-md-2">
                            <div class="d-flex gap-2">
                                <button type="submit" class="btn btn-dark w-100">
                                    <i class="bi bi-funnel me-1"></i>{{ __('messages.filter') ?? 'Filter' }}
                                </button>
                                <a href="{{ route('journeys.index') }}" class="btn btn-outline-secondary">
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
                <h5 class="mb-0 fw-semibold">
                    <i class="bi bi-geo-alt me-2 text-primary"></i>
                    {{ __('messages.journeys_map') ?? 'Journeys Map' }}
                </h5>
            </div>
            <div class="card-body p-0">
                <div id="journeys-map" style="height: 500px; width: 100%;"></div>
            </div>
        </div>

        <!-- Journeys Table -->
        <div class="card border-0 shadow-sm">
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover mb-0 align-middle">
                        <thead class="table-light">
                            <tr>
                                <th class="ps-3">{{ __('messages.name') ?? 'Name' }}</th>
                                <th class="ps-3">{{ __('messages.from_location') ?? 'From Location' }}</th>
                                <th class="ps-3">{{ __('messages.to_location') ?? 'To Location' }}</th>
                                <th class="text-center">{{ __('messages.total_score') ?? 'Total Score' }}</th>
                                <th class="text-center">{{ __('messages.status') ?? 'Status' }}</th>
                                <th>{{ __('messages.created_at') ?? 'Created At' }}</th>
                                <th class="text-end pe-3">{{ __('messages.actions') ?? 'Actions' }}</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($journeys as $journey)
                                <tr>
                                    <td class="ps-3">
                                        <div class="fw-semibold">{{ $journey->name }}</div>
                                    </td>
                                    <td class="ps-3">
                                        <span class="d-block">
                                            {{ $journey->from_location_name ?? '-' }}
                                        </span>
                                        <small class="text-muted">
                                            {{ $journey->from_latitude ?? '-' }}, {{ $journey->from_longitude ?? '-' }}
                                        </small>
                                    </td>
                                    <td class="ps-3">
                                        <span class="d-block">
                                            {{ $journey->to_location_name ?? '-' }}
                                        </span>
                                        <small class="text-muted">
                                            {{ $journey->to_latitude ?? '-' }}, {{ $journey->to_longitude ?? '-' }}
                                        </small>
                                    </td>
                                    <td class="text-center">
                                        <span class="badge bg-primary">
                                            {{ number_format($journey->total_score, 2) }}
                                        </span>
                                    </td>
                                    <td class="text-center">
                                        @php
                                            $status = $journey->status;
                                            $statusClasses = [
                                                'excellent' => 'bg-success',
                                                'good' => 'bg-info',
                                                'average' => 'bg-warning',
                                                'less' => 'bg-danger',
                                            ];
                                            $statusClass = $statusClasses[$status] ?? 'bg-secondary';
                                        @endphp
                                        <span class="badge {{ $statusClass }}">
                                            {{ __('messages.journey_status_' . $status) ?? ucfirst($status) }}
                                        </span>
                                    </td>
                                    <td>
                                        <span class="text-muted">
                                            {{ $journey->created_at->format('d/m/Y H:i') }}
                                        </span>
                                    </td>
                                    <td class="text-end pe-3">
                                        <div class="btn-group" role="group">
                                            <a href="{{ route('journeys.show', $journey) }}"
                                               class="btn btn-sm btn-outline-primary"
                                               title="{{ __('messages.view') ?? 'View' }}">
                                                <i class="bi bi-eye"></i>
                                            </a>
                                            <a href="{{ route('journeys.edit', $journey) }}"
                                               class="btn btn-sm btn-outline-warning"
                                               title="{{ __('messages.edit') ?? 'Edit' }}">
                                                <i class="bi bi-pencil"></i>
                                            </a>
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="7" class="text-center py-5 text-muted">
                                        <i class="bi bi-inbox fs-1 d-block mb-2"></i>
                                        {{ __('messages.no_journeys_found') ?? 'No journeys found' }}
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
            @if($journeys->hasPages())
                <div class="card-footer bg-white border-0 py-3">
                    <div class="d-flex justify-content-center">
                        {{ $journeys->links() }}
                    </div>
                </div>
            @endif
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
        box-shadow: 0 2px 4px rgba(0,0,0,0.3);
    }
    .custom-marker-letter {
        transform: rotate(45deg);
        color: white;
        font-weight: bold;
        font-size: 14px;
    }
</style>

<script>
document.addEventListener('DOMContentLoaded', function () {
    // ========== Main Map (Index Page) ==========
    const mapContainer = document.getElementById('journeys-map');
    if (mapContainer) {
        const map = L.map(mapContainer).setView([33.5731, -7.5898], 7);
        L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
            maxZoom: 19,
            attribution: '&copy; OpenStreetMap contributors',
        }).addTo(map);

        const journeys = @json($allJourneys);
        const isFiltered = @json($isFiltered ?? false);
        const routeLines = [];
        const allMarkers = [];
        const blackPointMarkers = [];

        // Helper: fetch and draw professional road route using OSRM
        async function fetchRouteAndDraw(fromLat, fromLng, toLat, toLng, journey) {
            const journeyId = journey.id;
            const journeyName = journey.name || '{{ __('messages.journey') ?? 'Journey' }}';
            
            // Create popup content for the route line
            const routePopupContent = `
                <div class="p-2">
                    <h6 class="fw-bold mb-2 text-primary">${journeyName}</h6>
                    <a href="{{ url('journeys') }}/${journeyId}" class="btn btn-sm btn-primary mt-2 w-100 text-white">{{ __('messages.view') ?? 'View' }}</a>
                </div>
            `;

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

                // Shadow (below)
                const routeShadow = L.polyline(coordinates, {
                    color: '#1e40af',
                    weight: 4,
                    opacity: 0.2,
                    lineJoin: 'round',
                    lineCap: 'round',
                }).addTo(map);

                // Main route (on top) - make it interactive
                const routeLine = L.polyline(coordinates, {
                    color: '#2563eb',
                    weight: 3,
                    opacity: 0.6,
                    lineJoin: 'round',
                    lineCap: 'round',
                }).addTo(map);

                // Bind popup to the route line
                routeLine.bindPopup(routePopupContent);
                
                // Make the route line more interactive (cursor pointer on hover)
                routeLine.on('mouseover', function() {
                    this.setStyle({ weight: 5, opacity: 0.8 });
                });
                routeLine.on('mouseout', function() {
                    this.setStyle({ weight: 3, opacity: 0.6 });
                });

                routeLines.push({ shadow: routeShadow, line: routeLine, journeyId: journeyId });
            } catch (error) {
                console.warn('Failed to fetch OSRM route for journey ' + journeyId + ', using straight line:', error);

                // Fallback: simple straight line
                const routeLine = L.polyline([[fromLat, fromLng], [toLat, toLng]], {
                    color: '#2563eb',
                    weight: 2,
                    opacity: 0.5,
                    dashArray: '10,5',
                }).addTo(map);

                // Bind popup to the fallback route line
                routeLine.bindPopup(routePopupContent);
                
                // Make the route line more interactive (cursor pointer on hover)
                routeLine.on('mouseover', function() {
                    this.setStyle({ weight: 4, opacity: 0.7 });
                });
                routeLine.on('mouseout', function() {
                    this.setStyle({ weight: 2, opacity: 0.5 });
                });

                routeLines.push({ shadow: null, line: routeLine, journeyId: journeyId });
            }
        }

        // Process each journey
        journeys.forEach(function(journey) {
            if (journey.from_latitude && journey.from_longitude && journey.to_latitude && journey.to_longitude) {
                const fromLat = parseFloat(journey.from_latitude);
                const fromLng = parseFloat(journey.from_longitude);
                const toLat = parseFloat(journey.to_latitude);
                const toLng = parseFloat(journey.to_longitude);

                // From marker
                const fromLocationName = journey.from_location_name || '';
                let fromPopupContent = '<div class="p-2">';
                fromPopupContent += '<h6 class="fw-bold mb-2">{{ __('messages.from_location') ?? 'From Location' }}</h6>';
                if (fromLocationName) {
                    fromPopupContent += '<p class="mb-1"><strong class="text-primary">' + fromLocationName + '</strong></p>';
                }
                fromPopupContent += '<p class="mb-1"><strong>{{ __('messages.journey') ?? 'Journey' }}:</strong> ' + (journey.name || '-') + '</p>';
                fromPopupContent += '<p class="mb-0 text-muted small"><strong>{{ __('messages.coordinates') ?? 'Coordinates' }}:</strong><br>' + fromLat.toFixed(6) + ', ' + fromLng.toFixed(6) + '</p>';
                fromPopupContent += '<a href="{{ url('journeys') }}/' + journey.id + '" class="btn btn-sm btn-primary mt-2 w-100">{{ __('messages.view') ?? 'View' }}</a>';
                fromPopupContent += '</div>';

                const fromMarker = L.marker([fromLat, fromLng], {
                    icon: L.divIcon({
                        className: 'custom-marker',
                        html: '<div class="custom-marker-pin" style="background-color: #2563eb;"><span class="custom-marker-letter">F</span></div>',
                        iconSize: [30, 30],
                        iconAnchor: [15, 30],
                        popupAnchor: [0, -30]
                    })
                }).addTo(map).bindPopup(fromPopupContent);

                // To marker
                const toLocationName = journey.to_location_name || '';
                let toPopupContent = '<div class="p-2">';
                toPopupContent += '<h6 class="fw-bold mb-2">{{ __('messages.to_location') ?? 'To Location' }}</h6>';
                if (toLocationName) {
                    toPopupContent += '<p class="mb-1"><strong class="text-primary">' + toLocationName + '</strong></p>';
                }
                toPopupContent += '<p class="mb-1"><strong>{{ __('messages.journey') ?? 'Journey' }}:</strong> ' + (journey.name || '-') + '</p>';
                toPopupContent += '<p class="mb-0 text-muted small"><strong>{{ __('messages.coordinates') ?? 'Coordinates' }}:</strong><br>' + toLat.toFixed(6) + ', ' + toLng.toFixed(6) + '</p>';
                toPopupContent += '<a href="{{ url('journeys') }}/' + journey.id + '" class="btn btn-sm btn-primary mt-2 w-100">{{ __('messages.view') ?? 'View' }}</a>';
                toPopupContent += '</div>';

                const toMarker = L.marker([toLat, toLng], {
                    icon: L.divIcon({
                        className: 'custom-marker',
                        html: '<div class="custom-marker-pin" style="background-color: #2563eb;"><span class="custom-marker-letter">T</span></div>',
                        iconSize: [30, 30],
                        iconAnchor: [15, 30],
                        popupAnchor: [0, -30]
                    })
                }).addTo(map).bindPopup(toPopupContent);

                allMarkers.push(fromMarker, toMarker);

                // Draw route - pass the entire journey object
                fetchRouteAndDraw(fromLat, fromLng, toLat, toLng, journey);

                // Add black points markers only when filtering by specific journey
                // Check both snake_case and camelCase (Laravel JSON serialization)
                const blackPoints = journey.black_points || journey.blackPoints || [];
                if (isFiltered && blackPoints.length > 0) {
                    blackPoints.forEach(function(point) {
                        const iconColor = '#dc3545'; // Red color for black points
                        
                        const marker = L.marker([parseFloat(point.latitude), parseFloat(point.longitude)], {
                            icon: L.divIcon({
                                className: 'custom-marker',
                                html: `<div class="custom-marker-pin" style="background-color: ${iconColor};"><i class="bi bi-exclamation-triangle-fill"></i></div>`,
                                iconSize: [30, 30],
                                iconAnchor: [15, 30],
                                popupAnchor: [0, -30]
                            })
                        }).addTo(map);
                        
                        const popupContent = `
                            <div class="p-2">
                                <h6 class="fw-bold mb-2">${point.name || '{{ __('messages.black_point') ?? 'Black Point' }}'}</h6>
                                ${point.description ? `<p class="mb-1">${point.description}</p>` : ''}
                                <p class="mb-0 text-muted small">
                                    <strong>{{ __('messages.coordinates') ?? 'Coordinates' }}:</strong><br>
                                    ${parseFloat(point.latitude).toFixed(6)}, ${parseFloat(point.longitude).toFixed(6)}
                                </p>
                            </div>
                        `;
                        
                        marker.bindPopup(popupContent);
                        blackPointMarkers.push(marker);
                        allMarkers.push(marker);
                    });
                }
            }
        });

        // Fit map to show all markers (including black points if filtered)
        if (allMarkers.length > 0) {
            const bounds = allMarkers.map(m => m.getLatLng());
            if (bounds.length > 0) {
                if (bounds.length === 1) {
                    map.setView(bounds[0], 13);
                } else {
                    const group = new L.featureGroup(allMarkers);
                    map.fitBounds(group.getBounds().pad(0.1));
                }
            }
        }
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
        const mapContainer = document.getElementById('journeys-map');
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
                form.action = '{{ route("journeys.export-pdf", $filters) }}';
                
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
                @if(request()->filled('journey_id'))
                const journeyIdInput = document.createElement('input');
                journeyIdInput.type = 'hidden';
                journeyIdInput.name = 'journey_id';
                journeyIdInput.value = '{{ request()->input("journey_id") }}';
                form.appendChild(journeyIdInput);
                @endif

                document.body.appendChild(form);
                form.submit();
            }).catch(function(error) {
                console.error('Error capturing map:', error);
                alert('{{ __('messages.error_capturing_map') ?? 'Error capturing map. Please try again.' }}');
                btn.disabled = false;
                btn.innerHTML = originalText;
            });
        }, 2000); // Wait 1000ms for map to render
    });
}
</script>

