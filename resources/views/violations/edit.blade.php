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
                                <i class="bi bi-pencil-square me-2 text-primary"></i>
                                {{ __('messages.edit') }} {{ __('messages.violation') }}
                            </h5>
                            <a href="{{ route('violations.show', $violation) }}" class="btn btn-outline-secondary">
                                <i class="bi bi-x-circle me-1"></i> {{ __('messages.cancel') }}
                            </a>
                        </div>
                    </div>
                    <div class="card-body">
                        <form action="{{ route('violations.update', $violation) }}" method="POST" enctype="multipart/form-data" novalidate>
                            @csrf
                            @method('PUT')

                            <div class="row g-4">
                                <div class="col-md-6">
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
                                                data-vehicle="{{ $driver->assignedVehicle?->id ?? '' }}"
                                                @selected((int) old('driver_id', $violation->driver_id) === (int) $driver->id)
                                            >
                                                {{ $driver->full_name }}
                                            </option>
                                        @endforeach
                                    </select>
                                    @error('driver_id')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>

                                <div class="col-md-6">
                                    <label for="violation_date" class="form-label fw-semibold">{{ __('messages.violation_date') }} <span class="text-danger">*</span></label>
                                    <input
                                        type="date"
                                        id="violation_date"
                                        name="violation_date"
                                        class="form-control @error('violation_date') is-invalid @enderror"
                                        value="{{ old('violation_date', $violation->violation_date?->format('Y-m-d')) }}"
                                        required
                                    >
                                    @error('violation_date')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>

                                <div class="col-md-6">
                                    <label for="violation_time" class="form-label fw-semibold">{{ __('messages.violation_time') }}</label>
                                    <input
                                        type="time"
                                        id="violation_time"
                                        name="violation_time"
                                        class="form-control @error('violation_time') is-invalid @enderror"
                                        value="{{ old('violation_time', $violation->violation_time ? $violation->violation_time->format('H:i') : null) }}"
                                    >
                                    @error('violation_time')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>

                                <div class="col-md-6">
                                    <label for="violation_type_id" class="form-label fw-semibold">{{ __('messages.violation_type') }} <span class="text-danger">*</span></label>
                                    <select
                                        id="violation_type_id"
                                        name="violation_type_id"
                                        class="form-select @error('violation_type_id') is-invalid @enderror"
                                        required
                                    >
                                        <option value="">{{ __('messages.select_violation_type') }}</option>
                                        @foreach($violationTypes as $type)
                                            <option 
                                                value="{{ $type->id }}"
                                                @selected((int) old('violation_type_id', $violation->violation_type_id) === (int) $type->id)
                                            >
                                                {{ $type->name }}
                                            </option>
                                        @endforeach
                                    </select>
                                    @error('violation_type_id')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>

                                <div class="col-md-6">
                                    <label for="location" class="form-label fw-semibold">{{ __('messages.location') }}</label>
                                    <input
                                        type="text"
                                        id="location"
                                        name="location"
                                        class="form-control @error('location') is-invalid @enderror"
                                        value="{{ old('location', $violation->location) }}"
                                    >
                                    @error('location')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>


                                <div class="col-md-6">
                                    <label for="vehicle_id" class="form-label fw-semibold">{{ __('messages.vehicle') }}</label>
                                    <select
                                        id="vehicle_id"
                                        name="vehicle_id"
                                        class="form-select @error('vehicle_id') is-invalid @enderror"
                                    >
                                        <option value="">{{ __('messages.select_vehicle') }}</option>
                                        @foreach($vehicles as $vehicle)
                                            <option 
                                                value="{{ $vehicle->id }}"
                                                @selected((int) old('vehicle_id', $violation->vehicle_id) === (int) $vehicle->id)
                                            >
                                                {{ $vehicle->license_plate }}
                                            </option>
                                        @endforeach
                                    </select>
                                    @error('vehicle_id')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>

                                <div class="col-md-6">
                                    <label for="speed" class="form-label fw-semibold">{{ __('messages.violation_speed') }}</label>
                                    <input
                                        type="number"
                                        step="0.01"
                                        min="0"
                                        id="speed"
                                        name="speed"
                                        class="form-control @error('speed') is-invalid @enderror"
                                        value="{{ old('speed', $violation->speed) }}"
                                    >
                                    @error('speed')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>

                                <div class="col-md-6">
                                    <label for="speed_limit" class="form-label fw-semibold">{{ __('messages.violation_speed_limit') }}</label>
                                    <input
                                        type="number"
                                        step="0.01"
                                        min="0"
                                        id="speed_limit"
                                        name="speed_limit"
                                        class="form-control @error('speed_limit') is-invalid @enderror"
                                        value="{{ old('speed_limit', $violation->speed_limit) }}"
                                    >
                                    @error('speed_limit')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>

                                <div class="col-md-6">
                                    <label for="violation_duration_seconds" class="form-label fw-semibold">{{ __('messages.violation_duration') }}</label>
                                    <input
                                        type="number"
                                        min="0"
                                        step="1"
                                        id="violation_duration_seconds"
                                        name="violation_duration_seconds"
                                        class="form-control @error('violation_duration_seconds') is-invalid @enderror"
                                        value="{{ old('violation_duration_seconds', $violation->violation_duration_seconds) }}"
                                    >
                                    @error('violation_duration_seconds')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>

                                <div class="col-md-6">
                                    <label for="violation_distance_km" class="form-label fw-semibold">{{ __('messages.violation_distance') }}</label>
                                    <input
                                        type="number"
                                        min="0"
                                        step="0.01"
                                        id="violation_distance_km"
                                        name="violation_distance_km"
                                        class="form-control @error('violation_distance_km') is-invalid @enderror"
                                        value="{{ old('violation_distance_km', $violation->violation_distance_km) }}"
                                    >
                                    @error('violation_distance_km')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>

                                <div class="col-md-6">
                                    <label for="status" class="form-label fw-semibold">{{ __('messages.status') }}</label>
                                    <select
                                        id="status"
                                        name="status"
                                        class="form-select @error('status') is-invalid @enderror"
                                    >
                                        <option value="pending" @selected(old('status', $violation->status) === 'pending')>{{ __('messages.pending') }}</option>
                                        <option value="confirmed" @selected(old('status', $violation->status) === 'confirmed')>{{ __('messages.confirmed') }}</option>
                                        <option value="rejected" @selected(old('status', $violation->status) === 'rejected')>{{ __('messages.rejected') }}</option>
                                    </select>
                                    @error('status')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>

                                <div class="col-12">
                                    <label for="description" class="form-label fw-semibold">{{ __('messages.description') }}</label>
                                    <textarea
                                        id="description"
                                        name="description"
                                        rows="4"
                                        class="form-control @error('description') is-invalid @enderror"
                                    >{{ old('description', $violation->description) }}</textarea>
                                    @error('description')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>

                                <div class="col-12">
                                    <label for="notes" class="form-label fw-semibold">{{ __('messages.notes') }}</label>
                                    <textarea
                                        id="notes"
                                        name="notes"
                                        rows="3"
                                        class="form-control @error('notes') is-invalid @enderror"
                                    >{{ old('notes', $violation->notes) }}</textarea>
                                    @error('notes')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>

                                <div class="col-12">
                                    <label for="analysis" class="form-label fw-semibold">
                                        {{ __('messages.violation_analysis') }}
                                    </label>
                                    <textarea
                                        id="analysis"
                                        name="analysis"
                                        rows="3"
                                        class="form-control @error('analysis') is-invalid @enderror"
                                        required
                                    >{{ old('analysis', $violation->actionPlan?->analysis) }}</textarea>
                                    <small class="text-muted d-block">{{ __('messages.violation_analysis_hint') }}</small>
                                    @error('analysis')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>

                                <div class="col-12">
                                    <label for="action_plan" class="form-label fw-semibold">
                                        {{ __('messages.violation_action_plan') }}
                                    </label>
                                    <textarea
                                        id="action_plan"
                                        name="action_plan"
                                        rows="4"
                                        class="form-control @error('action_plan') is-invalid @enderror"
                                        required
                                    >{{ old('action_plan', $violation->actionPlan?->action_plan) }}</textarea>
                                    <small class="text-muted d-block">{{ __('messages.violation_action_plan_hint') }}</small>
                                    @error('action_plan')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>

                                <div class="col-12">
                                    <label for="evidence" class="form-label fw-semibold">{{ __('messages.violation_evidence') }}</label>
                                    @if($violation->actionPlan?->evidence_path)
                                        <div class="mb-2 d-flex align-items-center gap-2">
                                            <a href="{{ route('violations.action-plan.evidence', $violation) }}" class="btn btn-sm btn-outline-primary">
                                                <i class="bi bi-download me-1"></i>{{ __('messages.download') }}
                                            </a>
                                            <span class="text-muted small">{{ $violation->actionPlan?->evidence_original_name }}</span>
                                        </div>
                                    @endif
                                    <input
                                        type="file"
                                        id="evidence"
                                        name="evidence"
                                        class="form-control @error('evidence') is-invalid @enderror"
                                        accept=".pdf,.jpg,.jpeg,.png"
                                    >
                                    <small class="text-muted">{{ __('messages.violation_evidence_hint') }}</small>
                                    @error('evidence')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>

                                <div class="col-12">
                                    <label for="document" class="form-label fw-semibold">{{ __('messages.document') }}</label>
                                    @if($violation->document_path)
                                        <div class="mb-2">
                                            <a href="{{ route('violations.document', $violation) }}" target="_blank" class="btn btn-sm btn-outline-primary">
                                                <i class="bi bi-eye me-1"></i>{{ __('messages.view_current_document') }}
                                            </a>
                                        </div>
                                    @endif
                                    <input
                                        type="file"
                                        id="document"
                                        name="document"
                                        class="form-control @error('document') is-invalid @enderror"
                                        accept=".pdf,.jpg,.jpeg,.png"
                                    >
                                    <small class="text-muted">{{ __('messages.max_file_size') }}: 10MB. {{ __('messages.leave_empty_to_keep_current') }}</small>
                                    @error('document')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>

                            <div class="d-flex justify-content-end mt-4">
                                <button type="submit" class="btn btn-dark">
                                    <i class="bi bi-save me-2"></i>{{ __('messages.update') }}
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
    const vehicleSelect = document.getElementById('vehicle_id');

    // Update vehicle based on driver selection
    if (driverSelect && vehicleSelect) {
        driverSelect.addEventListener('change', function() {
            const selectedOption = driverSelect.options[driverSelect.selectedIndex];
            const assignedVehicleId = selectedOption ? selectedOption.getAttribute('data-vehicle') : '';
            if (assignedVehicleId && !vehicleSelect.value) {
                vehicleSelect.value = assignedVehicleId;
            }
        });
    }
});
</script>

