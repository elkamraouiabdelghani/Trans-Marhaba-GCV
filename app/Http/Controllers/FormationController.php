<?php

namespace App\Http\Controllers;

use App\Http\Requests\FormationRequest;
use App\Models\Formation;
use App\Models\FormationCategory;
use App\Models\DriverFormation;
use App\Models\Flotte;
use App\Models\Driver;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Illuminate\Support\Facades\Log;
use Illuminate\Http\Response;
use Barryvdh\DomPDF\Facade\Pdf;
use Carbon\Carbon;

class FormationController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request): View
    {
        try {
            $categories = FormationCategory::orderBy('name')->get();

            // Get filter options
            $integratedDrivers = Driver::where('is_integrated', 1)
                ->orderBy('full_name')
                ->get();
            $flottes = Flotte::orderBy('name')->get();

        $currentYear = (int) now()->format('Y');

        // Get filter values from request
        $selectedDriver = $request->input('driver');
        $selectedFlotte = $request->input('flotte');
        $selectedStatus = $request->input('status');
        $selectedFormationCategory = $request->input('formation_category');
        $requestedYear = $request->input('year');
        $selectedYear = $requestedYear ?: $currentYear;
        $yearFilterApplied = $requestedYear !== null && $requestedYear !== '';

            // Get available years from DriverFormation records
            $doneYears = DriverFormation::whereNotNull('done_at')
                ->selectRaw('YEAR(done_at) as year')
                ->distinct()
                ->pluck('year');
            
            $plannedYears = DriverFormation::whereNotNull('planned_at')
                ->selectRaw('YEAR(planned_at) as year')
                ->distinct()
                ->pluck('year');
            
            // Get available years from formations realizing_date
            $realizingYears = Formation::whereNotNull('realizing_date')
                ->selectRaw('YEAR(realizing_date) as year')
                ->distinct()
                ->pluck('year');
            
        $years = $doneYears->merge($plannedYears)
                ->merge($realizingYears)
                ->unique()
                ->filter()
                ->sortDesc()
                ->values();
        if (!$years->contains($currentYear)) {
            $years->prepend($currentYear);
            $years = $years->unique()->sortDesc()->values();
        }

            // Auto-select flotte if driver is selected and has a flotte
            if ($selectedDriver && !$selectedFlotte) {
                $driver = \App\Models\Driver::find($selectedDriver);
                if ($driver && $driver->flotte_id) {
                    $selectedFlotte = $driver->flotte_id;
                }
            }

            // Determine if filters are applied
        $hasFilters = $selectedDriver || $selectedFlotte || $selectedStatus || $selectedFormationCategory || $yearFilterApplied;
            
            // Rule: If only status is selected, show nothing
        $onlyStatus = $selectedStatus && !$selectedDriver && !$selectedFlotte && !$selectedFormationCategory && !$yearFilterApplied;

        // Build base query for formations
        $formationsQuery = Formation::with(['category', 'flotte']);

        if ($selectedYear) {
            $formationsQuery->whereYear('realizing_date', $selectedYear);
        }

        // Yearly stats (before additional filters)
        $statsQuery = clone $formationsQuery;
        $totalYearFormations = (clone $statsQuery)->count();
        $realizedYearFormations = (clone $statsQuery)->where('status', 'realized')->count();
        $plannedYearFormations = (clone $statsQuery)->where('status', 'planned')->count();
        $realizedYearPercentage = $totalYearFormations > 0
            ? round(($realizedYearFormations / $totalYearFormations) * 100, 1)
            : 0;
        $yearlyStats = [
            'total' => $totalYearFormations,
            'planned' => $plannedYearFormations,
            'realized' => $realizedYearFormations,
            'percentage' => $realizedYearPercentage,
            'year' => $selectedYear,
        ];

            if ($onlyStatus) {
                // Show nothing if only status is selected
                $formationsQuery->whereRaw('1 = 0'); // Force empty result
            } else {

                // Filter by formation category (type)
                if ($selectedFormationCategory) {
                    $formationsQuery->where('formation_category_id', $selectedFormationCategory);
                }

                // Filter by realizing_date year if year is selected and no driver/flotte filters
                if ($selectedYear && !$selectedDriver && !$selectedFlotte) {
                    $formationsQuery->whereYear('realizing_date', $selectedYear);
                }

                // If driver is selected (alone or with others), show ALL formations
                // Drivers must do all formations, some are per flotte
                if ($selectedDriver) {
                    // Show all formations (no additional filtering needed)
                    // But if status or year is also selected, we need to filter
                    if ($selectedStatus || $selectedYear) {
                        $formationsQuery->where(function ($query) use ($selectedDriver, $selectedStatus, $selectedYear) {
                            // Filter by DriverFormation records
                            $query->whereHas('driverFormations', function ($q) use ($selectedDriver, $selectedStatus, $selectedYear) {
                                $q->where('driver_id', $selectedDriver);
                                if ($selectedStatus) {
                                    $q->where('status', $selectedStatus === 'realized' ? 'done' : 'planned');
                                }
                                if ($selectedYear) {
                                    if ($selectedStatus === 'realized') {
                                        $q->whereYear('done_at', $selectedYear);
                                    } elseif ($selectedStatus === 'planned') {
                                        $q->whereYear('planned_at', $selectedYear);
                                    } else {
                                        $q->where(function ($yearQuery) use ($selectedYear) {
                                            $yearQuery->whereYear('done_at', $selectedYear)
                                                      ->orWhereYear('planned_at', $selectedYear);
                                        });
                                    }
                                }
                            });
                            
                            // Also include formations filtered by realizing_date year
                            if ($selectedYear && !$selectedStatus) {
                                $query->orWhereYear('realizing_date', $selectedYear);
                            }
                        });
                    }
                } elseif ($selectedFlotte) {
                    // If only flotte is selected (or flotte + other filters), show formations for that flotte
                    // Use direct flotte_id relationship
                    $formationsQuery->where('flotte_id', $selectedFlotte);
                    
                    // If status or year is also selected, filter by DriverFormation records or realizing_date
                    if ($selectedStatus || $selectedYear) {
                        $formationsQuery->where(function ($query) use ($selectedStatus, $selectedYear) {
                            // Filter by DriverFormation records
                            $query->whereHas('driverFormations', function ($q) use ($selectedStatus, $selectedYear) {
                                if ($selectedStatus) {
                                    $q->where('status', $selectedStatus === 'realized' ? 'done' : 'planned');
                                }
                                if ($selectedYear) {
                                    if ($selectedStatus === 'realized') {
                                        $q->whereYear('done_at', $selectedYear);
                                    } elseif ($selectedStatus === 'planned') {
                                        $q->whereYear('planned_at', $selectedYear);
                                    } else {
                                        $q->where(function ($yearQuery) use ($selectedYear) {
                                            $yearQuery->whereYear('done_at', $selectedYear)
                                                      ->orWhereYear('planned_at', $selectedYear);
                                        });
                                    }
                                }
                                $q->whereHas('driver', function ($driverQuery) {
                                    $driverQuery->where('is_integrated', 1);
                                });
                            });
                            
                            // Also include formations filtered by realizing_date year
                            if ($selectedYear && !$selectedStatus) {
                                $query->orWhereYear('realizing_date', $selectedYear);
                            }
                        });
                    }
                }

                $shouldFilterByDriverFormations = ($selectedStatus || $selectedYear);

                if ($shouldFilterByDriverFormations) {
                    // Status or year with other filters (but not driver/flotte) - filter by status/year
                    $formationsQuery->where(function ($query) use ($selectedStatus, $selectedYear) {
                        // Filter by DriverFormation records
                        $query->whereHas('driverFormations', function ($q) use ($selectedStatus, $selectedYear) {
                            if ($selectedStatus) {
                                $q->where('status', $selectedStatus === 'realized' ? 'done' : 'planned');
                            }
                            if ($selectedYear) {
                                if ($selectedStatus === 'realized') {
                                    $q->whereYear('done_at', $selectedYear);
                                } elseif ($selectedStatus === 'planned') {
                                    $q->whereYear('planned_at', $selectedYear);
                                } else {
                                    $q->where(function ($yearQuery) use ($selectedYear) {
                                        $yearQuery->whereYear('done_at', $selectedYear)
                                                  ->orWhereYear('planned_at', $selectedYear);
                                    });
                                }
                            }
                            $q->whereHas('driver', function ($driverQuery) {
                                $driverQuery->where('is_integrated', 1);
                            });
                        });
                        
                        // Also include formations filtered by realizing_date year
                        if ($selectedYear && !$selectedStatus) {
                            $query->orWhereYear('realizing_date', $selectedYear);
                        }
                    });
                }
            }

            $formations = $formationsQuery->orderBy('name')->get();

            // Build graph data (always show when filters are applied, even with 0 counts)
            $graphData = null;
            if ($hasFilters && !$onlyStatus) {
                // Get all formations that should be displayed (for graph labels)
                $allFormationsForGraph = $formations->pluck('id')->toArray();
                
                // Build query for driver formations
                $query = \App\Models\DriverFormation::with(['driver', 'formation'])
                    ->whereHas('driver', function ($q) use ($selectedDriver, $selectedFlotte) {
                        $q->where('is_integrated', 1);
                        if ($selectedDriver) {
                            $q->where('id', $selectedDriver);
                        }
                        if ($selectedFlotte) {
                            $q->where('flotte_id', $selectedFlotte);
                        }
                    });

                if ($selectedStatus) {
                    $query->where('status', $selectedStatus === 'realized' ? 'done' : 'planned');
                }

                if ($selectedYear) {
                    if ($selectedStatus === 'realized') {
                        $query->whereYear('done_at', $selectedYear);
                    } elseif ($selectedStatus === 'planned') {
                        $query->whereYear('planned_at', $selectedYear);
                    } else {
                        $query->where(function ($yearQuery) use ($selectedYear) {
                            $yearQuery->whereYear('done_at', $selectedYear)
                                      ->orWhereYear('planned_at', $selectedYear);
                        });
                    }
                }

                if ($selectedFormationCategory) {
                    $query->whereHas('formation', function ($q) use ($selectedFormationCategory) {
                        $q->where('formation_category_id', $selectedFormationCategory);
                    });
                }

                // Get actual driver formation records
                $driverFormations = $query->get();

                // Get all drivers that match the filter criteria
                $driversQuery = \App\Models\Driver::where('is_integrated', 1);
                if ($selectedDriver) {
                    $driversQuery->where('id', $selectedDriver);
                }
                if ($selectedFlotte) {
                    $driversQuery->where('flotte_id', $selectedFlotte);
                }
                $relevantDrivers = $driversQuery->get();

                // Build graph data: for each driver, show all formations with their counts (0 if no record)
                $graphData = $relevantDrivers->map(function ($driver) use ($allFormationsForGraph, $driverFormations, $selectedStatus) {
                    $driverFormationsForDriver = $driverFormations->where('driver_id', $driver->id);
                    
                    $formationsData = Formation::whereIn('id', $allFormationsForGraph)
                        ->get()
                        ->map(function ($formation) use ($driverFormationsForDriver, $selectedStatus) {
                            $matchingFormations = $driverFormationsForDriver->where('formation_id', $formation->id);
                            
                            // If status filter is applied, only count matching status
                            if ($selectedStatus) {
                                $matchingFormations = $matchingFormations->filter(function ($df) use ($selectedStatus) {
                                    return $df->status === ($selectedStatus === 'realized' ? 'done' : 'planned');
                                });
                            }
                            
                            return [
                                'name' => $formation->name,
                                'count' => $matchingFormations->count(),
                            ];
                        })
                        ->values()
                        ->all();

                    return [
                        'driver' => $driver->full_name,
                        'formations' => $formationsData,
                    ];
                })
                ->values()
                ->all();
            }

            // Calculate stats
            $totalFormations = Formation::count();
            $totalIntegratedDrivers = Driver::where('is_integrated', 1)->count();
            $totalDriverFormations = DriverFormation::count();
            $realizedFormations = DriverFormation::where('status', 'done')->count();
            $percentageRealized = $totalDriverFormations > 0 
                ? round(($realizedFormations / $totalDriverFormations) * 100, 1) 
                : 0;

            return view('formations.index', compact(
                'formations',
                'categories',
                'integratedDrivers',
                'flottes',
                'selectedDriver',
                'selectedFlotte',
                'selectedStatus',
                'selectedFormationCategory',
                'selectedYear',
                'years',
                'graphData',
                'hasFilters',
                'yearlyStats'
            ));
        } catch (\Throwable $e) {
            Log::error('Failed to load formations', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            return view('formations.index', [
                'formations' => collect(),
                'categories' => collect(),
                'integratedDrivers' => collect(),
                'flottes' => collect(),
                'graphData' => null,
                'hasFilters' => false,
                'yearlyStats' => [
                    'total' => 0,
                    'planned' => 0,
                    'realized' => 0,
                    'percentage' => 0,
                    'year' => now()->year,
                ],
                'selectedYear' => now()->year,
                'years' => collect([now()->year]),
                'selectedDriver' => null,
                'selectedFlotte' => null,
                'selectedStatus' => null,
                'selectedFormationCategory' => null,
            ])->with('error', __('messages.formation_create_error'));
        }
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create(): View
    {
        $categories = FormationCategory::orderBy('name')->get();
        $flottes = \App\Models\Flotte::orderBy('name')->get();

        return view('formations.create', compact('categories', 'flottes'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(FormationRequest $request)
    {
        $validated = $request->validated();

        try {
            Formation::create($validated);
        } catch (\Throwable $e) {
            Log::error('Failed to create formation', [
                'payload' => $validated,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            return back()
                ->withInput()
                ->with('error', __('messages.formation_create_error') . ': ' . $e->getMessage());
        }

        return redirect()->route('formations.index')
            ->with('success', __('messages.formation_created'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Formation $formation): View
    {
        $categories = FormationCategory::orderBy('name')->get();
        $flottes = \App\Models\Flotte::orderBy('name')->get();

        return view('formations.edit', compact('formation', 'categories', 'flottes'));
    }

    /**
     * Display yearly planning overview.
     */
    public function planning(Request $request): View
    {
        $planningData = $this->getPlanningData($request->input('year'));

        return view('formations.planning', $planningData);
    }

    /**
     * Download yearly planning as PDF.
     */
    public function planningPdf(Request $request): Response
    {
        $planningData = $this->getPlanningData($request->input('year'));

        $pdf = Pdf::loadView('formations.planning_pdf', $planningData)
            ->setPaper('a4', 'landscape')
            ->setOption('isHtml5ParserEnabled', true)
            ->setOption('isRemoteEnabled', true);

        $filename = 'formation_planning_' . $planningData['selectedYear'] . '_' . now()->format('Ymd_His') . '.pdf';

        return $pdf->download($filename);
    }

    /**
     * Build planning data shared by HTML & PDF views.
     */
    protected function getPlanningData(?int $year = null): array
    {
        $currentYear = (int) now()->format('Y');
        $selectedYear = $year ? (int) $year : $currentYear;

        $availableYears = Formation::selectRaw('YEAR(COALESCE(realizing_date, created_at)) as year')
            ->distinct()
            ->pluck('year')
            ->filter()
            ->sortDesc()
            ->values();

        if ($availableYears->isEmpty()) {
            $availableYears = collect([$currentYear]);
        }

        if (!$availableYears->contains($selectedYear)) {
            $selectedYear = $availableYears->first();
        }

        $formations = Formation::with(['category'])
            ->where(function ($query) use ($selectedYear) {
                $query->whereYear('realizing_date', $selectedYear)
                    ->orWhere(function ($subQuery) use ($selectedYear) {
                        $subQuery->whereNull('realizing_date')
                            ->whereYear('created_at', $selectedYear);
                    });
            })
            ->get()
            ->map(function ($formation) {
                $dateSource = $formation->realizing_date
                    ? Carbon::parse($formation->realizing_date)
                    : $formation->created_at;
                $formation->month_index = $dateSource ? $dateSource->format('n') : '1';
                return $formation;
            });

        $formationsByMonth = $formations->groupBy('month_index');
        $totalFormations = $formations->count();
        $realizedFormations = $formations->where('status', 'realized')->count();
        $plannedFormations = $totalFormations - $realizedFormations;

        return [
            'formationsByMonth' => $formationsByMonth,
            'selectedYear' => $selectedYear,
            'availableYears' => $availableYears,
            'planningTotals' => [
                'total' => $totalFormations,
                'realized' => $realizedFormations,
                'planned' => $plannedFormations,
            ],
        ];
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(FormationRequest $request, Formation $formation)
    {
        $validated = $request->validated();

        try {
            $formation->update($validated);
        } catch (\Throwable $e) {
            Log::error('Failed to update formation', [
                'id' => $formation->id,
                'payload' => $validated,
                'error' => $e->getMessage(),
            ]);

            return back()
                ->withInput()
                ->with('error', __('messages.formation_update_error'));
        }

        return redirect()->route('formations.index')
            ->with('success', __('messages.formation_updated'));
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Formation $formation)
    {
        try {
            $formation->delete();
        } catch (\Throwable $e) {
            Log::error('Failed to delete formation', [
                'id' => $formation->id,
                'error' => $e->getMessage(),
            ]);

            return redirect()->route('formations.index')
                ->with('error', __('messages.formation_delete_error'));
        }

        return redirect()->route('formations.index')
            ->with('success', __('messages.formation_deleted'));
    }

    /**
     * Mark a formation as realized.
     */
    public function markAsRealized(Formation $formation)
    {
        try {
            $formation->update([
                'status' => 'realized',
            ]);
        } catch (\Throwable $e) {
            Log::error('Failed to mark formation as realized', [
                'id' => $formation->id,
                'error' => $e->getMessage(),
            ]);

            return redirect()->route('formations.index')
                ->with('error', __('messages.formation_mark_realized_error'));
        }

        return redirect()->route('formations.index')
            ->with('success', __('messages.formation_mark_realized_success'));
    }
}

