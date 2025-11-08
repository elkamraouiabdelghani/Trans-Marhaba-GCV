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

        <!-- Integrations Table -->
        <div class="card border-0 shadow-sm">
            <div class="card-header bg-white border-0 py-3">
                <div class="d-flex justify-content-between align-items-center">
                    <h5 class="mb-0 text-dark fw-bold">
                        <i class="bi bi-person-check me-2 text-primary"></i>
                        {{ __('messages.driver_integrations') }}
                    </h5>
                    <div class="d-flex gap-2">
                        <a href="{{ route('integrations.create') }}" class="btn btn-dark btn-sm">
                            <i class="bi bi-plus-circle me-1"></i>
                            {{ __('messages.start_new_integration') }}
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
                    <table class="table table-hover mb-0 align-middle" id="integrationsTable">
                        <thead class="table-light">
                            <tr>
                                <th class="border-0 py-3 px-4">#</th>
                                <th class="border-0 py-3 px-4">{{ __('messages.driver') }}</th>
                                <th class="border-0 py-3 px-4">{{ __('messages.poste_type') }}</th>
                                <th class="border-0 py-3 px-4">{{ __('messages.current_step') }}</th>
                                <th class="border-0 py-3 px-4">{{ __('messages.status') }}</th>
                                <th class="border-0 py-3 px-4">{{ __('messages.started_at') }}</th>
                                <th class="border-0 py-3 px-4 text-center">{{ __('messages.action') }}</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($integrations ?? [] as $integration)
                                <tr class="border-bottom">
                                    <td class="py-3 px-4">
                                        <strong class="text-muted">#{{ $integration->id }}</strong>
                                    </td>
                                    <td class="py-3 px-4">
                                        @php
                                            $step2 = $integration->getStep(2);
                                            $name = $step2 ? $step2->getStepData('full_name') : __('messages.driver_not_created_yet');
                                        @endphp
                                        <div class="d-flex align-items-center">
                                            <div class="bg-primary bg-opacity-10 rounded-circle px-2 py-1 me-3">
                                                <i class="bi bi-person text-primary"></i>
                                            </div>
                                            <div class="flex-grow-1">
                                                <strong class="text-dark">{{ $name }}</strong>
                                            </div>
                                        </div>
                                    </td>
                                    <td class="py-3 px-4">
                                        @if($integration->poste_type === 'chauffeur')
                                            <span class="badge bg-info bg-opacity-25 text-info">
                                                {{ __('messages.chauffeurs') }}
                                            </span>
                                        @else
                                            <span class="badge bg-secondary bg-opacity-25 text-secondary">
                                                {{ __('messages.administratife') }}
                                            </span>
                                        @endif
                                    </td>
                                    <td class="py-3 px-4">
                                        @php
                                            // Calculate progress
                                            $completedSteps = 0;
                                            for ($i = 1; $i <= 8; $i++) {
                                                $step = $integration->getStep($i);
                                                if ($step && $step->isValidated()) {
                                                    $completedSteps++;
                                                }
                                            }
                                            $progressPercentage = ($completedSteps / 8) * 100;
                                            
                                            // Get current step label
                                            $stepLabels = [
                                                1 => __('messages.identification_besoin'),
                                                2 => __('messages.driver_creation'),
                                                3 => __('messages.verification_documentaire'),
                                                4 => __('messages.test_oral_ecrit'),
                                                5 => __('messages.test_conduite'),
                                                6 => __('messages.validation') . ' + ' . __('messages.induction') . ' + ' . __('messages.signature_contrat'),
                                                7 => __('messages.accompagnement'),
                                                8 => __('messages.validation_finale'),
                                            ];
                                            $currentStepLabel = $stepLabels[$integration->current_step] ?? __('messages.step') . ' ' . $integration->current_step;
                                        @endphp
                                        <div class="w-100">
                                            <div class="progress mb-2" style="height: 20px;">
                                                <div class="progress-bar progress-bar-striped 
                                                    bg-{{ $integration->status === 'validated' ? 'success' : ($integration->status === 'rejected' ? 'danger' : 'primary') }}" 
                                                    role="progressbar" 
                                                    style="width: {{ $progressPercentage }}%" 
                                                    aria-valuenow="{{ $progressPercentage }}" 
                                                    aria-valuemin="0" 
                                                    aria-valuemax="100">
                                                    <small class="text-white fw-bold">{{ $integration->current_step }}/8</small>
                                                </div>
                                            </div>
                                            <div class="text-muted small">
                                                <i class="bi bi-circle-fill text-primary" style="font-size: 0.5rem;"></i>
                                                <strong>{{ __('messages.current_step') }}:</strong> {{ $currentStepLabel }}
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
                                            $color = $statusColors[$integration->status] ?? 'secondary';
                                            $label = $statusLabels[$integration->status] ?? $integration->status;
                                        @endphp
                                        <span class="badge bg-{{ $color }} bg-opacity-10 text-{{ $color }}">
                                            {{ $label }}
                                        </span>
                                    </td>
                                    <td class="py-3 px-4">
                                        <span class="text-muted">{{ $integration->created_at->format('d/m/Y') }}</span>
                                    </td>
                                    <td class="py-3 px-4 text-center">
                                        <div class="btn-group" role="group">
                                            <a href="{{ route('integrations.show', $integration) }}" class="btn btn-outline-primary btn-sm" title="{{ __('messages.view') }}">
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
                                            <h5 class="mb-2">{{ __('messages.no_integrations_found') }}</h5>
                                            <p class="mb-0">{{ __('messages.no_integrations_message') }}</p>
                                            <a href="{{ route('integrations.create') }}" class="btn btn-dark mt-3">
                                                <i class="bi bi-plus-circle me-1"></i>
                                                {{ __('messages.start_new_integration') }}
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

