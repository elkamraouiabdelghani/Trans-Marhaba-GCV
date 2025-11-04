<x-app-layout>
    <x-slot name="header">
        @include('layouts.topnav')
    </x-slot>

    <div class="container-fluid py-4">
        <!-- Stats Cards -->
        <div class="row g-4 mb-4">
            <div class="col-xl-3 col-lg-6 col-md-6">
                <div class="card border-0 shadow-sm h-100">
                    <div class="card-body p-4">
                        <div class="d-flex align-items-center">
                            <div class="bg-primary bg-opacity-10 rounded-circle d-flex align-items-center justify-content-center me-3" style="width: 60px; height: 60px;">
                                <i class="bi bi-people text-primary fs-4"></i>
                            </div>
                            <div class="flex-grow-1">
                                <h6 class="text-muted mb-1">{{ __('messages.total') }}</h6>
                                <h3 class="mb-0 fw-bold text-dark">{{ $total ?? 0 }}</h3>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-xl-3 col-lg-6 col-md-6">
                <div class="card border-0 shadow-sm h-100">
                    <div class="card-body p-4">
                        <div class="d-flex align-items-center">
                            <div class="bg-success bg-opacity-10 rounded-circle d-flex align-items-center justify-content-center me-3" style="width: 60px; height: 60px;">
                                <i class="bi bi-check-circle text-success fs-4"></i>
                            </div>
                            <div class="flex-grow-1">
                                <h6 class="text-muted mb-1">{{ __('messages.active') }}</h6>
                                <h3 class="mb-0 fw-bold text-dark">{{ $active ?? 0 }}</h3>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-xl-3 col-lg-6 col-md-6">
                <div class="card border-0 shadow-sm h-100">
                    <div class="card-body p-4">
                        <div class="d-flex align-items-center">
                            <div class="bg-danger bg-opacity-10 rounded-circle d-flex align-items-center justify-content-center me-3" style="width: 60px; height: 60px;">
                                <i class="bi bi-x-circle text-danger fs-4"></i>
                            </div>
                            <div class="flex-grow-1">
                                <h6 class="text-muted mb-1">{{ __('messages.inactive') }}</h6>
                                <h3 class="mb-0 fw-bold text-dark">{{ $inactive ?? 0 }}</h3>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Drivers Table -->
        <div class="card border-0 shadow-sm">
            <div class="card-header bg-white border-0 py-3">
                <div class="d-flex justify-content-between align-items-center">
                    <h5 class="mb-0 text-dark fw-bold">
                        <i class="bi bi-list-ul me-2 text-primary"></i>
                        {{ __('messages.drivers_page_title') }}
                    </h5>
                    <div class="d-flex gap-2">
                        <div class="col-md-12">
                            <div class="input-group input-group-sm">
                                <span class="input-group-text">
                                    <i class="bi bi-search"></i>
                                </span>
                                <input type="text" class="form-control" id="driverSearch" placeholder="{{ __('messages.search_by_name') }}" onkeyup="searchDrivers()">
                            </div>
                            <span id="driverSearchCount" class="text-muted small mt-2"></span>
                        </div>
                    </div>
                </div>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover mb-0 align-middle" id="driversTable">
                        <thead class="table-light">
                            <tr>
                                <th class="border-0 py-3 px-4">{{ __('messages.name') }}</th>
                                <th class="border-0 py-3 px-4">{{ __('messages.phone_number') }}</th>
                                <th class="border-0 py-3 px-4">{{ __('messages.vehicle_assign_matricule') }}</th>
                                <th class="border-0 py-3 px-4">{{ __('messages.flotte') }}</th>
                                <th class="border-0 py-3 px-4">{{ __('messages.status') }}</th>
                                <th class="border-0 py-3 px-4 text-center">{{ __('messages.action') }}</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($drivers ?? [] as $driver)
                                <tr class="border-bottom">
                                    <td class="py-3 px-4">
                                        <div class="d-flex align-items-center">
                                            <div class="bg-primary bg-opacity-10 rounded-circle px-2 py-1 me-3">
                                                <i class="bi bi-person text-primary"></i>
                                            </div>
                                            <div class="flex-grow-1">
                                                <strong class="text-dark">
                                                    {{ data_get($driver, 'full_name') ?? 'N/A' }}
                                                </strong>
                                            </div>
                                        </div>
                                    </td>
                                    <td class="py-3 px-4">
                                        @php
                                            $phone = data_get($driver, 'phone') ?? data_get($driver, 'phone_number') ?? data_get($driver, 'phone_numbre');
                                        @endphp
                                        @if($phone)
                                            <a href="tel:{{ $phone }}" class="text-decoration-none">
                                                {{ $phone }}
                                            </a>
                                        @else
                                            <span class="text-muted">N/A</span>
                                        @endif
                                    </td>
                                    <td class="py-3 px-4">
                                        @php
                                            // Try to get from relationship first
                                            $matricule = null;
                                            if ($driver->assignedVehicle && $driver->assignedVehicle->license_plate) {
                                                $matricule = $driver->assignedVehicle->license_plate;
                                            } else {
                                                // Fallback to direct attributes
                                                $matricule = data_get($driver, 'vehicle_matricule') 
                                                    ?? data_get($driver, 'matricule') 
                                                    ?? data_get($driver, 'assigned_vehicle_matricule');
                                            }
                                        @endphp
                                        @if($matricule)
                                            <div class="d-flex align-items-center">
                                                <div class="bg-success bg-opacity-10 rounded-circle px-2 py-1 me-2">
                                                    <i class="bi bi-truck text-success"></i>
                                                </div>
                                                <span class="text-success">
                                                    <strong>{{ $matricule }}</strong>
                                                </span>
                                            </div>
                                        @else
                                            <span class="text-muted">N/A</span>
                                        @endif
                                    </td>
                                    <td class="py-3 px-4">
                                        @if($driver->flotte && $driver->flotte->name)
                                            <div class="d-flex align-items-center">
                                                <div class="bg-info bg-opacity-10 rounded-circle px-2 py-1 me-2">
                                                    <i class="bi bi-building text-info"></i>
                                                </div>
                                                <span class="text-info">
                                                    <strong>{{ $driver->flotte->name }}</strong>
                                                </span>
                                            </div>
                                        @else
                                            <span class="text-muted">N/A</span>
                                        @endif
                                    </td>
                                    <td class="py-3 px-4">
                                        @php
                                            $status = data_get($driver, 'status') ?? data_get($driver, 'statu') ?? data_get($driver, 'state') ?? 'inactive';
                                            $statusLower = strtolower(trim((string)$status));
                                            $statusColors = [
                                                'active' => 'success',
                                                'actif' => 'success',
                                                'enabled' => 'success',
                                                'enable' => 'success',
                                                '1' => 'success',
                                                'true' => 'success',
                                                'yes' => 'success',
                                                'inactive' => 'secondary',
                                                'inactif' => 'secondary',
                                                'disabled' => 'secondary',
                                                'disable' => 'secondary',
                                                'deactive' => 'secondary',
                                                '0' => 'secondary',
                                                'false' => 'secondary',
                                                'no' => 'secondary',
                                            ];
                                            $color = $statusColors[$statusLower] ?? 'secondary';
                                            $statusLabels = [
                                                'active' => __('messages.active'),
                                                'actif' => __('messages.active'),
                                                'enabled' => __('messages.active'),
                                                'enable' => __('messages.active'),
                                                '1' => __('messages.active'),
                                                'true' => __('messages.active'),
                                                'yes' => __('messages.active'),
                                                'inactive' => __('messages.inactive'),
                                                'inactif' => __('messages.inactive'),
                                                'disabled' => __('messages.inactive'),
                                                'disable' => __('messages.inactive'),
                                                'deactive' => __('messages.inactive'),
                                                '0' => __('messages.inactive'),
                                                'false' => __('messages.inactive'),
                                                'no' => __('messages.inactive'),
                                            ];
                                            $label = $statusLabels[$statusLower] ?? __('messages.inactive');
                                        @endphp
                                        <span class="badge bg-{{ $color }} bg-opacity-10 text-{{ $color }}">
                                            {{ $label }}
                                        </span>
                                    </td>
                                    <td class="py-3 px-4 text-center">
                                        <div class="btn-group" role="group">
                                            <a href="{{ route('drivers.show', $driver) }}" class="btn btn-outline-success btn-sm" title="{{ __('messages.view') }}">
                                                <i class="bi bi-eye"></i>
                                            </a>
                                            @if(!$driver->is_integrated)
                                                <a href="{{ route('drivers.integrations', ['driver_id' => $driver->id]) }}" class="btn btn-outline-primary btn-sm" title="{{ __('messages.start_integration') }}">
                                                    <i class="bi bi-person-plus"></i>
                                                </a>
                                            @endif
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="6" class="text-center py-5">
                                        <div class="text-muted">
                                            <i class="bi bi-people display-1 mb-3"></i>
                                            <h5 class="mb-2">{{ __('messages.no_drivers_found') }}</h5>
                                        </div>
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <script>
        function normalize(str){ 
            return (str || '').toString().trim().toLowerCase(); 
        }

        function filterDrivers(value){
            const table = document.querySelector('#driversTable');
            if (!table) return;
            const tbody = table.querySelector('tbody');
            const rows = Array.from(tbody.querySelectorAll('tr'));
            const countEl = document.getElementById('driverSearchCount');
            const resultsLabel = "{{ __('messages.results') }}";

            const q = normalize(value);
            let visible = 0;
            rows.forEach(row => {
                const nameCell = row.querySelector('td'); // first column is name
                const text = normalize(nameCell ? nameCell.textContent : '');
                const show = q === '' || text.includes(q);
                row.style.display = show ? '' : 'none';
                if (show) visible++;
            });
            if (countEl) {
                countEl.textContent = q ? visible + ' ' + resultsLabel : '';
            }
        }

        function searchDrivers() {
            const input = document.getElementById('driverSearch');
            if (!input) return;
            filterDrivers(input.value);
        }

        // Initialize search on page load
        document.addEventListener('DOMContentLoaded', function() {
            const input = document.getElementById('driverSearch');
            if (input) {
                let timer = null;
                input.addEventListener('input', function(){
                    clearTimeout(timer);
                    const val = this.value;
                    timer = setTimeout(() => filterDrivers(val), 120);
                });
            }
        });
    </script>

    <style>
        /* Icon circle backgrounds */
        .bg-primary.bg-opacity-10 {
            background-color: rgba(13, 110, 253, 0.1) !important;
        }

        .bg-success.bg-opacity-10 {
            background-color: rgba(25, 135, 84, 0.1) !important;
        }

        .bg-danger.bg-opacity-10 {
            background-color: rgba(220, 53, 69, 0.1) !important;
        }

        /* Table hover effect */
        .table-hover tbody tr:hover {
            background-color: rgba(0, 0, 0, 0.02);
        }
    </style>
</x-app-layout>