<div class="card border-0 shadow-sm">
    <div class="card-header bg-white border-0 py-3">
        <h5 class="mb-0 text-dark fw-bold">
            <i class="bi bi-8-circle me-2 text-primary"></i>
            {{ __('messages.validation_finale') }}
        </h5>
    </div>
    <div class="card-body">
        @php
            $stepData = $step ? $step->step_data : [];
        @endphp

        <div class="alert alert-info">
            <i class="bi bi-info-circle me-2"></i>
            {{ __('messages.final_validation_info') }}
        </div>

        <form action="{{ route('integrations.save-step', ['integration' => $integration->id, 'stepNumber' => 8]) }}" method="POST">
            @csrf

            <div class="row">
                <div class="col-md-6 mb-3">
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

                <div class="col-md-6 mb-3">
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
            </div>

            <div class="mb-3">
                <label for="notes" class="form-label">{{ __('messages.notes') }}</label>
                <textarea class="form-control @error('notes') is-invalid @enderror" 
                          id="notes" 
                          name="notes" 
                          rows="3">{{ old('notes', $stepData['notes'] ?? '') }}</textarea>
                @error('notes')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>

            @if($step && $step->isValidated())
                <div class="alert alert-success">
                    <i class="bi bi-check-circle me-2"></i>
                    {{ __('messages.validated') }}
                    @if($integration->isValidated())
                        <div class="mt-2">
                            <strong>{{ __('messages.integration_finalized') }}</strong>
                        </div>
                    @endif
                </div>
            @elseif($step && $step->isRejected())
                <div class="alert alert-danger">
                    <i class="bi bi-x-circle me-2"></i>
                    {{ __('messages.rejected') }} - {{ $step->rejection_reason ?? '' }}
                </div>
            @endif

            <hr class="my-4">
            <div class="d-flex gap-2 justify-content-between">
                <div>
                    @if($stepNumber > 1)
                        <a href="{{ route('integrations.step', ['integration' => $integration->id, 'stepNumber' => $stepNumber - 1]) }}" class="btn btn-outline-secondary">
                            <i class="bi bi-arrow-left me-1"></i>
                            {{ __('messages.previous') }}
                        </a>
                    @endif
                </div>
                <div class="d-flex gap-2">
                    <button type="submit" class="btn btn-dark">
                        <i class="bi bi-check-circle me-1"></i>
                        {{ __('messages.save') }}
                    </button>
                    @if($step && $step->isPending() && auth()->check())
                        <button type="button" 
                                class="btn btn-success" 
                                onclick="validateStep({{ $integration->id }}, 8)">
                            <i class="bi bi-check-lg me-1"></i>
                            {{ __('messages.validate') }}
                        </button>
                    @endif
                    @if($step && $step->isValidated() && !$integration->isValidated() && auth()->check())
                        <button type="button" 
                                class="btn btn-primary" 
                                onclick="finalizeIntegration({{ $integration->id }})">
                            <i class="bi bi-check-all me-1"></i>
                            {{ __('messages.finalize') }}
                        </button>
                    @endif
                </div>
            </div>
        </form>
    </div>
</div>


