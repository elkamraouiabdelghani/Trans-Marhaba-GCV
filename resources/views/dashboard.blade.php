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

            <!-- Violations Overview -->
            <div class="col-xl-3 col-lg-6 col-md-6">
                <div class="card border-0 shadow-sm h-100">
                    <div class="card-body p-4">
                        <div class="d-flex align-items-center row">
                            <div class="col-3 bg-danger bg-opacity-10 rounded-circle d-flex align-items-center justify-content-center me-3" style="width: 50px; height: 50px;">
                                <i class="bi bi-flag text-danger fs-4"></i>
                            </div>
                            <div class="col-9">
                                <h6 class="text-muted mb-1">{{ __('messages.violations_this_week') }}</h6>
                                <h3 class="mb-0 fw-bold text-dark">
                                    {{ number_format($violationsInRange ?? 0) }}
                                </h3>
                                <small class="text-muted d-block">
                                    {{ optional($dashboardRangeStart ?? null)?->format('d M Y') }}
                                    –
                                    {{ optional($dashboardRangeEnd ?? null)?->format('d M Y') }}
                                </small>
                            </div>
                        </div>
                        <div class="mt-3">
                            <a href="{{ route('violations.index', [
                                    'date_from' => optional($dashboardRangeStart ?? null)?->toDateString(),
                                    'date_to' => optional($dashboardRangeEnd ?? null)?->toDateString(),
                                ]) }}"
                               class="btn btn-outline-danger btn-sm">
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
                                @if(!empty($topViolatingDriver) && ($topViolatingDriver->violations_count ?? 0) > 0)
                                    <h5 class="mb-0 fw-bold text-dark">
                                        {{ $topViolatingDriver->full_name ?? ($topViolatingDriver->name ?? __('messages.not_available')) }}
                                    </h5>
                                    <small class="text-muted d-block">
                                        {{ __('messages.total_violations') }}: {{ $topViolatingDriver->violations_count ?? 0 }}
                                    </small>
                                @else
                                    <h3 class="mb-0 fw-bold text-dark">N/A</h3>
                                    <small class="text-muted d-block">{{ __('messages.no_violations_found') }}</small>
                                @endif
                            </div>
                        </div>
                        <div class="mt-3">
                            @if(!empty($topViolatingDriver) && ($topViolatingDriver->violations_count ?? 0) > 0)
                                <a href="{{ route('drivers.show', $topViolatingDriver) }}" class="btn btn-outline-primary btn-sm">
                                    <i class="bi bi-eye me-1"></i>{{ __('messages.view_details') }}
                                </a>
                            @else
                                <button class="btn btn-outline-primary btn-sm" type="button" disabled>
                                    <i class="bi bi-eye me-1"></i>{{ __('messages.view_details') }}
                                </button>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- this month actions planning calendar --}}
        <div class="card border-0 shadow-sm mb-4">
            <div class="card-header bg-white border-0 py-3 d-flex justify-content-between align-items-center flex-wrap gap-2">
                <div>
                    <h5 class="mb-0 text-dark fw-bold">
                        <i class="bi bi-calendar3 me-2 text-primary"></i>
                        {{ __('messages.this_month_actions_calendar') }}
                    </h5>
                    <small class="text-muted">{{ $currentMonthLabel ?? now()->translatedFormat('F Y') }}</small>
                </div>
                <div class="d-flex align-items-center gap-3 flex-wrap">
                    {{-- add a button for download the calendar as a pdf --}}
                    <form method="GET" action="{{ route('dashboard') }}" class="d-flex align-items-center gap-2">
                        <select id="month-filter"
                                name="month"
                                class="form-select form-select-sm"
                                onchange="this.form.submit()"
                                style="min-width: 180px;">
                            @foreach(($monthOptions ?? collect()) as $option)
                                <option value="{{ $option['value'] }}"
                                    {{ ($selectedMonthValue ?? now()->format('Y-m')) === $option['value'] ? 'selected' : '' }}>
                                    {{ $option['label'] }}
                                </option>
                            @endforeach
                        </select>
                    </form>
                    <a href="{{ route('dashboard.calendar.pdf', ['month' => $selectedMonthValue ?? now()->format('Y-m')]) }}"
                       class="btn btn-danger btn-sm">
                        <i class="bi bi-file-earmark-pdf me-1"></i>
                        {{ __('messages.download_calendar_pdf') }}
                    </a>
                    <span class="badge bg-dark bg-opacity-10 text-dark">
                        {{ ($calendarEvents ?? collect())->count() }} {{ __('messages.events') ?? 'events' }}
                    </span>
                </div>
            </div>
            <div class="card-body">
                <div class="calendar-scroll">
                    <div class="alert alert-secondary d-flex flex-wrap align-items-center gap-3 mb-4 calendar-legend" role="alert">
                        <div class="d-flex align-items-center gap-2">
                            <span class="calendar-legend-dot bg-primary"></span>
                            <span>{{ __('messages.calendar_legend_formations') }}</span>
                        </div>
                        <div class="d-flex align-items-center gap-2">
                            <span class="calendar-legend-dot bg-warning"></span>
                            <span>{{ __('messages.calendar_legend_tbt') }}</span>
                        </div>
                        <div class="d-flex align-items-center gap-2">
                            <span class="calendar-legend-dot bg-success"></span>
                            <span>{{ __('messages.calendar_legend_coaching') }}</span>
                        </div>
                    </div>

                    <div class="calendar-container">
                        <div class="calendar-weekdays">
                            @foreach(($weekdayLabels ?? []) as $weekday)
                                <div class="calendar-weekday text-uppercase small fw-semibold text-muted text-center">
                                    {{ $weekday }}
                                </div>
                            @endforeach
                        </div>
                        <div class="calendar-grid">
                            @foreach(($calendarWeeks ?? collect()) as $week)
                                @foreach($week as $day)
                                    @php
                                        $dayEvents = collect($day['events'] ?? []);
                                    @endphp
                                    <div class="calendar-day {{ $day['isCurrentMonth'] ? '' : 'calendar-day--muted' }} {{ $day['isToday'] ? 'calendar-day--today' : '' }}">
                                        <div class="calendar-day-header d-flex justify-content-between align-items-center">
                                            <span class="calendar-day-number">{{ $day['date']->format('j') }}</span>
                                            @if($day['isToday'])
                                                <span class="badge bg-dark bg-opacity-10 text-dark text-uppercase small">{{ __('messages.today') ?? 'Today' }}</span>
                                            @endif
                                        </div>
                                        <div class="calendar-day-events">
                                            @forelse($dayEvents as $event)
                                                <a href="{{ $event['link'] ?? '#' }}"
                                                   class="calendar-event badge bg-{{ $event['color'] }} bg-opacity-10 text-{{ $event['color'] }} w-100 text-start mb-1">
                                                    <span class="d-flex align-items-center gap-1">
                                                        <i class="bi {{ $event['icon'] }}"></i>
                                                        <span class="text-truncate">{{ $event['title'] }}</span>
                                                    </span>
                                                    @if(!empty($event['details']))
                                                        <small class="d-block text-muted text-truncate">{{ $event['details'] }}</small>
                                                    @endif
                                                </a>
                                            @empty
                                                <div class="calendar-event-placeholder text-muted small">
                                                    {{ __('messages.no_events') ?? '—' }}
                                                </div>
                                            @endforelse
                                        </div>
                                    </div>
                                @endforeach
                            @endforeach
                        </div>
                    </div>
                </div>
                @if(($calendarEvents ?? collect())->isEmpty())
                    <div class="text-center py-4">
                        <i class="bi bi-calendar-x display-6 text-muted mb-2"></i>
                        <h6 class="text-dark mb-1">{{ __('messages.no_events_this_month') }}</h6>
                        <p class="text-muted mb-0">{{ __('messages.no_events_this_month_subtitle') }}</p>
                    </div>
                @endif
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

        .calendar-scroll {
            width: 100%;
            overflow-x: auto;
            padding-bottom: 0.5rem;
        }

        .calendar-container {
            width: 100%;
            min-width: 980px;
        }

        .calendar-weekdays {
            display: grid;
            grid-template-columns: repeat(7, 1fr);
            border-bottom: 1px solid #e9ecef;
        }

        .calendar-grid {
            display: grid;
            grid-template-columns: repeat(7, minmax(0, 1fr));
            gap: 0.5rem;
        }

        .calendar-day {
            min-height: 140px;
            border: 1px solid #f1f2f6;
            border-radius: 0.5rem;
            padding: 0.75rem;
            background-color: #ffffff;
            display: flex;
            flex-direction: column;
        }

        .calendar-day--muted {
            background-color: #f8f9fa;
            color: #adb5bd;
        }

        .calendar-day--today {
            border: 1px solid #0d6efd;
            box-shadow: 0 0 0 2px rgba(13, 110, 253, 0.15);
        }

        .calendar-day-number {
            font-weight: 600;
            font-size: 1.15rem;
        }

        .calendar-day-events {
            margin-top: 0.5rem;
            display: flex;
            flex-direction: column;
            gap: 0.25rem;
            overflow: hidden;
        }

        .calendar-event {
            font-size: 0.75rem;
            line-height: 1.2;
            border-radius: 0.35rem;
            white-space: normal;
        }

        .calendar-event-placeholder {
            font-size: 0.75rem;
        }

        .calendar-legend {
            border-radius: 0.75rem;
        }

        .calendar-legend-dot {
            width: 14px;
            height: 14px;
            border-radius: 50%;
            display: inline-block;
        }

        @media (max-width: 991px) {
            .calendar-day {
                min-height: 120px;
            }

            .calendar-container {
                min-width: 900px;
            }
        }
    </style>
</x-app-layout>