<?php

namespace App\Http\Controllers;

use App\Exports\DriverFormationAlertsExport;
use App\Exports\DriverActivityTimelineExport;
use App\Exports\DriverViolationsExport;
use App\Exports\DriversExport;
use App\Http\Requests\UpdateDriverRequest;
use App\Models\Driver;
use App\Models\Vehicle;
use App\Models\Flotte;
use App\Models\DriverFormation;
use App\Models\DriverViolation;
use App\Models\DriverActivity;
use App\Models\Formation;
use App\Models\ViolationType;
use Illuminate\Support\Collection;
use Illuminate\Http\Request;
use Illuminate\Http\RedirectResponse;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use Maatwebsite\Excel\Facades\Excel;
use Barryvdh\DomPDF\Facade\Pdf;
use PhpOffice\PhpSpreadsheet\Shared\Date as ExcelDate;

class DriversController extends Controller
{
    public function index(Request $request)
    {
        $flotteId = $request->get('flotte_id');
        $statusFilter = $request->get('status');

        $driversCollection = $this->driversQuery($flotteId)
            ->when($statusFilter !== 'terminated', fn($query) => $query->whereNotIn('status', ['terminated']))
            ->get();

        $total = $driversCollection->count();

        $resolveStatus = function ($driver): ?string {
            $status = data_get($driver, 'status') ?? data_get($driver, 'statu') ?? data_get($driver, 'state');
            if ($status === null) return null;
            return strtolower(trim((string)$status));
        };

        $activeValues = ['active','actif'];
        $inactiveValues = ['inactive','inactif'];

        $active = $driversCollection->filter(function ($driver) use ($resolveStatus, $activeValues) {
            $v = $resolveStatus($driver);
            return $v !== null && in_array($v, $activeValues, true);
        })->count();

        $inactive = $driversCollection->filter(function ($driver) use ($resolveStatus, $inactiveValues) {
            $v = $resolveStatus($driver);
            return $v !== null && in_array($v, $inactiveValues, true);
        })->count();

        $terminated = $this->countTerminatedDrivers($flotteId);

        $alertFormations = $this->getFormationAlerts();

        $driversWithAlerts = $alertFormations->pluck('driver_id')->unique()->count();
        $flottes = Flotte::orderBy('name')->get();

        $drivers = $driversCollection
            ->reject(fn($driver) => $this->isDriverTerminated($driver));

        if ($statusFilter === 'active') {
            $drivers = $drivers->filter(fn($driver) => $this->isDriverActive($driver));
        } elseif ($statusFilter === 'inactive') {
            $drivers = $drivers->filter(fn($driver) => $this->isDriverInactive($driver));
        }

        $drivers = $drivers->values();

        return view('drivers.index', compact(
            'drivers',
            'total',
            'active',
            'inactive',
            'terminated',
            'driversWithAlerts',
            'flottes',
            'flotteId',
            'statusFilter'
        ));
    }

    public function export(Request $request)
    {
        $flotteId = $request->get('flotte_id');
        $statusFilter = $request->get('status');
        $driversCollection = $this->driversQuery($flotteId)->get();

        if ($statusFilter === 'active') {
            $driversCollection = $driversCollection->filter(fn($driver) => $this->isDriverActive($driver));
        } elseif ($statusFilter === 'inactive') {
            $driversCollection = $driversCollection->filter(fn($driver) => $this->isDriverInactive($driver));
        } elseif ($statusFilter === 'terminated') {
            $driversCollection = $driversCollection->filter(fn($driver) => $this->isDriverTerminated($driver));
        }

        $fileName = 'drivers-' . ($flotteId ?: 'all') . '-' . ($statusFilter ?? 'all') . '-' . now()->format('Ymd_His') . '.xlsx';

        return Excel::download(new DriversExport($driversCollection->values()), $fileName);
    }

    public function terminated()
    {
        $drivers = $this->driversQuery()
            ->get()
            ->filter(fn($driver) => $this->isDriverTerminated($driver))
            ->values();

        return view('drivers.terminated', compact('drivers'));
    }

    public function show(Request $request, Driver $driver)
    {
        // Load relationships
        $driver->load([
            'assignedVehicle',
            'flotte',
            'formations.formation',
            'formations.formationProcess.steps',
            'integrationCandidate',
        ]);
       
        // Get filter parameters
        $violationTypeId = $request->get('violation_type_id');
        $statusFilter = $request->get('status');

        $defaultRangeStart = Carbon::now()->startOfYear();
        $defaultRangeEnd = Carbon::now()->endOfYear();

        $dateFromInput = $request->get('date_from');
        $dateToInput = $request->get('date_to');

        try {
            $rangeStart = $dateFromInput ? Carbon::createFromFormat('Y-m-d', $dateFromInput)->startOfDay() : $defaultRangeStart->copy();
        } catch (\Exception) {
            $rangeStart = $defaultRangeStart->copy();
        }

        try {
            $rangeEnd = $dateToInput ? Carbon::createFromFormat('Y-m-d', $dateToInput)->endOfDay() : $defaultRangeEnd->copy();
        } catch (\Exception) {
            $rangeEnd = $defaultRangeEnd->copy();
        }

        if ($rangeStart->gt($rangeEnd)) {
            [$rangeStart, $rangeEnd] = [$rangeEnd->copy()->startOfDay(), $rangeStart->copy()->endOfDay()];
        }

        $dateFromApplied = $rangeStart->toDateString();
        $dateToApplied = $rangeEnd->toDateString();

        // Load driver activities for the selected date range
        $activities = DriverActivity::where('driver_id', $driver->id)
            ->whereBetween('activity_date', [$dateFromApplied, $dateToApplied])
            ->orderBy('activity_date')
            ->orderBy('start_time')
            ->get();

        $timelineSummary = $this->summarizeDriverActivities($activities);
        $timelineData = $timelineSummary['timeline']->toArray();
        $totalDrivingHoursThisWeek = $this->calculateCurrentWeekDrivingHours($activities);

        $violationFilters = [
            'violation_type_id' => $violationTypeId,
            'status' => $statusFilter,
            'date_from' => $dateFromApplied,
            'date_to' => $dateToApplied,
        ];

        $driverViolations = $this->buildDriverViolationsQuery($driver, $violationFilters)->get();
        $totalViolations = DriverViolation::where('driver_id', $driver->id)->count();

        $violationTypes = ViolationType::orderBy('name')->pluck('name', 'id');

        $statusOptions = [
            'pending' => __('messages.pending'),
            'confirmed' => __('messages.confirmed'),
            'rejected' => __('messages.rejected'),
        ];

        // Integration removed; set placeholders
        $integration = null;
        $integrationProgress = null;

        // Get formations for this driver
        $formations = DriverFormation::where('driver_id', $driver->id)
            ->with(['formation', 'formationProcess'])
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

        $currentYear = now()->year;

        $formationsCatalog = Formation::with(['flotte'])
            ->whereYear('realizing_date', $currentYear)
            ->orderBy('theme')
            ->get();

        // Get TBT formations for the current year (default)
        $tbtFormationYear = $request->get('tbt_year', $currentYear);
        $tbtFormations = \App\Models\TbtFormation::where('year', $tbtFormationYear)
            ->orderBy('month', 'asc')
            ->orderBy('week_start_date', 'asc')
            ->get();

        // Get available years for TBT formations filter
        $tbtFormationYears = \App\Models\TbtFormation::select('year')
            ->distinct()
            ->orderBy('year', 'desc')
            ->pluck('year');

        if (!$tbtFormationYears->contains($tbtFormationYear)) {
            $tbtFormationYears->push($tbtFormationYear);
            $tbtFormationYears = $tbtFormationYears->sortDesc()->values();
        }

        $dateFrom = $dateFromInput;
        $dateTo = $dateToInput;

        return view('drivers.show', compact(
            'driver',
            'driverViolations',
            'totalViolations',
            'totalDrivingHoursThisWeek',
            'timelineData',
            'activities', // Pass individual activities for delete functionality
            'violationTypes',
            'statusOptions',
            'violationTypeId',
            'statusFilter',
            'dateFrom',
            'dateTo',
            'integration',
            'integrationProgress',
            'formations',
            'formationsCatalog',
            'warningAlerts',
            'criticalAlerts',
            'violationFilters',
            'tbtFormations',
            'tbtFormationYear',
            'tbtFormationYears'
        ));
    }

