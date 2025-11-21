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
        });
    </script>
</x-app-layout>

