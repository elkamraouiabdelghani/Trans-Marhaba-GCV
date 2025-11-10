<div class="card border-0 shadow-sm">
    <div class="card-header bg-white border-0 py-3">
        <h5 class="mb-0 text-dark fw-bold">
            <i class="bi bi-4-circle me-2 text-primary"></i>
            {{ __('messages.step4_choix_formateurs') }}
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
                    <label for="animateur" class="form-label">{{ __('messages.animateur') }} <span class="text-danger">*</span></label>
                    <input type="text" 
                           class="form-control @error('animateur') is-invalid @enderror" 
                           id="animateur" 
                           name="animateur" 
                           value="{{ old('animateur', $stepData['animateur'] ?? '') }}" 
                           required>
                    @error('animateur')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label for="trainer_contract" class="form-label">{{ __('messages.trainer_contract') }}</label>
                        <input type="file" 
                               class="form-control @error('trainer_contract') is-invalid @enderror" 
                               id="trainer_contract" 
                               name="trainer_contract" 
                               accept=".pdf,.doc,.docx">
                        @error('trainer_contract')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                        @if(!empty($stepData['trainer_contract_path']))
                            <small class="text-muted">{{ __('messages.file_uploaded') }}: {{ basename($stepData['trainer_contract_path']) }}</small>
                        @endif
                    </div>
                    <div class="col-md-6 mb-3">
                        <label for="training_program" class="form-label">{{ __('messages.training_program') }}</label>
                        <input type="file" 
                               class="form-control @error('training_program') is-invalid @enderror" 
                               id="training_program" 
                               name="training_program" 
                               accept=".pdf,.doc,.docx">
                        @error('training_program')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                        @if(!empty($stepData['training_program_path']))
                            <small class="text-muted">{{ __('messages.file_uploaded') }}: {{ basename($stepData['training_program_path']) }}</small>
                        @endif
                    </div>
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
                <div class="col-md-12">
                    <label class="form-label fw-bold">{{ __('messages.animateur') }}</label>
                    <p class="text-muted">{{ $stepData['animateur'] ?? 'N/A' }}</p>
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
