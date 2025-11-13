<?php

namespace App\Http\Controllers;

use App\Exports\DriverFormationAlertsExport;
use App\Exports\DriverActivityTimelineExport;
use App\Http\Requests\UpdateDriverRequest;
use App\Models\Driver;
use App\Models\Vehicle;
use App\Models\Flotte;
use App\Models\DriverFormation;
use App\Models\DriverActivity;
use App\Models\Formation;
use Illuminate\View\View;
use Illuminate\Http\Request;
use Illuminate\Http\RedirectResponse;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use Maatwebsite\Excel\Facades\Excel;
use Barryvdh\DomPDF\Facade\Pdf;

class DriversController extends Controller
{
    public function index(): View
    {
        $drivers = Driver::query()
            ->with(['assignedVehicle', 'flotte'])
            ->get();

        $total = $drivers->count();

        $resolveStatus = function ($driver): ?string {
            $status = data_get($driver, 'status') ?? data_get($driver, 'statu') ?? data_get($driver, 'state');
            if ($status === null) return null;
            return strtolower(trim((string)$status));
        };

        $activeValues = ['active','actif','enabled','enable','1','true','yes'];
        $inactiveValues = ['inactive','inactif','disabled','disable','deactive','0','false','no'];

        $active = $drivers->filter(function ($driver) use ($resolveStatus, $activeValues) {
            $v = $resolveStatus($driver);
            return $v !== null && in_array($v, $activeValues, true);
        })->count();

        $inactive = $drivers->filter(function ($driver) use ($resolveStatus, $inactiveValues) {
            $v = $resolveStatus($driver);
            return $v !== null && in_array($v, $inactiveValues, true);
        })->count();

        $alertFormations = $this->getFormationAlerts();

        $driversWithAlerts = $alertFormations->pluck('driver_id')->unique()->count();

        return view('drivers.index', compact('drivers', 'total', 'active', 'inactive', 'driversWithAlerts'));
    }

