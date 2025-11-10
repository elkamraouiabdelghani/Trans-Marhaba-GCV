<div class="card border-0 shadow-sm">
    <div class="card-header bg-white border-0 py-3">
        <h5 class="mb-0 text-dark fw-bold">
            <i class="bi bi-5-circle me-2 text-primary"></i>
            {{ __('messages.step5_organisation_logistique') }}
        </h5>
    </div>
    <div class="card-body">
        @php
            $stepData = $step ? $step->step_data : [];
            $isValidated = $step && $step->isValidated();
            $isRejected = $step && $step->isRejected();
            $canEdit = !$isValidated && !$isRejected && ($formationProcess->current_step == $stepNumber || !$step);
        @endphp

        @if($canEdit && !$formationProcess->isValidated())
            <form action="{{ route('formation-processes.save-step', ['formationProcess' => $formationProcess->id, 'stepNumber' => $stepNumber]) }}" method="POST">
                @csrf

                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label for="start_date" class="form-label">{{ __('messages.start_date') }} <span class="text-danger">*</span></label>
                        <input type="date" 
                               class="form-control @error('start_date') is-invalid @enderror" 
                               id="start_date" 
                               name="start_date" 
                               value="{{ old('start_date', $stepData['start_date'] ?? '') }}" 
                               required>
                        @error('start_date')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                    <div class="col-md-6 mb-3">
                        <label for="status" class="form-label">{{ __('messages.status') }} <span class="text-danger">*</span></label>
                        <select class="form-select @error('status') is-invalid @enderror" 
                                id="status" 
                                name="status" 
                                required>
                            <option value="">{{ __('messages.select_option') }}</option>
                            <option value="planned" {{ old('status', $stepData['status'] ?? '') == 'planned' ? 'selected' : '' }}>{{ __('messages.status_planned') }}</option>
                            <option value="realized" {{ old('status', $stepData['status'] ?? '') == 'realized' ? 'selected' : '' }}>{{ __('messages.status_realized') }}</option>
                        </select>
                        @error('status')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                </div>

                <div class="mb-3">
                    <label for="location" class="form-label">{{ __('messages.location') }}</label>
                    <input type="text" 
                           class="form-control @error('location') is-invalid @enderror" 
                           id="location" 
                           name="location" 
                           value="{{ old('location', $stepData['location'] ?? '') }}">
                    @error('location')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <div class="mb-3">
                    <label for="equipment_notes" class="form-label">{{ __('messages.equipment_notes') }}</label>
                    <textarea class="form-control @error('equipment_notes') is-invalid @enderror" 
                              id="equipment_notes" 
                              name="equipment_notes" 
                              rows="3">{{ old('equipment_notes', $stepData['equipment_notes'] ?? '') }}</textarea>
                    @error('equipment_notes')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <div class="mb-3">
                    <label for="participants_list" class="form-label">{{ __('messages.participants_list') }}</label>
                    <textarea class="form-control @error('participants_list') is-invalid @enderror" 
                              id="participants_list" 
                              name="participants_list" 
                              rows="3">{{ old('participants_list', $stepData['participants_list'] ?? '') }}</textarea>
                    @error('participants_list')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <hr class="my-4">
                <div class="d-flex gap-2 justify-content-end">
                    <button type="submit" class="btn btn-primary">
                        <i class="bi bi-save me-1"></i>
                        {{ __('messages.save') }}
                    </button>
                </div>
            </form>
        @else
            <div class="row mb-3">
                <div class="col-md-6">
                    <label class="form-label fw-bold">{{ __('messages.start_date') }}</label>
                    <p class="text-muted">{{ $stepData['start_date'] ? \Carbon\Carbon::parse($stepData['start_date'])->format('d/m/Y') : 'N/A' }}</p>
                </div>
                <div class="col-md-6">
                    <label class="form-label fw-bold">{{ __('messages.status') }}</label>
                    <p class="text-muted">
                        @if(($stepData['status'] ?? '') == 'realized')
                            <span class="badge bg-success">{{ __('messages.status_realized') }}</span>
                        @else
                            <span class="badge bg-info">{{ __('messages.status_planned') }}</span>
                        @endif
                    </p>
                </div>
            </div>

            @if($isValidated)
                <div class="alert alert-success">
                    <i class="bi bi-check-circle me-2"></i>
                    {{ __('messages.validated') }} - {{ $step->validated_at ? $step->validated_at->format('d/m/Y H:i') : '' }}
                </div>
            @elseif($isRejected)
                <div class="alert alert-danger">
                    <i class="bi bi-x-circle me-2"></i>
                    {{ __('messages.rejected') }} - {{ $step->rejection_reason ?? '' }}
                </div>
            @endif
        @endif

        @if($step && !$formationProcess->isValidated() && !$formationProcess->isRejected())
            <div class="d-flex gap-2 justify-content-end mt-3">
                @if(!$isValidated)
                    <button type="button" class="btn btn-success" onclick="validateStep({{ $formationProcess->id }}, {{ $stepNumber }})">
                        <i class="bi bi-check-circle me-1"></i>
                        {{ __('messages.validate') }}
                    </button>
                @endif
                @if(!$isRejected)
                    <button type="button" class="btn btn-danger" onclick="rejectStep({{ $formationProcess->id }}, {{ $stepNumber }})">
                        <i class="bi bi-x-circle me-1"></i>
                        {{ __('messages.reject') }}
                    </button>
                @endif
            </div>
        @endif
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.6/dist/js/bootstrap.bundle.min.js" 
    crossorigin="anonymous" defer></script>
