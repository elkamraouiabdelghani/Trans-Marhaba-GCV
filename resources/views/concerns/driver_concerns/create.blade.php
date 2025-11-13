<x-app-layout>
    <x-slot name="header">
        @include('layouts.topnav')
    </x-slot>

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
            <div class="col-lg-9">
                <div class="card border-0 shadow-sm">
                    <div class="card-header bg-white border-0 py-3">
                        <div class="d-flex justify-content-between align-items-center">
                            <h5 class="mb-0 text-dark fw-bold">
                                <i class="bi bi-plus-circle me-2 text-primary"></i>
                                {{ __('messages.add_concern') }}
                            </h5>
                            <a href="{{ route('concerns.driver-concerns.index') }}" class="btn btn-outline-secondary">
                                <i class="bi bi-x-circle me-1"></i> {{ __('messages.cancel') }}
                            </a>
                        </div>
                    </div>
                    <div class="card-body">
                        <form action="{{ route('concerns.driver-concerns.store') }}" method="POST" novalidate>
                            @csrf

                            <div class="row g-4">
                                <div class="col-md-4">
                                    <label for="reported_at" class="form-label fw-semibold">{{ __('messages.reported_at') }} <span class="text-danger">*</span></label>
                                    <input
                                        type="date"
                                        id="reported_at"
                                        name="reported_at"
                                        class="form-control @error('reported_at') is-invalid @enderror"
                                        value="{{ old('reported_at', now()->format('Y-m-d')) }}"
                                        required
                                    >
                                    @error('reported_at')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>

                                <div class="col-md-4">
                                    <label for="driver_id" class="form-label fw-semibold">{{ __('messages.driver') }} <span class="text-danger">*</span></label>
                                    <select
                                        id="driver_id"
                                        name="driver_id"
                                        class="form-select @error('driver_id') is-invalid @enderror"
                                        required
                                    >
                                        <option value="">{{ __('messages.select_driver') }}</option>
                                        @foreach($drivers as $driver)
                                            <option
                                                value="{{ $driver->id }}"
                                                data-vehicle="{{ $driver->assignedVehicle->license_plate ?? '' }}"
                                                @selected((int) old('driver_id') === (int) $driver->id)
                                            >
                                                {{ $driver->full_name }}
                                            </option>
                                        @endforeach
                                    </select>
                                    @error('driver_id')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>

                                <div class="col-md-4">
                                    <label for="vehicle_licence_plate" class="form-label fw-semibold">{{ __('messages.vehicle_licence_plate') }}</label>
                                    <input
                                        type="text"
                                        id="vehicle_licence_plate"
                                        name="vehicle_licence_plate"
                                        class="form-control @error('vehicle_licence_plate') is-invalid @enderror"
                                        value="{{ old('vehicle_licence_plate') }}"
                                        placeholder="{{ __('messages.vehicle_licence_plate_placeholder') }}"
                                    >
                                    @error('vehicle_licence_plate')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>

                                <div class="col-md-4">
                                    <label for="concern_type" class="form-label fw-semibold">{{ __('messages.concern_type') }} <span class="text-danger">*</span></label>
                                    <select
                                        id="concern_type"
                                        name="concern_type"
                                        class="form-select @error('concern_type') is-invalid @enderror"
                                        required
                                    >
                                        <option value="">{{ __('messages.select_concern_type') }}</option>
                                        @foreach($concernTypes as $key => $label)
                                            <option value="{{ $key }}" @selected(old('concern_type') === $key)>
                                                {{ $label }}
                                            </option>
                                        @endforeach
                                    </select>
                                    @error('concern_type')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>

                                <div class="col-md-8">
                                    <label for="responsible_party" class="form-label fw-semibold">{{ __('messages.responsible_party') }}</label>
                                    <input
                                        type="text"
                                        id="responsible_party"
                                        name="responsible_party"
                                        class="form-control @error('responsible_party') is-invalid @enderror"
                                        value="{{ old('responsible_party', auth()->user()->name ?? '') }}"
                                        placeholder="{{ __('messages.responsible_party_placeholder') }}"
                                    >
                                    @error('responsible_party')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>

                                <div class="col-12">
                                    <label for="description" class="form-label fw-semibold">{{ __('messages.description') }} <span class="text-danger">*</span></label>
                                    <textarea
                                        id="description"
                                        name="description"
                                        rows="4"
                                        class="form-control @error('description') is-invalid @enderror"
                                        required
                                    >{{ old('description') }}</textarea>
                                    @error('description')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>

                                <div class="col-12">
                                    <label for="immediate_action" class="form-label fw-semibold">{{ __('messages.immediate_action') }}</label>
                                    <textarea
                                        id="immediate_action"
                                        name="immediate_action"
                                        rows="3"
                                        class="form-control @error('immediate_action') is-invalid @enderror"
                                        placeholder="{{ __('messages.immediate_action_placeholder') }}"
                                    >{{ old('immediate_action') }}</textarea>
                                    @error('immediate_action')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>

                                <div class="col-12">
                                    <label for="resolution_comments" class="form-label fw-semibold">{{ __('messages.resolution_comments') }}</label>
                                    <textarea
                                        id="resolution_comments"
                                        name="resolution_comments"
                                        rows="3"
                                        class="form-control @error('resolution_comments') is-invalid @enderror"
                                        placeholder="{{ __('messages.resolution_comments_placeholder') }}"
                                    >{{ old('resolution_comments') }}</textarea>
                                    @error('resolution_comments')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>

                            <div class="d-flex justify-content-end mt-4">
                                {{-- <input type="hidden" name="status" value="in_progress"> --}}
                                <button type="submit" class="btn btn-dark">
                                    <i class="bi bi-save me-2"></i>{{ __('messages.save') }}
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>

<script>
document.addEventListener('DOMContentLoaded', function () {
    const driverSelect = document.getElementById('driver_id');
    const vehicleInput = document.getElementById('vehicle_licence_plate');

    if (!driverSelect || !vehicleInput) {
        return;
    }

    const updateVehicleField = () => {
        const selectedOption = driverSelect.options[driverSelect.selectedIndex];
        const assignedVehicle = selectedOption ? selectedOption.getAttribute('data-vehicle') : '';
        vehicleInput.value = assignedVehicle || '';
    };

    driverSelect.addEventListener('change', updateVehicleField);

    if (!vehicleInput.value && driverSelect.value) {
        updateVehicleField();
    }
});
</script>