    public function activitiesIndex(Request $request)
    {
        $driverId = $request->get('driver_id');
        $dateFrom = $request->get('date_from');
        $dateTo = $request->get('date_to');
        $search = trim((string) $request->get('search'));

        $driversList = Driver::select('id', 'full_name', 'flotte_id')
            ->with('flotte:id,name')
            ->orderByRaw("CASE WHEN (full_name IS NULL OR full_name = '') THEN 1 ELSE 0 END")
            ->orderBy('full_name')
            ->orderBy('id')
            ->get();

        $activitiesQuery = DriverActivity::query()
            ->with(['driver:id,full_name,flotte_id,status', 'driver.flotte:id,name'])
            ->orderByDesc('activity_date')
            ->orderBy('start_time');

        if ($driverId) {
            $activitiesQuery->where('driver_id', $driverId);
        }

        if ($dateFrom) {
            try {
                $fromDate = Carbon::createFromFormat('Y-m-d', $dateFrom)->startOfDay();
                $activitiesQuery->whereDate('activity_date', '>=', $fromDate);
            } catch (\Throwable $e) {
                // Ignore invalid input
            }
        }

        if ($dateTo) {
            try {
                $toDate = Carbon::createFromFormat('Y-m-d', $dateTo)->endOfDay();
                $activitiesQuery->whereDate('activity_date', '<=', $toDate);
            } catch (\Throwable $e) {
                // Ignore invalid input
            }
        }

        if ($search !== '') {
            $activitiesQuery->where(function ($query) use ($search) {
                $query->where('driver_name', 'like', "%{$search}%")
                    ->orWhere('flotte', 'like', "%{$search}%")
                    ->orWhere('asset_description', 'like', "%{$search}%")
                    ->orWhere('raison', 'like', "%{$search}%")
                    ->orWhere('start_location', 'like', "%{$search}%")
                    ->orWhere('overnight_location', 'like', "%{$search}%");
            });
        }

        $statsQuery = (clone $activitiesQuery)->reorder();

        $totalActivities = (clone $statsQuery)->count();
        $uniqueDrivers = (clone $statsQuery)->select('driver_id')->distinct()->count('driver_id');

        $durationTotals = (clone $statsQuery)
            ->selectRaw('
                COALESCE(SUM(TIME_TO_SEC(driving_time)), 0) as driving_seconds,
                COALESCE(SUM(TIME_TO_SEC(work_time)), 0) as work_seconds,
                COALESCE(SUM(TIME_TO_SEC(rest_time)), 0) as rest_seconds,
                COALESCE(SUM(TIME_TO_SEC(rest_daily)), 0) as rest_daily_seconds
            ')
            ->first();

        $stats = [
            'totalActivities' => $totalActivities,
            'uniqueDrivers' => $uniqueDrivers,
            'totalDriving' => $this->formatSecondsToDuration((int) ($durationTotals->driving_seconds ?? 0)),
            'totalWork' => $this->formatSecondsToDuration((int) ($durationTotals->work_seconds ?? 0)),
            'totalRest' => $this->formatSecondsToDuration((int) ($durationTotals->rest_seconds ?? 0)),
            'totalRestDaily' => $this->formatSecondsToDuration((int) ($durationTotals->rest_daily_seconds ?? 0)),
        ];

        $activities = $activitiesQuery->paginate(25)->withQueryString();

        return view('drivers.activities', [
            'activities' => $activities,
            'driversList' => $driversList,
            'filters' => [
                'driver_id' => $driverId,
                'date_from' => $dateFrom,
                'date_to' => $dateTo,
                'search' => $search,
            ],
            'stats' => $stats,
        ]);
    }

    public function importActivities(Request $request): RedirectResponse
    {
        $request->validate([
            'activities_file' => ['required', 'file', 'mimes:xlsx,xls,csv', 'max:10240'],
        ]);

        try {
            $collections = Excel::toArray([], $request->file('activities_file'));
            $sheetRows = $collections[0] ?? [];

            if (empty($sheetRows)) {
                return back()->with('error', __('messages.import_no_rows') ?? 'No data rows found in the file.');
            }

            // Skip first 4 rows (header is in row 5, which is index 4 in Excel)
            // Excel row 5 = index 4, so we skip indices 0-3
            $rowsBeforeHeader = array_slice($sheetRows, 0, 4);
            
            if (count($sheetRows) < 5) {
                return back()->with('error', 'File must have at least 5 rows (header row is in row 5).');
            }

            // Row 5 (index 4) is the header row
            $headerRow = $sheetRows[4] ?? [];
            
            // Data rows start from row 6 (index 5)
            $dataRows = array_slice($sheetRows, 5);

            if (empty($headerRow)) {
                return back()->with('error', 'Header row (row 5) is empty.');
            }

            if (empty($dataRows)) {
                return back()->with('error', 'No data rows found after header row.');
            }

            // Normalize header row to build index
            $normalizedHeaders = array_map(fn ($value) => $this->normalizeImportHeader($value), $headerRow);
            $headerIndex = $this->buildHeaderIndex($normalizedHeaders);
            
            // Verify we found the required columns (only check required ones, not optional)
            $required = $this->requiredActivityColumns();
            $requiredOnly = [
                'date' => 'date',
                'chauffeur' => 'chauffeur',
                'first_trip_start_time' => 'premireheurededbutdutrajet',
                'last_trip_end_time' => 'heuredefinduderniertrajet',
                'driving_time' => 'tempsdeconduitehhmmss',
                'rest_time' => 'tempsdattentehhmmss',
            ];
            $missingColumns = [];
            foreach ($requiredOnly as $key => $normalizedColumn) {
                if (!array_key_exists($normalizedColumn, $headerIndex)) {
                    $missingColumns[] = $key;
                }
            }
            
            if (!empty($missingColumns)) {
                $rawHeaders = array_filter($headerRow, fn($h) => $h !== null && trim((string)$h) !== '');
                $foundHeaders = array_keys($headerIndex);
                $errorMessage = "Missing required columns: " . implode(', ', $missingColumns);
                $errorMessage .= ". Found headers: " . implode(', ', array_slice($rawHeaders, 0, 12));
                $errorMessage .= ". Found normalized: " . implode(', ', array_slice($foundHeaders, 0, 12));
                $errorMessage .= ". Required normalized: " . implode(', ', array_values($requiredOnly));
                return back()->with('error', $errorMessage);
            }
            
            // Use data rows for processing
            $sheetRows = $dataRows;

            // Log header index for debugging
            Log::info('Import header detection', [
                'header_index_keys' => array_keys($headerIndex),
                'required_columns' => array_values($this->requiredActivityColumns()),
            ]);

            $driverLookup = $this->buildDriverLookup();
            
            Log::info('Import driver lookup', [
                'total_drivers' => count($driverLookup),
                'sample_drivers' => array_slice(array_map(fn($d) => $d['original_name'], $driverLookup), 0, 5),
            ]);

            $rowsToInsert = [];
            $errors = [];
            $processed = 0;

            foreach ($sheetRows as $rowIndex => $rawRow) {
                if ($this->isRowEmpty($rawRow)) {
                    continue;
                }

                // Skip if this row looks like a header row (contains "Date" or "Chauffeur" as first values)
                $firstCell = trim((string) ($rawRow[0] ?? ''));
                $secondCell = trim((string) ($rawRow[1] ?? ''));
                $normalizedFirst = $this->normalizeImportHeader($firstCell);
                $normalizedSecond = $this->normalizeImportHeader($secondCell);
                
                // If first cell is "date" or second cell is "chauffeur", skip this row (it's likely the header)
                if ($normalizedFirst === 'date' || $normalizedSecond === 'chauffeur') {
                    continue;
                }

                $processed++;
                // Row number in Excel: rowIndex 0 = Excel row 6 (after skipping 4 rows + header row 5)
                // So rowIndex 0 = Excel row 6, rowIndex 1 = Excel row 7, etc.
                $rowNumber = $rowIndex + 6;

                try {
                    // Map the row using header index
                    $mappedRow = $this->mapActivityRow($rawRow, $headerIndex);
                    
                    // Log the mapped row for debugging (first few rows only)
                    if ($processed <= 3) {
                        Log::info('Import row mapping', [
                            'row_number' => $rowNumber,
                            'mapped_row_keys' => array_keys($mappedRow),
                            'mapped_row_values' => array_map(function($v) {
                                return is_string($v) && strlen($v) > 50 ? substr($v, 0, 50) . '...' : $v;
                            }, $mappedRow),
                        ]);
                    }
                    
                    $transformed = $this->transformActivityRow($mappedRow, $driverLookup, $rowNumber);

                    if ($transformed === null) {
                        continue;
                    }

                    $rowsToInsert[] = $transformed;
                } catch (\Throwable $e) {
                    $errorMsg = $e->getMessage();
                    $errors[] = "Row {$rowNumber}: {$errorMsg}";
                    
                    // Log detailed error for first few failures
                    if (count($errors) <= 5) {
                        Log::warning('Import row error', [
                            'row_number' => $rowNumber,
                            'error' => $errorMsg,
                            'raw_row_sample' => array_slice($rawRow, 0, 10),
                            'mapped_row' => $mappedRow ?? null,
                        ]);
                    }
                }
            }

            $inserted = 0;
            if (!empty($rowsToInsert)) {
                foreach (array_chunk($rowsToInsert, 500) as $chunk) {
                    DriverActivity::insert($chunk);
                    $inserted += count($chunk);
                }
            }

            $summary = [
                'processed' => $processed,
                'inserted' => $inserted,
                'skipped' => count($errors),
                'errors' => array_slice($errors, 0, 20),
            ];

            Log::info('Driver activities import completed', [
                'user_id' => $request->user()->id ?? null,
                'summary' => $summary,
            ]);

            return redirect()
                ->route('drivers.activities.index')
                ->with('activity_import', $summary)
                ->with('success', __('messages.import_success') ?? 'Activities imported successfully.');
        } catch (\Throwable $e) {
            Log::error('Driver activities import failed', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            return back()->with('error', __('messages.import_failed') ?? 'Import failed. Please verify the file and try again.');
        }
    }

    public function alerts()
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
        $allDriverFormations = DriverFormation::with(['driver.assignedVehicle', 'driver.flotte', 'formation'])
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
    public function storeQuickFormation(Request $request, Driver $driver)
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

            $formation = Formation::findOrFail($data['formation_id']);
            $normalizedFormationTheme = Str::of($formation->theme ?? '')
                ->lower()
                ->trim()
                ->__toString();
            $isTmdFormation = Str::contains($normalizedFormationTheme, 'tmd');
            $isSixteenModuleFormation = Str::contains($normalizedFormationTheme, '16 module');

            if (!$isTmdFormation && !$isSixteenModuleFormation) {
                return back()
                    ->with('error', "Cette formation ne peut pas être traitée via ce formulaire.")
                    ->withInput()
                    ->with('open_quick_modal', true);
            }

            $driverFlotteName = optional($driver->flotte)->name;
            $normalizedFlotte = $driverFlotteName ? Str::of($driverFlotteName)->lower()->trim()->__toString() : null;

            $isTmdAllowed = $isTmdFormation && $normalizedFlotte === 'total';
            $isModuleAllowed = $isSixteenModuleFormation && $normalizedFlotte === 'vivo';

            if (!$isTmdAllowed && !$isModuleAllowed) {
                return back()
                    ->with('error', "Cette formation n'est pas disponible pour ce conducteur.")
                    ->withInput()
                    ->with('open_quick_modal', true);
            }

            $dueDate = Carbon::parse($data['due_at'])->startOfDay();

            $reportPath = $request->file('report_file')->store('driver-formations/reports', 'public');

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
                'formation_theme' => $normalizedFormationTheme,
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
    public function storeActivity(Request $request, Driver $driver)
    {
        try {
            $timeRule = ['required', 'regex:/^\d{2}:\d{2}(:\d{2})?$/'];

            $validator = Validator::make($request->all(), [
                'activity_date' => ['required', 'date'],
                'flotte' => ['nullable', 'string', 'max:255'],
                'asset_description' => ['nullable', 'string', 'max:255'],
                'driver_name' => ['nullable', 'string', 'max:255'],
                'start_time' => $timeRule,
                'end_time' => $timeRule,
                'work_time' => $timeRule,
                'driving_time' => $timeRule,
                'rest_time' => $timeRule,
                'rest_daily' => ['nullable', 'regex:/^\d{2}:\d{2}(:\d{2})?$/'],
                'raison' => ['nullable', 'string', 'max:1000'],
                'start_location' => ['nullable', 'string', 'max:255'],
                'overnight_location' => ['nullable', 'string', 'max:255'],
            ]);

            // Custom validation: end_time must be after start_time
            $validator->after(function ($validator) use ($request) {
                if ($request->has('start_time') && $request->has('end_time')) {
                    $start = $this->createTimeFromInput($request->start_time);
                    $end = $this->createTimeFromInput($request->end_time);
                    if ($start && $end && $end->lte($start)) {
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

            foreach (['start_time', 'end_time', 'work_time', 'driving_time', 'rest_time', 'rest_daily'] as $timeField) {
                $data[$timeField] = $this->normalizeTimeInput($data[$timeField] ?? null);
            }

            $data['driver_name'] = $data['driver_name'] ?? ($driver->full_name ?? $driver->name ?? null);
            $data['flotte'] = $data['flotte'] ?? ($driver->flotte->name ?? null);

            // Create the new activity first (rest_daily will be null, calculated when next activity is added)
            $newActivity = DriverActivity::create([
                'driver_id' => $driver->id,
                'activity_date' => $data['activity_date'],
                'flotte' => $data['flotte'] ?? null,
                'asset_description' => $data['asset_description'] ?? null,
                'driver_name' => $data['driver_name'] ?? null,
                'start_time' => $data['start_time'],
                'end_time' => $data['end_time'],
                'work_time' => $data['work_time'],
                'driving_time' => $data['driving_time'],
                'rest_time' => $data['rest_time'],
                'rest_daily' => null, // Will be calculated when next activity is added
                'raison' => $data['raison'] ?? null,
                'start_location' => $data['start_location'] ?? null,
                'overnight_location' => $data['overnight_location'] ?? null,
            ]);

            // Now find the previous activity (the one before the one we just created) to calculate rest_daily
            // Convert activity_date to string for comparison if needed
            $newActivityDate = is_string($newActivity->activity_date) 
                ? $newActivity->activity_date 
                : (is_object($newActivity->activity_date) && method_exists($newActivity->activity_date, 'format') 
                    ? $newActivity->activity_date->format('Y-m-d') 
                    : (string)$newActivity->activity_date);
            
            $newStartTime = is_string($newActivity->start_time) 
                ? $newActivity->start_time 
                : (is_object($newActivity->start_time) && method_exists($newActivity->start_time, 'format') 
                    ? $newActivity->start_time->format('H:i:s') 
                    : (string)$newActivity->start_time);

            // Find the previous activity (excluding the one we just created)
            $previousActivity = DriverActivity::where('driver_id', $driver->id)
                ->where('id', '!=', $newActivity->id) // Exclude the activity we just created
                ->where(function($query) use ($newActivityDate, $newStartTime) {
                    // Previous day activities (any activity from a previous day)
                    $query->where('activity_date', '<', $newActivityDate)
                        // Or same day but ended before this one starts
                        ->orWhere(function($q) use ($newActivityDate, $newStartTime) {
                            $q->whereDate('activity_date', '=', $newActivityDate)
                              ->where('end_time', '<=', $newStartTime);
                        });
                })
                ->orderBy('activity_date', 'desc')
                ->orderBy('end_time', 'desc')
                ->first();

            // If we found a previous activity, calculate rest_daily for it
            if ($previousActivity && $previousActivity->end_time) {
                // Format dates properly for calculation
                $previousActivityDate = $previousActivity->activity_date instanceof \Carbon\Carbon 
                    ? $previousActivity->activity_date->format('Y-m-d')
                    : (is_string($previousActivity->activity_date) 
                        ? $previousActivity->activity_date 
                        : ($previousActivity->activity_date ? (string)$previousActivity->activity_date : null));
                
                // Format times properly
                $previousEndTime = $previousActivity->end_time instanceof \DateTime 
                    ? $previousActivity->end_time->format('H:i:s')
                    : (is_string($previousActivity->end_time) 
                        ? $previousActivity->end_time 
                        : ($previousActivity->end_time ? (string)$previousActivity->end_time : null));

                if ($previousActivityDate && $previousEndTime && $newActivityDate && $newStartTime) {
                    // Calculate rest_daily: time between previous activity's end_time and new activity's start_time
                    $restDaily = $this->calculateTimeDifference($previousEndTime, $newStartTime, $previousActivityDate, $newActivityDate);
                
                    if ($restDaily) {
                        $previousActivity->rest_daily = $restDaily;
                        $previousActivity->save();
                        
                        Log::info('Updated rest_daily for previous activity', [
                            'previous_activity_id' => $previousActivity->id,
                            'new_activity_id' => $newActivity->id,
                            'driver_id' => $driver->id,
                            'rest_daily' => $restDaily,
                            'previous_end_time' => $previousEndTime,
                            'new_start_time' => $newStartTime,
                            'previous_activity_date' => $previousActivityDate,
                            'new_activity_date' => $newActivityDate,
                        ]);
                    } else {
                        Log::warning('Failed to calculate rest_daily (returned null)', [
                            'previous_activity_id' => $previousActivity->id,
                            'new_activity_id' => $newActivity->id,
                            'driver_id' => $driver->id,
                            'previous_end_time' => $previousEndTime,
                            'new_start_time' => $newStartTime,
                            'previous_activity_date' => $previousActivityDate,
                            'new_activity_date' => $newActivityDate,
                        ]);
                    }
                } else {
                    Log::warning('Missing required data to calculate rest_daily', [
                        'previous_activity_id' => $previousActivity->id ?? null,
                        'new_activity_id' => $newActivity->id,
                        'driver_id' => $driver->id,
                        'previous_activity_date' => $previousActivityDate,
                        'previous_end_time' => $previousEndTime,
                        'new_activity_date' => $newActivityDate,
                        'new_start_time' => $newStartTime,
                    ]);
                }
            } else {
                Log::info('No previous activity found to calculate rest_daily', [
                    'driver_id' => $driver->id,
                    'new_activity_id' => $newActivity->id,
                    'new_activity_date' => $newActivityDate,
                    'new_start_time' => $newStartTime,
                ]);
            }

            Log::info('Driver activity stored', [
                'driver_id' => $driver->id,
                'activity_date' => $data['activity_date'],
                'work_time' => $data['work_time'],
                'driving_time' => $data['driving_time'],
            ]);

            return back()
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
     * Delete a driver activity.
     */
    public function deleteActivity(DriverActivity $activity): RedirectResponse
    {
        // Restrict to admin role
        if (Auth::user()->role !== 'admin') {
            return back()->with('error', __('messages.unauthorized') ?? 'Unauthorized action.');
        }

        try {
            $driverId = $activity->driver_id;
            $activity->delete();

            Log::info('Driver activity deleted', [
                'activity_id' => $activity->id,
                'driver_id' => $driverId,
            ]);

            return back()->with('success', __('messages.activity_deleted_successfully') ?? 'Activity deleted successfully.');
        } catch (\Throwable $e) {
            Log::error('Failed to delete driver activity', [
                'activity_id' => $activity->id,
                'error' => $e->getMessage(),
            ]);

            return back()->with('error', __('messages.error_deleting_activity') ?? 'Error deleting activity.');
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

            $timelineSummary = $this->summarizeDriverActivities($activities);
            $timelineData = $timelineSummary['timeline']->toArray();
            $allViolations = $timelineSummary['violations']->toArray();
            $totalDrivingHours = $timelineSummary['totalDrivingHours'];

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

            $timelineSummary = $this->summarizeDriverActivities($activities);
            $timelineData = $timelineSummary['timeline'];
            $allViolations = $timelineSummary['violations'];

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

    public function exportViolationsPDF(Request $request, Driver $driver)
    {
        try {
            $filters = $this->extractViolationFilters($request);
            $violations = $this->buildDriverViolationsQuery($driver, $filters)->get();

            if ($violations->isEmpty()) {
                return back()->with('error', __('messages.no_violations_found'));
            }

            $pdf = Pdf::loadView('drivers.violations-pdf', [
                'driver' => $driver,
                'violations' => $violations,
                'filters' => $filters,
            ])->setPaper('a4', 'landscape');
            $driverSlug = Str::slug($driver->full_name ?? 'driver');

            $fileName = sprintf(
                'driver-%s-violations-%s.pdf',
                $driverSlug,
                now()->format('Ymd_His')
            );

            return $pdf->download($fileName);
        } catch (\Throwable $e) {
            report($e);

            return back()->with('error', __('messages.error_exporting_pdf') ?? 'Error exporting PDF.');
        }
    }

    public function exportViolationsCSV(Request $request, Driver $driver)
    {
        try {
            $filters = $this->extractViolationFilters($request);
            $violations = $this->buildDriverViolationsQuery($driver, $filters)->get();

            if ($violations->isEmpty()) {
                return back()->with('error', __('messages.no_violations_found'));
            }

            $export = new DriverViolationsExport($violations);
            $driverSlug = Str::slug($driver->full_name ?? 'driver');
            $fileName = sprintf(
                'driver-%s-violations-%s.xlsx',
                $driverSlug,
                now()->format('Ymd_His')
            );

            return Excel::download($export, $fileName);
        } catch (\Throwable $e) {
            report($e);

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
        float $maxDrivingHours,
        float $maxRestHours,
        float $maxTotalHours,
        array $thresholds,
        ?string $location
    ): array {
        $violations = [];
        $baseId = (int) $date->format('Ymd'); // Use date as base for unique IDs
        $violationId = $baseId * 100;

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
        if ($restHours > $maxRestHours) {
            $overLimit = $restHours - $maxRestHours;
            $severity = $this->determineSeverity($overLimit, $thresholds['rest_hours'] ?? []);
            
            $violations[] = [
                'id' => $baseId * 100 + count($violations) + 1,
                'date' => $date->format('d/m/Y'),
                'time' => $endTime ?? '23:59',
                'type' => 'rest',
                'type_label' => __('messages.rest_time_exceeded'),
                'rule' => __('messages.max_rest_hours_exceeded') . ": {$maxRestHours}h max, " . round($restHours, 1) . "h actual",
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

    private function summarizeDriverActivities(Collection $activities): array
    {
        $maxDrivingHours = config('driver_activity.max_daily_driving_hours', 9);
        $maxRestHours = config('driver_activity.max_daily_rest_hours', 3);
        $maxTotalHours = config('driver_activity.max_daily_total_hours', 12);
        $thresholds = config('driver_activity.violation_thresholds', []);

        $timelineData = collect();
        $allViolations = collect();
        $totalDrivingHours = 0.0;

        $activitiesByDate = $activities->groupBy(function ($activity) {
            $date = $activity->activity_date;
            return $date instanceof Carbon ? $date->format('Y-m-d') : Carbon::parse($date)->format('Y-m-d');
        });

        foreach ($activitiesByDate as $date => $dayActivities) {
            $activityDate = Carbon::parse($date);

            $drivingHours = $this->sumTimeColumn($dayActivities, 'driving_time');
            $restHours = $this->sumTimeColumn($dayActivities, 'rest_time');
            $workHours = $this->sumTimeColumn($dayActivities, 'work_time');
            $restDailyHours = $this->sumTimeColumn($dayActivities, 'rest_daily');
            $totalHours = $workHours > 0 ? $workHours : $drivingHours + $restHours;

            $startTimes = $dayActivities->pluck('start_time')->filter();
            $endTimes = $dayActivities->pluck('end_time')->filter();
            $earliestStart = $startTimes->isNotEmpty() ? $startTimes->min() : null;
            $latestEnd = $endTimes->isNotEmpty() ? $endTimes->max() : null;

            $flotteLabel = $this->condenseValues($dayActivities->pluck('flotte'));
            $assetDescription = $this->condenseValues($dayActivities->pluck('asset_description'));
            $driverName = $this->condenseValues($dayActivities->pluck('driver_name'));
            $raisonLabel = $this->condenseValues($dayActivities->pluck('raison'));
            $startLocationLabel = $this->condenseValues($dayActivities->pluck('start_location'));
            $overnightLocationLabel = $this->condenseValues($dayActivities->pluck('overnight_location'));
            $locationLabel = collect([$startLocationLabel, $overnightLocationLabel])
                ->filter()
                ->implode(' -> ');

            if (!$locationLabel) {
                $locationLabel = $assetDescription ?: null;
            }

            $dayViolations = $this->checkCompliance(
                $activityDate,
                $drivingHours,
                $restHours,
                $totalHours,
                $earliestStart,
                $latestEnd,
                $maxDrivingHours,
                $maxRestHours,
                $maxTotalHours,
                $thresholds,
                $locationLabel
            );

            foreach ($dayViolations as $violation) {
                $allViolations->push($violation);
            }

            $totalDrivingHours += $drivingHours;

            $timelineData->push([
                'date' => $date,
                'date_label' => $activityDate->format('d/m/Y'),
                'day_name' => $activityDate->format('l'),
                'flotte' => $flotteLabel,
                'asset_description' => $assetDescription,
                'driver_name' => $driverName,
                'start_time' => $earliestStart,
                'end_time' => $latestEnd,
                'work_hours' => $workHours,
                'driving_hours' => $drivingHours,
                'rest_hours' => $restHours,
                'rest_daily_hours' => $restDailyHours,
                'total_hours' => $totalHours,
                'raison' => $raisonLabel,
                'start_location' => $startLocationLabel,
                'overnight_location' => $overnightLocationLabel,
                'violations' => $dayViolations,
                'is_compliant' => count($dayViolations) === 0,
            ]);
        }

        return [
            'timeline' => $timelineData->values(),
            'violations' => $allViolations->values(),
            'totalDrivingHours' => round($totalDrivingHours, 2),
        ];
    }

    private function sumTimeColumn(Collection $activities, string $column): float
    {
        return $activities->sum(function ($activity) use ($column) {
            return $this->timeToDecimal($activity->{$column} ?? null);
        });
    }

    private function timeToDecimal($time): float
    {
        if (!$time) {
            return 0.0;
        }

        if ($time instanceof Carbon) {
            $time = $time->format('H:i:s');
        }

        $parts = explode(':', (string) $time);
        $hours = (int) ($parts[0] ?? 0);
        $minutes = (int) ($parts[1] ?? 0);
        $seconds = (int) ($parts[2] ?? 0);

        return $hours + ($minutes / 60) + ($seconds / 3600);
    }

    private function condenseValues(Collection $values): ?string
    {
        $unique = $values->filter()->unique()->values();
        return $unique->isNotEmpty() ? $unique->implode(' / ') : null;
    }

    private function calculateCurrentWeekDrivingHours(Collection $activities): float
    {
        $weekStart = Carbon::now()->startOfWeek();
        $weekEnd = Carbon::now()->endOfWeek();

        $hours = $activities->filter(function ($activity) use ($weekStart, $weekEnd) {
                if (!$activity->activity_date) {
                    return false;
                }

                $date = $activity->activity_date instanceof Carbon
                    ? $activity->activity_date
                    : Carbon::parse($activity->activity_date);

                return $date->between($weekStart, $weekEnd);
            })
            ->sum(fn($activity) => $this->timeToDecimal($activity->driving_time ?? null));

        return $hours;
    }

    private function normalizeTimeInput(?string $time): ?string
    {
        if ($time === null || $time === '') {
            return null;
        }

        if (preg_match('/^\d{2}:\d{2}:\d{2}$/', $time)) {
            return $time;
        }

        if (preg_match('/^\d{2}:\d{2}$/', $time)) {
            return $time . ':00';
        }

        return $time;
    }

    private function createTimeFromInput(?string $time): ?Carbon
    {
        $normalized = $this->normalizeTimeInput($time);

        if (!$normalized) {
            return null;
        }

        try {
            return Carbon::createFromFormat('H:i:s', $normalized);
        } catch (\Exception $e) {
            return null;
        }
    }

    private function formatSecondsToDuration(int $seconds): string
    {
        $seconds = max(0, $seconds);
        $hours = intdiv($seconds, 3600);
        $minutes = intdiv($seconds % 3600, 60);

        return sprintf('%02d:%02d', $hours, $minutes);
    }

    private function normalizeImportHeader(?string $value): string
    {
        if ($value === null) {
            return '';
        }
        
        // Trim whitespace, convert to lowercase, remove all non-alphanumeric characters
        return Str::of((string) $value)
            ->trim()
            ->lower()
            ->replaceMatches('/[^a-z0-9]/', '')
            ->__toString();
    }

    private function buildHeaderIndex(array $headerRow): array
    {
        $headerIndex = [];
        foreach ($headerRow as $index => $normalizedHeader) {
            if ($normalizedHeader === '') {
                continue;
            }
            $headerIndex[$normalizedHeader] = $index;
        }

        return $headerIndex;
    }

    private function resolveHeaderIndex(array $rows): array
    {
        $required = $this->requiredActivityColumns();
        $lastMissing = null;
        $rowsCopy = $rows; // Keep copy for iteration

        foreach ($rowsCopy as $rowIndex => $potentialHeader) {
            // Skip empty rows
            if ($this->isRowEmpty($potentialHeader)) {
                continue;
            }

            $normalized = array_map(fn ($value) => $this->normalizeImportHeader($value), $potentialHeader);
            $headerIndex = $this->buildHeaderIndex($normalized);

            // Skip if no headers found after normalization
            if (empty($headerIndex)) {
                continue;
            }

            $allPresent = true;
            foreach ($required as $columnKey => $columnLabel) {
                if (!array_key_exists($columnLabel, $headerIndex)) {
                    $lastMissing = $columnKey;
                    $allPresent = false;
                    break;
                }
            }

            if ($allPresent) {
                // Remove all rows up to and including the header row
                // $rowIndex is the index in $rowsCopy, which is the same as $rows
                $remainingRows = array_slice($rows, $rowIndex + 1);
                return [$headerIndex, $remainingRows, null];
            }
        }

        return [null, $rows, $lastMissing];
    }

    private function fallbackHeaderIndex(array $rows): ?array
    {
        while (!empty($rows) && $this->isRowEmpty($rows[0])) {
            array_shift($rows);
        }

        if (empty($rows)) {
            return null;
        }

        $columnOrder = [
            'date',
            'assetdescription',
            'firsttripstarttime',
            'lasttripendtime',
            null, // Driving Time vs Standing Time (ignored)
            'drivingtimehhmmss',
            'standingtimehhmmss',
            'durationhhmmss',
            'idletimehhmmss',
        ];

        $headerIndex = [];
        foreach ($columnOrder as $position => $label) {
            if (!$label) {
                continue;
            }
            $headerIndex[$label] = $position;
        }

        return [$headerIndex, $rows];
    }

    private function requiredActivityColumns(): array
    {
        return [
            'date' => 'date',
            'chauffeur' => 'chauffeur',
            'first_trip_start_time' => 'premireheurededbutdutrajet', // "Première heure de début du trajet" → "premireheurededbutdutrajet"
            'last_trip_end_time' => 'heuredefinduderniertrajet', // "Heure de fin du dernier trajet" → "heuredefinduderniertrajet"
            'driving_time' => 'tempsdeconduitehhmmss', // "Temps de conduite (hh:mm:ss)" → "tempsdeconduitehhmmss"
            'rest_time' => 'tempsdattentehhmmss', // "Temps d'attente (hh:mm:ss)" → "tempsdattentehhmmss"
        ];
    }

    private function mapActivityRow(array $row, array $headerIndex): array
    {
        $map = $this->requiredActivityColumns();
        
        // Add optional columns
        $map['work_time'] = 'durehhmmss'; // "durée (hh:mm:ss)" → "durehhmmss" (é removed)
        $map['rest_daily'] = 'durederalentihhmmss'; // "Durée de ralenti (hh:mm:ss)" → "durederalentihhmmss"
        
        $mapped = [];

        foreach ($map as $key => $normalizedColumn) {
            $columnPosition = $headerIndex[$normalizedColumn] ?? null;
            if ($columnPosition !== null && isset($row[$columnPosition])) {
                $value = $row[$columnPosition];
                // Convert to string and trim if it's not null
                $mapped[$key] = $value !== null ? trim((string) $value) : null;
            } else {
                $mapped[$key] = null;
            }
        }

        return $mapped;
    }

    private function transformActivityRow(array $row, array $driverLookup, int $rowNumber): array
    {
        $date = $this->parseImportDate($row['date'] ?? null);
        if (!$date) {
            $dateValue = $row['date'] ?? 'empty';
            throw new \InvalidArgumentException("Invalid date value: '{$dateValue}'. Expected format: dd/mm/yyyy, yyyy-mm-dd, or Excel date number.");
        }

        // Get driver name directly from 'Chauffeur' column
        $driverName = trim((string) ($row['chauffeur'] ?? ''));
        if ($driverName === '' || $driverName === null) {
            throw new \InvalidArgumentException('Driver name (Chauffeur column) is required.');
        }

        // Try multiple variations of the driver name for matching (using lowercase normalized names)
        $driverKeys = [
            $this->normalizeDriverName($driverName),
        ];

        // Try reversed name order
        $nameParts = preg_split('/\s+/', trim($driverName), 2);
        if (count($nameParts) === 2) {
            $driverKeys[] = $this->normalizeDriverName($nameParts[1] . ' ' . $nameParts[0]);
        }

        // Try partial matching as fallback
        $driver = null;
        foreach ($driverKeys as $key) {
            if ($key !== '' && isset($driverLookup[$key])) {
                $driver = $driverLookup[$key];
                break;
            }
        }

        // If still not found, try fuzzy matching (contains check and word matching)
        if (!$driver) {
            $normalizedSearch = $this->normalizeDriverName($driverName);
            $searchWords = array_filter(explode(' ', $normalizedSearch), fn($w) => strlen($w) >= 2);
            
            $bestMatch = null;
            $bestScore = 0;
            
            foreach ($driverLookup as $lookupKey => $lookupDriver) {
                $normalizedLookup = $this->normalizeDriverName($lookupDriver['original_name']);
                $lookupWords = array_filter(explode(' ', $normalizedLookup), fn($w) => strlen($w) >= 2);
                
                // Count matching words
                $commonWords = array_intersect($searchWords, $lookupWords);
                $matchScore = count($commonWords);
                
                // Also check if one name contains the other (for partial matches)
                if (str_contains($normalizedSearch, $normalizedLookup) || str_contains($normalizedLookup, $normalizedSearch)) {
                    $matchScore += 2; // Boost score for substring matches
                }
                
                // Check if last names match (usually the most important part)
                $searchLast = end($searchWords);
                $lookupLast = end($lookupWords);
                if ($searchLast && $lookupLast && $searchLast === $lookupLast) {
                    $matchScore += 3; // Strong boost for matching last name
                }
                
                if ($matchScore > $bestScore && $matchScore >= 1) {
                    $bestScore = $matchScore;
                    $bestMatch = $lookupDriver;
                }
            }
            
            if ($bestMatch) {
                $driver = $bestMatch;
            }
        }

        if (!$driver) {
            // Log available driver names for debugging (first 10 only to avoid spam)
            $availableNames = array_slice(array_map(fn($d) => $d['original_name'], $driverLookup), 0, 10);
            Log::warning('Driver not found during import', [
                'searched_name' => $driverName,
                'normalized_search' => $this->normalizeDriverName($driverName),
                'available_drivers_sample' => $availableNames,
                'total_drivers' => count($driverLookup),
            ]);
            
            $sampleDrivers = implode(', ', array_slice($availableNames, 0, 5));
            throw new \InvalidArgumentException("Driver '{$driverName}' not found in database. Sample drivers: {$sampleDrivers}...");
        }

        // Get time values from French headers
        $startTime = $this->normalizeImportTime($row['first_trip_start_time'] ?? null);
        $endTime = $this->normalizeImportTime($row['last_trip_end_time'] ?? null);

        if (!$startTime) {
            throw new \InvalidArgumentException("Start time (Première heure de début du trajet) is required. Value: " . ($row['first_trip_start_time'] ?? 'empty'));
        }
        
        if (!$endTime) {
            throw new \InvalidArgumentException("End time (Heure de fin du dernier trajet) is required. Value: " . ($row['last_trip_end_time'] ?? 'empty'));
        }

        $drivingTime = $this->normalizeImportTime($row['driving_time'] ?? null, allowNull: true);
        $restTime = $this->normalizeImportTime($row['rest_time'] ?? null, allowNull: true);
        $restDaily = $this->normalizeImportTime($row['rest_daily'] ?? null, allowNull: true);

        // Try to get work_time from "durée" column, otherwise calculate as sum of driving_time + rest_time
        $workTime = $this->normalizeImportTime($row['work_time'] ?? null, allowNull: true);
        if (!$workTime) {
            // Calculate work_time as sum of driving_time + rest_time if not provided
            $workTime = $this->addTimeValues($drivingTime, $restTime);
        }

        // Build asset_description: driver_name - vehicle_license_plate (or just driver_name if no vehicle)
        $vehicleLicensePlate = $driver['vehicle_license_plate'] ?? null;
        $assetDescription = $vehicleLicensePlate 
            ? $driver['original_name'] . ' - ' . $vehicleLicensePlate
            : $driver['original_name'];

        return [
            'driver_id' => $driver['id'],
            'activity_date' => $date->toDateString(),
            'flotte' => $driver['flotte'],
            'asset_description' => $assetDescription,
            'driver_name' => $driver['original_name'],
            'start_time' => $startTime,
            'end_time' => $endTime,
            'work_time' => $workTime,
            'driving_time' => $drivingTime,
            'rest_time' => $restTime,
            'rest_daily' => $restDaily,
            'raison' => null,
            'start_location' => null,
            'overnight_location' => null,
            'created_at' => now(),
            'updated_at' => now(),
        ];
    }

    private function parseImportDate($value): ?Carbon
    {
        if ($value === null || $value === '') {
            return null;
        }

        // Handle Excel numeric date format (serial number)
        if (is_numeric($value)) {
            try {
                // Excel dates are serial numbers where 1 = 1900-01-01
                // But PhpSpreadsheet handles this correctly
                $dateTime = ExcelDate::excelToDateTimeObject($value);
                return Carbon::instance($dateTime);
            } catch (\Throwable $e) {
                // Try as Unix timestamp if Excel date conversion fails
                try {
                    $timestamp = (int) $value;
                    if ($timestamp > 0 && $timestamp < 2147483647) { // Valid Unix timestamp range
                        return Carbon::createFromTimestamp($timestamp);
                    }
                } catch (\Throwable $e2) {
                    return null;
                }
                return null;
            }
        }

        $value = trim((string) $value);
        
        // Try Carbon's flexible parser first (handles many formats automatically)
        try {
            $date = Carbon::parse($value);
            if ($date && $date->year > 1900 && $date->year < 2100) {
                return $date;
            }
        } catch (\Throwable $e) {
            // Continue to try specific formats
        }

        // Try specific date formats (including single digit day/month)
        // Note: 'd' = day with leading zero, 'j' = day without leading zero
        //       'm' = month with leading zero, 'n' = month without leading zero
        // Priority: Most common formats first, especially d/n/Y for "12/3/2025" format
        $formats = [
            // Most common: d/n/Y handles "12/3/2025" (day with zero, month without zero)
            'd/n/Y', 'd/n/y', 'd-n-Y', 'd-n-y',
            // Standard formats with leading zeros
            'd/m/Y', 'd-m-Y', 'Y-m-d', 'm/d/Y',
            'd/m/y', 'd-m-y', 'y-m-d', 'm/d/y',
            // Single digit day (j = day without leading zero)
            'j/m/Y', 'j-m-Y', 'j/m/y', 'j-m-y',
            'j/n/Y', 'j-n-Y', 'j/n/y', 'j-n-y',
            // Dot separator
            'd.m.Y', 'd.m.y', 'j.m.Y', 'j.m.y', 'd.n.Y', 'd.n.y', 'j.n.Y', 'j.n.y',
            // Slash with different orders
            'Y/m/d', 'y/m/d', 'Y/m/j', 'y/m/j',
            // Text formats
            'd M Y', 'd M y', 'M d Y', 'M d y',
            'd F Y', 'd F y', 'F d Y', 'F d y',
            'j M Y', 'j M y', 'M j Y', 'M j y',
        ];
        
        foreach ($formats as $format) {
            try {
                $date = Carbon::createFromFormat($format, $value);
                if ($date !== false && $date->year > 1900 && $date->year < 2100) {
                    return $date;
                }
            } catch (\Throwable $e) {
                continue;
            }
        }

        // Last attempt: try to parse as natural language date
        try {
            $date = Carbon::parse($value);
            if ($date && $date->year > 1900 && $date->year < 2100) {
                return $date;
            }
        } catch (\Throwable $e) {
            // Ignore and return null
        }

        return null;
    }

    private function normalizeImportTime($value, bool $allowNull = false): ?string
    {
        if ($value === null || $value === '') {
            return $allowNull ? null : null;
        }

        if (is_numeric($value)) {
            $floatValue = (float) $value;
            $fraction = $floatValue - floor($floatValue);
            if ($fraction < 0) {
                $fraction = 0;
            }
            $totalSeconds = (int) round($fraction * 86400);
            $hours = intdiv($totalSeconds, 3600);
            $minutes = intdiv($totalSeconds % 3600, 60);
            $seconds = $totalSeconds % 60;

            return sprintf('%02d:%02d:%02d', $hours, $minutes, $seconds);
        }

        $value = trim((string) $value);
        if (preg_match('/^\d{1,2}:\d{2}(?::\d{2})?$/', $value)) {
            $parts = explode(':', $value);
            $hours = (int) ($parts[0] ?? 0);
            $minutes = (int) ($parts[1] ?? 0);
            $seconds = (int) ($parts[2] ?? 0);

            return sprintf('%02d:%02d:%02d', $hours, $minutes, $seconds);
        }

        return $allowNull ? null : null;
    }

    private function addTimeValues(?string $time1, ?string $time2): ?string
    {
        // If both are null, return null
        if ($time1 === null && $time2 === null) {
            return null;
        }

        // If one is null, return the non-null value
        if ($time1 === null) {
            return $time2;
        }
        if ($time2 === null) {
            return $time1;
        }

        // Parse both times to seconds
        $seconds1 = $this->timeToSeconds($time1);
        $seconds2 = $this->timeToSeconds($time2);

        if ($seconds1 === null || $seconds2 === null) {
            return null;
        }

        // Add seconds together
        $totalSeconds = $seconds1 + $seconds2;

        // Convert back to HH:MM:SS format
        $hours = intdiv($totalSeconds, 3600);
        $minutes = intdiv($totalSeconds % 3600, 60);
        $seconds = $totalSeconds % 60;

        return sprintf('%02d:%02d:%02d', $hours, $minutes, $seconds);
    }

    private function timeToSeconds(?string $time): ?int
    {
        if ($time === null || $time === '') {
            return null;
        }

        // Handle HH:MM:SS or HH:MM format
        $parts = explode(':', trim($time));
        if (count($parts) < 2) {
            return null;
        }

        $hours = (int) ($parts[0] ?? 0);
        $minutes = (int) ($parts[1] ?? 0);
        $seconds = (int) ($parts[2] ?? 0);

        return ($hours * 3600) + ($minutes * 60) + $seconds;
    }

    private function extractVehicleAndDriver(string $assetDescription): array
    {
        if (!str_contains($assetDescription, '//')) {
            return [trim($assetDescription), null];
        }

        [$vehicle, $driver] = array_map('trim', explode('//', $assetDescription, 2));

        return [$vehicle, $driver ?: null];
    }

    private function normalizeDriverName(?string $name): string
    {
        if (empty($name)) {
            return '';
        }

        // Remove accents/diacritics, lowercase, trim, normalize spaces
        $normalized = Str::of((string) $name)
            ->lower()
            ->trim()
            ->replace(['à', 'á', 'â', 'ã', 'ä', 'å'], 'a')
            ->replace(['è', 'é', 'ê', 'ë'], 'e')
            ->replace(['ì', 'í', 'î', 'ï'], 'i')
            ->replace(['ò', 'ó', 'ô', 'õ', 'ö'], 'o')
            ->replace(['ù', 'ú', 'û', 'ü'], 'u')
            ->replace(['ç'], 'c')
            ->replace(['ñ'], 'n')
            ->replace(['ý', 'ÿ'], 'y')
            ->replace(['ï'], 'i')
            ->replaceMatches('/\s+/', ' ') // Normalize multiple spaces to single space
            ->trim();

        return $normalized->__toString();
    }

    private function buildDriverLookup(): array
    {
        $drivers = Driver::with(['flotte:id,name', 'assignedVehicle:id,license_plate'])->get();
        $lookup = [];

        foreach ($drivers as $driver) {
            $fullName = trim((string) ($driver->full_name ?? ''));
            if ($fullName === '') {
                continue;
            }

            // Create multiple keys for flexible matching
            $keys = [
                $this->normalizeDriverName($fullName),
            ];

            // Try reversed name order (Last First -> First Last)
            $nameParts = preg_split('/\s+/', $fullName, 2);
            if (count($nameParts) === 2) {
                $keys[] = $this->normalizeDriverName($nameParts[1] . ' ' . $nameParts[0]);
            }

            // Try just first name + last name (in case of middle names)
            if (count($nameParts) > 2) {
                $keys[] = $this->normalizeDriverName($nameParts[0] . ' ' . end($nameParts));
            }

            // Store driver info under all possible keys
            $driverInfo = [
                'id' => $driver->id,
                'flotte' => $driver->flotte->name ?? null,
                'original_name' => $fullName,
                'vehicle_license_plate' => $driver->assignedVehicle->license_plate ?? null,
            ];

            foreach ($keys as $key) {
                if ($key !== '') {
                    // If key already exists, prefer the one with more complete name
                    if (!isset($lookup[$key]) || strlen($fullName) > strlen($lookup[$key]['original_name'])) {
                        $lookup[$key] = $driverInfo;
                    }
                }
            }
        }

        return $lookup;
    }

    private function isRowEmpty(array $row): bool
    {
        foreach ($row as $value) {
            if ($value !== null && trim((string) $value) !== '') {
                return false;
            }
        }

        return true;
    }

    private function driversQuery(?int $flotteId = null)
    {
        $query = Driver::query()
            ->with(['assignedVehicle', 'flotte']);

        if ($flotteId) {
            $query->where('flotte_id', $flotteId);
        }

        return $query;
    }

    private function getNormalizedStatus($driver): ?string
    {
        $status = data_get($driver, 'status') ?? data_get($driver, 'statu') ?? data_get($driver, 'state');
        return $status ? strtolower(trim((string) $status)) : null;
    }

    private function isDriverActive($driver): bool
    {
        $value = $this->getNormalizedStatus($driver);
        if ($value === null) {
            return false;
        }
        $activeValues = ['active','actif','enabled','enable','1','true','yes'];
        return in_array($value, $activeValues, true);
    }

    private function isDriverInactive($driver): bool
    {
        $value = $this->getNormalizedStatus($driver);
        if ($value === null) {
            return false;
        }

        $inactiveValues = [
            'inactive','inactif',
        ];

        return in_array($value, $inactiveValues, true);
    }

    private function isDriverTerminated($driver): bool
    {
        if (!empty($driver->terminated_date)) {
            return true;
        }

        $value = $this->getNormalizedStatus($driver);
        if ($value === null) {
            return false;
        }

        $terminatedValues = [
            'terminated',
            'terminé',
            'termine',
            'terminated.',
            'terminated ',
        ];

        return in_array($value, $terminatedValues, true);
    }

    private function countTerminatedDrivers(?int $flotteId = null): int
    {
        return Driver::query()
            ->when($flotteId, fn($query) => $query->where('flotte_id', $flotteId))
            ->get()
            ->filter(fn($driver) => $this->isDriverTerminated($driver))
            ->count();
    }

    private function buildDriverViolationsQuery(Driver $driver, array $filters = [])
    {
        $query = DriverViolation::query()
            ->with(['violationType', 'vehicle'])
            ->where('driver_id', $driver->id)
            ->latest('violation_date');

        if (!empty($filters['violation_type_id'])) {
            $query->where('violation_type_id', $filters['violation_type_id']);
        }

        if (!empty($filters['status'])) {
            $query->where('status', $filters['status']);
        }

        if (!empty($filters['date_from'])) {
            $query->whereDate('violation_date', '>=', $filters['date_from']);
        }

        if (!empty($filters['date_to'])) {
            $query->whereDate('violation_date', '<=', $filters['date_to']);
        }

        return $query;
    }

    private function extractViolationFilters(Request $request): array
    {
        $defaultFrom = Carbon::now()->startOfYear()->format('Y-m-d');
        $defaultTo = Carbon::now()->endOfYear()->format('Y-m-d');

        return [
            'violation_type_id' => $request->get('violation_type_id'),
            'status' => $request->get('status'),
            'date_from' => $request->get('date_from', $defaultFrom),
            'date_to' => $request->get('date_to', $defaultTo),
        ];
    }

    /**
     * Show the form for editing the specified driver.
     */
    public function edit(Driver $driver)
    {
        try {
            $driver->load(['assignedVehicle', 'flotte']);

            $vehicles = $this->getAvailableVehicles();
            if ($driver->assigned_vehicle_id && !$vehicles->contains('id', $driver->assigned_vehicle_id)) {
                if ($currentVehicle = Vehicle::find($driver->assigned_vehicle_id)) {
                    $vehicles->prepend($currentVehicle);
                }
            }

            $flottes = Flotte::orderBy('name')->get();

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

            // Certificates are stored on the public disk (default storage folder)
            if (!Storage::disk('public')->exists($path)) {
                Log::warning('Certificate file not found on any configured disk', [
                    'driver_formation_id' => $driverFormation->id,
                    'path' => $path,
                ]);

                return back()->with('error', __('messages.file_not_found') ?? 'Fichier introuvable.');
            }

            $fileName = basename($path);

            /** @var \Illuminate\Filesystem\FilesystemAdapter $storage */
            $storage = Storage::disk('public');

            return $storage->download($path, $fileName);
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
            unset($validated['documents'], $validated['profile_photo'], $validated['remove_photo']);

            // Capture requested status once, before we possibly override it based on vehicle assignment.
            $requestedStatus = isset($validated['status'])
                ? strtolower(trim((string) $validated['status']))
                : null;

            $existingPhotoPath = $driver->profile_photo_path;

            if ($request->hasFile('profile_photo')) {
                if ($existingPhotoPath && Storage::disk('public')->exists($existingPhotoPath)) {
                    Storage::disk('public')->delete($existingPhotoPath);
                }
                $validated['profile_photo_path'] = $request->file('profile_photo')->store('profiles/drivers', 'public');
            } elseif ($request->boolean('remove_photo')) {
                if ($existingPhotoPath && Storage::disk('public')->exists($existingPhotoPath)) {
                    Storage::disk('public')->delete($existingPhotoPath);
                }
                $validated['profile_photo_path'] = null;
            } elseif ($existingPhotoPath) {
                $validated['profile_photo_path'] = $existingPhotoPath;
            }

            $originalVehicleId = $driver->assigned_vehicle_id;
            $newVehicleId = $validated['assigned_vehicle_id'] ?? null;
            $newVehicleId = $newVehicleId ?: null;
            $validated['assigned_vehicle_id'] = $newVehicleId;

            // Prevent setting driver to inactive while they still have a vehicle
            // in this request. We only allow inactivation if the driver is
            // being unassigned from any vehicle at the same time.
            if (
                $originalVehicleId &&                                         // driver currently has a vehicle
                $this->isDriverActive($driver) &&                             // driver is currently active
                $requestedStatus !== null &&
                in_array($requestedStatus, ['inactive', 'inactif'], true) && // user requested inactive
                $newVehicleId !== null                                        // still assigned to a vehicle in this request
            ) {
                return back()
                    ->withInput()
                    ->with('error', 'Driver is assigned to a vehicle. Please unassign the driver from the vehicle before setting them inactive.');
            }

            $vehicleAssignmentChanged = $originalVehicleId !== $newVehicleId;

            if ($vehicleAssignmentChanged) {
                if ($newVehicleId && $this->isVehicleAssigned($newVehicleId, $driver->id)) {
                    return back()
                        ->with('error', __('messages.vehicle_already_assigned'))
                        ->withInput();
                }

                $validated['status'] = $newVehicleId ? 'active' : 'inactive';
            }

            $documentsPayload = $this->prepareDocumentUpdatePayload($request, $driver);
            $validated['documents'] = $documentsPayload['final'];

            $driver->update($validated);

            if ($vehicleAssignmentChanged) {
                if ($originalVehicleId) {
                    $this->releaseVehicle($originalVehicleId);
                }

                if ($newVehicleId) {
                    $this->markVehicleAssigned($newVehicleId, $driver);
                }
            } elseif ($driver->assigned_vehicle_id) {
                $this->syncVehicleFlotte($driver->assigned_vehicle_id, $driver->flotte_id);
            }

            if (!empty($documentsPayload['removed'])) {
                $this->removeDocumentFiles($documentsPayload['removed']);
            }
            
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
            $request->validate([
                'documents' => 'required|array',
                'documents.*' => 'file|max:10240', // 10MB max per file
            ]);

            $files = $request->file('documents', []);
            $documents = $this->storeUploadedDocuments($files, $driver);
            $driver->update(['documents' => $documents]);

            Log::info('Driver documents uploaded', [
                'driver_id' => $driver->id,
                'files_count' => is_array($files) ? count($files) : 0,
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
     * Get vehicles that are not currently assigned to a driver.
     */
    private function getAvailableVehicles()
    {
        return Vehicle::whereDoesntHave('driver')
            ->orderBy('license_plate')
            ->get();
    }

    /**
     * Check whether a vehicle is already assigned to a driver.
     */
    private function isVehicleAssigned(int $vehicleId, ?int $ignoreDriverId = null): bool
    {
        $query = Driver::where('assigned_vehicle_id', $vehicleId);

        if ($ignoreDriverId) {
            $query->where('id', '!=', $ignoreDriverId);
        }

        return $query->exists();
    }

    /**
     * Mark vehicle as assigned to the provided driver.
     */
    private function markVehicleAssigned(int $vehicleId, Driver $driver): void
    {
        $vehicle = Vehicle::find($vehicleId);
        if (!$vehicle) {
            return;
        }

        $vehicle->status = 'active';
        $vehicle->flotte_id = $driver->flotte_id;
        $vehicle->save();
    }

    /**
     * Release vehicle assignment and mark it inactive.
     */
    private function releaseVehicle(int $vehicleId): void
    {
        $vehicle = Vehicle::find($vehicleId);
        if (!$vehicle) {
            return;
        }

        $vehicle->status = 'inactive';
        $vehicle->flotte_id = null;
        $vehicle->save();
    }

    /**
     * Synchronize the vehicle flotte with the driver's flotte.
     */
    private function syncVehicleFlotte(int $vehicleId, ?int $flotteId): void
    {
        $vehicle = Vehicle::find($vehicleId);
        if (!$vehicle) {
            return;
        }

        $vehicle->flotte_id = $flotteId;
        $vehicle->save();
    }

    /**
     * Store uploaded documents and merge them with existing ones.
     */
    private function storeUploadedDocuments(?array $files, Driver $driver, ?array $baseDocuments = null): array
    {
        $documents = $baseDocuments ?? ($driver->documents ?? []);
        if (!is_array($documents)) {
            $documents = [];
        }

        if (empty($files)) {
            return $documents;
        }

        foreach ($files as $file) {
            if (!$file) {
                continue;
            }

            $originalName = pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME);
            $extension = strtolower($file->getClientOriginalExtension());
            $slug = Str::slug($originalName) ?: 'document';
            $timestamp = now()->format('YmdHis');
            $filename = "{$slug}_{$timestamp}." . $extension;
            $path = $file->storeAs(
                "drivers/documents/{$driver->id}",
                $filename,
                'public'
            );

            $documents[] = [
                'name' => $file->getClientOriginalName(),
                'original_name' => $file->getClientOriginalName(),
                'path' => $path,
                'uploaded_at' => now()->toDateTimeString(),
            ];
        }

        return $documents;
    }

    /**
     * Prepare document payload for driver update.
     */
    private function prepareDocumentUpdatePayload(Request $request, Driver $driver): array
    {
        $currentDocuments = $driver->documents ?? [];
        if (!is_array($currentDocuments)) {
            $currentDocuments = [];
        }

        $existingDocuments = $this->decodeDocumentsInput($request->input('existing_documents', []));
        if (empty($existingDocuments)) {
            $existingDocuments = $currentDocuments;
        }

        $removedDocuments = $this->decodeDocumentsInput($request->input('removed_documents', []));
        if (!empty($removedDocuments)) {
            $removedPaths = collect($removedDocuments)
                ->pluck('path')
                ->filter()
                ->unique()
                ->values()
                ->all();

            if (!empty($removedPaths)) {
                $existingDocuments = array_values(array_filter($existingDocuments, function ($document) use ($removedPaths) {
                    $path = $document['path'] ?? null;
                    return $path ? !in_array($path, $removedPaths, true) : true;
                }));
            }
        }

        $files = $request->file('documents', []);
        $finalDocuments = $this->storeUploadedDocuments($files, $driver, $existingDocuments);

        return [
            'final' => $finalDocuments,
            'removed' => $removedDocuments,
        ];
    }

    /**
     * Decode incoming document payloads (JSON strings or arrays).
     */
    private function decodeDocumentsInput($input): array
    {
        $items = is_array($input) ? $input : [];
        $documents = [];

        foreach ($items as $value) {
            $document = null;

            if (is_array($value)) {
                $document = $value;
            } elseif (is_string($value) && $value !== '') {
                $decoded = json_decode($value, true);
                if (json_last_error() === JSON_ERROR_NONE) {
                    $document = $decoded;
                } else {
                    $document = ['path' => $value];
                }
            }

            if ($document) {
                $normalized = $this->normalizeDocumentStructure($document);
                if ($normalized) {
                    $documents[] = $normalized;
                }
            }
        }

        return $documents;
    }

    /**
     * Normalize a single document structure.
     */
    private function normalizeDocumentStructure(array $document): ?array
    {
        $path = $document['path'] ?? $document['file_path'] ?? null;
        if (!$path) {
            return null;
        }

        return [
            'name' => $document['name'] ?? basename($path),
            'path' => $path,
            'uploaded_at' => $document['uploaded_at'] ?? now()->toDateTimeString(),
        ];
    }

    /**
     * Delete physical files for removed documents.
     */
    private function removeDocumentFiles(array $documents): void
    {
        foreach ($documents as $document) {
            $path = $document['path'] ?? null;
            if (!$path) {
                continue;
            }

            try {
                if (Storage::disk('public')->exists($path)) {
                    Storage::disk('public')->delete($path);
                }
            } catch (\Throwable $e) {
                Log::warning('Failed to delete driver document file', [
                    'path' => $path,
                    'error' => $e->getMessage(),
                ]);
            }
        }
    }

    /**
     * Stream or download a driver document through the application.
     */
    public function showDocument(Request $request, Driver $driver, string $document)
    {
        try {
            $documentData = $this->findDriverDocumentByToken($driver, $document);
            if (!$documentData) {
                abort(404);
            }

            $path = $documentData['path'];
            /** @var \Illuminate\Filesystem\FilesystemAdapter $disk */
            $disk = Storage::disk('public');

            if (!$disk->exists($path)) {
                abort(404);
            }

            $filename = $documentData['name'] ?? basename($path);

            if ($request->boolean('download')) {
                return $disk->download($path, $filename);
            }

            return $disk->response($path);
        } catch (\Throwable $e) {
            Log::error('Failed to serve driver document', [
                'driver_id' => $driver->id,
                'document' => $document,
                'error' => $e->getMessage(),
            ]);

            return redirect()
                ->back()
                ->with('error', __('messages.error_downloading_file') ?? 'Unable to open the document.');
        }
    }

    /**
     * Serve the driver's profile photo through the application.
     */
    public function showProfilePhoto(Driver $driver)
    {
        try {
            $path = $driver->profile_photo_path;

            if (! $path) {
                abort(404);
            }

            $disk = Storage::disk('public');

            if (! $disk->exists($path)) {
                abort(404);
            }

            return response()->file($disk->path($path));
        } catch (\Throwable $e) {
            Log::error('Failed to serve driver profile photo', [
                'driver_id' => $driver->id,
                'error' => $e->getMessage(),
            ]);

            abort(500);
        }
    }

    private function findDriverDocumentByToken(Driver $driver, string $token): ?array
    {
        $path = $this->decodeDocumentToken($token);
        if (!$path) {
            return null;
        }

        $documents = $driver->documents ?? [];
        if (!is_array($documents)) {
            return null;
        }

        foreach ($documents as $document) {
            if (($document['path'] ?? null) === $path) {
                return $document;
            }
        }

        return null;
    }

    private function decodeDocumentToken(string $token): ?string
    {
        $token = strtr($token, '-_', '+/');
        $padding = strlen($token) % 4;
        if ($padding) {
            $token .= str_repeat('=', 4 - $padding);
        }

        $decoded = base64_decode($token, true);

        return is_string($decoded) ? $decoded : null;
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
            if (isset($document['path']) && Storage::disk('public')->exists($document['path'])) {
                Storage::disk('public')->delete($document['path']);
            }

            // Remove document from array
            unset($documents[$index]);

            // Save updated documents array
            $driver->documents = array_values($documents); // Re-index array
            $driver->save();

            return back()->with('success', __('messages.document_deleted_successfully') ?? 'Document deleted successfully.');
        } catch (\Throwable $e) {
            Log::error('Failed to delete driver document', [
                'driver_id' => $driver->id,
                'index' => $index,
                'error' => $e->getMessage(),
            ]);

            return back()->with('error', __('messages.error_deleting_document') ?? 'Error deleting document.');
        }
    }

    /**
     * Calculate time difference between end_time of last activity and start_time of new activity
     * Handles cases where activities span across days
     */
    private function calculateTimeDifference(string $endTime, string $startTime, $endDate, $startDate): ?string
    {
        try {
            // Ensure dates are in Y-m-d format
            $endDateStr = is_string($endDate) ? $endDate : (is_object($endDate) && method_exists($endDate, 'format') ? $endDate->format('Y-m-d') : (string)$endDate);
            $startDateStr = is_string($startDate) ? $startDate : (is_object($startDate) && method_exists($startDate, 'format') ? $startDate->format('Y-m-d') : (string)$startDate);
            
            // Ensure times are in H:i:s format
            $endTimeStr = is_string($endTime) ? $endTime : (is_object($endTime) && method_exists($endTime, 'format') ? $endTime->format('H:i:s') : (string)$endTime);
            $startTimeStr = is_string($startTime) ? $startTime : (is_object($startTime) && method_exists($startTime, 'format') ? $startTime->format('H:i:s') : (string)$startTime);

            // Parse the times with dates
            $endDateTime = Carbon::parse($endDateStr . ' ' . $endTimeStr);
            $startDateTime = Carbon::parse($startDateStr . ' ' . $startTimeStr);

            // If start is before or equal to end on the same day, it means we've crossed midnight
            // Add 24 hours to start time
            if ($startDateTime->lte($endDateTime)) {
                $startDateTime->addDay();
            }

            // Calculate the difference in seconds
            $diffInSeconds = $startDateTime->diffInSeconds($endDateTime);

            // Convert to HH:MM:SS format
            $hours = floor($diffInSeconds / 3600);
            $minutes = floor(($diffInSeconds % 3600) / 60);
            $seconds = $diffInSeconds % 60;

            $result = sprintf('%02d:%02d:%02d', $hours, $minutes, $seconds);
            
            Log::info('Calculated rest_daily', [
                'end_date' => $endDateStr,
                'end_time' => $endTimeStr,
                'start_date' => $startDateStr,
                'start_time' => $startTimeStr,
                'rest_daily' => $result,
                'diff_seconds' => $diffInSeconds,
            ]);

            return $result;
        } catch (\Throwable $e) {
            Log::error('Failed to calculate rest_daily', [
                'end_time' => $endTime,
                'start_time' => $startTime,
                'end_date' => $endDate,
                'start_date' => $startDate,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);
            return null;
        }
    }
}
