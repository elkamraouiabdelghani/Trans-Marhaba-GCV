<div class="card border-0 shadow-sm">
    <div class="card-header bg-white border-0 py-3">
        <h5 class="mb-0 text-dark fw-bold">
            <i class="bi bi-7-circle me-2 text-primary"></i>
            {{ __('messages.step7_evaluation_formation') }}
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
                    <label for="note_formation" class="form-label">{{ __('messages.note_formation') }} <span class="text-danger">*</span></label>
                    <input type="number" 
                           class="form-control @error('note_formation') is-invalid @enderror" 
                           id="note_formation" 
                           name="note_formation" 
                           value="{{ old('note_formation', $stepData['note_formation'] ?? '') }}" 
                           min="0" 
                           max="100" 
                           required>
                    @error('note_formation')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                    <small class="form-text text-muted">{{ __('messages.enter_feedback_formation') }}</small>
                </div>

                <div class="mb-3">
                    <label for="feedback_formation" class="form-label">{{ __('messages.feedback_formation') }} <span class="text-danger">*</span></label>
                    <textarea class="form-control @error('feedback_formation') is-invalid @enderror" 
                              id="feedback_formation" 
                              name="feedback_formation" 
                              rows="4" 
                              required>{{ old('feedback_formation', $stepData['feedback_formation'] ?? '') }}</textarea>
                    @error('feedback_formation')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <div class="mb-3">
                    <label for="feedback_tbx" class="form-label">{{ __('messages.feedback_tbx') }}</label>
                    <textarea class="form-control @error('feedback_tbx') is-invalid @enderror" 
                              id="feedback_tbx" 
                              name="feedback_tbx" 
                              rows="3">{{ old('feedback_tbx', $stepData['feedback_tbx'] ?? '') }}</textarea>
                    @error('feedback_tbx')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <div class="mb-3">
                    <label for="nbr" class="form-label">{{ __('messages.nbr') }}</label>
                    <textarea class="form-control @error('nbr') is-invalid @enderror" 
                              id="nbr" 
                              name="nbr" 
                              rows="3">{{ old('nbr', $stepData['nbr'] ?? '') }}</textarea>
                    @error('nbr')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <div class="mb-3">
                    <label for="evaluation_questionnaire" class="form-label">{{ __('messages.evaluation_questionnaire') }}</label>
                    <input type="file" 
                           class="form-control @error('evaluation_questionnaire') is-invalid @enderror" 
                           id="evaluation_questionnaire" 
                           name="evaluation_questionnaire" 
                           accept=".pdf,.doc,.docx">
                    @error('evaluation_questionnaire')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                    @if(!empty($stepData['evaluation_questionnaire_path']))
                        <small class="text-muted">{{ __('messages.file_uploaded') }}: {{ basename($stepData['evaluation_questionnaire_path']) }}</small>
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
                    <label class="form-label fw-bold">{{ __('messages.note_formation') }}</label>
                    <p class="text-muted">{{ $stepData['note_formation'] ?? 'N/A' }}%</p>
                </div>
            </div>

            <div class="row mb-3">
                <div class="col-md-12">
                    <label class="form-label fw-bold">{{ __('messages.feedback_formation') }}</label>
                    <p class="text-muted">{{ $stepData['feedback_formation'] ?? 'N/A' }}</p>
                </div>
            </div>

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
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.6/dist/js/bootstrap.bundle.min.js" 
    crossorigin="anonymous" defer></script>