    public function show(Request $request, Driver $driver): View
    {
        // Load relationships
        $driver->load([
            'assignedVehicle',
            'flotte',
            'formations.formation',
            'formations.formationProcess.steps',
        ]);

        // Get filter parameters
        $violationType = $request->get('violation_type');
        $severity = $request->get('severity');
        $dateFrom = $request->get('date_from', Carbon::now()->subWeek()->format('Y-m-d'));
        $dateTo = $request->get('date_to', Carbon::now()->format('Y-m-d'));

        // Load driver activities for the selected date range
        $activities = DriverActivity::where('driver_id', $driver->id)
            ->whereBetween('activity_date', [$dateFrom, $dateTo])
            ->orderBy('activity_date')
            ->orderBy('start_time')
            ->get();

        // Get compliance configuration
        $maxDrivingHours = config('driver_activity.max_daily_driving_hours', 8);
        $minRestHours = config('driver_activity.min_daily_rest_hours', 4);
        $maxTotalHours = config('driver_activity.max_daily_total_hours', 12);
        $workingWindowStart = config('driver_activity.working_window_start', '06:00');
        $workingWindowEnd = config('driver_activity.working_window_end', '20:00');
        $thresholds = config('driver_activity.violation_thresholds', []);

        // Group activities by date and aggregate
        $timelineData = [];
        $allViolations = [];
        $totalDrivingHoursThisWeek = 0;

        $activitiesByDate = $activities->groupBy(function ($activity) {
            return $activity->activity_date->format('Y-m-d');
        });

        foreach ($activitiesByDate as $date => $dayActivities) {
            $activityDate = Carbon::parse($date);
            
            // Aggregate daily totals
            $drivingHours = $dayActivities->sum('driving_hours');
            $restHours = $dayActivities->sum('rest_hours');
            $totalHours = $drivingHours + $restHours;

            // Find earliest start time and latest end time
            $startTimes = $dayActivities->pluck('start_time')->filter();
            $endTimes = $dayActivities->pluck('end_time')->filter();
            $earliestStart = $startTimes->isNotEmpty() ? $startTimes->min() : null;
            $latestEnd = $endTimes->isNotEmpty() ? $endTimes->max() : null;

            // Combine route descriptions
            $routeDescriptions = $dayActivities->pluck('route_description')->filter()->unique()->values();
            $routeInfo = $routeDescriptions->isNotEmpty() ? $routeDescriptions->implode('; ') : null;

            // Check compliance and generate violations
            $dayViolations = $this->checkCompliance(
                $activityDate,
                $drivingHours,
                $restHours,
                $totalHours,
                $earliestStart,
                $latestEnd,
                $maxDrivingHours,
                $minRestHours,
                $maxTotalHours,
                $workingWindowStart,
                $workingWindowEnd,
                $thresholds,
                $routeInfo
            );

            // Add to all violations
            foreach ($dayViolations as $violation) {
                $allViolations[] = $violation;
            }

            // Calculate week total (for the current week)
            $weekStart = Carbon::now()->startOfWeek();
            $weekEnd = Carbon::now()->endOfWeek();
            if ($activityDate->between($weekStart, $weekEnd)) {
                $totalDrivingHoursThisWeek += $drivingHours;
            }

            // Build timeline day data
            $timelineData[] = [
                'date' => $date,
                'date_label' => $activityDate->format('d/m/Y'),
                'day_name' => $activityDate->format('l'), // Full day name (Monday, Tuesday, etc.)
                'driving_hours' => $drivingHours,
                'rest_hours' => $restHours,
                'total_hours' => $totalHours,
                'start_time' => $earliestStart,
                'end_time' => $latestEnd,
                'route_description' => $routeInfo,
                'violations' => $dayViolations,
                'is_compliant' => count($dayViolations) === 0,
            ];
        }

        // Filter violations based on request filters
        $violations = collect($allViolations);

        if ($violationType) {
            $violations = $violations->filter(function ($violation) use ($violationType) {
                return $violation['type'] === $violationType;
            });
        }

        if ($severity) {
            $violations = $violations->filter(function ($violation) use ($severity) {
                return $violation['severity'] === $severity;
            });
        }

        $violations = $violations->values()->all();
        $totalViolations = count($allViolations);

        // Violation types for filter dropdown
        $violationTypes = [
            'speed' => __('messages.speed_excess'),
            'rest' => __('messages.insufficient_rest'),
            'driving_time' => __('messages.driving_time_exceeded'),
            'safety' => __('messages.safety_violation'),
            'documentation' => __('messages.missing_documentation'),
        ];

        // Severity options
        $severityOptions = [
            'low' => __('messages.low'),
            'medium' => __('messages.medium'),
            'high' => __('messages.high'),
        ];

        // Integration removed; set placeholders
        $integration = null;
        $integrationProgress = null;

        // Get formations for this driver
        $formations = DriverFormation::where('driver_id', $driver->id)
            ->with(['formation.category', 'formationProcess'])
            ->orderBy('created_at', 'desc')
            ->get();

        // Calculate alerts based on latest completion date for each formation
        $formationsByFormationId = $formations->groupBy('formation_id');
        $alertStates = $formationsByFormationId->map(function ($driverFormations) {
            // Get the latest completed formation
            $latestDone = $driverFormations
                ->filter(fn($df) => $df->status === 'done' && $df->done_at)
                ->sortByDesc(fn($df) => $df->done_at)
                ->first();
            
            if (!$latestDone || !$latestDone->formation) {
                return 'none';
            }
            
            return $latestDone->formation->calculateAlertState($latestDone->done_at);
        });
        
        $warningAlerts = $alertStates->filter(fn ($state) => $state === 'warning')->count();
        $criticalAlerts = $alertStates->filter(fn ($state) => $state === 'critical')->count();

        $formationsCatalog = Formation::with(['category', 'flotte'])
            ->orderBy('name')
            ->get();

        return view('drivers.show', compact(
            'driver',
            'violations',
            'totalViolations',
            'totalDrivingHoursThisWeek',
            'timelineData',
            'violationTypes',
            'severityOptions',
            'violationType',
            'severity',
            'dateFrom',
            'dateTo',
            'integration',
            'integrationProgress',
            'formations',
            'formationsCatalog',
            'warningAlerts',
            'criticalAlerts'
        ));
    }

    public function alerts(): View
    {
        $alerts = $this->getFormationAlerts();
        
        // Calculate alert states using Formation model
        $alertsWithStates = $alerts->map(function (DriverFormation $driverFormation) {
            $alertState = $driverFormation->formation 
                ? $driverFormation->formation->calculateAlertState($driverFormation->done_at)
                : 'none';
            return [
                'driverFormation' => $driverFormation,
                'alertState' => $alertState,
            ];
        });

        $sortedAlerts = $alertsWithStates
            ->sortBy(fn ($item) => $item['alertState'] === 'critical' ? 0 : ($item['alertState'] === 'warning' ? 1 : 2))
            ->values();

        $criticalCount = $sortedAlerts->filter(fn ($item) => $item['alertState'] === 'critical')->count();
        $warningCount = $sortedAlerts->filter(fn ($item) => $item['alertState'] === 'warning')->count();
        $driversCount = $sortedAlerts->pluck('driverFormation.driver_id')->unique()->count();

        // Extract just the DriverFormation instances for the view
        $alerts = $sortedAlerts->pluck('driverFormation');

        return view('drivers.alerts', compact('alerts', 'criticalCount', 'warningCount', 'driversCount'));
    }

