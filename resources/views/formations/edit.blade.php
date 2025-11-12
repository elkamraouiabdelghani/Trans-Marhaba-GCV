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
                                <i class="bi bi-pencil me-2 text-primary"></i>
                                {{ __('messages.edit_formation') }}
                            </h5>
                            <a href="{{ route('formations.index') }}" class="btn btn-outline-secondary">
                                <i class="bi bi-x-circle me-1"></i>
                                {{ __('messages.cancel') }}
                            </a>
                        </div>
                    </div>
                    <div class="card-body">
                        <form action="{{ route('formations.update', $formation) }}" method="POST">
                            @csrf
                            @method('PUT')

                            <div class="mb-3">
                                <label for="name" class="form-label">{{ __('messages.formation_name') }} <span class="text-danger">*</span></label>
                                <input type="text" 
                                       class="form-control @error('name') is-invalid @enderror" 
                                       id="name" 
                                       name="name" 
                                       value="{{ old('name', $formation->name) }}" 
                                       required>
                                @error('name')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="mb-3">
                                <label for="code" class="form-label">{{ __('messages.formation_code') }} <span class="text-danger">*</span></label>
                                <input type="text" 
                                       class="form-control @error('code') is-invalid @enderror" 
                                       id="code" 
                                       name="code" 
                                       value="{{ old('code', $formation->code) }}" 
                                       required>
                                @error('code')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                                <small class="form-text text-muted">{{ __('messages.code_example') }}</small>
                            </div>

                            <div class="mb-3">
                                <label for="planned_year" class="form-label">{{ __('messages.planned_year') }}</label>
                                <input type="number" 
                                       class="form-control @error('planned_year') is-invalid @enderror" 
                                       id="planned_year" 
                                       name="planned_year" 
                                       value="{{ old('planned_year', $formation->planned_year ?? date('Y')) }}" 
                                       min="1900" 
                                       max="2100">
                                @error('planned_year')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                                <small class="form-text text-muted">{{ __('messages.planned_year_hint') }}</small>
                            </div>

                            <div class="mb-3">
                                <label for="description" class="form-label">{{ __('messages.formation_description') }}</label>
                                <textarea class="form-control @error('description') is-invalid @enderror" 
                                          id="description" 
                                          name="description" 
                                          rows="3">{{ old('description', $formation->description) }}</textarea>
                                @error('description')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="mb-3">
                                <label for="formation_category_id" class="form-label">{{ __('messages.formation_category_name') }}</label>
                                <select class="form-select @error('formation_category_id') is-invalid @enderror"
                                        id="formation_category_id"
                                        name="formation_category_id">
                                    <option value="">{{ __('messages.select_option') }}</option>
                                    @foreach(($categories ?? collect()) as $category)
                                        <option value="{{ $category->id }}" {{ (int) old('formation_category_id', $formation->formation_category_id) === $category->id ? 'selected' : '' }}>
                                            {{ $category->name }}
                                        </option>
                                    @endforeach
                                </select>
                                @error('formation_category_id')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="mb-3">
                                <label for="flotte_id" class="form-label">{{ __('messages.flotte') }}</label>
                                <select class="form-select @error('flotte_id') is-invalid @enderror"
                                        id="flotte_id"
                                        name="flotte_id">
                                    <option value="">{{ __('messages.select_option') }}</option>
                                    @foreach(($flottes ?? collect()) as $flotte)
                                        <option value="{{ $flotte->id }}" {{ (int) old('flotte_id', $formation->flotte_id) === $flotte->id ? 'selected' : '' }}>
                                            {{ $flotte->name }}
                                        </option>
                                    @endforeach
                                </select>
                                @error('flotte_id')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="mb-3 form-check">
                                <input type="checkbox" 
                                       class="form-check-input" 
                                       id="is_active" 
                                       name="is_active" 
                                       value="1" 
                                       {{ old('is_active', $formation->is_active) ? 'checked' : '' }}>
                                <label class="form-check-label" for="is_active">
                                    {{ __('messages.formation_active') }}
                                </label>
                            </div>
                            <div class="mb-3 form-check">
                                <input type="checkbox" 
                                       class="form-check-input" 
                                       id="obligatoire" 
                                       name="obligatoire" 
                                       value="1" 
                                       {{ old('obligatoire', $formation->obligatoire) ? 'checked' : '' }}>
                                <label class="form-check-label" for="obligatoire">
                                    {{ __('messages.obligatoire') }}
                                </label>
                            </div>

                            <hr class="my-3">

                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label for="reference_value" class="form-label">
                                        {{ __('messages.reference_duration') }}
                                    </label>
                                    <div class="input-group">
                                        <input type="number"
                                               class="form-control @error('reference_value') is-invalid @enderror"
                                               id="reference_value"
                                               name="reference_value"
                                               min="1"
                                               value="{{ old('reference_value', $formation->reference_value) }}">
                                        <select class="form-select @error('reference_unit') is-invalid @enderror"
                                                id="reference_unit"
                                                name="reference_unit">
                                            <option value="">{{ __('messages.select_option') }}</option>
                                            <option value="months" {{ old('reference_unit', $formation->reference_unit) === 'months' ? 'selected' : '' }}>
                                                {{ __('messages.months') ?? 'Months' }}
                                            </option>
                                            <option value="years" {{ old('reference_unit', $formation->reference_unit) === 'years' ? 'selected' : '' }}>
                                                {{ __('messages.years') ?? 'Years' }}
                                            </option>
                                        </select>
                                    </div>
                                    @error('reference_value')
                                        <div class="invalid-feedback d-block">{{ $message }}</div>
                                    @enderror
                                    @error('reference_unit')
                                        <div class="invalid-feedback d-block">{{ $message }}</div>
                                    @enderror
                                    <small class="form-text text-muted">
                                        {{ __('messages.reference_duration_hint') ?? 'Define the validity period of the formation.' }}
                                    </small>
                                </div>

                                <div class="col-md-6 mb-3">
                                    <label class="form-label">{{ __('messages.warning_alert') ?? 'Warning Alert' }}</label>
                                    <div class="row g-2">
                                        <div class="col">
                                            <input type="number"
                                                   class="form-control @error('warning_alert_percent') is-invalid @enderror"
                                                   id="warning_alert_percent"
                                                   name="warning_alert_percent"
                                                   min="0"
                                                   max="100"
                                                   value="{{ old('warning_alert_percent', $formation->warning_alert_percent) }}"
                                                   placeholder="%">
                                            @error('warning_alert_percent')
                                                <div class="invalid-feedback">{{ $message }}</div>
                                            @enderror
                                        </div>
                                        <div class="col">
                                            <input type="number"
                                                   class="form-control @error('warning_alert_days') is-invalid @enderror"
                                                   id="warning_alert_days"
                                                   name="warning_alert_days"
                                                   min="0"
                                                   value="{{ old('warning_alert_days', $formation->warning_alert_days) }}"
                                                   placeholder="{{ __('messages.days') ?? 'Days' }}">
                                            @error('warning_alert_days')
                                                <div class="invalid-feedback">{{ $message }}</div>
                                            @enderror
                                        </div>
                                    </div>
                                    <small class="form-text text-muted">
                                        {{ __('messages.warning_alert_hint') ?? 'Percentage of period elapsed and/or days before expiry to trigger a warning.' }}
                                    </small>
                                </div>
                            </div>

                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label class="form-label">{{ __('messages.critical_alert') ?? 'Critical Alert' }}</label>
                                    <div class="row g-2">
                                        <div class="col">
                                            <input type="number"
                                                   class="form-control @error('critical_alert_percent') is-invalid @enderror"
                                                   id="critical_alert_percent"
                                                   name="critical_alert_percent"
                                                   min="0"
                                                   max="100"
                                                   value="{{ old('critical_alert_percent', $formation->critical_alert_percent) }}"
                                                   placeholder="%">
                                            @error('critical_alert_percent')
                                                <div class="invalid-feedback">{{ $message }}</div>
                                            @enderror
                                        </div>
                                        <div class="col">
                                            <input type="number"
                                                   class="form-control @error('critical_alert_days') is-invalid @enderror"
                                                   id="critical_alert_days"
                                                   name="critical_alert_days"
                                                   min="0"
                                                   value="{{ old('critical_alert_days', $formation->critical_alert_days) }}"
                                                   placeholder="{{ __('messages.days') ?? 'Days' }}">
                                            @error('critical_alert_days')
                                                <div class="invalid-feedback">{{ $message }}</div>
                                            @enderror
                                        </div>
                                    </div>
                                    <small class="form-text text-muted">
                                        {{ __('messages.critical_alert_hint') ?? 'Percentage of period elapsed and/or days before expiry to trigger a critical alert.' }}
                                    </small>
                                </div>
                            </div>

                            <hr class="mb-3">

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

