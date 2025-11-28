<?php

namespace App\Http\Controllers;

use App\Models\Driver;
use App\Models\Formation;
use App\Models\TbtFormation;
use App\Models\CoachingSession;
use App\Models\DriverViolation;
use Barryvdh\DomPDF\Facade\Pdf;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;

class DashboardController extends Controller
{
    public function index(Request $request)
    {
        $data = $this->buildCalendarData($request);
        return view('dashboard', $data);
    }

    public function calendarPdf(Request $request)
    {
        $data = $this->buildCalendarData($request);
        $pdf = Pdf::loadView('dashboard.calendar_pdf', $data)->setPaper('a4', 'landscape');
        $fileName = 'calendar-' . ($data['selectedMonthValue'] ?? now()->format('Y-m')) . '.pdf';
        return $pdf->download($fileName);
    }

    protected function buildCalendarData(Request $request): array
    {
        $totalDrivers = Driver::query()->count();

        $now = Carbon::now();
        $selectedMonthInput = $request->input('month');
        $currentYear = $now->year;

        try {
            $selectedDate = $selectedMonthInput
                ? Carbon::createFromFormat('Y-m', $selectedMonthInput)->startOfMonth()
                : $now->copy()->startOfMonth();
        } catch (\Exception) {
            $selectedDate = $now->copy()->startOfMonth();
        }

        if ((int) $selectedDate->format('Y') !== $currentYear) {
            $selectedDate->setDate($currentYear, (int) $selectedDate->format('m'), 1);
        }

        $currentMonth = (int) $selectedDate->format('m');
        $currentMonthLabel = $selectedDate->translatedFormat('F Y');
        $selectedMonthValue = $selectedDate->format('Y-m');

        $allowedEventTypes = ['formation', 'tbt', 'coaching'];
        $selectedEventType = $request->filled('event_type') && in_array($request->input('event_type'), $allowedEventTypes, true)
            ? $request->input('event_type')
            : null;

        $monthOptions = collect(range(1, 12))->map(function (int $month) use ($currentYear) {
            $date = Carbon::create($currentYear, $month, 1);
            return [
                'value' => $date->format('Y-m'),
                'label' => $date->translatedFormat('F Y'),
            ];
        });

        $formationsThisMonth = Formation::query()
            ->whereNotNull('realizing_date')
            ->whereYear('realizing_date', $currentYear)
            ->whereMonth('realizing_date', $currentMonth)
            ->get();

        $tbtFormationsThisMonth = TbtFormation::query()
            ->where('year', $currentYear)
            ->where('month', $currentMonth)
            ->get();

        $coachingSessionsThisMonth = CoachingSession::with(['driver', 'flotte'])
            ->whereYear('date', $currentYear)
            ->whereMonth('date', $currentMonth)
            ->whereIn('status', ['planned', 'in_progress', 'completed'])
            ->get();

        $rangeStartInput = $request->input('from');
        $rangeEndInput = $request->input('to');
        $defaultRangeStart = Carbon::now()->startOfWeek(Carbon::MONDAY);
        $defaultRangeEnd = Carbon::now()->endOfWeek(Carbon::SUNDAY);

        $rangeStart = $defaultRangeStart->copy();
        $rangeEnd = $defaultRangeEnd->copy();

        if ($rangeStartInput) {
            try {
                $rangeStart = Carbon::createFromFormat('Y-m-d', $rangeStartInput)->startOfDay();
            } catch (\Exception $e) {
                // keep default
            }
        }

        if ($rangeEndInput) {
            try {
                $rangeEnd = Carbon::createFromFormat('Y-m-d', $rangeEndInput)->endOfDay();
            } catch (\Exception $e) {
                // keep default
            }
        }

        if ($rangeStart->gt($rangeEnd)) {
            [$rangeStart, $rangeEnd] = [$rangeEnd->copy()->startOfDay(), $rangeStart->copy()->endOfDay()];
        }

        $violationsInRange = DriverViolation::query()
            ->whereBetween('violation_date', [$rangeStart->toDateString(), $rangeEnd->toDateString()])
            ->count();

        $topViolatingDriver = Driver::query()
            ->withCount('violations')
            ->orderByDesc('violations_count')
            ->first();

        $calendarEvents = collect()
            ->merge(
                $formationsThisMonth->map(function (Formation $formation) {
                    return [
                        'type' => 'formation',
                        'date' => $formation->realizing_date,
                        'title' => $formation->theme,
                        'label' => __('messages.calendar_event_type_formation'),
                        'details' => $formation->theme ?? $formation->type_label,
                        'meta' => __('messages.status') . ': ' . ($formation->status === 'planned'
                            ? __('messages.status_planned')
                            : __('messages.status_realized')),
                        'color' => 'primary',
                        'icon' => 'bi-journal-text',
                        'link' => route('formations.show', $formation),
                    ];
                })
            )
            ->merge(
                $tbtFormationsThisMonth->map(function (TbtFormation $tbtFormation) {
                    $dateRange = null;
                    if ($tbtFormation->week_start_date && $tbtFormation->week_end_date) {
                        $dateRange = sprintf(
                            '%s → %s',
                            $tbtFormation->week_start_date->format('d M'),
                            $tbtFormation->week_end_date->format('d M')
                        );
                    }

                    $statusLabel = $tbtFormation->status === 'realized'
                        ? __('messages.tbt_formation_status_realized')
                        : __('messages.tbt_formation_status_planned');

                    $metaParts = [
                        __('messages.status') . ': ' . $statusLabel,
                        $dateRange,
                    ];

                    return [
                        'type' => 'tbt',
                        'date' => $tbtFormation->week_start_date ?? $tbtFormation->week_end_date,
                        'title' => $tbtFormation->title,
                        'label' => __('messages.calendar_event_type_tbt'),
                        'details' => $tbtFormation->participant ? __('messages.tbt_formation_participant') . ': ' . $tbtFormation->participant : null,
                        'meta' => implode(' • ', array_filter($metaParts)),
                        'color' => 'warning',
                        'icon' => 'bi-calendar-week',
                        'link' => route('tbt-formations.edit', $tbtFormation),
                    ];
                })
            )
            ->merge(
                $coachingSessionsThisMonth->map(function (CoachingSession $session) {
                    $statusLabel = match ($session->status) {
                        'in_progress' => __('messages.status_in_progress'),
                        'completed' => __('messages.status_realized') ?? __('messages.completed'),
                        default => __('messages.status_planned'),
                    };

                    $details = optional($session->driver)->full_name;
                    if ($session->flotte) {
                        $details = trim(($details ? $details . ' • ' : '') . $session->flotte->name);
                    }

                    $meta = __('messages.status') . ': ' . $statusLabel;
                    if ($session->type) {
                        $meta .= ' • ' . ucfirst(str_replace('_', ' ', $session->type));
                    }

                    return [
                        'type' => 'coaching',
                        'date' => $session->date,
                        'title' => __('messages.calendar_event_type_coaching'),
                        'label' => __('messages.calendar_event_type_coaching'),
                        'details' => $details,
                        'meta' => $meta,
                        'color' => 'success',
                        'icon' => 'bi-person-video3',
                        'link' => route('coaching-cabines.show', $session),
                    ];
                })
            )
            ->filter(fn ($event) => !empty($event['date']))
            ->when($selectedEventType, fn ($events) => $events->where('type', $selectedEventType))
            ->sortBy('date')
            ->values();

        $eventsByDate = $calendarEvents->groupBy(fn ($event) => $event['date']->toDateString());

        $startOfMonth = $selectedDate->copy()->startOfMonth();
        $endOfMonth = $selectedDate->copy()->endOfMonth();
        $calendarStart = $startOfMonth->copy()->startOfWeek(Carbon::MONDAY);
        $calendarEnd = $endOfMonth->copy()->endOfWeek(Carbon::SUNDAY);

        $calendarDays = [];
        $cursor = $calendarStart->copy();
        $today = Carbon::today();

        while ($cursor <= $calendarEnd) {
            $dateString = $cursor->toDateString();
            $calendarDays[] = [
                'date' => $cursor->copy(),
                'isCurrentMonth' => $cursor->month === $currentMonth,
                'isToday' => $cursor->isSameDay($today),
                'events' => $eventsByDate->get($dateString, collect()),
            ];
            $cursor->addDay();
        }

        $calendarWeeks = collect($calendarDays)->chunk(7);

        $weekdayLabels = [
            __('messages.calendar_weekday_mon'),
            __('messages.calendar_weekday_tue'),
            __('messages.calendar_weekday_wed'),
            __('messages.calendar_weekday_thu'),
            __('messages.calendar_weekday_fri'),
            __('messages.calendar_weekday_sat'),
            __('messages.calendar_weekday_sun'),
        ];

        $driversExceedingLegalHours = $this->getDriversExceedingLegalHours();

        return [
            'totalDrivers' => $totalDrivers,
            'calendarEvents' => $calendarEvents,
            'currentMonthLabel' => $currentMonthLabel,
            'selectedMonthValue' => $selectedMonthValue,
            'selectedEventType' => $selectedEventType,
            'monthOptions' => $monthOptions,
            'calendarWeeks' => $calendarWeeks,
            'weekdayLabels' => $weekdayLabels,
            'dashboardRangeStart' => $rangeStart,
            'dashboardRangeEnd' => $rangeEnd,
            'violationsInRange' => $violationsInRange,
            'topViolatingDriver' => $topViolatingDriver,
            'driversExceedingLegalHours' => $driversExceedingLegalHours,
        ];
    }