    public function exportAlerts()
    {
        $alerts = $this->getFormationAlerts();
        
        // Calculate alert states using Formation model for sorting
        $alertsWithStates = $alerts->map(function (DriverFormation $driverFormation) {
            $alertState = $driverFormation->formation 
                ? $driverFormation->formation->calculateAlertState($driverFormation->done_at)
                : 'none';
            return [
                'driverFormation' => $driverFormation,
                'alertState' => $alertState,
            ];
        });

        $sortedAlerts = $alertsWithStates
            ->sortBy(fn ($item) => $item['alertState'] === 'critical' ? 0 : ($item['alertState'] === 'warning' ? 1 : 2))
            ->values()
            ->pluck('driverFormation');

        $fileName = 'formation-alerts-' . now()->format('Ymd_His') . '.xlsx';
        return Excel::download(new DriverFormationAlertsExport($sortedAlerts), $fileName);
    }

    private function getFormationAlerts()
    {
        $allDriverFormations = DriverFormation::with(['driver.assignedVehicle', 'driver.flotte', 'formation.category'])
            ->get();

        // Group by driver_id and formation_id to get latest completion for each combination
        $grouped = $allDriverFormations->groupBy(function ($df) {
            return $df->driver_id . '_' . $df->formation_id;
        });

        $alerts = collect();

        foreach ($grouped as $key => $driverFormations) {
            // Get the latest completed formation for this driver+formation combination
            $latestDone = $driverFormations
                ->filter(fn($df) => $df->status === 'done' && $df->done_at && $df->formation)
                ->sortByDesc(fn($df) => $df->done_at)
                ->first();

            if ($latestDone && $latestDone->formation) {
                $alertState = $latestDone->formation->calculateAlertState($latestDone->done_at);
                
                if (in_array($alertState, ['warning', 'critical'], true)) {
                    $alerts->push($latestDone);
                }
            }
        }

        return $alerts;
    }

    /**
     * Store a simplified driver formation for TMD and 16 Module categories.
     */
    public function storeQuickFormation(Request $request, Driver $driver): RedirectResponse
    {
        try {
            $validator = Validator::make($request->all(), [
                'formation_id' => ['required', 'exists:formations,id'],
                'due_at' => ['required', 'date'],
                'report_file' => ['required', 'file', 'mimes:pdf,doc,docx,xlsx', 'max:5120'],
            ]);

            if ($validator->fails()) {
                return back()
                    ->withErrors($validator)
                    ->withInput()
                    ->with('open_quick_modal', true);
            }

            $data = $validator->validated();

            $formation = Formation::with('category')->findOrFail($data['formation_id']);
            $categoryName = optional($formation->category)->name;
            $normalizedCategory = $categoryName ? Str::of($categoryName)->lower()->trim()->__toString() : null;

            if (!in_array($normalizedCategory, ['tmd', '16 module'], true)) {
                return back()
                    ->with('error', "Cette formation ne peut pas être traitée via ce formulaire.")
                    ->withInput()
                    ->with('open_quick_modal', true);
            }

            $driverFlotteName = optional($driver->flotte)->name;
            $normalizedFlotte = $driverFlotteName ? Str::of($driverFlotteName)->lower()->trim()->__toString() : null;

            $isTmdAllowed = $normalizedCategory === 'tmd' && $normalizedFlotte === 'total';
            $isModuleAllowed = $normalizedCategory === '16 module' && $normalizedFlotte === 'vivo';

            if (!$isTmdAllowed && !$isModuleAllowed) {
                return back()
                    ->with('error', "Cette formation n'est pas disponible pour ce conducteur.")
                    ->withInput()
                    ->with('open_quick_modal', true);
            }

            $dueDate = Carbon::parse($data['due_at'])->startOfDay();

            $reportPath = $request->file('report_file')->store('driver-formations/reports', 'uploads');

            DriverFormation::create([
                'driver_id' => $driver->id,
                'formation_id' => $formation->id,
                'formation_process_id' => null,
                'status' => 'done',
                'planned_at' => $dueDate,
                'due_at' => $dueDate,
                'done_at' => $dueDate,
                'progress_percent' => 100,
                'validation_status' => 'validated',
                'certificate_path' => $reportPath,
                'notes' => null,
            ]);

            Log::info('Quick driver formation stored', [
                'driver_id' => $driver->id,
                'formation_id' => $formation->id,
                'category' => $normalizedCategory,
            ]);

            return redirect()
                ->route('drivers.show', $driver)
                ->with('success', 'Formation enregistrée avec succès.');
        } catch (\Throwable $e) {
            Log::error('Failed to store quick driver formation', [
                'driver_id' => $driver->id,
                'error' => $e->getMessage(),
            ]);

            return back()
                ->withInput()
                ->with('open_quick_modal', true)
                ->with('error', "Une erreur est survenue lors de l'enregistrement de la formation.");
        }
    }

