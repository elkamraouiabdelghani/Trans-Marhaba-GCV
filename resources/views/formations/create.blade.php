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
            <div class="col-lg-10">
                <div class="card border-0 shadow-sm">
                    <div class="card-header bg-white border-0 py-3">
                        <div class="d-flex justify-content-between align-items-center">
                            <h5 class="mb-0 text-dark fw-bold">
                                <i class="bi bi-plus-circle me-2 text-primary"></i>
                                {{ __('messages.create_formation') }}
                            </h5>
                            <a href="{{ route('formations.index') }}" class="btn btn-outline-secondary">
                                <i class="bi bi-x-circle me-1"></i>
                                {{ __('messages.cancel') }}
                            </a>
                        </div>
                    </div>
                    <div class="card-body">
                        <form action="{{ route('formations.store') }}" method="POST">
                            @csrf

                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label for="theme" class="form-label">{{ __('messages.formation_theme') }}</label>
                                    <input type="text"
                                        class="form-control @error('theme') is-invalid @enderror"
                                        id="theme"
                                        name="theme"
                                        value="{{ old('theme') }}"
                                        required>
                                    @error('theme')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>

                                <div class="col-md-6 mb-3">
                                    <label for="type" class="form-label">{{ __('messages.formation_type_label') }}</label>
                                    @php
                                        $defaultType = old('type', array_key_first($typeOptions ?? []) ?? null);
                                    @endphp
                                    <select class="form-select @error('type') is-invalid @enderror"
                                            id="type"
                                            name="type"
                                            required>
                                        @foreach(($typeOptions ?? []) as $value => $label)
                                            <option value="{{ $value }}" {{ $defaultType === $value ? 'selected' : '' }}>
                                                {{ $label }}
                                            </option>
                                        @endforeach
                                    </select>
                                    @error('type')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>

                                <div class="col-md-6 mb-3">
                                    <label for="delivery_type" class="form-label">
                                        {{ __('messages.formation_delivery_type') }}
                                    </label>
                                    <select class="form-select @error('delivery_type') is-invalid @enderror"
                                            id="delivery_type"
                                            name="delivery_type"
                                            required>
                                        <option value="interne" {{ old('delivery_type', 'interne') === 'interne' ? 'selected' : '' }}>
                                            {{ __('messages.formation_delivery_internal') }}
                                        </option>
                                        <option value="externe" {{ old('delivery_type') === 'externe' ? 'selected' : '' }}>
                                            {{ __('messages.formation_delivery_external') }}
                                        </option>
                                    </select>
                                    @error('delivery_type')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                    <small class="form-text text-muted">
                                        {{ __('messages.formation_delivery_type_hint') }}
                                    </small>
                                </div>
    
                                <div class="col-md-6 mb-3">
                                    <label for="flotte_id" class="form-label">{{ __('messages.flotte') }}</label>
                                    <select class="form-select @error('flotte_id') is-invalid @enderror"
                                            id="flotte_id"
                                            name="flotte_id">
                                        <option value="">{{ __('messages.select_option') }}</option>
                                        @foreach(($flottes ?? collect()) as $flotte)
                                            <option value="{{ $flotte->id }}" {{ (int) old('flotte_id') === $flotte->id ? 'selected' : '' }}>
                                                {{ $flotte->name }}
                                            </option>
                                        @endforeach
                                    </select>
                                    @error('flotte_id')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>

                                <div class="col-md-6 mb-3">
                                    <label for="realizing_date" class="form-label">{{ __('messages.formation_realizing_date') }}</label>
                                    <input type="date"
                                           class="form-control @error('realizing_date') is-invalid @enderror"
                                           id="realizing_date"
                                           name="realizing_date"
                                           value="{{ old('realizing_date') }}">
                                    @error('realizing_date')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>

                                <div class="col-md-6 mb-3">
                                    <label for="duree" class="form-label">{{ __('messages.formation_duration') }}</label>
                                    <input type="number"
                                           class="form-control @error('duree') is-invalid @enderror"
                                           id="duree"
                                           name="duree"
                                           min="0"
                                           value="{{ old('duree') }}">
                                    @error('duree')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                    <small class="form-text text-muted">
                                        {{ __('messages.formation_duration_hint') }}
                                    </small>
                                </div>

                                <div class="col-md-6 mb-3">
                                    <label for="status" class="form-label">{{ __('messages.formation_progress_status') }}</label>
                                    <select class="form-select @error('status') is-invalid @enderror"
                                            id="status"
                                            name="status">
                                        <option value="planned" {{ old('status', 'planned') === 'planned' ? 'selected' : '' }}>
                                            {{ __('messages.planned') }}
                                        </option>
                                        <option value="realized" {{ old('status') === 'realized' ? 'selected' : '' }}>
                                            {{ __('messages.realized') }}
                                        </option>
                                    </select>
                                    @error('status')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>


                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label for="participant" class="form-label">{{ __('messages.formation_participant') }}</label>
                                    <textarea
                                        class="form-control @error('participant') is-invalid @enderror"
                                        id="participant"
                                        name="participant"
                                        rows="3">{{ old('participant') }}</textarea>
                                    @error('participant')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label for="organisme" class="form-label">{{ __('messages.formation_organisme') }}</label>
                                    <textarea
                                        class="form-control @error('organisme') is-invalid @enderror"
                                        id="organisme"
                                        name="organisme"
                                        rows="3">{{ old('organisme') }}</textarea>
                                    @error('organisme')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>

                            <div class="mb-3">
                                <label for="description" class="form-label">{{ __('messages.formation_description') }}</label>
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
                                    {{ __('messages.formation_active') }}
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
                                               value="{{ old('reference_value') }}">
                                        <select class="form-select @error('reference_unit') is-invalid @enderror"
                                                id="reference_unit"
                                                name="reference_unit">
                                            <option value="">{{ __('messages.select_option') }}</option>
                                            <option value="months" {{ old('reference_unit') === 'months' ? 'selected' : '' }}>
                                                {{ __('messages.months') ?? 'Months' }}
                                            </option>
                                            <option value="years" {{ old('reference_unit') === 'years' ? 'selected' : '' }}>
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
                                    <input type="number"
                                           class="form-control @error('warning_alert_percent') is-invalid @enderror"
                                           id="warning_alert_percent"
                                           name="warning_alert_percent"
                                           min="0"
                                           max="100"
                                           value="{{ old('warning_alert_percent') }}"
                                           placeholder="%">
                                    @error('warning_alert_percent')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                    <small class="form-text text-muted">
                                        {{ __('messages.warning_alert_hint') ?? 'Percentage of period elapsed to trigger a warning.' }}
                                    </small>
                                </div>
                            </div>

                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label class="form-label">{{ __('messages.critical_alert') ?? 'Critical Alert' }}</label>
                                    <input type="number"
                                           class="form-control @error('critical_alert_percent') is-invalid @enderror"
                                           id="critical_alert_percent"
                                           name="critical_alert_percent"
                                           min="0"
                                           max="100"
                                           value="{{ old('critical_alert_percent') }}"
                                           placeholder="%">
                                    @error('critical_alert_percent')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                    <small class="form-text text-muted">
                                        {{ __('messages.critical_alert_hint') ?? 'Percentage of period elapsed to trigger a critical alert.' }}
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

