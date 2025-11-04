<div class="alert alert-warning">
    <i class="bi bi-exclamation-triangle me-2"></i>
    <strong>{{ __('messages.warning') }}:</strong> {{ __('messages.verification_documentaire_warning') }}
</div>

<div class="row mb-3">
    <div class="col-md-6">
        <label for="verification_date" class="form-label">{{ __('messages.verification_date') }} <span class="text-danger">*</span></label>
        <input type="date" 
               class="form-control @error('verification_date') is-invalid @enderror" 
               id="verification_date" 
               name="verification_date" 
               value="{{ old('verification_date', $stepData['verification_date'] ?? date('Y-m-d')) }}"
               required>
        @error('verification_date')
            <div class="invalid-feedback">{{ $message }}</div>
        @enderror
    </div>
    <div class="col-md-6">
        <label for="verified_by" class="form-label">{{ __('messages.verified_by') }} <span class="text-danger">*</span></label>
        <input type="text" 
               class="form-control @error('verified_by') is-invalid @enderror" 
               id="verified_by" 
               name="verified_by" 
               value="{{ old('verified_by', $stepData['verified_by'] ?? auth()->user()->name ?? '') }}"
               required>
        @error('verified_by')
            <div class="invalid-feedback">{{ $message }}</div>
        @enderror
    </div>
</div>

<div class="mb-3">
    <label for="verification_result" class="form-label">{{ __('messages.verification_result') }} <span class="text-danger">*</span></label>
    <select class="form-select @error('verification_result') is-invalid @enderror" 
            id="verification_result" 
            name="verification_result" 
            required>
        <option value="">{{ __('messages.select_result') }}</option>
        <option value="passed" {{ old('verification_result', $stepData['verification_result'] ?? '') == 'passed' ? 'selected' : '' }}>
            {{ __('messages.passed') }}
        </option>
        <option value="failed" {{ old('verification_result', $stepData['verification_result'] ?? '') == 'failed' ? 'selected' : '' }}>
            {{ __('messages.failed') }}
        </option>
    </select>
    @error('verification_result')
        <div class="invalid-feedback">{{ $message }}</div>
    @enderror
    <small class="form-text text-danger">
        <i class="bi bi-info-circle me-1"></i>
        {{ __('messages.verification_failed_warning') }}
    </small>
</div>

<div class="mb-3">
    <label for="notes" class="form-label">{{ __('messages.notes') }}</label>
    <textarea class="form-control @error('notes') is-invalid @enderror" 
              id="notes" 
              name="notes" 
              rows="4">{{ old('notes', $stepData['notes'] ?? '') }}</textarea>
    @error('notes')
        <div class="invalid-feedback">{{ $message }}</div>
    @enderror
</div>

