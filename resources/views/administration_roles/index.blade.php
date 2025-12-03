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
                @php
                    $activeCardParams = array_filter([
                        'status' => $status === 'active' ? null : 'active',
                        'search' => $status === 'active' ? $search : null,
                    ]);
                @endphp
                <a href="{{ route('administration-roles.index', $activeCardParams) }}" class="text-decoration-none">
                    <div class="card border-0 shadow-sm h-100 {{ $status === 'active' ? 'border-2 border-success' : '' }}">
                        <div class="card-body">
                            <div class="d-flex align-items-center">
                                <div class="bg-success bg-opacity-10 rounded-circle d-flex align-items-center justify-content-center me-3" style="width: 60px; height: 60px;">
                                    <i class="bi bi-check-circle text-success fs-4"></i>
                                </div>
                                <div>
                                    <p class="text-muted mb-1">{{ __('messages.status_active') }}</p>
                                    <h3 class="mb-0 fw-bold text-dark">{{ $stats['active'] }}</h3>
                                </div>
                            </div>
                        </div>
                        <span class="stretched-link"></span>
                    </div>
                </a>
            </div>
            <div class="col-xl-3 col-md-6">
                @php
                    $onLeaveCardParams = array_filter([
                        'status' => $status === 'on_leave' ? null : 'on_leave',
                        'search' => $status === 'on_leave' ? $search : null,
                    ]);
                @endphp
                <a href="{{ route('administration-roles.index', $onLeaveCardParams) }}" class="text-decoration-none">
                    <div class="card border-0 shadow-sm h-100 {{ $status === 'on_leave' ? 'border-2 border-warning' : '' }}">
                        <div class="card-body">
                            <div class="d-flex align-items-center">
                                <div class="bg-warning bg-opacity-10 rounded-circle d-flex align-items-center justify-content-center me-3" style="width: 60px; height: 60px;">
                                    <i class="bi bi-arrow-repeat text-warning fs-4"></i>
                                </div>
                                <div>
                                    <p class="text-muted mb-1">{{ __('messages.status_on_leave') }}</p>
                                    <h3 class="mb-0 fw-bold text-dark">{{ $stats['on_leave'] }}</h3>
                                </div>
                            </div>
                        </div>
                        <span class="stretched-link"></span>
                    </div>
                </a>
            </div>
            <div class="col-xl-3 col-md-6">
                <a href="{{ route('administration-roles.terminated') }}" class="text-decoration-none">
                    <div class="card border-0 shadow-sm h-100 position-relative overflow-hidden">
                        <div class="card-body">
                            <div class="d-flex align-items-center">
                                <div class="bg-dark bg-opacity-10 rounded-circle d-flex align-items-center justify-content-center me-3" style="width: 60px; height: 60px;">
                                    <i class="bi bi-person-x text-dark fs-4"></i>
                                </div>
                                <div>
                                    <p class="text-muted mb-1">{{ __('messages.terminated_staff') }}</p>
                                    <h3 class="mb-0 fw-bold text-dark">{{ $stats['terminated'] }}</h3>
                                </div>
                            </div>
                        </div>
                        <span class="stretched-link"></span>
                    </div>
                </a>
            </div>
        </div>

        <div class="card border-0 shadow-sm">
            <div class="card-header bg-white border-0 py-3">
                <div class="d-flex flex-column flex-lg-row align-items-lg-center justify-content-between">
                    <div class="mb-3 mb-lg-0">
                        <h5 class="mb-1 text-dark fw-bold" style="width: 200px;">
                            <i class="bi bi-person-badge me-2 text-primary"></i>
                            {{ __('messages.administration_roles') }}
                        </h5>
                    </div>
                    <div class="d-flex flex-column flex-md-row gap-3 align-items-md-center w-100 justify-content-md-end">
                        <div class="input-group input-group-sm border rounded-md" style="max-width: 320px;">
                            <span class="input-group-text bg-light border-0"><i class="bi bi-search"></i></span>
                            <input
                                type="text"
                                id="adminSearch"
                                class="form-control border-0"
                                placeholder="{{ __('messages.search_user_placeholder') }}"
                                value="{{ $search }}"
                            >
                        </div>
                        <span id="adminSearchCount" class="text-muted small"></span>
                        @php
                            $exportParams = array_filter(request()->only(['status', 'search']), fn($value) => $value !== null && $value !== '');
                        @endphp
                        <a
                            href="{{ route('administration-roles.export', $exportParams) }}"
                            class="btn btn-success btn-sm d-flex align-items-center gap-2"
                        >
                            <i class="bi bi-download"></i>
                            {{ __('messages.export_excel') }}
                        </a>
                        <a href="{{ route('administration-roles.create') }}" class="btn btn-dark btn-sm">
                            <i class="bi bi-person-plus me-1"></i>
                            {{ __('messages.create_new_user') ?? 'Create New User' }}
                        </a>
                    </div>
                </div>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead class="table-light">
                            <tr>
                                <th class="px-4">{{ __('messages.name') }}</th>
                                <th>{{ __('messages.age') }}</th>
                                <th>{{ __('messages.phone') }}</th>
                                <th>{{ __('messages.department') }}</th>
                                <th>{{ __('messages.role') }}</th>
                                <th>{{ __('messages.status') }}</th>
                                <th>{{ __('messages.date_integration') }}</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($users as $user)
                                <tr class="administration-row" data-admin-url="{{ route('administration-roles.show', $user) }}" style="cursor: pointer;">
                                    <td class="px-4">
                                        <div class="d-flex align-items-center">
                                            @if($user->profile_photo_path)
                                                <img src="{{ route('administration-roles.profile-photo', $user) }}"
                                                     alt="{{ $user->name }}"
                                                     class="rounded-circle me-3"
                                                     style="width: 40px; height: 40px; object-fit: cover;">
                                            @else
                                                <div class="bg-primary bg-opacity-10 rounded-circle px-2 py-1 me-3">
                                                    <i class="bi bi-person text-primary"></i>
                                                </div>
                                            @endif
                                            <div>
                                                <div class="fw-semibold text-dark">{{ $user->name }}</div>
                                                <small class="text-muted">{{ $user->email }}</small>
                                            </div>
                                        </div>
                                    </td>
                                    <td>
                                        @php
                                            $age = $user->date_of_birth ? \Carbon\Carbon::parse($user->date_of_birth)->age : null;
                                        @endphp
                                        @if($age)
                                            <span class="badge bg-primary bg-opacity-10 text-primary fw-semibold">
                                                {{ $age }} {{ __('messages.years') ?? 'years' }}
                                            </span>
                                        @else
                                            <span class="text-muted">—</span>
                                        @endif
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

    <script>
        document.addEventListener('DOMContentLoaded', function () {
            document.querySelectorAll('.administration-row').forEach(function (row) {
                row.addEventListener('click', function () {
                    const url = this.dataset.adminUrl;
                    if (url) {
                        window.location.href = url;
                    }
                });
            });
                const adminSearchInput = document.getElementById('adminSearch');
                const adminSearchCount = document.getElementById('adminSearchCount');
                const adminRows = Array.from(document.querySelectorAll('.administration-row'));
                const resultsLabel = "{{ __('messages.results') }}";

                function normalize(str) {
                    return (str || '').toString().trim().toLowerCase();
                }

                function filterAdminRows(query) {
                    const normalized = normalize(query);
                    let visible = 0;

                    adminRows.forEach(row => {
                        const nameCell = row.querySelector('.fw-semibold');
                        const emailCell = row.querySelector('small.text-muted');
                        const text = normalize((nameCell?.textContent || '') + ' ' + (emailCell?.textContent || ''));

                        const show = normalized === '' || text.includes(normalized);
                        row.style.display = show ? '' : 'none';
                        if (show) visible++;
                    });

                    if (adminSearchCount) {
                        adminSearchCount.textContent = normalized ? visible + ' ' + resultsLabel : '';
                    }
                }

                if (adminSearchInput) {
                    let debounce;
                    adminSearchInput.addEventListener('input', function () {
                        clearTimeout(debounce);
                        const value = this.value;
                        debounce = setTimeout(() => filterAdminRows(value), 150);
                    });
                    filterAdminRows(adminSearchInput.value);
                }
        });
    </script>
</x-app-layout>

