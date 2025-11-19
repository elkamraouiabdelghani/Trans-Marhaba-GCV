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
        <div class="card border-0 shadow-sm mb-4">
            <div class="card-header bg-white border-0 py-3">
                <form method="GET" action="{{ route('tbt-formations.index') }}">
                    <div class="row g-3 align-items-end">
                        <div class="col-md-3">
                            <label for="year" class="form-label small">{{ __('messages.tbt_formation_year') }}</label>
                            <select name="year" id="year" class="form-select form-select-sm">
                                <option value="">{{ __('messages.tbt_formation_all_years') }}</option>
                                @foreach($years ?? [] as $y)
                                    <option value="{{ $y }}" {{ request('year') == (string)$y ? 'selected' : '' }}>
                                        {{ $y }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-3">
                            <label for="month" class="form-label small">{{ __('messages.tbt_formation_month') }}</label>
                            <select name="month" id="month" class="form-select form-select-sm">
                                <option value="">{{ __('messages.tbt_formation_all_months') }}</option>
                                @for($m = 1; $m <= 12; $m++)
                                    <option value="{{ $m }}" {{ request('month') == (string)$m ? 'selected' : '' }}>
                                        {{ \Carbon\Carbon::create(null, $m, 1)->locale('fr')->monthName }}
                                    </option>
                                @endfor
                            </select>
                        </div>
                        <div class="col-md-2">
                            <label for="active" class="form-label small">{{ __('messages.tbt_formation_status') }}</label>
                            <select name="active" id="active" class="form-select form-select-sm">
                                <option value="">{{ __('messages.tbt_formation_all_status') }}</option>
                                <option value="1" {{ request('active') == '1' ? 'selected' : '' }}>{{ __('messages.tbt_formation_active_status') }}</option>
                                <option value="0" {{ request('active') == '0' ? 'selected' : '' }}>{{ __('messages.tbt_formation_inactive_status') }}</option>
                            </select>
                        </div>
                        <div class="col-md-2 d-flex align-items-end">
                            <button type="submit" class="btn btn-primary btn-sm w-100">
                                <i class="bi bi-search"></i> {{ __('messages.tbt_formation_filter') }}
                            </button>
                        </div>
                        <div class="col-md-2 d-flex align-items-end">
                            <a href="{{ route('tbt-formations.index') }}" class="btn btn-outline-secondary btn-sm w-100">
                                <i class="bi bi-x-circle me-1"></i> {{ __('messages.tbt_formation_reset') }}
                            </a>
                        </div>
                    </div>
                </form>
            </div>
        </div>

        <div class="card border-0 shadow-sm">
            <div class="card-header bg-white border-0 py-3">
                <div class="d-flex justify-content-between align-items-center">
                    <h5 class="mb-0 text-dark fw-bold">
                        <i class="bi bi-calendar-week me-2 text-primary"></i>
                        {{ __('messages.tbt_formations_title') }}
                    </h5>
                    <div class="d-flex gap-2">
                        <a href="{{ route('tbt-formations.planning') }}" class="btn btn-outline-primary btn-sm">
                            <i class="bi bi-calendar3 me-1"></i>
                            {{ __('messages.tbt_formation_planning_annual') }}
                        </a>
                        <a href="{{ route('tbt-formations.create') }}" class="btn btn-dark btn-sm">
                            <i class="bi bi-plus-circle me-1"></i>
                            {{ __('messages.tbt_formation_add') }}
                        </a>
                    </div>
                </div>
            </div>
            <div class="card-body">
                @if($formations->count() > 0)
                    <div class="table-responsive">
                        <table class="table table-hover align-middle">
                            <thead class="table-light">
                                <tr>
                                    <th>{{ __('messages.tbt_formation_code') }}</th>
                                    <th>{{ __('messages.tbt_formation_title') }}</th>
                                    <th>{{ __('messages.tbt_formation_year') }}</th>
                                    <th>{{ __('messages.tbt_formation_month') }}</th>
                                    <th>{{ __('messages.tbt_formation_week') }}</th>
                                    <th>{{ __('messages.tbt_formation_status') }}</th>
                                    <th class="text-end">{{ __('messages.actions') }}</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($formations as $formation)
                                    <tr>
                                        <td>
                                            <span class="badge bg-secondary">{{ $formation->code }}</span>
                                        </td>
                                        <td>
                                            <strong>{{ $formation->title }}</strong>
                                            @if($formation->description)
                                                <br><small class="text-muted">{{ Str::limit($formation->description, 50) }}</small>
                                            @endif
                                        </td>
                                        <td>{{ $formation->year }}</td>
                                        <td>
                                            {{ \Carbon\Carbon::create($formation->year, $formation->month, 1)->locale('fr')->monthName }}
                                        </td>
                                        <td>
                                            <small>
                                                {{ \Carbon\Carbon::parse($formation->week_start_date)->format('d/m') }} - 
                                                {{ \Carbon\Carbon::parse($formation->week_end_date)->format('d/m') }}
                                            </small>
                                        </td>
                                        <td>
                                            @if($formation->is_active)
                                                <span class="badge bg-success">{{ __('messages.tbt_formation_active_status') }}</span>
                                            @else
                                                <span class="badge bg-secondary">{{ __('messages.tbt_formation_inactive_status') }}</span>
                                            @endif
                                        </td>
                                        <td class="text-end">
                                            <div class="btn-group btn-group-sm">
                                                <a href="{{ route('tbt-formations.edit', $formation) }}" class="btn btn-outline-primary" title="{{ __('messages.tbt_formation_edit') }}">
                                                    <i class="bi bi-pencil"></i>
                                                </a>
                                            </div>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                    <div class="d-flex justify-content-center mt-3">
                        {{ $formations->links() }}
                    </div>
                @else
                    <div class="text-center py-5">
                        <i class="bi bi-inbox" style="font-size: 3rem; color: #ccc;"></i>
                        <p class="text-muted mt-3">{{ __('messages.tbt_formation_no_formations') }}</p>
                        <a href="{{ route('tbt-formations.create') }}" class="btn btn-dark mt-2">
                            <i class="bi bi-plus-circle me-1"></i>
                            {{ __('messages.tbt_formation_create_first') }}
                        </a>
                    </div>
                @endif
            </div>
        </div>
    </div>
</x-app-layout>



