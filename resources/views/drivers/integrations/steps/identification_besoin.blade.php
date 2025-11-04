@php
    $drivers = \App\Models\Driver::all();
@endphp

<div class="mb-3">
    <label for="driver_id" class="form-label">{{ __('messages.select_driver') }} <span class="text-danger">*</span></label>
    <select class="form-select @error('driver_id') is-invalid @enderror" 
            id="driver_id" 
            name="driver_id" 
            required>
        <option value="">{{ __('messages.select_driver') }}</option>
        @foreach($drivers as $driverOption)
            <option value="{{ $driverOption->id }}" {{ old('driver_id', $stepData['driver_id'] ?? '') == $driverOption->id ? 'selected' : '' }}>
                {{ $driverOption->full_name ?? __('messages.not_available') }}
            </option>
        @endforeach
    </select>
    @error('driver_id')
        <div class="invalid-feedback">{{ $message }}</div>
    @enderror
</div>

<div class="mb-3">
    <label for="identification_besoin" class="form-label">{{ __('messages.identification_besoin') }} <span class="text-danger">*</span></label>
    <textarea class="form-control @error('identification_besoin') is-invalid @enderror" 
              id="identification_besoin" 
              name="identification_besoin" 
              rows="4" 
              required>{{ old('identification_besoin', $stepData['identification_besoin'] ?? '') }}</textarea>
    @error('identification_besoin')
        <div class="invalid-feedback">{{ $message }}</div>
    @enderror
    <small class="form-text text-muted">{{ __('messages.enter_identification_besoin') }}</small>
</div>

<div class="mb-3">
    <label for="description_poste" class="form-label">{{ __('messages.description_poste') }} <span class="text-danger">*</span></label>
    <textarea class="form-control @error('description_poste') is-invalid @enderror" 
              id="description_poste" 
              name="description_poste" 
              rows="4" 
              required>{{ old('description_poste', $stepData['description_poste'] ?? '') }}</textarea>
    @error('description_poste')
        <div class="invalid-feedback">{{ $message }}</div>
    @enderror
    <small class="form-text text-muted">{{ __('messages.enter_description_poste') }}</small>
</div>

<div class="mb-3">
    <label for="prospection" class="form-label">{{ __('messages.prospection') }} <span class="text-danger">*</span></label>
    <textarea class="form-control @error('prospection') is-invalid @enderror" 
              id="prospection" 
              name="prospection" 
              rows="4" 
              required>{{ old('prospection', $stepData['prospection'] ?? '') }}</textarea>
    @error('prospection')
        <div class="invalid-feedback">{{ $message }}</div>
    @enderror
    <small class="form-text text-muted">{{ __('messages.enter_prospection') }}</small>
</div>