    /**
     * Store a new driver activity.
     */
    public function storeActivity(Request $request, Driver $driver): RedirectResponse
    {
        try {
            $validator = Validator::make($request->all(), [
                'activity_date' => ['required', 'date'],
                'start_time' => ['required', 'date_format:H:i'],
                'end_time' => ['required', 'date_format:H:i'],
                'driving_hours' => ['required', 'numeric', 'min:0', 'max:' . config('driver_activity.max_daily_driving_hours', 8)],
                'rest_hours' => ['required', 'numeric', 'min:0'],
                'route_description' => ['nullable', 'string', 'max:500'],
                'compliance_notes' => ['nullable', 'string', 'max:1000'],
            ]);

            // Custom validation: end_time must be after start_time
            $validator->after(function ($validator) use ($request) {
                if ($request->has('start_time') && $request->has('end_time')) {
                    $start = \Carbon\Carbon::createFromFormat('H:i', $request->start_time);
                    $end = \Carbon\Carbon::createFromFormat('H:i', $request->end_time);
                    if ($end->lte($start)) {
                        $validator->errors()->add('end_time', __('messages.end_time_must_be_after_start_time') ?? 'End time must be after start time.');
                    }
                }
            });

            if ($validator->fails()) {
                return back()
                    ->withErrors($validator)
                    ->withInput()
                    ->with('open_activity_modal', true);
            }

            $data = $validator->validated();

            // Convert hours to integers (stored as hours, not minutes)
            $data['driving_hours'] = (int) round($data['driving_hours']);
            $data['rest_hours'] = (int) round($data['rest_hours']);

            DriverActivity::create([
                'driver_id' => $driver->id,
                'activity_date' => $data['activity_date'],
                'start_time' => $data['start_time'],
                'end_time' => $data['end_time'],
                'driving_hours' => $data['driving_hours'],
                'rest_hours' => $data['rest_hours'],
                'route_description' => $data['route_description'] ?? null,
                'compliance_notes' => $data['compliance_notes'] ?? null,
            ]);

            Log::info('Driver activity stored', [
                'driver_id' => $driver->id,
                'activity_date' => $data['activity_date'],
                'driving_hours' => $data['driving_hours'],
                'rest_hours' => $data['rest_hours'],
            ]);

            return redirect()
                ->route('drivers.show', $driver)
                ->with('success', __('messages.activity_saved_successfully') ?? 'Activity saved successfully.');
        } catch (\Throwable $e) {
            Log::error('Failed to store driver activity', [
                'driver_id' => $driver->id,
                'error' => $e->getMessage(),
            ]);

            return back()
                ->withInput()
                ->with('open_activity_modal', true)
                ->with('error', __('messages.error_saving_activity') ?? 'An error occurred while saving the activity.');
        }
    }

