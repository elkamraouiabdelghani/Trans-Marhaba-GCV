<x-app-layout>
    <x-slot name="header">
        @include('layouts.topnav')
    </x-slot>

    <div class="container-fluid py-4 mt-4">
        <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center mb-4 gap-3 bg-white p-3 rounded-3 shadow-sm">
            <div>
                <h1 class="h4 mb-1">{{ __('messages.coaching_cabines_planning_title') }}</h1>
                <p class="text-muted mb-0">{{ __('messages.coaching_cabines_planning_subtitle') }}</p>
            </div>
            <div class="d-flex gap-2">
                <form method="GET" action="{{ route('coaching-cabines.planning') }}" class="d-flex gap-2" id="planningFilterForm">
                    <select name="year" id="year" class="form-select form-select-sm" onchange="this.form.submit()">
                        @for($y = date('Y') - 2; $y <= date('Y') + 2; $y++)
                            <option value="{{ $y }}" {{ $year == $y ? 'selected' : '' }}>{{ $y }}</option>
                        @endfor
                    </select>
                    <select name="flotte_id" id="flotte_id" class="form-select form-select-sm" onchange="this.form.submit()">
                        <option value="">{{ __('messages.all_flottes') ?? 'Toutes les flottes' }}</option>
                        @foreach($flottes as $flotte)
                            <option value="{{ $flotte->id }}" {{ (isset($flotteId) && $flotteId == $flotte->id) ? 'selected' : '' }}>
                                {{ $flotte->name }}
                            </option>
                        @endforeach
                    </select>
                </form>
                <a href="{{ route('coaching-cabines.planning.pdf', $year) }}{{ $flotteId ? '?flotte_id=' . $flotteId : '' }}" class="btn btn-danger btn-sm" target="_blank">
                    <i class="bi bi-file-pdf me-1"></i> {{ __('messages.export_pdf') ?? 'Exporter en PDF' }}
                </a>
                <a href="{{ route('coaching-cabines.index') }}" class="btn btn-outline-secondary btn-sm">
                    <i class="bi bi-arrow-left me-1"></i> {{ __('messages.coaching_cabines_back_to_list') }}
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

        <div class="card border-0 shadow-sm">
            <div class="card-footer bg-white">
                <div class="row">
                    <div class="col-md-6">
                        <small class="text-muted">
                            <span class="badge bg-primary bg-opacity-10 text-primary me-2">P</span> = {{ __('messages.planifie') }} |
                            <span class="badge bg-success bg-opacity-10 text-success me-2">R</span> = {{ __('messages.realise') }} |
                            <span class="badge bg-danger bg-opacity-10 text-danger me-2">NJ</span> = {{ __('messages.non_justifie') }}
                        </small>
                    </div>
                    
                    <div class="col-md-6 text-end">
                        <small class="text-warning">
                            <i class="bi bi-exclamation-triangle me-1"></i>
                            {{ __('messages.driver_missing_sessions') }}
                        </small>
                    </div>
                </div>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive" style="max-height: 70vh; overflow-y: auto;">
                    <table class="table table-bordered table-hover mb-0 align-middle" style="min-width: 1200px;">
                        <thead class="table-light sticky-top">
                            <tr>
                                <th rowspan="2" class="text-center align-middle ps-3" style="min-width: 200px; position: sticky; left: 0; background: white; z-index: 10;">
                                    {{ __('messages.driver') }}
                                </th>
                                @foreach($monthNames as $monthNum => $monthName)
                                    <th colspan="3" class="text-center bg-{{ $monthNum % 2 == 0 ? 'light' : 'white' }}">
                                        {{ $monthName }}
                                    </th>
                                @endforeach
                                <th rowspan="2" class="text-center align-middle bg-info bg-opacity-10" style="min-width: 100px;">
                                    {{ __('messages.total') }}
                                </th>
                            </tr>
                            <tr>
                                @foreach($monthNames as $monthNum => $monthName)
                                    <th class="text-center bg-{{ $monthNum % 2 == 0 ? 'light' : 'white' }}" style="min-width: 60px;">
                                        <span class="badge bg-primary bg-opacity-10 text-primary">P</span>
                                    </th>
                                    <th class="text-center bg-{{ $monthNum % 2 == 0 ? 'light' : 'white' }}" style="min-width: 60px;">
                                        <span class="badge bg-success bg-opacity-10 text-success">R</span>
                                    </th>
                                    <th class="text-center bg-{{ $monthNum % 2 == 0 ? 'light' : 'white' }}" style="min-width: 60px;">
                                        <span class="badge bg-danger bg-opacity-10 text-danger">NJ</span>
                                    </th>
                                @endforeach
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($planningData as $driverData)
                                @php
                                    $driver = $driverData['driver'];
                                    $totalSessions = 0;
                                    foreach($driverData['months'] as $monthData) {
                                        $totalSessions += $monthData['planned'] + $monthData['completed'] + $monthData['cancelled'];
                                    }
                                    $hasLessThanTwo = $totalSessions < 2;
                                @endphp
                                <tr class="{{ $hasLessThanTwo ? 'table-warning' : '' }}">
                                    <td class="fw-semibold ps-3" style="position: sticky; left: 0; background: {{ $hasLessThanTwo ? '#fff3cd' : 'white' }}; z-index: 5;">
                                        <div class="d-flex align-items-center">
                                            {{ $driver->full_name }}
                                            @if($hasLessThanTwo)
                                                <i class="bi bi-exclamation-triangle text-warning ms-2" title="{{ __('messages.driver_missing_sessions') }}"></i>
                                            @endif
                                        </div>
                                    </td>
                                    @foreach($monthNames as $monthNum => $monthName)
                                        @php
                                            $monthData = $driverData['months'][$monthNum];
                                            $planned = $monthData['planned'];
                                            $completed = $monthData['completed'];
                                            $cancelled = $monthData['cancelled'];
                                            $sessions = $monthData['sessions'];
                                        @endphp
                                        <td class="text-center bg-{{ $monthNum % 2 == 0 ? 'light' : 'white' }}">
                                            @if($planned > 0)
                                                <span class="badge bg-primary bg-opacity-10 text-primary" style="cursor: pointer;" 
                                                      onclick="showSessions({{ $sessions->whereIn('status', ['planned', 'in_progress', 'completed'])->pluck('id')->toJson() }}, 'planned')">
                                                    {{ $planned }}
                                                </span>
                                            @else
                                                <span class="text-muted" 
                                                      style="cursor: pointer; padding: 4px 8px; border-radius: 4px; transition: background-color 0.2s;"
                                                      onmouseover="this.style.backgroundColor='#e7f1ff';"
                                                      onmouseout="this.style.backgroundColor='transparent';"
                                                      onclick="openPlanningModal({{ $driver->id }}, {{ $year }}, {{ $monthNum }}, '{{ $driver->full_name }}', '{{ $monthName }}')">
                                                    -
                                                </span>
                                            @endif
                                        </td>
                                        <td class="text-center bg-{{ $monthNum % 2 == 0 ? 'light' : 'white' }}">
                                            @if($completed > 0)
                                                <span class="badge bg-success bg-opacity-10 text-success" style="cursor: pointer;"
                                                      onclick="showSessions({{ $sessions->where('status', 'completed')->pluck('id')->toJson() }}, 'completed')">
                                                    {{ $completed }}
                                                </span>
                                            @else
                                                <span class="text-muted">-</span>
                                            @endif
                                        </td>
                                        <td class="text-center bg-{{ $monthNum % 2 == 0 ? 'light' : 'white' }}">
                                            @if($cancelled > 0)
                                                <span class="badge bg-danger bg-opacity-10 text-danger" style="cursor: pointer;"
                                                      onclick="showSessions({{ $sessions->where('status', 'cancelled')->pluck('id')->toJson() }}, 'cancelled')">
                                                    {{ $cancelled }}
                                                </span>
                                            @else
                                                <span class="text-muted">-</span>
                                            @endif
                                        </td>
                                    @endforeach
                                    <td class="text-center fw-bold bg-info bg-opacity-10">
                                        {{ $totalSessions }}
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                        <tfoot class="table-light">
                            <tr>
                                <td class="fw-bold ps-3" style="position: sticky; left: 0; background: #f8f9fa; z-index: 5;">
                                    {{ __('messages.total') }}
                                </td>
                                @foreach($monthNames as $monthNum => $monthName)
                                    <td class="text-center fw-bold bg-{{ $monthNum % 2 == 0 ? 'light' : 'white' }}">
                                        <span class="badge bg-primary">{{ $monthTotals[$monthNum]['planned'] }}</span>
                                    </td>
                                    <td class="text-center fw-bold bg-{{ $monthNum % 2 == 0 ? 'light' : 'white' }}">
                                        <span class="badge bg-success">{{ $monthTotals[$monthNum]['completed'] }}</span>
                                    </td>
                                    <td class="text-center fw-bold bg-{{ $monthNum % 2 == 0 ? 'light' : 'white' }}">
                                        <span class="badge bg-danger">{{ $monthTotals[$monthNum]['cancelled'] }}</span>
                                    </td>
                                @endforeach
                                <td class="text-center fw-bold bg-info bg-opacity-10">
                                    <span class="badge bg-info">
                                        {{ $grandTotal['planned'] + $grandTotal['completed'] + $grandTotal['cancelled'] }}
                                    </span>
                                </td>
                            </tr>
                            <tr class="bg-info bg-opacity-10">
                                <td class="fw-bold ps-3" style="position: sticky; left: 0; background: #d1ecf1; z-index: 5;">
                                    {{ __('messages.grand_total') ?? 'Total général' }}
                                </td>
                                @foreach($monthNames as $monthNum => $monthName)
                                    <td colspan="3" class="text-center fw-bold">
                                        {{ $monthTotals[$monthNum]['planned'] + $monthTotals[$monthNum]['completed'] + $monthTotals[$monthNum]['cancelled'] }}
                                    </td>
                                @endforeach
                                <td class="text-center fw-bold">
                                    {{ $grandTotal['planned'] + $grandTotal['completed'] + $grandTotal['cancelled'] }}
                                </td>
                            </tr>
                            <tr class="bg-warning bg-opacity-10">
                                <td class="fw-bold ps-3" style="position: sticky; left: 0; background: #fff3cd; z-index: 5;">
                                    <i class="bi bi-percent me-1"></i>{{ __('messages.completed_percentage') ?? 'Completed %' }}
                                </td>
                                @foreach($monthNames as $monthNum => $monthName)
                                    @php
                                        // Use planned count (which includes completed) as the base for percentage
                                        $monthPlanned = $monthTotals[$monthNum]['planned'];
                                        $monthCompleted = $monthTotals[$monthNum]['completed'];
                                        $monthPercentage = $monthPlanned > 0 ? round(($monthCompleted / $monthPlanned) * 100, 1) : 0;
                                    @endphp
                                    <td colspan="3" class="text-center fw-bold">
                                        <span class="badge bg-warning text-dark">{{ number_format($monthPercentage, 1) }}%</span>
                                        <small class="text-muted d-block">{{ $monthCompleted }} / {{ $monthPlanned }}</small>
                                    </td>
                                @endforeach
                                <td class="text-center fw-bold">
                                    <span class="badge bg-warning text-dark">{{ number_format($completedPercentage, 1) }}%</span>
                                    <small class="text-muted d-block">{{ number_format($completedSessions) }} / {{ number_format($grandTotal['planned']) }}</small>
                                </td>
                            </tr>
                        </tfoot>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <!-- Bootstrap JS Bundle (includes Popper.js) -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.6/dist/js/bootstrap.bundle.min.js" 
            crossorigin="anonymous"></script>

    <!-- Planning Confirmation Modal -->
    <div class="modal fade" id="planningModal" tabindex="-1" aria-labelledby="planningModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header bg-primary text-white">
                    <h5 class="modal-title" id="planningModalLabel">
                        <i class="bi bi-calendar-check me-2"></i>
                        {{ __('messages.confirm_planning') ?? 'Confirm Planning' }}
                    </h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <p class="mb-3">
                        {{ __('messages.confirm_planning_message') ?? 'Are you sure you want to plan a coaching session for' }}
                        <strong id="modalDriverName"></strong>
                        {{ __('messages.in_month') ?? 'in' }}
                        <strong id="modalMonthName"></strong>
                        <strong id="modalYear"></strong>?
                    </p>
                    <div class="alert alert-info mb-0">
                        <i class="bi bi-info-circle me-2"></i>
                        <small>{{ __('messages.planning_info') ?? 'A planned coaching session will be created for this driver in the selected month.' }}</small>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">
                        {{ __('messages.cancel') ?? 'Cancel' }}
                    </button>
                    <button type="button" class="btn btn-primary" id="confirmPlanningBtn">
                        <i class="bi bi-check-circle me-2"></i>
                        {{ __('messages.confirm') ?? 'Confirm' }}
                    </button>
                </div>
            </div>
        </div>
    </div>

    <script>
        // Wait for Bootstrap to be loaded
        function waitForBootstrap(callback) {
            if (typeof bootstrap !== 'undefined' && bootstrap.Modal) {
                callback();
            } else {
                // Wait a bit and try again
                setTimeout(function() {
                    if (typeof bootstrap !== 'undefined' && bootstrap.Modal) {
                        callback();
                    } else {
                        console.error('Bootstrap is not loaded. Please ensure Bootstrap JS bundle is included.');
                        alert('{{ __('messages.error') ?? 'An error occurred' }}: Bootstrap is not loaded.');
                    }
                }, 100);
            }
        }

        // Define functions globally so they're accessible from onclick handlers
        let currentPlanningData = null;

        function showSessions(sessionIds, status) {
            if (sessionIds.length === 0) return;
            // For now, just redirect to index with filter
            // In future, could show a modal with session details
            window.location.href = '{{ route("coaching-cabines.index") }}?status=' + status;
        }

        function openPlanningModal(driverId, year, month, driverName, monthName) {
            currentPlanningData = {
                driver_id: driverId,
                year: year,
                month: month
            };

            document.getElementById('modalDriverName').textContent = driverName;
            document.getElementById('modalMonthName').textContent = monthName;
            document.getElementById('modalYear').textContent = year;

            // Wait for Bootstrap to be available before showing modal
            waitForBootstrap(function() {
                const modalEl = document.getElementById('planningModal');
                if (modalEl) {
                    const modal = new bootstrap.Modal(modalEl);
                    modal.show();
                }
            });
        }

        // Initialize modal functionality when DOM is ready
        document.addEventListener('DOMContentLoaded', function() {
            const confirmBtn = document.getElementById('confirmPlanningBtn');
            const modal = document.getElementById('planningModal');

            if (confirmBtn) {
                confirmBtn.addEventListener('click', function() {
                    if (!currentPlanningData) return;

                    // Disable button and show loading state
                    confirmBtn.disabled = true;
                    confirmBtn.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span>{{ __('messages.planning') ?? 'Planning...' }}';

                    // Send AJAX request
                    fetch('{{ route("coaching-cabines.quick-plan") }}', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': '{{ csrf_token() }}',
                            'Accept': 'application/json'
                        },
                        body: JSON.stringify(currentPlanningData)
                    })
                    .then(response => response.json())
                    .then(data => {
                        if (data.success) {
                            // Show success message
                            const alertDiv = document.createElement('div');
                            alertDiv.className = 'alert alert-success alert-dismissible fade show';
                            alertDiv.innerHTML = `
                                <i class="bi bi-check-circle me-2"></i>
                                ${data.message}
                                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                            `;
                            document.querySelector('.container-fluid').insertBefore(alertDiv, document.querySelector('.container-fluid').firstChild);

                            // Close modal
                            if (typeof bootstrap !== 'undefined' && bootstrap.Modal) {
                                const modalInstance = bootstrap.Modal.getInstance(modal);
                                if (modalInstance) {
                                    modalInstance.hide();
                                }
                            }

                            // Reload page after a short delay
                            setTimeout(() => {
                                window.location.reload();
                            }, 1000);
                        } else {
                            // Show error message
                            alert(data.message || '{{ __('messages.error') ?? 'An error occurred' }}');
                            confirmBtn.disabled = false;
                            confirmBtn.innerHTML = '<i class="bi bi-check-circle me-2"></i>{{ __('messages.confirm') ?? 'Confirm' }}';
                        }
                    })
                    .catch(error => {
                        console.error('Error:', error);
                        alert('{{ __('messages.error') ?? 'An error occurred' }}');
                        confirmBtn.disabled = false;
                        confirmBtn.innerHTML = '<i class="bi bi-check-circle me-2"></i>{{ __('messages.confirm') ?? "Confirm" }}';
                    });
                });

                // Reset button state when modal is hidden
                if (modal) {
                    modal.addEventListener('hidden.bs.modal', function() {
                        confirmBtn.disabled = false;
                        confirmBtn.innerHTML = '<i class="bi bi-check-circle me-2"></i>{{ __('messages.confirm') ?? "Confirm" }}';
                        currentPlanningData = null;
                    });
                }
            }
        });
    </script>
</x-app-layout>

