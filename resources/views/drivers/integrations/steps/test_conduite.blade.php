<div class="alert alert-warning">
    <i class="bi bi-exclamation-triangle me-2"></i>
    <strong>{{ __('messages.warning') }}:</strong> {{ __('messages.test_conduite_failed_warning') }}
</div>

<div class="row mb-3">
    <div class="col-md-6">
        <label for="test_date" class="form-label">{{ __('messages.test_date') }} <span class="text-danger">*</span></label>
        <input type="date" 
               class="form-control @error('test_date') is-invalid @enderror" 
               id="test_date" 
               name="test_date" 
               value="{{ old('test_date', $stepData['test_date'] ?? date('Y-m-d')) }}"
               required>
        @error('test_date')
            <div class="invalid-feedback">{{ $message }}</div>
        @enderror
    </div>
    <div class="col-md-6">
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

<div class="row mb-3">
    <div class="col-md-6">
        <label for="score" class="form-label">{{ __('messages.score') }} (0-100) <span class="text-danger">*</span></label>
        <input type="number" 
               class="form-control @error('score') is-invalid @enderror" 
               id="score" 
               name="score" 
               min="0" 
               max="100" 
               value="{{ old('score', $stepData['score'] ?? '') }}"
               required>
        @error('score')
            <div class="invalid-feedback">{{ $message }}</div>
        @enderror
    </div>
    <div class="col-md-6">
        <label for="test_result" class="form-label">{{ __('messages.test_result') }} <span class="text-danger">*</span></label>
        <select class="form-select @error('test_result') is-invalid @enderror" 
                id="test_result" 
                name="test_result" 
                required>
            <option value="">{{ __('messages.select_result') }}</option>
            <option value="passed" {{ old('test_result', $stepData['test_result'] ?? '') == 'passed' ? 'selected' : '' }}>
                {{ __('messages.passed') }}
            </option>
            <option value="failed" {{ old('test_result', $stepData['test_result'] ?? '') == 'failed' ? 'selected' : '' }}>
                {{ __('messages.failed') }}
            </option>
        </select>
        @error('test_result')
            <div class="invalid-feedback">{{ $message }}</div>
        @enderror
    </div>
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

