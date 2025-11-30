<x-app-layout>
    <x-slot name="header">
        @include('layouts.topnav')
    </x-slot>

    <div class="container-fluid py-4">
        <div class="d-flex justify-content-between align-items-center mb-4 card-header bg-white border-0 p-3 rounded-3 shadow-sm">
            <h5 class="mb-0 text-dark fw-bold">
                <i class="bi bi-box-arrow-down me-2 text-primary"></i>
                {{ __('messages.export_center_title') }}
            </h5>
        </div>

        @if(session('success'))
            <div class="alert alert-success border-0 shadow-sm">{{ session('success') }}</div>
        @endif

        @if(session('error'))
            <div class="alert alert-danger border-0 shadow-sm">{{ session('error') }}</div>
        @endif

        {{-- Period Filter Section --}}
        <div class="card border-0 shadow-sm mb-4">
            <div class="card-body">
                <form method="GET" action="{{ route('export-center.index') }}" id="periodFilterForm">
                    <div class="row g-3 align-items-end">
                        <div class="col-md-2">
                            <label class="form-label fw-semibold small">{{ __('messages.export_center_period_type') }}</label>
                            <select name="period_type" id="periodType" class="form-select form-select-sm">
                                <option value="month" {{ $periodType === 'month' ? 'selected' : '' }}>{{ __('messages.export_center_month') }}</option>
                                <option value="quarter" {{ $periodType === 'quarter' ? 'selected' : '' }}>{{ __('messages.export_center_quarter') }}</option>
                                <option value="year" {{ $periodType === 'year' ? 'selected' : '' }}>{{ __('messages.export_center_year') }}</option>
                                <option value="custom" {{ $periodType === 'custom' ? 'selected' : '' }}>{{ __('messages.export_center_custom_range') }}</option>
                            </select>
                        </div>

                        {{-- Month Selector --}}
                        <div class="col-md-2" id="monthSelector" style="display: {{ $periodType === 'month' ? 'block' : 'none' }};">
                            <label class="form-label fw-semibold small">{{ __('messages.export_center_select_month') }}</label>
                            <input type="month" name="month" class="form-control form-control-sm" value="{{ $month }}">
                        </div>

                        {{-- Quarter Selector --}}
                        <div class="col-md-2" id="quarterSelector" style="display: {{ $periodType === 'quarter' ? 'block' : 'none' }};">
                            <label class="form-label fw-semibold small">{{ __('messages.export_center_select_quarter') }}</label>
                            <select name="quarter" class="form-select form-select-sm">
                                <option value="Q1" {{ $quarter === 'Q1' ? 'selected' : '' }}>Q1</option>
                                <option value="Q2" {{ $quarter === 'Q2' ? 'selected' : '' }}>Q2</option>
                                <option value="Q3" {{ $quarter === 'Q3' ? 'selected' : '' }}>Q3</option>
                                <option value="Q4" {{ $quarter === 'Q4' ? 'selected' : '' }}>Q4</option>
                            </select>
                        </div>
                        <div class="col-md-2" id="quarterYearSelector" style="display: {{ $periodType === 'quarter' ? 'block' : 'none' }};">
                            <label class="form-label fw-semibold small">{{ __('messages.export_center_select_year') }}</label>
                            <input type="number" name="year" class="form-control form-control-sm" value="{{ $year }}" min="2020" max="2100">
                        </div>

                        {{-- Year Selector --}}
                        <div class="col-md-2" id="yearSelector" style="display: {{ $periodType === 'year' ? 'block' : 'none' }};">
                            <label class="form-label fw-semibold small">{{ __('messages.export_center_select_year') }}</label>
                            <input type="number" name="year" class="form-control form-control-sm" value="{{ $year }}" min="2020" max="2100">
                        </div>

                        {{-- Custom Range Selector --}}
                        <div class="col-md-2" id="customFromSelector" style="display: {{ $periodType === 'custom' ? 'block' : 'none' }};">
                            <label class="form-label fw-semibold small">{{ __('messages.date_from') }}</label>
                            <input type="date" name="date_from" class="form-control form-control-sm" value="{{ $dateFrom }}">
                        </div>
                        <div class="col-md-2" id="customToSelector" style="display: {{ $periodType === 'custom' ? 'block' : 'none' }};">
                            <label class="form-label fw-semibold small">{{ __('messages.date_to') }}</label>
                            <input type="date" name="date_to" class="form-control form-control-sm" value="{{ $dateTo }}">
                        </div>

                        <div class="col-md-2">
                            <button type="submit" class="btn btn-primary btn-sm w-100">
                                <i class="bi bi-search me-1"></i>
                                {{ __('messages.search') }}
                            </button>
                        </div>
                        <div class="col-md-2">
                            <button type="button" id="toggleCharts" class="btn btn-outline-secondary btn-sm w-100">
                                <i class="bi bi-{{ $showCharts ? 'eye-slash' : 'eye' }} me-1"></i>
                                {{ $showCharts ? __('messages.export_center_hide_charts') : __('messages.export_center_show_charts') }}
                            </button>
                            <input type="hidden" name="show_charts" id="showChartsInput" value="{{ $showCharts ? '1' : '0' }}">
                        </div>
                        <div class="col-md-2">
                            <a href="{{ route('export-center.index') }}" class="btn btn-outline-secondary btn-sm w-100">
                                <i class="bi bi-arrow-counterclockwise me-1"></i>
                                {{ __('messages.reset') }}
                            </a>
                        </div>
                    </div>
                </form>
                <div class="mt-2">
                    <small class="text-muted">
                        <strong>{{ __('messages.export_center_period_from') }}</strong>
                        {{ $startDate->format('d/m/Y') }} {{ __('messages.export_center_period_to') }} {{ $endDate->format('d/m/Y') }}
                    </small>
                </div>
            </div>
        </div>

        {{-- Violations Statistics Section --}}
        <div class="card border-0 shadow-sm mb-4" id="violationsSection">
            <div class="card-header bg-white border-0 py-3 d-flex justify-content-between align-items-center">
                <h6 class="mb-0 text-dark fw-bold text-uppercase small">
                    <i class="bi bi-flag me-2 text-danger"></i>
                    {{ __('messages.export_center_violations_statistics') }}
                </h6>
                <div class="d-flex gap-2">
                    <a href="{{ route('export-center.violations.export', request()->query()) }}" class="btn btn-success btn-sm">
                        <i class="bi bi-file-earmark-excel me-1"></i>
                        {{ __('messages.export') }}
                    </a>
                    <button type="button" id="exportViolationsPdfBtn" class="btn btn-danger btn-sm">
                        <i class="bi bi-file-earmark-pdf me-1"></i>
                        PDF
                    </button>
                </div>
            </div>
            <div class="card-body">
                {{-- Summary Cards --}}
                <div class="row g-3 mb-4">
                    <div class="col-xl-3 col-md-6">
                        <div class="card border-0 shadow-sm h-100">
                            <div class="card-body p-4">
                                <div class="d-flex align-items-center">
                                    <div class="bg-primary bg-opacity-10 rounded-circle d-flex align-items-center justify-content-center me-3" style="width: 60px; height: 60px;">
                                        <i class="bi bi-exclamation-triangle text-primary fs-4"></i>
                                    </div>
                                    <div class="flex-grow-1">
                                        <h6 class="text-muted mb-1">{{ __('messages.total') }}</h6>
                                        <h3 class="mb-0 fw-bold text-dark">{{ $violationsStats['total'] ?? 0 }}</h3>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-xl-3 col-md-6">
                        <div class="card border-0 shadow-sm h-100">
                            <div class="card-body p-4">
                                <div class="d-flex align-items-center">
                                    <div class="bg-success bg-opacity-10 rounded-circle d-flex align-items-center justify-content-center me-3" style="width: 60px; height: 60px;">
                                        <i class="bi bi-check-circle text-success fs-4"></i>
                                    </div>
                                    <div class="flex-grow-1">
                                        <h6 class="text-muted mb-1">{{ __('messages.confirmed') }}</h6>
                                        <h3 class="mb-0 fw-bold text-dark">{{ $violationsStats['confirmed'] ?? 0 }}</h3>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-xl-3 col-md-6">
                        <div class="card border-0 shadow-sm h-100">
                            <div class="card-body p-4">
                                <div class="d-flex align-items-center">
                                    <div class="bg-danger bg-opacity-10 rounded-circle d-flex align-items-center justify-content-center me-3" style="width: 60px; height: 60px;">
                                        <i class="bi bi-x-octagon text-danger fs-4"></i>
                                    </div>
                                    <div class="flex-grow-1">
                                        <h6 class="text-muted mb-1">{{ __('messages.rejected') }}</h6>
                                        <h3 class="mb-0 fw-bold text-dark">{{ $violationsStats['rejected'] ?? 0 }}</h3>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-xl-3 col-md-6">
                        <div class="card border-0 shadow-sm h-100">
                            <div class="card-body p-4">
                                <div class="d-flex align-items-center">
                                    <div class="bg-warning bg-opacity-10 rounded-circle d-flex align-items-center justify-content-center me-3" style="width: 60px; height: 60px;">
                                        <i class="bi bi-hourglass-split text-warning fs-4"></i>
                                    </div>
                                    <div class="flex-grow-1">
                                        <h6 class="text-muted mb-1">{{ __('messages.pending') }}</h6>
                                        <h3 class="mb-0 fw-bold text-dark">{{ $violationsStats['pending'] ?? 0 }}</h3>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Top 5 Drivers Table --}}
                <div class="mb-4">
                    <h6 class="text-dark fw-bold mb-3">{{ __('messages.export_center_top_drivers') }}</h6>
                    <div class="table-responsive">
                        <table class="table table-bordered align-middle">
                            <thead class="table-light">
                                <tr>
                                    <th>{{ __('messages.driver') }}</th>
                                    <th class="text-center">{{ __('messages.total') }}</th>
                                    <th class="text-center">{{ __('messages.confirmed') }}</th>
                                    <th class="text-center">{{ __('messages.rejected') }}</th>
                                    <th class="text-center">{{ __('messages.pending') }}</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($violationsStats['top_drivers'] ?? [] as $driver)
                                    <tr>
                                        <td class="fw-semibold">{{ $driver['driver_name'] }}</td>
                                        <td class="text-center">{{ $driver['total_count'] }}</td>
                                        <td class="text-center">
                                            <span class="badge bg-success">{{ $driver['confirmed_count'] }}</span>
                                        </td>
                                        <td class="text-center">
                                            <span class="badge bg-danger">{{ $driver['rejected_count'] }}</span>
                                        </td>
                                        <td class="text-center">
                                            <span class="badge bg-warning">{{ $driver['pending_count'] }}</span>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="5" class="text-center text-muted py-4">
                                            {{ __('messages.export_center_no_data') }}
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>

                {{-- Violations by Type Table --}}
                @if(isset($violationsStats['by_type']) && count($violationsStats['by_type']) > 0)
                    <div class="mb-4">
                        <h6 class="text-dark fw-bold mb-3">{{ __('messages.export_center_violations_by_type') }}</h6>
                        <div class="table-responsive">
                            <table class="table table-bordered align-middle">
                                <thead class="table-light">
                                    <tr>
                                        <th>{{ __('messages.violation_type') }}</th>
                                        <th class="text-center">{{ __('messages.total') }}</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($violationsStats['by_type'] as $type)
                                        <tr>
                                            <td class="fw-semibold">{{ $type['type_name'] }}</td>
                                            <td class="text-center">{{ $type['count'] }}</td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>
                @endif

                {{-- Charts Section (Toggleable) --}}
                @if($showCharts)
                    <div class="row g-3 mb-4" id="violationsCharts">
                        <div class="col-md-6">
                            <div class="card border-0 shadow-sm">
                                <div class="card-body">
                                    <h6 class="text-dark fw-bold mb-3">{{ __('messages.export_center_violations_statistics') }} - {{ __('messages.status') }}</h6>
                                    <canvas id="violationsStatusChart" height="200"></canvas>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="card border-0 shadow-sm">
                                <div class="card-body">
                                    <h6 class="text-dark fw-bold mb-3">{{ __('messages.export_center_top_drivers') }}</h6>
                                    <canvas id="topDriversViolationsChart" height="200"></canvas>
                                </div>
                            </div>
                        </div>
                        @if(isset($violationsStats['by_type']) && count($violationsStats['by_type']) > 0)
                            <div class="col-md-6">
                                <div class="card border-0 shadow-sm">
                                    <div class="card-body">
                                        <h6 class="text-dark fw-bold mb-3">{{ __('messages.export_center_violations_by_type') }}</h6>
                                        <canvas id="violationsByTypeChart" height="200"></canvas>
                                    </div>
                                </div>
                            </div>
                        @endif
                    </div>
                @endif
            </div>
        </div>

        {{-- Driving Times Statistics Section --}}
        <div class="card border-0 shadow-sm mb-4" id="drivingTimesSection">
            <div class="card-header bg-white border-0 py-3 d-flex justify-content-between align-items-center">
                <h6 class="mb-0 text-dark fw-bold text-uppercase small">
                    <i class="bi bi-clock-history me-2 text-primary"></i>
                    {{ __('messages.export_center_driving_times_statistics') }}
                </h6>
                <div class="d-flex gap-2">
                    <a href="{{ route('export-center.driving-times.export', request()->query()) }}" class="btn btn-success btn-sm">
                        <i class="bi bi-file-earmark-excel me-1"></i>
                        {{ __('messages.export') }}
                    </a>
                    <button type="button" id="exportDrivingTimesPdfBtn" class="btn btn-danger btn-sm">
                        <i class="bi bi-file-earmark-pdf me-1"></i>
                        PDF
                    </button>
                </div>
            </div>
            <div class="card-body">
                {{-- Summary Cards --}}
                <div class="row g-3 mb-4">
                    <div class="col-xl-4 col-md-6">
                        <div class="card border-0 shadow-sm h-100">
                            <div class="card-body p-4">
                                <div class="d-flex align-items-center">
                                    <div class="bg-primary bg-opacity-10 rounded-circle d-flex align-items-center justify-content-center me-3" style="width: 60px; height: 60px;">
                                        <i class="bi bi-speedometer2 text-primary fs-4"></i>
                                    </div>
                                    <div class="flex-grow-1">
                                        <h6 class="text-muted mb-1">{{ __('messages.export_center_total_driving_hours') }}</h6>
                                        <h3 class="mb-0 fw-bold text-dark">{{ number_format($drivingTimesStats['total_hours'] ?? 0, 2) }}h</h3>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-xl-4 col-md-6">
                        <div class="card border-0 shadow-sm h-100">
                            <div class="card-body p-4">
                                <div class="d-flex align-items-center">
                                    <div class="bg-info bg-opacity-10 rounded-circle d-flex align-items-center justify-content-center me-3" style="width: 60px; height: 60px;">
                                        <i class="bi bi-graph-up text-info fs-4"></i>
                                    </div>
                                    <div class="flex-grow-1">
                                        <h6 class="text-muted mb-1">{{ __('messages.export_center_average_per_driver') }}</h6>
                                        <h3 class="mb-0 fw-bold text-dark">{{ number_format($drivingTimesStats['average_per_driver'] ?? 0, 2) }}h</h3>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-xl-4 col-md-6">
                        <div class="card border-0 shadow-sm h-100">
                            <div class="card-body p-4">
                                <div class="d-flex align-items-center">
                                    <div class="bg-success bg-opacity-10 rounded-circle d-flex align-items-center justify-content-center me-3" style="width: 60px; height: 60px;">
                                        <i class="bi bi-people text-success fs-4"></i>
                                    </div>
                                    <div class="flex-grow-1">
                                        <h6 class="text-muted mb-1">{{ __('messages.export_center_unique_drivers') }}</h6>
                                        <h3 class="mb-0 fw-bold text-dark">{{ $drivingTimesStats['unique_drivers'] ?? 0 }}</h3>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Top 5 Drivers Table --}}
                <div class="mb-4">
                    <h6 class="text-dark fw-bold mb-3">{{ __('messages.export_center_top_drivers') }}</h6>
                    <div class="table-responsive">
                        <table class="table table-bordered align-middle">
                            <thead class="table-light">
                                <tr>
                                    <th>{{ __('messages.driver') }}</th>
                                    <th class="text-center">{{ __('messages.export_center_total_hours') }}</th>
                                    <th class="text-center">{{ __('messages.export_center_activity_count') }}</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($drivingTimesStats['top_drivers'] ?? [] as $driver)
                                    <tr>
                                        <td class="fw-semibold">{{ $driver['driver_name'] }}</td>
                                        <td class="text-center">{{ number_format($driver['total_hours'], 2) }}h</td>
                                        <td class="text-center">{{ $driver['activity_count'] }}</td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="3" class="text-center text-muted py-4">
                                            {{ __('messages.export_center_no_data') }}
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>

                {{-- Charts Section (Toggleable) --}}
                @if($showCharts)
                    <div class="row g-3 mb-4" id="drivingTimesCharts">
                        <div class="col-md-6">
                            <div class="card border-0 shadow-sm">
                                <div class="card-body">
                                    <h6 class="text-dark fw-bold mb-3">{{ __('messages.export_center_top_drivers') }}</h6>
                                    <canvas id="topDriversDrivingHoursChart" height="200"></canvas>
                                </div>
                            </div>
                        </div>
                    </div>
                @endif
            </div>
        </div>
    </div>

    @push('scripts')
    <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/html2canvas/1.4.1/html2canvas.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf/2.5.1/jspdf.umd.min.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Period type selector logic
            const periodTypeSelect = document.getElementById('periodType');
            const monthSelector = document.getElementById('monthSelector');
            const quarterSelector = document.getElementById('quarterSelector');
            const quarterYearSelector = document.getElementById('quarterYearSelector');
            const yearSelector = document.getElementById('yearSelector');
            const customFromSelector = document.getElementById('customFromSelector');
            const customToSelector = document.getElementById('customToSelector');

            function togglePeriodSelectors() {
                const periodType = periodTypeSelect.value;
                
                // Hide all
                monthSelector.style.display = 'none';
                quarterSelector.style.display = 'none';
                quarterYearSelector.style.display = 'none';
                yearSelector.style.display = 'none';
                customFromSelector.style.display = 'none';
                customToSelector.style.display = 'none';

                // Show relevant ones
                if (periodType === 'month') {
                    monthSelector.style.display = 'block';
                } else if (periodType === 'quarter') {
                    quarterSelector.style.display = 'block';
                    quarterYearSelector.style.display = 'block';
                } else if (periodType === 'year') {
                    yearSelector.style.display = 'block';
                } else if (periodType === 'custom') {
                    customFromSelector.style.display = 'block';
                    customToSelector.style.display = 'block';
                }
            }

            periodTypeSelect.addEventListener('change', togglePeriodSelectors);

            // Charts toggle
            const toggleChartsBtn = document.getElementById('toggleCharts');
            const showChartsInput = document.getElementById('showChartsInput');
            const violationsCharts = document.getElementById('violationsCharts');
            const drivingTimesCharts = document.getElementById('drivingTimesCharts');

            toggleChartsBtn.addEventListener('click', function() {
                const currentValue = showChartsInput.value === '1';
                showChartsInput.value = currentValue ? '0' : '1';
                document.getElementById('periodFilterForm').submit();
            });

            // Initialize charts if showCharts is true
            @if($showCharts)
                // Violations by Status Chart
                const violationsStatusCtx = document.getElementById('violationsStatusChart');
                if (violationsStatusCtx) {
                    new Chart(violationsStatusCtx, {
                        type: 'bar',
                        data: {
                            labels: ['{{ __('messages.confirmed') }}', '{{ __('messages.rejected') }}', '{{ __('messages.pending') }}'],
                            datasets: [{
                                label: '{{ __('messages.total') }}',
                                data: [
                                    {{ $violationsStats['confirmed'] ?? 0 }},
                                    {{ $violationsStats['rejected'] ?? 0 }},
                                    {{ $violationsStats['pending'] ?? 0 }}
                                ],
                                backgroundColor: [
                                    'rgba(40, 167, 69, 0.8)',
                                    'rgba(220, 53, 69, 0.8)',
                                    'rgba(255, 193, 7, 0.8)'
                                ],
                                borderColor: [
                                    'rgba(40, 167, 69, 1)',
                                    'rgba(220, 53, 69, 1)',
                                    'rgba(255, 193, 7, 1)'
                                ],
                                borderWidth: 1
                            }]
                        },
                        options: {
                            responsive: true,
                            maintainAspectRatio: true,
                            scales: {
                                y: {
                                    beginAtZero: true,
                                    ticks: {
                                        stepSize: 1
                                    }
                                }
                            }
                        }
                    });
                }

                // Top Drivers Violations Chart
                const topDriversViolationsCtx = document.getElementById('topDriversViolationsChart');
                if (topDriversViolationsCtx) {
                    const topDrivers = @json($violationsStats['top_drivers'] ?? []);
                    new Chart(topDriversViolationsCtx, {
                        type: 'bar',
                        data: {
                            labels: topDrivers.map(d => d.driver_name),
                            datasets: [{
                                label: '{{ __('messages.total') }}',
                                data: topDrivers.map(d => d.total_count),
                                backgroundColor: 'rgba(54, 162, 235, 0.8)',
                                borderColor: 'rgba(54, 162, 235, 1)',
                                borderWidth: 1
                            }]
                        },
                        options: {
                            responsive: true,
                            maintainAspectRatio: true,
                            scales: {
                                y: {
                                    beginAtZero: true,
                                    ticks: {
                                        stepSize: 1
                                    }
                                }
                            }
                        }
                    });
                }

                // Violations by Type Chart
                const violationsByTypeCtx = document.getElementById('violationsByTypeChart');
                if (violationsByTypeCtx) {
                    const byType = @json($violationsStats['by_type'] ?? []);
                    new Chart(violationsByTypeCtx, {
                        type: 'pie',
                        data: {
                            labels: byType.map(t => t.type_name),
                            datasets: [{
                                data: byType.map(t => t.count),
                                backgroundColor: [
                                    'rgba(255, 99, 132, 0.8)',
                                    'rgba(54, 162, 235, 0.8)',
                                    'rgba(255, 206, 86, 0.8)',
                                    'rgba(75, 192, 192, 0.8)',
                                    'rgba(153, 102, 255, 0.8)',
                                    'rgba(255, 159, 64, 0.8)'
                                ],
                                borderWidth: 1
                            }]
                        },
                        options: {
                            responsive: true,
                            maintainAspectRatio: true
                        }
                    });
                }

                // Top Drivers Driving Hours Chart
                const topDriversDrivingHoursCtx = document.getElementById('topDriversDrivingHoursChart');
                if (topDriversDrivingHoursCtx) {
                    const topDrivers = @json($drivingTimesStats['top_drivers'] ?? []);
                    new Chart(topDriversDrivingHoursCtx, {
                        type: 'bar',
                        data: {
                            labels: topDrivers.map(d => d.driver_name),
                            datasets: [{
                                label: '{{ __('messages.export_center_total_hours') }}',
                                data: topDrivers.map(d => d.total_hours),
                                backgroundColor: 'rgba(40, 167, 69, 0.8)',
                                borderColor: 'rgba(40, 167, 69, 1)',
                                borderWidth: 1
                            }]
                        },
                        options: {
                            responsive: true,
                            maintainAspectRatio: true,
                            scales: {
                                y: {
                                    beginAtZero: true
                                }
                            }
                        }
                    });
                }
            @endif

            // PDF Export with Charts
            const exportViolationsPdfBtn = document.getElementById('exportViolationsPdfBtn');
            const exportDrivingTimesPdfBtn = document.getElementById('exportDrivingTimesPdfBtn');

            // Export Violations PDF
            if (exportViolationsPdfBtn) {
                exportViolationsPdfBtn.addEventListener('click', function() {
                    const btn = this;
                    const originalText = btn.innerHTML;
                    btn.disabled = true;
                    btn.innerHTML = '<i class="bi bi-hourglass-split me-1"></i>{{ __('messages.generating_pdf') }}...';

                    const violationsSection = document.getElementById('violationsSection');
                    const includeCharts = {{ $showCharts ? 'true' : 'false' }};

                    if (includeCharts && violationsSection) {
                        // Export with charts using html2canvas
                        html2canvas(violationsSection, {
                            backgroundColor: '#ffffff',
                            scale: 2,
                            logging: false,
                            useCORS: true,
                            allowTaint: false
                        }).then(function(canvas) {
                            const imgData = canvas.toDataURL('image/png');
                            const { jsPDF } = window.jspdf;
                            const pdf = new jsPDF('portrait', 'mm', 'a4');

                            const pdfWidth = pdf.internal.pageSize.getWidth();
                            const pdfHeight = pdf.internal.pageSize.getHeight();
                            const imgWidth = pdfWidth - 20;
                            const imgHeight = (canvas.height * imgWidth) / canvas.width;

                            // Add title
                            pdf.setFontSize(16);
                            pdf.text('{{ __('messages.export_center_violations_statistics') }}', pdfWidth / 2, 15, { align: 'center' });

                            // Add period info
                            pdf.setFontSize(10);
                            const periodText = '{{ $startDate->format('d/m/Y') }} - {{ $endDate->format('d/m/Y') }}';
                            pdf.text(periodText, pdfWidth / 2, 22, { align: 'center' });

                            // Add image
                            pdf.addImage(imgData, 'PNG', 10, 28, imgWidth, imgHeight);

                            // Save PDF
                            const fileName = 'violations-stats-{{ $periodType }}-{{ now()->format('Ymd_His') }}.pdf';
                            pdf.save(fileName);

                            btn.disabled = false;
                            btn.innerHTML = originalText;
                        }).catch(function(error) {
                            console.error('Error generating PDF:', error);
                            // Fallback to server-side PDF
                            window.location.href = '{{ route('export-center.violations.export-pdf', array_merge(request()->query(), ['include_charts' => $showCharts ? '1' : '0'])) }}';
                            btn.disabled = false;
                            btn.innerHTML = originalText;
                        });
                    } else {
                        // Export without charts (server-side)
                        window.location.href = '{{ route('export-center.violations.export-pdf', array_merge(request()->query(), ['include_charts' => '0'])) }}';
                        btn.disabled = false;
                        btn.innerHTML = originalText;
                    }
                });
            }

            // Export Driving Times PDF
            if (exportDrivingTimesPdfBtn) {
                exportDrivingTimesPdfBtn.addEventListener('click', function() {
                    const btn = this;
                    const originalText = btn.innerHTML;
                    btn.disabled = true;
                    btn.innerHTML = '<i class="bi bi-hourglass-split me-1"></i>{{ __('messages.generating_pdf') }}...';

                    const drivingTimesSection = document.getElementById('drivingTimesSection');
                    const includeCharts = {{ $showCharts ? 'true' : 'false' }};

                    if (includeCharts && drivingTimesSection) {
                        // Export with charts using html2canvas
                        html2canvas(drivingTimesSection, {
                            backgroundColor: '#ffffff',
                            scale: 2,
                            logging: false,
                            useCORS: true,
                            allowTaint: false
                        }).then(function(canvas) {
                            const imgData = canvas.toDataURL('image/png');
                            const { jsPDF } = window.jspdf;
                            const pdf = new jsPDF('portrait', 'mm', 'a4');

                            const pdfWidth = pdf.internal.pageSize.getWidth();
                            const pdfHeight = pdf.internal.pageSize.getHeight();
                            const imgWidth = pdfWidth - 20;
                            const imgHeight = (canvas.height * imgWidth) / canvas.width;

                            // Add title
                            pdf.setFontSize(16);
                            pdf.text('{{ __('messages.export_center_driving_times_statistics') }}', pdfWidth / 2, 15, { align: 'center' });

                            // Add period info
                            pdf.setFontSize(10);
                            const periodText = '{{ $startDate->format('d/m/Y') }} - {{ $endDate->format('d/m/Y') }}';
                            pdf.text(periodText, pdfWidth / 2, 22, { align: 'center' });

                            // Add image
                            pdf.addImage(imgData, 'PNG', 10, 28, imgWidth, imgHeight);

                            // Save PDF
                            const fileName = 'driving-times-stats-{{ $periodType }}-{{ now()->format('Ymd_His') }}.pdf';
                            pdf.save(fileName);

                            btn.disabled = false;
                            btn.innerHTML = originalText;
                        }).catch(function(error) {
                            console.error('Error generating PDF:', error);
                            // Fallback to server-side PDF
                            window.location.href = '{{ route('export-center.driving-times.export-pdf', array_merge(request()->query(), ['include_charts' => $showCharts ? '1' : '0'])) }}';
                            btn.disabled = false;
                            btn.innerHTML = originalText;
                        });
                    } else {
                        // Export without charts (server-side)
                        window.location.href = '{{ route('export-center.driving-times.export-pdf', array_merge(request()->query(), ['include_charts' => '0'])) }}';
                        btn.disabled = false;
                        btn.innerHTML = originalText;
                    }
                });
            }
        });
    </script>
    @endpush
</x-app-layout>

