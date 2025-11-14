<div class="card border-0 shadow-sm">
    <div class="card-header bg-white border-0 py-3">
        <h5 class="mb-0 text-dark fw-bold">
            <i class="bi bi-2-circle me-2 text-primary"></i>
            {{ __('messages.step_2_evaluation') }}
        </h5>
    </div>
    <div class="card-body">
        @php
            $stepData = $step ? $step->step_data : [];
            $isValidated = $step && $step->isValidated();
            $isRejected = $step && $step->isRejected();
            $canEdit = !$isRejected && !$changement->isValidated() && !$changement->isRejected();
        @endphp

        @if(!$isRejected)
            <form action="{{ route('changements.save-step', ['changement' => $changement->id, 'stepNumber' => $stepNumber]) }}" method="POST" data-validate-form>
                @csrf

                <div class="mb-3">
                    <label for="impact_evaluation" class="form-label">
                        {{ __('messages.impact_evaluation') }} <span class="text-danger">*</span>
                    </label>
                    <textarea class="form-control @error('impact_evaluation') is-invalid @enderror" 
                              id="impact_evaluation" 
                              name="impact_evaluation" 
                              rows="5" 
                        >{{ old('impact_evaluation', $stepData['impact_evaluation'] ?? '') }}</textarea>
                    @error('impact_evaluation')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                    <small class="form-text text-muted">{{ __('messages.impact_evaluation_help') }}</small>
                </div>

                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label for="evaluated_by" class="form-label">{{ __('messages.evaluated_by') }}</label>
                        <input type="text" 
                               class="form-control @error('evaluated_by') is-invalid @enderror" 
                               id="evaluated_by" 
                               name="evaluated_by" 
                               value="{{ old('evaluated_by', $stepData['evaluated_by'] ?? auth()->user()->name ?? '') }}">
                        @error('evaluated_by')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                    <div class="col-md-6 mb-3">
                        <label for="evaluation_date" class="form-label">{{ __('messages.evaluation_date') }}</label>
                        <input type="date" 
                               class="form-control @error('evaluation_date') is-invalid @enderror" 
                               id="evaluation_date" 
                               name="evaluation_date" 
                               value="{{ old('evaluation_date', $stepData['evaluation_date'] ?? date('Y-m-d')) }}">
                        @error('evaluation_date')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                </div>

                <div class="mb-3">
                    <label for="evaluation_notes" class="form-label">{{ __('messages.notes') }} ({{ __('messages.optional') }})</label>
                    <textarea class="form-control @error('evaluation_notes') is-invalid @enderror" 
                              id="evaluation_notes" 
                              name="evaluation_notes" 
                              rows="3"
                              {{ !$canEdit ? 'readonly' : '' }}>{{ old('evaluation_notes', $stepData['evaluation_notes'] ?? '') }}</textarea>
                    @error('evaluation_notes')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <hr class="my-4">
                @if($isValidated)
                    {{-- Show Update and Navigation buttons when validated --}}
                    <div class="d-flex gap-2 justify-content-between">
                        <div>
                            @if($stepNumber > 1)
                                <a href="{{ route('changements.step', ['changement' => $changement->id, 'stepNumber' => $stepNumber - 1]) }}" class="btn btn-outline-secondary">
                                    <i class="bi bi-arrow-left me-1"></i>
                                    {{ __('messages.previous') }}
                                </a>
                            @endif
                        </div>
                        <div class="d-flex gap-2">
                            <button type="submit" class="btn btn-outline-success" data-submit-action="update">
                                <i class="bi bi-pencil me-1"></i>
                                {{ __('messages.update') }}
                            </button>
                            @if($stepNumber < 6)
                                <a href="{{ route('changements.step', ['changement' => $changement->id, 'stepNumber' => $stepNumber + 1]) }}" class="btn btn-primary">
                                    {{ __('messages.next') }} <i class="bi bi-arrow-right ms-1"></i>
                                </a>
                            @endif
                        </div>
                    </div>
                @else
                    <div class="d-flex gap-2 justify-content-between">
                        <div>
                            @if($stepNumber > 1)
                                <a href="{{ route('changements.step', ['changement' => $changement->id, 'stepNumber' => $stepNumber - 1]) }}" class="btn btn-outline-secondary">
                                    <i class="bi bi-arrow-left me-1"></i>
                                    {{ __('messages.previous') }}
                                </a>
                            @endif
                        </div>
                        <div class="d-flex gap-2">
                            @if($canEdit)
                                <button type="submit" class="btn btn-success" data-submit-action="validate">
                                    <i class="bi bi-check-lg me-1"></i>
                                    {{ __('messages.validate_and_next') }}
                                </button>
                                @if(!$changement->isValidated() && !$changement->isRejected())
                                    <button type="button" class="btn btn-danger" onclick="rejectStep({{ $stepNumber }})">
                                        <i class="bi bi-x-lg me-1"></i>
                                        {{ __('messages.reject') }}
                                    </button>
                                @endif
                            @else
                                <button type="submit" class="btn btn-outline-primary" disabled>
                                    <i class="bi bi-save me-1"></i>
                                    {{ __('messages.save') }}
                                </button>
                            @endif
                        </div>
                    </div>
                @endif
            </form>
        @else
            {{-- Show read-only view if rejected --}}
            <div class="row">
                <div class="col-12 mb-3">
                    <label class="form-label fw-bold">{{ __('messages.impact_evaluation') }}</label>
                    <p class="text-muted">{{ $stepData['impact_evaluation'] ?? __('messages.not_available') }}</p>
                </div>
            </div>

            @if($isRejected)
                <div class="alert alert-danger">
                    <i class="bi bi-x-circle me-2"></i>
                    {{ __('messages.rejected') }} - {{ $step->rejection_reason ?? '' }}
                </div>
            @endif
        @endif

        @if($isValidated)
            <div class="alert alert-success mt-3">
                <i class="bi bi-check-circle me-2"></i>
                {{ __('messages.validated') }} - {{ $step->validated_at ? $step->validated_at->format('d/m/Y H:i') : '' }}
            </div>
        @endif
    </div>
</div>

