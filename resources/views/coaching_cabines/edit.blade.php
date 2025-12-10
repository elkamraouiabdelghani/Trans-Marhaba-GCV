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
                            <select name="driver_id" id="driver_id" class="form-select @error('driver_id') is-invalid @enderror" required>
                                <option value="">{{ __('messages.select_driver') ?? 'Sélectionner un chauffeur' }}</option>
                                @foreach($drivers as $driver)
                                    <option value="{{ $driver->id }}" data-flotte-id="{{ $driver->flotte_id ?? '' }}" {{ old('driver_id', $coachingCabine->driver_id) == $driver->id ? 'selected' : '' }}>
                                        {{ $driver->full_name }}
                                    </option>
                                @endforeach
                            </select>
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
                            <select name="type" id="type" class="form-select @error('type') is-invalid @enderror" required>
                                @foreach(\App\Models\CoachingSession::getTypes() as $type)
                                    <option value="{{ $type }}" {{ old('type', $coachingCabine->type) == $type ? 'selected' : '' }}>
                                        {{ \App\Models\CoachingSession::getTypeTitles()[$type] }}
                                    </option>
                                @endforeach
                            </select>
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

                        <!-- Hidden inputs for coordinates -->
                        <input type="hidden" name="from_latitude" id="from_latitude" value="{{ old('from_latitude', $coachingCabine->from_latitude) }}">
                        <input type="hidden" name="from_longitude" id="from_longitude" value="{{ old('from_longitude', $coachingCabine->from_longitude) }}">
                        <input type="hidden" name="to_latitude" id="to_latitude" value="{{ old('to_latitude', $coachingCabine->to_latitude) }}">
                        <input type="hidden" name="to_longitude" id="to_longitude" value="{{ old('to_longitude', $coachingCabine->to_longitude) }}">
                        <input type="hidden" name="from_location_name" id="from_location_name" value="{{ old('from_location_name', $coachingCabine->from_location_name) }}">
                        <input type="hidden" name="to_location_name" id="to_location_name" value="{{ old('to_location_name', $coachingCabine->to_location_name) }}">

                        <div class="col-12">
                            <label class="form-label fw-semibold mb-1">{{ __('messages.route_taken') }}</label>
                            <div class="row g-3">
                                <div class="col-lg-6">
                                    <div class="card border-0">
                                        <div class="card-body">
                                            <div class="d-flex justify-content-between align-items-center mb-2">
                                                <div>
                                                    <h6 class="fw-semibold mb-0">{{ __('messages.from_location') }}</h6>
                                                    <small class="text-muted" id="from-coords-label">
                                                        @if(old('from_latitude', $coachingCabine->from_latitude) && old('from_longitude', $coachingCabine->from_longitude))
                                                            @if(old('from_location_name', $coachingCabine->from_location_name))
                                                                <span class="fw-semibold">{{ old('from_location_name', $coachingCabine->from_location_name) }}</span><br>
                                                                <small class="text-muted">{{ __('messages.location_coords_label') ?? 'Coordinates' }}: {{ old('from_latitude', $coachingCabine->from_latitude) }}, {{ old('from_longitude', $coachingCabine->from_longitude) }}</small>
                                                            @else
                                                                {{ __('messages.location_coords_label') ?? 'Coordinates' }}:
                                                                <span class="fw-semibold">{{ old('from_latitude', $coachingCabine->from_latitude) }}, {{ old('from_longitude', $coachingCabine->from_longitude) }}</span>
                                                            @endif
                                                        @else
                                                            {{ __('messages.location_map_help') ?? 'Click select to set coordinates.' }}
                                                        @endif
                                                    </small>
                                                </div>
                                                <button type="button" class="btn btn-outline-secondary btn-sm" data-bs-toggle="modal" data-bs-target="#fromLocationModal">
                                                    <i class="bi bi-map me-1"></i> {{ __('messages.select_on_map') }}
                                                </button>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-lg-6">
                                    <div class="card border-0">
                                        <div class="card-body">
                                            <div class="d-flex justify-content-between align-items-center mb-2">
                                                <div>
                                                    <h6 class="fw-semibold mb-0">{{ __('messages.to_location') }}</h6>
                                                    <small class="text-muted" id="to-coords-label">
                                                        @if(old('to_latitude', $coachingCabine->to_latitude) && old('to_longitude', $coachingCabine->to_longitude))
                                                            @if(old('to_location_name', $coachingCabine->to_location_name))
                                                                <span class="fw-semibold">{{ old('to_location_name', $coachingCabine->to_location_name) }}</span><br>
                                                                <small class="text-muted">{{ __('messages.location_coords_label') ?? 'Coordinates' }}: {{ old('to_latitude', $coachingCabine->to_latitude) }}, {{ old('to_longitude', $coachingCabine->to_longitude) }}</small>
                                                            @else
                                                                {{ __('messages.location_coords_label') ?? 'Coordinates' }}:
                                                                <span class="fw-semibold">{{ old('to_latitude', $coachingCabine->to_latitude) }}, {{ old('to_longitude', $coachingCabine->to_longitude) }}</span>
                                                            @endif
                                                        @else
                                                            {{ __('messages.location_map_help') ?? 'Click select to set coordinates.' }}
                                                        @endif
                                                    </small>
                                                </div>
                                                <button type="button" class="btn btn-outline-secondary btn-sm" data-bs-toggle="modal" data-bs-target="#toLocationModal">
                                                    <i class="bi bi-map me-1"></i> {{ __('messages.select_on_map') }}
                                                </button>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div class="col-12">
                                <label for="route_taken" class="form-label fw-semibold">{{ __('messages.description') }}</label>
                            <textarea name="route_taken" id="route_taken" rows="3" class="form-control @error('route_taken') is-invalid @enderror">{{ old('route_taken', $coachingCabine->route_taken) }}</textarea>
                            @error('route_taken')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                            </div>
                        </div>

                        <!-- From Location Modal -->
                        <div class="modal fade" id="fromLocationModal" tabindex="-1" aria-labelledby="fromLocationModalLabel" aria-hidden="true">
                            <div class="modal-dialog modal-lg">
                                <div class="modal-content">
                                    <div class="modal-header">
                                        <h5 class="modal-title" id="fromLocationModalLabel">{{ __('messages.select_from_location') ?? 'Select From Location' }}</h5>
                                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                    </div>
                                    <div class="modal-body">
                                        <div class="mb-3">
                                            <label for="from_city_search_input" class="form-label small mb-1">
                                                <i class="bi bi-search me-1"></i> {{ __('messages.search_city_village') ?? 'Search City or Village' }}
                                            </label>
                                            <div class="input-group input-group-sm">
                                                <input type="text" id="from_city_search_input" class="form-control" placeholder="{{ __('messages.enter_city_village_name') ?? 'e.g., Tangier, Rabat, Casablanca...' }}">
                                                <button type="button" class="btn btn-primary" id="from_city_search_btn">
                                                    <i class="bi bi-search"></i> {{ __('messages.search') ?? 'Search' }}
                                                </button>
                                            </div>
                                            <div id="from_city_search_error" class="text-danger small mt-1 d-none"></div>
                                        </div>
                                        <div class="mb-3">
                                            <label for="from_location_name_input" class="form-label small mb-1">{{ __('messages.location_name') ?? 'Location Name' }}</label>
                                            <input type="text" id="from_location_name_input" class="form-control form-control-sm" placeholder="{{ __('messages.enter_location_name') ?? 'Enter location name (optional)' }}">
                                        </div>
                                        <div class="row g-3">
                                            <div class="col-md-4">
                                                <label for="from_lat_input" class="form-label small mb-1">{{ __('messages.from_latitude') }}</label>
                                                <input type="text" id="from_lat_input" class="form-control form-control-sm" placeholder="33.5731">
                                            </div>
                                            <div class="col-md-4">
                                                <label for="from_lng_input" class="form-label small mb-1">{{ __('messages.from_longitude') }}</label>
                                                <input type="text" id="from_lng_input" class="form-control form-control-sm" placeholder="-7.5898">
                                            </div>
                                        </div>
                                        <div class="mt-3">
                                            <div id="from-location-map" style="height: 360px; width: 100%; background: #f5f5f5;"></div>
                                        </div>
                                    </div>
                                    <div class="modal-footer">
                                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">{{ __('messages.cancel') }}</button>
                                        <button type="button" class="btn btn-primary" id="applyFromLocation">{{ __('messages.apply') ?? 'Apply' }}</button>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- To Location Modal -->
                        <div class="modal fade" id="toLocationModal" tabindex="-1" aria-labelledby="toLocationModalLabel" aria-hidden="true">
                            <div class="modal-dialog modal-lg">
                                <div class="modal-content">
                                    <div class="modal-header">
                                        <h5 class="modal-title" id="toLocationModalLabel">{{ __('messages.select_to_location') ?? 'Select To Location' }}</h5>
                                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                    </div>
                                    <div class="modal-body">
                                        <div class="mb-3">
                                            <label for="to_city_search_input" class="form-label small mb-1">
                                                <i class="bi bi-search me-1"></i> {{ __('messages.search_city_village') ?? 'Search City or Village' }}
                                            </label>
                                            <div class="input-group input-group-sm">
                                                <input type="text" id="to_city_search_input" class="form-control" placeholder="{{ __('messages.enter_city_village_name') ?? 'e.g., Tangier, Rabat, Casablanca...' }}">
                                                <button type="button" class="btn btn-primary" id="to_city_search_btn">
                                                    <i class="bi bi-search"></i> {{ __('messages.search') ?? 'Search' }}
                                                </button>
                                            </div>
                                            <div id="to_city_search_error" class="text-danger small mt-1 d-none"></div>
                                        </div>
                                        <div class="mb-3">
                                            <label for="to_location_name_input" class="form-label small mb-1">{{ __('messages.location_name') ?? 'Location Name' }}</label>
                                            <input type="text" id="to_location_name_input" class="form-control form-control-sm" placeholder="{{ __('messages.enter_location_name') ?? 'Enter location name (optional)' }}">
                                        </div>
                                        <div class="row g-3">
                                            <div class="col-md-4">
                                                <label for="to_lat_input" class="form-label small mb-1">{{ __('messages.to_latitude') }}</label>
                                                <input type="text" id="to_lat_input" class="form-control form-control-sm" placeholder="33.5731">
                                            </div>
                                            <div class="col-md-4">
                                                <label for="to_lng_input" class="form-label small mb-1">{{ __('messages.to_longitude') }}</label>
                                                <input type="text" id="to_lng_input" class="form-control form-control-sm" placeholder="-7.5898">
                                            </div>
                                        </div>
                                        <div class="mt-3">
                                            <div id="to-location-map" style="height: 360px; width: 100%; background: #f5f5f5;"></div>
                                        </div>
                                    </div>
                                    <div class="modal-footer">
                                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">{{ __('messages.cancel') }}</button>
                                        <button type="button" class="btn btn-primary" id="applyToLocation">{{ __('messages.apply') ?? 'Apply' }}</button>
                                    </div>
                                </div>
                            </div>
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

                        {{-- Rest Places Section --}}
                        <div class="col-12">
                            <label class="form-label fw-semibold">{{ __('messages.rest_places') ?? 'Rest Places' }}</label>
                            <small class="text-muted d-block mb-2">
                                {{ __('messages.rest_places_help', ['days' => $coachingCabine->validity_days - 1]) ?? 'Add rest places from Day 1 to Day ' . ($coachingCabine->validity_days - 1) . ' (maximum ' . ($coachingCabine->validity_days - 1) . ' rest places)' }}
                            </small>
                            {{-- Hidden input to ensure rest_places is always in the request --}}
                            <input type="hidden" name="rest_places_sent" value="1">
                            <div id="rest-places-container-edit" data-max-places="{{ $coachingCabine->validity_days - 1 }}">
                                @php
                                    $restPlaces = old('rest_places', $coachingCabine->rest_places ?? []);
                                    $currentCount = count($restPlaces);
                                @endphp
                                @if($currentCount > 0)
                                    @foreach($restPlaces as $i => $place)
                                        <div class="rest-place-item mb-2" data-index="{{ $i }}">
                                            <div class="input-group input-group-sm">
                                                <span class="input-group-text">{{ __('messages.day') ?? 'Day' }} {{ $i + 1 }}</span>
                                                <input type="text" 
                                                       name="rest_places[]" 
                                                       class="form-control rest-place-input" 
                                                       value="{{ $place }}"
                                                       placeholder="{{ __('messages.rest_place_placeholder') ?? 'Enter city or village name' }}"
                                                       data-session-id="edit"
                                                       data-place-index="{{ $i }}">
                                                <button type="button" 
                                                        class="btn btn-outline-primary rest-place-search-btn" 
                                                        data-session-id="edit"
                                                        data-place-index="{{ $i }}"
                                                        title="{{ __('messages.search_rest_place_city') ?? 'Search city' }}">
                                                    <i class="bi bi-search"></i>
                                                </button>
                                                <button type="button" 
                                                        class="btn btn-outline-danger remove-rest-place-btn" 
                                                        data-session-id="edit"
                                                        title="{{ __('messages.remove_rest_place') ?? 'Remove' }}">
                                                    <i class="bi bi-trash"></i>
                                                </button>
                                            </div>
                                            <div class="rest-place-error text-danger small mt-1 d-none" id="rest-place-error-edit-{{ $i }}"></div>
                                            <div class="rest-place-map-container mt-2" id="rest-place-map-edit-{{ $i }}" style="height: 200px; width: 100%; background: #f5f5f5; display: none;"></div>
                                        </div>
                                    @endforeach
                                @endif
                            </div>
                            <button type="button" 
                                    class="btn btn-outline-success btn-sm mt-2 add-rest-place-btn" 
                                    data-session-id="edit"
                                    data-max-places="{{ $coachingCabine->validity_days - 1 }}"
                                    @if($currentCount >= ($coachingCabine->validity_days - 1)) style="display: none;" @endif>
                                <i class="bi bi-plus-circle me-1"></i> {{ __('messages.add_rest_place') ?? 'Add Rest Place' }}
                            </button>
                            @error('rest_places')
                                <div class="text-danger small mt-1">{{ $message }}</div>
                            @enderror
                            @error('rest_places.*')
                                <div class="text-danger small mt-1">{{ $message }}</div>
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

    <!-- Leaflet CSS and JS -->
    <link
        rel="stylesheet"
        href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css"
        integrity="sha256-p4NxAoJBhIIN+hmNHrzRCf9tD/miZyoHS5obTRR9BMY="
        crossorigin=""
    />
    <script
        src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"
        integrity="sha256-20nQCchB9co0qIjJZRGuk2/Z9VM+kNiyxNV1lvTlZBo="
        crossorigin=""
    ></script>

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function () {
        // Wait for Leaflet to load
        function waitForLeaflet(callback) {
            if (window.L) {
                callback();
            } else {
                setTimeout(function() {
                    waitForLeaflet(callback);
                }, 100);
            }
        }

        waitForLeaflet(function() {
            if (!window.L) {
                console.error('Leaflet library not loaded');
                return;
            }

            const defaultCenter = [33.5731, -7.5898];
            let fromMap = null;
            let fromMarker = null;
            let fromLat = null;
            let fromLng = null;

            let toMap = null;
            let toMarker = null;
            let toLat = null;
            let toLng = null;

            // Helper function to set from coordinates
            function setFromCoordinates(lat, lng, centerMap = true) {
                fromLat = lat;
                fromLng = lng;

                const hiddenLat = document.getElementById('from_latitude');
                const hiddenLng = document.getElementById('from_longitude');
                if (hiddenLat) hiddenLat.value = lat.toFixed(6);
                if (hiddenLng) hiddenLng.value = lng.toFixed(6);

                const latInput = document.getElementById('from_lat_input');
                const lngInput = document.getElementById('from_lng_input');
                if (latInput) latInput.value = lat.toFixed(6);
                if (lngInput) lngInput.value = lng.toFixed(6);

                const formLabel = document.getElementById('from-coords-label');
                if (formLabel) {
                    const locationName = document.getElementById('from_location_name')?.value || '';
                    const coordsLabel = @json(__('messages.location_coords_label') ?? 'Coordinates');
                    if (locationName) {
                        formLabel.innerHTML = '<span class="fw-semibold">' + locationName + '</span><br><small class="text-muted">' + coordsLabel + ': ' + lat.toFixed(6) + ', ' + lng.toFixed(6) + '</small>';
                    } else {
                        formLabel.innerHTML = coordsLabel + ': <span class="fw-semibold">' + lat.toFixed(6) + ', ' + lng.toFixed(6) + '</span>';
                    }
                }

                if (fromMap) {
                    if (fromMarker) {
                        fromMarker.setLatLng([lat, lng]);
                    } else {
                        fromMarker = L.marker([lat, lng], { draggable: true }).addTo(fromMap);
                        fromMarker.on('dragend', function() {
                            const pos = fromMarker.getLatLng();
                            setFromCoordinates(pos.lat, pos.lng, false);
                        });
                    }
                    if (centerMap) {
                        fromMap.setView([lat, lng], 13);
                    }
                }
            }

            // Helper function to set to coordinates
            function setToCoordinates(lat, lng, centerMap = true) {
                toLat = lat;
                toLng = lng;

                const hiddenLat = document.getElementById('to_latitude');
                const hiddenLng = document.getElementById('to_longitude');
                if (hiddenLat) hiddenLat.value = lat.toFixed(6);
                if (hiddenLng) hiddenLng.value = lng.toFixed(6);

                const latInput = document.getElementById('to_lat_input');
                const lngInput = document.getElementById('to_lng_input');
                if (latInput) latInput.value = lat.toFixed(6);
                if (lngInput) lngInput.value = lng.toFixed(6);

                const formLabel = document.getElementById('to-coords-label');
                if (formLabel) {
                    const locationName = document.getElementById('to_location_name')?.value || '';
                    const coordsLabel = @json(__('messages.location_coords_label') ?? 'Coordinates');
                    if (locationName) {
                        formLabel.innerHTML = '<span class="fw-semibold">' + locationName + '</span><br><small class="text-muted">' + coordsLabel + ': ' + lat.toFixed(6) + ', ' + lng.toFixed(6) + '</small>';
                    } else {
                        formLabel.innerHTML = coordsLabel + ': <span class="fw-semibold">' + lat.toFixed(6) + ', ' + lng.toFixed(6) + '</span>';
                    }
                }

                if (toMap) {
                    if (toMarker) {
                        toMarker.setLatLng([lat, lng]);
                    } else {
                        toMarker = L.marker([lat, lng], { draggable: true }).addTo(toMap);
                        toMarker.on('dragend', function() {
                            const pos = toMarker.getLatLng();
                            setToCoordinates(pos.lat, pos.lng, false);
                        });
                    }
                    if (centerMap) {
                        toMap.setView([lat, lng], 13);
                    }
                }
            }

            // Geocoding function using Nominatim
            async function geocodeQuery(query) {
                const url = 'https://nominatim.openstreetmap.org/search?format=json&limit=1&q=' + encodeURIComponent(query);
                const response = await fetch(url, {
                    headers: {
                        'Accept': 'application/json',
                        'User-Agent': 'GCV Coaching System'
                    }
                });
                if (!response.ok) {
                    throw new Error('Geocoding request failed');
                }
                const data = await response.json();
                if (!Array.isArray(data) || data.length === 0) {
                    throw new Error('No results found');
                }
                const first = data[0];
                const lat = parseFloat(first.lat);
                const lon = parseFloat(first.lon);
                if (Number.isNaN(lat) || Number.isNaN(lon)) {
                    throw new Error('Invalid coordinates in response');
                }
                
                // Extract short city/village name from address components
                let shortName = query; // Default to search query
                if (first.address) {
                    // Try to get city, town, or village name
                    shortName = first.address.city || 
                               first.address.town || 
                               first.address.village || 
                               first.address.municipality ||
                               first.address.county ||
                               query;
                } else {
                    // Fallback: extract first part of display_name (before first comma)
                    const displayName = first.display_name || '';
                    const parts = displayName.split(',');
                    if (parts.length > 0) {
                        shortName = parts[0].trim();
                    }
                }
                
                return { lat, lng: lon, display_name: shortName };
            }

            // Handle geocoding search for From Location
            async function handleFromCitySearch() {
                const input = document.getElementById('from_city_search_input');
                const errorEl = document.getElementById('from_city_search_error');
                if (errorEl) {
                    errorEl.classList.add('d-none');
                    errorEl.textContent = '';
                }
                if (!input) return;
                const query = input.value.trim();
                if (!query) {
                    if (errorEl) {
                        errorEl.textContent = '{{ __('messages.please_enter_city_name') ?? 'Please enter a city or village name' }}';
                        errorEl.classList.remove('d-none');
                    }
                    return;
                }

                const searchBtn = document.getElementById('from_city_search_btn');
                const originalText = searchBtn.innerHTML;
                searchBtn.disabled = true;
                searchBtn.innerHTML = '<span class="spinner-border spinner-border-sm me-1"></span>{{ __('messages.searching') ?? 'Searching...' }}';

                try {
                    const { lat, lng, display_name } = await geocodeQuery(query);
                    setFromCoordinates(lat, lng, true);
                    // Update location name input with the found location name
                    const locationNameInput = document.getElementById('from_location_name_input');
                    if (locationNameInput) {
                        locationNameInput.value = display_name;
                    }
                    // Update hidden location name field
                    const hiddenLocationName = document.getElementById('from_location_name');
                    if (hiddenLocationName) {
                        hiddenLocationName.value = display_name;
                    }
                    // Clear search input
                    input.value = '';
                    if (errorEl) {
                        errorEl.classList.add('d-none');
                    }
                } catch (err) {
                    if (errorEl) {
                        errorEl.textContent = err.message || '{{ __('messages.unable_to_find_location') ?? 'Unable to find this location' }}';
                        errorEl.classList.remove('d-none');
                    }
                } finally {
                    searchBtn.disabled = false;
                    searchBtn.innerHTML = originalText;
                }
            }

            // Handle geocoding search for To Location
            async function handleToCitySearch() {
                const input = document.getElementById('to_city_search_input');
                const errorEl = document.getElementById('to_city_search_error');
                if (errorEl) {
                    errorEl.classList.add('d-none');
                    errorEl.textContent = '';
                }
                if (!input) return;
                const query = input.value.trim();
                if (!query) {
                    if (errorEl) {
                        errorEl.textContent = '{{ __('messages.please_enter_city_name') ?? 'Please enter a city or village name' }}';
                        errorEl.classList.remove('d-none');
                    }
                    return;
                }

                const searchBtn = document.getElementById('to_city_search_btn');
                const originalText = searchBtn.innerHTML;
                searchBtn.disabled = true;
                searchBtn.innerHTML = '<span class="spinner-border spinner-border-sm me-1"></span>{{ __('messages.searching') ?? 'Searching...' }}';

                try {
                    const { lat, lng, display_name } = await geocodeQuery(query);
                    setToCoordinates(lat, lng, true);
                    // Update location name input with the found location name
                    const locationNameInput = document.getElementById('to_location_name_input');
                    if (locationNameInput) {
                        locationNameInput.value = display_name;
                    }
                    // Update hidden location name field
                    const hiddenLocationName = document.getElementById('to_location_name');
                    if (hiddenLocationName) {
                        hiddenLocationName.value = display_name;
                    }
                    // Clear search input
                    input.value = '';
                    if (errorEl) {
                        errorEl.classList.add('d-none');
                    }
                } catch (err) {
                    if (errorEl) {
                        errorEl.textContent = err.message || '{{ __('messages.unable_to_find_location') ?? 'Unable to find this location' }}';
                        errorEl.classList.remove('d-none');
                    }
                } finally {
                    searchBtn.disabled = false;
                    searchBtn.innerHTML = originalText;
                }
            }

            // Event listeners for search buttons
            document.getElementById('from_city_search_btn')?.addEventListener('click', handleFromCitySearch);
            document.getElementById('to_city_search_btn')?.addEventListener('click', handleToCitySearch);

            // Allow Enter key to trigger search
            document.getElementById('from_city_search_input')?.addEventListener('keypress', function(e) {
                if (e.key === 'Enter') {
                    e.preventDefault();
                    handleFromCitySearch();
                }
            });
            document.getElementById('to_city_search_input')?.addEventListener('keypress', function(e) {
                if (e.key === 'Enter') {
                    e.preventDefault();
                    handleToCitySearch();
                }
            });

            // Initialize From Location Map
            const fromLocationModal = document.getElementById('fromLocationModal');
            if (fromLocationModal) {
                fromLocationModal.addEventListener('shown.bs.modal', function () {
                    const mapContainer = document.getElementById('from-location-map');
                    if (!mapContainer) {
                        console.error('From location map container not found');
                        return;
                    }

                    if (!fromMap) {
                        fromMap = L.map(mapContainer).setView(defaultCenter, 7);
                        L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
                            maxZoom: 19,
                            attribution: '&copy; OpenStreetMap contributors',
                        }).addTo(fromMap);

                        // Load existing coordinates if any
                        const existingLat = document.getElementById('from_latitude')?.value;
                        const existingLng = document.getElementById('from_longitude')?.value;
                        const existingName = document.getElementById('from_location_name')?.value;
                        if (existingLat && existingLng) {
                            const lat = parseFloat(existingLat);
                            const lng = parseFloat(existingLng);
                            if (!isNaN(lat) && !isNaN(lng)) {
                                setFromCoordinates(lat, lng, true);
                            }
                        }
                        // Load existing location name
                        const locationNameInput = document.getElementById('from_location_name_input');
                        if (locationNameInput && existingName) {
                            locationNameInput.value = existingName;
                        }

                        fromMap.on('click', function (e) {
                            setFromCoordinates(e.latlng.lat, e.latlng.lng, false);
                        });
                    }
                    
                    // Always invalidate size when modal is shown
                    setTimeout(function() {
                        if (fromMap) {
                            fromMap.invalidateSize();
                        }
                    }, 100);
                    setTimeout(function() {
                        if (fromMap) {
                            fromMap.invalidateSize();
                        }
                    }, 300);
                });
            }

            // Initialize To Location Map
            const toLocationModal = document.getElementById('toLocationModal');
            if (toLocationModal) {
                toLocationModal.addEventListener('shown.bs.modal', function () {
                    const mapContainer = document.getElementById('to-location-map');
                    if (!mapContainer) {
                        console.error('To location map container not found');
                        return;
                    }

                    if (!toMap) {
                        toMap = L.map(mapContainer).setView(defaultCenter, 7);
                        L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
                            maxZoom: 19,
                            attribution: '&copy; OpenStreetMap contributors',
                        }).addTo(toMap);

                        // Load existing coordinates if any
                        const existingLat = document.getElementById('to_latitude')?.value;
                        const existingLng = document.getElementById('to_longitude')?.value;
                        const existingName = document.getElementById('to_location_name')?.value;
                        if (existingLat && existingLng) {
                            const lat = parseFloat(existingLat);
                            const lng = parseFloat(existingLng);
                            if (!isNaN(lat) && !isNaN(lng)) {
                                setToCoordinates(lat, lng, true);
                            }
                        }
                        // Load existing location name
                        const locationNameInput = document.getElementById('to_location_name_input');
                        if (locationNameInput && existingName) {
                            locationNameInput.value = existingName;
                        }

                        toMap.on('click', function (e) {
                            setToCoordinates(e.latlng.lat, e.latlng.lng, false);
                        });
                    }
                    
                    // Always invalidate size when modal is shown
                    setTimeout(function() {
                        if (toMap) {
                            toMap.invalidateSize();
                        }
                    }, 100);
                    setTimeout(function() {
                        if (toMap) {
                            toMap.invalidateSize();
                        }
                    }, 300);
                });
            }

            // Confirm From Location
            document.getElementById('applyFromLocation')?.addEventListener('click', function () {
                if (fromLat !== null && fromLng !== null) {
                    setFromCoordinates(fromLat, fromLng, false);
                    // Save location name
                    const locationNameInput = document.getElementById('from_location_name_input');
                    const hiddenLocationName = document.getElementById('from_location_name');
                    if (locationNameInput && hiddenLocationName) {
                        hiddenLocationName.value = locationNameInput.value;
                    }
                }
                const modalInstance = bootstrap.Modal.getInstance(fromLocationModal);
                if (modalInstance) {
                    modalInstance.hide();
                }
            });

            // Confirm To Location
            document.getElementById('applyToLocation')?.addEventListener('click', function () {
                if (toLat !== null && toLng !== null) {
                    setToCoordinates(toLat, toLng, false);
                    // Save location name
                    const locationNameInput = document.getElementById('to_location_name_input');
                    const hiddenLocationName = document.getElementById('to_location_name');
                    if (locationNameInput && hiddenLocationName) {
                        hiddenLocationName.value = locationNameInput.value;
                    }
                }
                const modalInstance = bootstrap.Modal.getInstance(toLocationModal);
                if (modalInstance) {
                    modalInstance.hide();
                }
            });

            // Sync input changes to coordinates
            document.getElementById('from_lat_input')?.addEventListener('change', function() {
                const lat = parseFloat(this.value);
                const lng = parseFloat(document.getElementById('from_lng_input')?.value || fromLng || defaultCenter[1]);
                if (!isNaN(lat) && !isNaN(lng)) {
                    setFromCoordinates(lat, lng, false);
                }
            });

            document.getElementById('from_lng_input')?.addEventListener('change', function() {
                const lat = parseFloat(document.getElementById('from_lat_input')?.value || fromLat || defaultCenter[0]);
                const lng = parseFloat(this.value);
                if (!isNaN(lat) && !isNaN(lng)) {
                    setFromCoordinates(lat, lng, false);
                }
            });

            document.getElementById('to_lat_input')?.addEventListener('change', function() {
                const lat = parseFloat(this.value);
                const lng = parseFloat(document.getElementById('to_lng_input')?.value || toLng || defaultCenter[1]);
                if (!isNaN(lat) && !isNaN(lng)) {
                    setToCoordinates(lat, lng, false);
                }
            });

            document.getElementById('to_lng_input')?.addEventListener('change', function() {
                const lat = parseFloat(document.getElementById('to_lat_input')?.value || toLat || defaultCenter[0]);
                const lng = parseFloat(this.value);
                if (!isNaN(lat) && !isNaN(lng)) {
                    setToCoordinates(lat, lng, false);
                }
            });
        });

        // Rest Places Geocoding
        async function geocodeRestPlace(query) {
            const url = 'https://nominatim.openstreetmap.org/search?format=json&limit=1&q=' + encodeURIComponent(query);
            const response = await fetch(url, {
                headers: {
                    'Accept': 'application/json',
                    'User-Agent': 'GCV Coaching System'
                }
            });
            if (!response.ok) {
                throw new Error('Geocoding request failed');
            }
            const data = await response.json();
            if (!Array.isArray(data) || data.length === 0) {
                throw new Error('No results found');
            }
            const first = data[0];
            
            let shortName = query;
            if (first.address) {
                shortName = first.address.city || 
                           first.address.town || 
                           first.address.village || 
                           first.address.municipality ||
                           first.address.county ||
                           query;
            } else {
                const displayName = first.display_name || '';
                const parts = displayName.split(',');
                if (parts.length > 0) {
                    shortName = parts[0].trim();
                }
            }
            
            return {
                name: shortName,
                lat: parseFloat(first.lat),
                lng: parseFloat(first.lon),
                displayName: first.display_name || shortName
            };
        }

        // Initialize map for rest place
        function initRestPlaceMap(containerId, lat, lng, placeName) {
            // Clear any existing timeout for this container
            if (window._restPlaceMapTimeouts && window._restPlaceMapTimeouts[containerId]) {
                clearTimeout(window._restPlaceMapTimeouts[containerId]);
                delete window._restPlaceMapTimeouts[containerId];
            }
            if (!window._restPlaceMapTimeouts) {
                window._restPlaceMapTimeouts = {};
            }

            function waitForLeaflet(callback) {
                if (window.L && window.L.map) {
                    callback();
                } else {
                    setTimeout(function() {
                        waitForLeaflet(callback);
                    }, 100);
                }
            }

            waitForLeaflet(function() {
                if (!window.L || !window.L.map) {
                    console.error('Leaflet not loaded');
                    return null;
                }

                const mapContainer = document.getElementById(containerId);
                if (!mapContainer) {
                    console.error('Map container not found:', containerId);
                    return null;
                }

                // Wait for container to be visible (important for modals)
                function initMapWhenVisible() {
                    // Check if container still exists
                    const container = document.getElementById(containerId);
                    if (!container) {
                        return; // Container was removed, abort
                    }

                    // Check if container is visible
                    const isVisible = container.offsetWidth > 0 && container.offsetHeight > 0;
                    if (!isVisible) {
                        // Container not visible yet, try again after a short delay
                        const timeoutId = setTimeout(initMapWhenVisible, 200);
                        window._restPlaceMapTimeouts[containerId] = timeoutId;
                        return;
                    }

                    // Double-check container still exists before proceeding
                    const containerCheck = document.getElementById(containerId);
                    if (!containerCheck || containerCheck !== container) {
                        return; // Container changed or removed, abort
                    }

                    // Remove existing map if any
                    if (container._leaflet_id) {
                        try {
                            const existingMap = L.Map.prototype.getContainer.call({ _container: container });
                            if (existingMap && existingMap.remove) {
                                existingMap.remove();
                            }
                        } catch (e) {
                            // Ignore errors when removing
                        }
                        container._leaflet_id = null;
                    }
                    container.innerHTML = '';

                    // Validate coordinates
                    if (isNaN(lat) || isNaN(lng) || lat < -90 || lat > 90 || lng < -180 || lng > 180) {
                        console.error('Invalid coordinates:', lat, lng);
                        return;
                    }

                    // Final check before initializing map
                    const finalContainer = document.getElementById(containerId);
                    if (!finalContainer || finalContainer !== container) {
                        return; // Container changed, abort
                    }

                    try {
                        // Initialize map with center and zoom first
                        const map = L.map(containerId, {
                            center: [lat, lng],
                            zoom: 10
                        });

                        // Add tile layer
                        L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
                            maxZoom: 19,
                            attribution: '&copy; OpenStreetMap contributors',
                        }).addTo(map);

                        // Add marker
                        const marker = L.marker([lat, lng]).addTo(map);
                        marker.bindPopup('<strong>' + placeName + '</strong><br><small>' + lat.toFixed(6) + ', ' + lng.toFixed(6) + '</small>').openPopup();

                        // Fix rendering after map is created
                        setTimeout(() => {
                            try {
                                const mapCheck = document.getElementById(containerId);
                                if (mapCheck && mapCheck._leaflet_id) {
                                    map.invalidateSize();
                                }
                            } catch (e) {
                                console.warn('Error invalidating map size:', e);
                            }
                        }, 300);
                    } catch (error) {
                        console.error('Error initializing map:', error);
                    }
                }

                initMapWhenVisible();
            });
        }

        // Handle rest place search
        document.querySelectorAll('.rest-place-search-btn').forEach(btn => {
            btn.addEventListener('click', async function() {
                const sessionId = this.getAttribute('data-session-id');
                const placeIndex = this.getAttribute('data-place-index');
                const input = document.querySelector(`input[data-session-id="${sessionId}"][data-place-index="${placeIndex}"]`);
                const errorEl = document.getElementById(`rest-place-error-${sessionId}-${placeIndex}`);
                
                if (!input) return;
                
                const query = input.value.trim();
                if (!query) {
                    if (errorEl) {
                        errorEl.textContent = '{{ __('messages.please_enter_city_name') ?? 'Please enter a city or village name' }}';
                        errorEl.classList.remove('d-none');
                    }
                    return;
                }

                const originalHtml = this.innerHTML;
                this.disabled = true;
                this.innerHTML = '<span class="spinner-border spinner-border-sm"></span>';

                if (errorEl) {
                    errorEl.classList.add('d-none');
                    errorEl.textContent = '';
                }

                try {
                    const result = await geocodeRestPlace(query);
                    input.value = result.name;
                    
                    // Show map with location
                    const mapContainerId = `rest-place-map-${sessionId}-${placeIndex}`;
                    const mapContainer = document.getElementById(mapContainerId);
                    if (mapContainer) {
                        mapContainer.style.display = 'block';
                        initRestPlaceMap(mapContainerId, result.lat, result.lng, result.name);
                    }
                    if (errorEl) {
                        errorEl.classList.add('d-none');
                    }
                } catch (err) {
                    if (errorEl) {
                        errorEl.textContent = err.message || '{{ __('messages.unable_to_find_location') ?? 'Unable to find this location' }}';
                        errorEl.classList.remove('d-none');
                    }
                } finally {
                    this.disabled = false;
                    this.innerHTML = originalHtml;
                }
            });
        });

        // Function to update add button visibility
        function updateAddRestPlaceButton(sessionId) {
            const btn = document.querySelector(`.add-rest-place-btn[data-session-id="${sessionId}"]`);
            if (!btn) return;
            
            const maxPlaces = parseInt(btn.getAttribute('data-max-places')) || 0;
            const container = document.getElementById(`rest-places-container-${sessionId}`);
            if (!container) return;
            
            const currentItems = container.querySelectorAll('.rest-place-item');
            const currentCount = currentItems.length;
            
            if (currentCount >= maxPlaces) {
                btn.style.display = 'none';
            } else {
                btn.style.display = 'inline-block';
            }
        }

        // Handle add rest place button
        document.querySelectorAll('.add-rest-place-btn').forEach(btn => {
            btn.addEventListener('click', function() {
                const sessionId = this.getAttribute('data-session-id');
                const container = document.getElementById(`rest-places-container-${sessionId}`);
                if (!container) return;
                
                const maxPlaces = parseInt(container.getAttribute('data-max-places')) || 0;
                const currentItems = container.querySelectorAll('.rest-place-item');
                const currentCount = currentItems.length;
                
                if (currentCount >= maxPlaces) {
                    alert('{{ __('messages.rest_places_max_reached') ?? 'Maximum number of rest places reached' }}');
                    return;
                }
                
                const newIndex = currentCount;
                const dayNumber = newIndex + 1;
                
                const newItem = document.createElement('div');
                newItem.className = 'rest-place-item mb-2';
                newItem.setAttribute('data-index', newIndex);
                newItem.innerHTML = `
                    <div class="input-group input-group-sm">
                        <span class="input-group-text">{{ __('messages.day') ?? 'Day' }} ${dayNumber}</span>
                        <input type="text" 
                               name="rest_places[]" 
                               class="form-control rest-place-input" 
                               value=""
                               placeholder="{{ __('messages.rest_place_placeholder') ?? 'Enter city or village name' }}"
                               data-session-id="${sessionId}"
                               data-place-index="${newIndex}">
                        <button type="button" 
                                class="btn btn-outline-primary rest-place-search-btn" 
                                data-session-id="${sessionId}"
                                data-place-index="${newIndex}"
                                title="{{ __('messages.search_rest_place_city') ?? 'Search city' }}">
                            <i class="bi bi-search"></i>
                        </button>
                        <button type="button" 
                                class="btn btn-outline-danger remove-rest-place-btn" 
                                data-session-id="${sessionId}"
                                title="{{ __('messages.remove_rest_place') ?? 'Remove' }}">
                            <i class="bi bi-trash"></i>
                        </button>
                    </div>
                        <div class="rest-place-error text-danger small mt-1 d-none" id="rest-place-error-${sessionId}-${newIndex}"></div>
                        <div class="rest-place-map-container mt-2" id="rest-place-map-${sessionId}-${newIndex}" style="height: 200px; width: 100%; background: #f5f5f5; display: none;"></div>
                    `;
                
                container.appendChild(newItem);
                
                // Attach event listeners to new elements
                const newSearchBtn = newItem.querySelector('.rest-place-search-btn');
                if (newSearchBtn) {
                    newSearchBtn.addEventListener('click', async function() {
                        const placeIndex = this.getAttribute('data-place-index');
                        const input = document.querySelector(`input[data-session-id="${sessionId}"][data-place-index="${placeIndex}"]`);
                        const errorEl = document.getElementById(`rest-place-error-${sessionId}-${placeIndex}`);
                        
                        if (!input) return;
                        
                        const query = input.value.trim();
                        if (!query) {
                            if (errorEl) {
                                errorEl.textContent = '{{ __('messages.please_enter_city_name') ?? 'Please enter a city or village name' }}';
                                errorEl.classList.remove('d-none');
                            }
                            return;
                        }

                        const originalHtml = this.innerHTML;
                        this.disabled = true;
                        this.innerHTML = '<span class="spinner-border spinner-border-sm"></span>';

                        if (errorEl) {
                            errorEl.classList.add('d-none');
                            errorEl.textContent = '';
                        }

                        try {
                    const result = await geocodeRestPlace(query);
                    input.value = result.name;
                    
                    // Show map with location
                    const mapContainerId = `rest-place-map-${sessionId}-${placeIndex}`;
                    const mapContainer = document.getElementById(mapContainerId);
                    if (mapContainer) {
                        mapContainer.style.display = 'block';
                        initRestPlaceMap(mapContainerId, result.lat, result.lng, result.name);
                    }
                            if (errorEl) {
                                errorEl.classList.add('d-none');
                            }
                        } catch (err) {
                            if (errorEl) {
                                errorEl.textContent = err.message || '{{ __('messages.unable_to_find_location') ?? 'Unable to find this location' }}';
                                errorEl.classList.remove('d-none');
                            }
                        } finally {
                            this.disabled = false;
                            this.innerHTML = originalHtml;
                        }
                    });
                }
                
                const newRemoveBtn = newItem.querySelector('.remove-rest-place-btn');
                if (newRemoveBtn) {
                    newRemoveBtn.addEventListener('click', function() {
                        const item = this.closest('.rest-place-item');
                        if (item) {
                            item.remove();
                            updateDayLabels(sessionId);
                            updateAddRestPlaceButton(sessionId);
                        }
                    });
                }
                
                updateDayLabels(sessionId);
                updateAddRestPlaceButton(sessionId);
            });
        });

        // Handle remove rest place (using event delegation)
        document.addEventListener('click', function(e) {
            if (e.target.closest('.remove-rest-place-btn')) {
                const btn = e.target.closest('.remove-rest-place-btn');
                const sessionId = btn.getAttribute('data-session-id');
                const item = btn.closest('.rest-place-item');
                if (item) {
                    item.remove();
                    updateDayLabels(sessionId);
                    updateAddRestPlaceButton(sessionId);
                }
            }
        });

        // Update day labels after removal
        function updateDayLabels(sessionId) {
            const container = document.getElementById(`rest-places-container-${sessionId}`);
            if (!container) return;
            
            const items = container.querySelectorAll('.rest-place-item');
            items.forEach((item, index) => {
                const label = item.querySelector('.input-group-text');
                if (label) {
                    label.textContent = '{{ __('messages.day') ?? 'Day' }} ' + (index + 1);
                }
                const input = item.querySelector('input');
                if (input) {
                    input.setAttribute('data-place-index', index);
                }
                const searchBtn = item.querySelector('.rest-place-search-btn');
                if (searchBtn) {
                    searchBtn.setAttribute('data-place-index', index);
                }
                const errorEl = item.querySelector('.rest-place-error');
                if (errorEl) {
                    errorEl.id = `rest-place-error-${sessionId}-${index}`;
                }
            });
        }
    });
</script>
@endpush

</x-app-layout>
