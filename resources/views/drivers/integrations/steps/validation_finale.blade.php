<div class="alert alert-success">
    <i class="bi bi-check-circle me-2"></i>
    {{ __('messages.final_validation_info') }}
</div>

<div class="row mb-3">
    <div class="col-md-6">
        <label for="validation_date" class="form-label">{{ __('messages.validation_date') }} <span class="text-danger">*</span></label>
        <input type="date" 
               class="form-control @error('validation_date') is-invalid @enderror" 
               id="validation_date" 
               name="validation_date" 
               value="{{ old('validation_date', $stepData['validation_date'] ?? date('Y-m-d')) }}"
               required>
        @error('validation_date')
            <div class="invalid-feedback">{{ $message }}</div>
        @enderror
    </div>
    <div class="col-md-6">
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
    <label for="decision" class="form-label">{{ __('messages.decision') }} <span class="text-danger">*</span></label>
    <select class="form-select @error('decision') is-invalid @enderror" 
            id="decision" 
            name="decision" 
            required>
        <option value="">{{ __('messages.select_decision') }}</option>
        <option value="validated" {{ old('decision', $stepData['decision'] ?? '') == 'validated' ? 'selected' : '' }}>
            {{ __('messages.validated') }}
        </option>
        <option value="rejected" {{ old('decision', $stepData['decision'] ?? '') == 'rejected' ? 'selected' : '' }}>
            {{ __('messages.rejected') }}
        </option>
    </select>
    @error('decision')
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

