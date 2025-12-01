<div class="card border-0 shadow-sm">
    <div class="card-header bg-white border-0 py-3">
        <h5 class="mb-0 text-dark fw-bold">
            <i class="bi bi-8-circle me-2 text-primary"></i>
            {{ __('messages.step8_mise_application') }}
        </h5>
    </div>
    <div class="card-body">
        @php
            $stepData = $step ? $step->step_data : [];
            $isValidated = $step && $step->isValidated();
            $isRejected = $step && $step->isRejected();
            $canEdit = !$isValidated && !$isRejected && ($formationProcess->current_step == $stepNumber || !$step);
        @endphp

        @if($canEdit && !$formationProcess->isValidated())
            <form action="{{ route('formation-processes.save-step', ['formationProcess' => $formationProcess->id, 'stepNumber' => $stepNumber]) }}" method="POST" enctype="multipart/form-data">
                @csrf

                <div class="mb-3">
                    <label for="final_validation_date" class="form-label">{{ __('messages.final_validation_date') }} <span class="text-danger">*</span></label>
                    <input type="date" 
                           class="form-control @error('final_validation_date') is-invalid @enderror" 
                           id="final_validation_date" 
                           name="final_validation_date" 
                           value="{{ old('final_validation_date', $stepData['final_validation_date'] ?? '') }}" 
                           required>
                    @error('final_validation_date')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <div class="mb-3">
                    <label for="validated_by" class="form-label">{{ __('messages.validated_by') }} <span class="text-danger">*</span></label>
                    <input type="text" 
                           class="form-control @error('validated_by') is-invalid @enderror" 
                           id="validated_by" 
                           name="validated_by" 
                           value="{{ old('validated_by', $stepData['validated_by'] ?? auth()->user()->name ?? '') }}" 
                           required>
                    @error('validated_by')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <div class="mb-3">
                    <label for="application_notes" class="form-label">{{ __('messages.application_notes') }}</label>
                    <textarea class="form-control @error('application_notes') is-invalid @enderror" 
                              id="application_notes" 
                              name="application_notes" 
                              rows="4">{{ old('application_notes', $stepData['application_notes'] ?? '') }}</textarea>
                    @error('application_notes')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <div class="mb-3">
                    <div class="form-check">
                        <input class="form-check-input" 
                               type="checkbox" 
                               id="follow_up_required" 
                               name="follow_up_required" 
                               value="1" 
                               {{ old('follow_up_required', $stepData['follow_up_required'] ?? false) ? 'checked' : '' }}>
                        <label class="form-check-label" for="follow_up_required">
                            {{ __('messages.follow_up_required') }}
                        </label>
                    </div>
                </div>

                <div class="mb-3">
                    <label for="completion_certificate" class="form-label">{{ __('messages.completion_certificate') }}</label>
                    <input type="file" 
                           class="form-control @error('completion_certificate') is-invalid @enderror" 
                           id="completion_certificate" 
                           name="completion_certificate" 
                           accept=".pdf,.doc,.docx">
                    @error('completion_certificate')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                    @if(!empty($stepData['completion_certificate_path']))
                        <small class="text-muted">{{ __('messages.file_uploaded') }}: {{ basename($stepData['completion_certificate_path']) }}</small>
                    @endif
                </div>

                <hr class="my-4">
                <div class="d-flex gap-2 justify-content-end">
                    <button type="submit" class="btn btn-primary">
                        <i class="bi bi-save me-1"></i>
                        {{ __('messages.save') }}
                    </button>
                </div>
            </form>
        @else
            <div class="row mb-3">
                <div class="col-md-6">
                    <label class="form-label fw-bold">{{ __('messages.final_validation_date') }}</label>
                    <p class="text-muted">{{ $stepData['final_validation_date'] ? \Carbon\Carbon::parse($stepData['final_validation_date'])->format('d/m/Y') : 'N/A' }}</p>
                </div>
                <div class="col-md-6">
                    <label class="form-label fw-bold">{{ __('messages.validated_by') }}</label>
                    <p class="text-muted">{{ $stepData['validated_by'] ?? 'N/A' }}</p>
                </div>
            </div>

            @if(!empty($stepData['report_path']))
                <div class="alert alert-info d-flex justify-content-between align-items-center">
                    <div>
                        <i class="bi bi-file-earmark-text me-2"></i>
                        {{ __('messages.formation_report_available') ?? 'Rapport de formation disponible.' }}
                    </div>
                    <a href="{{ asset('storage/' . $stepData['report_path']) }}"
                       class="btn btn-outline-primary btn-sm"
                       target="_blank"
                       rel="noopener">
                        <i class="bi bi-download me-1"></i>
                        {{ __('messages.download') ?? 'Télécharger' }}
                    </a>
                </div>
            @endif

            @if($isValidated)
                <div class="alert alert-success">
                    <i class="bi bi-check-circle me-2"></i>
                    {{ __('messages.validated') }} - {{ $step->validated_at ? $step->validated_at->format('d/m/Y H:i') : '' }}
                </div>
            @elseif($isRejected)
                <div class="alert alert-danger">
                    <i class="bi bi-x-circle me-2"></i>
                    {{ __('messages.rejected') }} - {{ $step->rejection_reason ?? '' }}
                </div>
            @endif
        @endif

        @if($step && !$formationProcess->isValidated() && !$formationProcess->isRejected())
            <div class="d-flex gap-2 justify-content-end mt-3">
                @if(!$isValidated)
                    <button type="button" class="btn btn-success" onclick="validateStep({{ $formationProcess->id }}, {{ $stepNumber }})">
                        <i class="bi bi-check-circle me-1"></i>
                        {{ __('messages.validate') }}
                    </button>
                @endif
                @if(!$isRejected)
                    <button type="button" class="btn btn-danger" onclick="rejectStep({{ $formationProcess->id }}, {{ $stepNumber }})">
                        <i class="bi bi-x-circle me-1"></i>
                        {{ __('messages.reject') }}
                    </button>
                @endif
            </div>
        @endif

        @if($isValidated && !$formationProcess->isValidated())
            <div class="d-flex justify-content-end mt-3">
                <button type="button" class="btn btn-primary" onclick="finalizeFormationProcess({{ $formationProcess->id }})">
                    <i class="bi bi-check-all me-1"></i>
                    {{ __('messages.finalize') }}
                </button>
            </div>
        @endif
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.6/dist/js/bootstrap.bundle.min.js" 
    crossorigin="anonymous" defer></script>