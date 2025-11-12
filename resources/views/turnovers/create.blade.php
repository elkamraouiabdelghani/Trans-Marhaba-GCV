<x-app-layout>
    <x-slot name="header">
        @include('layouts.topnav')
    </x-slot>

    <div class="container-fluid py-4">
        <!-- Breadcrumb -->
        <nav aria-label="breadcrumb" class="mb-4">
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">{{ __('messages.dashboard') }}</a></li>
                <li class="breadcrumb-item"><a href="{{ route('turnovers.index') }}">{{ __('messages.turnovers') }}</a></li>
                <li class="breadcrumb-item active">{{ __('messages.create_turnover') }}</li>
            </ol>
        </nav>

        <!-- Success/Error Messages -->
        @if(session('success'))
            <div class="alert alert-success alert-dismissible fade show" role="alert">
                <i class="bi bi-check-circle me-2"></i>
                {{ session('success') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        @endif

        @if(session('error'))
            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                <i class="bi bi-exclamation-triangle me-2"></i>
                {{ session('error') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        @endif

        <!-- Create Form -->
        <div class="card border-0 shadow-sm col-md-10 mx-auto">
            <div class="card-header bg-white border-bottom py-3">
                <div class="d-flex justify-content-between align-items-center">
                    <h5 class="mb-0 text-dark fw-bold">
                        <i class="bi bi-plus-circle me-2 text-primary"></i>
                        {{ __('messages.create_turnover') }}
                    </h5>
                    <a href="{{ route('turnovers.index') }}" class="btn btn-outline-secondary btn-sm">
                        <i class="bi bi-arrow-left me-1"></i>
                        {{ __('messages.back') }}
                    </a>
                </div>
            </div>
            <div class="card-body p-4">
                <form action="{{ route('turnovers.store') }}" method="POST" id="turnoverForm">
                    @csrf

                    @if ($errors->any())
                        <div class="alert alert-danger alert-dismissible fade show mb-4" role="alert">
                            <h6 class="alert-heading mb-2">
                                <i class="bi bi-exclamation-triangle me-2"></i>
                                {{ __('messages.validation_errors') ?? 'Please fix the following errors:' }}
                            </h6>
                            <ul class="mb-0">
                                @foreach ($errors->all() as $error)
                                    <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                        </div>
                    @endif
                    <div class="row mb-4">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">{{ __('messages.person_type') }} <span class="text-danger">*</span></label>
                            <div class="btn-group w-100" role="group">
                                <input type="radio" class="btn-check" name="person_type" id="person_type_driver" value="driver" checked>
                                <label class="btn btn-outline-dark" for="person_type_driver">
                                    <i class="bi bi-car-front me-1"></i>
                                    {{ __('messages.driver') }}
                                </label>
                                <input type="radio" class="btn-check" name="person_type" id="person_type_admin" value="admin">
                                <label class="btn btn-outline-dark" for="person_type_admin">
                                    <i class="bi bi-person-gear me-1"></i>
                                    {{ __('messages.administration') }}
                                </label>
                            </div>
                        </div>
                        <div class="col-md-6 mb-3">
                            <!-- Driver Selection -->
                            <div id="driver_section" class="person-section">
                                <div class="row">
                                    <label for="driver_id" class="form-label">{{ __('messages.driver') }} <span class="text-danger">*</span></label>
                                    <select class="form-select @error('driver_id') is-invalid @enderror" 
                                            id="driver_id" 
                                            name="driver_id">
                                        <option value="">{{ __('messages.select_driver') }}</option>
                                        @foreach($drivers as $driver)
                                            <option value="{{ $driver->id }}" 
                                                data-flotte="{{ $driver->flotte->name ?? '' }}"
                                                {{ old('driver_id') == $driver->id ? 'selected' : '' }}>
                                                {{ $driver->full_name }}
                                            </option>
                                        @endforeach
                                    </select>
                                    @error('driver_id')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
        
                            <!-- User Selection -->
                            <div id="admin_section" class="person-section" style="display: none;">
                                <div class="row">
                                    <label for="user_id" class="form-label">{{ __('messages.administration') }} <span class="text-danger">*</span></label>
                                    <select class="form-select @error('user_id') is-invalid @enderror" 
                                            id="user_id" 
                                            name="user_id">
                                        <option value="">{{ __('messages.select_user') }}</option>
                                        @foreach($users as $user)
                                            <option value="{{ $user->id }}" {{ old('user_id') == $user->id ? 'selected' : '' }}>
                                                {{ $user->name }} ({{ $user->role ?? 'N/A' }})
                                            </option>
                                        @endforeach
                                    </select>
                                    @error('user_id')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Turnover Information -->
                    <h6 class="text-primary mb-3 mt-4 border-bottom pb-2">
                        <i class="bi bi-info-circle me-2"></i>
                        {{ __('messages.turnover_information') }}
                    </h6>
                    <div class="row mb-4">
                        <div class="col-md-6 mb-3">
                            <label for="departure_date" class="form-label">{{ __('messages.departure_date') }} <span class="text-danger">*</span></label>
                            <input type="date" 
                                   class="form-control @error('departure_date') is-invalid @enderror" 
                                   id="departure_date" 
                                   name="departure_date" 
                                   value="{{ old('departure_date') }}" 
                                   required>
                            @error('departure_date')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="col-md-6 mb-3">
                            <label for="flotte" class="form-label">{{ __('messages.flotte') }}</label>
                            <input type="text" 
                                   class="form-control @error('flotte') is-invalid @enderror" 
                                   id="flotte" 
                                   name="flotte" 
                                   value="{{ old('flotte') }}" 
                                   placeholder="{{ __('messages.flotte') }}">
                            @error('flotte')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="col-md-6 mb-3">
                            <label for="position" class="form-label">{{ __('messages.position') }}</label>
                            <input type="text" 
                                   class="form-control @error('position') is-invalid @enderror" 
                                   id="position" 
                                   name="position" 
                                   value="{{ old('position') }}" 
                                   readonly
                                   placeholder="{{ __('messages.auto_filled') }}">
                            @error('position')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>

                    <!-- Departure Details -->
                    <h6 class="text-primary mb-3 mt-4 border-bottom pb-2">
                        <i class="bi bi-file-text me-2"></i>
                        {{ __('messages.departure_details') }}
                    </h6>
                    <div class="row mb-4">
                        <div class="col-12 mb-3">
                            <label for="departure_reason" class="form-label">{{ __('messages.departure_reason') }} <span class="text-danger">*</span></label>
                            <textarea class="form-control @error('departure_reason') is-invalid @enderror" 
                                      id="departure_reason" 
                                      name="departure_reason" 
                                      rows="4" 
                                      required>{{ old('departure_reason') }}</textarea>
                            @error('departure_reason')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="col-12 mb-3">
                            <label for="interview_notes" class="form-label">{{ __('messages.interview_notes') }}</label>
                            <textarea class="form-control @error('interview_notes') is-invalid @enderror" 
                                      id="interview_notes" 
                                      name="interview_notes" 
                                      rows="4">{{ old('interview_notes') }}</textarea>
                            @error('interview_notes')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="mb-3">
                            <label for="interviewed_by" class="form-label">{{ __('messages.interviewed_by') }}</label>
                            <input type="text" 
                                   class="form-control @error('interviewed_by') is-invalid @enderror" 
                                   id="interviewed_by" 
                                   name="interviewed_by" 
                                   value="{{ auth()->user()->name }}" 
                                   placeholder="{{ __('messages.interviewed_by') }}">
                            @error('interviewed_by')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        {{-- interview exit questions to answer --}}

                        <div class="col-12 mb-3">
                            <label for="observations" class="form-label">{{ __('messages.observations') }}</label>
                            <textarea class="form-control @error('observations') is-invalid @enderror" 
                                      id="observations" 
                                      name="observations" 
                                      rows="4">{{ old('observations') }}</textarea>
                            @error('observations')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>

                    <!-- Form Actions -->
                    <div class="d-flex justify-content-center gap-2 mt-4 pt-3 border-top">
                        <a href="{{ route('turnovers.index') }}" class="btn btn-outline-secondary">
                            <i class="bi bi-x-circle me-1"></i>
                            {{ __('messages.cancel') }}
                        </a>
                        <button type="submit" class="btn btn-dark" id="submitBtn">
                            <i class="bi bi-check-circle me-1"></i>
                            {{ __('messages.save') }}
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    @push('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const personTypeRadios = document.querySelectorAll('input[name="person_type"]');
            const driverSection = document.getElementById('driver_section');
            const adminSection = document.getElementById('admin_section');
            const driverSelect = document.getElementById('driver_id');
            const userSelect = document.getElementById('user_id');
            const positionInput = document.getElementById('position');
            const flotteInput = document.getElementById('flotte');

            // Handle person type change
            personTypeRadios.forEach(radio => {
                radio.addEventListener('change', function() {
                    if (this.value === 'driver') {
                        driverSection.style.display = 'block';
                        adminSection.style.display = 'none';
                        driverSelect.required = true;
                        userSelect.required = false;
                        userSelect.value = '';
                    } else {
                        driverSection.style.display = 'none';
                        adminSection.style.display = 'block';
                        driverSelect.required = false;
                        userSelect.required = true;
                        driverSelect.value = '';
                        positionInput.value = '';
                        flotteInput.value = '';
                    }
                });
            });

            // Auto-fill position and flotte when driver is selected
            driverSelect.addEventListener('change', function() {
                const selectedOption = this.options[this.selectedIndex];
                if (selectedOption.value) {
                    positionInput.value = 'Chauffeur';
                    const flotteName = selectedOption.getAttribute('data-flotte');
                    if (flotteName) {
                        flotteInput.value = flotteName;
                    }
                } else {
                    positionInput.value = '';
                    flotteInput.value = '';
                }
            });

            // Auto-fill position and flotte when user is selected
            userSelect.addEventListener('change', function() {
                const selectedOption = this.options[this.selectedIndex];
                if (selectedOption.value) {
                    const userText = selectedOption.text;
                    const roleMatch = userText.match(/\(([^)]+)\)/);
                    if (roleMatch) {
                        positionInput.value = roleMatch[1];
                    } else {
                        positionInput.value = 'Administration';
                    }
                    // Auto-fill flotte to "Administration" for administration staff
                    flotteInput.value = 'Administration';
                } else {
                    positionInput.value = '';
                    flotteInput.value = '';
                }
            });
        });
    </script>
    @endpush
</x-app-layout>

