<x-app-layout>
    <x-slot name="header">
        @include('layouts.topnav')
    </x-slot>

    <!-- Toast Container - Top Center -->
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
            <div class="col-lg-8">
                <div class="card border-0 shadow-sm">
                    <div class="card-header bg-white border-0 py-3">
                        <h5 class="mb-0 text-dark fw-bold">
                            <i class="bi bi-person-plus me-2 text-primary"></i>
                            {{ __('messages.start_integration') }}
                        </h5>
                    </div>
                    <div class="card-body">
                        <form action="{{ route('drivers.integrations.create') }}" method="POST">
                            @csrf

                            <div class="mb-3">
                                <label for="driver_id" class="form-label">{{ __('messages.select_driver') }} <span class="text-danger">*</span></label>
                                <select class="form-select @error('driver_id') is-invalid @enderror" 
                                        id="driver_id" 
                                        name="driver_id" 
                                        required>
                                    <option value="">{{ __('messages.select_driver') }}</option>
                                    @foreach($drivers as $driver)
                                        <option value="{{ $driver->id }}" {{ old('driver_id') == $driver->id ? 'selected' : '' }}>
                                            {{ $driver->full_name ?? __('messages.not_available') }}
                                        </option>
                                    @endforeach
                                </select>
                                @error('driver_id')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="d-flex gap-2 justify-content-end">
                                <a href="{{ route('drivers.index') }}" class="btn btn-outline-secondary">
                                    <i class="bi bi-x-circle me-1"></i>
                                    {{ __('messages.cancel') }}
                                </a>
                                <button type="submit" class="btn btn-primary">
                                    <i class="bi bi-arrow-right me-1"></i>
                                    {{ __('messages.start') }}
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>

        <!-- Integrated Drivers Table -->
        <div class="card border-0 shadow-sm mt-4">
            <div class="card-header bg-white border-0 py-3">
                <div class="d-flex justify-content-between align-items-center">
                    <h5 class="mb-0 text-dark fw-bold">
                        <i class="bi bi-people me-2 text-primary"></i>
                        {{ __('messages.integrated_drivers') }}
                    </h5>
                    <div class="d-flex gap-2">
                        <div class="input-group input-group-sm" style="width: 250px;">
                            <span class="input-group-text">
                                <i class="bi bi-search"></i>
                            </span>
                            <input type="text" class="form-control" id="integrationSearch" placeholder="{{ __('messages.search_by_name') }}" onkeyup="searchIntegrations()">
                        </div>
                    </div>
                </div>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover mb-0 align-middle" id="integrationsTable">
                        <thead class="table-light">
                            <tr>
                                <th class="border-0 py-3 px-4">{{ __('messages.driver') }}</th>
                                <th class="border-0 py-3 px-4">{{ __('messages.progress') }}</th>
                                <th class="border-0 py-3 px-4">{{ __('messages.current_step') }}</th>
                                <th class="border-0 py-3 px-4">{{ __('messages.status') }}</th>
                                <th class="border-0 py-3 px-4">{{ __('messages.started_at') }}</th>
                                <th class="border-0 py-3 px-4 text-center">{{ __('messages.action') }}</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($integrationsWithProgress ?? [] as $item)
                                @php
                                    $integration = $item['integration'];
                                    $driver = $item['driver'];
                                @endphp
                                <tr class="border-bottom">
                                    <td class="py-3 px-4">
                                        <div class="d-flex align-items-center">
                                            <div class="bg-primary bg-opacity-10 rounded-circle px-2 py-1 me-3">
                                                <i class="bi bi-person text-primary"></i>
                                            </div>
                                            <div class="flex-grow-1">
                                                <strong class="text-dark">
                                                    {{ $driver->full_name ?? __('messages.not_available') }}
                                                </strong>
                                                @if($driver->phone || $driver->phone_number)
                                                    <br>
                                                    <small class="text-muted">
                                                        {{ $driver->phone ?? $driver->phone_number ?? '' }}
                                                    </small>
                                                @endif
                                            </div>
                                        </div>
                                    </td>
                                    <td class="py-3 px-4">
                                        <div class="d-flex align-items-center">
                                            <div class="flex-grow-1 me-2">
                                                <div class="progress" style="height: 20px;">
                                                    <div class="progress-bar bg-{{ $integration->status === 'validated' ? 'success' : ($integration->status === 'rejected' ? 'danger' : 'primary') }}" 
                                                         role="progressbar" 
                                                         style="width: {{ $item['progress_percentage'] }}%" 
                                                         aria-valuenow="{{ $item['progress_percentage'] }}" 
                                                         aria-valuemin="0" 
                                                         aria-valuemax="100">
                                                        <small class="text-white fw-bold">{{ $item['progress_percentage'] }}%</small>
                                                    </div>
                                                </div>
                                            </div>
                                            <small class="text-muted">
                                                {{ $item['completed_steps'] }}/{{ $item['total_steps'] }}
                                            </small>
                                        </div>
                                    </td>
                                    <td class="py-3 px-4">
                                        @if($item['current_step_label'])
                                            <span class="badge bg-info bg-opacity-10 text-info">
                                                {{ $item['current_step_label'] }}
                                            </span>
                                        @else
                                            <span class="text-muted">{{ __('messages.not_started') }}</span>
                                        @endif
                                    </td>
                                    <td class="py-3 px-4">
                                        <span class="badge bg-{{ $integration->status === 'validated' ? 'success' : ($integration->status === 'rejected' ? 'danger' : 'warning') }}">
                                            {{ __('messages.' . $integration->status) }}
                                        </span>
                                    </td>
                                    <td class="py-3 px-4">
                                        <small class="text-muted">
                                            {{ $integration->started_at ? $integration->started_at->format('d/m/Y') : __('messages.not_available') }}
                                        </small>
                                        @if($integration->completed_at)
                                            <br>
                                            <small class="text-success">
                                                {{ __('messages.completed_at') }}: {{ $integration->completed_at->format('d/m/Y') }}
                                            </small>
                                        @endif
                                    </td>
                                    <td class="py-3 px-4 text-center">
                                        <a href="{{ route('drivers.integrations.step', ['integration' => $integration->id, 'step' => $integration->current_step ?? \App\Models\DriverIntegration::STEP_IDENTIFICATION_BESOIN]) }}" 
                                           class="btn btn-primary btn-sm" 
                                           title="{{ __('messages.view_integration') }}">
                                            <i class="bi bi-eye me-1"></i>
                                            {{ __('messages.view_integration') }}
                                        </a>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="6" class="text-center py-5">
                                        <div class="text-muted">
                                            <i class="bi bi-inbox display-1 mb-3"></i>
                                            <h5 class="mb-2">{{ __('messages.no_integrations_found') }}</h5>
                                            <p class="mb-0">{{ __('messages.no_integrations_message') }}</p>
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
        function normalize(str) {
            return (str || '').toString().trim().toLowerCase();
        }

        function searchIntegrations() {
            const input = document.getElementById('integrationSearch');
            const filter = normalize(input.value);
            const table = document.getElementById('integrationsTable');
            const tr = table.getElementsByTagName('tr');
            
            let visibleCount = 0;
            
            // Skip header row (index 0)
            for (let i = 1; i < tr.length; i++) {
                const td = tr[i].getElementsByTagName('td');
                let found = false;
                
                // Search in driver name (first column)
                if (td[0]) {
                    const txtValue = td[0].textContent || td[0].innerText;
                    if (normalize(txtValue).indexOf(filter) > -1) {
                        found = true;
                    }
                }
                
                tr[i].style.display = found ? '' : 'none';
                if (found) visibleCount++;
            }
        }
    </script>
</x-app-layout>

