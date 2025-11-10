<div class="card border-0 shadow-sm">
    <div class="card-header bg-white border-0 py-3">
        <h5 class="mb-0 text-dark fw-bold">
            <i class="bi bi-2-circle me-2 text-primary"></i>
            {{ __('messages.step2_conception_plan') }}
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
            <form action="{{ route('formation-processes.save-step', ['formationProcess' => $formationProcess->id, 'stepNumber' => $stepNumber]) }}" method="POST">
                @csrf

                <div class="mb-3">
                    <label for="theme" class="form-label">{{ __('messages.theme') }}</label>
                    <input type="text" 
                           class="form-control @error('theme') is-invalid @enderror" 
                           id="theme" 
                           name="theme" 
                           value="{{ old('theme', $stepData['theme'] ?? $formationProcess->theme ?? '') }}">
                    @error('theme')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <div class="mb-3">
                    <label for="date_prevu" class="form-label">{{ __('messages.date_prevu') }} <span class="text-danger">*</span></label>
                    <input type="date" 
                           class="form-control @error('date_prevu') is-invalid @enderror" 
                           id="date_prevu" 
                           name="date_prevu" 
                           value="{{ old('date_prevu', $stepData['date_prevu'] ?? '') }}" 
                           required>
                    @error('date_prevu')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <div class="mb-3">
                    <label for="plan_details" class="form-label">{{ __('messages.plan_details') }}</label>
                    <textarea class="form-control @error('plan_details') is-invalid @enderror" 
                              id="plan_details" 
                              name="plan_details" 
                              rows="4">{{ old('plan_details', $stepData['plan_details'] ?? '') }}</textarea>
                    @error('plan_details')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
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
                    <label class="form-label fw-bold">{{ __('messages.theme') }}</label>
                    <p class="text-muted">{{ $stepData['theme'] ?? $formationProcess->theme ?? 'N/A' }}</p>
                </div>
                <div class="col-md-6">
                    <label class="form-label fw-bold">{{ __('messages.date_prevu') }}</label>
                    <p class="text-muted">{{ $stepData['date_prevu'] ? \Carbon\Carbon::parse($stepData['date_prevu'])->format('d/m/Y') : 'N/A' }}</p>
                </div>
            </div>

            @if(!empty($stepData['plan_details']))
                <div class="row mb-3">
                    <div class="col-md-12">
                        <label class="form-label fw-bold">{{ __('messages.plan_details') }}</label>
                        <p class="text-muted">{{ $stepData['plan_details'] }}</p>
                    </div>
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
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.6/dist/js/bootstrap.bundle.min.js" 
    crossorigin="anonymous" defer></script>

