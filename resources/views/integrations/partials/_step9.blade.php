<div class="card border-0 shadow-sm">
    <div class="card-header bg-white border-0 py-3">
        <h5 class="mb-0 text-dark fw-bold">
            <i class="bi bi-9-circle me-2 text-primary"></i>
            {{ __('messages.validation_finale') }} + {{ __('messages.signature_contrat') }}
        </h5>
    </div>
    <div class="card-body">
        @php
            $stepData = $step ? $step->step_data : [];
            $prevStepLink = $previousAvailableStep ?? ($stepNumber > 1 ? $stepNumber - 1 : null);
        @endphp

        <div class="alert alert-info">
            <i class="bi bi-info-circle me-2"></i>
            {{ __('messages.final_validation_info') }}
        </div>

        <form action="{{ route('integrations.save-step', ['integration' => $integration->id, 'stepNumber' => 9]) }}" method="POST" enctype="multipart/form-data">
            @csrf
            <input type="hidden" name="submit_action" value="">

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
                           class="form-control @error('contract') is-invalid @enderror @error('contract.*') is-invalid @enderror" 
                           id="contract" 
                           name="contract[]" 
                           accept=".pdf,.doc,.docx"
                           multiple>
                    @error('contract')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                    @error('contract.*')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                    <small class="form-text text-muted">{{ __('messages.multiple_files_allowed') }}</small>
                    @if(!empty($stepData['contract_paths']) && is_array($stepData['contract_paths']))
                        <div class="mt-2">
                            @foreach($stepData['contract_paths'] as $contract)
                                <small class="d-block text-muted">{{ __('messages.file_uploaded') }}: {{ $contract['name'] ?? basename($contract['path']) }}</small>
                            @endforeach
                        </div>
                    @elseif(isset($stepData['contract_path']))
                        <small class="text-muted d-block mt-2">{{ __('messages.file_uploaded') }}: {{ basename($stepData['contract_path']) }}</small>
                    @endif
                </div>
            </div>

            <div class="mb-3">
                <label for="documents" class="form-label">{{ __('messages.supporting_documents') }}</label>
                <input type="file"
                       class="form-control @error('documents') is-invalid @enderror @error('documents.*') is-invalid @enderror"
                       id="documents"
                       name="documents[]"
                       multiple>
                @error('documents')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
                @error('documents.*')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
                <small class="form-text text-muted">{{ __('messages.multiple_files_allowed') }}</small>
                @if(!empty($stepData['documents']) && is_array($stepData['documents']))
                    <div class="mt-2">
                        @foreach($stepData['documents'] as $doc)
                            <small class="d-block text-muted">{{ __('messages.file_uploaded') }}: {{ $doc['name'] ?? basename($doc['path']) }}</small>
                        @endforeach
                    </div>
                @endif
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
            @if($step && $step->isValidated())
                {{-- Show Update button when validated --}}
                <div class="d-flex gap-2 justify-content-between">
                    <div>
                        @if($prevStepLink)
                            <a href="{{ route('integrations.step', ['integration' => $integration->id, 'stepNumber' => $prevStepLink]) }}" class="btn btn-outline-secondary">
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
                        @if(!$integration->isValidated() && auth()->check())
                            <button type="button" 
                                    class="btn btn-primary" 
                                    onclick="finalizeIntegration({{ $integration->id }})">
                                <i class="bi bi-check-all me-1"></i>
                                {{ __('messages.finalize') }}
                            </button>
                        @endif
                    </div>
                </div>
            @else
                {{-- Show Validate button when not validated --}}
                <div class="d-flex gap-2 justify-content-between">
                    <div>
                        @if($prevStepLink)
                            <a href="{{ route('integrations.step', ['integration' => $integration->id, 'stepNumber' => $prevStepLink]) }}" class="btn btn-outline-secondary">
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
                    </div>
                </div>
            @endif
        </form>
    </div>
</div>


