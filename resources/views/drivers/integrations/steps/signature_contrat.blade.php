<div class="row mb-3">
    <div class="col-md-6">
        <label for="contract_signed_date" class="form-label">{{ __('messages.contract_signed_date') }} <span class="text-danger">*</span></label>
        <input type="date" 
               class="form-control @error('contract_signed_date') is-invalid @enderror" 
               id="contract_signed_date" 
               name="contract_signed_date" 
               value="{{ old('contract_signed_date', $stepData['contract_signed_date'] ?? date('Y-m-d')) }}"
               required>
        @error('contract_signed_date')
            <div class="invalid-feedback">{{ $message }}</div>
        @enderror
    </div>
    <div class="col-md-6">
        <label for="signed_by" class="form-label">{{ __('messages.signed_by') }} <span class="text-danger">*</span></label>
        <input type="text" 
               class="form-control @error('signed_by') is-invalid @enderror" 
               id="signed_by" 
               name="signed_by" 
               value="{{ old('signed_by', $stepData['signed_by'] ?? '') }}"
               required>
        @error('signed_by')
            <div class="invalid-feedback">{{ $message }}</div>
        @enderror
    </div>
</div>

<div class="mb-3">
    <label for="contract_path" class="form-label">{{ __('messages.contract_path') }}</label>
    <input type="text" 
           class="form-control @error('contract_path') is-invalid @enderror" 
           id="contract_path" 
           name="contract_path" 
           value="{{ old('contract_path', $stepData['contract_path'] ?? '') }}"
           placeholder="{{ __('messages.contract_path_placeholder') }}">
    @error('contract_path')
        <div class="invalid-feedback">{{ $message }}</div>
    @enderror
</div>

