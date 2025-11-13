<x-app-layout>
    <x-slot name="header">
        @include('layouts.topnav')
    </x-slot>

    @php
        $statusLabels = [
            'open' => __('messages.status_open'),
            'in_progress' => __('messages.status_in_progress'),
            'resolved' => __('messages.status_resolved'),
            'closed' => __('messages.status_closed'),
        ];
        $statusBadges = [
            'open' => 'secondary',
            'in_progress' => 'warning',
            'resolved' => 'success',
            'closed' => 'dark',
        ];
        $concernTypeLabel = $concern->concern_type ? (\App\Models\DriverConcern::TYPES[$concern->concern_type] ?? $concern->concern_type) : null;
    @endphp

    <div class="container-fluid py-4 mt-4">
        <div class="row justify-content-center">
            <div class="col-xl-9 col-lg-10">
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <a href="{{ route('concerns.driver-concerns.index') }}" class="btn btn-outline-secondary">
                        <i class="bi bi-arrow-left me-1"></i>{{ __('messages.back_to_list') }}
                    </a>
                    <div class="d-flex gap-2">
                        <a href="{{ route('concerns.driver-concerns.edit', $concern) }}" class="btn btn-primary">
                            <i class="bi bi-pencil-square me-1"></i>{{ __('messages.edit') }}
                        </a>
                    </div>
                </div>

                <div class="card border-0 shadow-sm mb-4">
                    <div class="card-body p-4">
                        <div class="d-flex flex-column flex-md-row justify-content-between align-items-start align-items-md-center mb-4">
                            <div>
                                <h4 class="fw-bold mb-1">{{ __('messages.concern_details') }}</h4>
                                <p class="text-muted mb-0">
                                    {{ __('messages.reported_on_by', [
                                        'date' => $concern->reported_at?->format(__('messages.date_format_long') ?? 'd F Y'),
                                        'driver' => $concern->driver?->full_name ?? __('messages.not_available'),
                                    ]) }}
                                </p>
                            </div>
                            <span class="badge bg-{{ $statusBadges[$concern->status] ?? 'secondary' }} bg-opacity-10 text-{{ $statusBadges[$concern->status] ?? 'secondary' }} fs-6 px-4 py-2">
                                {{ $statusLabels[$concern->status] ?? ucfirst(str_replace('_', ' ', $concern->status)) }}
                            </span>
                        </div>

                        <div class="row g-4">
                            <div class="col-md-6">
                                <div class="border rounded-3 p-3 h-100">
                                    <h6 class="text-uppercase text-muted small fw-semibold">{{ __('messages.concern_type') }}</h6>
                                    <p class="fw-semibold mb-1">{{ $concernTypeLabel ?? __('messages.not_available') }}</p>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="border rounded-3 p-3 h-100">
                                    <h6 class="text-uppercase text-muted small fw-semibold">{{ __('messages.vehicle_information') }}</h6>
                                    <p class="mb-2">
                                        <span class="fw-semibold">{{ __('messages.vehicle_licence_plate') }}:</span>
                                        {{ $concern->vehicle_licence_plate ?? __('messages.not_available') }}
                                    </p>
                                    <p class="mb-0">
                                        <span class="fw-semibold">{{ __('messages.responsible_party') }}:</span>
                                        {{ $concern->responsible_party ?? __('messages.not_available') }}
                                    </p>
                                </div>
                            </div>

                            <div class="col-12">
                                <div class="border rounded-3 p-3">
                                    <h6 class="text-uppercase text-muted small fw-semibold">{{ __('messages.description') }}</h6>
                                    <p class="mb-0">{{ $concern->description }}</p>
                                </div>
                            </div>

                            <div class="col-12">
                                <div class="border rounded-3 p-3 bg-light">
                                    <h6 class="text-uppercase text-muted small fw-semibold">{{ __('messages.immediate_action') }}</h6>
                                    <p class="mb-0">{{ $concern->immediate_action ?: __('messages.not_available') }}</p>
                                </div>
                            </div>

                            <div class="col-12">
                                <div class="border rounded-3 p-3">
                                    <h6 class="text-uppercase text-muted small fw-semibold">{{ __('messages.resolution_comments') }}</h6>
                                    <p class="mb-0">{{ $concern->resolution_comments ?: __('messages.not_available') }}</p>
                                </div>
                            </div>
                        </div>

                        <hr class="my-4">

                        <div class="row g-4">
                            <div class="col-md-6">
                                <div class="border rounded-3 p-3 h-100">
                                    <h6 class="text-uppercase text-muted small fw-semibold">{{ __('messages.created_at') }}</h6>
                                    <p class="mb-0">
                                        {{ $concern->created_at->format(__('messages.datetime_format') ?? 'd/m/Y H:i') }}
                                        <span class="text-muted">({{ $concern->created_at->diffForHumans() }})</span>
                                    </p>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="border rounded-3 p-3 h-100">
                                    <h6 class="text-uppercase text-muted small fw-semibold">{{ __('messages.updated_at') }}</h6>
                                    <p class="mb-0">
                                        {{ $concern->updated_at->format(__('messages.datetime_format') ?? 'd/m/Y H:i') }}
                                        <span class="text-muted">({{ $concern->updated_at->diffForHumans() }})</span>
                                    </p>
                                </div>
                            </div>
                            @if($concern->completion_date)
                                <div class="col-md-6">
                                    <div class="border rounded-3 p-3 h-100 bg-success bg-opacity-10">
                                        <h6 class="text-uppercase text-muted small fw-semibold">{{ __('messages.completion_date') }}</h6>
                                        <p class="mb-0 fw-semibold text-success">
                                            {{ $concern->completion_date->format(__('messages.date_format_long') ?? 'd F Y') }}
                                        </p>
                                    </div>
                                </div>
                            @endif
                        </div>
                    </div>
                </div>

                <div class="card border-0 shadow-sm">
                    <div class="card-body p-4">
                        <h5 class="fw-bold mb-3">
                            <i class="bi bi-person-lines-fill me-2 text-primary"></i>{{ __('messages.driver_information') }}
                        </h5>
                        @if($concern->driver)
                            <div class="row g-3">
                                <div class="col-md-6">
                                    <p class="mb-1">
                                        <span class="fw-semibold">{{ __('messages.driver') }}:</span>
                                        {{ $concern->driver->full_name }}
                                    </p>
                                    <p class="mb-1">
                                        <span class="fw-semibold">{{ __('messages.phone') }}:</span>
                                        {{ $concern->driver->phone ?? __('messages.not_available') }}
                                    </p>
                                    <p class="mb-0">
                                        <span class="fw-semibold">{{ __('messages.email') }}:</span>
                                        {{ $concern->driver->email ?? __('messages.not_available') }}
                                    </p>
                                </div>
                                <div class="col-md-6">
                                    <p class="mb-1">
                                        <span class="fw-semibold">{{ __('messages.assigned_vehicle') }}:</span>
                                        {{ $concern->driver->assignedVehicle->license_plate ?? __('messages.not_assigned') }}
                                    </p>
                                    <p class="mb-0">
                                        <a href="{{ route('drivers.show', $concern->driver) }}" class="btn btn-link p-0">
                                            <i class="bi bi-box-arrow-up-right me-1"></i>{{ __('messages.view_driver') }}
                                        </a>
                                    </p>
                                </div>
                            </div>
                        @else
                            <p class="text-muted mb-0">{{ __('messages.driver_not_available') }}</p>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>

