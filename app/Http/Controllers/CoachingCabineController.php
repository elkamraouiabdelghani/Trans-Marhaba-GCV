<?php

namespace App\Http\Controllers;

use App\Http\Requests\CoachingSessionRequest;
use App\Models\CoachingSession;
use App\Models\Driver;
use App\Models\Flotte;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Throwable;

class CoachingCabineController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        try {
            $query = CoachingSession::with(['driver', 'flotte', 'checklist']);

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

            $year = $request->input('year', date('Y'));
            // Always scope to selected year (default current year)
            $query->whereYear('date', $year);

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

            $sessions = $query->with(['checklist.answers'])
                ->orderBy('date', 'desc')
                ->orderBy('created_at', 'desc')
                ->get();

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
    public function create()
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
    public function store(CoachingSessionRequest $request)
    {
        $validated = $request->validated();

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
    public function show(CoachingSession $coachingCabine)
    {
        try {
            $coachingCabine->load(['driver', 'flotte']);
            $drivers = Driver::orderBy('full_name')->get();
            $flottes = Flotte::orderBy('name')->get();

            return view('coaching_cabines.show', compact('coachingCabine', 'drivers', 'flottes'));
        } catch (Throwable $th) {
            report($th);
            return back()->with('error', __('messages.coaching_cabines_show_error') ?? 'Impossible de charger les détails de la session.');
        }
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(CoachingSession $coachingCabine)
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
    public function update(CoachingSessionRequest $request, CoachingSession $coachingCabine)
    {
        $validated = $request->validated();

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

            // Process rest_places: Always replace with what's in the request
            // This ensures that removed rest places are deleted from the database
            // Check if the form was submitted with rest_places (using hidden field as indicator)
            if ($request->has('rest_places_sent')) {
                // Get rest_places from request (will be empty array if no inputs, or array of values)
                $restPlacesInput = $request->input('rest_places', []);
                // Filter out empty values (null, empty string, whitespace-only)
                $restPlaces = array_filter($restPlacesInput, function($value) {
                    return $value !== null && $value !== '' && trim($value) !== '';
                });
                // Re-index array and set to null if empty (to allow clearing all rest places)
                // Always replace existing rest_places with what's in the request
                $validated['rest_places'] = !empty($restPlaces) ? array_values($restPlaces) : null;
            } else {
                // If rest_places_sent is not in the request, don't update rest_places (keep existing)
                unset($validated['rest_places']);
            }

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
                        $validityDays = $existingPlannedSession->validity_days ?? 5;
                        $dateFin = $nextPlanningDate->copy()->addDays($validityDays);
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
    public function destroy(CoachingSession $coachingCabine)
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
    public function planning(Request $request, ?int $year = null)
    {
        try {
            $year = $year ?? $request->input('year', date('Y'));
            $flotteId = $request->input('flotte_id');

            // Get all flottes for the filter dropdown
            $flottes = Flotte::orderBy('name')->get();

            // Get drivers, filtered by flotte if provided
            $driversQuery = Driver::with(['coachingSessions' => function ($query) use ($year) {
                $query->whereYear('date', $year);
            }]);

            if ($flotteId) {
                $driversQuery->where('flotte_id', $flotteId);
            }

            $drivers = $driversQuery->orderBy('full_name')->get();

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

                    // Planned: includes all sessions that were planned (planned, in_progress, or completed)
                    // This way completed sessions appear in both planned and completed columns
                    $planned = $sessions->whereIn('status', ['planned', 'in_progress', 'completed'])->count();
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
            // Total planned sessions (including completed ones) - filtered by flotte if provided
            $sessionsQuery = CoachingSession::whereYear('date', $year);
            if ($flotteId) {
                $sessionsQuery->whereHas('driver', function ($query) use ($flotteId) {
                    $query->where('flotte_id', $flotteId);
                });
            }
            
            $totalPlannedSessions = (clone $sessionsQuery)
                ->whereIn('status', ['planned', 'in_progress', 'completed'])
                ->count();
            $completedSessions = (clone $sessionsQuery)->where('status', 'completed')->count();
            $completedPercentage = $totalPlannedSessions > 0 ? round(($completedSessions / $totalPlannedSessions) * 100, 2) : 0;
            $totalSessions = (clone $sessionsQuery)->count();

            return view('coaching_cabines.planning', compact(
                'planningData',
                'monthTotals',
                'grandTotal',
                'monthNames',
                'year',
                'totalSessions',
                'completedSessions',
                'completedPercentage',
                'flottes',
                'flotteId'
            ));
        } catch (Throwable $th) {
            report($th);
            return back()->with('error', __('messages.coaching_cabines_planning_error') ?? 'Impossible de charger la vue de planification.');
        }
    }

    /**
     * Export planning as PDF.
     */
    public function planningPdf(Request $request, ?int $year = null)
    {
        try {
            $year = $year ?? $request->input('year', date('Y'));
            $flotteId = $request->input('flotte_id');

            // Get drivers, filtered by flotte if provided
            $driversQuery = Driver::with(['coachingSessions' => function ($query) use ($year) {
                $query->whereYear('date', $year);
            }]);

            if ($flotteId) {
                $driversQuery->where('flotte_id', $flotteId);
            }

            $drivers = $driversQuery->orderBy('full_name')->get();

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

                    // Planned: includes all sessions that were planned (planned, in_progress, or completed)
                    // This way completed sessions appear in both planned and completed columns
                    $planned = $sessions->whereIn('status', ['planned', 'in_progress', 'completed'])->count();
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
            // Total planned sessions (including completed ones) - filtered by flotte if provided
            $sessionsQuery = CoachingSession::whereYear('date', $year);
            if ($flotteId) {
                $sessionsQuery->whereHas('driver', function ($query) use ($flotteId) {
                    $query->where('flotte_id', $flotteId);
                });
            }
            
            $totalPlannedSessions = (clone $sessionsQuery)
                ->whereIn('status', ['planned', 'in_progress', 'completed'])
                ->count();
            $completedSessions = (clone $sessionsQuery)->where('status', 'completed')->count();
            $completedPercentage = $totalPlannedSessions > 0 ? round(($completedSessions / $totalPlannedSessions) * 100, 2) : 0;
            $totalSessions = (clone $sessionsQuery)->count();

            // Get type information for legend
            $typeTitles = \App\Models\CoachingSession::getTypeTitles();
            $typeColors = \App\Models\CoachingSession::getTypeColors();

            // Get flotte information if selected
            $selectedFlotte = null;
            if ($flotteId) {
                $selectedFlotte = Flotte::find($flotteId);
            }

            $pdf = Pdf::loadView('coaching_cabines.planning_pdf', compact(
                'planningData',
                'monthTotals',
                'grandTotal',
                'monthNames',
                'year',
                'totalSessions',
                'completedSessions',
                'completedPercentage',
                'typeTitles',
                'typeColors',
                'selectedFlotte'
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
    public function pdf(Request $request, CoachingSession $coachingCabine)
    {
        try {
            $coachingCabine->load(['driver', 'flotte']);
            
            // Get map image from request (captured by html2canvas from show page)
            $mapImageBase64 = $request->input('map_image');
            $staticMapUrl = null;
            
            // If no map image provided, try to generate static map URL as fallback
            if (empty($mapImageBase64)) {
                $staticMapUrl = $this->generateCoachingSessionMapUrl($coachingCabine);
                
                if ($staticMapUrl) {
                    try {
                        $response = \Illuminate\Support\Facades\Http::timeout(30)->get($staticMapUrl);
                        if ($response->successful()) {
                            $imageContent = $response->body();
                            if (!empty($imageContent)) {
                                $mapImageBase64 = 'data:image/png;base64,' . base64_encode($imageContent);
                            }
                        }
                    } catch (\Exception $e) {
                        \Illuminate\Support\Facades\Log::warning('Failed to download map image for coaching session PDF', [
                            'url' => $staticMapUrl,
                            'error' => $e->getMessage(),
                        ]);
                    }
                }
            }
            
            $pdf = Pdf::loadView('coaching_cabines.pdf', compact('coachingCabine', 'staticMapUrl', 'mapImageBase64'))
                ->setPaper('a4', 'portrait')
                ->setOption('isHtml5ParserEnabled', true)
                ->setOption('isRemoteEnabled', true)
                ->setOption('enable-local-file-access', true);

            $filename = 'coaching_session_' . $coachingCabine->id . '_' . date('Y-m-d') . '.pdf';

            return $pdf->download($filename);
        } catch (Throwable $th) {
            report($th);
            return back()->with('error', __('messages.coaching_cabines_pdf_error') ?? 'Impossible de générer le PDF.');
        }
    }

    /**
     * Generate static map URL with route and rest places for coaching session
     */
    private function generateCoachingSessionMapUrl(CoachingSession $coachingCabine): ?string
    {
        // Check if we have route coordinates
        if (!$coachingCabine->from_latitude || !$coachingCabine->from_longitude || 
            !$coachingCabine->to_latitude || !$coachingCabine->to_longitude) {
            return null;
        }

        $fromLat = $coachingCabine->from_latitude;
        $fromLng = $coachingCabine->from_longitude;
        $toLat = $coachingCabine->to_latitude;
        $toLng = $coachingCabine->to_longitude;

        // Get rest places
        $restPlaces = $coachingCabine->rest_places ?? [];
        
        // Try to get route path from OSRM for better visualization
        $routePath = null;
        try {
            $osrmUrl = "https://router.project-osrm.org/route/v1/driving/{$fromLng},{$fromLat};{$toLng},{$toLat}?overview=full&geometries=geojson";
            $response = \Illuminate\Support\Facades\Http::timeout(10)->get($osrmUrl);
            if ($response->successful()) {
                $data = $response->json();
                if (isset($data['routes'][0]['geometry']['coordinates'])) {
                    $coordinates = $data['routes'][0]['geometry']['coordinates'];
                    // Convert [lng, lat] to [lat, lng] for Google Maps
                    $routePath = array_map(function($coord) {
                        return $coord[1] . ',' . $coord[0]; // [lat, lng]
                    }, $coordinates);
                    // Limit to reasonable number of points (Google Maps has URL length limits)
                    if (count($routePath) > 100) {
                        $routePath = array_filter($routePath, function($key) use ($routePath) {
                            return $key % ceil(count($routePath) / 100) == 0 || $key == 0 || $key == count($routePath) - 1;
                        }, ARRAY_FILTER_USE_KEY);
                        $routePath = array_values($routePath);
                    }
                }
            }
        } catch (\Exception $e) {
            // Fallback to straight line if OSRM fails
        }
        
        // Calculate center point
        $centerLat = ($fromLat + $toLat) / 2;
        $centerLng = ($fromLng + $toLng) / 2;
        
        // Calculate zoom level based on distance
        $latDiff = abs($toLat - $fromLat);
        $lngDiff = abs($toLng - $fromLng);
        $maxDiff = max($latDiff, $lngDiff);
        
        if ($maxDiff > 5) {
            $zoom = 6;
        } elseif ($maxDiff > 1) {
            $zoom = 7;
        } elseif ($maxDiff > 0.5) {
            $zoom = 8;
        } elseif ($maxDiff > 0.1) {
            $zoom = 9;
        } else {
            $zoom = 10;
        }

        // Try Google Maps Static API first (if API key is available)
        $googleApiKey = env('GOOGLE_MAPS_API_KEY', '');
        if (!empty($googleApiKey)) {
            $params = [
                'center' => $centerLat . ',' . $centerLng,
                'zoom' => $zoom,
                'size' => '1200x800', // Larger size for better quality
                'maptype' => 'roadmap',
                'key' => $googleApiKey,
                'scale' => 2, // High DPI for better quality
            ];

            // Add path for route (use OSRM path if available, otherwise straight line)
            if ($routePath && count($routePath) > 1) {
                $path = 'color:0x2563eb|weight:6|' . implode('|', $routePath);
            } else {
                $path = 'color:0x2563eb|weight:6|' . $fromLat . ',' . $fromLng . '|' . $toLat . ',' . $toLng;
            }
            $params['path'] = $path;

            // Build markers parameter
            $markerParams = [];
            // Start marker (green)
            $markerParams[] = 'markers=color:green|label:F|' . $fromLat . ',' . $fromLng;
            // End marker (red)
            $markerParams[] = 'markers=color:red|label:T|' . $toLat . ',' . $toLng;
            
            // Note: Rest places are city names, we'd need to geocode them to add markers
            // For now, we'll just show the route and list rest places in the PDF text
            
            // Build URL with path and markers
            $url = 'https://maps.googleapis.com/maps/api/staticmap?' . http_build_query($params);
            if (!empty($markerParams)) {
                $url .= '&' . implode('&', $markerParams);
            }

            return $url;
        }

        // Fallback to OpenStreetMap static map (basic, without route/path)
        // Note: OSM static maps don't support paths/markers easily, so this is a basic fallback
        return 'https://staticmap.openstreetmap.de/staticmap.php?' . http_build_query([
            'center' => $centerLat . ',' . $centerLng,
            'zoom' => $zoom,
            'size' => '1200x800',
            'maptype' => 'mapnik',
        ]);
    }

    /**
     * Complete a coaching session.
     */
    public function complete(Request $request, CoachingSession $coachingCabine)
    {
        $validated = $request->validate([
            'score' => ['required', 'integer', 'min:0', 'max:100'],
            'next_planning_session' => ['nullable', 'date'],
            'route_taken' => ['nullable', 'string', 'max:255'],
            'assessment' => ['nullable', 'string'],
            'notes' => ['nullable', 'string'],
            'rest_places' => ['nullable', 'array'],
            'rest_places.*' => ['required', 'string', 'max:255'],
            'moniteur' => ['nullable', 'string', 'max:255'],
            'date' => ['nullable', 'date'],
            'date_fin' => ['nullable', 'date', 'after_or_equal:date'],
            'type' => ['nullable', 'in:initial,suivi,correctif,route_analysing,obc_suite,other'],
            'validity_days' => ['nullable', 'integer', 'min:1'],
            'from_latitude' => ['nullable', 'numeric', 'between:-90,90'],
            'from_longitude' => ['nullable', 'numeric', 'between:-180,180'],
            'from_location_name' => ['nullable', 'string', 'max:255'],
            'to_latitude' => ['nullable', 'numeric', 'between:-90,90'],
            'to_longitude' => ['nullable', 'numeric', 'between:-180,180'],
            'to_location_name' => ['nullable', 'string', 'max:255'],
        ]);

        // Validate rest_places count doesn't exceed validity_days - 1 (maximum allowed)
        $validityDays = $coachingCabine->validity_days;
        $restPlaces = $validated['rest_places'] ?? [];
        $maxCount = $validityDays - 1;
        $actualCount = count(array_filter($restPlaces));
        
        // Allow up to validity_days - 1 rest places when completing (0 to max)
        if ($actualCount > $maxCount) {
            return back()->withInput()->withErrors([
                'rest_places' => __('messages.rest_places_max_exceeded', ['max' => $maxCount, 'actual' => $actualCount])
            ]);
        }

        try {
            // Prepare update data
            $updateData = [
                'status' => 'completed',
                'score' => $validated['score'],
                'next_planning_session' => $validated['next_planning_session'] ?? null,
                'route_taken' => $validated['route_taken'] ?? null,
                'assessment' => $validated['assessment'] ?? null,
                'notes' => $validated['notes'] ?? null,
                'rest_places' => $restPlaces,
            ];

            // Add optional fields if provided
            if (isset($validated['moniteur'])) {
                $updateData['moniteur'] = $validated['moniteur'];
            }
            if (isset($validated['date'])) {
                $updateData['date'] = $validated['date'];
            }
            if (isset($validated['date_fin'])) {
                $updateData['date_fin'] = $validated['date_fin'];
            }
            if (isset($validated['type'])) {
                $updateData['type'] = $validated['type'];
            }
            if (isset($validated['validity_days'])) {
                $updateData['validity_days'] = $validated['validity_days'];
            }
            if (isset($validated['from_latitude'])) {
                $updateData['from_latitude'] = $validated['from_latitude'];
            }
            if (isset($validated['from_longitude'])) {
                $updateData['from_longitude'] = $validated['from_longitude'];
            }
            if (isset($validated['from_location_name'])) {
                $updateData['from_location_name'] = $validated['from_location_name'];
            }
            if (isset($validated['to_latitude'])) {
                $updateData['to_latitude'] = $validated['to_latitude'];
            }
            if (isset($validated['to_longitude'])) {
                $updateData['to_longitude'] = $validated['to_longitude'];
            }
            if (isset($validated['to_location_name'])) {
                $updateData['to_location_name'] = $validated['to_location_name'];
            }

            // Update the session with completion data
            $coachingCabine->update($updateData);

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
                        $validityDays = $existingPlannedSession->validity_days ?? 5;
                        $dateFin = $nextPlanningDate->copy()->addDays($validityDays);
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

            $returnUrl = $request->input('return_url') ?: $request->headers->get('referer');

            return redirect()
                ->to($returnUrl ?? route('coaching-cabines.index'))
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
            // Calculate date_fin based on validity_days (5 days for suivi type)
            $validityDays = 5; // Default for suivi type
            $dateFin = $nextPlanningDate->copy()->addDays($validityDays);

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
                'validity_days' => $validityDays,
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
            // Calculate date_fin based on validity_days (5 days for suivi type)
            $validityDays = 5; // Default for suivi type
            $dateFin = $nextPlanningDate->copy()->addDays($validityDays);

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
                'validity_days' => $validityDays,
            ]);
        }
    }

    /**
     * Quick plan a coaching session for a driver in a specific month.
     */
    public function quickPlan(Request $request)
    {
        $validated = $request->validate([
            'driver_id' => ['required', 'exists:drivers,id'],
            'year' => ['required', 'integer', 'min:2000', 'max:2100'],
            'month' => ['required', 'integer', 'min:1', 'max:12'],
        ]);

        try {
            $driver = Driver::findOrFail($validated['driver_id']);
            $year = $validated['year'];
            $month = $validated['month'];

            // Calculate the date (first day of the month)
            $date = \Carbon\Carbon::create($year, $month, 1);

            // Check if a session already exists for this driver in this month
            $existingSession = CoachingSession::where('driver_id', $driver->id)
                ->whereYear('date', $year)
                ->whereMonth('date', $month)
                ->where('status', 'planned')
                ->first();

            if ($existingSession) {
                return response()->json([
                    'success' => false,
                    'message' => __('messages.coaching_session_already_planned_for_month') ?? 'A coaching session is already planned for this driver in this month.',
                ], 422);
            }

            // Calculate date_fin based on validity_days (5 days for suivi type)
            $validityDays = 5; // Default for suivi type
            $dateFin = $date->copy()->addDays($validityDays);

            // Get flotte_id from driver
            $flotteId = $driver->flotte_id;

            // Create new coaching session
            CoachingSession::create([
                'driver_id' => $driver->id,
                'flotte_id' => $flotteId,
                'date' => $date->format('Y-m-d'),
                'date_fin' => $dateFin->format('Y-m-d'),
                'type' => 'suivi',
                'status' => 'planned',
                'validity_days' => $validityDays,
            ]);

            return response()->json([
                'success' => true,
                'message' => __('messages.coaching_session_planned_successfully') ?? 'Coaching session planned successfully.',
            ]);
        } catch (Throwable $th) {
            report($th);
            return response()->json([
                'success' => false,
                'message' => __('messages.coaching_session_planning_error') ?? 'Error planning coaching session.',
            ], 500);
        }
    }
}
