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
                                        @if($activity->driver)
                                            <a href="{{ route('drivers.show', $activity->driver) }}" class="btn btn-sm btn-outline-primary">
                                                <i class="bi bi-eye"></i>
                                            </a>
                                        @else
                                            <span class="text-muted">—</span>
                                        @endif
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
</x-app-layout>