    /**
     * Export driver activity timeline as PDF
     */
    public function exportTimelinePDF(Request $request, Driver $driver)
    {
        try {
            $dateFrom = $request->get('date_from', Carbon::now()->subWeek()->format('Y-m-d'));
            $dateTo = $request->get('date_to', Carbon::now()->format('Y-m-d'));

            // Load driver activities for the selected date range
            $activities = DriverActivity::where('driver_id', $driver->id)
                ->whereBetween('activity_date', [$dateFrom, $dateTo])
                ->orderBy('activity_date')
                ->orderBy('start_time')
                ->get();

            // Get compliance configuration
            $maxDrivingHours = config('driver_activity.max_daily_driving_hours', 8);
            $minRestHours = config('driver_activity.min_daily_rest_hours', 4);
            $maxTotalHours = config('driver_activity.max_daily_total_hours', 12);
            $workingWindowStart = config('driver_activity.working_window_start', '06:00');
            $workingWindowEnd = config('driver_activity.working_window_end', '20:00');
            $thresholds = config('driver_activity.violation_thresholds', []);

            // Group activities by date and aggregate (same logic as show method)
            $timelineData = [];
            $allViolations = [];
            $totalDrivingHours = 0;

            $activitiesByDate = $activities->groupBy(function ($activity) {
                return $activity->activity_date->format('Y-m-d');
            });

            foreach ($activitiesByDate as $date => $dayActivities) {
                $activityDate = Carbon::parse($date);
                
                $drivingHours = $dayActivities->sum('driving_hours');
                $restHours = $dayActivities->sum('rest_hours');
                $totalHours = $drivingHours + $restHours;

                $startTimes = $dayActivities->pluck('start_time')->filter();
                $endTimes = $dayActivities->pluck('end_time')->filter();
                $earliestStart = $startTimes->isNotEmpty() ? $startTimes->min() : null;
                $latestEnd = $endTimes->isNotEmpty() ? $endTimes->max() : null;

                $routeDescriptions = $dayActivities->pluck('route_description')->filter()->unique()->values();
                $routeInfo = $routeDescriptions->isNotEmpty() ? $routeDescriptions->implode('; ') : null;

                $dayViolations = $this->checkCompliance(
                    $activityDate,
                    $drivingHours,
                    $restHours,
                    $totalHours,
                    $earliestStart,
                    $latestEnd,
                    $maxDrivingHours,
                    $minRestHours,
                    $maxTotalHours,
                    $workingWindowStart,
                    $workingWindowEnd,
                    $thresholds,
                    $routeInfo
                );

                foreach ($dayViolations as $violation) {
                    $allViolations[] = $violation;
                }

                $totalDrivingHours += $drivingHours;

                $timelineData[] = [
                    'date' => $date,
                    'date_label' => $activityDate->format('d/m/Y'),
                    'day_name' => $activityDate->format('l'),
                    'driving_hours' => $drivingHours,
                    'rest_hours' => $restHours,
                    'total_hours' => $totalHours,
                    'start_time' => $earliestStart,
                    'end_time' => $latestEnd,
                    'route_description' => $routeInfo,
                    'violations' => $dayViolations,
                    'is_compliant' => count($dayViolations) === 0,
                ];
            }

            $pdf = Pdf::loadView('drivers.timeline-pdf', [
                'driver' => $driver,
                'timelineData' => $timelineData,
                'violations' => $allViolations,
                'totalDrivingHours' => $totalDrivingHours,
                'dateFrom' => Carbon::parse($dateFrom)->format('d/m/Y'),
                'dateTo' => Carbon::parse($dateTo)->format('d/m/Y'),
            ])->setPaper('a4', 'landscape');

            $fileName = sprintf(
                'driver-activity-timeline-%d-%s.pdf',
                $driver->id,
                now()->format('YmdHis')
            );

            return $pdf->download($fileName);
        } catch (\Throwable $e) {
            Log::error('Failed to export timeline PDF', [
                'driver_id' => $driver->id,
                'error' => $e->getMessage(),
            ]);

            return back()->with('error', __('messages.error_exporting_pdf') ?? 'Error exporting PDF.');
        }
    }

    /**
     * Export driver activity timeline as CSV
     */
    public function exportTimelineCSV(Request $request, Driver $driver)
    {
        try {
            $dateFrom = $request->get('date_from', Carbon::now()->subWeek()->format('Y-m-d'));
            $dateTo = $request->get('date_to', Carbon::now()->format('Y-m-d'));

            // Load driver activities for the selected date range
            $activities = DriverActivity::where('driver_id', $driver->id)
                ->whereBetween('activity_date', [$dateFrom, $dateTo])
                ->orderBy('activity_date')
                ->orderBy('start_time')
                ->get();

            // Get compliance configuration
            $maxDrivingHours = config('driver_activity.max_daily_driving_hours', 8);
            $minRestHours = config('driver_activity.min_daily_rest_hours', 4);
            $maxTotalHours = config('driver_activity.max_daily_total_hours', 12);
            $workingWindowStart = config('driver_activity.working_window_start', '06:00');
            $workingWindowEnd = config('driver_activity.working_window_end', '20:00');
            $thresholds = config('driver_activity.violation_thresholds', []);

            // Group activities by date and aggregate (same logic as show method)
            $timelineData = collect();
            $allViolations = collect();

            $activitiesByDate = $activities->groupBy(function ($activity) {
                return $activity->activity_date->format('Y-m-d');
            });

            foreach ($activitiesByDate as $date => $dayActivities) {
                $activityDate = Carbon::parse($date);
                
                $drivingHours = $dayActivities->sum('driving_hours');
                $restHours = $dayActivities->sum('rest_hours');
                $totalHours = $drivingHours + $restHours;

                $startTimes = $dayActivities->pluck('start_time')->filter();
                $endTimes = $dayActivities->pluck('end_time')->filter();
                $earliestStart = $startTimes->isNotEmpty() ? $startTimes->min() : null;
                $latestEnd = $endTimes->isNotEmpty() ? $endTimes->max() : null;

                $routeDescriptions = $dayActivities->pluck('route_description')->filter()->unique()->values();
                $routeInfo = $routeDescriptions->isNotEmpty() ? $routeDescriptions->implode('; ') : null;

                $dayViolations = $this->checkCompliance(
                    $activityDate,
                    $drivingHours,
                    $restHours,
                    $totalHours,
                    $earliestStart,
                    $latestEnd,
                    $maxDrivingHours,
                    $minRestHours,
                    $maxTotalHours,
                    $workingWindowStart,
                    $workingWindowEnd,
                    $thresholds,
                    $routeInfo
                );

                foreach ($dayViolations as $violation) {
                    $allViolations->push($violation);
                }

                $timelineData->push([
                    'date' => $date,
                    'date_label' => $activityDate->format('d/m/Y'),
                    'day_name' => $activityDate->format('l'),
                    'driving_hours' => $drivingHours,
                    'rest_hours' => $restHours,
                    'total_hours' => $totalHours,
                    'start_time' => $earliestStart,
                    'end_time' => $latestEnd,
                    'route_description' => $routeInfo,
                    'violations' => $dayViolations,
                    'is_compliant' => count($dayViolations) === 0,
                ]);
            }

            $fileName = sprintf(
                'driver-activity-timeline-%d-%s.xlsx',
                $driver->id,
                now()->format('YmdHis')
            );

            return Excel::download(
                new DriverActivityTimelineExport($timelineData, $allViolations),
                $fileName
            );
        } catch (\Throwable $e) {
            Log::error('Failed to export timeline CSV', [
                'driver_id' => $driver->id,
                'error' => $e->getMessage(),
            ]);

            return back()->with('error', __('messages.error_exporting_csv') ?? 'Error exporting CSV.');
        }
    }

