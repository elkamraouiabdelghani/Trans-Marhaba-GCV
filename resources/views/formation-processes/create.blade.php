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
                            <h5 class="mb-0 text-dark fw-bold">
                                <i class="bi bi-plus-circle me-2 text-primary"></i>
                                {{ __('messages.create_formation_process') }} - {{ __('messages.step1_identification_besoin') }}
                            </h5>
                            <a href="{{ route('formation-processes.index') }}" class="btn btn-outline-secondary btn-sm">
                                <i class="bi bi-x-circle me-1"></i>
                                {{ __('messages.cancel') }}
                            </a>
                        </div>
                    </div>
                    <div class="card-body">
                        <form action="{{ route('formation-processes.store') }}" method="POST">
                            @csrf

                            <div class="mb-3">
                                <label for="site" class="form-label">{{ __('messages.site') }} <span class="text-danger">*</span></label>
                                <input type="text" 
                                       class="form-control @error('site') is-invalid @enderror" 
                                       id="site" 
                                       name="site" 
                                       value="{{ old('site', request('site')) }}" 
                                       required>
                                @error('site')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                                <small class="form-text text-muted">{{ __('messages.enter_site') }}</small>
                            </div>

                            <div class="mb-3">
                                <label for="driver_id" class="form-label">{{ __('messages.driver') }} <span class="text-danger">*</span></label>
                                <select class="form-select @error('driver_id') is-invalid @enderror" 
                                        id="driver_id" 
                                        name="driver_id" 
                                        required>
                                    <option value="">{{ __('messages.select_driver_for_formation') }}</option>
                                    @foreach($drivers as $driver)
                                        <option value="{{ $driver->id }}" {{ old('driver_id', request('driver_id', $selectedDriverId ?? null)) == $driver->id ? 'selected' : '' }}>
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
                                        @foreach($flottes as $flotte)
                                            <option value="{{ $flotte->id }}" {{ old('flotte_id', request('flotte_id', $selectedFlotteId ?? null)) == $flotte->id ? 'selected' : '' }}>
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
                                        @foreach($formationTypes as $formationType)
                                            <option value="{{ $formationType->id }}" {{ old('formation_type_id', request('formation_type_id', $selectedFormationTypeId ?? null)) == $formationType->id ? 'selected' : '' }}>
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
                                       value="{{ old('theme') }}" 
                                       required>
                                @error('theme')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                                <small class="form-text text-muted">{{ __('messages.enter_theme') }}</small>
                            </div>

                            <div class="mb-3">
                                <label for="identification_besoin" class="form-label">{{ __('messages.identification_besoin_notes') }}</label>
                                <textarea class="form-control @error('identification_besoin') is-invalid @enderror" 
                                          id="identification_besoin" 
                                          name="identification_besoin" 
                                          rows="3">{{ old('identification_besoin') }}</textarea>
                                @error('identification_besoin')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <hr class="my-4">
                            <div class="d-flex gap-2 justify-content-end">
                                <a href="{{ route('formation-processes.index') }}" class="btn btn-outline-secondary">
                                    <i class="bi bi-x-circle me-1"></i>
                                    {{ __('messages.cancel') }}
                                </a>
                                <button type="submit" class="btn btn-dark">
                                    <i class="bi bi-check-circle me-1"></i>
                                    {{ __('messages.next') }}
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const toasts = document.querySelectorAll('.toast.show');
            toasts.forEach(function(toastEl) {
                if (typeof bootstrap !== 'undefined') {
                    const toast = new bootstrap.Toast(toastEl);
                    toast.show();
                }
            });
        });
    </script>
</x-app-layout>

