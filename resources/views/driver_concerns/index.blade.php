<x-app-layout>
    <x-slot name="header">
        @include('layouts.topnav')
    </x-slot>

    @php
        $statusLabels = [
            'open' => __('messages.status_open'),
            'in_progress' => __('messages.status_in_progress'),
            'resolved' => __('messages.status_resolved'),
            'closed' => __('messages.status_closed'),
        ];
        $statusBadges = [
            'open' => 'secondary',
            'in_progress' => 'warning',
            'resolved' => 'success',
            'closed' => 'dark',
        ];
    @endphp

    <div class="container-fluid py-4 mt-4">
        <div class="row g-3 mb-4">
            <div class="col-xl-3 col-md-6">
                <div class="card border-0 shadow-sm h-100">
                    <div class="card-body">
                        <div class="d-flex align-items-center">
                            <div class="rounded-circle bg-primary bg-opacity-10 d-flex align-items-center justify-content-center me-3" style="width: 52px; height: 52px;">
                                <i class="bi bi-collection text-primary fs-4"></i>
                            </div>
                            <div>
                                <p class="text-muted mb-1">{{ __('messages.total_concerns') }}</p>
                                <h4 class="mb-0 fw-bold text-dark">{{ $stats['total'] ?? 0 }}</h4>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-xl-3 col-md-6">
                <div class="card border-0 shadow-sm h-100">
                    <div class="card-body">
                        <div class="d-flex align-items-center">
                            <div class="rounded-circle bg-warning bg-opacity-10 d-flex align-items-center justify-content-center me-3" style="width: 52px; height: 52px;">
                                <i class="bi bi-hourglass-split text-warning fs-4"></i>
                            </div>
                            <div>
                                <p class="text-muted mb-1">{{ __('messages.in_progress_concerns') }}</p>
                                <h4 class="mb-0 fw-bold text-dark">{{ $stats['in_progress'] ?? 0 }}</h4>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-xl-3 col-md-6">
                <div class="card border-0 shadow-sm h-100">
                    <div class="card-body">
                        <div class="d-flex align-items-center">
                            <div class="rounded-circle bg-success bg-opacity-10 d-flex align-items-center justify-content-center me-3" style="width: 52px; height: 52px;">
                                <i class="bi bi-check-circle text-success fs-4"></i>
                            </div>
                            <div>
                                <p class="text-muted mb-1">{{ __('messages.closed_concerns') }}</p>
                                <h4 class="mb-0 fw-bold text-dark">{{ $stats['closed'] ?? 0 }}</h4>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-xl-3 col-md-6">
                <div class="card border-0 shadow-sm h-100">
                    <div class="card-body">
                        <div class="d-flex align-items-center">
                            <div class="rounded-circle bg-info bg-opacity-10 d-flex align-items-center justify-content-center me-3" style="width: 52px; height: 52px;">
                                <i class="bi bi-percent text-info fs-4"></i>
                            </div>
                            <div>
                                <p class="text-muted mb-1">{{ __('messages.concerns_per_driver_percentage') }}</p>
                                <h4 class="mb-0 fw-bold text-dark">{{ $stats['concerns_per_driver_percentage'] ?? 0 }}%</h4>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="card border-0 shadow-sm mb-4">
            <div class="card-body p-4">
                <form action="{{ route('driver-concerns.index') }}" method="GET" id="concernFiltersForm">
                    <div class="row g-3 align-items-end">
                        <div class="col-12 col-md-3">
                            <label for="statusFilter" class="form-label fw-semibold">{{ __('messages.status') }}</label>
                            <select name="status" id="statusFilter" class="form-select">
                                <option value="">{{ __('messages.all_status') }}</option>
                                @foreach($statuses as $status)
                                    <option value="{{ $status }}" @selected(($filters['status'] ?? '') === $status)>
                                        {{ $statusLabels[$status] ?? ucfirst(str_replace('_', ' ', $status)) }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-12 col-md-3">
                            <label for="concernTypeFilter" class="form-label fw-semibold">{{ __('messages.concern_type') }}</label>
                            <select name="concern_type_id" id="concernTypeFilter" class="form-select">
                                <option value="">{{ __('messages.all_types') }}</option>
                                @foreach($concernTypes as $id => $label)
                                    <option value="{{ $id }}" @selected(($filters['concern_type_id'] ?? '') == $id)>{{ $label }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-12 col-md-3">
                            <label for="driverFilter" class="form-label fw-semibold">{{ __('messages.driver') }}</label>
                            <select name="driver_id" id="driverFilter" class="form-select">
                                <option value="">{{ __('messages.all_drivers') }}</option>
                                @foreach($drivers as $id => $label)
                                    <option value="{{ $id }}" @selected(($filters['driver_id'] ?? '') == $id)>{{ $label }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-12 col-md-3">
                            <label for="searchConcerns" class="form-label fw-semibold">{{ __('messages.search') }}</label>
                            <div class="input-group">
                                <span class="input-group-text"><i class="bi bi-search"></i></span>
                                <input type="text" class="form-control" id="searchConcerns" name="search" value="{{ $filters['search'] ?? '' }}" placeholder="{{ __('messages.search_in_table') }}">
                            </div>
                        </div>
                        <div class="col-12">
                            <div class="d-flex gap-2">
                                <button type="submit" class="btn btn-primary">
                                    <i class="bi bi-funnel me-1"></i>{{ __('messages.filter') }}
                                </button>
                                <a href="{{ route('driver-concerns.index') }}" class="btn btn-outline-secondary">
                                    <i class="bi bi-x-circle me-1"></i>{{ __('messages.clear_filters') }}
                                </a>
                                <a href="{{ route('driver-concerns.create') }}" class="btn btn-dark ms-auto">
                                    <i class="bi bi-plus-circle me-2"></i>{{ __('messages.add_concern') }}
                                </a>
                            </div>
                        </div>
                    </div>
                </form>
            </div>
        </div>

        <div class="card border-0 shadow-sm">
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover mb-0 align-middle">
                        <thead class="table-light">
                            <tr>
                                <th class="ps-3">{{ __('messages.reported_at') }}</th>
                                <th>{{ __('messages.driver') }}</th>
                                <th>{{ __('messages.vehicle_licence_plate') }}</th>
                                <th>{{ __('messages.concern_type') }}</th>
                                <th>{{ __('messages.status') }}</th>
                                <th class="text-end pe-3">{{ __('messages.concern_type_actions') }}</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($concerns as $concern)
                                <tr>
                                    <td class="ps-3">
                                        <div class="fw-semibold">
                                            {{ $concern->reported_at?->format(__('messages.date_format_short') ?? 'd/m/Y') }}
                                        </div>
                                        <small class="text-muted">
                                            {{ __('messages.created_at_short', ['time' => $concern->created_at->diffForHumans()]) }}
                                        </small>
                                    </td>
                                    <td>
                                        <div class="fw-semibold">{{ $concern->driver?->full_name ?? __('messages.not_available') }}</div>
                                        @if($concern->responsible_party)
                                            <small class="text-muted">{{ __('messages.responsible_party') }}: {{ $concern->responsible_party }}</small>
                                        @endif
                                    </td>
                                    <td>
                                        <span class="badge bg-secondary bg-opacity-10 text-secondary">
                                            {{ $concern->vehicle_licence_plate ?? __('messages.not_available') }}
                                        </span>
                                    </td>
                                    <td>
                                        <div class="fw-semibold">{{ $concern->concernType?->name ?? __('messages.not_available') }}</div>
                                        <small class="text-muted">
                                            {{ __('messages.priority') }}:
                                            <span class="text-capitalize">{{ $concern->concernType?->status ?? __('messages.not_available') }}</span>
                                        </small>
                                    </td>
                                    <td>
                                        @php $status = $concern->status; @endphp
                                        <span class="badge bg-{{ $statusBadges[$status] ?? 'secondary' }} bg-opacity-10 text-{{ $statusBadges[$status] ?? 'secondary' }}">
                                            {{ $statusLabels[$status] ?? ucfirst(str_replace('_', ' ', $status)) }}
                                        </span>
                                        @if($concern->completion_date)
                                            <div class="text-muted small mt-1">
                                                {{ __('messages.completed_on') }} {{ $concern->completion_date->format(__('messages.date_format_short') ?? 'd/m/Y') }}
                                            </div>
                                        @endif
                                    </td>
                                    <td class="text-end pe-3">
                                        <div class="btn-group" role="group">
                                            <a href="{{ route('driver-concerns.show', $concern) }}" class="btn btn-sm btn-outline-secondary" title="{{ __('messages.view_details') }}">
                                                <i class="bi bi-eye"></i>
                                            </a>
                                            <a href="{{ route('driver-concerns.edit', $concern) }}" class="btn btn-sm btn-outline-primary" title="{{ __('messages.edit') }}">
                                                <i class="bi bi-pencil"></i>
                                            </a>
                                            @if($concern->status !== 'closed')
                                                <button
                                                    type="button"
                                                    class="btn btn-sm btn-outline-success"
                                                    title="{{ __('messages.complete_concern') }}"
                                                    data-bs-toggle="modal"
                                                    data-bs-target="#completeConcernModal-{{ $concern->id }}"
                                                >
                                                    <i class="bi bi-check2-circle"></i>
                                                </button>
                                            @endif
                                        </div>
                                    </td>
                                </tr>
                                @if($concern->status !== 'closed')
                                    <div class="modal fade" id="completeConcernModal-{{ $concern->id }}" tabindex="-1" aria-labelledby="completeConcernModalLabel-{{ $concern->id }}" aria-hidden="true">
                                        <div class="modal-dialog">
                                            <div class="modal-content">
                                                <div class="modal-header">
                                                    <h5 class="modal-title" id="completeConcernModalLabel-{{ $concern->id }}">
                                                        {{ __('messages.complete_concern') }}
                                                    </h5>
                                                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="{{ __('messages.close') }}"></button>
                                                </div>
                                                <form action="{{ route('driver-concerns.complete', $concern) }}" method="POST">
                                                    @csrf
                                                    <div class="modal-body">
                                                        <p class="text-muted">{{ __('messages.complete_concern_confirmation') }}</p>
                                                        <div class="mb-3">
                                                            <label for="completion_date_{{ $concern->id }}" class="form-label">{{ __('messages.completion_date') }}</label>
                                                            <input type="date"
                                                                   name="completion_date"
                                                                   id="completion_date_{{ $concern->id }}"
                                                                   class="form-control"
                                                                   value="{{ now()->format('Y-m-d') }}"
                                                                   required>
                                                        </div>
                                                    </div>
                                                    <div class="modal-footer">
                                                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">{{ __('messages.cancel') }}</button>
                                                        <button type="submit" class="btn btn-success">{{ __('messages.mark_as_completed') }}</button>
                                                    </div>
                                                </form>
                                            </div>
                                        </div>
                                    </div>
                                @endif
                            @empty
                                <tr>
                                    <td colspan="6" class="text-center py-5 text-muted">
                                        <i class="bi bi-inbox fs-1 d-block mb-2"></i>
                                        {{ __('messages.no_concerns_found') }}
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>

            @if($concerns instanceof \Illuminate\Contracts\Pagination\LengthAwarePaginator)
                <div class="card-footer bg-white border-0">
                    <div class="d-flex justify-content-between align-items-center">
                        <p class="mb-0 text-muted small">
                            {{ $concerns->total() }} {{ \Illuminate\Support\Str::plural(__('messages.concerns'), $concerns->total()) }}
                        </p>
                        {{ $concerns->links() }}
                    </div>
                </div>
            @endif
        </div>
    </div>
</x-app-layout>

