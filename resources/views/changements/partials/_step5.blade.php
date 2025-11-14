<div class="card border-0 shadow-sm">
    <div class="card-header bg-white border-0 py-3">
        <h5 class="mb-0 text-dark fw-bold">
            <i class="bi bi-5-circle me-2 text-primary"></i>
            {{ __('messages.step_5_implementation') }}
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
                    <label for="change_implementation" class="form-label">
                        {{ __('messages.change_implementation') }} <span class="text-danger">*</span>
                    </label>
                    <textarea class="form-control @error('change_implementation') is-invalid @enderror" 
                              id="change_implementation" 
                              name="change_implementation" 
                              rows="5" 
>{{ old('change_implementation', $stepData['change_implementation'] ?? '') }}</textarea>
                    @error('change_implementation')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                    <small class="form-text text-muted">{{ __('messages.change_implementation_help') }}</small>
                </div>

                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label for="implemented_by" class="form-label">{{ __('messages.implemented_by') }}</label>
                        <input type="text" 
                               class="form-control @error('implemented_by') is-invalid @enderror" 
                               id="implemented_by" 
                               name="implemented_by" 
                               value="{{ old('implemented_by', $stepData['implemented_by'] ?? auth()->user()->name ?? '') }}"
>
                        @error('implemented_by')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                    <div class="col-md-6 mb-3">
                        <label for="implementation_date" class="form-label">{{ __('messages.implementation_date') }}</label>
                        <input type="date" 
                               class="form-control @error('implementation_date') is-invalid @enderror" 
                               id="implementation_date" 
                               name="implementation_date" 
                               value="{{ old('implementation_date', $stepData['implementation_date'] ?? date('Y-m-d')) }}"
>
                        @error('implementation_date')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                </div>

                <div class="mb-3">
                    <label for="implementation_notes" class="form-label">{{ __('messages.notes') }} ({{ __('messages.optional') }})</label>
                    <textarea class="form-control @error('implementation_notes') is-invalid @enderror" 
                              id="implementation_notes" 
                              name="implementation_notes" 
                              rows="3"
                              {{ !$canEdit ? 'readonly' : '' }}>{{ old('implementation_notes', $stepData['implementation_notes'] ?? '') }}</textarea>
                    @error('implementation_notes')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <hr class="my-4">
                @if($isValidated)
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
                                <a href="{{ route('changements.checklist', $changement) }}" class="btn btn-primary">
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
            <div class="row">
                <div class="col-12 mb-3">
                    <label class="form-label fw-bold">{{ __('messages.change_implementation') }}</label>
                    <p class="text-muted">{{ $stepData['change_implementation'] ?? __('messages.not_available') }}</p>
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