    // Removed placeholder violations method; wire actual data when available

    /**
     * Check compliance for a day's activities and generate violations
     */
    private function checkCompliance(
        Carbon $date,
        float $drivingHours,
        float $restHours,
        float $totalHours,
        ?string $startTime,
        ?string $endTime,
        int $maxDrivingHours,
        int $minRestHours,
        int $maxTotalHours,
        string $workingWindowStart,
        string $workingWindowEnd,
        array $thresholds,
        ?string $location
    ): array {
        $violations = [];
        $baseId = (int) $date->format('Ymd'); // Use date as base for unique IDs

        // Check driving hours limit
        if ($drivingHours > $maxDrivingHours) {
            $overLimit = $drivingHours - $maxDrivingHours;
            $severity = $this->determineSeverity($overLimit, $thresholds['driving_hours'] ?? []);
            
            $violations[] = [
                'id' => $baseId * 100 + count($violations) + 1,
                'date' => $date->format('d/m/Y'),
                'time' => $startTime ?? '00:00',
                'type' => 'driving_time',
                'type_label' => __('messages.driving_time_exceeded'),
                'rule' => __('messages.max_driving_hours_exceeded') . ": {$maxDrivingHours}h max, " . round($drivingHours, 1) . "h actual",
                'severity' => $severity,
                'severity_label' => ucfirst($severity),
                'location' => $location ?? __('messages.unknown_location'),
            ];
        }

        // Check rest hours minimum
        if ($restHours < $minRestHours) {
            $underMinimum = $minRestHours - $restHours;
            $severity = $this->determineSeverity($underMinimum, $thresholds['rest_hours'] ?? []);
            
            $violations[] = [
                'id' => $baseId * 100 + count($violations) + 1,
                'date' => $date->format('d/m/Y'),
                'time' => $endTime ?? '23:59',
                'type' => 'rest',
                'type_label' => __('messages.insufficient_rest'),
                'rule' => __('messages.insufficient_rest') . ": {$minRestHours}h min, " . round($restHours, 1) . "h actual",
                'severity' => $severity,
                'severity_label' => ucfirst($severity),
                'location' => $location ?? __('messages.unknown_location'),
            ];
        }

        // Check total hours limit
        if ($totalHours > $maxTotalHours) {
            $overLimit = $totalHours - $maxTotalHours;
            $severity = $this->determineSeverity($overLimit, $thresholds['total_hours'] ?? []);
            
            $violations[] = [
                'id' => $baseId * 100 + count($violations) + 1,
                'date' => $date->format('d/m/Y'),
                'time' => $endTime ?? '23:59',
                'type' => 'driving_time',
                'type_label' => __('messages.total_work_hours_exceeded'),
                'rule' => __('messages.total_work_hours_exceeded') . ": {$maxTotalHours}h max, " . round($totalHours, 1) . "h actual",
                'severity' => $severity,
                'severity_label' => ucfirst($severity),
                'location' => $location ?? __('messages.unknown_location'),
            ];
        }

        // Check working window compliance
        if ($startTime && $endTime) {
            // Handle time format (could be H:i or H:i:s)
            $startTimeFormatted = strlen($startTime) === 5 ? $startTime . ':00' : $startTime;
            $endTimeFormatted = strlen($endTime) === 5 ? $endTime . ':00' : $endTime;
            
            try {
                $startCarbon = Carbon::createFromFormat('H:i:s', $startTimeFormatted);
                $endCarbon = Carbon::createFromFormat('H:i:s', $endTimeFormatted);
                $windowStart = Carbon::createFromFormat('H:i', $workingWindowStart);
                $windowEnd = Carbon::createFromFormat('H:i', $workingWindowEnd);
            } catch (\Exception $e) {
                // If parsing fails, skip window check
                return $violations;
            }

            // Check if start time is before window
            if ($startCarbon->lt($windowStart)) {
                $minutesOutside = $startCarbon->diffInMinutes($windowStart);
                $severity = $this->determineSeverityMinutes($minutesOutside, $thresholds['working_window'] ?? []);
                
                $violations[] = [
                    'id' => ++$violationId,
                    'date' => $date->format('d/m/Y'),
                    'time' => $startTime,
                    'type' => 'safety',
                    'type_label' => __('messages.working_window_violation'),
                    'rule' => __('messages.working_window_violation') . ": Start time {$startTime} is before allowed window ({$workingWindowStart})",
                    'severity' => $severity,
                    'severity_label' => ucfirst($severity),
                    'location' => $location ?? __('messages.unknown_location'),
                ];
            }

            // Check if end time is after window
            if ($endCarbon->gt($windowEnd)) {
                $minutesOutside = $endCarbon->diffInMinutes($windowEnd);
                $severity = $this->determineSeverityMinutes($minutesOutside, $thresholds['working_window'] ?? []);
                
                $violations[] = [
                    'id' => ++$violationId,
                    'date' => $date->format('d/m/Y'),
                    'time' => $endTime,
                    'type' => 'safety',
                    'type_label' => __('messages.working_window_violation'),
                    'rule' => __('messages.working_window_violation') . ": End time {$endTime} is after allowed window ({$workingWindowEnd})",
                    'severity' => $severity,
                    'severity_label' => ucfirst($severity),
                    'location' => $location ?? __('messages.unknown_location'),
                ];
            }
        }

        return $violations;
    }

