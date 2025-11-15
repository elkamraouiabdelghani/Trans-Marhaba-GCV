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
                                <th>{{ __('messages.score') ?? 'Score' }}</th>
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
                                        @php
                                            $typeLabels = [
                                                'initial' => __('messages.type_initial'),
                                                'suivi' => __('messages.type_suivi'),
                                                'correctif' => __('messages.type_correctif')
                                            ];
                                            $typeColors = [
                                                'initial' => 'primary',
                                                'suivi' => 'info',
                                                'correctif' => 'warning'
                                            ];
                                        @endphp
                                        <span class="badge bg-{{ $typeColors[$session->type] ?? 'secondary' }}-opacity-10 text-{{ $typeColors[$session->type] ?? 'secondary' }}">
                                            {{ $typeLabels[$session->type] ?? $session->type }}
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
                                        @if($session->score !== null)
                                            <span class="badge bg-{{ $session->score >= 70 ? 'success' : ($session->score >= 50 ? 'warning' : 'danger') }}-opacity-10 text-{{ $session->score >= 70 ? 'success' : ($session->score >= 50 ? 'warning' : 'danger') }}">
                                                {{ $session->score }}/100
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
                                            @if ($session->status == 'completed')
                                                <a href="{{ route('coaching-cabines.pdf', $session) }}" class="btn btn-danger btn-sm" target="_blank" title="{{ __('messages.export_pdf') }}">
                                                    <i class="bi bi-file-pdf"></i>
                                                </a>
                                            @endif
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="8" class="text-center py-5 text-muted">
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
</x-app-layout>

