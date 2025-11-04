<div class="alert alert-warning">
    <i class="bi bi-exclamation-triangle me-2"></i>
    <strong>{{ __('messages.warning') }}:</strong> {{ __('messages.accompaniment_failed_warning') }}
</div>

<div class="row mb-3">
    <div class="col-md-6">
        <label for="end_date" class="form-label">{{ __('messages.end_date') }} <span class="text-danger">*</span></label>
        <input type="date" 
               class="form-control @error('end_date') is-invalid @enderror" 
               id="end_date" 
               name="end_date" 
               value="{{ old('end_date', $stepData['end_date'] ?? date('Y-m-d')) }}"
               required>
        @error('end_date')
            <div class="invalid-feedback">{{ $message }}</div>
        @enderror
    </div>
    <div class="col-md-6">
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
</div>

<div class="mb-3">
    <label for="accompaniment_result" class="form-label">{{ __('messages.accompaniment_result') }} <span class="text-danger">*</span></label>
    <select class="form-select @error('accompaniment_result') is-invalid @enderror" 
            id="accompaniment_result" 
            name="accompaniment_result" 
            required>
        <option value="">{{ __('messages.select_result') }}</option>
        <option value="passed" {{ old('accompaniment_result', $stepData['accompaniment_result'] ?? '') == 'passed' ? 'selected' : '' }}>
            {{ __('messages.passed') }}
        </option>
        <option value="failed" {{ old('accompaniment_result', $stepData['accompaniment_result'] ?? '') == 'failed' ? 'selected' : '' }}>
            {{ __('messages.failed') }}
        </option>
    </select>
    @error('accompaniment_result')
        <div class="invalid-feedback">{{ $message }}</div>
    @enderror
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

