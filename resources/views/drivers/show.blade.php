<x-app-layout>
    <x-slot name="header">
        @include('layouts.topnav')
    </x-slot>

    <div class="container-fluid py-4">
        <!-- Breadcrumb -->
        <nav aria-label="breadcrumb" class="mb-4">
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">{{ __('messages.dashboard') }}</a></li>
                <li class="breadcrumb-item"><a href="{{ route('drivers.index') }}">{{ __('messages.drivers') }}</a></li>
                <li class="breadcrumb-item active">{{ $driver->full_name ?? __('messages.driver_number') . $driver->id }}</li>
            </ol>
        </nav>

        <!-- Driver Information Box -->
        <div class="card border-0 shadow-sm mb-4">
            <div class="card-body p-4">
                <div class="row align-items-center">
                    <div class="col-md-3 text-center mb-3 mb-md-0">
                        <div class="position-relative d-inline-block">
                            <div class="bg-primary bg-opacity-10 rounded-circle d-flex align-items-center justify-content-center" style="width: 120px; height: 120px;">
                                <i class="bi bi-person-fill text-primary" style="font-size: 4rem;"></i>
                            </div>
                            @if($driver->isActive())
                                <span class="badge bg-success position-absolute top-0 end-0 rounded-circle d-flex align-items-center justify-content-center" style="width: 24px; height: 24px; padding: 0;">
                                    <i class="bi bi-check"></i>
                                </span>
                            @endif
                        </div>
                    </div>
                    <div class="col-md-9">
                        <div class="row g-3">
                                    <div class="col-md-6">
                                        <h3 class="mb-2 text-dark fw-bold">{{ $driver->full_name ?? __('messages.driver_number') . $driver->id }}</h3>
                                        <div class="d-flex flex-wrap gap-3 mb-3">
                                            <div>
                                                <small class="text-muted d-block">{{ __('messages.license_number') }}</small>
                                                <strong class="text-dark">#{{ $driver->id ?? 'N/A' }}</strong>
                                            </div>
                                            <div>
                                                <small class="text-muted d-block">{{ __('messages.nationality') }}</small>
                                                <strong class="text-dark">N/A</strong>
                                            </div>
                                        </div>
                                    </div>
                            <div class="col-md-6">
                                <div class="row g-3">
                                    <div class="col-6">
                                        <div class="bg-info bg-opacity-10 rounded p-3 text-center h-100">
                                            <i class="bi bi-clock-history text-info fs-4 d-block mb-2"></i>
                                            <small class="text-muted d-block">{{ __('messages.driving_hours') }}</small>
                                            <h4 class="mb-0 fw-bold text-dark">{{ $totalDrivingHoursThisWeek }}h</h4>
                                            <small class="text-muted">{{ __('messages.this_week') }}</small>
                                        </div>
                                    </div>
                                    <div class="col-6">
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
                        <div class="mt-3 pt-3 border-top">
                            <div class="row g-3">
                                <div class="col-md-4">
                                    <small class="text-muted d-block">{{ __('messages.phone') }}</small>
                                    <span class="text-dark">
                                        {{ $driver->phone ?? $driver->phone_number ?? $driver->phone_numbre ?? 'N/A' }}
                                    </span>
                                </div>
                                <div class="col-md-4">
                                    <small class="text-muted d-block">{{ __('messages.assigned_vehicle') }}</small>
                                    <span class="text-dark">
                                        @if($driver->assignedVehicle && $driver->assignedVehicle->license_plate)
                                            {{ $driver->assignedVehicle->license_plate }}
                                        @else
                                            {{ $driver->vehicle_matricule ?? $driver->matricule ?? __('messages.not_assigned') }}
                                        @endif
                                    </span>
                                </div>
                                <div class="col-md-4">
                                    <small class="text-muted d-block">{{ __('messages.flotte') }}</small>
                                    <span class="text-dark">
                                        {{ $driver->flotte->name ?? 'N/A' }}
                                    </span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
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
                            <select name="violation_type" class="form-select form-select-sm">
                                <option value="">{{ __('messages.all_types') }}</option>
                                @foreach($violationTypes as $key => $label)
                                    <option value="{{ $key }}" {{ $violationType === $key ? 'selected' : '' }}>
                                        {{ $label }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-2">
                            <label class="form-label small text-muted">{{ __('messages.severity') }}</label>
                            <select name="severity" class="form-select form-select-sm">
                                <option value="">{{ __('messages.all_severities') }}</option>
                                @foreach($severityOptions as $key => $label)
                                    <option value="{{ $key }}" {{ $severity === $key ? 'selected' : '' }}>
                                        {{ $label }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label small text-muted">{{ __('messages.date_from') }}</label>
                            <input type="date" name="date_from" class="form-control form-control-sm" value="{{ $dateFrom }}">
                        </div>
                        <div class="col-md-3">
                            <label class="form-label small text-muted">{{ __('messages.date_to') }}</label>
                            <input type="date" name="date_to" class="form-control form-control-sm" value="{{ $dateTo }}">
                        </div>
                        <div class="col-md-1">
                            <label class="form-label small text-muted d-block">&nbsp;</label>
                            <button type="submit" class="btn btn-primary btn-sm w-100">
                                <i class="bi bi-search"></i>
                            </button>
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
                    <div class="d-flex gap-2">
                        <button class="btn btn-sm btn-outline-primary" onclick="exportTimelinePDF()">
                            <i class="bi bi-file-pdf me-1"></i> {{ __('messages.export_pdf') }}
                        </button>
                        <button class="btn btn-sm btn-outline-success" onclick="exportTimelineCSV()">
                            <i class="bi bi-file-earmark-spreadsheet me-1"></i> {{ __('messages.export_csv') }}
                        </button>
                    </div>
                </div>
            </div>
            <div class="card-body">
                <div class="timeline-container" style="overflow-x: auto;">
                    <div class="timeline-wrapper" style="min-width: 800px;">
                        @forelse($timelineData as $day)
                            <div class="timeline-day mb-4 p-3 border rounded">
                                <div class="d-flex justify-content-between align-items-center mb-3">
                                    <div>
                                        <h6 class="mb-0 fw-bold text-dark">{{ $day['day_name'] }}</h6>
                                        <small class="text-muted">{{ $day['date_label'] }}</small>
                                    </div>
                                    <div class="text-end">
                                        <small class="text-muted d-block">{{ __('messages.driving') }}: <strong class="text-primary">{{ $day['driving_hours'] }}{{ __('messages.hour') }}</strong></small>
                                        <small class="text-muted d-block">{{ __('messages.rest') }}: <strong class="text-success">{{ $day['rest_hours'] }}{{ __('messages.hour') }}</strong></small>
                                    </div>
                                </div>
                                
                                <!-- Gantt Bar -->
                                <div class="gantt-bar-container mb-3" style="height: 40px; position: relative; background: #f8f9fa; border-radius: 4px;">
                                    <div class="d-flex h-100" style="position: relative;">
                                        <!-- Driving Hours -->
                                        <div class="driving-hours bg-primary" 
                                             style="width: {{ ($day['driving_hours'] / 24) * 100 }}%; border-radius: 4px 0 0 4px;"
                                             title="Heures de conduite: {{ $day['driving_hours'] }}h">
                                        </div>
                                        <!-- Rest Hours -->
                                        <div class="rest-hours bg-success" 
                                             style="width: {{ ($day['rest_hours'] / 24) * 100 }}%; border-radius: 0 4px 4px 0;"
                                             title="Heures de repos: {{ $day['rest_hours'] }}h">
                                        </div>
                                        
                                        <!-- Violation Markers -->
                                        @foreach($day['violations'] as $violation)
                                            @php
                                                $hour = (int)explode(':', $violation['time'])[0];
                                                $minute = (int)explode(':', $violation['time'])[1];
                                                $position = (($hour * 60 + $minute) / (24 * 60)) * 100;
                                                $severityColors = [
                                                    'low' => 'warning',
                                                    'medium' => 'warning',
                                                    'high' => 'danger'
                                                ];
                                                $color = $severityColors[$violation['severity']] ?? 'danger';
                                            @endphp
                                            <div class="violation-marker position-absolute" 
                                                 style="left: {{ $position }}%; top: 50%; transform: translate(-50%, -50%); z-index: 10;"
                                                 data-bs-toggle="tooltip" 
                                                 data-bs-placement="top"
                                                 title="{{ $violation['time'] }} - {{ $violation['type_label'] }}: {{ $violation['rule'] }} ({{ $violation['location'] }})">
                                                <i class="bi bi-exclamation-circle-fill text-{{ $color }}" style="font-size: 1.5rem; filter: drop-shadow(0 0 2px rgba(0,0,0,0.3));"></i>
                                            </div>
                                        @endforeach
                                    </div>
                                </div>
                                
                                <!-- Violations List for this day -->
                                @if(count($day['violations']) > 0)
                                    <div class="violations-list">
                                        <small class="text-muted d-block mb-2">
                                            <i class="bi bi-exclamation-triangle me-1"></i>
                                            {{ count($day['violations']) }} {{ __('messages.violations_this_day') }}
                                        </small>
                                        <div class="d-flex flex-wrap gap-2">
                                            @foreach($day['violations'] as $violation)
                                                @php
                                                    $severityBadges = [
                                                        'low' => 'warning',
                                                        'medium' => 'warning',
                                                        'high' => 'danger'
                                                    ];
                                                    $badgeColor = $severityBadges[$violation['severity']] ?? 'secondary';
                                                @endphp
                                                <span class="badge bg-{{ $badgeColor }} bg-opacity-10 text-{{ $badgeColor }}">
                                                    {{ $violation['time'] }} - {{ $violation['type_label'] }}
                                                </span>
                                            @endforeach
                                        </div>
                                    </div>
                                @endif
                            </div>
                        @empty
                            <div class="text-center text-muted py-5">
                                <i class="bi bi-calendar-x display-6 d-block mb-2"></i>
                                <div>{{ __('messages.no_activity_data') }}</div>
                            </div>
                        @endforelse
                    </div>
                </div>
            </div>
        </div>

        <!-- Violations Table -->
        <div class="card border-0 shadow-sm mb-4">
            <div class="card-header bg-white border-0 py-3">
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <h5 class="mb-0 text-dark fw-bold">
                        <i class="bi bi-list-ul me-2 text-primary"></i>
                        {{ __('messages.violations_table') }}
                    </h5>
                    <div class="d-flex gap-2">
                        <button class="btn btn-sm btn-outline-primary" onclick="exportViolationsPDF()">
                            <i class="bi bi-file-pdf me-1"></i> {{ __('messages.export_pdf') }}
                        </button>
                        <button class="btn btn-sm btn-outline-success" onclick="exportViolationsCSV()">
                            <i class="bi bi-file-earmark-spreadsheet me-1"></i> {{ __('messages.export_csv') }}
                        </button>
                    </div>
                </div>
                <div class="row">
                    <div class="col-md-4">
                        <div class="input-group input-group-sm">
                            <span class="input-group-text">
                                <i class="bi bi-search"></i>
                            </span>
                            <input type="text" class="form-control" id="violationSearch" placeholder="{{ __('messages.search_in_table') }}" onkeyup="searchViolations()">
                        </div>
                    </div>
                </div>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover mb-0 align-middle" id="violationsTable">
                        <thead class="table-light">
                            <tr>
                                <th class="border-0 py-3 px-4" style="cursor: pointer;" onclick="sortTable(0)">
                                    {{ __('messages.date') }} <i class="bi bi-arrow-down-up"></i>
                                </th>
                                <th class="border-0 py-3 px-4" style="cursor: pointer;" onclick="sortTable(1)">
                                    {{ __('messages.time') }} <i class="bi bi-arrow-down-up"></i>
                                </th>
                                <th class="border-0 py-3 px-4" style="cursor: pointer;" onclick="sortTable(2)">
                                    {{ __('messages.type') }} <i class="bi bi-arrow-down-up"></i>
                                </th>
                                <th class="border-0 py-3 px-4">{{ __('messages.rule_broken') }}</th>
                                <th class="border-0 py-3 px-4" style="cursor: pointer;" onclick="sortTable(4)">
                                    {{ __('messages.severity') }} <i class="bi bi-arrow-down-up"></i>
                                </th>
                                <th class="border-0 py-3 px-4">{{ __('messages.location') }}</th>
                                <th class="border-0 py-3 px-4 text-center">{{ __('messages.action') }}</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($violations as $violation)
                                @php
                                    $severityBadges = [
                                        'low' => 'warning',
                                        'medium' => 'warning',
                                        'high' => 'danger'
                                    ];
                                    $badgeColor = $severityBadges[$violation['severity']] ?? 'secondary';
                                @endphp
                                <tr class="border-bottom violation-row" data-violation-id="{{ $violation['id'] }}">
                                    <td class="py-3 px-4">{{ $violation['date'] }}</td>
                                    <td class="py-3 px-4">{{ $violation['time'] }}</td>
                                    <td class="py-3 px-4">
                                        <span class="badge bg-primary bg-opacity-10 text-primary">
                                            {{ $violation['type_label'] }}
                                        </span>
                                    </td>
                                    <td class="py-3 px-4">
                                        <small class="text-dark">{{ $violation['rule'] }}</small>
                                    </td>
                                    <td class="py-3 px-4">
                                        <span class="badge bg-{{ $badgeColor }} bg-opacity-10 text-{{ $badgeColor }}">
                                            {{ $violation['severity_label'] }}
                                        </span>
                                    </td>
                                    <td class="py-3 px-4">
                                        <small class="text-muted">
                                            <i class="bi bi-geo-alt me-1"></i>
                                            {{ $violation['location'] }}
                                        </small>
                                    </td>
                                    <td class="py-3 px-4 text-center">
                                        <button class="btn btn-sm btn-outline-info" 
                                                onclick="showViolationDetails({{ json_encode($violation) }})"
                                                title="{{ __('messages.view_details') }}">
                                            <i class="bi bi-eye"></i>
                                        </button>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="7" class="text-center py-5">
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
                @if(count($violations) > 10)
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

        <!-- Comments/Notes Section -->
        <div class="card border-0 shadow-sm">
            <div class="card-header bg-white border-0 py-3">
                <h5 class="mb-0 text-dark fw-bold">
                    <i class="bi bi-chat-left-text me-2 text-primary"></i>
                    {{ __('messages.supervisor_notes') }}
                </h5>
            </div>
            <div class="card-body">
                <form id="commentForm">
                    @csrf
                    <div class="mb-3">
                        <label for="commentText" class="form-label">{{ __('messages.add_note') }}</label>
                        <textarea class="form-control" id="commentText" rows="3" placeholder="{{ __('messages.note_placeholder') }}"></textarea>
                    </div>
                    <button type="submit" class="btn btn-primary">
                        <i class="bi bi-save me-1"></i> {{ __('messages.save_note') }}
                    </button>
                </form>
                
                <hr class="my-4">
                
                <h6 class="mb-3">{{ __('messages.previous_notes') }}</h6>
                <div id="commentsList">
                    <p class="text-muted mb-0">{{ __('messages.no_notes') }}</p>
                </div>
            </div>
        </div>
    </div>

    <!-- Violation Details Modal -->
    <div class="modal fade" id="violationModal" tabindex="-1" aria-labelledby="violationModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="violationModalLabel">{{ __('messages.violation_details') }}</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body" id="violationModalBody">
                    <!-- Content will be populated by JavaScript -->
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">{{ __('messages.close') }}</button>
                </div>
            </div>
        </div>
    </div>

    @push('styles')
    <style>
        .timeline-day {
            transition: all 0.3s ease;
        }
        .timeline-day:hover {
            box-shadow: 0 4px 12px rgba(0,0,0,0.1);
        }
        .violation-marker {
            cursor: pointer;
            transition: transform 0.2s ease;
        }
        .violation-marker:hover {
            transform: translate(-50%, -50%) scale(1.2);
        }
        .gantt-bar-container {
            position: relative;
        }
        .driving-hours, .rest-hours {
            transition: all 0.3s ease;
        }
        .comment-item {
            border-left: 3px solid #0d6efd;
        }
        .table-hover tbody tr:hover {
            background-color: rgba(0, 0, 0, 0.02);
        }
    </style>
    @endpush

    @push('scripts')
    <script>
        // Initialize tooltips
        document.addEventListener('DOMContentLoaded', function() {
            var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
            var tooltipList = tooltipTriggerList.map(function (tooltipTriggerEl) {
                return new bootstrap.Tooltip(tooltipTriggerEl);
            });
        });

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
                
                if (columnIndex === 0 || columnIndex === 1) {
                    // Date/Time sorting
                    return isAscending ? aText.localeCompare(bText) : bText.localeCompare(aText);
                } else {
                    // Text sorting
                    return isAscending ? aText.localeCompare(bText) : bText.localeCompare(aText);
                }
            });
            
            rows.forEach(row => tbody.appendChild(row));
        }

        // Show violation details
        function showViolationDetails(violation) {
            const modal = new bootstrap.Modal(document.getElementById('violationModal'));
            const body = document.getElementById('violationModalBody');
            
            const severityColors = {
                'low': 'warning',
                'medium': 'warning',
                'high': 'danger'
            };
            const badgeColor = severityColors[violation.severity] || 'secondary';
            
            body.innerHTML = `
                <div class="row g-3">
                    <div class="col-md-6">
                        <strong class="text-muted d-block mb-1">{{ __('messages.date') }}</strong>
                        <p class="mb-0">${violation.date}</p>
                    </div>
                    <div class="col-md-6">
                        <strong class="text-muted d-block mb-1">{{ __('messages.time') }}</strong>
                        <p class="mb-0">${violation.time}</p>
                    </div>
                    <div class="col-md-6">
                        <strong class="text-muted d-block mb-1">{{ __('messages.violation_type') }}</strong>
                        <span class="badge bg-primary bg-opacity-10 text-primary">${violation.type_label}</span>
                    </div>
                    <div class="col-md-6">
                        <strong class="text-muted d-block mb-1">{{ __('messages.severity') }}</strong>
                        <span class="badge bg-${badgeColor} bg-opacity-10 text-${badgeColor}">${violation.severity_label}</span>
                    </div>
                    <div class="col-12">
                        <strong class="text-muted d-block mb-1">{{ __('messages.rule_broken') }}</strong>
                        <p class="mb-0">${violation.rule}</p>
                    </div>
                    <div class="col-12">
                        <strong class="text-muted d-block mb-1">{{ __('messages.location') }}</strong>
                        <p class="mb-0"><i class="bi bi-geo-alt me-1"></i>${violation.location}</p>
                    </div>
                </div>
            `;
            
            modal.show();
        }

        // Export functions (placeholders)
        function exportViolationsPDF() {
            alert('{{ __('messages.export_violations_pdf') }}');
            // TODO: Implement PDF export
        }

        function exportViolationsCSV() {
            alert('{{ __('messages.export_violations_csv') }}');
            // TODO: Implement CSV export
        }

        function exportTimelinePDF() {
            alert('{{ __('messages.export_timeline_pdf') }}');
            // TODO: Implement PDF export
        }

        function exportTimelineCSV() {
            alert('{{ __('messages.export_timeline_csv') }}');
            // TODO: Implement CSV export
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
        });
    </script>
    @endpush
</x-app-layout>

