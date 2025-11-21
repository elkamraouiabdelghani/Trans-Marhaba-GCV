<x-app-layout>
    <x-slot name="header">
        @include('layouts.topnav')
    </x-slot>

    <div class="container-fluid py-4 mt-4">
        <!-- User Information Box -->
        <div class="card border-0 shadow-sm mb-4">
            <nav aria-label="breadcrumb" class="p-3">
                <div class="d-flex justify-content-between align-items-center">
                    <ol class="breadcrumb mb-0">
                        <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">{{ __('messages.dashboard') }}</a></li>
                        <li class="breadcrumb-item"><a href="{{ route('administration-roles.index') }}">{{ __('messages.administration_roles') }}</a></li>
                        <li class="breadcrumb-item active">{{ $user->name }}</li>
                    </ol>
                    
                    <div class="d-flex gap-2">
                        @if($user->status !== 'terminated')
                            <button type="button" class="btn btn-danger btn-sm" data-bs-toggle="modal" data-bs-target="#terminateModal">
                                <i class="bi bi-x-circle me-1"></i>
                                {{ __('messages.mark_as_terminated') }}
                            </button>
                        @endif
                        <a href="{{ route('administration-roles.index') }}" class="btn btn-outline-secondary btn-sm">
                            <i class="bi bi-arrow-left me-1"></i>
                            {{ __('messages.back_to_list') }}
                        </a>
                    </div>
                </div>
            </nav>

            <hr class="my-2">

            <div class="card-body p-4">
                <div class="row align-items-center">
                    <div class="col-md-2 text-center mb-3 mb-md-0">
                        <div class="position-relative d-inline-block">
                            <div class="bg-primary bg-opacity-10 rounded-circle d-flex align-items-center justify-content-center" style="width: 120px; height: 120px;">
                                <i class="bi bi-person-gear text-primary" style="font-size: 4rem;"></i>
                            </div>
                            @if($user->status === 'active')
                                <span class="badge bg-success position-absolute top-0 end-0 rounded-circle d-flex align-items-center justify-content-center" style="width: 24px; height: 24px; padding: 0;">
                                    <i class="bi bi-check"></i>
                                </span>
                            @endif
                        </div>
                    </div>
                    <div class="col-md-10">
                        <div class="row g-3">
                            <div class="col-md-6">
                                <h3 class="mb-2 text-dark fw-bold">{{ $user->name }}</h3>
                                <div class="row g-3">
                                    <div class="col-12">
                                        <small class="text-muted d-block">{{ __('messages.email') }}</small>
                                        <strong class="text-dark">{{ $user->email ?? __('messages.not_available') }}</strong>
                                    </div>
                                    <div class="col-6">
                                        <small class="text-muted d-block">{{ __('messages.phone') }}</small>
                                        <span class="text-dark">{{ $user->phone ?? __('messages.not_available') }}</span>
                                    </div>
                                    <div class="col-6">
                                        <small class="text-muted d-block">{{ __('messages.department') }}</small>
                                        <span class="text-dark">{{ $user->department ?? __('messages.not_available') }}</span>
                                    </div>
                                    <div class="col-6">
                                        <small class="text-muted d-block">{{ __('messages.role') }}</small>
                                        <span class="badge bg-primary bg-opacity-10 text-primary text-uppercase">{{ $user->role }}</span>
                                    </div>
                                    <div class="col-6">
                                        <small class="text-muted d-block">{{ __('messages.status') }}</small>
                                        @php
                                            $statusColorMap = [
                                                'active' => 'success',
                                                'inactive' => 'secondary',
                                                'on_leave' => 'warning',
                                                'terminated' => 'danger',
                                            ];
                                            $statusKey = 'status_' . ($user->status ?? 'inactive');
                                            $badgeColor = $statusColorMap[$user->status] ?? 'secondary';
                                        @endphp
                                        <span class="badge bg-{{ $badgeColor }} bg-opacity-10 text-{{ $badgeColor }}">
                                            {{ __('messages.' . $statusKey) }}
                                        </span>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="row g-3">
                                    <div class="col-md-6">
                                        <div class="bg-info bg-opacity-10 rounded p-3 text-center h-100">
                                            <i class="bi bi-arrow-repeat text-info fs-4 d-block mb-2"></i>
                                            <small class="text-muted d-block">{{ __('messages.turnovers') }}</small>
                                            <h4 class="mb-0 fw-bold text-dark">{{ $user->turnovers->count() }}</h4>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="bg-warning bg-opacity-10 rounded p-3 text-center h-100">
                                            <i class="bi bi-list-check text-warning fs-4 d-block mb-2"></i>
                                            <small class="text-muted d-block">{{ __('messages.changements') }}</small>
                                            <h4 class="mb-0 fw-bold text-dark">{{ $user->changements->count() }}</h4>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Additional Information -->
        <div class="row">
            <!-- Personal Information -->
            <div class="col-lg-6 mb-4">
                <div class="card border-0 shadow-sm h-100">
                    <div class="card-header bg-white border-0 py-3">
                        <h6 class="mb-0 fw-bold">
                            <i class="bi bi-person-circle me-2 text-primary"></i>
                            {{ __('messages.personal_information') }}
                        </h6>
                    </div>
                    <div class="card-body">
                        <div class="row g-3">
                            <div class="col-12">
                                <div class="d-flex justify-content-between align-items-start mb-2">
                                    <small class="text-muted">{{ __('messages.name') }}</small>
                                </div>
                                <strong class="text-dark">{{ $user->name }}</strong>
                            </div>
                            <div class="col-12">
                                <div class="d-flex justify-content-between align-items-start mb-2">
                                    <small class="text-muted">{{ __('messages.email') }}</small>
                                </div>
                                <strong class="text-dark">{{ $user->email ?? __('messages.not_available') }}</strong>
                            </div>
                            <div class="col-12">
                                <div class="d-flex justify-content-between align-items-start mb-2">
                                    <small class="text-muted">{{ __('messages.phone') }}</small>
                                </div>
                                <strong class="text-dark">{{ $user->phone ?? __('messages.not_available') }}</strong>
                            </div>
                            <div class="col-12">
                                <div class="d-flex justify-content-between align-items-start mb-2">
                                    <small class="text-muted">{{ __('messages.department') }}</small>
                                </div>
                                <strong class="text-dark">{{ $user->department ?? __('messages.not_available') }}</strong>
                            </div>
                            <div class="col-12">
                                <div class="d-flex justify-content-between align-items-start mb-2">
                                    <small class="text-muted">{{ __('messages.role') }}</small>
                                </div>
                                <span class="badge bg-primary bg-opacity-10 text-primary text-uppercase">{{ $user->role }}</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Professional Information -->
            <div class="col-lg-6 mb-4">
                <div class="card border-0 shadow-sm h-100">
                    <div class="card-header bg-white border-0 py-3">
                        <h6 class="mb-0 fw-bold">
                            <i class="bi bi-briefcase me-2 text-primary"></i>
                            {{ __('messages.administrative_information') }}
                        </h6>
                    </div>
                    <div class="card-body">
                        <div class="row g-3">
                            <div class="col-12">
                                <div class="d-flex justify-content-between align-items-start mb-2">
                                    <small class="text-muted">{{ __('messages.status') }}</small>
                                </div>
                                @php
                                    $statusColorMap = [
                                        'active' => 'success',
                                        'inactive' => 'secondary',
                                        'on_leave' => 'warning',
                                        'terminated' => 'danger',
                                    ];
                                    $statusKey = 'status_' . ($user->status ?? 'inactive');
                                    $badgeColor = $statusColorMap[$user->status] ?? 'secondary';
                                @endphp
                                <span class="badge bg-{{ $badgeColor }} bg-opacity-10 text-{{ $badgeColor }}">
                                    {{ __('messages.' . $statusKey) }}
                                </span>
                            </div>
                            <div class="col-12">
                                <div class="d-flex justify-content-between align-items-start mb-2">
                                    <small class="text-muted">{{ __('messages.date_integration') }}</small>
                                </div>
                                <strong class="text-dark">
                                    {{ optional($user->date_integration)->format(__('messages.date_format_short')) ?? __('messages.not_available') }}
                                </strong>
                            </div>
                            <div class="col-12">
                                <div class="d-flex justify-content-between align-items-start mb-2">
                                    <small class="text-muted">{{ __('messages.integrated_staff') }}</small>
                                </div>
                                @if($user->is_integrated)
                                    <span class="badge bg-success bg-opacity-10 text-success">
                                        <i class="bi bi-check-circle me-1"></i>
                                        {{ __('messages.yes') }}
                                    </span>
                                @else
                                    <span class="badge bg-secondary bg-opacity-10 text-secondary">
                                        <i class="bi bi-x-circle me-1"></i>
                                        {{ __('messages.no') }}
                                    </span>
                                @endif
                            </div>
                            @if($user->terminated_date)
                            <div class="col-12">
                                <div class="d-flex justify-content-between align-items-start mb-2">
                                    <small class="text-muted">{{ __('messages.terminated_date') }}</small>
                                </div>
                                <strong class="text-dark">
                                    {{ $user->terminated_date->format(__('messages.date_format_short')) }}
                                </strong>
                            </div>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Related Data -->
        <div class="row">
            <!-- Turnovers -->
            <div class="col-lg-6 mb-4">
                <div class="card border-0 shadow-sm h-100">
                    <div class="card-header bg-white border-0 py-3">
                        <div class="d-flex justify-content-between align-items-center">
                            <h6 class="mb-0 fw-bold">
                                <i class="bi bi-arrow-repeat me-2 text-primary"></i>
                                {{ __('messages.turnovers') }}
                            </h6>
                            <span class="badge bg-info">{{ $user->turnovers->count() }}</span>
                        </div>
                    </div>
                    <div class="card-body">
                        @if($user->turnovers->count() > 0)
                            <div class="table-responsive">
                                <table class="table table-sm table-hover">
                                    <thead>
                                        <tr>
                                            <th>{{ __('messages.departure_date') }}</th>
                                            <th>{{ __('messages.position') }}</th>
                                            <th>{{ __('messages.status') }}</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach($user->turnovers->take(5) as $turnover)
                                            <tr>
                                                <td>{{ optional($turnover->departure_date)->format(__('messages.date_format_short')) ?? __('messages.not_available') }}</td>
                                                <td>{{ $turnover->position ?? __('messages.not_available') }}</td>
                                                <td>
                                                    @if($turnover->confirmed)
                                                        <span class="badge bg-success bg-opacity-10 text-success">{{ __('messages.confirmed') }}</span>
                                                    @else
                                                        <span class="badge bg-warning bg-opacity-10 text-warning">{{ __('messages.pending') }}</span>
                                                    @endif
                                                </td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                            @if($user->turnovers->count() > 5)
                                <div class="text-center mt-3">
                                    <small class="text-muted">{{ __('messages.showing') }} 5 {{ __('messages.of') }} {{ $user->turnovers->count() }}</small>
                                </div>
                            @endif
                        @else
                            <div class="text-center py-4">
                                <i class="bi bi-inbox text-muted" style="font-size: 3rem;"></i>
                                <p class="text-muted mt-3 mb-0">{{ __('messages.no_turnovers_found') }}</p>
                            </div>
                        @endif
                    </div>
                </div>
            </div>

            <!-- Changements -->
            <div class="col-lg-6 mb-4">
                <div class="card border-0 shadow-sm h-100">
                    <div class="card-header bg-white border-0 py-3">
                        <div class="d-flex justify-content-between align-items-center">
                            <h6 class="mb-0 fw-bold">
                                <i class="bi bi-list-check me-2 text-primary"></i>
                                {{ __('messages.changements') }}
                            </h6>
                            <span class="badge bg-warning">{{ $user->changements->count() }}</span>
                        </div>
                    </div>
                    <div class="card-body">
                        @if($user->changements->count() > 0)
                            <div class="table-responsive">
                                <table class="table table-sm table-hover">
                                    <thead>
                                        <tr>
                                            <th>{{ __('messages.changement_type') }}</th>
                                            <th>{{ __('messages.date_changement') }}</th>
                                            <th>{{ __('messages.status') }}</th>
                                            <th class="text-center">{{ __('messages.actions') }}</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach($user->changements->take(5) as $changement)
                                            <tr>
                                                <td>{{ $changement->changementType->name ?? __('messages.not_available') }}</td>
                                                <td>{{ optional($changement->date_changement)->format(__('messages.date_format_short')) ?? __('messages.not_available') }}</td>
                                                <td>
                                                    @php
                                                        $statusColors = [
                                                            'draft' => 'secondary',
                                                            'in_progress' => 'info',
                                                            'completed' => 'success',
                                                            'approved' => 'warning',
                                                        ];
                                                        $color = $statusColors[$changement->status] ?? 'secondary';
                                                        $statusLabels = [
                                                            'draft' => __('messages.draft'),
                                                            'in_progress' => __('messages.in_progress'),
                                                            'completed' => __('messages.completed'),
                                                            'approved' => __('messages.approved'),
                                                        ];
                                                        $label = $statusLabels[$changement->status] ?? $changement->status;
                                                    @endphp
                                                    <span class="badge bg-{{ $color }} bg-opacity-10 text-{{ $color }}">
                                                        {{ $label }}
                                                    </span>
                                                </td>
                                                <td class="text-center">
                                                    @if($changement->check_list_path && \Illuminate\Support\Facades\Storage::disk('uploads')->exists($changement->check_list_path))
                                                        <a href="{{ route('changements.checklist.download', $changement) }}" 
                                                           class="btn btn-outline-success btn-sm" 
                                                           title="{{ __('messages.download_checklist') }}">
                                                            <i class="bi bi-download"></i>
                                                        </a>
                                                    @else
                                                        <span class="text-muted" title="{{ __('messages.checklist_not_available') }}">
                                                            <i class="bi bi-dash-circle"></i>
                                                        </span>
                                                    @endif
                                                </td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                            @if($user->changements->count() > 5)
                                <div class="text-center mt-3">
                                    <small class="text-muted">{{ __('messages.showing') }} 5 {{ __('messages.of') }} {{ $user->changements->count() }}</small>
                                </div>
                            @endif
                        @else
                            <div class="text-center py-4">
                                <i class="bi bi-inbox text-muted" style="font-size: 3rem;"></i>
                                <p class="text-muted mt-3 mb-0">{{ __('messages.no_changements_found') }}</p>
                            </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Terminate User Modal -->
    @if($user->status !== 'terminated')
    <div class="modal fade" id="terminateModal" tabindex="-1" aria-labelledby="terminateModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header bg-danger text-white">
                    <h5 class="modal-title" id="terminateModalLabel">
                        <i class="bi bi-exclamation-triangle me-2"></i>
                        {{ __('messages.mark_as_terminated') }}
                    </h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form action="{{ route('administration-roles.terminate', $user) }}" method="POST">
                    @csrf
                    <div class="modal-body">
                        <div class="alert alert-warning" role="alert">
                            <i class="bi bi-exclamation-triangle me-2"></i>
                            {{ __('messages.terminate_user_warning', ['name' => $user->name]) }}
                        </div>
                        <div class="mb-3">
                            <label for="terminated_date" class="form-label">
                                {{ __('messages.terminated_date') }} <span class="text-danger">*</span>
                            </label>
                            <input type="date" 
                                   class="form-control @error('terminated_date') is-invalid @enderror" 
                                   id="terminated_date" 
                                   name="terminated_date" 
                                   value="{{ old('terminated_date', date('Y-m-d')) }}" 
                                   required>
                            @error('terminated_date')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                            <small class="form-text text-muted">{{ __('messages.terminated_date_help') }}</small>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                            {{ __('messages.cancel') }}
                        </button>
                        <button type="submit" class="btn btn-danger">
                            <i class="bi bi-x-circle me-1"></i>
                            {{ __('messages.confirm_termination') }}
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    @endif

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

    <script>
        // Initialize and show toasts on page load
        document.addEventListener('DOMContentLoaded', function() {
            const toasts = document.querySelectorAll('.toast.show');
            toasts.forEach(function(toastEl) {
                if (typeof bootstrap !== 'undefined') {
                    const toast = new bootstrap.Toast(toastEl);
                    toast.show();
                }
            });
        });
    </script>
</x-app-layout>

