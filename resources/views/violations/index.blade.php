<x-app-layout>
    <x-slot name="header">
        @include('layouts.topnav')
    </x-slot>

    @php
        $statusLabels = [
            'pending' => __('messages.pending'),
            'confirmed' => __('messages.confirmed'),
            'rejected' => __('messages.rejected'),
        ];
        $statusBadges = [
            'pending' => 'warning',
            'confirmed' => 'success',
            'rejected' => 'danger',
        ];
    @endphp

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
        <div class="row g-3 mb-4">
            <div class="col-xl-3 col-md-6">
                <div class="card border-0 shadow-sm h-100">
                    <div class="card-body">
                        <div class="d-flex align-items-center">
                            <div class="rounded-circle bg-primary bg-opacity-10 d-flex align-items-center justify-content-center me-3" style="width: 52px; height: 52px;">
                                <i class="bi bi-exclamation-triangle text-primary fs-4"></i>
                            </div>
                            <div>
                                <p class="text-muted mb-1">{{ __('messages.total_violations') }}</p>
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
                                <p class="text-muted mb-1">{{ __('messages.pending_violations') }}</p>
                                <h4 class="mb-0 fw-bold text-dark">{{ $stats['pending'] ?? 0 }}</h4>
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
                                <p class="text-muted mb-1">{{ __('messages.confirmed') }}</p>
                                <h4 class="mb-0 fw-bold text-dark">{{ $stats['confirmed'] ?? 0 }}</h4>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-xl-3 col-md-6">
                <div class="card border-0 shadow-sm h-100">
                    <div class="card-body">
                        <div class="d-flex align-items-center">
                            <div class="rounded-circle bg-danger bg-opacity-10 d-flex align-items-center justify-content-center me-3" style="width: 52px; height: 52px;">
                                <i class="bi bi-x-octagon text-danger fs-4"></i>
                            </div>
                            <div>
                                <p class="text-muted mb-1">{{ __('messages.rejected') }}</p>
                                <h4 class="mb-0 fw-bold text-dark">{{ $stats['rejected'] ?? 0 }}</h4>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="card border-0 shadow-sm mb-4">
            <div class="card-body p-4">
                <form action="{{ route('violations.index') }}" method="GET" id="violationFiltersForm">
                    <div class="row g-3 align-items-end">
                        <div class="col-12 col-md-2">
                            <label for="statusFilter" class="form-label fw-semibold">{{ __('messages.status') }}</label>
                            <select name="status" id="statusFilter" class="form-select">
                                <option value="">{{ __('messages.all_status') }}</option>
                                @foreach($statuses as $key => $label)
                                    <option value="{{ $key }}" @selected(($filters['status'] ?? '') === $key)>
                                        {{ $label }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-12 col-md-2">
                            <label for="violationTypeFilter" class="form-label fw-semibold">{{ __('messages.violation_type') }}</label>
                            <select name="violation_type_id" id="violationTypeFilter" class="form-select">
                                <option value="">{{ __('messages.all_types') }}</option>
                                @foreach($violationTypes as $id => $name)
                                    <option value="{{ $id }}" @selected(($filters['violation_type_id'] ?? '') == $id)>{{ $name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-12 col-md-2">
                            <label for="flotteFilter" class="form-label fw-semibold">{{ __('messages.flotte') }}</label>
                            <select name="flotte_id" id="flotteFilter" class="form-select">
                                <option value="">{{ __('messages.all_flottes') }}</option>
                                @foreach($flottes as $id => $name)
                                    <option value="{{ $id }}" @selected(($filters['flotte_id'] ?? '') == $id)>{{ $name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-12 col-md-2">
                            <label for="dateFromFilter" class="form-label fw-semibold">{{ __('messages.date_from') }}</label>
                            <input type="date" name="date_from" id="dateFromFilter" class="form-select" value="{{ $filters['date_from'] ?? '' }}">
                        </div>
                        <div class="col-12 col-md-2">
                            <label for="dateToFilter" class="form-label fw-semibold">{{ __('messages.date_to') }}</label>
                            <input type="date" name="date_to" id="dateToFilter" class="form-select" value="{{ $filters['date_to'] ?? '' }}">
                        </div>
                        <div class="col-12 col-md-2">
                            <label for="searchViolations" class="form-label fw-semibold">{{ __('messages.search') }}</label>
                            <div class="input-group">
                                <span class="input-group-text"><i class="bi bi-search"></i></span>
                                <input type="text" class="form-control" id="searchViolations" name="search" value="{{ $filters['search'] ?? '' }}" placeholder="{{ __('messages.search_in_table') }}">
                            </div>
                        </div>
                        <div class="col-12">
                            <div class="d-flex gap-2 justify-content-between align-items-center">
                                <div class="d-flex gap-2">
                                    <button type="submit" class="btn btn-dark">
                                        <i class="bi bi-funnel me-1"></i>{{ __('messages.filter') }}
                                    </button>
                                    <a href="{{ route('violations.index') }}" class="btn btn-outline-secondary">
                                        <i class="bi bi-x-circle"></i>
                                    </a>
                                </div>
                                <div class="d-flex gap-2">
                                    <a href="{{ route('violation-types.index') }}" class="btn btn-sm btn-info">
                                        <i class="bi bi-tags me-2"></i>{{ __('messages.violation_types') }}
                                    </a>
                                    <a href="{{ route('violations.create') }}" class="btn btn-sm btn-dark ms-auto">
                                        <i class="bi bi-plus-circle me-2"></i>{{ __('messages.add') }} {{ __('messages.violation') }}
                                    </a>
                                </div>
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
                                <th class="ps-3">{{ __('messages.violation_date') }}</th>
                                <th>{{ __('messages.driver') }}</th>
                                <th>{{ __('messages.flotte') }}</th>
                                <th>{{ __('messages.violation_type') }}</th>
                                <th>{{ __('messages.location') }}</th>
                                <th>{{ __('messages.status') }}</th>
                                <th class="text-end pe-3">{{ __('messages.actions') }}</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($violations as $violation)
                                <tr>
                                    <td class="ps-3">
                                        <div class="fw-semibold">
                                            {{ $violation->violation_date?->format('d/m/Y') }}
                                        </div>
                                    </td>
                                    <td>
                                        <div class="fw-semibold">{{ $violation->driver?->full_name ?? __('messages.not_available') }}</div>
                                    </td>
                                    <td>
                                        <div class="fw-semibold">
                                            {{ $violation->flotte?->name ?? '-' }}
                                        </div>
                                    </td>
                                    <td>
                                        <div class="fw-semibold">
                                            {{ $violation->type_name }}
                                        </div>
                                    </td>
                                    <td>
                                        <span class="text-muted">
                                            {{ $violation->location ?? '-' }}
                                        </span>
                                    </td>
                                    <td>
                                        @php $status = $violation->status; @endphp
                                        <span class="badge bg-{{ $statusBadges[$status] ?? 'secondary' }} bg-opacity-10 text-{{ $statusBadges[$status] ?? 'secondary' }}">
                                            {{ $statusLabels[$status] ?? ucfirst($status) }}
                                        </span>
                                    </td>
                                    <td class="text-end pe-3">
                                        <div class="btn-group" role="group">
                                            <button type="button" class="btn btn-sm btn-outline-info" data-bs-toggle="modal" data-bs-target="#changeStatusModal" data-violation-id="{{ $violation->id }}" data-current-status="{{ $violation->status }}" data-violation-driver="{{ $violation->driver?->full_name ?? __('messages.not_available') }}" title="{{ __('messages.change_status') }}">
                                                <i class="bi bi-arrow-repeat"></i>
                                            </button>
                                            <a href="{{ route('violations.show', $violation) }}" class="btn btn-sm btn-outline-secondary" title="{{ __('messages.view_details') }}">
                                                <i class="bi bi-eye"></i>
                                            </a>
                                            <a href="{{ route('violations.edit', $violation) }}" class="btn btn-sm btn-outline-primary" title="{{ __('messages.edit') }}">
                                                <i class="bi bi-pencil"></i>
                                            </a>
                                            <a href="{{ route('violations.report', $violation) }}" class="btn btn-sm btn-outline-danger" title="{{ __('messages.download_report') }}">
                                                <i class="bi bi-file-earmark-arrow-down"></i>
                                            </a>
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="6" class="text-center py-5 text-muted">
                                        <i class="bi bi-inbox fs-1 d-block mb-2"></i>
                                        {{ __('messages.no_violations_found') }}
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>

            @if ($violations instanceof \Illuminate\Contracts\Pagination\LengthAwarePaginator)
                <div class="card-footer bg-white border-0">
                    <div class="d-flex justify-content-between align-items-center">
                        <div class="text-muted small">
                            {{ __('messages.showing') }} {{ $violations->firstItem() }} {{ __('messages.to') }} {{ $violations->lastItem() }} {{ __('messages.of') }} {{ $violations->total() }}
                        </div>
                        {{ $violations->links() }}
                    </div>
                </div>
            @endif
        </div>
    </div>

    <!-- Change Status Modal -->
    <div class="modal fade" id="changeStatusModal" tabindex="-1" aria-labelledby="changeStatusModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header bg-info">
                    <h5 class="modal-title" id="changeStatusModalLabel">
                        <i class="bi bi-arrow-repeat me-2"></i>{{ __('messages.change_status') }}
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form id="changeStatusForm" method="POST">
                    @csrf
                    @method('POST')
                    <div class="modal-body">
                        <div class="mb-3">
                            <label class="form-label text-muted small">{{ __('messages.driver') }}</label>
                            <p class="fw-semibold mb-0" id="modal-violation-driver"></p>
                        </div>
                        <div class="mb-3">
                            <label class="form-label text-muted small">{{ __('messages.current_status') }}</label>
                            <p class="mb-0">
                                <span class="badge" id="modal-current-status-badge"></span>
                            </p>
                        </div>
                        <div class="mb-3">
                            <label for="new_status" class="form-label fw-semibold">{{ __('messages.new_status') }} <span class="text-danger">*</span></label>
                            <select class="form-select @error('status') is-invalid @enderror" id="new_status" name="status" required>
                                <option value="">{{ __('messages.select_status') }}</option>
                                <option value="pending" data-badge="warning">{{ __('messages.pending') }}</option>
                                <option value="confirmed" data-badge="success">{{ __('messages.confirmed') }}</option>
                                <option value="rejected" data-badge="danger">{{ __('messages.rejected') }}</option>
                            </select>
                            @error('status')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">{{ __('messages.cancel') }}</button>
                        <button type="submit" class="btn btn-dark">
                            <i class="bi bi-check-circle me-1"></i>{{ __('messages.update') }}
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</x-app-layout>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const changeStatusModal = document.getElementById('changeStatusModal');
    const changeStatusForm = document.getElementById('changeStatusForm');
    const statusSelect = document.getElementById('new_status');
    const currentStatusBadge = document.getElementById('modal-current-status-badge');
    const violationDriver = document.getElementById('modal-violation-driver');

    const statusLabels = {
        'pending': '{{ __('messages.pending') }}',
        'confirmed': '{{ __('messages.confirmed') }}',
        'rejected': '{{ __('messages.rejected') }}'
    };

    const statusBadges = {
        'pending': 'warning',
        'confirmed': 'success',
        'rejected': 'danger'
    };

    if (changeStatusModal) {
        changeStatusModal.addEventListener('show.bs.modal', function(event) {
            const button = event.relatedTarget;
            const violationId = button.getAttribute('data-violation-id');
            const currentStatus = button.getAttribute('data-current-status');
            const driverName = button.getAttribute('data-violation-driver');

            // Update form action
            changeStatusForm.action = '{{ route("violations.update-status", ":id") }}'.replace(':id', violationId);

            // Update driver name
            violationDriver.textContent = driverName;

            // Update current status badge
            const badgeColor = statusBadges[currentStatus] || 'secondary';
            currentStatusBadge.className = 'badge bg-' + badgeColor + ' bg-opacity-10 text-' + badgeColor;
            currentStatusBadge.textContent = statusLabels[currentStatus] || currentStatus;

            // Reset and disable current status option
            statusSelect.value = '';
            Array.from(statusSelect.options).forEach(option => {
                option.disabled = option.value === currentStatus;
            });
        });

        // Reset form when modal is hidden
        changeStatusModal.addEventListener('hidden.bs.modal', function() {
            changeStatusForm.reset();
            Array.from(statusSelect.options).forEach(option => {
                option.disabled = false;
            });
        });
    }
});
</script>

