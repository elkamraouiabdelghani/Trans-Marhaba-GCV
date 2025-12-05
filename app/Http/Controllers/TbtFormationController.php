<?php

namespace App\Http\Controllers;

use App\Models\TbtFormation;
use App\Http\Requests\TbtFormationRequest;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Response;
use Carbon\Carbon;
use Barryvdh\DomPDF\Facade\Pdf;

class TbtFormationController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request): View
    {
        $selectedYear = $request->input('year');
        if (empty($selectedYear)) {
            $selectedYear = date('Y');
        }

        $baseQuery = TbtFormation::query()->where('year', $selectedYear);

        // Filter by month if provided
        if ($request->has('month') && $request->month) {
            $baseQuery->where('month', $request->month);
        }

        // Filter by active status
        if ($request->has('active')) {
            $baseQuery->where('is_active', $request->boolean('active'));
        }

        // Get available years for filter
        $years = TbtFormation::select('year')
            ->distinct()
            ->orderBy('year', 'desc')
            ->pluck('year');

        if (!$years->contains($selectedYear)) {
            $years->push($selectedYear);
            $years = $years->sortDesc()->values();
        }

        $totalCount = (clone $baseQuery)->count();
        $realizedCount = (clone $baseQuery)->where('status', 'realized')->count();
        $plannedCount = (clone $baseQuery)->where('status', 'planned')->count();
        $realizedPercentage = $totalCount > 0 ? round(($realizedCount / $totalCount) * 100, 1) : 0;

        $formations = $baseQuery->orderBy('year', 'desc')
            ->paginate(20);

        $stats = [
            'total' => $totalCount,
            'planned' => $plannedCount,
            'realized' => $realizedCount,
            'realized_percentage' => $realizedPercentage,
        ];

        return view('formations.tbt_formations.index', compact('formations', 'years', 'selectedYear', 'stats'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create(Request $request): View
    {
        // Pre-fill from query parameters if coming from planning calendar
        $year = $request->input('year', date('Y'));
        $weekStartDate = $request->input('week_start_date');
        $weekEndDate = $request->input('week_end_date');
        $month = null;
        
        if ($weekStartDate) {
            $month = \Carbon\Carbon::parse($weekStartDate)->month;
        }

        return view('formations.tbt_formations.create', compact('year', 'weekStartDate', 'weekEndDate', 'month'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(TbtFormationRequest $request): RedirectResponse
    {
        $validated = $request->validated();

        // Check for overlapping formation: same year, same month, overlapping week date range
        $weekStart = $validated['week_start_date'];
        $weekEnd = $validated['week_end_date'];
        
        $exists = TbtFormation::where('year', $validated['year'])
            ->where('month', $validated['month'])
            ->where(function ($query) use ($weekStart, $weekEnd) {
                $query->where(function ($q) use ($weekStart, $weekEnd) {
                    // Check if existing week overlaps with new week
                    // Overlap occurs when: existing_start <= new_end AND existing_end >= new_start
                    $q->where('week_start_date', '<=', $weekEnd)
                      ->where('week_end_date', '>=', $weekStart);
                });
            })
            ->exists();

        if ($exists) {
            return back()->withErrors([
                'week_start_date' => 'Une formation existe déjà pour cette semaine dans ce mois et cette année.'
            ])->withInput();
        }

        $validated['is_active'] = $request->has('is_active');

        // Handle documents (multi, unlimited)
        $documents = [];
        if ($request->hasFile('documents')) {
            foreach ($request->file('documents') as $file) {
                if (!$file) {
                    continue;
                }
                $path = $file->store('tbt_formations/documents', 'public');
                $documents[] = [
                    'name' => $file->getClientOriginalName(),
                    'path' => $path,
                    'uploaded_at' => now()->toDateTimeString(),
                ];
            }
        }
        $validated['documents'] = $documents;

        TbtFormation::create($validated);

        // Redirect to planning if coming from planning calendar, otherwise to index
        $redirectTo = $request->input('from_planning') 
            ? route('tbt-formations.planning', ['year' => $validated['year']])
            : route('tbt-formations.index');

        return redirect($redirectTo)
            ->with('success', __('messages.tbt_formations_created'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(TbtFormation $tbtFormation): View
    {
        return view('formations.tbt_formations.edit', compact('tbtFormation'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(TbtFormationRequest $request, TbtFormation $tbtFormation): RedirectResponse
    {
        $validated = $request->validated();

        // Check for overlapping formation: same year, same month, overlapping week date range (excluding current record)
        $weekStart = $validated['week_start_date'];
        $weekEnd = $validated['week_end_date'];
        
        $exists = TbtFormation::where('year', $validated['year'])
            ->where('month', $validated['month'])
            ->where('id', '!=', $tbtFormation->id)
            ->where(function ($query) use ($weekStart, $weekEnd) {
                $query->where(function ($q) use ($weekStart, $weekEnd) {
                    // Check if existing week overlaps with new week
                    // Overlap occurs when: existing_start <= new_end AND existing_end >= new_start
                    $q->where('week_start_date', '<=', $weekEnd)
                      ->where('week_end_date', '>=', $weekStart);
                });
            })
            ->exists();

        if ($exists) {
            return back()->withErrors([
                'week_start_date' => 'Une formation existe déjà pour cette semaine dans ce mois et cette année.'
            ])->withInput();
        }

        $validated['is_active'] = $request->has('is_active');

        // Handle documents (append new ones, keep existing)
        $existingDocuments = $tbtFormation->documents ?? [];
        if (!is_array($existingDocuments)) {
            $existingDocuments = [];
        }

        $newDocuments = [];
        if ($request->hasFile('documents')) {
            foreach ($request->file('documents') as $file) {
                if (!$file) {
                    continue;
                }
                $path = $file->store('tbt_formations/documents', 'public');
                $newDocuments[] = [
                    'name' => $file->getClientOriginalName(),
                    'path' => $path,
                    'uploaded_at' => now()->toDateTimeString(),
                ];
            }
        }

        $validated['documents'] = array_values(array_merge($existingDocuments, $newDocuments));

        $tbtFormation->update($validated);

        return redirect()->route('tbt-formations.index')
            ->with('success', __('messages.tbt_formations_updated'));
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(TbtFormation $tbtFormation): RedirectResponse
    {
        $tbtFormation->delete();

        return redirect()->route('tbt-formations.index')
            ->with('success', __('messages.tbt_formations_deleted'));
    }

    /**
     * Mark the specified formation as realized.
     */
    public function markAsRealized(Request $request, TbtFormation $tbtFormation): RedirectResponse
    {
        if ($tbtFormation->status !== 'realized') {
            $tbtFormation->status = 'realized';
            $tbtFormation->save();
        }

        return redirect()->route('tbt-formations.index', ['year' => $tbtFormation->year])
            ->with('success', __('messages.tbt_formation_marked_realized'));
    }

    /**
     * Display the yearly planning calendar.
     */
    public function planning(Request $request): View
    {
        $year = $request->input('year', date('Y'));
        
        // Get all formations for the year
        $formations = TbtFormation::where('year', $year)
            ->orderBy('month', 'asc')
            ->orderBy('week_start_date', 'asc')
            ->get();

        // Get available years for navigation
        $availableYears = TbtFormation::select('year')
            ->distinct()
            ->orderBy('year', 'desc')
            ->pluck('year');

        // Add one year before the minimum and one year after the maximum
        if ($availableYears->isNotEmpty()) {
            $minYear = $availableYears->min();
            $maxYear = $availableYears->max();
            
            // Add year before minimum
            if (!$availableYears->contains($minYear - 1)) {
                $availableYears->push($minYear - 1);
            }
            
            // Add year after maximum
            if (!$availableYears->contains($maxYear + 1)) {
                $availableYears->push($maxYear + 1);
            }
        }

        // If no formations exist, ensure current year and adjacent years are included
        if ($availableYears->isEmpty()) {
            $currentYear = (int) date('Y');
            $availableYears->push($currentYear - 1);
            $availableYears->push($currentYear);
            $availableYears->push($currentYear + 1);
        } else {
            // Ensure the selected year is included
            if (!$availableYears->contains($year)) {
                $availableYears->push($year);
            }
        }

        // Sort in descending order
        $availableYears = $availableYears->sortDesc()->values();

        // Build calendar grid data structure
        // First, collect all weeks for the entire year
        $allWeeks = [];
        $yearStart = Carbon::create($year, 1, 1)->startOfWeek(Carbon::MONDAY);
        $yearEnd = Carbon::create($year, 12, 31)->endOfWeek(Carbon::SUNDAY);
        
        $currentDate = $yearStart->copy();
        $currentWeek = [];
        
        while ($currentDate <= $yearEnd) {
            $currentWeek[] = [
                'date' => $currentDate->copy(),
                'day' => $currentDate->day,
                'month' => $currentDate->month,
                'isWeekend' => $currentDate->isWeekend(),
            ];
            
            if (count($currentWeek) == 7) {
                // Determine which month this week belongs to (month with most days)
                // Only consider months in the target year
                $monthCounts = [];
                foreach ($currentWeek as $day) {
                    $dayYear = $day['date']->year;
                    $month = $day['month'];
                    // Only count days that are in the target year
                    if ($dayYear == $year) {
                        $monthCounts[$month] = ($monthCounts[$month] ?? 0) + 1;
                    }
                }
                
                // Only assign to a month if the week has at least one day in the target year
                if (!empty($monthCounts)) {
                    $dominantMonth = array_search(max($monthCounts), $monthCounts);
                    
                    // Add week with its dominant month
                    $allWeeks[] = [
                        'week' => $currentWeek,
                        'month' => $dominantMonth,
                        'weekStart' => $currentWeek[0]['date'],
                        'weekEnd' => $currentWeek[6]['date'],
                    ];
                }
                
                $currentWeek = [];
            }
            
            $currentDate->addDay();
        }
        
        // Handle remaining days if any
        if (count($currentWeek) > 0) {
            // Only consider months in the target year
            $monthCounts = [];
            foreach ($currentWeek as $day) {
                $dayYear = $day['date']->year;
                $month = $day['month'];
                // Only count days that are in the target year
                if ($dayYear == $year) {
                    $monthCounts[$month] = ($monthCounts[$month] ?? 0) + 1;
                }
            }
            
            // Only assign to a month if the week has at least one day in the target year
            if (!empty($monthCounts)) {
                $dominantMonth = array_search(max($monthCounts), $monthCounts);
                
                $allWeeks[] = [
                    'week' => $currentWeek,
                    'month' => $dominantMonth,
                    'weekStart' => $currentWeek[0]['date'],
                    'weekEnd' => $currentWeek[count($currentWeek) - 1]['date'],
                ];
            }
        }
        
        // Now organize weeks by month
        $calendarData = [];
        for ($month = 1; $month <= 12; $month++) {
            $firstDay = Carbon::create($year, $month, 1);
            $lastDay = $firstDay->copy()->endOfMonth();
            
            // Get weeks that belong to this month
            $monthWeeks = [];
            foreach ($allWeeks as $weekData) {
                if ($weekData['month'] == $month) {
                    // Format week days for display
                    $formattedWeek = [];
                    foreach ($weekData['week'] as $day) {
                        $formattedWeek[] = [
                            'date' => $day['date'],
                            'day' => $day['day'],
                            'isInMonth' => $day['month'] == $month,
                            'isWeekend' => $day['isWeekend'],
                        ];
                    }
                    $monthWeeks[] = $formattedWeek;
                }
            }
            
            // Match formations to weeks
            $monthFormations = $formations->where('month', $month);
            foreach ($monthWeeks as $weekIndex => &$week) {
                if (count($week) > 0) {
                    $weekStart = $week[0]['date'];
                    $weekEnd = (count($week) == 7 && isset($week[6])) 
                        ? $week[6]['date'] 
                        : $week[count($week) - 1]['date'];
                    
                    $week['formation'] = $monthFormations->first(function($f) use ($weekStart, $weekEnd) {
                        $fStart = Carbon::parse($f->week_start_date);
                        $fEnd = Carbon::parse($f->week_end_date);
                        return $fStart->lte($weekEnd) && $fEnd->gte($weekStart);
                    });
                } else {
                    $week['formation'] = null;
                }
            }
            
            $calendarData[$month] = [
                'name' => Carbon::create($year, $month, 1)->locale('fr')->monthName,
                'shortName' => Carbon::create($year, $month, 1)->locale('fr')->shortMonthName . '-' . substr($year, -2),
                'weeks' => $monthWeeks,
                'firstDay' => $firstDay,
                'lastDay' => $lastDay,
            ];
        }

        return view('formations.tbt_formations.planning', compact('year', 'calendarData', 'availableYears'));
    }

    /**
     * Download the yearly planning calendar as PDF.
     */
    public function planningPdf(Request $request): Response
    {
        $year = $request->input('year', date('Y'));
        
        // Get all formations for the year
        $formations = TbtFormation::where('year', $year)
            ->orderBy('month', 'asc')
            ->orderBy('week_start_date', 'asc')
            ->get();

        // Build calendar grid data structure (same as planning method)
        // First, collect all weeks for the entire year
        $allWeeks = [];
        $yearStart = Carbon::create($year, 1, 1)->startOfWeek(Carbon::MONDAY);
        $yearEnd = Carbon::create($year, 12, 31)->endOfWeek(Carbon::SUNDAY);
        
        $currentDate = $yearStart->copy();
        $currentWeek = [];
        
        while ($currentDate <= $yearEnd) {
            $currentWeek[] = [
                'date' => $currentDate->copy(),
                'day' => $currentDate->day,
                'month' => $currentDate->month,
                'isWeekend' => $currentDate->isWeekend(),
            ];
            
            if (count($currentWeek) == 7) {
                // Determine which month this week belongs to (month with most days)
                // Only consider months in the target year
                $monthCounts = [];
                foreach ($currentWeek as $day) {
                    $dayYear = $day['date']->year;
                    $month = $day['month'];
                    // Only count days that are in the target year
                    if ($dayYear == $year) {
                        $monthCounts[$month] = ($monthCounts[$month] ?? 0) + 1;
                    }
                }
                
                // Only assign to a month if the week has at least one day in the target year
                if (!empty($monthCounts)) {
                    $dominantMonth = array_search(max($monthCounts), $monthCounts);
                    
                    // Add week with its dominant month
                    $allWeeks[] = [
                        'week' => $currentWeek,
                        'month' => $dominantMonth,
                        'weekStart' => $currentWeek[0]['date'],
                        'weekEnd' => $currentWeek[6]['date'],
                    ];
                }
                
                $currentWeek = [];
            }
            
            $currentDate->addDay();
        }
        
        // Handle remaining days if any
        if (count($currentWeek) > 0) {
            // Only consider months in the target year
            $monthCounts = [];
            foreach ($currentWeek as $day) {
                $dayYear = $day['date']->year;
                $month = $day['month'];
                // Only count days that are in the target year
                if ($dayYear == $year) {
                    $monthCounts[$month] = ($monthCounts[$month] ?? 0) + 1;
                }
            }
            
            // Only assign to a month if the week has at least one day in the target year
            if (!empty($monthCounts)) {
                $dominantMonth = array_search(max($monthCounts), $monthCounts);
                
                $allWeeks[] = [
                    'week' => $currentWeek,
                    'month' => $dominantMonth,
                    'weekStart' => $currentWeek[0]['date'],
                    'weekEnd' => $currentWeek[count($currentWeek) - 1]['date'],
                ];
            }
        }
        
        // Now organize weeks by month
        $calendarData = [];
        for ($month = 1; $month <= 12; $month++) {
            $firstDay = Carbon::create($year, $month, 1);
            $lastDay = $firstDay->copy()->endOfMonth();
            
            // Get weeks that belong to this month
            $monthWeeks = [];
            foreach ($allWeeks as $weekData) {
                if ($weekData['month'] == $month) {
                    // Format week days for display
                    $formattedWeek = [];
                    foreach ($weekData['week'] as $day) {
                        $formattedWeek[] = [
                            'date' => $day['date'],
                            'day' => $day['day'],
                            'isInMonth' => $day['month'] == $month,
                            'isWeekend' => $day['isWeekend'],
                        ];
                    }
                    $monthWeeks[] = $formattedWeek;
                }
            }
            
            // Match formations to weeks
            $monthFormations = $formations->where('month', $month);
            foreach ($monthWeeks as $weekIndex => &$week) {
                if (count($week) > 0) {
                    $weekStart = $week[0]['date'];
                    $weekEnd = (count($week) == 7 && isset($week[6])) 
                        ? $week[6]['date'] 
                        : $week[count($week) - 1]['date'];
                    
                    $week['formation'] = $monthFormations->first(function($f) use ($weekStart, $weekEnd) {
                        $fStart = Carbon::parse($f->week_start_date);
                        $fEnd = Carbon::parse($f->week_end_date);
                        return $fStart->lte($weekEnd) && $fEnd->gte($weekStart);
                    });
                } else {
                    $week['formation'] = null;
                }
            }
            
            $calendarData[$month] = [
                'name' => Carbon::create($year, $month, 1)->locale('fr')->monthName,
                'shortName' => Carbon::create($year, $month, 1)->locale('fr')->shortMonthName . '-' . substr($year, -2),
                'weeks' => $monthWeeks,
                'firstDay' => $firstDay,
                'lastDay' => $lastDay,
            ];
        }

        $pdf = Pdf::loadView('formations.tbt_formations.planning_pdf', compact('year', 'calendarData'))
            ->setPaper('a4', 'landscape')
            ->setOption('isHtml5ParserEnabled', true)
            ->setOption('isRemoteEnabled', true)
            ->setOption('enable-javascript', true)
            ->setOption('page-break-inside', 'avoid');

        $filename = 'tbt_planning_' . $year . '_' . date('Y-m-d') . '.pdf';

        return $pdf->download($filename);
    }
}
