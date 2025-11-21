<x-app-layout>
    <x-slot name="header">
        @include('layouts.topnav')
    </x-slot>

    <div class="container-fluid py-4 mt-4">
        <div class="row g-4 mb-4">
            <div class="col-xl-3 col-md-6">
                <div class="card border-0 shadow-sm h-100">
                    <div class="card-body">
                        <div class="d-flex align-items-center">
                            <div class="bg-primary bg-opacity-10 rounded-circle d-flex align-items-center justify-content-center me-3" style="width: 60px; height: 60px;">
                                <i class="bi bi-people text-primary fs-4"></i>
                            </div>
                            <div>
                                <p class="text-muted mb-1">{{ __('messages.total') }}</p>
                                <h3 class="mb-0 fw-bold">{{ $stats['total'] }}</h3>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-xl-3 col-md-6">
                <div class="card border-0 shadow-sm h-100">
                    <div class="card-body">
                        <div class="d-flex align-items-center">
                            <div class="bg-success bg-opacity-10 rounded-circle d-flex align-items-center justify-content-center me-3" style="width: 60px; height: 60px;">
                                <i class="bi bi-check-circle text-success fs-4"></i>
                            </div>
                            <div>
                                <p class="text-muted mb-1">{{ __('messages.status_active') }}</p>
                                <h3 class="mb-0 fw-bold">{{ $stats['active'] }}</h3>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-xl-3 col-md-6">
                <div class="card border-0 shadow-sm h-100">
                    <div class="card-body">
                        <div class="d-flex align-items-center">
                            <div class="bg-warning bg-opacity-10 rounded-circle d-flex align-items-center justify-content-center me-3" style="width: 60px; height: 60px;">
                                <i class="bi bi-arrow-repeat text-warning fs-4"></i>
                            </div>
                            <div>
                                <p class="text-muted mb-1">{{ __('messages.status_on_leave') }}</p>
                                <h3 class="mb-0 fw-bold">{{ $stats['on_leave'] }}</h3>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-xl-3 col-md-6">
                <div class="card border-0 shadow-sm h-100">
                    <div class="card-body">
                        <div class="d-flex align-items-center">
                            <div class="bg-info bg-opacity-10 rounded-circle d-flex align-items-center justify-content-center me-3" style="width: 60px; height: 60px;">
                                <i class="bi bi-award text-info fs-4"></i>
                            </div>
                            <div>
                                <p class="text-muted mb-1">{{ __('messages.integrated_staff') }}</p>
                                <h3 class="mb-0 fw-bold">{{ $stats['integrated'] }}</h3>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="card border-0 shadow-sm">
            <div class="card-header bg-white border-0 py-3">
                <div class="d-flex flex-column flex-lg-row align-items-lg-center justify-content-between">
                    <div class="mb-3 mb-lg-0">
                        <h5 class="mb-1 text-dark fw-bold">
                            <i class="bi bi-person-badge me-2 text-primary"></i>
                            {{ __('messages.administration_roles') }}
                        </h5>
                    </div>
                    <div class="d-flex flex-column flex-md-row gap-2">
                        <form method="GET" action="{{ route('administration-roles.index') }}" class="d-flex flex-column flex-md-row gap-2">
                            <div class="input-group border rounded-1">
                                <span class="input-group-text bg-light border-0"><i class="bi bi-search"></i></span>
                                <input type="text" name="search" class="form-control border-0" placeholder="{{ __('messages.search_user_placeholder') }}" value="{{ $search }}">
                            </div>
                            <select name="status" class="form-select">
                                <option value="all" {{ $status === 'all' ? 'selected' : '' }}>{{ __('messages.all_statuses') }}</option>
                                <option value="active" {{ $status === 'active' ? 'selected' : '' }}>{{ __('messages.status_active') }}</option>
                                <option value="inactive" {{ $status === 'inactive' ? 'selected' : '' }}>{{ __('messages.status_inactive') }}</option>
                                <option value="on_leave" {{ $status === 'on_leave' ? 'selected' : '' }}>{{ __('messages.status_on_leave') }}</option>
                                <option value="terminated" {{ $status === 'terminated' ? 'selected' : '' }}>{{ __('messages.status_terminated') }}</option>
                            </select>
                            <div class="d-flex gap-2">
                                <button type="submit" class="btn btn-dark">
                                    <i class="bi bi-funnel"></i>
                                </button>
                                <a href="{{ route('administration-roles.index') }}" class="btn btn-outline-secondary">
                                    <i class="bi bi-arrow-counterclockwise"></i>
                                </a>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead class="table-light">
                            <tr>
                                <th class="px-4">{{ __('messages.name') }}</th>
                                <th>{{ __('messages.phone') }}</th>
                                <th>{{ __('messages.department') }}</th>
                                <th>{{ __('messages.role') }}</th>
                                <th>{{ __('messages.status') }}</th>
                                <th>{{ __('messages.date_integration') }}</th>
                                <th class="text-center">{{ __('messages.actions') }}</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($users as $user)
                                <tr>
                                    <td class="px-4">
                                        <div class="d-flex align-items-center">
                                            <div class="bg-primary bg-opacity-10 rounded-circle px-2 py-1 me-3">
                                                <i class="bi bi-person text-primary"></i>
                                            </div>
                                            <div>
                                                <div class="fw-semibold text-dark">{{ $user->name }}</div>
                                                <small class="text-muted">{{ $user->email }}</small>
                                            </div>
                                        </div>
                                    </td>
                                    <td>{{ $user->phone ?? __('messages.not_available') }}</td>
                                    <td>
                                        <span class="badge bg-secondary bg-opacity-10 text-secondary text-uppercase">{{ $user->department ?? __('messages.not_available') }}</span>
                                    </td>
                                    <td>
                                        <span class="badge bg-primary bg-opacity-10 text-primary text-uppercase">{{ $user->role }}</span>
                                    </td>
                                    <td>
                                        @php
                                            $statusColorMap = [
                                                'active' => 'success',
                                                'inactive' => 'secondary',
                                                'on_leave' => 'warning',
                                                'terminated' => 'danger',
                                            ];
                                            $statusKey = 'status_' . ($user->status ?? 'inactive');
                                            $badgeColor = $statusColorMap[$user->status] ?? 'secondary';
                                        @endphp
                                        <span class="badge bg-{{ $badgeColor }} bg-opacity-10 text-{{ $badgeColor }}">
                                            {{ __('messages.' . $statusKey) }}
                                        </span>
                                    </td>
                                    <td>
                                        {{ optional($user->date_integration)->format(__('messages.date_format_short')) ?? __('messages.not_available') }}
                                    </td>
                                    <td class="text-center">
                                        <a href="{{ route('administration-roles.show', $user) }}" class="btn btn-outline-primary btn-sm" title="{{ __('messages.view_details') }}">
                                            <i class="bi bi-eye"></i>
                                        </a>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="7" class="text-center py-5">
                                        <div class="text-muted">
                                            <i class="bi bi-archive display-5 d-block mb-3"></i>
                                            <h5>{{ __('messages.no_administration_roles_found') }}</h5>
                                            <p class="mb-0">{{ __('messages.try_adjusting_filters') }}</p>
                                        </div>
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
            <div class="card-footer bg-white border-0">
                {{ $users->links() }}
            </div>
        </div>
    </div>
</x-app-layout>

