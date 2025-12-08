<x-app-layout>
    <x-slot name="header">
        @include('layouts.topnav')
    </x-slot>

    <div class="container-fluid py-4 mt-4">
        <!-- Header -->
        <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center mb-4 gap-3 mx-auto bg-white p-4 rounded-3 shadow-sm">
            <div>
                <h1 class="h4 mb-1">{{ __('messages.formation_theme') }}: {{ $formation->theme }}</h1>
                <p class="text-muted mb-0">
                    {{ __('messages.formation_type_label') }}: {{ $formation->type_label }}
                </p>
            </div>
            <div class="d-flex gap-2">
                <a href="{{ route('formations.index') }}" class="btn btn-sm btn-outline-secondary">
                    <i class="bi bi-arrow-left me-1"></i> {{ __('messages.back_to_list') ?? 'Back to List' }}
                </a>
                <a href="{{ route('formations.edit', $formation) }}" class="btn btn-sm btn-warning">
                    <i class="bi bi-pencil me-1"></i> {{ __('messages.edit') }}
                </a>
            </div>
        </div>

        @if (session('success'))
            <div class="alert alert-success alert-dismissible fade show" role="alert">
                <i class="bi bi-check-circle me-2"></i>
                {{ session('success') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        @endif

        @if (session('error'))
            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                <i class="bi bi-exclamation-triangle me-2"></i>
                {{ session('error') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        @endif

        <!-- Statistics Cards -->
        <div class="row mb-4">
            <div class="col-md-3 mb-3">
                <div class="card border-0 shadow-sm h-100">
                    <div class="card-body">
                        <div class="d-flex align-items-center">
                            <div class="flex-shrink-0">
                                <div class="bg-primary bg-opacity-10 rounded-circle d-flex align-items-center justify-content-center" style="width: 60px; height: 60px;">
                                    <i class="bi bi-people text-primary" style="font-size: 1.5rem;"></i>
                                </div>
                            </div>
                            <div class="flex-grow-1 ms-3">
                                <h6 class="text-muted mb-1 small">{{ __('messages.total_drivers') ?? 'Total Drivers' }}</h6>
                                <h3 class="mb-0 fw-bold text-dark">{{ $totalDrivers }}</h3>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-3 mb-3">
                <div class="card border-0 shadow-sm h-100">
                    <div class="card-body">
                        <div class="d-flex align-items-center">
                            <div class="flex-shrink-0">
                                <div class="bg-success bg-opacity-10 rounded-circle d-flex align-items-center justify-content-center" style="width: 60px; height: 60px;">
                                    <i class="bi bi-check2-circle text-success" style="font-size: 1.5rem;"></i>
                                </div>
                            </div>
                            <div class="flex-grow-1 ms-3">
                                <h6 class="text-muted mb-1 small">{{ __('messages.completed') ?? 'Completed' }}</h6>
                                <h3 class="mb-0 fw-bold text-dark">{{ $completedDrivers }}</h3>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-3 mb-3">
                <div class="card border-0 shadow-sm h-100">
                    <div class="card-body">
                        <div class="d-flex align-items-center">
                            <div class="flex-shrink-0">
                                <div class="bg-warning bg-opacity-10 rounded-circle d-flex align-items-center justify-content-center" style="width: 60px; height: 60px;">
                                    <i class="bi bi-calendar-week text-warning" style="font-size: 1.5rem;"></i>
                                </div>
                            </div>
                            <div class="flex-grow-1 ms-3">
                                <h6 class="text-muted mb-1 small">{{ __('messages.planned') ?? 'Planned' }}</h6>
                                <h3 class="mb-0 fw-bold text-dark">{{ $plannedDrivers }}</h3>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-3 mb-3">
                <div class="card border-0 shadow-sm h-100">
                    <div class="card-body">
                        <div class="d-flex align-items-center">
                            <div class="flex-shrink-0">
                                <div class="bg-info bg-opacity-10 rounded-circle d-flex align-items-center justify-content-center" style="width: 60px; height: 60px;">
                                    <i class="bi bi-percent text-info" style="font-size: 1.5rem;"></i>
                                </div>
                            </div>
                            <div class="flex-grow-1 ms-3">
                                <h6 class="text-muted mb-1 small">{{ __('messages.completion_percentage') ?? 'Completion %' }}</h6>
                                <h3 class="mb-0 fw-bold text-dark">{{ $completionPercentage }}%</h3>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="row g-4">
            <!-- Formation Information -->
            <div class="col-lg-8">
                <div class="card border-0 shadow-sm mb-4">
                    <div class="card-header bg-white border-bottom py-3">
                        <h5 class="mb-0">
                            <i class="bi bi-info-circle me-2 text-primary"></i>
                            {{ __('messages.formation_information') ?? 'Formation Information' }}
                        </h5>
                    </div>
                    <div class="card-body">
                        <div class="row g-3">
                            <div class="col-md-6">
                                <label class="form-label text-muted small">{{ __('messages.formation_theme') }}</label>
                                <p class="mb-0 fw-semibold">{{ $formation->theme ?? '-' }}</p>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label text-muted small">{{ __('messages.formation_type_label') }}</label>
                                <p class="mb-0">
                                    <span class="badge bg-light text-dark">{{ $formation->type_label }}</span>
                                </p>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label text-muted small">{{ __('messages.flotte') }}</label>
                                <p class="mb-0">
                                    @if($formation->flotte)
                                        <span class="badge bg-primary bg-opacity-25 text-primary">{{ $formation->flotte->name }}</span>
                                    @else
                                        <span class="badge bg-secondary bg-opacity-25 text-secondary">{{ __('messages.not_assigned') ?? 'Not Assigned' }}</span>
                                    @endif
                                </p>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label text-muted small">{{ __('messages.formation_delivery_type') }}</label>
                                <p class="mb-0">
                                    <span class="badge bg-info bg-opacity-25 text-info">
                                        {{ $formation->delivery_type === 'interne' ? __('messages.formation_delivery_internal') : __('messages.formation_delivery_external') }}
                                    </span>
                                </p>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label text-muted small">{{ __('messages.formation_participant') }}</label>
                                <p class="mb-0 fw-semibold">{{ $formation->participant ?? '-' }}</p>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label text-muted small">{{ __('messages.formation_duration') }}</label>
                                <p class="mb-0 fw-semibold">{{ $formation->duree ?? '-' }} {{ __('messages.days') ?? 'days' }}</p>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label text-muted small">{{ __('messages.formation_realizing_date') }}</label>
                                <p class="mb-0 fw-semibold">
                                    {{ $formation->realizing_date ? $formation->realizing_date->format('d/m/Y') : '-' }}
                                </p>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label text-muted small">{{ __('messages.formation_status') }}</label>
                                <p class="mb-0">
                                    @if($formation->status === 'realized')
                                        <span class="badge bg-success">{{ __('messages.realized') ?? 'Realized' }}</span>
                                    @elseif($formation->status === 'planned')
                                        <span class="badge bg-warning">{{ __('messages.planned') ?? 'Planned' }}</span>
                                    @else
                                        <span class="badge bg-secondary">{{ $formation->status ?? '-' }}</span>
                                    @endif
                                </p>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label text-muted small">{{ __('messages.formation_organisme') }}</label>
                                <p class="mb-0 fw-semibold">{{ $formation->organisme ?? '-' }}</p>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label text-muted small">{{ __('messages.formation_active') }} / {{ __('messages.formation_inactive') }}</label>
                                <p class="mb-0">
                                    @if($formation->is_active)
                                        <span class="badge bg-success">{{ __('messages.formation_active') }}</span>
                                    @else
                                        <span class="badge bg-secondary">{{ __('messages.formation_inactive') }}</span>
                                    @endif
                                </p>
                            </div>
                            @if($formation->description)
                            <div class="col-12">
                                <label class="form-label text-muted small">{{ __('messages.formation_description') }}</label>
                                <p class="mb-0">{{ $formation->description }}</p>
                            </div>
                            @endif
                            @if($formation->reference_value && $formation->reference_unit)
                            <div class="col-md-6">
                                <label class="form-label text-muted small">{{ __('messages.reference_value') ?? 'Reference Value' }}</label>
                                <p class="mb-0 fw-semibold">{{ $formation->reference_value }} {{ $formation->reference_unit }}</p>
                            </div>
                            @endif
                        </div>
                    </div>
                </div>
            </div>

            <!-- Quick Actions -->
            <div class="col-lg-4">
                <div class="card border-0 shadow-sm">
                    <div class="card-header bg-white border-bottom py-3">
                        <h5 class="mb-0">
                            <i class="bi bi-lightning-charge me-2 text-warning"></i>
                            {{ __('messages.quick_actions') ?? 'Quick Actions' }}
                        </h5>
                    </div>
                    <div class="card-body">
                        <div class="d-grid gap-2">
                            <a href="{{ route('formations.edit', $formation) }}" class="btn btn-sm btn-warning">
                                <i class="bi bi-pencil me-1"></i> {{ __('messages.edit') }}
                            </a>
                            @if($formation->status !== 'realized')
                                <form action="{{ route('formations.mark-realized', $formation) }}" method="POST" class="d-inline">
                                    @csrf
                                    <button type="submit" class="btn btn-sm btn-success w-100" onclick="return confirm('{{ __('messages.confirm_mark_realized_message', ['name' => $formation->theme]) }}')">
                                        <i class="bi bi-check-circle me-1"></i> {{ __('messages.mark_as_realized') ?? 'Mark as Realized' }}
                                    </button>
                                </form>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Drivers Table -->
        <div class="card border-0 shadow-sm">
            <div class="card-header bg-white border-bottom py-3">
                <h5 class="mb-0">
                    <i class="bi bi-people me-2 text-primary"></i>
                    {{ __('messages.drivers_with_formation') ?? 'Drivers with this Formation' }}
                </h5>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover mb-0 align-middle">
                        <thead class="table-light">
                            <tr>
                                <th class="ps-3">{{ __('messages.driver') }}</th>
                                <th>{{ __('messages.flotte') }}</th>
                                <th>{{ __('messages.status') }}</th>
                                <th>{{ __('messages.planned_at') ?? 'Planned At' }}</th>
                                <th>{{ __('messages.done_at') ?? 'Done At' }}</th>
                                <th>{{ __('messages.validation_status') ?? 'Validation Status' }}</th>
                                <th class="text-end pe-3">{{ __('messages.actions') }}</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($driverFormations as $driverFormation)
                                <tr>
                                    <td class="ps-3">
                                        @if($driverFormation->driver)
                                            <a href="{{ route('drivers.show', $driverFormation->driver) }}" class="text-decoration-none">
                                                <strong>{{ $driverFormation->driver->full_name ?? '-' }}</strong>
                                            </a>
                                        @else
                                            <span class="text-muted">-</span>
                                        @endif
                                    </td>
                                    <td>
                                        @if($driverFormation->driver && $driverFormation->driver->flotte)
                                            <span class="badge bg-primary bg-opacity-25 text-primary">
                                                {{ $driverFormation->driver->flotte->name }}
                                            </span>
                                        @else
                                            <span class="text-muted">-</span>
                                        @endif
                                    </td>
                                    <td>
                                        @if($driverFormation->status === 'done')
                                            <span class="badge bg-success">{{ __('messages.completed') ?? 'Completed' }}</span>
                                        @elseif($driverFormation->status === 'planned')
                                            <span class="badge bg-warning">{{ __('messages.planned') ?? 'Planned' }}</span>
                                        @else
                                            <span class="badge bg-secondary">{{ $driverFormation->status ?? '-' }}</span>
                                        @endif
                                    </td>
                                    <td>
                                        {{ $driverFormation->planned_at ? $driverFormation->planned_at->format('d/m/Y') : '-' }}
                                    </td>
                                    <td>
                                        {{ $driverFormation->done_at ? $driverFormation->done_at->format('d/m/Y') : '-' }}
                                    </td>
                                    <td>
                                        @if($driverFormation->validation_status === 'validated')
                                            <span class="badge bg-success">{{ __('messages.validated') ?? 'Validated' }}</span>
                                        @elseif($driverFormation->validation_status === 'pending')
                                            <span class="badge bg-warning">{{ __('messages.pending') ?? 'Pending' }}</span>
                                        @else
                                            <span class="badge bg-secondary">{{ $driverFormation->validation_status ?? '-' }}</span>
                                        @endif
                                    </td>
                                    <td class="text-end pe-3">
                                        @if($driverFormation->driver)
                                            <a href="{{ route('drivers.show', $driverFormation->driver) }}" class="btn btn-sm btn-outline-primary" title="{{ __('messages.view_driver') ?? 'View Driver' }}">
                                                <i class="bi bi-eye"></i>
                                            </a>
                                        @endif
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="8" class="text-center py-5 text-muted">
                                        <i class="bi bi-inbox fs-1 d-block mb-2"></i>
                                        {{ __('messages.no_drivers_found') ?? 'No drivers found for this formation.' }}
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>

