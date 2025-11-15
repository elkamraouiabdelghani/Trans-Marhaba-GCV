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
                <form method="GET" action="{{ route('coaching-cabines.planning') }}" class="d-flex gap-2">
                    <select name="year" id="year" class="form-select form-select-sm" onchange="this.form.submit()">
                        @for($y = date('Y') - 2; $y <= date('Y') + 2; $y++)
                            <option value="{{ $y }}" {{ $year == $y ? 'selected' : '' }}>{{ $y }}</option>
                        @endfor
                    </select>
                </form>
                <a href="{{ route('coaching-cabines.planning.pdf', $year) }}" class="btn btn-danger btn-sm" target="_blank">
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
                                                      onclick="showSessions({{ $sessions->where('status', 'planned')->pluck('id')->toJson() }}, 'planned')">
                                                    {{ $planned }}
                                                </span>
                                            @else
                                                <span class="text-muted">-</span>
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
                                        $monthTotal = $monthTotals[$monthNum]['planned'] + $monthTotals[$monthNum]['completed'] + $monthTotals[$monthNum]['cancelled'];
                                        $monthCompleted = $monthTotals[$monthNum]['completed'];
                                        $monthPercentage = $monthTotal > 0 ? round(($monthCompleted / $monthTotal) * 100, 1) : 0;
                                    @endphp
                                    <td colspan="3" class="text-center fw-bold">
                                        <span class="badge bg-warning text-dark">{{ number_format($monthPercentage, 1) }}%</span>
                                        <small class="text-muted d-block">{{ $monthCompleted }} / {{ $monthTotal }}</small>
                                    </td>
                                @endforeach
                                <td class="text-center fw-bold">
                                    <span class="badge bg-warning text-dark">{{ number_format($completedPercentage, 1) }}%</span>
                                    <small class="text-muted d-block">{{ number_format($completedSessions) }} / {{ number_format($totalSessions) }}</small>
                                </td>
                            </tr>
                        </tfoot>
                    </table>
                </div>
            </div>
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
        </div>
    </div>
</x-app-layout>

@push('scripts')
<script>
    function showSessions(sessionIds, status) {
        if (sessionIds.length === 0) return;
        // For now, just redirect to index with filter
        // In future, could show a modal with session details
        window.location.href = '{{ route("coaching-cabines.index") }}?status=' + status;
    }
</script>
@endpush

