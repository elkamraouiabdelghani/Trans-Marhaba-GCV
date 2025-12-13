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
                            <label for="yearFilter" class="form-label fw-semibold">{{ __('messages.year') ?? 'Year' }}</label>
                            <select name="year" id="yearFilter" class="form-select">
                                @foreach($availableYears as $year)
                                    <option value="{{ $year }}" @selected($selectedYear == $year)>{{ $year }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-12 col-md-2">
                            <label for="monthFilter" class="form-label fw-semibold">{{ __('messages.month') ?? 'Month' }}</label>
                            <select name="month" id="monthFilter" class="form-select">
                                @for($i = 1; $i <= 12; $i++)
                                    <option value="{{ $i }}" @selected($selectedMonth == $i)>
                                        {{ \Carbon\Carbon::create(null, $i, 1)->translatedFormat('F') }}
                                    </option>
                                @endfor
                            </select>
                        </div>
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
        </div>
    </div>

</x-app-layout>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Auto-submit form when year or month changes
    const yearFilter = document.getElementById('yearFilter');
    const monthFilter = document.getElementById('monthFilter');
    const violationFiltersForm = document.getElementById('violationFiltersForm');
    
    if (yearFilter && violationFiltersForm) {
        yearFilter.addEventListener('change', function() {
            violationFiltersForm.submit();
        });
    }
    
    if (monthFilter && violationFiltersForm) {
        monthFilter.addEventListener('change', function() {
            violationFiltersForm.submit();
        });
    }

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

});
</script>

