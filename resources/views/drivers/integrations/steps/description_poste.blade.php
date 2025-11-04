<div class="mb-3">
    <label for="description" class="form-label">{{ __('messages.description_poste') }} <span class="text-danger">*</span></label>
    <textarea class="form-control @error('description') is-invalid @enderror" 
              id="description" 
              name="description" 
              rows="4" 
              required>{{ old('description', $stepData['description'] ?? '') }}</textarea>
    @error('description')
        <div class="invalid-feedback">{{ $message }}</div>
    @enderror
</div>

<div class="mb-3">
    <label for="responsabilites" class="form-label">{{ __('messages.responsabilites') }} <span class="text-danger">*</span></label>
    <textarea class="form-control @error('responsabilites') is-invalid @enderror" 
              id="responsabilites" 
              name="responsabilites" 
              rows="4" 
              required>{{ old('responsabilites', $stepData['responsabilites'] ?? '') }}</textarea>
    @error('responsabilites')
        <div class="invalid-feedback">{{ $message }}</div>
    @enderror
</div>

<div class="mb-3">
    <label for="qualifications" class="form-label">{{ __('messages.qualifications_requises') }} <span class="text-danger">*</span></label>
    <textarea class="form-control @error('qualifications') is-invalid @enderror" 
              id="qualifications" 
              name="qualifications" 
              rows="4" 
              required>{{ old('qualifications', $stepData['qualifications'] ?? '') }}</textarea>
    @error('qualifications')
        <div class="invalid-feedback">{{ $message }}</div>
    @enderror
</div>

<div class="mb-3">
    <label for="conditions" class="form-label">{{ __('messages.conditions_travail') }}</label>
    <textarea class="form-control @error('conditions') is-invalid @enderror" 
              id="conditions" 
              name="conditions" 
              rows="4">{{ old('conditions', $stepData['conditions'] ?? '') }}</textarea>
    @error('conditions')
        <div class="invalid-feedback">{{ $message }}</div>
    @enderror
</div>

