<div class="card border-0 shadow-sm">
    <div class="card-header bg-white border-0 py-3">
        <h5 class="mb-0 text-dark fw-bold">
            <i class="bi bi-3-circle me-2 text-primary"></i>
            {{ __('messages.step3_validation_budget') }}
        </h5>
    </div>
    <div class="card-body">
        @php
            $stepData = $step ? $step->step_data : [];
            $isValidated = $step && $step->isValidated();
            $isRejected = $step && $step->isRejected();
        @endphp

        @if(!$isRejected)
            <form action="{{ route('formation-processes.save-step', ['formationProcess' => $formationProcess->id, 'stepNumber' => $stepNumber]) }}" method="POST">
                @csrf

                <div class="mb-3">
                    <label for="budget_amount" class="form-label">{{ __('messages.budget_amount') }}</label>
                    <input type="number" 
                           class="form-control @error('budget_amount') is-invalid @enderror" 
                           id="budget_amount" 
                           name="budget_amount" 
                           value="{{ old('budget_amount', $stepData['budget_amount'] ?? '') }}" 
                           step="0.01" 
                           min="0">
                    @error('budget_amount')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <div class="mb-3">
                    <label class="form-label">{{ __('messages.budget_approved') }}</label>
                    <div class="form-check">
                        <input class="form-check-input" type="radio" name="budget_approved" id="budget_approved_yes" value="1" {{ old('budget_approved', $stepData['budget_approved'] ?? '') == '1' ? 'checked' : '' }}>
                        <label class="form-check-label" for="budget_approved_yes">{{ __('messages.yes') }}</label>
                    </div>
                    <div class="form-check">
                        <input class="form-check-input" type="radio" name="budget_approved" id="budget_approved_no" value="0" {{ old('budget_approved', $stepData['budget_approved'] ?? '') == '0' ? 'checked' : '' }}>
                        <label class="form-check-label" for="budget_approved_no">{{ __('messages.no') }}</label>
                    </div>
                </div>

                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label for="validated_by_dg" class="form-label">{{ __('messages.validated_by_dg') }}</label>
                        <input type="text" 
                               class="form-control @error('validated_by_dg') is-invalid @enderror" 
                               id="validated_by_dg" 
                               name="validated_by_dg" 
                               value="{{ old('validated_by_dg', $stepData['validated_by_dg'] ?? '') }}">
                        @error('validated_by_dg')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                    <div class="col-md-6 mb-3">
                        <label for="validated_by_dga" class="form-label">{{ __('messages.validated_by_dga') }}</label>
                        <input type="text" 
                               class="form-control @error('validated_by_dga') is-invalid @enderror" 
                               id="validated_by_dga" 
                               name="validated_by_dga" 
                               value="{{ old('validated_by_dga', $stepData['validated_by_dga'] ?? '') }}">
                        @error('validated_by_dga')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                </div>

                <div class="mb-3">
                    <label for="validation_notes" class="form-label">{{ __('messages.validation_notes') }}</label>
                    <textarea class="form-control @error('validation_notes') is-invalid @enderror" 
                              id="validation_notes" 
                              name="validation_notes" 
                              rows="3">{{ old('validation_notes', $stepData['validation_notes'] ?? '') }}</textarea>
                    @error('validation_notes')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <hr class="my-4">
                @if($step && $step->isValidated())
                    {{-- Show Update and Next buttons when validated --}}
                    <div class="d-flex gap-2 justify-content-between">
                        <div>
                            @if($stepNumber > 1)
                                <a href="{{ route('formation-processes.step', ['formationProcess' => $formationProcess->id, 'stepNumber' => $stepNumber - 1]) }}" class="btn btn-outline-secondary">
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
                            @if($stepNumber < 7)
                                <a href="{{ route('formation-processes.step', ['formationProcess' => $formationProcess->id, 'stepNumber' => $stepNumber + 1]) }}" class="btn btn-primary">
                                    {{ __('messages.next') }} <i class="bi bi-arrow-right ms-1"></i>
                                </a>
                            @endif
                        </div>
                    </div>
                @else
                    {{-- Show Validate button when not validated --}}
                    <div class="d-flex gap-2 justify-content-between">
                        <div>
                            @if($stepNumber > 1)
                                <a href="{{ route('formation-processes.step', ['formationProcess' => $formationProcess->id, 'stepNumber' => $stepNumber - 1]) }}" class="btn btn-outline-secondary">
                                    <i class="bi bi-arrow-left me-1"></i>
                                    {{ __('messages.previous') }}
                                </a>
                            @endif
                        </div>
                        <div class="d-flex gap-2">
                            <button type="submit"
                                    class="btn btn-success"
                                    data-submit-action="validate">
                                <i class="bi bi-check-lg me-1"></i>
                                {{ __('messages.validate_and_next') }}
                            </button>
                            @if(!$isRejected && !$formationProcess->isValidated() && !$formationProcess->isRejected())
                                <button type="button" class="btn btn-danger" onclick="rejectStep({{ $formationProcess->id }}, {{ $stepNumber }})">
                                    <i class="bi bi-x-lg me-1"></i>
                                    {{ __('messages.reject') }}
                                </button>
                            @endif
                        </div>
                    </div>
                @endif
            </form>
        @else
            <div class="row mb-3">
                <div class="col-md-6">
                    <label class="form-label fw-bold">{{ __('messages.budget_amount') }}</label>
                    <p class="text-muted">{{ $stepData['budget_amount'] ?? 'N/A' }}</p>
                </div>
                <div class="col-md-6">
                    <label class="form-label fw-bold">{{ __('messages.budget_approved') }}</label>
                    <p class="text-muted">{{ ($stepData['budget_approved'] ?? false) ? __('messages.yes') : __('messages.no') }}</p>
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

    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.6/dist/js/bootstrap.bundle.min.js" 
    crossorigin="anonymous" defer></script>
