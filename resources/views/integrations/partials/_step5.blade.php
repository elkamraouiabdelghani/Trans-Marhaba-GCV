<div class="card border-0 shadow-sm">
    <div class="card-header bg-white border-0 py-3">
        <h5 class="mb-0 text-dark fw-bold">
            <i class="bi bi-5-circle me-2 text-primary"></i>
            {{ __('messages.test_conduite') }}
        </h5>
    </div>
    <div class="card-body">
        @php
            $stepData = $step ? $step->step_data : [];
        @endphp

        <div class="alert alert-warning">
            <i class="bi bi-exclamation-triangle me-2"></i>
            {{ __('messages.test_conduite_failed_warning') }}
        </div>

        <form action="{{ route('integrations.save-step', ['integration' => $integration->id, 'stepNumber' => 5]) }}" method="POST">
            @csrf

            <div class="row">
                <div class="col-md-6 mb-3">
                    <label for="test_date" class="form-label">{{ __('messages.test_date') }} <span class="text-danger">*</span></label>
                    <input type="date" 
                           class="form-control @error('test_date') is-invalid @enderror" 
                           id="test_date" 
                           name="test_date" 
                           value="{{ old('test_date', $stepData['test_date'] ?? '') }}" 
                           required>
                    @error('test_date')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <div class="col-md-6 mb-3">
                    <label for="instructor" class="form-label">{{ __('messages.instructor') }} <span class="text-danger">*</span></label>
                    <input type="text" 
                           class="form-control @error('instructor') is-invalid @enderror" 
                           id="instructor" 
                           name="instructor" 
                           value="{{ old('instructor', $stepData['instructor'] ?? '') }}" 
                           required>
                    @error('instructor')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
            </div>

            <div class="mb-3">
                <label for="score" class="form-label">{{ __('messages.score') }}</label>
                <input type="number" 
                       class="form-control @error('score') is-invalid @enderror" 
                       id="score" 
                       name="score" 
                       value="{{ old('score', $stepData['score'] ?? '') }}" 
                       min="0" 
                       max="100">
                @error('score')
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
                                    onclick="validateStep({{ $integration->id }}, 5)">
                                <i class="bi bi-check-lg me-1"></i>
                                {{ __('messages.validate') }}
                            </button>
                            <button type="button" 
                                    class="btn btn-danger" 
                                    onclick="rejectStep({{ $integration->id }}, 5)">
                                <i class="bi bi-x-lg me-1"></i>
                                {{ __('messages.reject') }}
                            </button>
                        @endif
                    </div>
                </div>
            @endif
        </form>
    </div>
</div>


