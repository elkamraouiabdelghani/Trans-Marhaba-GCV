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
                                <h3 class="mb-0 fw-bold text-dark">{{ $total ?? 0 }}</h3>
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
                                <h3 class="mb-0 fw-bold text-dark">{{ $inProgress ?? 0 }}</h3>
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
                                <h6 class="text-muted mb-1">{{ __('messages.validated') }}</h6>
                                <h3 class="mb-0 fw-bold text-dark">{{ $validated ?? 0 }}</h3>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-xl-3 col-lg-6 col-md-6">
                <div class="card border-0 shadow-sm h-100">
                    <div class="card-body p-4">
                        <div class="d-flex align-items-center">
                            <div class="bg-danger bg-opacity-10 rounded-circle d-flex align-items-center justify-content-center me-3" style="width: 60px; height: 60px;">
                                <i class="bi bi-x-circle text-danger fs-4"></i>
                            </div>
                            <div class="flex-grow-1">
                                <h6 class="text-muted mb-1">{{ __('messages.rejected') }}</h6>
                                <h3 class="mb-0 fw-bold text-dark">{{ $rejected ?? 0 }}</h3>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Formation Processes Table -->
        <div class="card border-0 shadow-sm">
            <div class="card-header bg-white border-0 py-3">
                <div class="d-flex justify-content-between align-items-center">
                    <h5 class="mb-0 text-dark fw-bold">
                        <i class="bi bi-book me-2 text-primary"></i>
                        {{ __('messages.formation_process_list') }}
                    </h5>
                    <div class="d-flex gap-2">
                        <a href="{{ route('formation-processes.create') }}" class="btn btn-dark btn-sm">
                            <i class="bi bi-plus-circle me-1"></i>
                            {{ __('messages.start_formation_process') }}
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
                    <table class="table table-hover mb-0 align-middle" id="formationProcessesTable">
                        <thead class="table-light">
                            <tr>
                                <th class="border-0 py-3 px-4">{{ __('messages.driver') }}</th>
                                <th class="border-0 py-3 px-4">{{ __('messages.formation_name') }}</th>
                                <th class="border-0 py-3 px-4">{{ __('messages.current_step') }}</th>
                                <th class="border-0 py-3 px-4">{{ __('messages.status') }}</th>
                                <th class="border-0 py-3 px-4">{{ __('messages.started_at') }}</th>
                                <th class="border-0 py-3 px-4 text-center">{{ __('messages.action') }}</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($formationProcesses ?? [] as $formationProcess)
                                <tr class="border-bottom">
                                    <td class="py-3 px-4">
                                        <div class="d-flex align-items-center">
                                            <div class="bg-primary bg-opacity-10 rounded-circle px-2 py-1 me-3">
                                                <i class="bi bi-person text-primary"></i>
                                            </div>
                                            <div class="flex-grow-1">
                                                <strong class="text-dark">{{ $formationProcess->driver->full_name ?? 'N/A' }}</strong>
                                            </div>
                                        </div>
                                    </td>
                                    <td class="py-3 px-4">
                                        <span class="badge bg-info bg-opacity-25 text-info">
                                            {{ $formationProcess->formation->name ?? 'N/A' }}
                                        </span>
                                    </td>
                                    <td class="py-3 px-4">
                                        @php
                                            $completedSteps = 0;
                                            for ($i = 1; $i <= 7; $i++) {
                                                $step = $formationProcess->getStep($i);
                                                if ($step && $step->isValidated()) {
                                                    $completedSteps++;
                                                }
                                            }
                                            $progressPercentage = ($completedSteps / 7) * 100;
                                        @endphp
                                        <div class="w-100">
                                            <div class="progress mb-2" style="height: 20px;">
                                                <div class="progress-bar progress-bar-striped 
                                                    bg-{{ $formationProcess->status === 'validated' ? 'success' : ($formationProcess->status === 'rejected' ? 'danger' : 'primary') }}" 
                                                    role="progressbar" 
                                                    style="width: {{ $progressPercentage }}%" 
                                                    aria-valuenow="{{ $progressPercentage }}" 
                                                    aria-valuemin="0" 
                                                    aria-valuemax="100">
                                                    <small class="text-white fw-bold">{{ $formationProcess->current_step }}/7</small>
                                                </div>
                                            </div>
                                        </div>
                                    </td>
                                    <td class="py-3 px-4">
                                        @php
                                            $statusColors = [
                                                'draft' => 'secondary',
                                                'in_progress' => 'info',
                                                'validated' => 'success',
                                                'rejected' => 'danger',
                                            ];
                                            $statusLabels = [
                                                'draft' => __('messages.not_started'),
                                                'in_progress' => __('messages.in_progress'),
                                                'validated' => __('messages.validated'),
                                                'rejected' => __('messages.rejected'),
                                            ];
                                            $color = $statusColors[$formationProcess->status] ?? 'secondary';
                                            $label = $statusLabels[$formationProcess->status] ?? $formationProcess->status;
                                        @endphp
                                        <span class="badge bg-{{ $color }} bg-opacity-10 text-{{ $color }}">
                                            {{ $label }}
                                        </span>
                                    </td>
                                    <td class="py-3 px-4">
                                        <span class="text-muted">{{ $formationProcess->created_at->format('d/m/Y') }}</span>
                                    </td>
                                    <td class="py-3 px-4 text-center">
                                        <div class="btn-group" role="group">
                                            <a href="{{ route('formation-processes.show', $formationProcess) }}" class="btn btn-outline-primary btn-sm" title="{{ __('messages.view') }}">
                                                <i class="bi bi-eye"></i>
                                            </a>
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="7" class="text-center py-5">
                                        <div class="text-muted">
                                            <i class="bi bi-inbox display-1 mb-3"></i>
                                            <h5 class="mb-2">{{ __('messages.no_formation_processes_found') }}</h5>
                                            <p class="mb-0">{{ __('messages.no_formation_processes_message') }}</p>
                                            <a href="{{ route('formation-processes.create') }}" class="btn btn-dark mt-3">
                                                <i class="bi bi-plus-circle me-1"></i>
                                                {{ __('messages.start_formation_process') }}
                                            </a>
                                        </div>
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
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

        .table-hover tbody tr:hover {
            background-color: rgba(0, 0, 0, 0.02);
        }
    </style>
</x-app-layout>

