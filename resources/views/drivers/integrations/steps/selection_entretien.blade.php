<div class="row mb-3">
    <div class="col-md-6">
        <label for="interview_date" class="form-label">{{ __('messages.interview_date') }} <span class="text-danger">*</span></label>
        <input type="date" 
               class="form-control @error('interview_date') is-invalid @enderror" 
               id="interview_date" 
               name="interview_date" 
               value="{{ old('interview_date', $stepData['interview_date'] ?? date('Y-m-d')) }}"
               required>
        @error('interview_date')
            <div class="invalid-feedback">{{ $message }}</div>
        @enderror
    </div>
    <div class="col-md-6">
        <label for="interviewed_by" class="form-label">{{ __('messages.interviewed_by') }} <span class="text-danger">*</span></label>
        <input type="text" 
               class="form-control @error('interviewed_by') is-invalid @enderror" 
               id="interviewed_by" 
               name="interviewed_by" 
               value="{{ old('interviewed_by', $stepData['interviewed_by'] ?? auth()->user()->name ?? '') }}"
               required>
        @error('interviewed_by')
            <div class="invalid-feedback">{{ $message }}</div>
        @enderror
    </div>
</div>

<div class="mb-3">
    <label for="evaluation" class="form-label">{{ __('messages.evaluation') }} <span class="text-danger">*</span></label>
    <textarea class="form-control @error('evaluation') is-invalid @enderror" 
              id="evaluation" 
              name="evaluation" 
              rows="5" 
              required>{{ old('evaluation', $stepData['evaluation'] ?? '') }}</textarea>
    @error('evaluation')
        <div class="invalid-feedback">{{ $message }}</div>
    @enderror
</div>

<div class="mb-3">
    <label for="decision" class="form-label">{{ __('messages.decision') }} <span class="text-danger">*</span></label>
    <select class="form-select @error('decision') is-invalid @enderror" 
            id="decision" 
            name="decision" 
            required>
        <option value="">{{ __('messages.select_decision') }}</option>
        <option value="accepted" {{ old('decision', $stepData['decision'] ?? '') == 'accepted' ? 'selected' : '' }}>
            {{ __('messages.accepted') }}
        </option>
        <option value="rejected" {{ old('decision', $stepData['decision'] ?? '') == 'rejected' ? 'selected' : '' }}>
            {{ __('messages.rejected') }}
        </option>
        <option value="pending" {{ old('decision', $stepData['decision'] ?? '') == 'pending' ? 'selected' : '' }}>
            {{ __('messages.pending') }}
        </option>
    </select>
    @error('decision')
        <div class="invalid-feedback">{{ $message }}</div>
    @enderror
</div>

