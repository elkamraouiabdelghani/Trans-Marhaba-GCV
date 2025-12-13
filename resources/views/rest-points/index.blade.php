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
                <div class="d-flex flex-column flex-md-row justify-content-between align-items-start align-items-md-center mb-2">
                    <h2 class="mb-0 fw-bold text-dark fs-4">
                        <i class="bi bi-geo-alt-fill me-2 text-primary"></i>
                        {{ __('messages.rest_points') ?? 'Rest Areas' }}
                    </h2>
                    <div class="d-flex flex-column flex-md-row gap-2 gap-md-3 mt-2 mt-md-0">
                        <button type="button" id="exportPdfBtn" class="btn btn-sm btn-danger">
                            <i class="bi bi-file-earmark-pdf me-2"></i>
                            {{ __('messages.export_pdf') ?? 'Export Map PDF' }}
                        </button>
                        <a href="{{ route('rest-points.export', $filters) }}" class="btn btn-sm btn-success">
                            <i class="bi bi-file-earmark-excel me-2"></i>
                            {{ __('messages.export_excel') ?? 'Export to Excel' }}
                        </a>
                        <a href="{{ route('rest-points.planning') }}" class="btn btn-sm btn-info">
                            <i class="bi bi-calendar-check me-2"></i>
                            {{ __('messages.planning') ?? 'Planning' }}
                        </a>
                        <a href="{{ route('rest-points.create') }}" class="btn btn-sm btn-dark">
                            <i class="bi bi-plus-circle me-2"></i>
                            {{ __('messages.add_rest_point') ?? 'Add Rest Area' }}
                        </a>
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
                    {{ __('messages.rest_points') ?? 'Rest Areas' }}
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
                                <th>{{ __('messages.last_inspection') ?? 'Last inspection' }}</th>
                                <th>{{ __('messages.next_inspection_due') ?? 'Next due' }}</th>
                                <th>{{ __('messages.created_at') ?? 'Created At' }}</th>
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
                                        @if($restPoint->last_inspection_date)
                                            <div class="d-flex flex-column">
                                                <span class="text-muted">
                                                    {{ $restPoint->last_inspection_date->format('d/m/Y') }}
                                                </span>
                                                @php
                                                    $status = $restPoint->inspection_status;
                                                    $badgeClass = $status === 'overdue' ? 'bg-danger' : 'bg-success';
                                                    $label = $status === 'overdue'
                                                        ? __('messages.overdue') ?? 'Overdue'
                                                        : __('messages.on_time') ?? 'On time';
                                                @endphp
                                                <span class="badge {{ $badgeClass }} mt-1">
                                                    {{ $label }}
                                                </span>
                                            </div>
                                        @else
                                            <span class="badge bg-secondary">
                                                {{ __('messages.no_inspection') ?? 'No inspection' }}
                                            </span>
                                        @endif
                                    </td>
                                    <td>
                                        @if($restPoint->next_inspection_due_at)
                                            <span class="text-muted">
                                                {{ $restPoint->next_inspection_due_at->format('d/m/Y') }}
                                            </span>
                                        @else
                                            <span class="text-muted">-</span>
                                        @endif
                                    </td>
                                    <td>
                                        <span class="text-muted">
                                            {{ $restPoint->created_at->format('d/m/Y H:i') }}
                                        </span>
                                    </td>
                                    <td class="text-end pe-3">
                                        <div class="btn-group" role="group">
                                            <a href="{{ route('rest-points.show', $restPoint) }}"
                                               class="btn btn-sm btn-outline-primary"
                                               title="{{ __('messages.view') ?? 'View' }}">
                                                <i class="bi bi-eye"></i>
                                            </a>
                                            <a href="{{ route('rest-points.edit', $restPoint) }}"
                                               class="btn btn-sm btn-outline-warning"
                                               title="{{ __('messages.edit') ?? 'Edit' }}">
                                                <i class="bi bi-pencil"></i>
                                            </a>
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="6" class="text-center py-5 text-muted">
                                        <i class="bi bi-inbox fs-1 d-block mb-2"></i>
                                        {{ __('messages.no_rest_points_found') ?? 'No rest areas found' }}
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
    // ========== Main Map (Index Page) ==========
    const mapContainer = document.getElementById('rest-points-map');
    if (mapContainer) {
        const map = L.map(mapContainer).setView([33.5731, -7.5898], 7);
        L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
            maxZoom: 19,
            attribution: '&copy; OpenStreetMap contributors',
        }).addTo(map);

        const restPoints = @json($allRestPoints);
        function createCustomIcon(type) {
            const color = '#28a745'; // same green for all rest points
            return L.divIcon({
                className: 'custom-marker',
                html: `
                    <div class="custom-marker-pin" style="background-color: ${color};">
                        <span class="custom-marker-letter">P</span>
                    </div>
                `,
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

.custom-marker-pin {
    width: 30px;
    height: 30px;
    border-radius: 50% 50% 50% 0;
    transform: rotate(-45deg);
    border: 3px solid #ffffff;
    box-shadow: 0 2px 5px rgba(0, 0, 0, 0.3);
    position: relative;
    display: flex;
    align-items: center;
    justify-content: center;
}

.custom-marker-letter {
    transform: rotate(45deg);
    color: #ffffff;
    font-weight: 700;
    font-size: 14px;
}
</style>

