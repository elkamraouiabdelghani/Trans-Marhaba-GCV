<x-app-layout>
    <x-slot name="header">
        @include('layouts.topnav')
    </x-slot>

    <!-- Toast Container -->
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
    </div>

    <div class="container-fluid py-4 mt-4">
        <div class="row justify-content-center">
            <div class="col-lg-8">
                <div class="card border-0 shadow-sm">
                    <div class="card-header bg-white border-0 py-3">
                        <div class="d-flex justify-content-between align-items-center">
                            <h5 class="mb-0 text-dark fw-bold">
                                <i class="bi bi-plus-circle me-2 text-primary"></i>
                                {{ __('messages.tbt_formations_create_title') }}
                            </h5>
                            <a href="{{ route('tbt-formations.index') }}" class="btn btn-outline-secondary">
                                <i class="bi bi-x-circle me-1"></i>
                                {{ __('messages.tbt_formation_cancel') }}
                            </a>
                        </div>
                    </div>
                    <div class="card-body">
                        <form action="{{ route('tbt-formations.store') }}" method="POST">
                            @csrf
                            @if(request('year'))
                                <input type="hidden" name="from_planning" value="1">
                            @endif

                            <div class="mb-3">
                                <label for="title" class="form-label">{{ __('messages.tbt_formation_title') }} <span class="text-danger">*</span></label>
                                <input type="text" 
                                       class="form-control @error('title') is-invalid @enderror" 
                                       id="title" 
                                       name="title" 
                                       value="{{ old('title') }}" 
                                       required>
                                @error('title')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="mb-3">
                                <label for="code" class="form-label">{{ __('messages.tbt_formation_code') }} <span class="text-danger">*</span></label>
                                <input type="text" 
                                       class="form-control @error('code') is-invalid @enderror" 
                                       id="code" 
                                       name="code" 
                                       value="{{ old('code') }}" 
                                       required>
                                @error('code')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                                <small class="form-text text-muted">{{ __('messages.tbt_formation_code_uppercase_hint') }}</small>
                            </div>

                            <div class="mb-3">
                                <label for="description" class="form-label">{{ __('messages.tbt_formation_description') }}</label>
                                <textarea class="form-control @error('description') is-invalid @enderror" 
                                          id="description" 
                                          name="description" 
                                          rows="3">{{ old('description') }}</textarea>
                                @error('description')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <hr class="my-3">
                            <h6 class="mb-3">{{ __('messages.tbt_formation_planning') }}</h6>

                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label for="year" class="form-label">{{ __('messages.tbt_formation_year') }} <span class="text-danger">*</span></label>
                                    <input type="number" 
                                           class="form-control @error('year') is-invalid @enderror" 
                                           id="year" 
                                           name="year" 
                                           value="{{ old('year', $year ?? date('Y')) }}" 
                                           min="2000" 
                                           max="2100"
                                           required>
                                    @error('year')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>

                                <div class="col-md-6 mb-3">
                                    <label for="month" class="form-label">{{ __('messages.tbt_formation_month') }} <span class="text-danger">*</span></label>
                                    <select class="form-select @error('month') is-invalid @enderror" 
                                            id="month" 
                                            name="month"
                                            required>
                                        <option value="">{{ __('messages.tbt_formation_select_month') }}</option>
                                        @for($m = 1; $m <= 12; $m++)
                                            <option value="{{ $m }}" {{ old('month', $month ?? null) == $m ? 'selected' : '' }}>
                                                {{ \Carbon\Carbon::create(null, $m, 1)->locale('fr')->monthName }}
                                            </option>
                                        @endfor
                                    </select>
                                    @error('month')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>

                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label for="week_start_date" class="form-label">{{ __('messages.tbt_formation_week_start') }} <span class="text-danger">*</span></label>
                                    <input type="date" 
                                           class="form-control @error('week_start_date') is-invalid @enderror" 
                                           id="week_start_date" 
                                           name="week_start_date" 
                                           value="{{ old('week_start_date', $weekStartDate ?? '') }}" 
                                           required>
                                    @error('week_start_date')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                    <small class="form-text text-muted">{{ __('messages.tbt_formation_week_monday') }}</small>
                                </div>

                                <div class="col-md-6 mb-3">
                                    <label for="week_end_date" class="form-label">{{ __('messages.tbt_formation_week_end') }} <span class="text-danger">*</span></label>
                                    <input type="date" 
                                           class="form-control @error('week_end_date') is-invalid @enderror" 
                                           id="week_end_date" 
                                           name="week_end_date" 
                                           value="{{ old('week_end_date', $weekEndDate ?? '') }}" 
                                           required>
                                    @error('week_end_date')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                    <small class="form-text text-muted">{{ __('messages.tbt_formation_week_sunday') }}</small>
                                </div>
                            </div>

                            <div class="mb-3">
                                <label for="notes" class="form-label">{{ __('messages.tbt_formation_notes') }}</label>
                                <textarea class="form-control @error('notes') is-invalid @enderror" 
                                          id="notes" 
                                          name="notes" 
                                          rows="2">{{ old('notes') }}</textarea>
                                @error('notes')
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
                                    {{ __('messages.tbt_formation_active') }}
                                </label>
                            </div>

                            <hr class="mb-3">
                            <div class="d-flex gap-2 justify-content-center align-items-center">
                                <button type="submit" class="btn btn-dark px-4">
                                    <i class="bi bi-check-circle me-1"></i>
                                    {{ __('messages.tbt_formation_save') }}
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script>
        // Auto-update month when week_start_date changes
        document.getElementById('week_start_date').addEventListener('change', function() {
            const startDate = new Date(this.value);
            if (startDate && !isNaN(startDate.getTime())) {
                const month = startDate.getMonth() + 1; // JavaScript months are 0-indexed
                document.getElementById('month').value = month;
            }
        });
    </script>
</x-app-layout>

