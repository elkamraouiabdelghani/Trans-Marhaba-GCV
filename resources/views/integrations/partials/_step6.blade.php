<div class="card border-0 shadow-sm">
    <div class="card-header bg-white border-0 py-3">
        <h5 class="mb-0 text-dark fw-bold">
            <i class="bi bi-6-circle me-2 text-primary"></i>
            {{ __('messages.validation') }} + {{ __('messages.induction') }} + {{ __('messages.signature_contrat') }}
        </h5>
    </div>
    <div class="card-body">
        @php
            $stepData = $step ? $step->step_data : [];
        @endphp

        <form action="{{ route('integrations.save-step', ['integration' => $integration->id, 'stepNumber' => 6]) }}" method="POST" enctype="multipart/form-data">
            @csrf

            <h6 class="fw-bold mb-3">{{ __('messages.validation') }}</h6>
            <div class="row mb-4">
                <div class="col-md-6 mb-3">
                    <label for="validation_date" class="form-label">{{ __('messages.validation_date') }} <span class="text-danger">*</span></label>
                    <input type="date" 
                           class="form-control @error('validation_date') is-invalid @enderror" 
                           id="validation_date" 
                           name="validation_date" 
                           value="{{ old('validation_date', $stepData['validation_date'] ?? '') }}" 
                           required>
                    @error('validation_date')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <div class="col-md-6 mb-3">
                    <label for="validated_by" class="form-label">{{ __('messages.validated_by') }} <span class="text-danger">*</span></label>
                    <input type="text" 
                           class="form-control @error('validated_by') is-invalid @enderror" 
                           id="validated_by" 
                           name="validated_by" 
                           value="{{ old('validated_by', $stepData['validated_by'] ?? '') }}" 
                           required>
                    @error('validated_by')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
            </div>

            <h6 class="fw-bold mb-3">{{ __('messages.induction') }}</h6>
            <div class="row mb-4">
                <div class="col-md-6 mb-3">
                    <label for="induction_date" class="form-label">{{ __('messages.induction_date') }}</label>
                    <input type="date" 
                           class="form-control @error('induction_date') is-invalid @enderror" 
                           id="induction_date" 
                           name="induction_date" 
                           value="{{ old('induction_date', $stepData['induction_date'] ?? '') }}">
                    @error('induction_date')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <div class="col-md-6 mb-3">
                    <label for="induction_conducted_by" class="form-label">{{ __('messages.conducted_by') }}</label>
                    <input type="text" 
                           class="form-control @error('induction_conducted_by') is-invalid @enderror" 
                           id="induction_conducted_by" 
                           name="induction_conducted_by" 
                           value="{{ old('induction_conducted_by', $stepData['induction_conducted_by'] ?? '') }}">
                    @error('induction_conducted_by')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
            </div>

            <h6 class="fw-bold mb-3">{{ __('messages.signature_contrat') }}</h6>
            <div class="row mb-4">
                <div class="col-md-6 mb-3">
                    <label for="contract_signed_date" class="form-label">{{ __('messages.contract_signed_date') }}</label>
                    <input type="date" 
                           class="form-control @error('contract_signed_date') is-invalid @enderror" 
                           id="contract_signed_date" 
                           name="contract_signed_date" 
                           value="{{ old('contract_signed_date', $stepData['contract_signed_date'] ?? '') }}">
                    @error('contract_signed_date')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <div class="col-md-6 mb-3">
                    <label for="contract" class="form-label">{{ __('messages.contract_path') }}</label>
                    <input type="file" 
                           class="form-control @error('contract') is-invalid @enderror" 
                           id="contract" 
                           name="contract" 
                           accept=".pdf,.doc,.docx">
                    @error('contract')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                    @if(isset($stepData['contract_path']))
                        <small class="text-muted">{{ __('messages.file_uploaded') }}: {{ basename($stepData['contract_path']) }}</small>
                    @endif
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
                </div>
            @elseif($step && $step->isRejected())
                <div class="alert alert-danger">
                    <i class="bi bi-x-circle me-2"></i>
                    {{ __('messages.rejected') }} - {{ $step->rejection_reason ?? '' }}
                </div>
            @endif

            <hr class="my-4">
            @if($step && $step->isValidated())
                {{-- Show Next button when validated --}}
                <div class="d-flex gap-2 justify-content-between">
                    <div>
                        @if($stepNumber > 1)
                            <a href="{{ route('integrations.step', ['integration' => $integration->id, 'stepNumber' => $stepNumber - 1]) }}" class="btn btn-outline-secondary">
                                <i class="bi bi-arrow-left me-1"></i>
                                {{ __('messages.previous') }}
                            </a>
                        @endif
                    </div>
                    <div>
                        @if($stepNumber < 8)
                            <a href="{{ route('integrations.step', ['integration' => $integration->id, 'stepNumber' => $stepNumber + 1]) }}" class="btn btn-primary">
                                {{ __('messages.next') }} <i class="bi bi-arrow-right ms-1"></i>
                            </a>
                        @endif
                    </div>
                </div>
            @else
                {{-- Show Save/Validate buttons when not validated --}}
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
                                    onclick="validateStep({{ $integration->id }}, 6)">
                                <i class="bi bi-check-lg me-1"></i>
                                {{ __('messages.validate') }}
                            </button>
                        @endif
                    </div>
                </div>
            @endif
        </form>
    </div>
</div>


