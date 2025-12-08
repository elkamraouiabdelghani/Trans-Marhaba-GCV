<x-app-layout>
    <x-slot name="header">
        @include('layouts.topnav')
    </x-slot>

    <div class="container-fluid py-4 mt-4">
        <div class="d-flex justify-content-between align-items-center mb-3 col-md-10 mx-auto bg-white p-4 rounded-3 shadow-sm">
            <div>
                <h1 class="h4 mb-1">{{ __('messages.coaching_cabines_edit_title') }}</h1>
                <p class="text-muted mb-0">{{ __('messages.coaching_cabines_edit_subtitle') }}</p>
            </div>
            <a href="{{ route('coaching-cabines.show', $coachingCabine) }}" class="btn btn-outline-secondary">
                <i class="bi bi-arrow-left me-1"></i> {{ __('messages.coaching_cabines_back_to_list') }}
            </a>
        </div>

        @if ($errors->any())
            <div class="alert alert-danger">
                <strong>{{ __('messages.form_fix_errors') }}</strong>
                <ul class="mb-0 mt-2">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        @if(session('success'))
            <div class="alert alert-success">
                {{ session('success') }}
            </div>
        @endif

        @if(session('error'))
            <div class="alert alert-danger">
                {{ session('error') }}
            </div>
        @endif

        <div class="card border-0 shadow-sm col-md-10 mx-auto">
            <div class="card-body p-4">
                <form action="{{ route('coaching-cabines.update', $coachingCabine) }}" method="POST" id="coachingSessionForm">
                    @csrf
                    @method('PUT')

                    <div class="row g-3">
                        <div class="col-md-6">
                            <label for="driver_id" class="form-label fw-semibold">{{ __('messages.driver') }} <span class="text-danger">*</span></label>
                            <select name="driver_id" id="driver_id" class="form-select @error('driver_id') is-invalid @enderror" required disabled>
                                <option value="">{{ __('messages.select_driver') ?? 'Sélectionner un chauffeur' }}</option>
                                @foreach($drivers as $driver)
                                    <option value="{{ $driver->id }}" data-flotte-id="{{ $driver->flotte_id ?? '' }}" {{ old('driver_id', $coachingCabine->driver_id) == $driver->id ? 'selected' : '' }}>
                                        {{ $driver->full_name }}
                                    </option>
                                @endforeach
                            </select>
                            <input type="hidden" name="driver_id" value="{{ old('driver_id', $coachingCabine->driver_id) }}">
                            @error('driver_id')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="col-md-6">
                            <label for="flotte_id" class="form-label fw-semibold">{{ __('messages.flotte') }}</label>
                            <select name="flotte_id" id="flotte_id" class="form-select @error('flotte_id') is-invalid @enderror">
                                <option value="">{{ __('messages.all_flottes') }}</option>
                                @foreach($flottes as $flotte)
                                    <option value="{{ $flotte->id }}" {{ old('flotte_id', $coachingCabine->flotte_id) == $flotte->id ? 'selected' : '' }}>
                                        {{ $flotte->name }}
                                    </option>
                                @endforeach
                            </select>
                            @error('flotte_id')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <script>
                            // Auto-select flotte based on selected driver
                            (function() {
                                const driverSelect = document.getElementById('driver_id');
                                const flotteSelect = document.getElementById('flotte_id');
                                
                                if (!driverSelect || !flotteSelect) {
                                    return;
                                }
                                
                                function autoSelectFlotte() {
                                    const selectedDriverOption = driverSelect.options[driverSelect.selectedIndex];
                                    const flotteId = selectedDriverOption.getAttribute('data-flotte-id');
                                    
                                    if (flotteId && flotteId !== '') {
                                        // Find and select the matching flotte option
                                        for (let i = 0; i < flotteSelect.options.length; i++) {
                                            if (flotteSelect.options[i].value === flotteId) {
                                                flotteSelect.value = flotteId;
                                                // Trigger change event to ensure any other listeners are notified
                                                flotteSelect.dispatchEvent(new Event('change'));
                                                break;
                                            }
                                        }
                                    } else {
                                        // If driver has no flotte, clear selection
                                        flotteSelect.value = '';
                                    }
                                }
                                
                                // Auto-select on driver change (if driver dropdown becomes enabled)
                                driverSelect.addEventListener('change', autoSelectFlotte);
                                
                                // Auto-select on page load if driver is already selected
                                if (driverSelect.value) {
                                    autoSelectFlotte();
                                }
                            })();
                        </script>

                        <div class="col-md-6">
                            <label for="date" class="form-label fw-semibold">{{ __('messages.from_date') }} <span class="text-danger">*</span></label>
                            <input type="date" name="date" id="date" class="form-control @error('date') is-invalid @enderror" value="{{ old('date', $coachingCabine->date?->format('Y-m-d')) }}" required>
                            @error('date')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="col-md-6">
                            <label for="date_fin" class="form-label fw-semibold">{{ __('messages.date_fin') }} <span class="text-danger">*</span></label>
                            <input type="date" name="date_fin" id="date_fin" class="form-control @error('date_fin') is-invalid @enderror" value="{{ old('date_fin', $coachingCabine->date_fin?->format('Y-m-d')) }}" required>
                            <small class="text-muted">{{ __('messages.date_fin_auto') ?? 'Calculé automatiquement (date + 5 jours)' }}</small>
                            @error('date_fin')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="col-md-6">
                            <label for="type" class="form-label fw-semibold">{{ __('messages.type') ?? 'Type' }} <span class="text-danger">*</span></label>
                            <select name="type" id="type" class="form-select @error('type') is-invalid @enderror" required disabled>
                                @foreach(\App\Models\CoachingSession::getTypes() as $type)
                                    <option value="{{ $type }}" {{ old('type', $coachingCabine->type) == $type ? 'selected' : '' }}>
                                        {{ \App\Models\CoachingSession::getTypeTitles()[$type] }}
                                    </option>
                                @endforeach
                            </select>
                            <input type="hidden" name="type" value="{{ old('type', $coachingCabine->type) }}">
                            @error('type')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="col-md-6">
                            <label for="moniteur" class="form-label fw-semibold">{{ __('messages.moniteur') }}</label>
                            <select name="moniteur" id="moniteur" class="form-select @error('moniteur') is-invalid @enderror">
                                <option value="">{{ __('messages.select_moniteur') ?? 'Select Moniteur' }}</option>
                                <option value="Redouan Issa" {{ old('moniteur', $coachingCabine->moniteur) === 'Redouan Issa' ? 'selected' : '' }}>Redouan Issa</option>
                                <option value="Fathlah Khalid" {{ old('moniteur', $coachingCabine->moniteur) === 'Fathlah Khalid' ? 'selected' : '' }}>Fathlah Khalid</option>
                            </select>
                            @error('moniteur')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="col-md-6">
                            <label for="status" class="form-label fw-semibold">{{ __('messages.status') }} <span class="text-danger">*</span></label>
                            <select name="status" id="status" class="form-select @error('status') is-invalid @enderror" required>
                                <option value="planned" {{ old('status', $coachingCabine->status) == 'planned' ? 'selected' : '' }}>{{ __('messages.status_planned') }}</option>
                                <option value="in_progress" {{ old('status', $coachingCabine->status) == 'in_progress' ? 'selected' : '' }}>{{ __('messages.status_in_progress') }}</option>
                                <option value="completed" {{ old('status', $coachingCabine->status) == 'completed' ? 'selected' : '' }}>{{ __('messages.status_completed') }}</option>
                                <option value="cancelled" {{ old('status', $coachingCabine->status) == 'cancelled' ? 'selected' : '' }}>{{ __('messages.status_cancelled') }}</option>
                            </select>
                            @error('status')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="col-md-6">
                            <label for="validity_days" class="form-label fw-semibold">{{ __('messages.validity_days') }} <span class="text-danger">*</span></label>
                            <input type="number" name="validity_days" id="validity_days" class="form-control @error('validity_days') is-invalid @enderror" value="{{ old('validity_days', $coachingCabine->validity_days) }}" min="1" required>
                            @error('validity_days')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="col-md-6">
                            <label for="score" class="form-label fw-semibold">{{ __('messages.score') ?? 'Score' }}</label>
                            <input type="number" name="score" id="score" class="form-control @error('score') is-invalid @enderror" value="{{ old('score', $coachingCabine->score) }}" min="0" max="100">
                            <small class="text-muted">{{ __('messages.score_range') ?? 'Entre 0 et 100' }}</small>
                            @error('score')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="col-md-6">
                            <label for="next_planning_session" class="form-label fw-semibold">{{ __('messages.next_planning_session') }}</label>
                            <input type="date" name="next_planning_session" id="next_planning_session" class="form-control @error('next_planning_session') is-invalid @enderror" value="{{ old('next_planning_session', $coachingCabine->next_planning_session?->format('Y-m-d')) }}">
                            @error('next_planning_session')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        
                        <script>
                            // Setup date auto-fill after all date inputs are defined
                            (function() {
                                const dateInput = document.getElementById('date');
                                const dateFinInput = document.getElementById('date_fin');
                                const nextPlanningInput = document.getElementById('next_planning_session');
                                const validityDaysInput = document.getElementById('validity_days');
                                
                                if (!dateInput || !dateFinInput) {
                                    return;
                                }
                                
                                let isAutoFillingDateFin = false;
                                
                                function autoFillDateFin() {
                                    if (!dateInput.value) {
                                        dateFinInput.value = '';
                                        return;
                                    }
                                    
                                    if (document.activeElement === dateFinInput) {
                                        return;
                                    }
                                    
                                    isAutoFillingDateFin = true;
                                    
                                    try {
                                        const date = new Date(dateInput.value);
                                        if (isNaN(date.getTime())) return;
                                        
                                        date.setDate(date.getDate() + 5);
                                        
                                        const year = date.getFullYear();
                                        const month = String(date.getMonth() + 1).padStart(2, '0');
                                        const day = String(date.getDate()).padStart(2, '0');
                                        const newDate = year + '-' + month + '-' + day;
                                        
                                        dateFinInput.value = newDate;
                                        
                                        // Set validity_days to 5 when auto-filling date_fin
                                        if (validityDaysInput) {
                                            validityDaysInput.value = 5;
                                        }
                                    } catch (error) {
                                        console.error('Error calculating date_fin:', error);
                                    } finally {
                                        setTimeout(() => { isAutoFillingDateFin = false; }, 100);
                                    }
                                }
                                
                                function autoFillNextPlanning() {
                                    // Get the element each time to ensure it exists
                                    const nextPlanning = document.getElementById('next_planning_session');
                                    
                                    if (!nextPlanning) {
                                        return;
                                    }
                                    
                                    if (!dateInput.value) {
                                        nextPlanning.value = '';
                                        return;
                                    }
                                    
                                    if (document.activeElement === nextPlanning) {
                                        return;
                                    }
                                    
                                    try {
                                        const date = new Date(dateInput.value);
                                        if (isNaN(date.getTime())) {
                                            return;
                                        }
                                        
                                        // Add 6 months
                                        date.setMonth(date.getMonth() + 6);
                                        
                                        const year = date.getFullYear();
                                        const month = String(date.getMonth() + 1).padStart(2, '0');
                                        const day = String(date.getDate()).padStart(2, '0');
                                        const newDate = year + '-' + month + '-' + day;
                                        
                                        nextPlanning.value = newDate;
                                    } catch (error) {
                                        console.error('Error calculating next_planning_session:', error);
                                    }
                                }
                                
                                function calculateValidityDays() {
                                    if (!validityDaysInput) {
                                        return;
                                    }
                                    
                                    // Don't calculate if user is currently editing validity_days
                                    if (document.activeElement === validityDaysInput) {
                                        return;
                                    }
                                    
                                    // Don't calculate if we're auto-filling date_fin (keep default 5)
                                    if (isAutoFillingDateFin) {
                                        return;
                                    }
                                    
                                    if (!dateInput.value || !dateFinInput.value) {
                                        return;
                                    }
                                    
                                    try {
                                        const startDate = new Date(dateInput.value);
                                        const endDate = new Date(dateFinInput.value);
                                        
                                        if (isNaN(startDate.getTime()) || isNaN(endDate.getTime())) {
                                            return;
                                        }
                                        
                                        // Calculate difference in days
                                        const timeDiff = endDate.getTime() - startDate.getTime();
                                        const daysDiff = Math.ceil(timeDiff / (1000 * 60 * 60 * 24));
                                        
                                        // Only update if difference is positive and valid
                                        if (daysDiff > 0) {
                                            validityDaysInput.value = daysDiff;
                                        }
                                    } catch (error) {
                                        console.error('Error calculating validity_days:', error);
                                    }
                                }
                                
                                function handleDateChange() {
                                    autoFillDateFin();
                                    autoFillNextPlanning();
                                }
                                
                                // Only attach listeners if date field is not readonly
                                if (!dateInput.readOnly) {
                                    dateInput.addEventListener('change', handleDateChange);
                                    dateInput.addEventListener('input', handleDateChange);
                                    dateInput.addEventListener('blur', function() {
                                        if (this.value) handleDateChange();
                                    });
                                }
                                
                                // Calculate validity_days when date_fin changes (user manual edit)
                                dateFinInput.addEventListener('change', function() {
                                    if (!isAutoFillingDateFin) {
                                        calculateValidityDays();
                                    }
                                });
                                
                                dateFinInput.addEventListener('blur', function() {
                                    if (!isAutoFillingDateFin) {
                                        calculateValidityDays();
                                    }
                                });
                                
                                // Auto-fill on page load if date is set
                                if (dateInput.value) {
                                    if (!dateFinInput.value) {
                                        autoFillDateFin();
                                    }
                                    const nextPlanning = document.getElementById('next_planning_session');
                                    if (nextPlanning && !nextPlanning.value) {
                                        autoFillNextPlanning();
                                    }
                                }
                                
                                // Calculate validity_days on page load if both dates are set
                                if (dateInput.value && dateFinInput.value) {
                                    calculateValidityDays();
                                }
                            })();
                        </script>

                        <div class="col-12">
                            <label for="route_taken" class="form-label fw-semibold">{{ __('messages.route_taken') }}</label>
                            <textarea name="route_taken" id="route_taken" rows="3" class="form-control @error('route_taken') is-invalid @enderror">{{ old('route_taken', $coachingCabine->route_taken) }}</textarea>
                            @error('route_taken')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="col-12">
                            <label for="assessment" class="form-label fw-semibold">{{ __('messages.assessment') }}</label>
                            <textarea name="assessment" id="assessment" rows="4" class="form-control @error('assessment') is-invalid @enderror">{{ old('assessment', $coachingCabine->assessment) }}</textarea>
                            @error('assessment')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="col-12">
                            <label for="notes" class="form-label fw-semibold">{{ __('messages.notes') ?? 'Notes' }}</label>
                            <textarea name="notes" id="notes" rows="3" class="form-control @error('notes') is-invalid @enderror">{{ old('notes', $coachingCabine->notes) }}</textarea>
                            @error('notes')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>

                    <div class="mt-4 d-flex gap-2 justify-content-end">
                        <a href="{{ route('coaching-cabines.show', $coachingCabine) }}" class="btn btn-outline-secondary">
                            {{ __('messages.cancel') }}
                        </a>
                        <button type="submit" class="btn btn-dark">
                            <i class="bi bi-save me-1"></i> {{ __('messages.update') }}
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</x-app-layout>

@push('scripts')
<script>
    // Additional functionality can be added here if needed
</script>
@endpush

