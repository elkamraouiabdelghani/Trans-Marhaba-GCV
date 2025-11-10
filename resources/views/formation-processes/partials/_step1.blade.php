<div class="card border-0 shadow-sm">
    <div class="card-header bg-white border-0 py-3">
        <h5 class="mb-0 text-dark fw-bold">
            <i class="bi bi-1-circle me-2 text-primary"></i>
            {{ __('messages.step1_identification_besoin') }}
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
            <form action="{{ route('formation-processes.save-step', ['formationProcess' => $formationProcess->id, 'stepNumber' => $stepNumber]) }}" method="POST" enctype="multipart/form-data">
                @csrf

                <div class="mb-3">
                    <label for="site" class="form-label">{{ __('messages.site') }} <span class="text-danger">*</span></label>
                    <input type="text" 
                           class="form-control @error('site') is-invalid @enderror" 
                           id="site" 
                           name="site" 
                           value="{{ old('site', $stepData['site'] ?? $formationProcess->site ?? '') }}" 
                           required>
                    @error('site')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <div class="mb-3">
                    <label for="driver_id" class="form-label">{{ __('messages.driver') }} <span class="text-danger">*</span></label>
                    <select class="form-select @error('driver_id') is-invalid @enderror" 
                            id="driver_id" 
                            name="driver_id" 
                            required>
                        <option value="">{{ __('messages.select_driver_for_formation') }}</option>
                        @foreach(\App\Models\Driver::all() as $driver)
                            <option value="{{ $driver->id }}" {{ old('driver_id', $stepData['driver_id'] ?? $formationProcess->driver_id) == $driver->id ? 'selected' : '' }}>
                                {{ $driver->full_name }} 
                                @if($driver->license_number)
                                    - {{ $driver->license_number }}
                                @endif
                            </option>
                        @endforeach
                    </select>
                    @error('driver_id')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label for="flotte_id" class="form-label">{{ __('messages.flotte') }} <span class="text-danger">*</span></label>
                        <select class="form-select @error('flotte_id') is-invalid @enderror" 
                                id="flotte_id" 
                                name="flotte_id" 
                                required>
                            <option value="">{{ __('messages.select_flotte') }}</option>
                            @php
                                // Determine selected flotte: priority: old value > step data > formation process > driver's flotte
                                $selectedFlotteId = old('flotte_id', 
                                    $stepData['flotte_id'] ?? 
                                    $formationProcess->flotte_id ?? 
                                    ($formationProcess->driver && $formationProcess->driver->flotte_id ? $formationProcess->driver->flotte_id : null)
                                );
                            @endphp
                            @foreach(\App\Models\Flotte::all() as $flotte)
                                <option value="{{ $flotte->id }}" {{ $selectedFlotteId == $flotte->id ? 'selected' : '' }}>
                                    {{ $flotte->name }}
                                </option>
                            @endforeach
                        </select>
                        @error('flotte_id')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="col-md-6 mb-3">
                        <label for="formation_type_id" class="form-label">{{ __('messages.formation_type_name') }} <span class="text-danger">*</span></label>
                        <select class="form-select @error('formation_type_id') is-invalid @enderror" 
                                id="formation_type_id" 
                                name="formation_type_id" 
                                required>
                            <option value="">{{ __('messages.select_formation_type') }}</option>
                            @foreach(\App\Models\FormationType::active()->get() as $formationType)
                                <option value="{{ $formationType->id }}" {{ old('formation_type_id', $stepData['formation_type_id'] ?? $formationProcess->formation_type_id) == $formationType->id ? 'selected' : '' }}>
                                    {{ $formationType->name }} ({{ $formationType->code }})
                                </option>
                            @endforeach
                        </select>
                        @error('formation_type_id')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                </div>

                <div class="mb-3">
                    <label for="theme" class="form-label">{{ __('messages.theme') }} <span class="text-danger">*</span></label>
                    <input type="text" 
                           class="form-control @error('theme') is-invalid @enderror" 
                           id="theme" 
                           name="theme" 
                           value="{{ old('theme', $stepData['theme'] ?? $formationProcess->theme ?? '') }}" 
                           required>
                    @error('theme')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <div class="mb-3">
                    <label for="identification_besoin" class="form-label">{{ __('messages.identification_besoin_notes') }}</label>
                    <textarea class="form-control @error('identification_besoin') is-invalid @enderror" 
                              id="identification_besoin" 
                              name="identification_besoin" 
                              rows="3">{{ old('identification_besoin', $stepData['identification_besoin'] ?? '') }}</textarea>
                    @error('identification_besoin')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <hr class="my-4">
                <div class="d-flex gap-2 justify-content-end">
                    <button type="submit" class="btn btn-primary">
                        <i class="bi bi-save me-1"></i>
                        {{ __('messages.save') }}
                    </button>
                    @if($step && $step->isValidated())
                        <button type="button" class="btn btn-success" onclick="validateStep({{ $formationProcess->id }}, {{ $stepNumber }})">
                            <i class="bi bi-check-circle me-1"></i>
                            {{ __('messages.validate') }}
                        </button>
                    @endif
                </div>
            </form>
        @else
            {{-- Read-only view --}}
            <div class="row mb-3">
                <div class="col-md-6">
                    <label class="form-label fw-bold">{{ __('messages.site') }}</label>
                    <p class="text-muted">{{ $stepData['site'] ?? $formationProcess->site ?? 'N/A' }}</p>
                </div>
                <div class="col-md-6">
                    <label class="form-label fw-bold">{{ __('messages.flotte') }}</label>
                    <p class="text-muted">{{ $formationProcess->flotte->name ?? 'N/A' }}</p>
                </div>
            </div>

            <div class="row mb-3">
                <div class="col-md-6">
                    <label class="form-label fw-bold">{{ __('messages.formation_type_name') }}</label>
                    <p class="text-muted">{{ $formationProcess->formationType->name ?? 'N/A' }}</p>
                </div>
                <div class="col-md-6">
                    <label class="form-label fw-bold">{{ __('messages.driver') }}</label>
                    <p class="text-muted">{{ $formationProcess->driver->full_name ?? 'N/A' }}</p>
                </div>
            </div>

            <div class="row mb-3">
                <div class="col-md-12">
                    <label class="form-label fw-bold">{{ __('messages.theme') }}</label>
                    <p class="text-muted">{{ $stepData['theme'] ?? $formationProcess->theme ?? 'N/A' }}</p>
                </div>
            </div>

            @if(!empty($stepData['identification_besoin']))
                <div class="row mb-3">
                    <div class="col-md-12">
                        <label class="form-label fw-bold">{{ __('messages.identification_besoin_notes') }}</label>
                        <p class="text-muted">{{ $stepData['identification_besoin'] }}</p>
                    </div>
                </div>
            @endif

            @if($isValidated)
                <div class="alert alert-success">
                    <i class="bi bi-check-circle me-2"></i>
                    {{ __('messages.validated') }} - {{ $step->validated_at ? $step->validated_at->format('d/m/Y H:i') : '' }}
                    @if($step->validator)
                        <br><small>{{ __('messages.validated_by') }}: {{ $step->validator->name ?? 'N/A' }}</small>
                    @endif
                </div>
            @elseif($isRejected)
                <div class="alert alert-danger">
                    <i class="bi bi-x-circle me-2"></i>
                    {{ __('messages.rejected') }} - {{ $step->rejection_reason ?? '' }}
                </div>
            @endif

            @if($isValidated && $stepNumber < 8)
                <div class="d-flex justify-content-end mt-3">
                    <a href="{{ route('formation-processes.step', ['formationProcess' => $formationProcess->id, 'stepNumber' => $stepNumber + 1]) }}" class="btn btn-primary">
                        {{ __('messages.next') }} <i class="bi bi-arrow-right ms-1"></i>
                    </a>
                </div>
            @endif
        @endif

        {{-- Action buttons for admins --}}
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

        @if($stepNumber == 8 && $isValidated && !$formationProcess->isValidated())
            <div class="d-flex justify-content-end mt-3">
                <button type="button" class="btn btn-primary" onclick="finalizeFormationProcess({{ $formationProcess->id }})">
                    <i class="bi bi-check-all me-1"></i>
                    {{ __('messages.finalize') }}
                </button>
            </div>
        @endif
    </div>
</div>

