<x-app-layout>
    <x-slot name="header">
        @include('layouts.topnav')
    </x-slot>

    <div class="container-fluid py-4 mt-4">
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

        {{-- Stats Cards --}}
        <div class="row g-3 mb-4">
            <div class="col-md-3">
                <div class="card border-0 shadow-sm h-100">
                    <div class="card-body">
                        <div class="d-flex align-items-center">
                            <div class="flex-shrink-0">
                                <div class="bg-primary bg-opacity-10 rounded-circle p-3 d-flex align-items-center justify-content-center" style="width:48px; height:48px;">
                                    <i class="bi bi-calendar-check text-primary fs-4"></i>
                                </div>
                            </div>
                            <div class="flex-grow-1 ms-3">
                                <h6 class="text-muted mb-1 small">{{ __('messages.total') ?? 'Total' }}</h6>
                                <h4 class="mb-0 fw-bold">{{ number_format($total) }}</h4>
                                <small class="text-muted">{{ __('messages.year') ?? 'Year' }}: {{ $year }}</small>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-md-3">
                <div class="card border-0 shadow-sm h-100">
                    <div class="card-body">
                        <div class="d-flex align-items-center">
                            <div class="flex-shrink-0">
                                <div class="bg-info bg-opacity-10 rounded-circle p-3 d-flex align-items-center justify-content-center" style="width:48px; height:48px;">
                                    <i class="bi bi-calendar-event text-info fs-4"></i>
                                </div>
                            </div>
                            <div class="flex-grow-1 ms-3">
                                <h6 class="text-muted mb-1 small">{{ __('messages.status_planned') ?? 'Planned' }}</h6>
                                <h4 class="mb-0 fw-bold">{{ number_format($planned) }}</h4>
                                <small class="text-muted">{{ __('messages.year') ?? 'Year' }}: {{ $year }}</small>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-md-3">
                <div class="card border-0 shadow-sm h-100">
                    <div class="card-body">
                        <div class="d-flex align-items-center">
                            <div class="flex-shrink-0">
                                <div class="bg-success bg-opacity-10 rounded-circle p-3 d-flex align-items-center justify-content-center" style="width:48px; height:48px;">
                                    <i class="bi bi-check-circle text-success fs-4"></i>
                                </div>
                            </div>
                            <div class="flex-grow-1 ms-3">
                                <h6 class="text-muted mb-1 small">{{ __('messages.status_completed') ?? 'Completed' }}</h6>
                                <h4 class="mb-0 fw-bold">{{ number_format($completed) }}</h4>
                                <small class="text-muted">{{ __('messages.year') ?? 'Year' }}: {{ $year }}</small>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-md-3">
                <div class="card border-0 shadow-sm h-100">
                    <div class="card-body">
                        <div class="d-flex align-items-center">
                            <div class="flex-shrink-0">
                                <div class="bg-warning bg-opacity-10 rounded-circle p-3 d-flex align-items-center justify-content-center" style="width:48px; height:48px;">
                                    <i class="bi bi-percent text-warning fs-4"></i>
                                </div>
                            </div>
                            <div class="flex-grow-1 ms-3">
                                <h6 class="text-muted mb-1 small">{{ __('messages.completed_percentage') ?? 'Completed %' }}</h6>
                                <h4 class="mb-0 fw-bold">{{ number_format($completedPercentage, 1) }}%</h4>
                                <small class="text-muted">{{ __('messages.year') ?? 'Year' }}: {{ $year }}</small>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Filters -->
        <div class="card border-0 shadow-sm mb-4">
            <div class="card-body p-4">
                <form method="GET" action="{{ route('coaching-cabines.index') }}" class="row g-3">
                    <div class="col-md-2">
                        <label for="driver_id" class="form-label small">{{ __('messages.driver') }}</label>
                        <select name="driver_id" id="driver_id" class="form-select form-select-sm">
                            <option value="">{{ __('messages.all_drivers') }}</option>
                            @foreach($drivers as $driver)
                                <option value="{{ $driver->id }}" {{ request('driver_id') == $driver->id ? 'selected' : '' }}>
                                    {{ $driver->full_name }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-2">
                        <label for="flotte_id" class="form-label small">{{ __('messages.flotte') }}</label>
                        <select name="flotte_id" id="flotte_id" class="form-select form-select-sm">
                            <option value="">{{ __('messages.all_flottes') }}</option>
                            @foreach($flottes as $flotte)
                                <option value="{{ $flotte->id }}" {{ request('flotte_id') == $flotte->id ? 'selected' : '' }}>
                                    {{ $flotte->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-2">
                        <label for="status" class="form-label small">{{ __('messages.status') }}</label>
                        <select name="status" id="status" class="form-select form-select-sm">
                            <option value="">{{ __('messages.all_status') }}</option>
                            <option value="planned" {{ request('status') == 'planned' ? 'selected' : '' }}>{{ __('messages.status_planned') }}</option>
                            <option value="in_progress" {{ request('status') == 'in_progress' ? 'selected' : '' }}>{{ __('messages.status_in_progress') }}</option>
                            <option value="completed" {{ request('status') == 'completed' ? 'selected' : '' }}>{{ __('messages.status_completed') }}</option>
                            <option value="cancelled" {{ request('status') == 'cancelled' ? 'selected' : '' }}>{{ __('messages.status_cancelled') }}</option>
                        </select>
                    </div>
                    <div class="col-md-2">
                        <label for="date_from" class="form-label small">{{ __('messages.from_date') }}</label>
                        <input type="date" name="date_from" id="date_from" class="form-control form-control-sm" value="{{ request('date_from') }}">
                    </div>
                    <div class="col-md-2">
                        <label for="date_to" class="form-label small">{{ __('messages.to_date') }}</label>
                        <input type="date" name="date_to" id="date_to" class="form-control form-control-sm" value="{{ request('date_to') }}">
                    </div>
                    <div class="col-md-2">
                        <label for="year" class="form-label small">{{ __('messages.year') }}</label>
                        <input type="number" name="year" id="year" class="form-control form-control-sm" value="{{ request('year', date('Y')) }}" min="2020" max="2100">
                    </div>
                    <div class="col-md-12 d-flex gap-2 justify-content-end">
                        <button type="submit" class="btn btn-primary btn-sm">
                            <i class="bi bi-search me-1"></i> {{ __('messages.filter') }}
                        </button>
                        <a href="{{ route('coaching-cabines.index') }}" class="btn btn-outline-secondary btn-sm">
                            <i class="bi bi-x-circle me-1"></i> {{ __('messages.clear_filters') }}
                        </a>
                    </div>
                </form>
            </div>
        </div>

        <div class="card border-0 shadow-sm">
            <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-3 px-4 py-3 border-bottom">
                <div>
                    <h1 class="h4 mb-1">{{ __('messages.coaching_cabines_index_title') }}</h1>
                </div>
                <div class="d-flex align-items-center gap-2 small text-muted">
                    <span class="fw-semibold">{{ __('messages.score_legend') ?? 'Score:' }}</span>
                    <span class="badge bg-success bg-opacity-10 text-success">
                        <span class="fw-semibold">≥ 70</span> - {{ __('messages.score_excellent') ?? 'Excellent' }}
                    </span>
                    <span class="badge bg-warning bg-opacity-10 text-warning">
                        <span class="fw-semibold">50-69</span> - {{ __('messages.score_average') ?? 'Average' }}
                    </span>
                    <span class="badge bg-danger bg-opacity-10 text-danger">
                        <span class="fw-semibold">&lt; 50</span> - {{ __('messages.score_poor') ?? 'Poor' }}
                    </span>
                </div>
                <div class="d-flex gap-2">
                    <a href="{{ route('coaching-cabines.planning') }}" class="btn btn-info btn-sm">
                        <i class="bi bi-calendar3 me-1"></i> {{ __('messages.planning') }}
                    </a>
                    <a href="{{ route('coaching-cabines.create') }}" class="btn btn-dark btn-sm">
                        <i class="bi bi-plus-circle me-1"></i> {{ __('messages.new_session') }}
                    </a>
                </div>
            </div>

            <!-- Table -->
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover mb-0 align-middle">
                        <thead class="table-light">
                            <tr>
                                <th class="ps-4">{{ __('messages.from_date') }}</th>
                                <th>{{ __('messages.driver') }}</th>
                                <th>{{ __('messages.flotte') }}</th>
                                <th>{{ __('messages.type') ?? 'Type' }}</th>
                                <th>{{ __('messages.moniteur') }}</th>
                                <th>{{ __('messages.status') }}</th>
                                <th>{{ __('messages.checklist_score') ?? 'Checklist Score' }}</th>
                                <th>{{ __('messages.route') ?? 'Route' }}</th>
                                <th>{{ __('messages.distance') ?? 'Distance' }}</th>
                                <th class="text-end pe-4">{{ __('messages.action') }}</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($sessions as $session)
                                <tr>
                                    <td class="ps-4">
                                        <div class="fw-semibold text-dark">
                                            {{ $session->date ? $session->date->format('d/m/Y') : '-' }}
                                        </div>
                                        <small class="text-muted">
                                            {{ $session->date_fin ? $session->date_fin->format('d/m/Y') : '-' }}
                                        </small>
                                    </td>
                                    <td>
                                        <div class="fw-semibold text-dark">
                                            {{ $session->driver->full_name ?? '-' }}
                                        </div>
                                    </td>
                                    <td>
                                        @if($session->flotte)
                                            <span class="badge bg-info bg-opacity-10 text-info">
                                                {{ $session->flotte->name }}
                                            </span>
                                        @else
                                            <span class="text-muted">-</span>
                                        @endif
                                    </td>
                                    <td>
                                        <span class="badge bg-{{ $session->getTypeColor() }}-opacity-10 text-{{ $session->getTypeColor() }}">
                                            {{ $session->getTypeTitle() }}
                                        </span>
                                    </td>
                                    <td>
                                        <span class="text-muted">{{ $session->moniteur ?? '-' }}</span>
                                    </td>
                                    <td>
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
                                        <span class="badge bg-{{ $statusColors[$session->status] ?? 'secondary' }}-opacity-10 text-{{ $statusColors[$session->status] ?? 'secondary' }}">
                                            {{ $statusLabels[$session->status] ?? $session->status }}
                                        </span>
                                    </td>
                                    <td>
                                        @if($session->checklist)
                                            @php
                                                $totalScore = $session->checklist->getTotalScore();
                                                $status = $session->checklist->getScoreStatus();
                                                $statusColor = $session->checklist->getScoreStatusColor();
                                            @endphp
                                            <span class="badge text-{{ $statusColor }}">
                                                {{ $totalScore }} - {{ $status }}
                                            </span>
                                        @else
                                            <span class="text-muted">-</span>
                                        @endif
                                    </td>
                                    <td>
                                        @if($session->from_location_name && $session->to_location_name)
                                            <div class="small">
                                                <span class="fw-semibold">{{ $session->from_location_name }}</span>
                                                <i class="bi bi-arrow-right mx-1 text-muted"></i>
                                                <span class="fw-semibold">{{ $session->to_location_name }}</span>
                                            </div>
                                        @elseif($session->from_latitude && $session->from_longitude && $session->to_latitude && $session->to_longitude)
                                            <div class="small text-muted">
                                                {{ __('messages.coordinates') ?? 'Coordinates' }}
                                            </div>
                                        @else
                                            <span class="text-muted">-</span>
                                        @endif
                                    </td>
                                    <td>
                                        @if($session->from_latitude && $session->from_longitude && $session->to_latitude && $session->to_longitude)
                                            @php
                                                // Calculate distance using Haversine formula
                                                $lat1 = deg2rad($session->from_latitude);
                                                $lon1 = deg2rad($session->from_longitude);
                                                $lat2 = deg2rad($session->to_latitude);
                                                $lon2 = deg2rad($session->to_longitude);
                                                
                                                $earthRadius = 6371; // Earth's radius in kilometers
                                                $dLat = $lat2 - $lat1;
                                                $dLon = $lon2 - $lon1;
                                                
                                                $a = sin($dLat / 2) * sin($dLat / 2) +
                                                     cos($lat1) * cos($lat2) *
                                                     sin($dLon / 2) * sin($dLon / 2);
                                                $c = 2 * atan2(sqrt($a), sqrt(1 - $a));
                                                $distance = $earthRadius * $c;
                                            @endphp
                                            <span class="badge bg-secondary bg-opacity-10 text-secondary">
                                                {{ number_format($distance, 1) }} km
                                            </span>
                                        @else
                                            <span class="text-muted">-</span>
                                        @endif
                                    </td>
                                    <td class="text-end pe-4">
                                        <div class="btn-group btn-group-sm" role="group">
                                            <a href="{{ route('coaching-cabines.show', $session) }}" class="btn btn-outline-secondary" title="{{ __('messages.view') }}">
                                                <i class="bi bi-eye"></i>
                                            </a>
                                            <a href="{{ route('coaching-cabines.edit', $session) }}" class="btn btn-outline-warning" title="{{ __('messages.edit') }}">
                                                <i class="bi bi-pencil"></i>
                                            </a>
                                            @if ($session->status != 'completed')
                                                <button type="button" class="btn btn-outline-success" title="{{ __('messages.complete_session') ?? 'Compléter la session' }}" data-bs-toggle="modal" data-bs-target="#completeModal{{ $session->id }}">
                                                    <i class="bi bi-check-circle"></i>
                                                </button>
                                            @endif
                                            @if (!isset($session->checklist) || !$session->checklist)
                                                <a href="{{ route('coaching.checklists.create', $session) }}" class="btn btn-outline-primary btn-sm" title="{{ __('messages.add_checklist') ?? 'Add Checklist' }}">
                                                    <i class="bi bi-clipboard-plus"></i>
                                                </a>
                                            @endif
                                            <button
                                                type="button"
                                                class="btn btn-outline-danger"
                                                data-bs-toggle="modal"
                                                data-bs-target="#deleteSessionModal"
                                                data-session-id="{{ $session->id }}"
                                                data-session-driver="{{ $session->driver->full_name ?? '' }}"
                                                title="{{ __('messages.delete') }}"
                                            >
                                                <i class="bi bi-trash"></i>
                                            </button>
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="10" class="text-center py-5 text-muted">
                                        <i class="bi bi-inbox fs-1 d-block mb-2"></i>
                                        {{ __('messages.coaching_cabines_empty') }}
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>

            @if ($sessions instanceof \Illuminate\Contracts\Pagination\LengthAwarePaginator)
                <div class="card-footer bg-white border-0">
                    <div class="d-flex justify-content-between align-items-center">
                        <div class="text-muted small">
                            Affichage de {{ $sessions->firstItem() }} à {{ $sessions->lastItem() }} sur {{ $sessions->total() }} résultats
                        </div>
                        {{ $sessions->links() }}
                    </div>
                </div>
            @endif
        </div>
    </div>

    {{-- Complete Session Modals --}}
    @foreach($sessions as $session)
        @if($session->status != 'completed')
        <div class="modal fade" id="completeModal{{ $session->id }}" tabindex="-1" aria-labelledby="completeModalLabel{{ $session->id }}" aria-hidden="true">
            <div class="modal-dialog modal-lg">
                <div class="modal-content">
                    <form action="{{ route('coaching-cabines.complete', $session) }}" method="POST">
                        @csrf
                        @method('PUT')
                        <div class="modal-header">
                            <h5 class="modal-title" id="completeModalLabel{{ $session->id }}">
                                <i class="bi bi-check-circle me-2 text-success"></i>
                                {{ __('messages.complete_session') ?? 'Compléter la session' }} - {{ $session->driver->full_name ?? '' }}
                            </h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                        </div>
                        <div class="modal-body">
                            <div class="row g-3">
                                <div class="col-md-6">
                                    <label for="score{{ $session->id }}" class="form-label fw-semibold">{{ __('messages.score') }} <span class="text-danger">*</span></label>
                                    <input type="number" name="score" id="score{{ $session->id }}" class="form-control @error('score') is-invalid @enderror" value="{{ old('score', $session->score) }}" min="0" max="100" required>
                                    <small class="text-muted">{{ __('messages.score_range') ?? 'Entre 0 et 100' }}</small>
                                    @error('score')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>

                                <div class="col-md-6">
                                    <label for="next_planning_session{{ $session->id }}" class="form-label fw-semibold">{{ __('messages.next_planning_session') }}</label>
                                    <input type="date" name="next_planning_session" id="next_planning_session{{ $session->id }}" class="form-control @error('next_planning_session') is-invalid @enderror" value="{{ old('next_planning_session', $session->next_planning_session?->format('Y-m-d')) }}">
                                    @error('next_planning_session')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>

                                <div class="col-12">
                                    <label for="route_taken{{ $session->id }}" class="form-label fw-semibold">{{ __('messages.route_taken') }}</label>
                                    <textarea name="route_taken" id="route_taken{{ $session->id }}" rows="3" class="form-control @error('route_taken') is-invalid @enderror">{{ old('route_taken', $session->route_taken) }}</textarea>
                                    @error('route_taken')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>

                                <div class="col-12">
                                    <label for="assessment{{ $session->id }}" class="form-label fw-semibold">{{ __('messages.assessment') }}</label>
                                    <textarea name="assessment" id="assessment{{ $session->id }}" rows="4" class="form-control @error('assessment') is-invalid @enderror">{{ old('assessment', $session->assessment) }}</textarea>
                                    @error('assessment')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>

                                <div class="col-12">
                                    <label for="notes{{ $session->id }}" class="form-label fw-semibold">{{ __('messages.notes') ?? 'Notes' }}</label>
                                    <textarea name="notes" id="notes{{ $session->id }}" rows="3" class="form-control @error('notes') is-invalid @enderror">{{ old('notes', $session->notes) }}</textarea>
                                    @error('notes')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>

                                {{-- Rest Places Section --}}
                                <div class="col-12">
                                    <label class="form-label fw-semibold">{{ __('messages.rest_places') ?? 'Rest Places' }}</label>
                                    <small class="text-muted d-block mb-2">
                                        {{ __('messages.rest_places_help', ['days' => $session->validity_days - 1]) ?? 'Add rest places from Day 1 to Day ' . ($session->validity_days - 1) . ' (maximum ' . ($session->validity_days - 1) . ' rest places)' }}
                                    </small>
                                    <div id="rest-places-container-{{ $session->id }}" data-max-places="{{ $session->validity_days - 1 }}">
                                        @php
                                            $restPlaces = old('rest_places', $session->rest_places ?? []);
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
                                                               data-session-id="{{ $session->id }}"
                                                               data-place-index="{{ $i }}">
                                                        <button type="button" 
                                                                class="btn btn-outline-primary rest-place-search-btn" 
                                                                data-session-id="{{ $session->id }}"
                                                                data-place-index="{{ $i }}"
                                                                title="{{ __('messages.search_rest_place_city') ?? 'Search city' }}">
                                                            <i class="bi bi-search"></i>
                                                        </button>
                                                        <button type="button" 
                                                                class="btn btn-outline-danger remove-rest-place-btn" 
                                                                data-session-id="{{ $session->id }}"
                                                                title="{{ __('messages.remove_rest_place') ?? 'Remove' }}">
                                                            <i class="bi bi-trash"></i>
                                                        </button>
                                                    </div>
                                                    <div class="rest-place-error text-danger small mt-1 d-none" id="rest-place-error-{{ $session->id }}-{{ $i }}"></div>
                                                    <div class="rest-place-map-container mt-2" id="rest-place-map-{{ $session->id }}-{{ $i }}" style="height: 200px; width: 100%; background: #f5f5f5; display: none;"></div>
                                                </div>
                                            @endforeach
                                        @endif
                                    </div>
                                    <button type="button" 
                                            class="btn btn-outline-success btn-sm mt-2 add-rest-place-btn" 
                                            data-session-id="{{ $session->id }}"
                                            data-max-places="{{ $session->validity_days - 1 }}"
                                            @if($currentCount >= ($session->validity_days - 1)) style="display: none;" @endif>
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
    @endforeach

    {{-- Delete Session Modal --}}
    <div class="modal fade" id="deleteSessionModal" tabindex="-1" aria-labelledby="deleteSessionModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <form id="deleteSessionForm" method="POST">
                    @csrf
                    @method('DELETE')
                    <div class="modal-header bg-danger text-white">
                        <h5 class="modal-title" id="deleteSessionModalLabel">
                            <i class="bi bi-trash me-2"></i>
                            {{ __('messages.coaching_cabines_delete_confirm') ?? 'Are you sure you want to delete this coaching session?' }}
                        </h5>
                        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <p class="mb-2">
                            {{ __('messages.confirm_delete_turnover_warning') ?? 'This action cannot be undone.' }}
                        </p>
                        <p class="mb-0 text-muted">
                            <strong>{{ __('messages.driver') }}:</strong>
                            <span id="delete-session-driver"></span>
                        </p>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">
                            {{ __('messages.cancel') }}
                        </button>
                        <button type="submit" class="btn btn-danger">
                            <i class="bi bi-trash me-1"></i>{{ __('messages.delete') }}
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    {{-- Leaflet CSS and JS for rest places maps --}}
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

            // Handle add rest place button
            document.querySelectorAll('.add-rest-place-btn').forEach(btn => {
                btn.addEventListener('click', function() {
                    const sessionId = this.getAttribute('data-session-id');
                    const container = document.getElementById(`rest-places-container-${sessionId}`);
                    if (!container) return;
                    
                    const maxPlaces = parseInt(container.getAttribute('data-max-places')) || 0;
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

            // Handle rest place search
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

            // Handle remove rest place (if needed for dynamic adding)
            document.querySelectorAll('.remove-rest-place-btn').forEach(btn => {
                btn.addEventListener('click', function() {
                    const sessionId = this.getAttribute('data-session-id');
                    const item = this.closest('.rest-place-item');
                    if (item) {
                        item.remove();
                        // Update day labels
                        updateDayLabels(sessionId);
                        updateAddRestPlaceButton(sessionId);
                    }
                });
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
                });
            }
        });
    </script>
    @endpush

<script>
document.addEventListener('DOMContentLoaded', function () {
    const deleteModal = document.getElementById('deleteSessionModal');
    if (!deleteModal) return;

    const deleteForm = document.getElementById('deleteSessionForm');
    const driverSpan = document.getElementById('delete-session-driver');

    deleteModal.addEventListener('show.bs.modal', function (event) {
        const button = event.relatedTarget;
        if (!button) return;

        const sessionId = button.getAttribute('data-session-id');
        const driverName = button.getAttribute('data-session-driver') || '';

        if (driverSpan) {
            driverSpan.textContent = driverName;
        }

        if (deleteForm && sessionId) {
            const actionTemplate = '{{ route("coaching-cabines.destroy", ":id") }}';
            deleteForm.action = actionTemplate.replace(':id', sessionId);
        }
    });
});
</script>
</x-app-layout>
