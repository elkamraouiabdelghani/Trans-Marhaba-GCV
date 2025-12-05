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
                                <h6 class="text-muted mb-1 small">{{ __('messages.total') }} ({{ $selectedYear }})</h6>
                                <h3 class="mb-0 fw-bold text-dark">{{ $stats['total'] ?? 0 }}</h3>
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
                                <h6 class="text-muted mb-1 small">{{ __('messages.tbt_formation_status_planned') }}</h6>
                                <h3 class="mb-0 fw-bold text-dark">{{ $stats['planned'] ?? 0 }}</h3>
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
                                <h6 class="text-muted mb-1 small">{{ __('messages.tbt_formation_status_realized') }}</h6>
                                <h3 class="mb-0 fw-bold text-dark">{{ $stats['realized'] ?? 0 }}</h3>
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
                                <h3 class="mb-0 fw-bold text-dark">{{ number_format($stats['realized_percentage'] ?? 0, 1) }}%</h3>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="card border-0 shadow-sm mb-4">
            <div class="card-header bg-white border-0 py-3">
                <form method="GET" action="{{ route('tbt-formations.index') }}">
                    <div class="row g-3 align-items-end">
                        <div class="col-md-3">
                            <label for="year" class="form-label small">{{ __('messages.tbt_formation_year') }}</label>
                            <select name="year" id="year" class="form-select form-select-sm">
                                @foreach($years ?? [] as $y)
                                    <option value="{{ $y }}" {{ (string)$selectedYear === (string)$y ? 'selected' : '' }}>
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
                            <label for="active" class="form-label small">{{ __('messages.tbt_formation_active') }}</label>
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
                        <a href="{{ route('tbt-formations.planning') }}" class="btn btn-outline-dark btn-sm">
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
                                    <th>{{ __('messages.tbt_formation_title') }}</th>
                                    <th>{{ __('messages.tbt_formation_year') }}</th>
                                    <th>{{ __('messages.tbt_formation_month') }}</th>
                                    <th>{{ __('messages.tbt_formation_week') }}</th>
                                    <th>{{ __('messages.tbt_formation_status') }}</th>
                                    <th>{{ __('messages.tbt_formation_active') }}</th>
                                    <th class="text-end">{{ __('messages.actions') }}</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($formations as $formation)
                                    <tr>
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
                                            @php
                                                $statusClass = $formation->status === 'realized' ? 'bg-success' : 'bg-warning text-dark';
                                                $statusLabel = $formation->status === 'realized'
                                                    ? __('messages.tbt_formation_status_realized')
                                                    : __('messages.tbt_formation_status_planned');
                                            @endphp
                                            <span class="badge {{ $statusClass }}">{{ $statusLabel }}</span>
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
                                                @if($formation->status !== 'realized')
                                                    <button type="button" class="btn btn-outline-success" title="{{ __('messages.mark_as_realized') }}" data-bs-toggle="modal" data-bs-target="#confirmRealizedModal" data-formation-id="{{ $formation->id }}" data-formation-name="{{ $formation->title }}">
                                                        <i class="bi bi-check-circle"></i>
                                                    </button>
                                                @endif
                                                <a href="{{ route('tbt-formations.edit', $formation) }}" class="btn btn-outline-primary" title="{{ __('messages.tbt_formation_edit') }}">
                                                    <i class="bi bi-pencil"></i>
                                                </a>
                                                <button type="button" class="btn btn-outline-danger" title="{{ __('messages.tbt_formation_delete') }}" data-bs-toggle="modal" data-bs-target="#confirmDeleteModal" data-formation-id="{{ $formation->id }}" data-formation-name="{{ $formation->title }}">
                                                    <i class="bi bi-trash"></i>
                                                </button>
                                            </div>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                    <div class="d-flex justify-content-end mt-3">
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

        <!-- Delete Confirmation Modal -->
        <div class="modal fade" id="confirmDeleteModal" tabindex="-1" aria-labelledby="confirmDeleteModalLabel" aria-hidden="true">
            <div class="modal-dialog modal-dialog-centered">
                <div class="modal-content">
                    <div class="modal-header bg-danger text-white">
                        <h5 class="modal-title text-white" id="confirmDeleteModalLabel">{{ __('messages.confirm_delete') }}</h5>
                        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <p id="confirmDeleteMessage">{{ __('messages.tbt_formation_confirm_delete') }}</p>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">{{ __('messages.cancel') }}</button>
                        <form id="deleteForm" method="POST" style="display: inline;">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="btn btn-danger">
                                <i class="bi bi-trash me-1"></i>
                                {{ __('messages.tbt_formation_delete') }}
                            </button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>

    @push('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const realizedModal = document.getElementById('confirmRealizedModal');
            const realizedForm = document.getElementById('markRealizedForm');
            const realizedMessage = document.getElementById('confirmRealizedMessage');
            const realizedMessageTemplate = @json(__('messages.confirm_mark_realized_message'));

            if (realizedModal && realizedForm) {
                realizedModal.addEventListener('show.bs.modal', function (event) {
                    const triggerButton = event.relatedTarget;
                    const formationId = triggerButton ? triggerButton.getAttribute('data-formation-id') : null;
                    const formationName = triggerButton ? triggerButton.getAttribute('data-formation-name') : null;

                    if (formationId) {
                        realizedForm.action = `{{ url('tbt-formations') }}/${formationId}/mark-realized`;
                        if (realizedMessage && formationName) {
                            realizedMessage.textContent = realizedMessageTemplate.replace(':name', formationName);
                        }
                    }
                });
            }

            const deleteModal = document.getElementById('confirmDeleteModal');
            const deleteForm = document.getElementById('deleteForm');
            const deleteMessage = document.getElementById('confirmDeleteMessage');
            const deleteMessageTemplate = @json(__('messages.tbt_formation_confirm_delete'));

            if (deleteModal && deleteForm) {
                deleteModal.addEventListener('show.bs.modal', function (event) {
                    const triggerButton = event.relatedTarget;
                    const formationId = triggerButton ? triggerButton.getAttribute('data-formation-id') : null;
                    const formationName = triggerButton ? triggerButton.getAttribute('data-formation-name') : null;

                    if (formationId) {
                        deleteForm.action = `{{ url('tbt-formations') }}/${formationId}`;
                        if (deleteMessage && formationName) {
                            deleteMessage.textContent = deleteMessageTemplate + ' "' + formationName + '" ?';
                        }
                    }
                });
            }
        });
    </script>
    @endpush
</x-app-layout>
