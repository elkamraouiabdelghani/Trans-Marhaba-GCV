<x-app-layout>
    <x-slot name="header">
        @include('layouts.topnav')
    </x-slot>

    <div class="container-fluid py-4 mt-4">
        <!-- Key Performance Indicators -->
        <div class="row g-4 mb-4">

            <!-- Total Drivers Overview -->
            <div class="col-xl-3 col-lg-6 col-md-6">
                <div class="card border-0 shadow-sm h-100">
                    <div class="card-body p-4">
                        <div class="d-flex align-items-center row">
                            <div class="col-3 bg-info bg-opacity-10 rounded-circle d-flex align-items-center justify-content-center me-3" style="width: 50px; height: 50px;">
                                <i class="bi bi-people text-info fs-4"></i>
                            </div>
                            <div class="col-9">
                                <h6 class="text-muted mb-1">{{ __('messages.total_drivers') }}</h6>
                                <h3 class="mb-0 fw-bold text-dark">{{ $totalDrivers ?? 0 }}</h3>
                            </div>
                        </div>
                        <div class="mt-3">
                            <a href="{{ route('drivers.index') }}" class="btn btn-outline-info btn-sm">
                                <i class="bi bi-eye me-1"></i>{{ __('messages.view_all') }}
                            </a>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Violations This Week Overview -->
            <div class="col-xl-3 col-lg-6 col-md-6">
                <div class="card border-0 shadow-sm h-100">
                    <div class="card-body p-4">
                        <div class="d-flex align-items-center row">
                            <div class="col-3 bg-danger bg-opacity-10 rounded-circle d-flex align-items-center justify-content-center me-3" style="width: 50px; height: 50px;">
                                <i class="bi bi-flag text-danger fs-4"></i>
                            </div>
                            <div class="col-9">
                                <h6 class="text-muted mb-1">{{ __('messages.violations_this_week') }}</h6>
                                <h3 class="mb-0 fw-bold text-dark">N/A</h3>
                            </div>
                        </div>
                        <div class="mt-3">
                            <a href="#" class="btn btn-outline-danger btn-sm">
                                <i class="bi bi-eye me-1"></i>{{ __('messages.view_all') }}
                            </a>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Drivers Exceeding Legal Hours Overview -->
            <div class="col-xl-3 col-lg-6 col-md-6">
                <div class="card border-0 shadow-sm h-100">
                    <div class="card-body p-4">
                        <div class="d-flex align-items-center row">
                            <div class="col-3 bg-warning bg-opacity-10 rounded-circle d-flex align-items-center justify-content-center me-3" style="width: 50px; height: 50px;">
                                <i class="bi bi-alarm text-warning fs-4"></i>
                            </div>
                            <div class="col-9">
                                <h6 class="text-muted mb-1">{{ __('messages.drivers_exceeding_legal_hours') }}</h6>
                                <h3 class="mb-0 fw-bold text-dark">N/A</h3>
                            </div>
                        </div>
                        <div class="mt-3">
                            <a href="#" class="btn btn-outline-warning btn-sm">
                                <i class="bi bi-eye me-1"></i>{{ __('messages.view_all') }}
                            </a>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Top Violating Driver Overview -->
            <div class="col-xl-3 col-lg-6 col-md-6">
                <div class="card border-0 shadow-sm h-100">
                    <div class="card-body p-4">
                        <div class="d-flex align-items-center row">
                            <div class="col-3 bg-primary bg-opacity-10 rounded-circle d-flex align-items-center justify-content-center me-3" style="width: 50px; height: 50px;">
                                <i class="bi bi-exclamation-triangle text-primary fs-4"></i>
                            </div>
                            <div class="col-9">
                                <h6 class="text-muted mb-1">{{ __('messages.top_violating_driver') }}</h6>
                                <h3 class="mb-0 fw-bold text-dark">N/A</h3>
                            </div>
                        </div>
                        <div class="mt-3">
                            <a href="#" class="btn btn-outline-primary btn-sm">
                                <i class="bi bi-eye me-1"></i>{{ __('messages.view_details') }}
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>

            <!-- Recent Activity Section -->
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg mb-6">
                <div class="p-6">
                    <h3 class="text-lg font-semibold text-gray-800 mb-4">{{ __('Recent Activity') }}</h3>
                    <div class="text-gray-600">
                        {{ __("You're logged in!") }}
                    </div>
                </div>
            </div>
            
        </div>
    </div>

    <style>
        /* Statistics badges */
        .badge {
            font-weight: 500;
            letter-spacing: 0.5px;
        }

        /* Modern gradient backgrounds */
        .bg-gradient-primary {
            background: linear-gradient(135deg, #0d6efd 0%, #6610f2 100%);
        }

        .bg-gradient-warning {
            background: linear-gradient(135deg, #fd7e14 0%, #ffc107 100%);
        }

        /* Icon circle backgrounds */
        .bg-info.bg-opacity-10 {
            background-color: rgba(13, 202, 240, 0.1) !important;
        }

        .bg-danger.bg-opacity-10 {
            background-color: rgba(220, 53, 69, 0.1) !important;
        }

        .bg-warning.bg-opacity-10 {
            background-color: rgba(255, 193, 7, 0.1) !important;
        }

        .bg-primary.bg-opacity-10 {
            background-color: rgba(13, 110, 253, 0.1) !important;
        }

        .bg-success.bg-opacity-10 {
            background-color: rgba(25, 135, 84, 0.1) !important;
        }
    </style>
</x-app-layout>