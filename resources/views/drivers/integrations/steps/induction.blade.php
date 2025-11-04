<div class="row mb-3">
    <div class="col-md-6">
        <label for="induction_date" class="form-label">{{ __('messages.induction_date') }} <span class="text-danger">*</span></label>
        <input type="date" 
               class="form-control @error('induction_date') is-invalid @enderror" 
               id="induction_date" 
               name="induction_date" 
               value="{{ old('induction_date', $stepData['induction_date'] ?? date('Y-m-d')) }}"
               required>
        @error('induction_date')
            <div class="invalid-feedback">{{ $message }}</div>
        @enderror
    </div>
    <div class="col-md-6">
        <label for="conducted_by" class="form-label">{{ __('messages.conducted_by') }} <span class="text-danger">*</span></label>
        <input type="text" 
               class="form-control @error('conducted_by') is-invalid @enderror" 
               id="conducted_by" 
               name="conducted_by" 
               value="{{ old('conducted_by', $stepData['conducted_by'] ?? auth()->user()->name ?? '') }}"
               required>
        @error('conducted_by')
            <div class="invalid-feedback">{{ $message }}</div>
        @enderror
    </div>
</div>

<div class="mb-3 form-check">
    <input type="checkbox" 
           class="form-check-input @error('completed') is-invalid @enderror" 
           id="completed" 
           name="completed" 
           value="1"
           {{ old('completed', $stepData['completed'] ?? false) ? 'checked' : '' }}>
    <label class="form-check-label" for="completed">
        {{ __('messages.induction_completed') }}
    </label>
    @error('completed')
        <div class="invalid-feedback">{{ $message }}</div>
    @enderror
</div>