    /**
     * Determine violation severity based on threshold
     */
    private function determineSeverity(float $value, array $thresholds): string
    {
        if ($value >= ($thresholds['high'] ?? 4)) {
            return 'high';
        } elseif ($value >= ($thresholds['medium'] ?? 2)) {
            return 'medium';
        } else {
            return 'low';
        }
    }

    /**
     * Determine violation severity based on minutes threshold
     */
    private function determineSeverityMinutes(int $minutes, array $thresholds): string
    {
        if ($minutes >= ($thresholds['high'] ?? 120)) {
            return 'high';
        } elseif ($minutes >= ($thresholds['medium'] ?? 60)) {
            return 'medium';
        } else {
            return 'low';
        }
    }

    /**
     * Calculate driving hours for this week (placeholder)
     */
    // Removed placeholder driving hours; show 0 until real data is connected
    private function calculateDrivingHoursThisWeek($driver)
    {
        return 0;
    }

    /**
     * Prepare timeline data for Gantt chart
     */
    // Removed placeholder timeline; return empty until real activity data is wired
    private function prepareTimelineData($driver, $dateFrom, $dateTo, $violations)
    {
        return [];
    }

    /**
     * Show the form for editing the specified driver.
     */
    public function edit(Driver $driver): View
    {
        try {
            $driver->load(['assignedVehicle', 'flotte']);
            
            // Get vehicles for dropdown
            $vehicles = Vehicle::all();
            
            // Get flottes for dropdown
            $flottes = Flotte::all();
            
            return view('drivers.edit', compact('driver', 'vehicles', 'flottes'));
        } catch (\Throwable $e) {
            Log::error('Failed to edit driver', [
                'driver_id' => $driver->id,
                'error' => $e->getMessage(),
            ]);
            return back()->with('error', __('messages.error_editing_driver'));
        }
    }

    /**
     * Download the certificate attached to a driver formation.
     */
    public function downloadFormationCertificate(DriverFormation $driverFormation)
    {
        try {
            if (empty($driverFormation->certificate_path)) {
                return back()->with('error', __('messages.file_not_found') ?? 'Fichier introuvable.');
            }

            $path = $driverFormation->certificate_path;
            $disk = null;

            if (Storage::disk('uploads')->exists($path)) {
                $disk = 'uploads';
            } elseif (Storage::disk('public')->exists($path)) {
                $disk = 'public';
            } elseif (Storage::disk(config('filesystems.default'))->exists($path)) {
                $disk = config('filesystems.default');
            }

            if (!$disk) {
                Log::warning('Certificate file not found on any configured disk', [
                    'driver_formation_id' => $driverFormation->id,
                    'path' => $path,
                ]);

                return back()->with('error', __('messages.file_not_found') ?? 'Fichier introuvable.');
            }

            $fileName = basename($path);

            return Storage::disk($disk)->download($path, $fileName);
        } catch (\Throwable $e) {
            Log::error('Failed to download driver formation certificate', [
                'driver_formation_id' => $driverFormation->id,
                'error' => $e->getMessage(),
            ]);

            return back()->with('error', __('messages.error_downloading_file') ?? 'Erreur lors du téléchargement du fichier.');
        }
    }

