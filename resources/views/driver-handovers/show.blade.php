<x-app-layout>
    <x-slot name="header">
        @include('layouts.topnav')
    </x-slot>

    <div class="container-fluid py-4">
        <div class="d-flex justify-content-between align-items-center mb-4 card-header bg-white border-0 p-3 rounded-3 shadow-sm">
            <h5 class="mb-0 text-dark fw-bold">
                <i class="bi bi-arrow-left-right me-2 text-primary"></i>
                {{ __('messages.driver_handover_details') }}
            </h5>
            <a href="{{ route('driver-handovers.index') }}" class="btn btn-outline-secondary btn-sm">
                <i class="bi bi-arrow-left me-1"></i>
                {{ __('messages.back') }}
            </a>
        </div>

        @if(session('success'))
            <div class="alert alert-success border-0 shadow-sm">{{ session('success') }}</div>
        @endif

        @if(session('error'))
            <div class="alert alert-danger border-0 shadow-sm">{{ session('error') }}</div>
        @endif

        <div class="row">
            <!-- Main Content -->
            <div class="col-12 col-lg-8">
                <div class="card border-0 shadow-sm mb-4">
                    <div class="card-header bg-white border-0 py-3 d-flex justify-content-between align-items-center">
                        <div>
                            <h5 class="mb-0 text-dark fw-bold">
                                <i class="bi bi-arrow-left-right me-2 text-primary"></i>
                                {{ __('messages.driver_handover_details') }}
                            </h5>
                            <small class="text-muted">
                                {{ __('messages.created_at') }}: {{ $handover->created_at?->format('d/m/Y H:i') }}
                            </small>
                        </div>
                        <span class="badge bg-{{ $handover->status === 'confirmed' ? 'success' : 'secondary' }} bg-opacity-10 text-{{ $handover->status === 'confirmed' ? 'success' : 'secondary' }}">
                            {{ __('messages.status') }}: {{ __('messages.' . ($handover->status ?? 'pending')) }}
                        </span>
                    </div>
                    <div class="card-body">
                        <div class="row g-4">
                            <div class="col-md-6">
                                <h6 class="text-muted text-uppercase small">{{ __('messages.driver_replace') }}</h6>
                                <p class="mb-1 fw-semibold">
                                    {{ $handover->driver_from_name ?? optional($handover->driverFrom)->full_name ?? __('messages.not_available') }}
                                </p>
                                @if($handover->driverFrom)
                                    <a href="{{ route('drivers.show', $handover->driverFrom) }}" class="small text-decoration-underline text-success">
                                        {{ __('messages.view_driver_profile') }}
                                    </a>
                                @endif
                            </div>
                            <div class="col-md-6">
                                <h6 class="text-muted text-uppercase small">{{ __('messages.driver_replacement') }}</h6>
                                <p class="mb-1 fw-semibold">
                                    {{ $handover->driver_to_name ?? optional($handover->driverTo)->full_name ?? __('messages.not_available') }}
                                </p>
                                @if($handover->driverTo)
                                    <a href="{{ route('drivers.show', $handover->driverTo) }}" class="small text-decoration-underline text-success">
                                        {{ __('messages.view_driver_profile') }}
                                    </a>
                                @endif
                            </div>
                            <div class="col-md-6">
                                <h6 class="text-muted text-uppercase small">{{ __('messages.vehicle') }}</h6>
                                <p class="mb-1 fw-semibold">
                                    {{ optional($handover->vehicle)->license_plate ?? __('messages.not_available') }}
                                </p>
                            </div>
                            <div class="col-md-6">
                                <h6 class="text-muted text-uppercase small">{{ __('messages.code') ?? 'Code' }}</h6>
                                <p class="mb-1 fw-semibold">
                                    {{ $handover->code ?? __('messages.not_available') }}
                                </p>
                            </div>
                            <div class="col-md-6">
                                <h6 class="text-muted text-uppercase small">{{ __('messages.km_in_handover') ?? 'KM in handover' }}</h6>
                                <p class="mb-1 fw-semibold">
                                    {{ $handover->vehicle_km ?? __('messages.not_available') }} km
                                </p>
                            </div>
                            <div class="col-md-6">
                                <h6 class="text-muted text-uppercase small">{{ __('messages.gasoil') ?? 'Gasoil' }}</h6>
                                <p class="mb-1 fw-semibold">
                                    {{ $handover->gasoil ? number_format($handover->gasoil, 2) . ' L' : __('messages.not_available') }}
                                </p>
                            </div>
                            <div class="col-md-6">
                                <h6 class="text-muted text-uppercase small">{{ __('messages.date') }}</h6>
                                <div class="d-flex align-items-center gap-2">
                                    <p class="mb-1 fw-semibold">{{ optional($handover->handover_date)->format('d/m/Y') ?? '—' }}</p>
                                    @if($handover->location)
                                        -<small class="fw-semibold">{{ $handover->location }}</small>
                                    @endif
                                </div>
                            </div>
                            <div class="col-md-6">
                                <h6 class="text-muted text-uppercase small">{{ __('messages.cause') }}</h6>
                                <p class="mb-1 fw-semibold">{{ $handover->cause ?? '—' }}</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Sidebar with Quick Actions -->
            <div class="col-12 col-lg-4">
                <aside class="position-sticky" style="top: 0.5rem;">
                    <div class="card border-0 shadow-sm">
                        <div class="card-header bg-white border-0 py-3">
                            <h6 class="mb-0 text-dark fw-bold text-uppercase small">
                                <i class="bi bi-lightning-charge text-warning me-2"></i>
                                {{ __('messages.quick_actions') }}
                            </h6>
                        </div>
                        <div class="card-body d-grid gap-2">
                            @if($handover->status !== 'confirmed')
                                <a href="{{ route('driver-handovers.edit', $handover) }}" class="btn btn-warning btn-sm">
                                    <i class="bi bi-pencil me-1"></i>
                                    {{ __('messages.edit') }}
                                </a>
                            @endif
                            
                            @if($handover->status !== 'confirmed')
                                <button type="button" 
                                        class="btn btn-success btn-sm"
                                        data-bs-toggle="modal" 
                                        data-bs-target="#confirmHandoverModal"
                                        data-handover-id="{{ $handover->id }}"
                                        data-driver-from="{{ $handover->driver_from_name ?? optional($handover->driverFrom)->full_name ?? 'N/A' }}"
                                        data-driver-to="{{ $handover->driver_to_name ?? optional($handover->driverTo)->full_name ?? 'N/A' }}"
                                        data-vehicle="{{ optional($handover->vehicle)->license_plate ?? 'N/A' }}">
                                    <i class="bi bi-check-circle me-1"></i>
                                    {{ __('messages.confirm') }}
                                </button>
                            @endif

                            @if($handover->handover_file_path)
                                <a href="{{ route('uploads.serve', $handover->handover_file_path) }}" 
                                   target="_blank" 
                                   class="btn btn-primary btn-sm"
                                   download>
                                    <i class="bi bi-download me-1"></i>
                                    {{ __('messages.download') ?? 'Download PDF' }}
                                </a>
                            @endif

                            <hr class="my-2">

                            <button type="button" 
                                    class="btn btn-outline-danger btn-sm"
                                    data-bs-toggle="modal" 
                                    data-bs-target="#deleteHandoverModal"
                                    data-handover-id="{{ $handover->id }}"
                                    data-handover-date="{{ optional($handover->handover_date)->format('d/m/Y') ?? 'N/A' }}">
                                <i class="bi bi-trash me-1"></i>
                                {{ __('messages.delete') }}
                            </button>
                        </div>
                    </div>
                </aside>
            </div>
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

    <!-- Delete Handover Modal -->
    <div class="modal fade" id="deleteHandoverModal" tabindex="-1" aria-labelledby="deleteHandoverModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header bg-danger text-white">
                    <h5 class="modal-title" id="deleteHandoverModalLabel">
                        <i class="bi bi-exclamation-triangle me-2"></i>
                        {{ __('messages.delete_handover') }}
                    </h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="{{ __('messages.close') }}"></button>
                </div>
                <div class="modal-body">
                    <p class="mb-3">
                        {{ __('messages.delete_confirmation') }}
                    </p>
                    <div class="alert alert-warning mb-0">
                        <strong>{{ __('messages.date') }}:</strong> <span id="deleteHandoverDate"></span>
                    </div>
                    <p class="mt-3 mb-0 text-danger small">
                        <i class="bi bi-exclamation-circle me-1"></i>
                        {{ __('messages.delete_handover_warning') }}
                    </p>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                        {{ __('messages.cancel') }}
                    </button>
                    <form id="deleteHandoverForm" method="POST" class="d-inline">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="btn btn-danger">
                            <i class="bi bi-trash me-1"></i>
                            {{ __('messages.delete') }}
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

