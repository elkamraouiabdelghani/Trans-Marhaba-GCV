<div class="alert alert-warning">
    <i class="bi bi-exclamation-triangle me-2"></i>
    <strong>{{ __('messages.warning') }}:</strong> {{ __('messages.test_failed_warning') }}
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
        <label for="evaluator" class="form-label">{{ __('messages.evaluator') }} <span class="text-danger">*</span></label>
        <input type="text" 
               class="form-control @error('evaluator') is-invalid @enderror" 
               id="evaluator" 
               name="evaluator" 
               value="{{ old('evaluator', $stepData['evaluator'] ?? auth()->user()->name ?? '') }}"
               required>
        @error('evaluator')
            <div class="invalid-feedback">{{ $message }}</div>
        @enderror
    </div>
</div>

<div class="row mb-3">
    <div class="col-md-6">
        <label for="oral_score" class="form-label">{{ __('messages.oral_score') }} (0-100) <span class="text-danger">*</span></label>
        <input type="number" 
               class="form-control @error('oral_score') is-invalid @enderror" 
               id="oral_score" 
               name="oral_score" 
               min="0" 
               max="100" 
               value="{{ old('oral_score', $stepData['oral_score'] ?? '') }}"
               required>
        @error('oral_score')
            <div class="invalid-feedback">{{ $message }}</div>
        @enderror
    </div>
    <div class="col-md-6">
        <label for="written_score" class="form-label">{{ __('messages.written_score') }} (0-100) <span class="text-danger">*</span></label>
        <input type="number" 
               class="form-control @error('written_score') is-invalid @enderror" 
               id="written_score" 
               name="written_score" 
               min="0" 
               max="100" 
               value="{{ old('written_score', $stepData['written_score'] ?? '') }}"
               required>
        @error('written_score')
            <div class="invalid-feedback">{{ $message }}</div>
        @enderror
    </div>
</div>

<div class="mb-3">
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