    /**
     * Update the specified driver in storage.
     */
    public function update(UpdateDriverRequest $request, Driver $driver): RedirectResponse
    {
        try {
            $validated = $request->validated();

            // Handle document uploads
            if ($request->hasFile('documents')) {
                $existingDocuments = $driver->documents ?? [];
                if (!is_array($existingDocuments)) {
                    $existingDocuments = [];
                }

                $uploadedDocuments = [];
                foreach ($request->file('documents') as $file) {
                    $path = $file->store('drivers/documents', 'uploads');
                    $uploadedDocuments[] = [
                        'name' => $file->getClientOriginalName(),
                        'path' => $path,
                        'uploaded_at' => now()->toDateTimeString(),
                    ];
                }

                // Merge with existing documents
                $validated['documents'] = array_merge($existingDocuments, $uploadedDocuments);
            } else {
                // Preserve existing documents if no new files uploaded
                $validated['documents'] = $driver->documents ?? [];
            }

            $driver->update($validated);
            
            Log::info('Driver updated', [
                'driver_id' => $driver->id,
                'driver_name' => $driver->full_name,
            ]);

            return redirect()->route('drivers.show', $driver)
                ->with('success', __('messages.driver_updated_successfully'));
        } catch (\Throwable $e) {
            Log::error('Failed to update driver', [
                'driver_id' => $driver->id,
                'error' => $e->getMessage(),
            ]);
            
            return back()
                ->withInput()
                ->with('error', __('messages.error_updating_driver'));
        }
    }

    /**
     * Upload documents for a driver.
     */
    public function uploadDocuments(Request $request, Driver $driver): RedirectResponse
    {
        try {
            $validated = $request->validate([
                'documents' => 'required|array',
                'documents.*' => 'file|max:10240', // 10MB max per file
            ]);

            $existingDocuments = $driver->documents ?? [];
            if (!is_array($existingDocuments)) {
                $existingDocuments = [];
            }

            $uploadedDocuments = [];
            foreach ($request->file('documents') as $file) {
                $path = $file->store('drivers/documents', 'uploads');
                $uploadedDocuments[] = [
                    'name' => $file->getClientOriginalName(),
                    'path' => $path,
                    'uploaded_at' => now()->toDateTimeString(),
                ];
            }

            // Merge with existing documents
            $allDocuments = array_merge($existingDocuments, $uploadedDocuments);
            
            $driver->update(['documents' => $allDocuments]);

            Log::info('Driver documents uploaded', [
                'driver_id' => $driver->id,
                'files_count' => count($uploadedDocuments),
            ]);

            return back()->with('success', __('messages.documents_uploaded_successfully'));
        } catch (\Throwable $e) {
            Log::error('Failed to upload driver documents', [
                'driver_id' => $driver->id,
                'error' => $e->getMessage(),
            ]);

            return back()
                ->withInput()
                ->with('error', __('messages.error_uploading_documents'));
        }
    }

    /**
     * Delete a document from a driver.
     */
    public function deleteDocument(Request $request, Driver $driver, int $index): RedirectResponse
    {
        try {
            $documents = $driver->documents ?? [];
            if (!is_array($documents)) {
                $documents = [];
            }

            if (!isset($documents[$index])) {
                return back()->with('error', __('messages.document_not_found'));
            }

            $document = $documents[$index];
            
            // Delete file from storage if path exists
            if (isset($document['path']) && Storage::disk('uploads')->exists($document['path'])) {
                Storage::disk('uploads')->delete($document['path']);
            }

            // Remove document from array
            unset($documents[$index]);
            $documents = array_values($documents); // Re-index array

            $driver->update(['documents' => $documents]);

            Log::info('Driver document deleted', [
                'driver_id' => $driver->id,
                'document_index' => $index,
            ]);

            return back()->with('success', __('messages.document_deleted_successfully'));
        } catch (\Throwable $e) {
            Log::error('Failed to delete driver document', [
                'driver_id' => $driver->id,
                'document_index' => $index,
                'error' => $e->getMessage(),
            ]);

            return back()->with('error', __('messages.error_deleting_document'));
        }
    }
}


