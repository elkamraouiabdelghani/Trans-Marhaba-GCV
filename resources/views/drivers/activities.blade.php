@php
    $formatDuration = static function ($value): string {
        if (!$value) {
            return '—';
        }

        $stringValue = (string) $value;

        if (strlen($stringValue) >= 5) {
            return substr($stringValue, 0, 5);
        }

        return $stringValue;
    };
@endphp

<x-app-layout>
    <x-slot name="header">
        @include('layouts.topnav')
    </x-slot>

    <div class="container-fluid py-4">
        <div class="d-flex justify-content-between align-items-center mb-4 card-header bg-white border-0 p-3 rounded-3 shadow-sm">
            <div>
                <h1 class="h3 text-dark fw-bold mb-1">
                    <i class="bi bi-clock-history text-primary me-2"></i>
                    {{ __('messages.driver_activities') }}
                </h1>
                <p class="text-muted mb-0">{{ __('messages.driver_activities_description') }}</p>
            </div>
            <div class="d-flex gap-2">
                <button class="btn btn-dark btn-sm d-flex align-items-center text-white" data-bs-toggle="modal" data-bs-target="#addActivityModal">
                    <i class="bi bi-plus-circle me-2"></i>
                    {{ __('messages.add_driver_activity') ?? 'Add Activity' }}
                </button>
                <button class="btn btn-primary btn-sm d-flex align-items-center" data-bs-toggle="modal" data-bs-target="#activitiesImportModal">
                    <i class="bi bi-cloud-upload me-2"></i>
                    {{ __('messages.import') ?? 'Import' }}
                </button>
                <a href="{{ route('drivers.index') }}" class="btn btn-outline-secondary btn-sm">
                    <i class="bi bi-people me-1"></i>
                    {{ __('messages.drivers') }}
                </a>
            </div>
        </div>

        @if(session('success'))
            <div class="alert alert-success border-0 shadow-sm">{{ session('success') }}</div>
        @endif

        @if(session('error'))
            <div class="alert alert-danger border-0 shadow-sm">{{ session('error') }}</div>
        @endif

        @if(session('activity_import'))
            @php
                $importSummary = session('activity_import');
            @endphp
            <div class="alert alert-info border-0 shadow-sm">
                <div class="d-flex flex-column flex-md-row justify-content-between gap-2">
                    <div>
                        <strong>{{ __('messages.import_summary') ?? 'Import summary' }}:</strong>
                        {{ __('messages.import_processed_rows') ?? 'Processed' }} {{ $importSummary['processed'] ?? 0 }},
                        {{ __('messages.import_inserted_rows') ?? 'Inserted' }} {{ $importSummary['inserted'] ?? 0 }},
                        {{ __('messages.import_skipped_rows') ?? 'Skipped' }} {{ $importSummary['skipped'] ?? 0 }}
                    </div>
                    <button type="button" class="btn-close ms-md-3" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
                @if(!empty($importSummary['errors']))
                    <div class="mt-3">
                        <strong>{{ __('messages.import_errors') ?? 'Errors' }}:</strong>
                        <ul class="mb-0 small">
                            @foreach($importSummary['errors'] as $message)
                                <li>{{ $message }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif
            </div>
        @endif

        <div class="card border-0 shadow-sm mb-4">
            <div class="card-header bg-white border-0 py-3">
                <h5 class="mb-0 text-dark fw-bold">
                    <i class="bi bi-funnel me-2 text-primary"></i>
                    {{ __('messages.driver_activity_filters') }}
                </h5>
            </div>
            <div class="card-body">
                <form method="GET" action="{{ route('drivers.activities.index') }}" class="row g-3">
                    <div class="col-md-3">
                        <label class="form-label small text-muted">{{ __('messages.driver') }}</label>
                        <select name="driver_id" class="form-select form-select-sm">
                            <option value="">{{ __('messages.all_drivers') }}</option>
                            @foreach($driversList as $driver)
                                @php
                                    $driverLabel = $driver->full_name ?? "Driver #{$driver->id}";
                                @endphp
                                <option value="{{ $driver->id }}" {{ (string)($filters['driver_id'] ?? '') === (string)$driver->id ? 'selected' : '' }}>
                                    {{ $driverLabel }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-2">
                        <label class="form-label small text-muted">{{ __('messages.date_from') }}</label>
                        <input type="date" name="date_from" value="{{ $filters['date_from'] ?? '' }}" class="form-control form-control-sm">
                    </div>
                    <div class="col-md-2">
                        <label class="form-label small text-muted">{{ __('messages.date_to') }}</label>
                        <input type="date" name="date_to" value="{{ $filters['date_to'] ?? '' }}" class="form-control form-control-sm">
                    </div>
                    <div class="col-md-3">
                        <label class="form-label small text-muted">{{ __('messages.search') }}</label>
                        <input type="text"
                            name="search"
                            value="{{ $filters['search'] ?? '' }}"
                            placeholder="{{ __('messages.driver_activity_search_placeholder') }}"
                            class="form-control form-control-sm">
                    </div>
                    <div class="col-md-2 d-flex align-items-end gap-2">
                        <button type="submit" class="btn btn-primary btn-sm w-100">
                            <i class="bi bi-search"></i>
                        </button>
                        <a href="{{ route('drivers.activities.index') }}" class="btn btn-secondary btn-sm w-100">
                            <i class="bi bi-arrow-counterclockwise"></i>
                        </a>
                    </div>
                </form>
            </div>
        </div>

        <div class="card border-0 shadow-sm">
            <div class="card-header bg-white border-0 py-3 d-flex flex-column flex-md-row gap-2 justify-content-between align-items-md-center">
                <h5 class="mb-0 text-dark fw-bold">
                    <i class="bi bi-table me-2 text-primary"></i>
                    {{ __('messages.driver_activity_table') }}
                </h5>
                <div class="text-muted small">
                    {{ __('messages.results') ?? '' }}: {{ $activities->firstItem() ?? 0 }}-{{ $activities->lastItem() ?? 0 }} / {{ $activities->total() }}
                </div>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead class="table-light">
                            <tr>
                                <th>{{ __('messages.date') }}</th>
                                <th>{{ __('messages.driver') }}</th>
                                <th>{{ __('messages.flotte') }}</th>
                                <th>{{ __('messages.asset_description') ?? 'Asset' }}</th>
                                <th>{{ __('messages.start_time') }}</th>
                                <th>{{ __('messages.end_time') }}</th>
                                <th>{{ __('messages.work_time') ?? 'Work' }}</th>
                                <th>{{ __('messages.driving_time') ?? __('messages.driving_hours') }}</th>
                                <th>{{ __('messages.rest_time') ?? __('messages.rest_hours') }}</th>
                                <th>{{ __('messages.rest_daily') ?? 'Daily Rest' }}</th>
                                <th>{{ __('messages.location') ?? 'Location' }}</th>
                                <th>{{ __('messages.raison') ?? 'Reason' }}</th>
                                <th class="text-center">{{ __('messages.actions') }}</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($activities as $activity)
                                <tr>
                                    <td>
                                        <div class="fw-semibold text-dark">{{ optional($activity->activity_date)->format('d/m/Y') }}</div>
                                        <small class="text-muted">{{ optional($activity->activity_date)->translatedFormat('l') }}</small>
                                    </td>
                                    <td>
                                        <div class="fw-semibold text-dark">{{ $activity->driver_name ?? optional($activity->driver)->full_name ?? optional($activity->driver)->name ?? __('messages.not_available') }}</div>
                                        @if($activity->driver && $activity->driver->flotte)
                                            <small class="text-muted">{{ $activity->driver->flotte->name }}</small>
                                        @endif
                                    </td>
                                    <td>{{ $activity->flotte ?? optional($activity->driver->flotte)->name ?? '—' }}</td>
                                    <td>{{ $activity->asset_description ?? '—' }}</td>
                                    <td>{{ $formatDuration($activity->start_time) }}</td>
                                    <td>{{ $formatDuration($activity->end_time) }}</td>
                                    <td>{{ $formatDuration($activity->work_time) }}</td>
                                    <td>{{ $formatDuration($activity->driving_time) }}</td>
                                    <td>{{ $formatDuration($activity->rest_time) }}</td>
                                    <td>{{ $formatDuration($activity->rest_daily) }}</td>
                                    <td>
                                        <div>{{ $activity->start_location ?? '—' }}</div>
                                        @if($activity->overnight_location)
                                            <small class="text-muted">{{ $activity->overnight_location }}</small>
                                        @endif
                                    </td>
                                    <td style="max-width: 200px;">
                                        <div class="text-truncate" title="{{ $activity->raison ?? '' }}">
                                            {{ $activity->raison ?? '—' }}
                                        </div>
                                    </td>
                                    <td class="text-center">
                                        <div class="d-flex gap-1 justify-content-center">
                                            @if($activity->driver)
                                                <a href="{{ route('drivers.show', $activity->driver) }}" class="btn btn-sm btn-outline-primary" title="{{ __('messages.view') ?? 'View' }}">
                                                    <i class="bi bi-eye"></i>
                                                </a>
                                            @endif
                                            @if(Auth::user()->role === 'admin')
                                                <button type="button" class="btn btn-sm btn-outline-danger" title="{{ __('messages.delete') ?? 'Delete' }}" data-bs-toggle="modal" data-bs-target="#deleteActivityModal" data-activity-id="{{ $activity->id }}" data-activity-date="{{ optional($activity->activity_date)->format('d/m/Y') }}" data-activity-time="{{ optional($activity->start_time)->format('H:i') }}">
                                                    <i class="bi bi-trash"></i>
                                                </button>
                                            @endif
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="13" class="text-center py-5 text-muted">
                                        <i class="bi bi-clipboard-x display-6 d-block mb-3"></i>
                                        {{ __('messages.no_activity_data') }}
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
            @if($activities->hasPages())
                <div class="card-footer bg-white border-0">
                    {{ $activities->links() }}
                </div>
            @endif
        </div>
    </div>

    <!-- Add Activity Modal -->
    <div class="modal fade" id="addActivityModal" tabindex="-1" aria-labelledby="addActivityModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <form method="POST" id="addActivityForm" class="modal-content">
                @csrf
                <div class="modal-header">
                    <h5 class="modal-title" id="addActivityModalLabel">
                        <i class="bi bi-clock-history me-2 text-info"></i>
                        {{ __('messages.add_driver_activity') ?? 'Add Driver Activity' }}
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="{{ __('messages.close') }}"></button>
                </div>
                <div class="modal-body">
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label">
                                {{ __('messages.driver') }}
                                <span class="text-danger">*</span>
                            </label>
                            <select name="driver_id" id="activity-driver-select" class="form-select @error('driver_id') is-invalid @enderror" required>
                                <option value="">{{ __('messages.select_driver') ?? 'Select Driver' }}</option>
                                @foreach($driversList as $driver)
                                    @php
                                        $driverLabel = $driver->full_name ?? "Driver #{$driver->id}";
                                    @endphp
                                    <option value="{{ $driver->id }}" data-driver-name="{{ $driver->full_name ?? '' }}" data-flotte-name="{{ optional($driver->flotte)->name ?? '' }}">
                                        {{ $driverLabel }}
                                    </option>
                                @endforeach
                            </select>
                            @error('driver_id')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">
                                {{ __('messages.activity_date') ?? 'Date' }}
                                <span class="text-danger">*</span>
                            </label>
                            <input type="date"
                                   name="activity_date"
                                   class="form-control @error('activity_date') is-invalid @enderror"
                                   value="{{ old('activity_date', now()->format('Y-m-d')) }}"
                                   required>
                            @error('activity_date')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">{{ __('messages.start_time') ?? 'Start Time' }}<span class="text-danger">*</span></label>
                            <input type="time"
                                   step="60"
                                   id="activity-start-time"
                                   name="start_time"
                                   class="form-control @error('start_time') is-invalid @enderror"
                                   value="{{ old('start_time') }}"
                                   required>
                            @error('start_time')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">{{ __('messages.end_time') ?? 'End Time' }}<span class="text-danger">*</span></label>
                            <input type="time"
                                   step="60"
                                   id="activity-end-time"
                                   name="end_time"
                                   class="form-control @error('end_time') is-invalid @enderror"
                                   value="{{ old('end_time') }}"
                                   required>
                            @error('end_time')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">{{ __('messages.work_time') ?? 'Work Time' }}<span class="text-danger">*</span></label>
                            <input type="text"
                                   inputmode="numeric"
                                   pattern="^\d{2}:\d{2}(:\d{2})?$"
                                   placeholder="HH:MM"
                                   id="activity-work-time"
                                   readonly
                                   name="work_time"
                                   class="form-control @error('work_time') is-invalid @enderror"
                                   value="{{ old('work_time') }}"
                                   required>
                            <small class="text-muted">{{ __('messages.duration_format_hint') ?? 'Format HH:MM (calculated)' }}</small>
                            @error('work_time')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">{{ __('messages.driving_time') ?? 'Driving Time' }}<span class="text-danger">*</span></label>
                            <input type="text"
                                   inputmode="numeric"
                                   pattern="^\d{2}:\d{2}(:\d{2})?$"
                                   placeholder="HH:MM"
                                   name="driving_time"
                                   class="form-control @error('driving_time') is-invalid @enderror"
                                   value="{{ old('driving_time') }}"
                                   required>
                            <small class="text-muted">{{ __('messages.duration_format_hint') ?? 'Format HH:MM' }}</small>
                            @error('driving_time')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">{{ __('messages.rest_time') ?? 'Rest Time' }}<span class="text-danger">*</span></label>
                            <input type="text"
                                   inputmode="numeric"
                                   pattern="^\d{2}:\d{2}(:\d{2})?$"
                                   placeholder="HH:MM"
                                   name="rest_time"
                                   class="form-control @error('rest_time') is-invalid @enderror"
                                   value="{{ old('rest_time') }}"
                                   required>
                            <small class="text-muted">{{ __('messages.duration_format_hint') ?? 'Format HH:MM' }}</small>
                            @error('rest_time')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">{{ __('messages.rest_daily') ?? 'Daily Rest' }}</label>
                            <input type="text"
                                   inputmode="numeric"
                                   pattern="^\d{2}:\d{2}(:\d{2})?$"
                                   placeholder="HH:MM"
                                   name="rest_daily"
                                   class="form-control @error('rest_daily') is-invalid @enderror"
                                   value="{{ old('rest_daily') }}">
                            <small class="text-muted">{{ __('messages.duration_format_hint') ?? 'Format HH:MM' }}</small>
                            @error('rest_daily')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">{{ __('messages.flotte') ?? 'Flotte' }}</label>
                            <input type="text"
                                   id="activity-flotte"
                                   name="flotte"
                                   class="form-control @error('flotte') is-invalid @enderror"
                                   value="{{ old('flotte') }}">
                            @error('flotte')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">{{ __('messages.asset_description') ?? 'Asset Description' }}</label>
                            <input type="text"
                                   name="asset_description"
                                   class="form-control @error('asset_description') is-invalid @enderror"
                                   value="{{ old('asset_description') }}">
                            @error('asset_description')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">{{ __('messages.driver_name') ?? 'Driver Name' }}</label>
                            <input type="text"
                                   id="activity-driver-name"
                                   name="driver_name"
                                   class="form-control @error('driver_name') is-invalid @enderror"
                                   value="{{ old('driver_name') }}">
                            @error('driver_name')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">{{ __('messages.start_location') ?? 'Start Location' }}</label>
                            <input type="text"
                                   name="start_location"
                                   class="form-control @error('start_location') is-invalid @enderror"
                                   value="{{ old('start_location') }}">
                            @error('start_location')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">{{ __('messages.overnight_location') ?? 'Overnight Location' }}</label>
                            <input type="text"
                                   name="overnight_location"
                                   class="form-control @error('overnight_location') is-invalid @enderror"
                                   value="{{ old('overnight_location') }}">
                            @error('overnight_location')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        <div class="col-12">
                            <label class="form-label">{{ __('messages.reason') ?? 'Reason / Observations' }}</label>
                            <textarea name="raison"
                                      class="form-control @error('raison') is-invalid @enderror"
                                      rows="3"
                                      placeholder="{{ __('messages.activity_reason_placeholder') ?? 'Notes on activity...' }}">{{ old('raison') }}</textarea>
                            @error('raison')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">
                        {{ __('messages.cancel') }}
                    </button>
                    <button type="submit" class="btn btn-dark text-white">
                        <i class="bi bi-save me-1"></i>
                        {{ __('messages.save') ?? 'Save' }}
                    </button>
                </div>
            </form>
        </div>
    </div>

    <div class="modal fade" id="activitiesImportModal" tabindex="-1" aria-labelledby="activitiesImportModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <form class="modal-content" method="POST" action="{{ route('drivers.activities.import') }}" enctype="multipart/form-data">
                @csrf
                <div class="modal-header">
                    <h5 class="modal-title" id="activitiesImportModalLabel">
                        <i class="bi bi-cloud-upload me-2"></i>
                        {{ __('messages.import_driver_activities') ?? 'Import Driver Activities' }}
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="{{ __('messages.close') }}"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label for="activities-file-input" class="form-label">
                            {{ __('messages.choose_file') ?? 'Choose file' }}
                            <span class="text-danger">*</span>
                        </label>
                        <input type="file"
                               id="activities-file-input"
                               name="activities_file"
                               accept=".xlsx,.xls,.csv"
                               class="form-control @error('activities_file') is-invalid @enderror">
                        @error('activities_file')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                        <small class="text-muted">
                            {{ __('messages.import_file_hint') ?? 'Accepted formats: XLSX, XLS, CSV (max 10 MB).' }}
                        </small>
                    </div>
                    <div class="alert alert-light border small mb-0">
                        <p class="mb-1">{{ __('messages.import_activity_help') ?? 'Ensure the file keeps the same headers as the fleet export.' }}</p>
                        <p class="mb-0">{{ __('messages.import_activity_driver_hint') ?? 'Driver names are extracted from "Asset Description" (vehicle // driver).' }}</p>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">
                        {{ __('messages.cancel') }}
                    </button>
                    <button type="submit" class="btn btn-primary">
                        <i class="bi bi-upload me-1"></i>
                        {{ __('messages.import') ?? 'Import' }}
                    </button>
                </div>
            </form>
        </div>
    </div>

    @push('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const driverSelect = document.getElementById('activity-driver-select');
            const driverNameInput = document.getElementById('activity-driver-name');
            const flotteInput = document.getElementById('activity-flotte');
            const activityForm = document.getElementById('addActivityForm');
            const startTimeInput = document.getElementById('activity-start-time');
            const endTimeInput = document.getElementById('activity-end-time');
            const workTimeInput = document.getElementById('activity-work-time');

            // Update form action when driver is selected
            if (driverSelect && activityForm) {
                const baseRouteUrl = @json(url('/drivers')) + '/';
                
                driverSelect.addEventListener('change', function() {
                    const driverId = this.value;
                    if (driverId) {
                        activityForm.action = baseRouteUrl + driverId + '/activities';
                        
                        // Auto-populate driver name and flotte
                        const selectedOption = this.options[this.selectedIndex];
                        const driverName = selectedOption.getAttribute('data-driver-name') || '';
                        const flotteName = selectedOption.getAttribute('data-flotte-name') || '';
                        
                        if (driverNameInput) {
                            driverNameInput.value = driverName;
                        }
                        if (flotteInput) {
                            flotteInput.value = flotteName || '';
                        }
                    } else {
                        activityForm.action = '#';
                        // Clear fields when no driver is selected
                        if (driverNameInput) {
                            driverNameInput.value = '';
                        }
                        if (flotteInput) {
                            flotteInput.value = '';
                        }
                    }
                });
            }

            // Calculate work time from start and end times
            const parseTimeToMinutes = (value) => {
                if (!value || typeof value !== 'string') {
                    return null;
                }
                const parts = value.split(':').map(Number);
                if (parts.some(number => Number.isNaN(number))) {
                    return null;
                }
                const [hours = 0, minutes = 0, seconds = 0] = parts;
                return (hours * 60) + minutes + Math.floor(seconds / 60);
            };

            const formatMinutesToTime = (minutes) => {
                const hrs = Math.floor(minutes / 60).toString().padStart(2, '0');
                const mins = Math.floor(minutes % 60).toString().padStart(2, '0');
                return `${hrs}:${mins}`;
            };

            const updateWorkTime = () => {
                if (!startTimeInput || !endTimeInput || !workTimeInput) {
                    return;
                }
                const startMinutes = parseTimeToMinutes(startTimeInput.value);
                const endMinutes = parseTimeToMinutes(endTimeInput.value);

                if (startMinutes === null || endMinutes === null || endMinutes <= startMinutes) {
                    workTimeInput.value = '';
                    return;
                }

                workTimeInput.value = formatMinutesToTime(endMinutes - startMinutes);
            };

            if (startTimeInput && endTimeInput) {
                startTimeInput.addEventListener('input', updateWorkTime);
                startTimeInput.addEventListener('change', updateWorkTime);
                endTimeInput.addEventListener('input', updateWorkTime);
                endTimeInput.addEventListener('change', updateWorkTime);
            }

            // Validate form before submission
            if (activityForm) {
                activityForm.addEventListener('submit', function(e) {
                    const driverId = driverSelect?.value;
                    if (!driverId) {
                        e.preventDefault();
                        alert('{{ __('messages.please_select_driver') ?? 'Please select a driver' }}');
                        driverSelect?.focus();
                        return false;
                    }
                });
            }
        });
    </script>
    @endpush

    <!-- Delete Activity Confirmation Modal -->
    @if(Auth::user()->role === 'admin')
    <div class="modal fade" id="deleteActivityModal" tabindex="-1" aria-labelledby="deleteActivityModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header bg-danger text-white">
                    <h5 class="modal-title" id="deleteActivityModalLabel">
                        <i class="bi bi-exclamation-triangle text-warning me-2"></i>
                        {{ __('messages.confirm_delete') ?? 'Confirm Delete' }}
                    </h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <p>{{ __('messages.confirm_delete_activity') ?? 'Are you sure you want to delete this activity?' }}</p>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                        {{ __('messages.cancel') ?? 'Cancel' }}
                    </button>
                    <form id="deleteActivityForm" method="POST" class="d-inline">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="btn btn-danger">
                            <i class="bi bi-trash me-1"></i>
                            {{ __('messages.delete') ?? 'Delete' }}
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const deleteModal = document.getElementById('deleteActivityModal');
            if (deleteModal) {
                deleteModal.addEventListener('show.bs.modal', function(event) {
                    const button = event.relatedTarget;
                    const activityId = button.getAttribute('data-activity-id');
                    const activityDate = button.getAttribute('data-activity-date');
                    const activityTime = button.getAttribute('data-activity-time');
                    
                    const form = deleteModal.querySelector('#deleteActivityForm');
                    const info = deleteModal.querySelector('#deleteActivityInfo');
                    
                    form.action = '{{ route("drivers.activities.delete", ":id") }}'.replace(':id', activityId);
                    info.textContent = activityDate && activityTime 
                        ? `Activity on ${activityDate} at ${activityTime}` 
                        : `Activity ID: ${activityId}`;
                });
            }
        });
    </script>
    @endif
</x-app-layout>

