<div class="card border-0 shadow-sm">
    <div class="card-header bg-white border-0 py-3">
        <h5 class="mb-0 text-dark fw-bold">
            <i class="bi bi-8-circle me-2 text-primary"></i>
            {{ __('messages.accompagnement') }}
        </h5>
    </div>
    <div class="card-body">
        @php
            $stepData = $step ? $step->step_data : [];
            $prevStepLink = $previousAvailableStep ?? ($stepNumber > 1 ? $stepNumber - 1 : null);
            $nextStepLink = $nextAvailableStep ?? ($stepNumber < 9 ? $stepNumber + 1 : null);
        @endphp

        <div class="alert alert-warning">
            <i class="bi bi-exclamation-triangle me-2"></i>
            {{ __('messages.accompaniment_failed_warning') }}
        </div>

        <form action="{{ route('integrations.save-step', ['integration' => $integration->id, 'stepNumber' => 8]) }}" method="POST" enctype="multipart/form-data">
            @csrf
            <input type="hidden" name="submit_action" value="">

            <div class="row">
                <div class="col-md-6 mb-3">
                    <label for="accompaniment_start_date" class="form-label">{{ __('messages.accompaniment_start_date') }} <span class="text-danger">*</span></label>
                    <input type="date" 
                           class="form-control @error('accompaniment_start_date') is-invalid @enderror" 
                           id="accompaniment_start_date" 
                           name="accompaniment_start_date" 
                           value="{{ old('accompaniment_start_date', $stepData['accompaniment_start_date'] ?? '') }}" 
                           required>
                    @error('accompaniment_start_date')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <div class="col-md-6 mb-3">
                    <label for="accompaniment_end_date" class="form-label">{{ __('messages.accompaniment_end_date') }}</label>
                    <input type="date" 
                           class="form-control @error('accompaniment_end_date') is-invalid @enderror" 
                           id="accompaniment_end_date" 
                           name="accompaniment_end_date" 
                           value="{{ old('accompaniment_end_date', $stepData['accompaniment_end_date'] ?? '') }}">
                    @error('accompaniment_end_date')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
            </div>

            <div class="mb-3">
                <label for="accompanied_by" class="form-label">{{ __('messages.accompanied_by') }} <span class="text-danger">*</span></label>
                <input type="text" 
                       class="form-control @error('accompanied_by') is-invalid @enderror" 
                       id="accompanied_by" 
                       name="accompanied_by" 
                       value="{{ old('accompanied_by', $stepData['accompanied_by'] ?? '') }}" 
                       required>
                @error('accompanied_by')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>

            <div class="mb-3">
                <label for="result" class="form-label">{{ __('messages.test_result') }} <span class="text-danger">*</span></label>
                <select class="form-select @error('result') is-invalid @enderror" 
                        id="result" 
                        name="result" 
                        required>
                    <option value="">{{ __('messages.select_result') }}</option>
                    <option value="passed" {{ old('result', $stepData['result'] ?? '') === 'passed' ? 'selected' : '' }}>{{ __('messages.passed') }}</option>
                    <option value="failed" {{ old('result', $stepData['result'] ?? '') === 'failed' ? 'selected' : '' }}>{{ __('messages.failed') }}</option>
                </select>
                @error('result')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
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
                </div>
            @elseif($step && $step->isRejected())
                <div class="alert alert-danger">
                    <i class="bi bi-x-circle me-2"></i>
                    {{ __('messages.rejected') }} - {{ $step->rejection_reason ?? '' }}
                </div>
            @endif

            <hr class="my-4">
            @if($step && $step->isValidated())
                {{-- Show Update and Next buttons when validated --}}
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
                        @if($nextStepLink)
                        <a href="{{ route('integrations.step', ['integration' => $integration->id, 'stepNumber' => $nextStepLink]) }}" class="btn btn-primary">
                            {{ __('messages.next') }} <i class="bi bi-arrow-right ms-1"></i>
                        </a>
                        @endif
                    </div>
                </div>
            @else
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
