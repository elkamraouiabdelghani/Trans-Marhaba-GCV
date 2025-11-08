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

    <div class="container-fluid py-4">
        <!-- Breadcrumb -->
        <nav aria-label="breadcrumb" class="mb-4">
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">{{ __('messages.dashboard') }}</a></li>
                <li class="breadcrumb-item"><a href="{{ route('drivers.index') }}">{{ __('messages.drivers') }}</a></li>
                <li class="breadcrumb-item"><a href="{{ route('drivers.show', $driver) }}">{{ $driver->full_name ?? __('messages.driver_number') . $driver->id }}</a></li>
                <li class="breadcrumb-item active">{{ __('messages.edit') }}</li>
            </ol>
        </nav>

        <!-- Header -->
        <div class="card border-0 shadow-sm mb-4">
            <div class="card-header bg-white border-0 py-3">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h5 class="mb-0 text-dark fw-bold">
                            <i class="bi bi-pencil-square me-2 text-primary"></i>
                            {{ __('messages.edit_driver') }}: {{ $driver->full_name ?? __('messages.driver_number') . $driver->id }}
                        </h5>
                        <small class="text-muted">{{ __('messages.complete_driver_information') }}</small>
                    </div>
                    <a href="{{ route('drivers.show', $driver) }}" class="btn btn-outline-secondary btn-sm">
                        <i class="bi bi-arrow-left me-1"></i>
                        {{ __('messages.back') }}
                    </a>
                </div>
            </div>
        </div>

        <!-- Edit Form -->
        <div class="card border-0 shadow-sm col-lg-10 mx-auto">
            <div class="card-body p-4">
                <form action="{{ route('drivers.update', $driver) }}" method="POST">
                    @csrf
                    @method('PATCH')

                    <!-- Personal Information -->
                    <h6 class="text-primary mb-3 border-bottom pb-2">
                        <i class="bi bi-person me-2"></i>
                        {{ __('messages.personal_information') }}
                    </h6>
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="full_name" class="form-label">{{ __('messages.name') }} <span class="text-danger">*</span></label>
                            <input type="text" 
                                   class="form-control @error('full_name') is-invalid @enderror" 
                                   id="full_name" 
                                   name="full_name" 
                                   value="{{ old('full_name', $driver->full_name ?? '') }}" 
                                   required>
                            @error('full_name')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="col-md-6 mb-3">
                            <label for="email" class="form-label">{{ __('messages.email') }}</label>
                            <input type="email" 
                                   class="form-control @error('email') is-invalid @enderror" 
                                   id="email" 
                                   name="email" 
                                   value="{{ old('email', $driver->email ?? '') }}">
                            @error('email')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="phone" class="form-label">{{ __('messages.phone') }}</label>
                            <input type="text" 
                                   class="form-control @error('phone') is-invalid @enderror" 
                                   id="phone" 
                                   name="phone" 
                                   value="{{ old('phone', $driver->phone ?? '') }}">
                            @error('phone')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="col-md-6 mb-3">
                            <label for="cin" class="form-label">{{ __('messages.cin') }}</label>
                            <input type="text" 
                                   class="form-control @error('cin') is-invalid @enderror" 
                                   id="cin" 
                                   name="cin" 
                                   value="{{ old('cin', $driver->cin ?? '') }}">
                            @error('cin')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="date_of_birth" class="form-label">{{ __('messages.date_of_birth') }}</label>
                            <input type="date" 
                                   class="form-control @error('date_of_birth') is-invalid @enderror" 
                                   id="date_of_birth" 
                                   name="date_of_birth" 
                                   value="{{ old('date_of_birth', $driver->date_of_birth ? $driver->date_of_birth->format('Y-m-d') : '') }}">
                            @error('date_of_birth')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="col-md-6 mb-3">
                            <label for="city" class="form-label">{{ __('messages.city') }}</label>
                            <input type="text" 
                                   class="form-control @error('city') is-invalid @enderror" 
                                   id="city" 
                                   name="city" 
                                   value="{{ old('city', $driver->city ?? '') }}">
                            @error('city')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>

                    <div class="mb-3">
                        <label for="address" class="form-label">{{ __('messages.address') }}</label>
                        <textarea class="form-control @error('address') is-invalid @enderror" 
                                  id="address" 
                                  name="address" 
                                  rows="2">{{ old('address', $driver->address ?? '') }}</textarea>
                        @error('address')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <!-- License Information -->
                    <h6 class="text-primary mb-3 mt-4 border-bottom pb-2">
                        <i class="bi bi-card-text me-2"></i>
                        {{ __('messages.license_information') }}
                    </h6>
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="license_number" class="form-label">{{ __('messages.license_number') }} <span class="text-danger">*</span></label>
                            <input type="text" 
                                   class="form-control @error('license_number') is-invalid @enderror" 
                                   id="license_number" 
                                   name="license_number" 
                                   value="{{ old('license_number', $driver->license_number ?? '') }}" 
                                   required>
                            @error('license_number')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="col-md-6 mb-3">
                            <label for="license_type" class="form-label">{{ __('messages.license_type') }}</label>
                            <input type="text" 
                                   class="form-control @error('license_type') is-invalid @enderror" 
                                   id="license_type" 
                                   name="license_type" 
                                   value="{{ old('license_type', $driver->license_type ?? '') }}">
                            @error('license_type')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="license_issue_date" class="form-label">{{ __('messages.license_issue_date') }}</label>
                            <input type="date" 
                                   class="form-control @error('license_issue_date') is-invalid @enderror" 
                                   id="license_issue_date" 
                                   name="license_issue_date" 
                                   value="{{ old('license_issue_date', $driver->license_issue_date ? $driver->license_issue_date->format('Y-m-d') : '') }}">
                            @error('license_issue_date')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="col-md-6 mb-3">
                            <label for="license_class" class="form-label">{{ __('messages.license_class') }}</label>
                            <input type="text" 
                                   class="form-control @error('license_class') is-invalid @enderror" 
                                   id="license_class" 
                                   name="license_class" 
                                   value="{{ old('license_class', $driver->license_class ?? '') }}">
                            @error('license_class')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>

                    <!-- Medical & Formation Information -->
                    <h6 class="text-primary mb-3 mt-4 border-bottom pb-2">
                        <i class="bi bi-heart-pulse me-2"></i>
                        {{ __('messages.medical_formation') }}
                    </h6>
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="visite_medical" class="form-label">{{ __('messages.visite_medical') }}</label>
                            <input type="date" 
                                   class="form-control @error('visite_medical') is-invalid @enderror" 
                                   id="visite_medical" 
                                   name="visite_medical" 
                                   value="{{ old('visite_medical', $driver->visite_medical ? $driver->visite_medical->format('Y-m-d') : '') }}">
                            @error('visite_medical')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="col-md-6 mb-3">
                            <label for="visite_yeux" class="form-label">{{ __('messages.visite_yeux') }}</label>
                            <input type="date" 
                                   class="form-control @error('visite_yeux') is-invalid @enderror" 
                                   id="visite_yeux" 
                                   name="visite_yeux" 
                                   value="{{ old('visite_yeux', $driver->visite_yeux ? $driver->visite_yeux->format('Y-m-d') : '') }}">
                            @error('visite_yeux')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="formation_imd" class="form-label">{{ __('messages.formation_imd') }}</label>
                            <input type="date" 
                                   class="form-control @error('formation_imd') is-invalid @enderror" 
                                   id="formation_imd" 
                                   name="formation_imd" 
                                   value="{{ old('formation_imd', $driver->formation_imd ? $driver->formation_imd->format('Y-m-d') : '') }}">
                            @error('formation_imd')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="col-md-6 mb-3">
                            <label for="formation_16_module" class="form-label">{{ __('messages.formation_16_module') }}</label>
                            <input type="date" 
                                   class="form-control @error('formation_16_module') is-invalid @enderror" 
                                   id="formation_16_module" 
                                   name="formation_16_module" 
                                   value="{{ old('formation_16_module', $driver->formation_16_module ? $driver->formation_16_module->format('Y-m-d') : '') }}">
                            @error('formation_16_module')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>

                    <!-- Administrative Information -->
                    <h6 class="text-primary mb-3 mt-4 border-bottom pb-2">
                        <i class="bi bi-briefcase me-2"></i>
                        {{ __('messages.administrative_information') }}
                    </h6>
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="date_integration" class="form-label">{{ __('messages.date_integration') }}</label>
                            <input type="date" 
                                   class="form-control @error('date_integration') is-invalid @enderror" 
                                   id="date_integration" 
                                   name="date_integration" 
                                   value="{{ old('date_integration', $driver->date_integration ? $driver->date_integration->format('Y-m-d') : '') }}">
                            @error('date_integration')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="col-md-6 mb-3">
                            <label for="status" class="form-label">{{ __('messages.status') }}</label>
                            <select class="form-select @error('status') is-invalid @enderror" 
                                    id="status" 
                                    name="status">
                                <option value="">{{ __('messages.select_option') }}</option>
                                <option value="active" {{ old('status', $driver->status) === 'active' ? 'selected' : '' }}>{{ __('messages.active') }}</option>
                                <option value="inactive" {{ old('status', $driver->status) === 'inactive' ? 'selected' : '' }}>{{ __('messages.inactive') }}</option>
                            </select>
                            @error('status')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="n_cnss" class="form-label">{{ __('messages.n_cnss') }}</label>
                            <input type="text" 
                                   class="form-control @error('n_cnss') is-invalid @enderror" 
                                   id="n_cnss" 
                                   name="n_cnss" 
                                   value="{{ old('n_cnss', $driver->n_cnss ?? '') }}">
                            @error('n_cnss')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="col-md-6 mb-3">
                            <label for="rib" class="form-label">{{ __('messages.rib') }}</label>
                            <input type="text" 
                                   class="form-control @error('rib') is-invalid @enderror" 
                                   id="rib" 
                                   name="rib" 
                                   value="{{ old('rib', $driver->rib ?? '') }}">
                            @error('rib')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="attestation_travail" class="form-label">{{ __('messages.attestation_travail') }}</label>
                            <input type="text" 
                                   class="form-control @error('attestation_travail') is-invalid @enderror" 
                                   id="attestation_travail" 
                                   name="attestation_travail" 
                                   value="{{ old('attestation_travail', $driver->attestation_travail ?? '') }}">
                            @error('attestation_travail')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="col-md-6 mb-3">
                            <label for="carte_profession" class="form-label">{{ __('messages.carte_profession') }}</label>
                            <input type="text" 
                                   class="form-control @error('carte_profession') is-invalid @enderror" 
                                   id="carte_profession" 
                                   name="carte_profession" 
                                   value="{{ old('carte_profession', $driver->carte_profession ?? '') }}">
                            @error('carte_profession')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>

                    <!-- Assignment Information -->
                    <h6 class="text-primary mb-3 mt-4 border-bottom pb-2">
                        <i class="bi bi-truck me-2"></i>
                        {{ __('messages.assignment_information') }}
                    </h6>
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="flotte_id" class="form-label">{{ __('messages.flotte') }}</label>
                            <select class="form-select @error('flotte_id') is-invalid @enderror" 
                                    id="flotte_id" 
                                    name="flotte_id">
                                <option value="">{{ __('messages.select_option') }}</option>
                                @foreach($flottes as $flotte)
                                    <option value="{{ $flotte->id }}" {{ old('flotte_id', $driver->flotte_id) == $flotte->id ? 'selected' : '' }}>
                                        {{ $flotte->name }}
                                    </option>
                                @endforeach
                            </select>
                            @error('flotte_id')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="col-md-6 mb-3">
                            <label for="assigned_vehicle_id" class="form-label">{{ __('messages.assigned_vehicle') }}</label>
                            <select class="form-select @error('assigned_vehicle_id') is-invalid @enderror" 
                                    id="assigned_vehicle_id" 
                                    name="assigned_vehicle_id">
                                <option value="">{{ __('messages.select_option') }}</option>
                                @foreach($vehicles as $vehicle)
                                    <option value="{{ $vehicle->id }}" {{ old('assigned_vehicle_id', $driver->assigned_vehicle_id) == $vehicle->id ? 'selected' : '' }}>
                                        {{ $vehicle->license_plate }} - {{ $vehicle->brand }} {{ $vehicle->model }}
                                    </option>
                                @endforeach
                            </select>
                            @error('assigned_vehicle_id')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>

                    <!-- Notes -->
                    <div class="mb-3 mt-4">
                        <label for="notes" class="form-label">{{ __('messages.notes') }}</label>
                        <textarea class="form-control @error('notes') is-invalid @enderror" 
                                  id="notes" 
                                  name="notes" 
                                  rows="4">{{ old('notes', $driver->notes ?? '') }}</textarea>
                        @error('notes')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <!-- Form Actions -->
                    <div class="d-flex justify-content-between mt-4 pt-3 border-top">
                        <a href="{{ route('drivers.show', $driver) }}" class="btn btn-outline-secondary">
                            <i class="bi bi-x-circle me-1"></i>
                            {{ __('messages.cancel') }}
                        </a>
                        <button type="submit" class="btn btn-primary">
                            <i class="bi bi-check-circle me-1"></i>
                            {{ __('messages.save') }}
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</x-app-layout>

