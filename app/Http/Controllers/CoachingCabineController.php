<?php

namespace App\Http\Controllers;

use App\Models\CoachingSession;
use App\Models\Driver;
use App\Models\Flotte;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Throwable;

class CoachingCabineController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request): View|RedirectResponse
    {
        try {
            $query = CoachingSession::with(['driver', 'flotte']);

            // Apply filters
            if ($request->filled('driver_id')) {
                $query->where('driver_id', $request->driver_id);
            }

            if ($request->filled('flotte_id')) {
                $query->where('flotte_id', $request->flotte_id);
            }

            if ($request->filled('status')) {
                $query->where('status', $request->status);
            }

            if ($request->filled('date_from')) {
                $query->where('date', '>=', $request->date_from);
            }

            if ($request->filled('date_to')) {
                $query->where('date', '<=', $request->date_to);
            }

            $year = $request->filled('year') ? $request->year : date('Y');
            
            if ($request->filled('year')) {
                $query->whereYear('date', $request->year);
            }

            // Calculate stats for the selected year
            // Base query builder function
            $buildStatsQuery = function() use ($request, $year) {
                $statsQuery = CoachingSession::query();
                
                // Apply same filters as main query for stats
                if ($request->filled('driver_id')) {
                    $statsQuery->where('driver_id', $request->driver_id);
                }

                if ($request->filled('flotte_id')) {
                    $statsQuery->where('flotte_id', $request->flotte_id);
                }

                if ($request->filled('status')) {
                    $statsQuery->where('status', $request->status);
                }

                if ($request->filled('date_from')) {
                    $statsQuery->where('date', '>=', $request->date_from);
                }

                if ($request->filled('date_to')) {
                    $statsQuery->where('date', '<=', $request->date_to);
                }

                // Always filter by year for stats
                $statsQuery->whereYear('date', $year);
                
                return $statsQuery;
            };

            $total = $buildStatsQuery()->count();
            $planned = $buildStatsQuery()->where('status', 'planned')->count();
            $completed = $buildStatsQuery()->where('status', 'completed')->count();
            $completedPercentage = $total > 0 ? round(($completed / $total) * 100, 2) : 0;

            $sessions = $query->orderBy('date', 'desc')
                ->orderBy('created_at', 'desc')
                ->paginate(15);

            $drivers = Driver::orderBy('full_name')->get();
            $flottes = Flotte::orderBy('name')->get();

            return view('coaching_cabines.index', compact('sessions', 'drivers', 'flottes', 'total', 'planned', 'completed', 'completedPercentage', 'year'));
        } catch (Throwable $th) {
            report($th);
            return back()->with('error', __('messages.coaching_cabines_load_error') ?? 'Impossible de charger les sessions de coaching.');
        }
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create(): View|RedirectResponse
    {
        try {
            $drivers = Driver::orderBy('full_name')->get();
            $flottes = Flotte::orderBy('name')->get();

            return view('coaching_cabines.create', compact('drivers', 'flottes'));
        } catch (Throwable $th) {
            report($th);
            return back()->with('error', __('messages.coaching_cabines_create_form_error') ?? 'Impossible d\'afficher le formulaire de création.');
        }
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'driver_id' => ['required', 'exists:drivers,id'],
            'flotte_id' => ['nullable', 'exists:flottes,id'],
            'date' => ['required', 'date'],
            'date_fin' => ['required', 'date', 'after_or_equal:date'],
            'type' => ['required', 'in:initial,suivi,correctif'],
            'route_taken' => ['nullable', 'string', 'max:255'],
            'moniteur' => ['nullable', 'string', 'max:255'],
            'assessment' => ['nullable', 'string'],
            'status' => ['required', 'in:planned,in_progress,completed,cancelled'],
            'validity_days' => ['required', 'integer', 'min:1'],
            'next_planning_session' => ['nullable', 'date'],
            'score' => ['nullable', 'integer', 'min:0', 'max:100'],
            'notes' => ['nullable', 'string'],
        ]);

        try {
            // If driver doesn't have a flotte assigned, assign the selected flotte
            $driver = Driver::find($validated['driver_id']);
            if ($driver && !$driver->flotte_id && isset($validated['flotte_id']) && $validated['flotte_id']) {
                $driver->update(['flotte_id' => $validated['flotte_id']]);
            }

            CoachingSession::create($validated);

            return redirect()
                ->route('coaching-cabines.index')
                ->with('success', __('messages.coaching_cabines_created'));
        } catch (Throwable $th) {
            report($th);
            return back()->withInput()->with('error', __('messages.coaching_cabines_create_error'));
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(CoachingSession $coachingCabine): View|RedirectResponse
    {
        try {
            $coachingCabine->load(['driver', 'flotte']);

            return view('coaching_cabines.show', compact('coachingCabine'));
        } catch (Throwable $th) {
            report($th);
            return back()->with('error', __('messages.coaching_cabines_show_error') ?? 'Impossible de charger les détails de la session.');
        }
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(CoachingSession $coachingCabine): View|RedirectResponse
    {
        try {
            $drivers = Driver::orderBy('full_name')->get();
            $flottes = Flotte::orderBy('name')->get();

            return view('coaching_cabines.edit', compact('coachingCabine', 'drivers', 'flottes'));
        } catch (Throwable $th) {
            report($th);
            return back()->with('error', __('messages.coaching_cabines_edit_form_error') ?? 'Impossible d\'afficher le formulaire de modification.');
        }
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, CoachingSession $coachingCabine): RedirectResponse
    {
        $validated = $request->validate([
            'driver_id' => ['required', 'exists:drivers,id'],
            'flotte_id' => ['nullable', 'exists:flottes,id'],
            'date' => ['required', 'date'],
            'date_fin' => ['required', 'date', 'after_or_equal:date'],
            'type' => ['required', 'in:initial,suivi,correctif'],
            'route_taken' => ['nullable', 'string', 'max:255'],
            'moniteur' => ['nullable', 'string', 'max:255'],
            'assessment' => ['nullable', 'string'],
            'status' => ['required', 'in:planned,in_progress,completed,cancelled'],
            'validity_days' => ['required', 'integer', 'min:1'],
            'next_planning_session' => ['nullable', 'date'],
            'score' => ['nullable', 'integer', 'min:0', 'max:100'],
            'notes' => ['nullable', 'string'],
        ]);

        try {
            // If driver doesn't have a flotte assigned, assign the selected flotte
            $driver = Driver::find($validated['driver_id']);
            if ($driver && !$driver->flotte_id && isset($validated['flotte_id']) && $validated['flotte_id']) {
                $driver->update(['flotte_id' => $validated['flotte_id']]);
            }

            // Check if next_planning_session has changed before updating
            $oldNextPlanning = $coachingCabine->next_planning_session?->format('Y-m-d');
            $newNextPlanning = isset($validated['next_planning_session']) && $validated['next_planning_session'] 
                ? \Carbon\Carbon::parse($validated['next_planning_session'])->format('Y-m-d') 
                : null;

            $coachingCabine->update($validated);

            // Handle next_planning_session changes
            if ($newNextPlanning && $newNextPlanning !== $oldNextPlanning && $validated['status'] === 'completed') {
                $nextPlanningDate = \Carbon\Carbon::parse($validated['next_planning_session']);
                
                // If old date exists, find the planned session created for that old date and update it
                if ($oldNextPlanning) {
                    $oldDate = \Carbon\Carbon::parse($oldNextPlanning);
                    $existingPlannedSession = CoachingSession::where('driver_id', $validated['driver_id'])
                        ->where('id', '!=', $coachingCabine->id)
                        ->whereDate('date', $oldDate->format('Y-m-d'))
                        ->where('status', 'planned')
                        ->first();

                    if ($existingPlannedSession) {
                        // Update the existing planned session to the new date
                        $dateFin = $nextPlanningDate->copy()->addDays(3);
                        $existingPlannedSession->update([
                            'date' => $nextPlanningDate->format('Y-m-d'),
                            'date_fin' => $dateFin->format('Y-m-d'),
                        ]);
                    } else {
                        // No existing planned session found for old date, create new one for new date
                        $this->createPlannedSession($validated, $coachingCabine, $driver, $nextPlanningDate);
                    }
                } else {
                    // No old date, create new session for new date
                    $this->createPlannedSession($validated, $coachingCabine, $driver, $nextPlanningDate);
                }
            }

            return redirect()
                ->route('coaching-cabines.show', $coachingCabine)
                ->with('success', __('messages.coaching_cabines_updated'));
        } catch (Throwable $th) {
            report($th);
            return back()->withInput()->with('error', __('messages.coaching_cabines_update_error'));
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(CoachingSession $coachingCabine): RedirectResponse
    {
        try {
            $coachingCabine->delete();

            return redirect()
                ->route('coaching-cabines.index')
                ->with('success', __('messages.coaching_cabines_deleted'));
        } catch (Throwable $th) {
            report($th);
            return back()->with('error', __('messages.coaching_cabines_delete_error'));
        }
    }

    /**
     * Display the yearly planning view (spreadsheet-like).
     */
    public function planning(Request $request, ?int $year = null): View|RedirectResponse
    {
        try {
            $year = $year ?? $request->input('year', date('Y'));

            // Get all drivers
            $drivers = Driver::with(['coachingSessions' => function ($query) use ($year) {
                $query->whereYear('date', $year);
            }])->orderBy('full_name')->get();

            // Calculate P/R/NJ for each driver per month
            $planningData = [];
            $monthNames = [
                1 => 'Janvier', 2 => 'Février', 3 => 'Mars', 4 => 'Avril',
                5 => 'Mai', 6 => 'Juin', 7 => 'Juillet', 8 => 'Août',
                9 => 'Septembre', 10 => 'Octobre', 11 => 'Novembre', 12 => 'Décembre'
            ];

            foreach ($drivers as $driver) {
                $driverData = [
                    'driver' => $driver,
                    'months' => []
                ];

                for ($month = 1; $month <= 12; $month++) {
                    $sessions = $driver->coachingSessions->filter(function ($session) use ($year, $month) {
                        return $session->date && 
                               $session->date->year == $year && 
                               $session->date->month == $month;
                    });

                    $planned = $sessions->where('status', 'planned')->count();
                    $completed = $sessions->where('status', 'completed')->count();
                    $cancelled = $sessions->where('status', 'cancelled')->count();

                    $driverData['months'][$month] = [
                        'planned' => $planned,
                        'completed' => $completed,
                        'cancelled' => $cancelled,
                        'sessions' => $sessions,
                    ];
                }

                $planningData[] = $driverData;
            }

            // Calculate totals per month
            $monthTotals = [];
            for ($month = 1; $month <= 12; $month++) {
                $monthTotals[$month] = [
                    'planned' => 0,
                    'completed' => 0,
                    'cancelled' => 0,
                ];

                foreach ($planningData as $driverData) {
                    $monthTotals[$month]['planned'] += $driverData['months'][$month]['planned'];
                    $monthTotals[$month]['completed'] += $driverData['months'][$month]['completed'];
                    $monthTotals[$month]['cancelled'] += $driverData['months'][$month]['cancelled'];
                }
            }

            // Calculate grand total
            $grandTotal = [
                'planned' => array_sum(array_column($monthTotals, 'planned')),
                'completed' => array_sum(array_column($monthTotals, 'completed')),
                'cancelled' => array_sum(array_column($monthTotals, 'cancelled')),
            ];

            // Calculate completed percentage for the year
            $totalSessions = CoachingSession::whereYear('date', $year)->count();
            $completedSessions = CoachingSession::whereYear('date', $year)->where('status', 'completed')->count();
            $completedPercentage = $totalSessions > 0 ? round(($completedSessions / $totalSessions) * 100, 2) : 0;

            return view('coaching_cabines.planning', compact(
                'planningData',
                'monthTotals',
                'grandTotal',
                'monthNames',
                'year',
                'totalSessions',
                'completedSessions',
                'completedPercentage'
            ));
        } catch (Throwable $th) {
            report($th);
            return back()->with('error', __('messages.coaching_cabines_planning_error') ?? 'Impossible de charger la vue de planification.');
        }
    }

    /**
     * Export planning as PDF.
     */
    public function planningPdf(?int $year = null)
    {
        try {
            $year = $year ?? date('Y');

            // Get all drivers
            $drivers = Driver::with(['coachingSessions' => function ($query) use ($year) {
                $query->whereYear('date', $year);
            }])->orderBy('full_name')->get();

            // Calculate P/R/NJ for each driver per month
            $planningData = [];
            $monthNames = [
                1 => 'Janvier', 2 => 'Février', 3 => 'Mars', 4 => 'Avril',
                5 => 'Mai', 6 => 'Juin', 7 => 'Juillet', 8 => 'Août',
                9 => 'Septembre', 10 => 'Octobre', 11 => 'Novembre', 12 => 'Décembre'
            ];

            foreach ($drivers as $driver) {
                $driverData = [
                    'driver' => $driver,
                    'months' => []
                ];

                for ($month = 1; $month <= 12; $month++) {
                    $sessions = $driver->coachingSessions->filter(function ($session) use ($year, $month) {
                        return $session->date && 
                               $session->date->year == $year && 
                               $session->date->month == $month;
                    });

                    $planned = $sessions->where('status', 'planned')->count();
                    $completed = $sessions->where('status', 'completed')->count();
                    $cancelled = $sessions->where('status', 'cancelled')->count();

                    $driverData['months'][$month] = [
                        'planned' => $planned,
                        'completed' => $completed,
                        'cancelled' => $cancelled,
                    ];
                }

                $planningData[] = $driverData;
            }

            // Calculate totals per month
            $monthTotals = [];
            for ($month = 1; $month <= 12; $month++) {
                $monthTotals[$month] = [
                    'planned' => 0,
                    'completed' => 0,
                    'cancelled' => 0,
                ];

                foreach ($planningData as $driverData) {
                    $monthTotals[$month]['planned'] += $driverData['months'][$month]['planned'];
                    $monthTotals[$month]['completed'] += $driverData['months'][$month]['completed'];
                    $monthTotals[$month]['cancelled'] += $driverData['months'][$month]['cancelled'];
                }
            }

            // Calculate grand total
            $grandTotal = [
                'planned' => array_sum(array_column($monthTotals, 'planned')),
                'completed' => array_sum(array_column($monthTotals, 'completed')),
                'cancelled' => array_sum(array_column($monthTotals, 'cancelled')),
            ];

            // Calculate completed percentage for the year
            $totalSessions = CoachingSession::whereYear('date', $year)->count();
            $completedSessions = CoachingSession::whereYear('date', $year)->where('status', 'completed')->count();
            $completedPercentage = $totalSessions > 0 ? round(($completedSessions / $totalSessions) * 100, 2) : 0;

            $pdf = Pdf::loadView('coaching_cabines.planning_pdf', compact(
                'planningData',
                'monthTotals',
                'grandTotal',
                'monthNames',
                'year',
                'totalSessions',
                'completedSessions',
                'completedPercentage'
            ))
                ->setPaper('a4', 'landscape')
                ->setOption('isHtml5ParserEnabled', true)
                ->setOption('isRemoteEnabled', true);

            $filename = 'coaching_planning_' . $year . '_' . date('Y-m-d') . '.pdf';

            return $pdf->download($filename);
        } catch (Throwable $th) {
            report($th);
            return back()->with('error', __('messages.coaching_cabines_planning_pdf_error') ?? 'Impossible de générer le PDF de planification.');
        }
    }

    /**
     * Export coaching session as PDF.
     */
    public function pdf(CoachingSession $coachingCabine)
    {
        try {
            $coachingCabine->load(['driver', 'flotte']);
            
            $pdf = Pdf::loadView('coaching_cabines.pdf', compact('coachingCabine'))
                ->setPaper('a4', 'portrait')
                ->setOption('isHtml5ParserEnabled', true)
                ->setOption('isRemoteEnabled', true);

            $filename = 'coaching_session_' . $coachingCabine->id . '_' . date('Y-m-d') . '.pdf';

            return $pdf->download($filename);
        } catch (Throwable $th) {
            report($th);
            return back()->with('error', __('messages.coaching_cabines_pdf_error') ?? 'Impossible de générer le PDF.');
        }
    }

    /**
     * Complete a coaching session.
     */
    public function complete(Request $request, CoachingSession $coachingCabine): RedirectResponse
    {
        $validated = $request->validate([
            'score' => ['required', 'integer', 'min:0', 'max:100'],
            'next_planning_session' => ['nullable', 'date'],
            'route_taken' => ['nullable', 'string', 'max:255'],
            'assessment' => ['nullable', 'string'],
            'notes' => ['nullable', 'string'],
        ]);

        try {
            // Update the session with completion data
            $coachingCabine->update([
                'status' => 'completed',
                'score' => $validated['score'],
                'next_planning_session' => $validated['next_planning_session'] ?? null,
                'route_taken' => $validated['route_taken'] ?? null,
                'assessment' => $validated['assessment'] ?? null,
                'notes' => $validated['notes'] ?? null,
            ]);

            // Handle next_planning_session changes
            $oldNextPlanning = $coachingCabine->next_planning_session?->format('Y-m-d');
            $newNextPlanning = isset($validated['next_planning_session']) && $validated['next_planning_session'] 
                ? \Carbon\Carbon::parse($validated['next_planning_session'])->format('Y-m-d') 
                : null;

            // Only process if next_planning_session is NEW (different from old value)
            if ($newNextPlanning && $newNextPlanning !== $oldNextPlanning) {
                $nextPlanningDate = \Carbon\Carbon::parse($validated['next_planning_session']);
                
                // If old date exists, find the planned session created for that old date and update it
                if ($oldNextPlanning) {
                    $oldDate = \Carbon\Carbon::parse($oldNextPlanning);
                    $existingPlannedSession = CoachingSession::where('driver_id', $coachingCabine->driver_id)
                        ->where('id', '!=', $coachingCabine->id)
                        ->whereDate('date', $oldDate->format('Y-m-d'))
                        ->where('status', 'planned')
                        ->first();

                    if ($existingPlannedSession) {
                        // Update the existing planned session to the new date
                        $dateFin = $nextPlanningDate->copy()->addDays(3);
                        $existingPlannedSession->update([
                            'date' => $nextPlanningDate->format('Y-m-d'),
                            'date_fin' => $dateFin->format('Y-m-d'),
                        ]);
                    } else {
                        // No existing planned session found for old date, create new one for new date
                        $this->createPlannedSessionForComplete($coachingCabine, $nextPlanningDate);
                    }
                } else {
                    // No old date, create new session for new date
                    $this->createPlannedSessionForComplete($coachingCabine, $nextPlanningDate);
                }
            }

            return redirect()
                ->route('coaching-cabines.index')
                ->with('success', __('messages.coaching_cabines_completed') ?? 'Session de coaching complétée avec succès.');
        } catch (Throwable $th) {
            report($th);
            return back()->withInput()->with('error', __('messages.coaching_cabines_complete_error') ?? 'Erreur lors de la complétion de la session.');
        }
    }

    /**
     * Helper method to create a planned session from update method.
     */
    private function createPlannedSession(array $validated, CoachingSession $coachingCabine, Driver $driver, \Carbon\Carbon $nextPlanningDate): void
    {
        // Check if a session already exists for this driver on this date (excluding current session)
        $existingSession = CoachingSession::where('driver_id', $validated['driver_id'])
            ->where('id', '!=', $coachingCabine->id)
            ->whereDate('date', $nextPlanningDate->format('Y-m-d'))
            ->first();

        if (!$existingSession) {
            // Calculate date_fin (3 days after the start date)
            $dateFin = $nextPlanningDate->copy()->addDays(3);

            // Get flotte_id from current session or driver
            $flotteId = $validated['flotte_id'] ?? $coachingCabine->flotte_id ?? $driver->flotte_id;

            // Create new coaching session
            CoachingSession::create([
                'driver_id' => $validated['driver_id'],
                'flotte_id' => $flotteId,
                'date' => $nextPlanningDate->format('Y-m-d'),
                'date_fin' => $dateFin->format('Y-m-d'),
                'moniteur' => $validated['moniteur'],
                'type' => 'suivi',
                'status' => 'planned',
                'validity_days' => 3,
            ]);
        }
    }

    /**
     * Helper method to create a planned session from complete method.
     */
    private function createPlannedSessionForComplete(CoachingSession $coachingCabine, \Carbon\Carbon $nextPlanningDate): void
    {
        // Check if a session already exists for this driver on this date (excluding current session)
        $existingSession = CoachingSession::where('driver_id', $coachingCabine->driver_id)
            ->where('id', '!=', $coachingCabine->id)
            ->whereDate('date', $nextPlanningDate->format('Y-m-d'))
            ->first();

        if (!$existingSession) {
            // Calculate date_fin (3 days after the start date)
            $dateFin = $nextPlanningDate->copy()->addDays(3);

            // Get flotte_id from current session or driver
            $driver = $coachingCabine->driver;
            $flotteId = $coachingCabine->flotte_id ?? $driver->flotte_id;

            // Create new coaching session
            CoachingSession::create([
                'driver_id' => $coachingCabine->driver_id,
                'flotte_id' => $flotteId,
                'date' => $nextPlanningDate->format('Y-m-d'),
                'date_fin' => $dateFin->format('Y-m-d'),
                'moniteur' => $coachingCabine->moniteur,
                'type' => 'suivi',
                'status' => 'planned',
                'validity_days' => 3,
            ]);
        }
    }
}
