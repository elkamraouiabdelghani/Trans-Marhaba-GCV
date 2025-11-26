<x-app-layout>
    <x-slot name="header">
        @include('layouts.topnav')
    </x-slot>

    <div class="container-fluid py-4 mt-4">
        <!-- Stats Cards -->
        <div class="row g-4 mb-4">
            <div class="col-xl-3 col-lg-6 col-md-6">
                <div class="card border-0 shadow-sm h-100">
                    <div class="card-body p-4">
                        <div class="d-flex align-items-center">
                            <div class="bg-primary bg-opacity-10 rounded-circle d-flex align-items-center justify-content-center me-3" style="width: 60px; height: 60px;">
                                <i class="bi bi-list-ul text-primary fs-4"></i>
                            </div>
                            <div class="flex-grow-1">
                                <h6 class="text-muted mb-1">{{ __('messages.total') }}</h6>
                                <h3 class="mb-0 fw-bold text-dark">{{ $totalCount ?? $changements->total() }}</h3>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-xl-3 col-lg-6 col-md-6">
                <div class="card border-0 shadow-sm h-100">
                    <div class="card-body p-4">
                        <div class="d-flex align-items-center">
                            <div class="bg-info bg-opacity-10 rounded-circle d-flex align-items-center justify-content-center me-3" style="width: 60px; height: 60px;">
                                <i class="bi bi-hourglass-split text-info fs-4"></i>
                            </div>
                            <div class="flex-grow-1">
                                <h6 class="text-muted mb-1">{{ __('messages.in_progress') }}</h6>
                                <h3 class="mb-0 fw-bold text-dark">{{ $inProgressCount ?? 0 }}</h3>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-xl-3 col-lg-6 col-md-6">
                <div class="card border-0 shadow-sm h-100">
                    <div class="card-body p-4">
                        <div class="d-flex align-items-center">
                            <div class="bg-success bg-opacity-10 rounded-circle d-flex align-items-center justify-content-center me-3" style="width: 60px; height: 60px;">
                                <i class="bi bi-check-circle text-success fs-4"></i>
                            </div>
                            <div class="flex-grow-1">
                                <h6 class="text-muted mb-1">{{ __('messages.completed') }}</h6>
                                <h3 class="mb-0 fw-bold text-dark">{{ $completedCount ?? 0 }}</h3>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-xl-3 col-lg-6 col-md-6">
                <div class="card border-0 shadow-sm h-100">
                    <div class="card-body p-4">
                        <div class="d-flex align-items-center">
                            <div class="bg-warning bg-opacity-10 rounded-circle d-flex align-items-center justify-content-center me-3" style="width: 60px; height: 60px;">
                                <i class="bi bi-file-check text-warning fs-4"></i>
                            </div>
                            <div class="flex-grow-1">
                                <h6 class="text-muted mb-1">{{ __('messages.approved') }}</h6>
                                <h3 class="mb-0 fw-bold text-dark">{{ $approvedCount ?? 0 }}</h3>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Changements Table -->
        <div class="card border-0 shadow-sm">
            <div class="card-header bg-white border-0 py-3">
                <div class="d-flex justify-content-between align-items-center flex-wrap gap-3">
                    <h5 class="mb-0 text-dark fw-bold">
                        <i class="bi bi-arrow-repeat me-2 text-primary"></i>
                        {{ __('messages.changements') }}
                    </h5>
                    <div class="d-flex gap-2 flex-wrap">
                        <!-- Filters Form -->
                        <form method="GET" action="{{ route('changements.index') }}" class="d-flex gap-2 flex-wrap">
                            <!-- Status Filter -->
                            <select name="status" 
                                    class="form-select form-select-sm" 
                                    style="width: auto; min-width: 150px;"
                                    onchange="this.form.submit()">
                                <option value="all" {{ request('status', 'all') === 'all' ? 'selected' : '' }}>
                                    {{ __('messages.all_statuses') }}
                                </option>
                                <option value="draft" {{ request('status') === 'draft' ? 'selected' : '' }}>
                                    {{ __('messages.draft') }}
                                </option>
                                <option value="in_progress" {{ request('status') === 'in_progress' ? 'selected' : '' }}>
                                    {{ __('messages.in_progress') }}
                                </option>
                                <option value="completed" {{ request('status') === 'completed' ? 'selected' : '' }}>
                                    {{ __('messages.completed') }}
                                </option>
                                <option value="approved" {{ request('status') === 'approved' ? 'selected' : '' }}>
                                    {{ __('messages.approved') }}
                                </option>
                            </select>

                            <!-- Changement Type Filter -->
                            @if(request('status'))
                                <input type="hidden" name="status" value="{{ request('status') }}">
                            @endif
                            @if(request('date_from'))
                                <input type="hidden" name="date_from" value="{{ request('date_from') }}">
                            @endif
                            @if(request('date_to'))
                                <input type="hidden" name="date_to" value="{{ request('date_to') }}">
                            @endif
                            <select name="changement_type_id" 
                                    class="form-select form-select-sm" 
                                    style="width: auto; min-width: 180px;"
                                    onchange="this.form.submit()">
                                <option value="all" {{ request('changement_type_id', 'all') === 'all' ? 'selected' : '' }}>
                                    {{ __('messages.all_types') }}
                                </option>
                                @foreach($changementTypes ?? [] as $type)
                                    <option value="{{ $type->id }}" {{ request('changement_type_id') == $type->id ? 'selected' : '' }}>
                                        {{ $type->name }}
                                    </option>
                                @endforeach
                            </select>

                            <!-- Date From -->
                            <input type="date" 
                                   name="date_from" 
                                   class="form-control form-control-sm" 
                                   style="width: auto; min-width: 150px;"
                                   value="{{ request('date_from') }}"
                                   placeholder="{{ __('messages.date_from') }}"
                                   onchange="this.form.submit()">

                            <!-- Date To -->
                            <input type="date" 
                                   name="date_to" 
                                   class="form-control form-control-sm" 
                                   style="width: auto; min-width: 150px;"
                                   value="{{ request('date_to') }}"
                                   placeholder="{{ __('messages.date_to') }}"
                                   onchange="this.form.submit()">

                            <!-- Clear Filters -->
                            @if(request('status') || request('changement_type_id') || request('date_from') || request('date_to'))
                                <a href="{{ route('changements.index') }}" class="btn btn-outline-secondary btn-sm">
                                    <i class="bi bi-x-circle me-1"></i>
                                    {{ __('messages.clear_filters') }}
                                </a>
                            @endif
                        </form>

                        <a href="{{ route('changements.create') }}" class="btn btn-dark btn-sm align-self-center">
                            <i class="bi bi-plus-circle me-1"></i>
                            {{ __('messages.new_changement') }}
                        </a>
                    </div>
                </div>
            </div>

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

            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover mb-0 align-middle">
                        <thead class="table-light">
                            <tr>
                                <th class="border-0 py-3 px-4">{{ __('messages.changement_type') }}</th>
                                <th class="border-0 py-3 px-4">{{ __('messages.subject') }}</th>
                                <th class="border-0 py-3 px-4">{{ __('messages.responsable') }}</th>
                                <th class="border-0 py-3 px-4">{{ __('messages.date_changement') }}</th>
                                <th class="border-0 py-3 px-4">{{ __('messages.action') }}</th>
                                <th class="border-0 py-3 px-4">{{ __('messages.current_step') }}</th>
                                <th class="border-0 py-3 px-4">{{ __('messages.status') }}</th>
                                <th class="border-0 py-3 px-4 text-center">{{ __('messages.actions') }}</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($changements as $changement)
                                <tr class="border-bottom">
                                    <td class="py-3 px-4">
                                        <div class="d-flex align-items-center">
                                            <div class="bg-primary bg-opacity-10 rounded-circle px-2 py-1 me-3">
                                                <i class="bi bi-tag text-primary"></i>
                                            </div>
                                            <div class="flex-grow-1">
                                                <strong class="text-dark">{{ $changement->changementType->name ?? __('messages.not_available') }}</strong>
                                            </div>
                                        </div>
                                    </td>
                                    <td class="py-3 px-4">
                                        @if($changement->subject)
                                            <div class="d-flex align-items-center">
                                                @if($changement->isForDriver())
                                                    <i class="bi bi-person-badge text-info me-2"></i>
                                                    <div>
                                                        <strong class="text-dark">{{ $changement->getSubjectName() }}</strong>
                                                        <br>
                                                        <small class="text-muted">{{ __('messages.driver') }}</small>
                                                    </div>
                                                @elseif($changement->isForAdministrative())
                                                    <i class="bi bi-person-gear text-warning me-2"></i>
                                                    <div>
                                                        <strong class="text-dark">{{ $changement->getSubjectName() }}</strong>
                                                        <br>
                                                        <small class="text-muted">{{ __('messages.administrative_user') }}</small>
                                                    </div>
                                                @endif
                                            </div>
                                        @else
                                            <span class="text-muted">{{ __('messages.not_specified') }}</span>
                                        @endif
                                    </td>
                                    <td class="py-3 px-4">
                                        <span class="badge bg-secondary bg-opacity-10 text-secondary">
                                            {{ $changement->responsable_changement }}
                                        </span>
                                    </td>
                                    <td class="py-3 px-4">
                                        <span class="text-muted">{{ $changement->date_changement->format('d/m/Y') }}</span>
                                    </td>
                                    <td class="py-3 px-4">
                                        @if($changement->action)
                                            <span class="text-dark">
                                                {{ \Illuminate\Support\Str::limit($changement->action, 80) }}
                                            </span>
                                        @else
                                            <span class="text-muted">
                                                <i class="bi bi-dash-circle me-1"></i>
                                                {{ __('messages.not_specified') }}
                                            </span>
                                        @endif
                                    </td>
                                    <td class="py-3 px-4">
                                        @php
                                            $stepLabels = [
                                                1 => __('messages.step_1_identification'),
                                                2 => __('messages.step_2_evaluation'),
                                                3 => __('messages.step_3_planification'),
                                                4 => __('messages.step_4_approbation'),
                                                5 => __('messages.step_5_implementation'),
                                                6 => __('messages.step_6_checklist'),
                                            ];
                                            $currentStepLabel = $stepLabels[$changement->current_step] ?? __('messages.step') . ' ' . $changement->current_step;
                                            
                                            // Calculate progress
                                            $completedSteps = 0;
                                            for ($i = 1; $i <= 6; $i++) {
                                                $step = $changement->getStep($i);
                                                if ($step && $step->isValidated()) {
                                                    $completedSteps++;
                                                }
                                            }
                                            $progressPercentage = ($completedSteps / 6) * 100;
                                        @endphp
                                        <div class="w-100">
                                            <div class="progress mb-2" style="height: 20px;">
                                                <div class="progress-bar progress-bar-striped 
                                                    bg-{{ $changement->status === 'approved' ? 'success' : ($changement->status === 'completed' ? 'info' : 'primary') }}" 
                                                    role="progressbar" 
                                                    style="width: {{ $progressPercentage }}%" 
                                                    aria-valuenow="{{ $progressPercentage }}" 
                                                    aria-valuemin="0" 
                                                    aria-valuemax="100">
                                                    <small class="text-white fw-bold">{{ $changement->current_step }}/6</small>
                                                </div>
                                            </div>
                                            <div class="text-muted small">
                                                <i class="bi bi-circle-fill text-primary" style="font-size: 0.5rem;"></i>
                                                {{ $currentStepLabel }}
                                            </div>
                                        </div>
                                    </td>
                                    <td class="py-3 px-4">
                                        @php
                                            $statusColors = [
                                                'draft' => 'secondary',
                                                'in_progress' => 'info',
                                                'completed' => 'success',
                                                'approved' => 'warning',
                                            ];
                                            $statusLabels = [
                                                'draft' => __('messages.draft'),
                                                'in_progress' => __('messages.in_progress'),
                                                'completed' => __('messages.completed'),
                                                'approved' => __('messages.approved'),
                                            ];
                                            $color = $statusColors[$changement->status] ?? 'secondary';
                                            $label = $statusLabels[$changement->status] ?? $changement->status;
                                        @endphp
                                        <span class="badge bg-{{ $color }} bg-opacity-10 text-{{ $color }}">
                                            {{ $label }}
                                        </span>
                                    </td>
                                    <td class="py-3 px-4 text-center">
                                        <div class="btn-group" role="group">
                                            <a href="{{ route('changements.show', $changement) }}" class="btn btn-outline-primary btn-sm" title="{{ __('messages.view') }}">
                                                <i class="bi bi-eye"></i>
                                            </a>
                                            @php
                                                $hasChecklist = $changement->check_list_path && \Illuminate\Support\Facades\Storage::disk('uploads')->exists($changement->check_list_path);
                                                $step5 = $changement->getStep(5);
                                                $canAccessChecklist = $step5 && $step5->isValidated();
                                            @endphp
                                            @if($hasChecklist)
                                                <a href="{{ route('changements.checklist.download', $changement) }}" 
                                                   class="btn btn-outline-success btn-sm" 
                                                   title="{{ __('messages.download_checklist') }}">
                                                    <i class="bi bi-file-pdf"></i>
                                                </a>
                                            @elseif($canAccessChecklist)
                                                <a href="{{ route('changements.checklist', $changement) }}" 
                                                   class="btn btn-outline-warning btn-sm" 
                                                   title="{{ __('messages.fill_checklist') }}">
                                                    <i class="bi bi-list-check"></i>
                                                </a>
                                            @endif
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="7" class="text-center py-5">
                                        <div class="text-muted">
                                            <i class="bi bi-inbox display-1 mb-3"></i>
                                            <h5 class="mb-2">{{ __('messages.no_changements_found') }}</h5>
                                            <p class="mb-0">{{ __('messages.no_changements_message') }}</p>
                                            <a href="{{ route('changements.create') }}" class="btn btn-dark mt-3">
                                                <i class="bi bi-plus-circle me-1"></i>
                                                {{ __('messages.new_changement') }}
                                            </a>
                                        </div>
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                <!-- Pagination -->
                @if($changements->hasPages())
                    <div class="card-footer bg-white border-0 py-3">
                        <div class="d-flex justify-content-between align-items-center">
                            <div class="text-muted small">
                                {{ __('messages.showing') }} {{ $changements->firstItem() }} {{ __('messages.to') }} {{ $changements->lastItem() }} {{ __('messages.of') }} {{ $changements->total() }} {{ __('messages.results') }}
                            </div>
                            <div>
                                {{ $changements->links() }}
                            </div>
                        </div>
                    </div>
                @endif
            </div>
        </div>
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

    <style>
        .bg-primary.bg-opacity-10 {
            background-color: rgba(13, 110, 253, 0.1) !important;
        }

        .bg-success.bg-opacity-10 {
            background-color: rgba(25, 135, 84, 0.1) !important;
        }

        .bg-danger.bg-opacity-10 {
            background-color: rgba(220, 53, 69, 0.1) !important;
        }

        .bg-info.bg-opacity-10 {
            background-color: rgba(13, 202, 240, 0.1) !important;
        }

        .bg-warning.bg-opacity-10 {
            background-color: rgba(255, 193, 7, 0.1) !important;
        }

        .table-hover tbody tr:hover {
            background-color: rgba(0, 0, 0, 0.02);
        }
    </style>
</x-app-layout>
