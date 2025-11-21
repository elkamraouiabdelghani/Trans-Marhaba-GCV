<x-app-layout>
    <x-slot name="header">
        @include('layouts.topnav')
    </x-slot>

    <!-- Toast Container -->
    <div class="toast-container position-fixed top-0 start-50 translate-middle-x p-3" style="z-index: 1055;">
        @if(session('success'))
            <div class="toast show" role="alert" aria-live="assertive" aria-atomic="true" data-bs-autohide="true" data-bs-delay="5000">
                <div class="toast-header bg-success text-white">
                    <i class="bi bi-check-circle me-2"></i>
                    <strong class="me-auto">{{ __('messages.success') }}</strong>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="toast" aria-label="Close"></button>
                </div>
                <div class="toast-body">
                    {{ session('success') }}
                </div>
            </div>
        @endif

        @if(session('error'))
            <div class="toast show" role="alert" aria-live="assertive" aria-atomic="true" data-bs-autohide="true" data-bs-delay="5000">
                <div class="toast-header bg-danger text-white">
                    <i class="bi bi-exclamation-triangle me-2"></i>
                    <strong class="me-auto">{{ __('messages.error') }}</strong>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="toast" aria-label="Close"></button>
                </div>
                <div class="toast-body">
                    {{ session('error') }}
                </div>
            </div>
        @endif
    </div>

    <div class="container-fluid py-4 mt-4">
        <div class="row justify-content-center">
            <div class="col-lg-10">
                <div class="card border-0 shadow-sm">
                    <div class="card-header bg-white border-0 py-3">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <h5 class="mb-0 text-dark fw-bold">
                                    <i class="bi bi-plus-circle me-2 text-primary"></i>
                                    {{ __('messages.new_changement') }} - {{ __('messages.step_1_identification') }}
                                </h5>
                                <small class="text-muted">{{ __('messages.changements_create_subtitle') }}</small>
                            </div>
                            <a href="{{ route('changements.index') }}" class="btn btn-outline-secondary btn-sm">
                                <i class="bi bi-x-circle me-1"></i>
                                {{ __('messages.cancel') }}
                            </a>
                        </div>
                    </div>
                    <div class="card-body">
                        <form action="{{ route('changements.store') }}" method="POST">
                            @csrf

                            <div class="row g-3">
                                <!-- Changement Type -->
                                <div class="col-md-6">
                                    <label for="changement_type_id" class="form-label">
                                        {{ __('messages.changement_type') }} <span class="text-danger">*</span>
                                    </label>
                                    <select class="form-select @error('changement_type_id') is-invalid @enderror" 
                                            id="changement_type_id" 
                                            name="changement_type_id" 
                                            required>
                                        <option value="">{{ __('messages.select_option') }}</option>
                                        <option value="other">{{ __('messages.other') }}</option>
                                        @foreach($changementTypes as $type)
                                            <option value="{{ $type->id }}" {{ old('changement_type_id') == $type->id ? 'selected' : '' }}>
                                                {{ $type->name }}
                                            </option>
                                        @endforeach
                                    </select>
                                    @error('changement_type_id')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>

                                <!-- Subject Type -->
                                <div class="col-md-6">
                                    <label for="subject_type" class="form-label">
                                        {{ __('messages.subject_type') }}
                                    </label>
                                    <select class="form-select @error('subject_type') is-invalid @enderror" 
                                            id="subject_type" 
                                            name="subject_type">
                                        <option value="">{{ __('messages.select_option') }}</option>
                                        <option value="driver" {{ old('subject_type') === 'driver' ? 'selected' : '' }}>
                                            {{ __('messages.driver') }}
                                        </option>
                                        <option value="administrative" {{ old('subject_type') === 'administrative' ? 'selected' : '' }}>
                                            {{ __('messages.administrative_user') }}
                                        </option>
                                    </select>
                                    @error('subject_type')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                    <small class="form-text text-muted">{{ __('messages.subject_type_help') }}</small>
                                </div>

                                <!-- Hidden input for subject_id -->
                                <input type="hidden" name="subject_id" id="subject_id" value="{{ old('subject_id') }}">

                                <!-- Subject Selection (Driver) -->
                                <div class="col-md-6" id="driver_select_container" style="display: none;">
                                    <label for="subject_id_driver" class="form-label">
                                        {{ __('messages.select_driver') }}
                                    </label>
                                    <select class="form-select @error('subject_id') is-invalid @enderror" 
                                            id="subject_id_driver">
                                        <option value="">{{ __('messages.select_option') }}</option>
                                        @foreach($drivers as $driver)
                                            <option value="{{ $driver->id }}" {{ old('subject_id') == $driver->id && old('subject_type') === 'driver' ? 'selected' : '' }}>
                                                {{ $driver->full_name }} 
                                                @if($driver->license_number)
                                                    ({{ $driver->license_number }})
                                                @endif
                                            </option>
                                        @endforeach
                                    </select>
                                    @error('subject_id')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>

                                <!-- Subject Selection (Administrative User) -->
                                <div class="col-md-6" id="administrative_select_container" style="display: none;">
                                    <label for="subject_id_administrative" class="form-label">
                                        {{ __('messages.select_administrative_user') }}
                                    </label>
                                    <select class="form-select @error('subject_id') is-invalid @enderror" 
                                            id="subject_id_administrative">
                                        <option value="">{{ __('messages.select_option') }}</option>
                                        @foreach($users as $user)
                                            <option value="{{ $user->id }}" {{ old('subject_id') == $user->id && old('subject_type') === 'administrative' ? 'selected' : '' }}>
                                                {{ $user->name }} 
                                                @if($user->email)
                                                    ({{ $user->email }})
                                                @endif
                                            </option>
                                        @endforeach
                                    </select>
                                    @error('subject_id')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>

                                <!-- Hidden input for replacement_type (auto-set based on subject_type) -->
                                <input type="hidden" name="replacement_type" id="replacement_type" value="{{ old('replacement_type') }}">

                                <!-- Hidden input for replacement_id -->
                                <input type="hidden" name="replacement_id" id="replacement_id" value="{{ old('replacement_id') }}">

                                <!-- Replacement Selection (Driver) -->
                                <div class="col-md-6" id="replacement_driver_select_container" style="display: none;">
                                    <label for="replacement_id_driver" class="form-label">
                                        {{ __('messages.select_replacement_driver') }}
                                    </label>
                                    <select class="form-select @error('replacement_id') is-invalid @enderror" 
                                            id="replacement_id_driver">
                                        <option value="">{{ __('messages.select_option') }}</option>
                                        @foreach($drivers as $driver)
                                            <option value="{{ $driver->id }}" {{ old('replacement_id') == $driver->id && old('replacement_type') === 'driver' ? 'selected' : '' }}>
                                                {{ $driver->full_name }} 
                                                @if($driver->license_number)
                                                    ({{ $driver->license_number }})
                                                @endif
                                            </option>
                                        @endforeach
                                    </select>
                                    @error('replacement_id')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                    <small class="form-text text-muted">{{ __('messages.replacement_help') }}</small>
                                </div>

                                <!-- Replacement Selection (Administrative User) -->
                                <div class="col-md-6" id="replacement_administrative_select_container" style="display: none;">
                                    <label for="replacement_id_administrative" class="form-label">
                                        {{ __('messages.select_replacement_administrative') }}
                                    </label>
                                    <select class="form-select @error('replacement_id') is-invalid @enderror" 
                                            id="replacement_id_administrative">
                                        <option value="">{{ __('messages.select_option') }}</option>
                                        @foreach($users as $user)
                                            <option value="{{ $user->id }}" {{ old('replacement_id') == $user->id && old('replacement_type') === 'administrative' ? 'selected' : '' }}>
                                                {{ $user->name }} 
                                                @if($user->email)
                                                    ({{ $user->email }})
                                                @endif
                                            </option>
                                        @endforeach
                                    </select>
                                    @error('replacement_id')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                    <small class="form-text text-muted">{{ __('messages.replacement_help') }}</small>
                                </div>

                                <!-- Date Changement -->
                                <div class="col-md-6">
                                    <label for="date_changement" class="form-label">
                                        {{ __('messages.date_changement') }} <span class="text-danger">*</span>
                                    </label>
                                    <input type="date" 
                                           class="form-control @error('date_changement') is-invalid @enderror" 
                                           id="date_changement" 
                                           name="date_changement" 
                                           value="{{ old('date_changement', date('Y-m-d')) }}" 
                                           required>
                                    @error('date_changement')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>

                                <!-- Responsable Changement -->
                                <div class="col-md-6">
                                    <label for="responsable_changement" class="form-label">
                                        {{ __('messages.responsable') }} <span class="text-danger">*</span>
                                    </label>
                                    <select class="form-select @error('responsable_changement') is-invalid @enderror" 
                                            id="responsable_changement" 
                                            name="responsable_changement" 
                                            required>
                                        <option value="">{{ __('messages.select_option') }}</option>
                                        <option value="RH" {{ old('responsable_changement') === 'RH' ? 'selected' : '' }}>RH</option>
                                        <option value="DGA" {{ old('responsable_changement') === 'DGA' ? 'selected' : '' }}>DGA</option>
                                        <option value="QHSE" {{ old('responsable_changement') === 'QHSE' ? 'selected' : '' }}>QHSE</option>
                                    </select>
                                    @error('responsable_changement')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>

                                <!-- Description Changement -->
                                <div class="col-12">
                                    <label for="description_changement" class="form-label">
                                        {{ __('messages.description_changement') }} <span class="text-danger">*</span>
                                    </label>
                                    <textarea class="form-control @error('description_changement') is-invalid @enderror" 
                                              id="description_changement" 
                                              name="description_changement" 
                                              rows="4" 
                                              required>{{ old('description_changement') }}</textarea>
                                    @error('description_changement')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                    <small class="form-text text-muted">{{ __('messages.description_changement_help') }}</small>
                                </div>

                                <!-- Impact -->
                                <div class="col-12">
                                    <label for="impact" class="form-label">
                                        {{ __('messages.impact') }}
                                    </label>
                                    <textarea class="form-control @error('impact') is-invalid @enderror" 
                                              id="impact" 
                                              name="impact" 
                                              rows="3">{{ old('impact') }}</textarea>
                                    @error('impact')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                    <small class="form-text text-muted">{{ __('messages.impact_help') }}</small>
                                </div>

                                <!-- Action -->
                                <div class="col-12">
                                    <label for="action" class="form-label">
                                        {{ __('messages.action') }}
                                    </label>
                                    <textarea class="form-control @error('action') is-invalid @enderror" 
                                              id="action" 
                                              name="action" 
                                              rows="3">{{ old('action') }}</textarea>
                                    @error('action')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                    <small class="form-text text-muted">{{ __('messages.action_help') }}</small>
                                </div>
                            </div>

                            <hr class="my-4">

                            <div class="d-flex justify-content-between">
                                <a href="{{ route('changements.index') }}" class="btn btn-outline-secondary">
                                    <i class="bi bi-arrow-left me-1"></i>
                                    {{ __('messages.cancel') }}
                                </a>
                                <button type="submit" class="btn btn-dark">
                                    {{ __('messages.save_and_continue') }}
                                    <i class="bi bi-arrow-right ms-1"></i>
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script>
        // Initialize and show toasts on page load
        document.addEventListener('DOMContentLoaded', function() {
            const toasts = document.querySelectorAll('.toast.show');
            toasts.forEach(function(toastEl) {
                if (typeof bootstrap !== 'undefined') {
                    const toast = new bootstrap.Toast(toastEl);
                    toast.show();
                }
            });

            // Handle "other" option in changement type dropdown
            const changementTypeSelect = document.getElementById('changement_type_id');
            if (changementTypeSelect) {
                changementTypeSelect.addEventListener('change', function() {
                    if (this.value === 'other') {
                        // Redirect to changement types index page
                        window.location.href = '{{ route("changement-types.index") }}';
                    }
                });
            }

            // Handle subject type selection
            const subjectTypeSelect = document.getElementById('subject_type');
            const driverSelectContainer = document.getElementById('driver_select_container');
            const administrativeSelectContainer = document.getElementById('administrative_select_container');
            const driverSelect = document.getElementById('subject_id_driver');
            const administrativeSelect = document.getElementById('subject_id_administrative');
            const subjectIdHidden = document.getElementById('subject_id');

            function updateSubjectSelectors() {
                const selectedType = subjectTypeSelect.value;
                
                // Hide both containers first
                driverSelectContainer.style.display = 'none';
                administrativeSelectContainer.style.display = 'none';
                
                // Clear both selects and hidden input
                if (driverSelect) driverSelect.value = '';
                if (administrativeSelect) administrativeSelect.value = '';
                if (subjectIdHidden) subjectIdHidden.value = '';
                
                // Show appropriate container based on selection
                if (selectedType === 'driver') {
                    driverSelectContainer.style.display = 'block';
                    if (driverSelect) driverSelect.required = true;
                    if (administrativeSelect) administrativeSelect.required = false;
                } else if (selectedType === 'administrative') {
                    administrativeSelectContainer.style.display = 'block';
                    if (administrativeSelect) administrativeSelect.required = true;
                    if (driverSelect) driverSelect.required = false;
                } else {
                    if (driverSelect) driverSelect.required = false;
                    if (administrativeSelect) administrativeSelect.required = false;
                }
            }

            // Update hidden input when driver is selected
            if (driverSelect) {
                driverSelect.addEventListener('change', function() {
                    if (subjectIdHidden) {
                        subjectIdHidden.value = this.value;
                    }
                    // Update replacement options to exclude selected subject
                    updateReplacementSelectors();
                });
            }

            // Update hidden input when administrative user is selected
            if (administrativeSelect) {
                administrativeSelect.addEventListener('change', function() {
                    if (subjectIdHidden) {
                        subjectIdHidden.value = this.value;
                    }
                    // Update replacement options to exclude selected subject
                    updateReplacementSelectors();
                });
            }

            if (subjectTypeSelect) {
                subjectTypeSelect.addEventListener('change', function() {
                    updateSubjectSelectors();
                    updateReplacementSelectors(); // Update replacement when subject changes
                });
                // Initialize on page load
                updateSubjectSelectors();
                
                // Set initial value if old input exists
                const oldSubjectType = subjectTypeSelect.value;
                if (oldSubjectType === 'driver' && driverSelect && driverSelect.value) {
                    if (subjectIdHidden) subjectIdHidden.value = driverSelect.value;
                } else if (oldSubjectType === 'administrative' && administrativeSelect && administrativeSelect.value) {
                    if (subjectIdHidden) subjectIdHidden.value = administrativeSelect.value;
                }
            }

            // Handle replacement selection
            const replacementTypeHidden = document.getElementById('replacement_type');
            const replacementIdHidden = document.getElementById('replacement_id');
            const replacementDriverContainer = document.getElementById('replacement_driver_select_container');
            const replacementAdministrativeContainer = document.getElementById('replacement_administrative_select_container');
            const replacementDriverSelect = document.getElementById('replacement_id_driver');
            const replacementAdministrativeSelect = document.getElementById('replacement_id_administrative');

            function updateReplacementSelectors() {
                const subjectType = subjectTypeSelect ? subjectTypeSelect.value : '';
                const subjectId = subjectIdHidden ? subjectIdHidden.value : '';
                
                // Hide both replacement containers first
                replacementDriverContainer.style.display = 'none';
                replacementAdministrativeContainer.style.display = 'none';
                
                // Clear replacement selects and hidden input
                if (replacementDriverSelect) replacementDriverSelect.value = '';
                if (replacementAdministrativeSelect) replacementAdministrativeSelect.value = '';
                if (replacementIdHidden) replacementIdHidden.value = '';
                
                // Auto-set replacement_type to match subject_type
                if (replacementTypeHidden) {
                    replacementTypeHidden.value = subjectType;
                }
                
                // Show appropriate replacement container based on subject type
                if (subjectType === 'driver') {
                    replacementDriverContainer.style.display = 'block';
                    // Filter out the selected subject from replacement options
                    if (replacementDriverSelect) {
                        Array.from(replacementDriverSelect.options).forEach(option => {
                            if (option.value === '' || option.value === '0') {
                                // Keep the empty option visible
                                option.style.display = 'block';
                            } else if (subjectId && option.value === subjectId) {
                                // Hide the selected subject
                                option.style.display = 'none';
                            } else {
                                // Show all other options
                                option.style.display = 'block';
                            }
                        });
                    }
                } else if (subjectType === 'administrative') {
                    replacementAdministrativeContainer.style.display = 'block';
                    // Filter out the selected subject from replacement options
                    if (replacementAdministrativeSelect) {
                        Array.from(replacementAdministrativeSelect.options).forEach(option => {
                            if (option.value === '' || option.value === '0') {
                                // Keep the empty option visible
                                option.style.display = 'block';
                            } else if (subjectId && option.value === subjectId) {
                                // Hide the selected subject
                                option.style.display = 'none';
                            } else {
                                // Show all other options
                                option.style.display = 'block';
                            }
                        });
                    }
                }
            }

            // Update hidden input when replacement driver is selected
            if (replacementDriverSelect) {
                replacementDriverSelect.addEventListener('change', function() {
                    if (replacementIdHidden) {
                        replacementIdHidden.value = this.value;
                    }
                });
            }

            // Update hidden input when replacement administrative user is selected
            if (replacementAdministrativeSelect) {
                replacementAdministrativeSelect.addEventListener('change', function() {
                    if (replacementIdHidden) {
                        replacementIdHidden.value = this.value;
                    }
                });
            }

            // Update replacement when subject changes
            if (subjectIdHidden) {
                subjectIdHidden.addEventListener('change', function() {
                    updateReplacementSelectors();
                });
            }

            // Initialize replacement selectors on page load
            updateReplacementSelectors();
            
            // Set initial replacement value if old input exists
            const oldReplacementType = replacementTypeHidden ? replacementTypeHidden.value : '';
            if (oldReplacementType === 'driver' && replacementDriverSelect && replacementDriverSelect.value) {
                if (replacementIdHidden) replacementIdHidden.value = replacementDriverSelect.value;
            } else if (oldReplacementType === 'administrative' && replacementAdministrativeSelect && replacementAdministrativeSelect.value) {
                if (replacementIdHidden) replacementIdHidden.value = replacementAdministrativeSelect.value;
            }
        });
    </script>
</x-app-layout>

