<x-app-layout>
    <x-slot name="header">
        @include('layouts.topnav')
    </x-slot>

    <div class="container-fluid py-4 mt-4">

        {{-- Stats Cards Section --}}
        <div class="row">
            <div class="col-md-3 mb-4">
                <div class="card border-0 shadow-sm h-100">
                    <div class="card-body">
                        <div class="d-flex align-items-center">
                            <div class="flex-shrink-0">
                                <div class="bg-primary bg-opacity-10 rounded-circle d-flex align-items-center justify-content-center" style="width: 60px; height: 60px;">
                                    <i class="bi bi-collection text-primary" style="font-size: 1.5rem;"></i>
                                </div>
                            </div>
                            <div class="flex-grow-1 ms-3">
                                <h6 class="text-muted mb-1 small">
                                    {{ __('messages.total_formations') }} ({{ $yearlyStats['year'] ?? now()->year }})
                                </h6>
                                <h3 class="mb-0 fw-bold text-dark">{{ $yearlyStats['total'] ?? 0 }}</h3>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-3 mb-4">
                <div class="card border-0 shadow-sm h-100">
                    <div class="card-body">
                        <div class="d-flex align-items-center">
                            <div class="flex-shrink-0">
                                <div class="bg-warning bg-opacity-10 rounded-circle d-flex align-items-center justify-content-center" style="width: 60px; height: 60px;">
                                    <i class="bi bi-calendar-week text-warning" style="font-size: 1.5rem;"></i>
                                </div>
                            </div>
                            <div class="flex-grow-1 ms-3">
                                <h6 class="text-muted mb-1 small">{{ __('messages.planned_formations') }}</h6>
                                <h3 class="mb-0 fw-bold text-dark">{{ $yearlyStats['planned'] ?? 0 }}</h3>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-3 mb-4">
                <div class="card border-0 shadow-sm h-100">
                    <div class="card-body">
                        <div class="d-flex align-items-center">
                            <div class="flex-shrink-0">
                                <div class="bg-success bg-opacity-10 rounded-circle d-flex align-items-center justify-content-center" style="width: 60px; height: 60px;">
                                    <i class="bi bi-check2-circle text-success" style="font-size: 1.5rem;"></i>
                                </div>
                            </div>
                            <div class="flex-grow-1 ms-3">
                                <h6 class="text-muted mb-1 small">{{ __('messages.realized_formations') }}</h6>
                                <h3 class="mb-0 fw-bold text-dark">{{ $yearlyStats['realized'] ?? 0 }}</h3>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-3 mb-4">
                <div class="card border-0 shadow-sm h-100">
                    <div class="card-body">
                        <div class="d-flex align-items-center">
                            <div class="flex-shrink-0">
                                <div class="bg-info bg-opacity-10 rounded-circle d-flex align-items-center justify-content-center" style="width: 60px; height: 60px;">
                                    <i class="bi bi-percent text-info" style="font-size: 1.5rem;"></i>
                                </div>
                            </div>
                            <div class="flex-grow-1 ms-3">
                                <h6 class="text-muted mb-1 small">{{ __('messages.realized_percentage') }}</h6>
                                <h3 class="mb-0 fw-bold text-dark">{{ $yearlyStats['percentage'] ?? 0 }}%</h3>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- filter bar section --}}
        <div class="card border-0 shadow-sm mb-4">
            <div class="card-header bg-white border-0 py-3">
                <div class="d-flex justify-content-between align-items-center">
                    <h6 class="mb-0 text-dark fw-bold">
                        <i class="bi bi-funnel me-2 text-primary"></i>
                        {{ __('messages.filters') }}
                    </h6>
                    <div>
                        <a href="{{ route('formations.planning') }}" class="btn btn-sm btn-dark" title="{{ __('messages.planning') }}">
                            <i class="bi bi-calendar-date me-1"></i>
                            {{ __('messages.planning') }}
                        </a>
                        <a href="{{ route('drivers.alerts') }}" class="btn btn-sm btn-warning" title="{{ __('messages.alerts') }}">
                            <i class="bi bi-bell-fill me-1"></i>
                            {{ __('messages.alerts') }}
                        </a>
                    </div>
                </div>
            </div>
            <div class="card-body">
                <form method="GET" action="{{ route('formations.index') }}" id="filterForm">
                    <div class="row g-3">
                        <div class="col-md-2">
                            <label for="driver" class="form-label small">{{ __('messages.driver') }}</label>
                            <select name="driver" id="driver" class="form-select form-select-sm">
                                <option value="">{{ __('messages.all_drivers') }}</option>
                                @foreach($integratedDrivers ?? [] as $driver)
                                    <option value="{{ $driver->id }}" {{ request('driver') == $driver->id ? 'selected' : '' }}>
                                        {{ $driver->full_name }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-2">
                            <label for="flotte" class="form-label small">{{ __('messages.flotte') }}</label>
                            <select name="flotte" id="flotte" class="form-select form-select-sm">
                                <option value="">{{ __('messages.all_flottes') }}</option>
                                @foreach($flottes ?? [] as $flotte)
                                    <option value="{{ $flotte->id }}" {{ ($selectedFlotte ?? request('flotte')) == $flotte->id ? 'selected' : '' }}>
                                        {{ $flotte->name }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-2">
                            <label for="status" class="form-label small">{{ __('messages.status') }}</label>
                            <select name="status" id="status" class="form-select form-select-sm">
                                <option value="">{{ __('messages.all_status') }}</option>
                                <option value="planned" {{ request('status') == 'planned' ? 'selected' : '' }}>
                                    {{ __('messages.planned') }}
                                </option>
                                <option value="realized" {{ request('status') == 'realized' ? 'selected' : '' }}>
                                    {{ __('messages.realized') }}
                                </option>
                            </select>
                        </div>
                        <div class="col-md-2">
                            <label for="type" class="form-label small">{{ __('messages.formation_type_label') }}</label>
                            <select name="type" id="type" class="form-select form-select-sm">
                                <option value="">{{ __('messages.all_formation_types') }}</option>
                                @foreach($typeOptions ?? [] as $value => $label)
                                    <option value="{{ $value }}" {{ request('type', $selectedFormationType ?? '') === $value ? 'selected' : '' }}>
                                        {{ $label }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-2">
                            <label for="theme" class="form-label small">{{ __('messages.theme') }}</label>
                            <select name="theme" id="theme" class="form-select form-select-sm">
                                <option value="">{{ __('messages.all_themes') }}</option>
                                @foreach($themes ?? [] as $theme)
                                    <option value="{{ $theme }}" {{ request('theme', $selectedTheme ?? '') === $theme ? 'selected' : '' }}>
                                        {{ $theme }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-2">
                            <label for="year" class="form-label small">{{ __('messages.year') }}</label>
                            @php
                                $yearValue = request('year', $selectedYear ?? now()->year);
                            @endphp
                            <select name="year" id="year" class="form-select form-select-sm">
                                <option value="">{{ __('messages.all_years') }}</option>
                                @foreach($years ?? [] as $year)
                                    <option value="{{ $year }}" {{ (string)$yearValue === (string)$year ? 'selected' : '' }}>
                                        {{ $year }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-1 d-flex align-items-end">
                            <button type="submit" class="btn btn-primary btn-sm w-100">
                                <i class="bi bi-search"></i>
                            </button>
                            @if($hasFilters ?? false)
                                <div class="row mt-2">
                                    <div class="col-12">
                                        <a href="{{ route('formations.index') }}" class="btn btn-outline-secondary btn-sm">
                                            <i class="bi bi-x-circle"></i>
                                        </a>
                                    </div>
                                </div>
                            @endif
                        </div>
                    </div>
                </form>
            </div>
        </div>

        <div class="card border-0 shadow-sm">
            <div class="card-header bg-white border-0 py-3">
                <div class="d-flex justify-content-between align-items-center">
                    <h5 class="mb-0 text-dark fw-bold">
                        <i class="bi bi-book me-2 text-primary"></i>
                        {{ __('messages.formations_title') }}
                    </h5>
                    <div class="d-flex gap-2 justify-content-end align-items-center">
                        <div class="d-flex justify-content-end align-items-center w-auto" style="width: max-content;">
                            <div class="input-group input-group-sm">
                                <span class="input-group-text">
                                    <i class="bi bi-search"></i>
                                </span>
                                <input type="text" class="form-control" id="ftSearch" placeholder="{{ __('messages.search_by_name') }}" onkeyup="filterFormations()">
                            </div>
                        </div>
                        <a href="{{ route('formations.create') }}" class="btn btn-dark btn-sm">
                            <i class="bi bi-plus-circle me-1"></i>
                            {{ __('messages.add_formation') }}
                        </a>
                    </div>
                </div>
            </div>

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

            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover mb-0 align-middle" id="formationsTable">
                        <thead class="table-light">
                            <tr>
                                <th class="ps-3">{{ __('messages.formation_theme') }}</th>
                                <th>{{ __('messages.formation_type_label') }}</th>
                                <th>{{ __('messages.flotte') }}</th>
                                <th>{{ __('messages.formation_delivery_type') }}</th>
                                <th>{{ __('messages.formation_progress_status') }}</th>
                                <th>{{ __('messages.formation_realizing_date') }}</th>
                                <th>{{ __('messages.formation_duration') }}</th>
                                <th>{{ __('messages.formation_status') }}</th>
                                <th class="text-end pe-3">{{ __('messages.formation_actions') }}</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($formations as $formation)
                                <tr>
                                    <td class="ps-3">
                                        <strong>{{ $formation->theme }}</strong>
                                    </td>
                                    <td>
                                        <span class="badge bg-light text-dark">
                                            {{ $formation->type_label ?? __('messages.not_available') }}
                                        </span>
                                    </td>
                                    <td>
                                        @if($formation->flotte)
                                            <span class="badge bg-primary bg-opacity-25 text-primary">
                                                {{ $formation->flotte->name }}
                                            </span>
                                        @else
                                            <span class="badge bg-secondary bg-opacity-25 text-secondary">
                                                {{ __('messages.not_assigned') }}
                                            </span>
                                        @endif
                                    </td>
                                    <td>
                                        @if($formation->delivery_type === 'externe')
                                            <span class="badge bg-warning bg-opacity-25 text-warning">
                                                {{ __('messages.formation_delivery_external') }}
                                            </span>
                                        @else
                                            <span class="badge bg-primary bg-opacity-25 text-primary">
                                                {{ __('messages.formation_delivery_internal') }}
                                            </span>
                                        @endif
                                    </td>
                                    <td>
                                        @if($formation->status === 'realized')
                                            <span class="badge bg-success bg-opacity-25 text-success">{{ __('messages.realized') }}</span>
                                        @else
                                            <span class="badge bg-warning bg-opacity-25 text-warning">{{ __('messages.planned') }}</span>
                                        @endif
                                    </td>
                                    <td>
                                        {{ optional($formation->realizing_date)->format('d-m-Y') ?? '---' }}
                                    </td>
                                    <td>
                                        @if(!is_null($formation->duree))
                                            <span class="badge bg-primary bg-opacity-10 text-primary">
                                                {{ $formation->duree }} {{ __('messages.days_short') }}
                                            </span>
                                        @else
                                            <span class="text-muted">---</span>
                                        @endif
                                    </td>
                                    <td>
                                        @if($formation->is_active)
                                            <span class="badge bg-success bg-opacity-25 text-success">{{ __('messages.formation_active') }}</span>
                                        @else
                                            <span class="badge bg-secondary bg-opacity-25 text-secondary">{{ __('messages.formation_inactive') }}</span>
                                        @endif
                                    </td>
                                    <td class="text-end pe-3">
                                        <div class="btn-group" role="group">
                                            <a href="{{ route('formations.show', $formation) }}" 
                                               class="btn btn-sm btn-outline-secondary" 
                                               title="{{ __('messages.view') }}">
                                                <i class="bi bi-eye"></i>
                                            </a>
                                            @if($formation->status !== 'realized')
                                                <button type="button" 
                                                        class="btn btn-sm btn-outline-success" 
                                                        title="{{ __('messages.mark_as_realized') }}"
                                                        data-bs-toggle="modal" 
                                                        data-bs-target="#confirmRealizedModal"
                                                        data-formation-id="{{ $formation->id }}"
                                                        data-formation-theme="{{ $formation->theme }}">
                                                    <i class="bi bi-check-circle"></i>
                                                </button>
                                            @else
                                                <a href="{{ route('formations.presence-pdf', $formation) }}" 
                                                class="btn btn-sm btn-outline-danger" 
                                                title="{{ __('messages.generate_presence_list') ?? 'Presence List PDF' }}"
                                                target="_blank" rel="noopener">
                                                    <i class="bi bi-file-pdf"></i>
                                                </a>
                                            @endif
                                            <a href="{{ route('formations.edit', $formation) }}" 
                                               class="btn btn-sm btn-outline-warning" 
                                               title="{{ __('messages.edit') }}">
                                                <i class="bi bi-pencil"></i>
                                            </a>
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="13" class="text-center py-5 text-muted">
                                        <i class="bi bi-inbox fs-1 d-block mb-2"></i>
                                        {{ __('messages.no_formations_found') }}
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <!-- Mark as Realized Confirmation Modal -->
        <div class="modal fade" id="confirmRealizedModal" tabindex="-1" aria-labelledby="confirmRealizedModalLabel" aria-hidden="true">
            <div class="modal-dialog modal-dialog-centered">
                <div class="modal-content">
                    <div class="modal-header bg-success text-white">
                        <h5 class="modal-title text-white" id="confirmRealizedModalLabel">{{ __('messages.confirm_mark_realized') }}</h5>
                        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <p id="confirmRealizedMessage">{!! __('messages.confirm_mark_realized_message') !!}</p>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">{{ __('messages.cancel') }}</button>
                        <form id="markRealizedForm" method="POST" style="display: inline;">
                            @csrf
                            <button type="submit" class="btn btn-success">
                                <i class="bi bi-check-circle me-1"></i>
                                {{ __('messages.confirm') }}
                            </button>
                        </form>
                    </div>
                </div>
            </div>
        </div>

        @push('scripts')
        <script>
            // Auto-select flotte when driver is selected (client-side for immediate feedback)
            document.addEventListener('DOMContentLoaded', function() {
                const driverSelect = document.getElementById('driver');
                const flotteSelect = document.getElementById('flotte');
                
                if (driverSelect && flotteSelect) {
                    // Get driver data from the page (if available)
                    const driversData = @json($integratedDrivers ?? []);
                    
                    driverSelect.addEventListener('change', function() {
                        const driverId = this.value;
                        if (driverId && driversData.length > 0) {
                            const driver = driversData.find(d => d.id == driverId);
                            if (driver && driver.flotte_id) {
                                flotteSelect.value = driver.flotte_id;
                            } else {
                                flotteSelect.value = '';
                            }
                        } else {
                            flotteSelect.value = '';
                        }
                    });
                }
            });

            function filterFormations() {
                const input = document.getElementById('ftSearch');
                const filter = input.value.toLowerCase();
                const table = document.getElementById('formationsTable');
                const rows = table.getElementsByTagName('tr');
                let visible = 0;
                for (let i = 1; i < rows.length; i++) {
                    const cells = rows[i].getElementsByTagName('td');
                    if (!cells.length) continue;
                    const theme = (cells[0].innerText || '').toLowerCase();
                    const match = theme.includes(filter);
                    rows[i].style.display = match ? '' : 'none';
                    if (match) visible++;
                }
                const count = document.getElementById('ftSearchCount');
                if (count) {
                    const resultsText = count.getAttribute('data-results-text') || '{{ __('messages.results_count') }}';
                    count.textContent = visible + ' ' + resultsText;
                }
            }
            // Mark as Realized confirmation behaviour
            (function() {
                const realizedModal = document.getElementById('confirmRealizedModal');
                const realizedForm = document.getElementById('markRealizedForm');
                const realizedMessage = document.getElementById('confirmRealizedMessage');
                const realizedMessageTemplate = @json(__('messages.confirm_mark_realized_message'));

                if (realizedModal && realizedForm) {
                    realizedModal.addEventListener('show.bs.modal', function (event) {
                        const triggerButton = event.relatedTarget;
                        const formationId = triggerButton ? triggerButton.getAttribute('data-formation-id') : null;
                        const formationTheme = triggerButton ? triggerButton.getAttribute('data-formation-theme') : null;

                        if (formationId) {
                            realizedForm.action = `/formations/${formationId}/mark-realized`;
                            if (realizedMessage && formationTheme) {
                                realizedMessage.textContent = realizedMessageTemplate.replace(':name', formationTheme);
                            }
                        }
                    });
                }
            })();

            // Initialize and show toasts on page load
            document.addEventListener('DOMContentLoaded', function() {
                const successToast = document.getElementById('successToast');
                const errorToast = document.getElementById('errorToast');

                if (successToast && typeof bootstrap !== 'undefined') {
                    const toast = new bootstrap.Toast(successToast);
                    toast.show();
                }

                if (errorToast && typeof bootstrap !== 'undefined') {
                    const toast = new bootstrap.Toast(errorToast);
                    toast.show();
                }
            });
        </script>
        @endpush
    </div>
</x-app-layout>

