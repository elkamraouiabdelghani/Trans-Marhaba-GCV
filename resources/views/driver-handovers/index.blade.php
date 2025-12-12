<x-app-layout>
    <x-slot name="header">
        @include('layouts.topnav')
    </x-slot>

    <div class="container-fluid py-4">
        {{-- Stats Cards Section --}}
        <div class="row g-3 mb-4">
            <div class="col-xl-4 col-lg-4 col-md-6">
                <div class="card border-0 shadow-sm h-100">
                    <div class="card-body p-4">
                        <div class="d-flex align-items-center">
                            <div class="bg-primary bg-opacity-10 rounded-circle d-flex align-items-center justify-content-center me-3" style="width: 60px; height: 60px;">
                                <i class="bi bi-arrow-left-right text-primary fs-4"></i>
                            </div>
                            <div class="flex-grow-1">
                                <h6 class="text-muted mb-1">{{ __('messages.total') }}</h6>
                                <h3 class="mb-0 fw-bold text-dark">{{ $totalHandovers ?? 0 }}</h3>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-xl-4 col-lg-4 col-md-6">
                <div class="card border-0 shadow-sm h-100">
                    <div class="card-body p-4">
                        <div class="d-flex align-items-center">
                            <div class="bg-success bg-opacity-10 rounded-circle d-flex align-items-center justify-content-center me-3" style="width: 60px; height: 60px;">
                                <i class="bi bi-check-circle text-success fs-4"></i>
                            </div>
                            <div class="flex-grow-1">
                                <h6 class="text-muted mb-1">{{ __('messages.confirmed') }}</h6>
                                <h3 class="mb-0 fw-bold text-dark">{{ $confirmedHandovers ?? 0 }}</h3>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-xl-4 col-lg-4 col-md-6">
                <div class="card border-0 shadow-sm h-100">
                    <div class="card-body p-4">
                        <div class="d-flex align-items-center">
                            <div class="bg-warning bg-opacity-10 rounded-circle d-flex align-items-center justify-content-center me-3" style="width: 60px; height: 60px;">
                                <i class="bi bi-clock text-warning fs-4"></i>
                            </div>
                            <div class="flex-grow-1">
                                <h6 class="text-muted mb-1">{{ __('messages.pending') }}</h6>
                                <h3 class="mb-0 fw-bold text-dark">{{ $pendingHandovers ?? 0 }}</h3>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        @if(session('success'))
            <div class="alert alert-success border-0 shadow-sm">{{ session('success') }}</div>
        @endif

        @if(session('error'))
            <div class="alert alert-danger border-0 shadow-sm">{{ session('error') }}</div>
        @endif

        <div class="card border-0 shadow-sm mb-4">
            <div class="card-header bg-white border-0 py-3 d-flex flex-column flex-lg-row align-items-lg-center justify-content-between gap-3">
                <h5 class="mb-0 text-dark fw-bold d-flex align-items-center">
                    <i class="bi bi-funnel me-2 text-primary"></i>
                    {{ __('messages.filters') }}
                </h5>
                <form method="GET" action="{{ route('driver-handovers.index') }}" class="row g-2 flex-grow-1 align-items-center">
                    <div class="col-md-3">
                        <input type="date" 
                               name="date_from" 
                               value="{{ request('date_from') }}" 
                               class="form-control form-control-sm">
                    </div>
                    <div class="col-md-3">
                        <input type="date" 
                               name="date_to" 
                               value="{{ request('date_to') }}" 
                               class="form-control form-control-sm">
                    </div>
                    <div class="col-md-2">
                        <select name="status" class="form-select form-select-sm">
                            <option value="">{{ __('messages.all_status') }}</option>
                            @foreach($statusOptions as $key => $label)
                                <option value="{{ $key }}" {{ $statusFilter === $key ? 'selected' : '' }}>
                                    {{ $label }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-2 d-flex align-items-end gap-2">
                        <button type="submit" class="btn btn-primary btn-sm flex-fill">
                            <i class="bi bi-search"></i>
                        </button>
                        <a href="{{ route('driver-handovers.index') }}" class="btn btn-secondary btn-sm flex-fill" title="{{ __('messages.reset') }}">
                            <i class="bi bi-arrow-counterclockwise"></i>
                        </a>
                    </div>
                    <div class="col-md-2 d-flex align-items-end">
                        <a href="{{ route('driver-handovers.create') }}" class="btn btn-dark btn-sm w-100 d-flex align-items-center justify-content-center gap-2">
                            <i class="bi bi-plus-circle"></i>
                            {{ __('messages.create_handover') }}
                        </a>
                    </div>
                </form>
            </div>
        </div>

        <div class="card border-0 shadow-sm">
            <div class="card-header bg-white border-0 py-3 d-flex justify-content-between align-items-center">
                <h5 class="mb-0 text-dark fw-bold">
                    <i class="bi bi-table me-2 text-primary"></i>
                    {{ __('messages.driver_handovers_table') }}
                </h5>
                <div class="d-flex align-items-center gap-3">
                    <div class="text-muted small">
                        {{ __('messages.results') }}: {{ $handovers->firstItem() ?? 0 }}-{{ $handovers->lastItem() ?? 0 }} / {{ $handovers->total() }}
                    </div>
                    <a href="{{ route('driver-handovers.export', request()->query()) }}" 
                       class="btn btn-success btn-sm d-flex align-items-center gap-2"
                       title="{{ __('messages.export') ?? 'Export to Excel' }}">
                        <i class="bi bi-file-earmark-excel"></i>
                        {{ __('messages.export') ?? 'Export' }}
                    </a>
                </div>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead class="table-light">
                            <tr>
                                <th>{{ __('messages.dates') ?? 'Dates' }}</th>
                                <th>{{ __('messages.driver_replace') }}</th>
                                <th>{{ __('messages.driver_replacement') }}</th>
                                <th>{{ __('messages.vehicle') }}</th>
                                <th>{{ __('messages.status') }}</th>
                                <th class="text-end">{{ __('messages.actions') }}</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($handovers as $handover)
                                <tr>
                                    <td>
                                        <div class="fw-semibold text-dark">
                                            <div>{{ __('messages.handover_date') ?? 'Sortie' }}: {{ optional($handover->handover_date)->format('d/m/Y') ?? '—' }}</div>
                                            @if($handover->back_date)
                                                <div class="text-success small mt-1">{{ __('messages.back_date') ?? 'Retour' }}: {{ $handover->back_date->format('d/m/Y') }}</div>
                                            @endif
                                        </div>
                                        @if($handover->location)
                                            <small class="text-muted">{{ $handover->location }}</small>
                                        @endif
                                    </td>
                                    <td>
                                        <div class="fw-semibold text-dark">
                                            {{ $handover->driver_from_name ?? optional($handover->driverFrom)->full_name ?? '—' }}
                                        </div>
                                    </td>
                                    <td>
                                        <div class="fw-semibold text-dark">
                                            {{ $handover->driver_to_name ?? optional($handover->driverTo)->full_name ?? '—' }}
                                        </div>
                                    </td>
                                    <td>
                                        <div>{{ optional($handover->vehicle)->license_plate ?? __('messages.not_available') }}</div>
                                        @if($handover->code)
                                            <small class="text-muted">{{ $handover->code }}</small>
                                        @endif
                                    </td>
                                    <td>
                                        <span class="badge bg-{{ $handover->status === 'confirmed' ? 'success' : 'secondary' }} bg-opacity-10 text-{{ $handover->status === 'confirmed' ? 'success' : 'secondary' }}">
                                            {{ $statusOptions[$handover->status] ?? __('messages.pending') }}
                                        </span>
                                    </td>
                                    <td class="text-end">
                                        <div class="btn-group" role="group">
                                            <a href="{{ route('driver-handovers.show', $handover) }}" class="btn btn-sm btn-outline-primary" title="{{ __('messages.view') }}">
                                                <i class="bi bi-eye"></i>
                                            </a>
                                            @if($handover->status !== 'confirmed')
                                                <a href="{{ route('driver-handovers.edit', $handover) }}" class="btn btn-sm btn-outline-warning" title="{{ __('messages.edit') }}">
                                                    <i class="bi bi-pencil"></i>
                                                </a>
                                            @endif
                                            @if($handover->status !== 'confirmed')
                                                <button type="button" 
                                                        class="btn btn-sm btn-outline-success" 
                                                        title="{{ __('messages.confirm') }}"
                                                        data-bs-toggle="modal" 
                                                        data-bs-target="#confirmHandoverModal"
                                                        data-handover-id="{{ $handover->id }}"
                                                        data-driver-from="{{ $handover->driver_from_name ?? optional($handover->driverFrom)->full_name ?? 'N/A' }}"
                                                        data-driver-to="{{ $handover->driver_to_name ?? optional($handover->driverTo)->full_name ?? 'N/A' }}"
                                                        data-vehicle="{{ optional($handover->vehicle)->license_plate ?? 'N/A' }}">
                                                    <i class="bi bi-check-circle"></i>
                                                </button>
                                            @endif
                                            @if($handover->handover_file_path)
                                                <a href="{{ asset('storage/' . $handover->handover_file_path) }}" 
                                                   target="_blank" 
                                                   class="btn btn-sm btn-outline-danger"
                                                   download>
                                                    <i class="bi bi-download"></i>
                                                </a>
                                            @endif
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="6" class="text-center py-5 text-muted">
                                        <i class="bi bi-clipboard-x display-6 d-block mb-3"></i>
                                        {{ __('messages.no_handover_data') }}
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
            @if($handovers->hasPages())
                <div class="card-footer bg-white border-0">
                    {{ $handovers->links() }}
                </div>
            @endif
        </div>
    </div>

    <!-- Confirm Handover Modal -->
    <div class="modal fade" id="confirmHandoverModal" tabindex="-1" aria-labelledby="confirmHandoverModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header bg-success text-white">
                    <h5 class="modal-title" id="confirmHandoverModalLabel">
                        <i class="bi bi-check-circle me-2"></i>
                        {{ __('messages.confirm_handover') }}
                    </h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="{{ __('messages.close') }}"></button>
                </div>
                <div class="modal-body">
                    <p class="mb-3">
                        {{ __('messages.confirm_handover_question') }}
                    </p>
                    <div class="alert alert-info mb-0">
                        <strong>{{ __('messages.driver_replace') }}:</strong> <span id="confirmDriverFrom"></span><br>
                        <strong>{{ __('messages.driver_replacement') }}:</strong> <span id="confirmDriverTo"></span><br>
                        <strong>{{ __('messages.vehicle') }}:</strong> <span id="confirmVehicle"></span>
                    </div>
                    <p class="mt-3 mb-0 text-muted small">
                        {{ __('messages.confirm_handover_warning') }}
                    </p>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                        {{ __('messages.cancel') }}
                    </button>
                    <form id="confirmHandoverForm" method="POST" class="d-inline">
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
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.6/dist/js/bootstrap.bundle.min.js" crossorigin="anonymous"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Confirm Handover Modal
            const confirmModal = document.getElementById('confirmHandoverModal');
            if (confirmModal) {
                confirmModal.addEventListener('show.bs.modal', function(event) {
                    const button = event.relatedTarget;
                    const handoverId = button.getAttribute('data-handover-id');
                    const driverFrom = button.getAttribute('data-driver-from');
                    const driverTo = button.getAttribute('data-driver-to');
                    const vehicle = button.getAttribute('data-vehicle');

                    const modalBody = confirmModal.querySelector('#confirmDriverFrom');
                    const modalBodyTo = confirmModal.querySelector('#confirmDriverTo');
                    const modalBodyVehicle = confirmModal.querySelector('#confirmVehicle');
                    const form = confirmModal.querySelector('#confirmHandoverForm');

                    if (modalBody) modalBody.textContent = driverFrom;
                    if (modalBodyTo) modalBodyTo.textContent = driverTo;
                    if (modalBodyVehicle) modalBodyVehicle.textContent = vehicle;
                    if (form) {
                        form.action = '{{ route("driver-handovers.confirm", ":id") }}'.replace(':id', handoverId);
                    }
                });
            }

            // Delete Handover Modal
            const deleteModal = document.getElementById('deleteHandoverModal');
            if (deleteModal) {
                deleteModal.addEventListener('show.bs.modal', function(event) {
                    const button = event.relatedTarget;
                    const handoverId = button.getAttribute('data-handover-id');
                    const handoverDate = button.getAttribute('data-handover-date');

                    const modalBody = deleteModal.querySelector('#deleteHandoverDate');
                    const form = deleteModal.querySelector('#deleteHandoverForm');

                    if (modalBody) modalBody.textContent = handoverDate;
                    if (form) {
                        form.action = '{{ route("driver-handovers.destroy", ":id") }}'.replace(':id', handoverId);
                    }
                });
            }
        });
    </script>
    @endpush
</x-app-layout>

