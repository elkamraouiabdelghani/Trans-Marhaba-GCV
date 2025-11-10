<x-app-layout>
    <x-slot name="header">
        @include('layouts.topnav')
    </x-slot>

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
        <!-- Header -->
        <div class="card border-0 shadow-sm mb-4">
            <div class="card-header bg-white border-0 py-3">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h5 class="mb-0 text-dark fw-bold">
                            <i class="bi bi-book me-2 text-primary"></i>
                            {{ __('messages.formation_process_progress') }} #{{ $formationProcess->id }}
                        </h5>
                        <small class="text-muted">
                            {{ $formationProcess->driver->full_name ?? 'N/A' }} - {{ $formationProcess->formationType->name ?? 'N/A' }}
                        </small>
                    </div>
                    <a href="{{ route('formation-processes.index') }}" class="btn btn-outline-secondary btn-sm">
                        <i class="bi bi-arrow-left me-1"></i>
                        {{ __('messages.back') }}
                    </a>
                </div>
            </div>
            <!-- Progress Bar -->
            <div class="card-body bg-light">
                @php
                    $completedSteps = 0;
                    $totalSteps = 8;
                    for ($i = 1; $i <= 8; $i++) {
                        $step = $formationProcess->getStep($i);
                        if ($step && $step->isValidated()) {
                            $completedSteps++;
                        }
                    }
                    $progressPercentage = ($completedSteps / $totalSteps) * 100;
                @endphp
                <div class="mb-2 d-flex justify-content-between align-items-center">
                    <span class="text-muted small">{{ __('messages.progress') }}: {{ $completedSteps }}/{{ $totalSteps }} {{ __('messages.steps_completed') }}</span>
                    <span class="badge bg-{{ $formationProcess->status === 'validated' ? 'success' : ($formationProcess->status === 'rejected' ? 'danger' : 'info') }}">
                        {{ ucfirst(__('messages.' . $formationProcess->status)) }}
                    </span>
                </div>
                <div class="progress" style="height: 25px;">
                    <div class="progress-bar progress-bar-striped progress-bar-animated 
                        bg-{{ $formationProcess->status === 'validated' ? 'success' : ($formationProcess->status === 'rejected' ? 'danger' : 'primary') }}" 
                        role="progressbar" 
                        style="width: {{ $progressPercentage }}%" 
                        aria-valuenow="{{ $progressPercentage }}" 
                        aria-valuemin="0" 
                        aria-valuemax="100">
                        <strong>{{ number_format($progressPercentage, 0) }}%</strong>
                    </div>
                </div>
            </div>
        </div>

        <div class="row">
            <!-- Steps Sidebar -->
            <div class="col-lg-3 mb-4">
                <div class="card border-0 shadow-sm">
                    <div class="card-header bg-white border-0 py-3">
                        <h6 class="mb-0 fw-bold">{{ __('messages.progress') }}</h6>
                    </div>
                    <div class="card-body p-0">
                        <div class="list-group list-group-flush">
                            @php
                                $viewedStepNumber = request()->route('stepNumber') ?? $formationProcess->current_step;
                            @endphp
                            @for($i = 1; $i <= 8; $i++)
                                @php
                                    $stepInfo = $steps[$i] ?? [];
                                    $step = $stepInfo['step'] ?? null;
                                    $isValidated = $stepInfo['is_validated'] ?? false;
                                    $isRejected = $stepInfo['is_rejected'] ?? false;
                                    $isCurrent = $formationProcess->current_step == $i;
                                    $isViewed = $viewedStepNumber == $i;
                                    $canAccess = $stepInfo['can_access'] ?? false;
                                    
                                    $stepLabels = [
                                        1 => __('messages.step1_label'),
                                        2 => __('messages.step2_label'),
                                        3 => __('messages.step3_label'),
                                        4 => __('messages.step4_label'),
                                        5 => __('messages.step5_label'),
                                        6 => __('messages.step6_label'),
                                        7 => __('messages.step7_label'),
                                        8 => __('messages.step8_label'),
                                    ];
                                @endphp
                                <a href="{{ $canAccess ? route('formation-processes.step', ['formationProcess' => $formationProcess->id, 'stepNumber' => $i]) : '#' }}" 
                                   class="list-group-item list-group-item-action {{ !$canAccess ? 'disabled' : '' }} {{ $isViewed ? 'active' : '' }}"
                                   style="{{ !$canAccess ? 'cursor: not-allowed; opacity: 0.6;' : '' }}">
                                    <div class="d-flex align-items-center">
                                        <div class="me-3">
                                            @if($isValidated)
                                                <i class="bi bi-check-circle-fill text-success"></i>
                                            @elseif($isRejected)
                                                <i class="bi bi-x-circle-fill text-danger"></i>
                                            @elseif($isViewed)
                                                <i class="bi bi-circle-fill text-primary"></i>
                                            @elseif($isCurrent)
                                                <i class="bi bi-circle-fill text-info"></i>
                                            @else
                                                <i class="bi bi-circle text-muted"></i>
                                            @endif
                                        </div>
                                        <div class="flex-grow-1">
                                            <div class="fw-bold small">{{ __('messages.step') }} {{ $i }}</div>
                                            <div class="small text-muted">{{ $stepLabels[$i] ?? '' }}</div>
                                            @if($isValidated)
                                                <div class="small text-success">
                                                    <i class="bi bi-check"></i> {{ __('messages.validated') }}
                                                </div>
                                            @elseif($isRejected)
                                                <div class="small text-danger">
                                                    <i class="bi bi-x"></i> {{ __('messages.rejected') }}
                                                </div>
                                            @elseif($isViewed)
                                                <div class="small text-primary fw-bold">
                                                    <i class="bi bi-eye"></i> {{ __('messages.viewing') }}
                                                </div>
                                            @elseif($isCurrent)
                                                <div class="small text-info">
                                                    <i class="bi bi-hourglass-split"></i> {{ __('messages.in_progress') }}
                                                </div>
                                            @else
                                                <div class="small text-muted">
                                                    <i class="bi bi-lock"></i> {{ __('messages.pending') }}
                                                </div>
                                            @endif
                                        </div>
                                    </div>
                                </a>
                            @endfor
                        </div>
                    </div>
                </div>
            </div>

            <!-- Step Content -->
            <div class="col-lg-9">
                @php
                    $currentStepNumber = request()->route('stepNumber') ?? $formationProcess->current_step;
                    $currentStep = $formationProcess->getStep($currentStepNumber);
                    
                    if ($currentStepNumber < 1 || $currentStepNumber > 8) {
                        $currentStepNumber = $formationProcess->current_step;
                        $currentStep = $formationProcess->getStep($currentStepNumber);
                    }
                @endphp

                @if($currentStepNumber && $currentStepNumber >= 1 && $currentStepNumber <= 8)
                    @include('formation-processes.partials._step' . $currentStepNumber, [
                        'formationProcess' => $formationProcess, 
                        'step' => $currentStep, 
                        'stepNumber' => $currentStepNumber,
                        'steps' => $steps
                    ])
                @else
                    <div class="card border-0 shadow-sm">
                        <div class="card-body text-center py-5">
                            <i class="bi bi-info-circle display-1 text-muted mb-3"></i>
                            <h5>{{ __('messages.select_step_to_continue') }}</h5>
                            <a href="{{ route('formation-processes.step', ['formationProcess' => $formationProcess->id, 'stepNumber' => $formationProcess->current_step]) }}" class="btn btn-primary mt-3">
                                {{ __('messages.continue_formation_process') }}
                            </a>
                        </div>
                    </div>
                @endif

                {{-- Rejection Notice --}}
                @if($formationProcess->status === 'rejected' && $formationProcess->rejection_reason)
                    <div class="col-12 mt-4">
                        <div class="alert alert-danger border-0 shadow-sm" role="alert">
                            <div class="d-flex align-items-start">
                                <div class="me-3">
                                    <i class="bi bi-exclamation-triangle-fill fs-4"></i>
                                </div>
                                <div class="flex-grow-1">
                                    <h5 class="alert-heading mb-2">
                                        <i class="bi bi-x-circle me-2"></i>
                                        {{ __('messages.formation_process_rejected') }}
                                    </h5>
                                    <p class="mb-2">
                                        <strong>{{ __('messages.rejection_reason') }}:</strong>
                                        {{ $formationProcess->rejection_reason }}
                                    </p>
                                    @if($formationProcess->rejected_at)
                                        <p class="mb-0 text-muted small">
                                            <i class="bi bi-clock me-1"></i>
                                            {{ __('messages.rejected_at') }}: {{ $formationProcess->rejected_at->format('d/m/Y H:i') }}
                                        </p>
                                    @endif
                                </div>
                            </div>
                        </div>
                    </div>
                @endif
            </div>
        </div>
    </div>

    <!-- Validation Confirmation Modal -->
    <div class="modal fade" id="validateModal" tabindex="-1" aria-labelledby="validateModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header bg-success text-white">
                    <h5 class="modal-title" id="validateModalLabel">
                        <i class="bi bi-check-circle me-2"></i>
                        {{ __('messages.confirm_validation') }}
                    </h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <p>{{ __('messages.confirm_validation') }}</p>
                    <div class="mb-3">
                        <label for="validationNotes" class="form-label">{{ __('messages.notes') }} ({{ __('messages.optional') }})</label>
                        <textarea class="form-control" id="validationNotes" rows="3" placeholder="{{ __('messages.notes') }}"></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">{{ __('messages.cancel') }}</button>
                    <button type="button" class="btn btn-success" id="confirmValidateBtn">
                        <i class="bi bi-check-lg me-1"></i>
                        {{ __('messages.validate') }}
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Rejection Confirmation Modal -->
    <div class="modal fade" id="rejectModal" tabindex="-1" aria-labelledby="rejectModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header bg-danger text-white">
                    <h5 class="modal-title" id="rejectModalLabel">
                        <i class="bi bi-x-circle me-2"></i>
                        {{ __('messages.reject') }} {{ __('messages.step') }}
                    </h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="alert alert-warning">
                        <i class="bi bi-exclamation-triangle me-2"></i>
                        <strong>{{ __('messages.warning') }}:</strong> {{ __('messages.rejection_warning') }}
                    </div>
                    <div class="mb-3">
                        <label for="rejectionReason" class="form-label">{{ __('messages.rejection_reason') }} <span class="text-danger">*</span></label>
                        <textarea class="form-control" id="rejectionReason" rows="3" required placeholder="{{ __('messages.rejection_reason') }}"></textarea>
                    </div>
                    <div class="mb-3">
                        <label for="rejectionNotes" class="form-label">{{ __('messages.notes') }} ({{ __('messages.optional') }})</label>
                        <textarea class="form-control" id="rejectionNotes" rows="2" placeholder="{{ __('messages.notes') }}"></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">{{ __('messages.cancel') }}</button>
                    <button type="button" class="btn btn-danger" id="confirmRejectBtn">
                        <i class="bi bi-x-lg me-1"></i>
                        {{ __('messages.reject') }}
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Finalize Formation Process Confirmation Modal -->
    <div class="modal fade" id="finalizeModal" tabindex="-1" aria-labelledby="finalizeModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header bg-primary text-white">
                    <h5 class="modal-title" id="finalizeModalLabel">
                        <i class="bi bi-check-all me-2"></i>
                        {{ __('messages.finalize') }} {{ __('messages.formation_process') }}
                    </h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="alert alert-info">
                        <i class="bi bi-info-circle me-2"></i>
                        <strong>{{ __('messages.warning') }}:</strong> {{ __('messages.confirm_finalize') }}
                    </div>
                    <p class="mb-3">
                        {{ __('messages.finalize_warning_message') }}
                    </p>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                        <i class="bi bi-x-lg me-1"></i>
                        {{ __('messages.cancel') }}
                    </button>
                    <button type="button" class="btn btn-primary" id="confirmFinalizeBtn">
                        <i class="bi bi-check-all me-1"></i>
                        {{ __('messages.finalize') }}
                    </button>
                </div>
            </div>
        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Initialize toasts
            const toasts = document.querySelectorAll('.toast.show');
            toasts.forEach(function(toastEl) {
                if (typeof bootstrap !== 'undefined') {
                    const toast = new bootstrap.Toast(toastEl);
                    toast.show();
                }
            });

            // Prevent navigation to locked steps
            const stepLinks = document.querySelectorAll('.list-group-item.disabled');
            stepLinks.forEach(function(link) {
                link.addEventListener('click', function(e) {
                    e.preventDefault();
                    e.stopPropagation();
                    
                    const toastContainer = document.querySelector('.toast-container');
                    if (toastContainer && typeof bootstrap !== 'undefined') {
                        const toastHtml = `
                            <div class="toast" role="alert" aria-live="assertive" aria-atomic="true" data-bs-autohide="true" data-bs-delay="5000">
                                <div class="toast-header bg-warning text-dark">
                                    <i class="bi bi-exclamation-triangle me-2"></i>
                                    <strong class="me-auto">{{ __('messages.warning') }}</strong>
                                    <button type="button" class="btn-close" data-bs-dismiss="toast" aria-label="Close"></button>
                                </div>
                                <div class="toast-body">
                                    {{ __('messages.must_validate_previous_steps') }}
                                </div>
                            </div>
                        `;
                        toastContainer.insertAdjacentHTML('beforeend', toastHtml);
                        const toastElement = toastContainer.lastElementChild;
                        const toast = new bootstrap.Toast(toastElement);
                        toast.show();
                        
                        toastElement.addEventListener('hidden.bs.toast', function() {
                            toastElement.remove();
                        });
                    }
                    return false;
                });
            });

            // Add loading states to forms
            const forms = document.querySelectorAll('form');
            forms.forEach(function(form) {
                form.addEventListener('submit', function() {
                    const submitBtn = form.querySelector('button[type="submit"]');
                    if (submitBtn) {
                        submitBtn.disabled = true;
                        submitBtn.innerHTML = '<span class="spinner-border spinner-border-sm me-1"></span>{{ __('messages.saving') }}...';
                    }
                });
            });
        });

        // Global functions for validation and rejection
        let currentFormationProcessId = null;
        let currentStepNumber = null;

        function validateStep(formationProcessId, stepNum) {
            currentFormationProcessId = formationProcessId;
            currentStepNumber = stepNum;
            const modal = new bootstrap.Modal(document.getElementById('validateModal'));
            modal.show();
        }

        function rejectStep(formationProcessId, stepNum) {
            currentFormationProcessId = formationProcessId;
            currentStepNumber = stepNum;
            const modal = new bootstrap.Modal(document.getElementById('rejectModal'));
            modal.show();
        }

        function finalizeFormationProcess(formationProcessId) {
            currentFormationProcessId = formationProcessId;
            const modal = new bootstrap.Modal(document.getElementById('finalizeModal'));
            modal.show();
        }

        // Handle validation confirmation
        document.getElementById('confirmValidateBtn')?.addEventListener('click', function() {
            if (!currentFormationProcessId || !currentStepNumber) return;
            
            const notes = document.getElementById('validationNotes').value;
            const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');
            const form = document.createElement('form');
            form.method = 'POST';
            form.action = `/formation-processes/${currentFormationProcessId}/step/${currentStepNumber}/validate`;
            form.innerHTML = `<input type="hidden" name="_token" value="${csrfToken}">` + 
                (notes ? `<input type="hidden" name="notes" value="${notes.replace(/"/g, '&quot;')}">` : '');
            document.body.appendChild(form);
            form.submit();
        });

        // Handle rejection confirmation
        document.getElementById('confirmRejectBtn')?.addEventListener('click', function() {
            if (!currentFormationProcessId || !currentStepNumber) return;
            
            const reason = document.getElementById('rejectionReason').value;
            if (!reason || reason.trim() === '') {
                alert('{{ __('messages.rejection_reason') }} {{ __('messages.required_field') }}');
                return;
            }
            
            const notes = document.getElementById('rejectionNotes').value;
            const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');
            const form = document.createElement('form');
            form.method = 'POST';
            form.action = `/formation-processes/${currentFormationProcessId}/step/${currentStepNumber}/reject`;
            form.innerHTML = `<input type="hidden" name="_token" value="${csrfToken}">` + 
                `<input type="hidden" name="rejection_reason" value="${reason.replace(/"/g, '&quot;')}">` +
                (notes ? `<input type="hidden" name="notes" value="${notes.replace(/"/g, '&quot;')}">` : '');
            document.body.appendChild(form);
            form.submit();
        });

        // Handle finalization confirmation
        document.getElementById('confirmFinalizeBtn')?.addEventListener('click', function() {
            if (!currentFormationProcessId) return;
            
            const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');
            const form = document.createElement('form');
            form.method = 'POST';
            form.action = `/formation-processes/${currentFormationProcessId}/finalize`;
            form.innerHTML = `<input type="hidden" name="_token" value="${csrfToken}">`;
            document.body.appendChild(form);
            form.submit();
        });
    </script>
</x-app-layout>