    /**
     * Get top 3 drivers exceeding legal weekly driving hours.
     * Legal limit is typically 56 hours per week (8 hours × 7 days).
     */
    protected function getDriversExceedingLegalHours(int $limit = 56, int $topCount = 3): Collection
    {
        $weekStart = Carbon::now()->startOfWeek();
        $weekEnd = Carbon::now()->endOfWeek();

        $drivers = Driver::query()
            ->whereHas('activities', function ($query) use ($weekStart, $weekEnd) {
                $query->whereBetween('activity_date', [$weekStart->toDateString(), $weekEnd->toDateString()]);
            })
            ->with(['activities' => function ($query) use ($weekStart, $weekEnd) {
                $query->whereBetween('activity_date', [$weekStart->toDateString(), $weekEnd->toDateString()]);
            }])
            ->get();

        $driversWithHours = $drivers->map(function ($driver) use ($weekStart, $weekEnd, $limit) {
            $activities = $driver->activities->filter(function ($activity) use ($weekStart, $weekEnd) {
                if (!$activity->activity_date) {
                    return false;
                }

                $date = $activity->activity_date instanceof Carbon
                    ? $activity->activity_date
                    : Carbon::parse($activity->activity_date);

                return $date->between($weekStart, $weekEnd);
            });

            $totalHours = $activities->sum(fn($activity) => $this->timeToDecimal($activity->driving_time ?? null));

            return [
                'driver' => $driver,
                'total_hours' => $totalHours,
                'over_limit' => max(0, $totalHours - $limit),
            ];
        })
        ->filter(function($item) use ($limit) {
            return $item['total_hours'] > $limit;
        })
        ->sortByDesc('total_hours')
        ->take($topCount)
        ->values();

        return $driversWithHours;
    }

    /**
     * Convert time string (HH:MM:SS or HH:MM) to decimal hours.
     */
    protected function timeToDecimal(?string $time): float
    {
        if (!$time) {
            return 0.0;
        }

        $parts = explode(':', $time);
        $hours = (int) ($parts[0] ?? 0);
        $minutes = (int) ($parts[1] ?? 0);
        $seconds = (int) ($parts[2] ?? 0);

        return $hours + ($minutes / 60) + ($seconds / 3600);
    }
}