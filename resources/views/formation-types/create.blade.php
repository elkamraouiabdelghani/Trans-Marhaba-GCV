<x-app-layout>
    <x-slot name="header">
        @include('layouts.topnav')
    </x-slot>

    <!-- Toast Container - Top Center -->
    <div class="toast-container position-fixed top-0 start-50 translate-middle-x p-3" style="z-index: 1055;">
        @if(session('success'))
            <div class="toast show" role="alert" aria-live="assertive" aria-atomic="true" data-bs-autohide="true" data-bs-delay="10000">
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
            <div class="toast show" role="alert" aria-live="assertive" aria-atomic="true" data-bs-autohide="true" data-bs-delay="10000">
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
            <div class="col-lg-8">
                <div class="card border-0 shadow-sm">
                    <div class="card-header bg-white border-0 py-3">
                        <div class="d-flex justify-content-between align-items-center">
                            <h5 class="mb-0 text-dark fw-bold">
                                <i class="bi bi-plus-circle me-2 text-primary"></i>
                                {{ __('messages.create_formation_type') }}
                            </h5>
                            <a href="{{ route('formation-types.index') }}" class="btn btn-outline-secondary">
                                <i class="bi bi-x-circle me-1"></i>
                                {{ __('messages.cancel') }}
                            </a>
                        </div>
                    </div>
                    <div class="card-body">
                        <form action="{{ route('formation-types.store') }}" method="POST">
                            @csrf

                            <div class="mb-3">
                                <label for="name" class="form-label">{{ __('messages.formation_type_name') }} <span class="text-danger">*</span></label>
                                <input type="text" 
                                       class="form-control @error('name') is-invalid @enderror" 
                                       id="name" 
                                       name="name" 
                                       value="{{ old('name') }}" 
                                       required>
                                @error('name')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="mb-3">
                                <label for="code" class="form-label">{{ __('messages.formation_type_code') }} <span class="text-danger">*</span></label>
                                <input type="text" 
                                       class="form-control @error('code') is-invalid @enderror" 
                                       id="code" 
                                       name="code" 
                                       value="{{ old('code') }}" 
                                       required>
                                @error('code')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                                <small class="form-text text-muted">{{ __('messages.code_example') }}</small>
                            </div>

                            <div class="mb-3">
                                <label for="description" class="form-label">{{ __('messages.formation_type_description') }}</label>
                                <textarea class="form-control @error('description') is-invalid @enderror" 
                                          id="description" 
                                          name="description" 
                                          rows="3">{{ old('description') }}</textarea>
                                @error('description')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="mb-3 form-check">
                                <input type="checkbox" 
                                       class="form-check-input" 
                                       id="is_active" 
                                       name="is_active" 
                                       value="1" 
                                       {{ old('is_active', true) ? 'checked' : '' }}>
                                <label class="form-check-label" for="is_active">
                                    {{ __('messages.formation_type_active') }}
                                </label>
                            </div>
                            <div class="mb-3 form-check">
                                <input type="checkbox" 
                                       class="form-check-input" 
                                       id="obligatoire" 
                                       name="obligatoire" 
                                       value="1" 
                                       {{ old('obligatoire', false) ? 'checked' : '' }}>
                                <label class="form-check-label" for="obligatoire">
                                    {{ __('messages.obligatoire') }}
                                </label>
                            </div>
                            <hr class="my-3">
                            <div class="d-flex gap-2 justify-content-center align-items-center">
                                <button type="submit" class="btn btn-dark px-4">
                                    <i class="bi bi-check-circle me-1"></i>
                                    {{ __('messages.save') }}
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
