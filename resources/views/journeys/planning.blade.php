<x-app-layout>
    <x-slot name="header">
        @include('layouts.topnav')
    </x-slot>

    <div class="container-fluid py-4 mt-4">
        <div class="d-flex justify-content-between align-items-center mb-3 bg-white p-4 rounded-3 shadow-sm">
            <div>
                <h2 class="mb-1 fw-bold text-dark fs-4">
                    <i class="bi bi-grid-3x3-gap-fill me-2 text-primary"></i>
                    {{ __('messages.journeys_planning_title') ?? 'Journeys annual inspection plan' }}
                </h2>
            </div>
            <div>
                <a href="{{ route('journeys.index') }}" class="btn btn-sm btn-outline-secondary">
                    <i class="bi bi-arrow-left"></i>
                    {{ __('messages.back_to_list') ?? 'Back to list' }}
                </a>
            </div>
        </div>

        <div class="card border-0 shadow-sm mb-3">
            <div class="card-body p-3 d-flex justify-content-between align-items-center flex-wrap gap-3">
                <div class="d-flex align-items-center gap-3 flex-wrap">
                    <div class="d-flex align-items-center gap-1">
                        <span class="badge rounded-pill" style="background-color:#ffc107;">&nbsp;&nbsp;</span>
                        <small class="text-muted">{{ __('messages.planned_only') ?? 'Planned (no checklist yet)' }}</small>
                    </div>
                    <div class="d-flex align-items-center gap-1">
                        <span class="badge rounded-pill" style="background-color:#0d6efd;">&nbsp;&nbsp;</span>
                        <small class="text-muted">{{ __('messages.realized_only') ?? 'Realized (unplanned)' }}</small>
                    </div>
                    <div class="d-flex align-items-center gap-1">
                        <span class="badge rounded-pill" style="background-color:#198754;">&nbsp;&nbsp;</span>
                        <small class="text-muted">{{ __('messages.planned_and_realized') ?? 'Planned and realized' }}</small>
                    </div>
                </div>

                <div class="d-flex align-items-center gap-2 flex-wrap">
                    <form method="GET" action="{{ route('journeys.planning') }}" class="d-flex align-items-center gap-2">
                        <label for="year" class="form-label mb-0 fw-semibold">
                            {{ __('messages.year') ?? 'Year' }}
                        </label>
                        <select name="year" id="year" class="form-select form-select-sm" onchange="this.form.submit()">
                            @foreach($years as $y)
                                <option value="{{ $y }}" @selected($y == $year)>{{ $y }}</option>
                            @endforeach
                        </select>
                    </form>
    
                    <form method="GET" action="{{ route('journeys.planning.pdf') }}" target="_blank" class="d-flex align-items-center">
                        <input type="hidden" name="year" value="{{ $year }}">
                        <button type="submit" class="btn btn-sm btn-danger">
                            <i class="bi bi-download me-1"></i>
                            {{ __('messages.download_pdf') ?? 'Download PDF' }}
                        </button>
                    </form>
                </div>
            </div>
        </div>

        <div class="card border-0 shadow-sm">
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-bordered table-sm mb-0 align-middle">
                        <thead class="table-light">
                            <tr>
                                <th rowspan="2" style="min-width: 260px; vertical-align: middle;">
                                    {{ __('messages.journeys') ?? 'Journeys' }}
                                </th>
                                @foreach($months as $monthNumber => $label)
                                    <th class="text-center" colspan="3" style="min-width: 120px;">
                                        {{ $label }}
                                    </th>
                                @endforeach
                            </tr>
                            <tr>
                                @foreach($months as $monthNumber => $label)
                                    <th class="text-center small">P</th>
                                    <th class="text-center small">R</th>
                                    <th class="text-center small">NJ</th>
                                @endforeach
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($planningData as $row)
                                @php
                                    /** @var \App\Models\Journey $journey */
                                    $journey = $row['journey'];
                                    $realized = $row['realized'];
                                    $planned = $row['planned'];
                                    $nj = $row['nj'];
                                @endphp
                                <tr>
                                    <td class="fw-semibold">
                                        {{ $journey->name }}
                                    </td>
                                    @foreach($months as $monthNumber => $label)
                                        @php
                                            $plannedCount = $planned[$monthNumber] ?? 0;
                                            $realizedCount = $realized[$monthNumber] ?? 0;
                                            $njCount = $nj[$monthNumber] ?? 0;

                                            // Background colors by type
                                            $pClass = $plannedCount > 0 ? 'bg-warning' : '';
                                            $rClass = $realizedCount > 0 ? 'bg-primary text-white' : '';
                                            $njClass = $njCount > 0 ? 'bg-success text-white' : '';
                                        @endphp
                                        <td class="text-center {{ $pClass }}">
                                            {{ $plannedCount > 0 ? $plannedCount : '' }}
                                        </td>
                                        <td class="text-center {{ $rClass }}">
                                            {{ $realizedCount > 0 ? $realizedCount : '' }}
                                        </td>
                                        <td class="text-center {{ $njClass }}">
                                            {{ $njCount > 0 ? $njCount : '' }}
                                        </td>
                                    @endforeach
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="{{ 1 + (count($months) * 3) }}" class="text-center text-muted py-4">
                                        {{ __('messages.no_journeys_found') ?? 'No journeys found.' }}
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                        @if($totalPlanned > 0 || $totalRealized > 0)
                            <tfoot class="table-light fw-bold">
                                <tr>
                                    <td>{{ __('messages.total') ?? 'Total' }}</td>
                                    @foreach($months as $monthNumber => $label)
                                        @php
                                            $monthPlanned = 0;
                                            $monthRealized = 0;
                                            $monthNj = 0;
                                            foreach ($planningData as $row) {
                                                $monthPlanned += $row['planned'][$monthNumber] ?? 0;
                                                $monthRealized += $row['realized'][$monthNumber] ?? 0;
                                                $monthNj += $row['nj'][$monthNumber] ?? 0;
                                            }
                                        @endphp
                                        <td class="text-center">{{ $monthPlanned > 0 ? $monthPlanned : '' }}</td>
                                        <td class="text-center">{{ $monthRealized > 0 ? $monthRealized : '' }}</td>
                                        <td class="text-center">{{ $monthNj > 0 ? $monthNj : '' }}</td>
                                    @endforeach
                                </tr>
                            </tfoot>
                        @endif
                    </table>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>

