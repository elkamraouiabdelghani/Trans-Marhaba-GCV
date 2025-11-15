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
                            <select name="driver_id" id="driver_id" class="form-select @error('driver_id') is-invalid @enderror" required disabled>
                                <option value="">{{ __('messages.select_driver') ?? 'Sélectionner un chauffeur' }}</option>
                                @foreach($drivers as $driver)
                                    <option value="{{ $driver->id }}" {{ old('driver_id', $coachingCabine->driver_id) == $driver->id ? 'selected' : '' }}>
                                        {{ $driver->full_name }}
                                    </option>
                                @endforeach
                            </select>
                            <input type="hidden" name="driver_id" value="{{ old('driver_id', $coachingCabine->driver_id) }}">
                            @error('driver_id')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="col-md-6">
                            <label for="flotte_id" class="form-label fw-semibold">{{ __('messages.flotte') }}</label>
                            <select name="flotte_id" id="flotte_id" class="form-select @error('flotte_id') is-invalid @enderror" disabled>
                                <option value="">{{ __('messages.all_flottes') }}</option>
                                @foreach($flottes as $flotte)
                                    <option value="{{ $flotte->id }}" {{ old('flotte_id', $coachingCabine->flotte_id) == $flotte->id ? 'selected' : '' }}>
                                        {{ $flotte->name }}
                                    </option>
                                @endforeach
                            </select>
                            <input type="hidden" name="flotte_id" value="{{ old('flotte_id', $coachingCabine->flotte_id ?? '') }}">
                            @error('flotte_id')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="col-md-6">
                            <label for="date" class="form-label fw-semibold">{{ __('messages.from_date') }} <span class="text-danger">*</span></label>
                            <input type="date" name="date" id="date" class="form-control @error('date') is-invalid @enderror" value="{{ old('date', $coachingCabine->date?->format('Y-m-d')) }}" required readonly>
                            @error('date')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="col-md-6">
                            <label for="date_fin" class="form-label fw-semibold">{{ __('messages.date_fin') }} <span class="text-danger">*</span></label>
                            <input type="date" name="date_fin" id="date_fin" class="form-control @error('date_fin') is-invalid @enderror" value="{{ old('date_fin', $coachingCabine->date_fin?->format('Y-m-d')) }}" required>
                            <small class="text-muted">{{ __('messages.date_fin_auto') ?? 'Calculé automatiquement (date + 3 jours)' }}</small>
                            @error('date_fin')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="col-md-6">
                            <label for="type" class="form-label fw-semibold">{{ __('messages.type') ?? 'Type' }} <span class="text-danger">*</span></label>
                            <select name="type" id="type" class="form-select @error('type') is-invalid @enderror" required disabled>
                                <option value="initial" {{ old('type', $coachingCabine->type) == 'initial' ? 'selected' : '' }}>{{ __('messages.type_initial') }}</option>
                                <option value="suivi" {{ old('type', $coachingCabine->type) == 'suivi' ? 'selected' : '' }}>{{ __('messages.type_suivi') }}</option>
                                <option value="correctif" {{ old('type', $coachingCabine->type) == 'correctif' ? 'selected' : '' }}>{{ __('messages.type_correctif') }}</option>
                            </select>
                            <input type="hidden" name="type" value="{{ old('type', $coachingCabine->type) }}">
                            @error('type')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="col-md-6">
                            <label for="moniteur" class="form-label fw-semibold">{{ __('messages.moniteur') }}</label>
                            <input type="text" name="moniteur" id="moniteur" class="form-control @error('moniteur') is-invalid @enderror" value="{{ old('moniteur', $coachingCabine->moniteur) }}">
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

                        <div class="col-12">
                            <label for="route_taken" class="form-label fw-semibold">{{ __('messages.route_taken') }}</label>
                            <textarea name="route_taken" id="route_taken" rows="3" class="form-control @error('route_taken') is-invalid @enderror">{{ old('route_taken', $coachingCabine->route_taken) }}</textarea>
                            @error('route_taken')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
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
</x-app-layout>

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function() {
        const dateInput = document.getElementById('date');
        const dateFinInput = document.getElementById('date_fin');

        // Auto-calculate date_fin when date changes
        dateInput.addEventListener('change', function() {
            if (this.value) {
                const date = new Date(this.value);
                date.setDate(date.getDate() + 3);
                const dateFin = date.toISOString().split('T')[0];
                dateFinInput.value = dateFin;
            }
        });
    });
</script>
@endpush

