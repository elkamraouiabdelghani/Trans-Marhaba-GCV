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
        @php
            $stepNumbers = range(1, 6);
        @endphp

        <!-- Header -->
        <div class="card border-0 shadow-sm mb-4">
            <div class="card-header bg-white border-0 py-3">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h5 class="mb-0 text-dark fw-bold">
                            <i class="bi bi-arrow-repeat me-2 text-primary"></i>
                            {{ __('messages.changements') }} #{{ $changement->changementType->name }}
                        </h5>
                        <small class="text-muted">
                            {{ $changement->changementType->name ?? __('messages.not_available') }} - 
                            {{ $changement->date_changement->format('d/m/Y') }} - 
                            {{ $changement->responsable_changement }}
                            @if($changement->subject)
                                - 
                                @if($changement->isForDriver())
                                    <i class="bi bi-person-badge text-info"></i> {{ __('messages.driver') }}: {{ $changement->getSubjectName() }}
                                @elseif($changement->isForAdministrative())
                                    <i class="bi bi-person-gear text-warning"></i> {{ __('messages.administrative_user') }}: {{ $changement->getSubjectName() }}
                                @endif
                                @if($changement->replacement)
                                    → 
                                    @if($changement->isReplacementDriver())
                                        <i class="bi bi-person-badge text-info"></i> {{ __('messages.replacement') }}: {{ $changement->getReplacementName() }}
                                    @elseif($changement->isReplacementAdministrative())
                                        <i class="bi bi-person-gear text-warning"></i> {{ __('messages.replacement') }}: {{ $changement->getReplacementName() }}
                                    @endif
                                @endif
                            @endif
                        </small>
                    </div>
                    <a href="{{ route('changements.index') }}" class="btn btn-outline-secondary btn-sm">
                        <i class="bi bi-arrow-left me-1"></i>
                        {{ __('messages.back_to_list') }}
                    </a>
                </div>
            </div>
            <!-- Progress Bar -->
            <div class="card-body bg-light">
                @php
                    $completedSteps = 0;
                    foreach ($stepNumbers as $stepIndex) {
                        $step = $changement->getStep($stepIndex);
                        if ($step && $step->isValidated()) {
                            $completedSteps++;
                        }
                    }
                    $totalSteps = count($stepNumbers);
                    $progressPercentage = $totalSteps > 0 ? ($completedSteps / $totalSteps) * 100 : 0;
                @endphp
                <div class="mb-2 d-flex justify-content-between align-items-center">
                    <span class="text-muted small">{{ __('messages.progress') }}: {{ $completedSteps }}/{{ $totalSteps }} {{ __('messages.steps_completed') }}</span>
                    <span class="badge bg-{{ $changement->status === 'approved' ? 'success' : ($changement->status === 'rejected' ? 'danger' : 'info') }}">
                        {{ ucfirst(__('messages.' . $changement->status)) }}
                    </span>
                </div>
                <div class="progress" style="height: 25px;">
                    <div class="progress-bar progress-bar-striped progress-bar-animated 
                        bg-{{ $changement->status === 'approved' ? 'success' : ($changement->status === 'rejected' ? 'danger' : 'primary') }}" 
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

        <!-- Subject Information Card -->
        @if($changement->subject)
        <div class="card border-0 shadow-sm mb-4">
            <div class="card-header bg-white border-0 py-3">
                <h6 class="mb-0 fw-bold">
                    <i class="bi bi-person-circle me-2 text-primary"></i>
                    {{ __('messages.subject_information') }}
                </h6>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-6">
                        <div class="d-flex align-items-center mb-3">
                            @if($changement->isForDriver())
                                <div class="bg-info bg-opacity-10 rounded-circle d-flex align-items-center justify-content-center me-3" style="width: 50px; height: 50px;">
                                    <i class="bi bi-person-badge text-info fs-5"></i>
                                </div>
                                <div>
                                    <strong class="text-dark d-block">{{ __('messages.driver') }}</strong>
                                    <span class="text-muted">{{ $changement->getSubjectName() }}</span>
                                </div>
                            @elseif($changement->isForAdministrative())
                                <div class="bg-warning bg-opacity-10 rounded-circle d-flex align-items-center justify-content-center me-3" style="width: 50px; height: 50px;">
                                    <i class="bi bi-person-gear text-warning fs-5"></i>
                                </div>
                                <div>
                                    <strong class="text-dark d-block">{{ __('messages.administrative_user') }}</strong>
                                    <span class="text-muted">{{ $changement->getSubjectName() }}</span>
                                </div>
                            @endif
                        </div>
                    </div>
                    @if($changement->isForDriver() && $changement->subject)
                        <div class="col-md-6">
                            <div class="mb-2">
                                <small class="text-muted d-block">{{ __('messages.license_number') }}</small>
                                <strong>{{ $changement->subject->license_number ?? __('messages.not_available') }}</strong>
                            </div>
                            @if($changement->subject->email)
                            <div class="mb-2">
                                <small class="text-muted d-block">{{ __('messages.email') }}</small>
                                <strong>{{ $changement->subject->email }}</strong>
                            </div>
                            @endif
                        </div>
                    @elseif($changement->isForAdministrative() && $changement->subject)
                        <div class="col-md-6">
                            @if($changement->subject->email)
                            <div class="mb-2">
                                <small class="text-muted d-block">{{ __('messages.email') }}</small>
                                <strong>{{ $changement->subject->email }}</strong>
                            </div>
                            @endif
                            @if($changement->subject->department)
                            <div class="mb-2">
                                <small class="text-muted d-block">{{ __('messages.department') }}</small>
                                <strong>{{ $changement->subject->department }}</strong>
                            </div>
                            @endif
                        </div>
                    @endif
                </div>
            </div>
        </div>
        @endif

        <!-- Replacement Information Card -->
        @if($changement->replacement)
        <div class="card border-0 shadow-sm mb-4">
            <div class="card-header bg-white border-0 py-3">
                <h6 class="mb-0 fw-bold">
                    <i class="bi bi-person-plus-circle me-2 text-success"></i>
                    {{ __('messages.replacement_information') }}
                </h6>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-6">
                        <div class="d-flex align-items-center mb-3">
                            @if($changement->isReplacementDriver())
                                <div class="bg-info bg-opacity-10 rounded-circle d-flex align-items-center justify-content-center me-3" style="width: 50px; height: 50px;">
                                    <i class="bi bi-person-badge text-info fs-5"></i>
                                </div>
                                <div>
                                    <strong class="text-dark d-block">{{ __('messages.driver') }}</strong>
                                    <span class="text-muted">{{ $changement->getReplacementName() }}</span>
                                </div>
                            @elseif($changement->isReplacementAdministrative())
                                <div class="bg-warning bg-opacity-10 rounded-circle d-flex align-items-center justify-content-center me-3" style="width: 50px; height: 50px;">
                                    <i class="bi bi-person-gear text-warning fs-5"></i>
                                </div>
                                <div>
                                    <strong class="text-dark d-block">{{ __('messages.administrative_user') }}</strong>
                                    <span class="text-muted">{{ $changement->getReplacementName() }}</span>
                                </div>
                            @endif
                        </div>
                    </div>
                    @if($changement->isReplacementDriver() && $changement->replacement)
                        <div class="col-md-6">
                            <div class="mb-2">
                                <small class="text-muted d-block">{{ __('messages.license_number') }}</small>
                                <strong>{{ $changement->replacement->license_number ?? __('messages.not_available') }}</strong>
                            </div>
                            @if($changement->replacement->email)
                            <div class="mb-2">
                                <small class="text-muted d-block">{{ __('messages.email') }}</small>
                                <strong>{{ $changement->replacement->email }}</strong>
                            </div>
                            @endif
                        </div>
                    @elseif($changement->isReplacementAdministrative() && $changement->replacement)
                        <div class="col-md-6">
                            @if($changement->replacement->email)
                            <div class="mb-2">
                                <small class="text-muted d-block">{{ __('messages.email') }}</small>
                                <strong>{{ $changement->replacement->email }}</strong>
                            </div>
                            @endif
                            @if($changement->replacement->department)
                            <div class="mb-2">
                                <small class="text-muted d-block">{{ __('messages.department') }}</small>
                                <strong>{{ $changement->replacement->department }}</strong>
                            </div>
                            @endif
                        </div>
                    @endif
                </div>
            </div>
        </div>
        @endif

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
                                $viewedStepNumber = $stepNumber ?? $changement->current_step;
                            @endphp
                            @foreach($stepNumbers as $i)
                                @php
                                    $stepInfo = $steps[$i] ?? [];
                                    $step = $stepInfo['step'] ?? null;
                                    $isValidated = $stepInfo['is_validated'] ?? false;
                                    $isRejected = $stepInfo['is_rejected'] ?? false;
                                    $isCurrent = $changement->current_step == $i;
                                    $isViewed = $viewedStepNumber == $i;
                                    $canAccess = $stepInfo['can_access'] ?? false;
                                    
                                    $stepLabels = [
                                        1 => __('messages.step_1_identification'),
                                        2 => __('messages.step_2_evaluation'),
                                        3 => __('messages.step_3_planification'),
                                        4 => __('messages.step_4_approbation'),
                                        5 => __('messages.step_5_implementation'),
                                        6 => __('messages.step_6_checklist'),
                                    ];
                                @endphp
                                <a href="{{ $canAccess ? route('changements.step', ['changement' => $changement->id, 'stepNumber' => $i]) : '#' }}" 
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
                                            <div class="fw-bold small">{{ $stepLabels[$i] ?? __('messages.step') . ' ' . $i }}</div>
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
                            @endforeach
                        </div>
                    </div>
                </div>
            </div>

            <!-- Step Content -->
            <div class="col-lg-9">
                @php
                    $currentStepNumber = $stepNumber ?? $changement->current_step;
                    $currentStep = $step ?? $changement->getStep($currentStepNumber);
                @endphp

                @if($currentStepNumber && in_array($currentStepNumber, $stepNumbers, true))
                    @if($currentStepNumber == 6)
                        {{-- Step 6 is the checklist, handled by checklist route --}}
                        <div class="card border-0 shadow-sm">
                            <div class="card-body text-center py-5">
                                <i class="bi bi-list-check display-1 text-primary mb-3"></i>
                                <h5>{{ __('messages.step_6_checklist') }}</h5>
                                <p class="text-muted">{{ __('messages.changements_checklist_description') }}</p>
                                <a href="{{ route('changements.checklist', $changement) }}" class="btn btn-primary mt-3">
                                    <i class="bi bi-arrow-right me-1"></i>
                                    {{ __('messages.access_checklist') }}
                                </a>
                            </div>
                        </div>
                    @else
                        @include('changements.partials._step' . $currentStepNumber, [
                            'changement' => $changement,
                            'step' => $currentStep,
                            'stepNumber' => $currentStepNumber,
                            'steps' => $steps,
                        ])
                    @endif
                @else
                    <div class="card border-0 shadow-sm">
                        <div class="card-body text-center py-5">
                            <i class="bi bi-info-circle display-1 text-muted mb-3"></i>
                            <h5>{{ __('messages.select_step_to_continue') }}</h5>
                            <a href="{{ route('changements.step', ['changement' => $changement->id, 'stepNumber' => $changement->current_step]) }}" class="btn btn-primary mt-3">
                                {{ __('messages.continue_changement') }}
                            </a>
                        </div>
                    </div>
                @endif

                {{-- Rejection Notice --}}
                @if($changement->isRejected() && $changement->rejection_reason)
                    <div class="col-12 mt-4">
                        <div class="alert alert-danger border-0 shadow-sm" role="alert">
                            <div class="d-flex align-items-start">
                                <div class="me-3">
                                    <i class="bi bi-exclamation-triangle-fill fs-4"></i>
                                </div>
                                <div class="flex-grow-1">
                                    <h5 class="alert-heading mb-2">
                                        <i class="bi bi-x-circle me-2"></i>
                                        {{ __('messages.changements_step_rejected') }}
                                    </h5>
                                    <p class="mb-2">
                                        <strong>{{ __('messages.rejection_reason') }}:</strong>
                                        {{ $changement->rejection_reason }}
                                    </p>
                                    @if($changement->rejected_at)
                                        <p class="mb-0 text-muted small">
                                            <i class="bi bi-clock me-1"></i>
                                            {{ __('messages.rejected_at') }}: {{ $changement->rejected_at->format('d/m/Y H:i') }}
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

    <!-- Validation Progress Modal -->
    <div class="modal fade" id="validationProgressModal" tabindex="-1" aria-labelledby="validationProgressModalLabel" aria-hidden="true" data-bs-backdrop="static" data-bs-keyboard="false">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header bg-success text-white">
                    <h5 class="modal-title" id="validationProgressModalLabel">
                        <i class="bi bi-hourglass-split me-2"></i>
                        {{ __('messages.validating') ?? 'Validating' }}
                    </h5>
                </div>
                <div class="modal-body text-center py-4" id="validationProgressBody">
                    <div class="spinner-border text-success mb-3" role="status" style="width: 3rem; height: 3rem;">
                        <span class="visually-hidden">{{ __('messages.loading') ?? 'Loading...' }}</span>
                    </div>
                    <h5 class="mb-2">{{ __('messages.validating_step') ?? 'Validating Step' }}</h5>
                    <p class="text-muted mb-0">{{ __('messages.please_wait') ?? 'Please wait while we validate the step...' }}</p>
                </div>
            </div>
        </div>
    </div>

    <!-- Rejection Modal -->
    <div class="modal fade" id="rejectModal" tabindex="-1" aria-labelledby="rejectModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header bg-danger text-white">
                    <h5 class="modal-title" id="rejectModalLabel">
                        <i class="bi bi-x-circle me-2"></i>
                        {{ __('messages.reject') }}
                    </h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form id="rejectForm" method="POST">
                    @csrf
                    <div class="modal-body">
                        <div class="alert alert-warning">
                            <i class="bi bi-exclamation-triangle me-2"></i>
                            {{ __('messages.rejection_warning') }}
                        </div>
                        <div class="mb-3">
                            <label for="rejectionReason" class="form-label">{{ __('messages.rejection_reason') }} <span class="text-danger">*</span></label>
                            <textarea class="form-control" id="rejectionReason" name="rejection_reason" rows="3" required></textarea>
                        </div>
                        <div class="mb-3">
                            <label for="rejectionNotes" class="form-label">{{ __('messages.notes') }} ({{ __('messages.optional') }})</label>
                            <textarea class="form-control" id="rejectionNotes" name="notes" rows="2"></textarea>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">{{ __('messages.cancel') }}</button>
                        <button type="submit" class="btn btn-danger">
                            <i class="bi bi-x-lg me-1"></i>
                            {{ __('messages.reject') }}
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Bootstrap JS Bundle (includes Popper.js) -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.6/dist/js/bootstrap.bundle.min.js" 
        crossorigin="anonymous"></script>

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

            // Handle form submission with data-submit-action
            const forms = document.querySelectorAll('form');
            forms.forEach(function(form) {
                form.addEventListener('submit', function(e) {
                    const submitBtn = form.querySelector('button[type="submit"][data-submit-action]');
                    if (submitBtn) {
                        const action = submitBtn.getAttribute('data-submit-action');
                        if (action) {
                            // Add hidden input for submit_action
                            let hiddenInput = form.querySelector('input[name="submit_action"]');
                            if (!hiddenInput) {
                                hiddenInput = document.createElement('input');
                                hiddenInput.type = 'hidden';
                                hiddenInput.name = 'submit_action';
                                form.appendChild(hiddenInput);
                            }
                            hiddenInput.value = action;
                            
                            // Check if this is a validation action
                            if (action === 'validate') {
                                e.preventDefault(); // Prevent immediate submission
                                
                                // Get the step number from the form action or current URL
                                const formAction = form.getAttribute('action');
                                const stepMatch = formAction ? formAction.match(/stepNumber[=\/](\d+)/) : null;
                                const stepNumber = stepMatch ? stepMatch[1] : '?';
                                
                                // Update modal content
                                const modalBody = document.getElementById('validationProgressBody');
                                if (modalBody) {
                                    modalBody.innerHTML = `
                                        <div class="text-center">
                                            <div class="spinner-border text-success mb-3" role="status" style="width: 3rem; height: 3rem;">
                                                <span class="visually-hidden">{{ __('messages.loading') ?? 'Loading...' }}</span>
                                            </div>
                                            <h5 class="mb-2">{{ __('messages.validating_step') ?? 'Validating Step' }} ${stepNumber}</h5>
                                            <p class="text-muted mb-0">{{ __('messages.please_wait') ?? 'Please wait while we validate the step...' }}</p>
                                        </div>
                                    `;
                                }
                                
                                // Show validation progress modal
                                if (typeof bootstrap !== 'undefined') {
                                    const validationModal = new bootstrap.Modal(document.getElementById('validationProgressModal'));
                                    validationModal.show();
                                }
                                
                                // Submit the form after a short delay to show the modal
                                setTimeout(function() {
                                    form.submit();
                                }, 300);
                                
                                return false;
                            }
                            
                            // Show loading state for other actions
                            submitBtn.disabled = true;
                            const originalHtml = submitBtn.innerHTML;
                            submitBtn.innerHTML = '<span class="spinner-border spinner-border-sm me-1"></span>{{ __('messages.processing') }}...';
                            
                            // Re-enable button after a delay in case of error
                            setTimeout(function() {
                                submitBtn.disabled = false;
                                submitBtn.innerHTML = originalHtml;
                            }, 10000);
                        }
                    } else {
                        // Regular form submission
                        const submitBtn = form.querySelector('button[type="submit"]');
                        if (submitBtn) {
                            submitBtn.disabled = true;
                            submitBtn.innerHTML = '<span class="spinner-border spinner-border-sm me-1"></span>{{ __('messages.saving') }}...';
                        }
                    }
                });
            });


            // Handle rejection modal
            const rejectModal = document.getElementById('rejectModal');
            if (rejectModal) {
                rejectModal.addEventListener('show.bs.modal', function(event) {
                    const button = event.relatedTarget;
                    const stepNumber = button.getAttribute('data-step-number');
                    const form = document.getElementById('rejectForm');
                    if (form && stepNumber) {
                        form.action = '{{ route("changements.reject-step", ["changement" => $changement->id, "stepNumber" => ":stepNumber"]) }}'.replace(':stepNumber', stepNumber);
                    }
                });
            }
        });

        function rejectStep(stepNumber) {
            if (typeof bootstrap === 'undefined') {
                console.error('Bootstrap is not loaded');
                return;
            }
            const modal = new bootstrap.Modal(document.getElementById('rejectModal'));
            const form = document.getElementById('rejectForm');
            if (form) {
                form.action = '{{ route("changements.reject-step", ["changement" => $changement->id, "stepNumber" => ":stepNumber"]) }}'.replace(':stepNumber', stepNumber);
            }
            modal.show();
        }
    </script>
</x-app-layout>

