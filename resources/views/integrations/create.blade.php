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
                                {{ __('messages.start_integration') }} - {{ __('messages.identification_besoin') }}
                            </h5>
                            <a href="{{ route('integrations.index') }}" class="btn btn-outline-secondary btn-sm">
                                <i class="bi bi-x-circle me-1"></i>
                                {{ __('messages.cancel') }}
                            </a>
                        </div>
                    </div>
                    <div class="card-body">
                        <form action="{{ route('integrations.store') }}" method="POST">
                            @csrf

                            <div class="mb-3">
                                <label for="identification_besoin" class="form-label">{{ __('messages.identification_besoin') }} <span class="text-danger">*</span></label>
                                <textarea class="form-control @error('identification_besoin') is-invalid @enderror" 
                                          id="identification_besoin" 
                                          name="identification_besoin" 
                                          rows="3" 
                                          required>{{ old('identification_besoin') }}</textarea>
                                @error('identification_besoin')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                                <small class="form-text text-muted">{{ __('messages.enter_identification_besoin') }}</small>
                            </div>

                            <div class="mb-3">
                                <label for="poste_type" class="form-label">{{ __('messages.poste_type') }} <span class="text-danger">*</span></label>
                                <select class="form-select @error('poste_type') is-invalid @enderror" 
                                        id="poste_type" 
                                        name="poste_type" 
                                        required>
                                    <option value="">{{ __('messages.select_option') }}</option>
                                    <option value="chauffeur" {{ old('poste_type') === 'chauffeur' ? 'selected' : '' }}>{{ __('messages.chauffeurs') }}</option>
                                    <option value="administration" {{ old('poste_type') === 'administration' ? 'selected' : '' }}>{{ __('messages.administratife') }}</option>
                                </select>
                                @error('poste_type')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="mb-3">
                                <label for="description_poste" class="form-label">{{ __('messages.description_poste') }} <span class="text-danger">*</span></label>
                                <textarea class="form-control @error('description_poste') is-invalid @enderror" 
                                          id="description_poste" 
                                          name="description_poste" 
                                          rows="4" 
                                          required>{{ old('description_poste') }}</textarea>
                                @error('description_poste')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                                <small class="form-text text-muted">{{ __('messages.enter_description_poste') }}</small>
                            </div>

                            <div class="mb-3">
                                <label class="form-label">{{ __('messages.prospection') }} - {{ __('messages.methodes_prospection') }} <span class="text-danger">*</span></label>
                                <div class="form-check">
                                    <input class="form-check-input @error('prospection_method') is-invalid @enderror" 
                                           type="radio" 
                                           name="prospection_method" 
                                           id="prospection_reseaux_social" 
                                           value="reseaux_social" 
                                           {{ old('prospection_method') === 'reseaux_social' ? 'checked' : '' }} 
                                           required>
                                    <label class="form-check-label" for="prospection_reseaux_social">
                                        {{ __('messages.reseaux_sociaux') }}
                                    </label>
                                </div>
                                <div class="form-check">
                                    <input class="form-check-input @error('prospection_method') is-invalid @enderror" 
                                           type="radio" 
                                           name="prospection_method" 
                                           id="prospection_bouche_a_oreil" 
                                           value="bouche_a_oreil" 
                                           {{ old('prospection_method') === 'bouche_a_oreil' ? 'checked' : '' }}>
                                    <label class="form-check-label" for="prospection_bouche_a_oreil">
                                        {{ __('messages.bouche_a_oreille') }}
                                    </label>
                                </div>
                                <div class="form-check">
                                    <input class="form-check-input @error('prospection_method') is-invalid @enderror" 
                                           type="radio" 
                                           name="prospection_method" 
                                           id="prospection_bureau_recrutement" 
                                           value="bureau_recrutement" 
                                           {{ old('prospection_method') === 'bureau_recrutement' ? 'checked' : '' }}>
                                    <label class="form-check-label" for="prospection_bureau_recrutement">
                                        {{ __('messages.bureau_recrutement') }}
                                    </label>
                                </div>
                                <div class="form-check">
                                    <input class="form-check-input @error('prospection_method') is-invalid @enderror" 
                                           type="radio" 
                                           name="prospection_method" 
                                           id="prospection_autre" 
                                           value="autre" 
                                           {{ old('prospection_method') === 'autre' ? 'checked' : '' }}>
                                    <label class="form-check-label" for="prospection_autre">
                                        {{ __('messages.autres') }}
                                    </label>
                                </div>
                                @error('prospection_method')
                                    <div class="invalid-feedback d-block">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label for="prospection_date" class="form-label">{{ __('messages.date_debut_prospection') }}</label>
                                    <input type="date" 
                                           class="form-control @error('prospection_date') is-invalid @enderror" 
                                           id="prospection_date" 
                                           name="prospection_date" 
                                           value="{{ old('prospection_date') }}">
                                    @error('prospection_date')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>

                            <div class="mb-3">
                                <label for="notes_prospection" class="form-label">{{ __('messages.notes_supplementaires') }}</label>
                                <textarea class="form-control @error('notes_prospection') is-invalid @enderror" 
                                          id="notes_prospection" 
                                          name="notes_prospection" 
                                          rows="3">{{ old('notes_prospection') }}</textarea>
                                @error('notes_prospection')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <hr class="my-4">
                            <div class="d-flex gap-2 justify-content-center">
                                <a href="{{ route('integrations.index') }}" class="btn btn-outline-secondary">
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

