<?php

namespace App\Http\Controllers;

use App\Models\Formation;
use App\Models\FormationCategory;
use App\Models\DriverFormation;
use App\Models\Flotte;
use App\Models\Driver;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Illuminate\Support\Facades\Log;

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
            $integratedDrivers = \App\Models\Driver::where('is_integrated', 1)
                ->orderBy('full_name')
                ->get();
            $flottes = \App\Models\Flotte::orderBy('name')->get();

            // Get filter values from request
            $selectedDriver = $request->input('driver');
            $selectedFlotte = $request->input('flotte');
            $selectedStatus = $request->input('status');
            $selectedFormationCategory = $request->input('formation_category');
            $selectedYear = $request->input('year');

            // Get available years from DriverFormation records
            $doneYears = \App\Models\DriverFormation::whereNotNull('done_at')
                ->selectRaw('YEAR(done_at) as year')
                ->distinct()
                ->pluck('year');
            
            $plannedYears = \App\Models\DriverFormation::whereNotNull('planned_at')
                ->selectRaw('YEAR(planned_at) as year')
                ->distinct()
                ->pluck('year');
            
            $years = $doneYears->merge($plannedYears)
                ->unique()
                ->filter()
                ->sortDesc()
                ->values();

            // Auto-select flotte if driver is selected and has a flotte
            if ($selectedDriver && !$selectedFlotte) {
                $driver = \App\Models\Driver::find($selectedDriver);
                if ($driver && $driver->flotte_id) {
                    $selectedFlotte = $driver->flotte_id;
                }
            }

            // Determine if filters are applied
            $hasFilters = $selectedDriver || $selectedFlotte || $selectedStatus || $selectedFormationCategory || $selectedYear;
            
            // Rule: If only status is selected, show nothing
            $onlyStatus = $selectedStatus && !$selectedDriver && !$selectedFlotte && !$selectedFormationCategory;

            // Build base query for formations
            $formationsQuery = Formation::with(['category', 'flotte']);

            if ($onlyStatus) {
                // Show nothing if only status is selected
                $formationsQuery->whereRaw('1 = 0'); // Force empty result
            } else {
                // Filter by formation category (type)
                if ($selectedFormationCategory) {
                    $formationsQuery->where('formation_category_id', $selectedFormationCategory);
                }

                // If driver is selected (alone or with others), show ALL formations
                // Drivers must do all formations, some are per flotte
                if ($selectedDriver) {
                    // Show all formations (no additional filtering needed)
                    // But if status is also selected, we need to filter by DriverFormation records
                    if ($selectedStatus || $selectedYear) {
                        $formationsQuery->whereHas('driverFormations', function ($q) use ($selectedDriver, $selectedStatus, $selectedYear) {
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
                    }
                } elseif ($selectedFlotte) {
                    // If only flotte is selected (or flotte + other filters), show formations for that flotte
                    // Use direct flotte_id relationship
                    $formationsQuery->where('flotte_id', $selectedFlotte);
                    
                    // If status or year is also selected, filter by DriverFormation records
                    if ($selectedStatus || $selectedYear) {
                        $formationsQuery->whereHas('driverFormations', function ($q) use ($selectedStatus, $selectedYear) {
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
                    }
                } elseif ($selectedStatus || $selectedYear) {
                    // Status or year with other filters (but not driver/flotte) - filter by status/year
                    $formationsQuery->whereHas('driverFormations', function ($q) use ($selectedStatus, $selectedYear) {
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
                'totalFormations',
                'totalIntegratedDrivers',
                'percentageRealized'
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
                'totalFormations' => 0,
                'totalIntegratedDrivers' => 0,
                'percentageRealized' => 0,
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
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255|unique:formations,name',
            'code' => 'required|string|max:255|unique:formations,code',
            'planned_year' => 'nullable|integer|min:1900|max:2100',
            'description' => 'nullable|string',
            'formation_category_id' => 'nullable|exists:formation_categories,id',
            'flotte_id' => 'nullable|exists:flottes,id',
            'is_active' => 'sometimes|boolean',
            'obligatoire' => 'sometimes|boolean',
            'reference_value' => 'nullable|integer|min:1',
            'reference_unit' => 'nullable|in:months,years',
            'warning_alert_percent' => 'nullable|integer|min:0|max:100',
            'warning_alert_days' => 'nullable|integer|min:0',
            'critical_alert_percent' => 'nullable|integer|min:0|max:100',
            'critical_alert_days' => 'nullable|integer|min:0',
        ]);

        $validated['is_active'] = $request->has('is_active');
        $validated['obligatoire'] = $request->has('obligatoire');

        try {
            Formation::create($validated);
        } catch (\Throwable $e) {
            Log::error('Failed to create formation', [
                'payload' => $validated,
                'error' => $e->getMessage(),
            ]);

            return back()
                ->withInput()
                ->with('error', __('messages.formation_create_error'));
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
     * Update the specified resource in storage.
     */
    public function update(Request $request, Formation $formation)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255|unique:formations,name,' . $formation->id,
            'code' => 'required|string|max:255|unique:formations,code,' . $formation->id,
            'planned_year' => 'nullable|integer|min:1900|max:2100',
            'description' => 'nullable|string',
            'formation_category_id' => 'nullable|exists:formation_categories,id',
            'flotte_id' => 'nullable|exists:flottes,id',
            'is_active' => 'sometimes|boolean',
            'obligatoire' => 'sometimes|boolean',
            'reference_value' => 'nullable|integer|min:1',
            'reference_unit' => 'nullable|in:months,years',
            'warning_alert_percent' => 'nullable|integer|min:0|max:100',
            'warning_alert_days' => 'nullable|integer|min:0',
            'critical_alert_percent' => 'nullable|integer|min:0|max:100',
            'critical_alert_days' => 'nullable|integer|min:0',
        ]);

        $validated['is_active'] = $request->has('is_active');
        $validated['obligatoire'] = $request->has('obligatoire');

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
}

