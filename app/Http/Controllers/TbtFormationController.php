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

        // If no formations exist for the selected year, add current year to available years
        if (!$availableYears->contains($year)) {
            $availableYears->push($year)->sortDesc();
        }

        // Build calendar grid data structure
        $calendarData = [];
        for ($month = 1; $month <= 12; $month++) {
            $firstDay = Carbon::create($year, $month, 1);
            $lastDay = $firstDay->copy()->endOfMonth();
            $startDate = $firstDay->copy()->startOfWeek(Carbon::MONDAY);
            $endDate = $lastDay->copy()->endOfWeek(Carbon::SUNDAY);
            
            // Get all days in the month (including partial weeks)
            $days = [];
            $currentDate = $startDate->copy();
            while ($currentDate <= $endDate) {
                $days[] = [
                    'date' => $currentDate->copy(),
                    'day' => $currentDate->day,
                    'isInMonth' => $currentDate->month == $month,
                    'isWeekend' => $currentDate->isWeekend(),
                ];
                $currentDate->addDay();
            }
            
            // Group days into weeks
            $weeks = [];
            $currentWeek = [];
            foreach ($days as $day) {
                $currentWeek[] = $day;
                if (count($currentWeek) == 7) {
                    $weeks[] = $currentWeek;
                    $currentWeek = [];
                }
            }
            if (count($currentWeek) > 0) {
                $weeks[] = $currentWeek;
            }

            // Avoid duplicating weeks that already belong to the previous month
            if (!empty($weeks)) {
                $firstWeekStart = $weeks[0][0]['date'];
                if ($firstWeekStart->year == $year && $firstWeekStart->month < $month) {
                    array_shift($weeks);
                }
            }
            
            // Match formations to weeks
            $monthFormations = $formations->where('month', $month);
            foreach ($weeks as $weekIndex => &$week) {
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
                'weeks' => $weeks,
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
        $calendarData = [];
        for ($month = 1; $month <= 12; $month++) {
            $firstDay = Carbon::create($year, $month, 1);
            $lastDay = $firstDay->copy()->endOfMonth();
            $startDate = $firstDay->copy()->startOfWeek(Carbon::MONDAY);
            $endDate = $lastDay->copy()->endOfWeek(Carbon::SUNDAY);
            
            // Get all days in the month (including partial weeks)
            $days = [];
            $currentDate = $startDate->copy();
            while ($currentDate <= $endDate) {
                $days[] = [
                    'date' => $currentDate->copy(),
                    'day' => $currentDate->day,
                    'isInMonth' => $currentDate->month == $month,
                    'isWeekend' => $currentDate->isWeekend(),
                ];
                $currentDate->addDay();
            }
            
            // Group days into weeks
            $weeks = [];
            $currentWeek = [];
            foreach ($days as $day) {
                $currentWeek[] = $day;
                if (count($currentWeek) == 7) {
                    $weeks[] = $currentWeek;
                    $currentWeek = [];
                }
            }
            if (count($currentWeek) > 0) {
                $weeks[] = $currentWeek;
            }

            // Avoid duplicating weeks that already belong to the previous month
            if (!empty($weeks)) {
                $firstWeekStart = $weeks[0][0]['date'];
                if ($firstWeekStart->year == $year && $firstWeekStart->month < $month) {
                    array_shift($weeks);
                }
            }
            
            // Match formations to weeks
            $monthFormations = $formations->where('month', $month);
            foreach ($weeks as $weekIndex => &$week) {
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
                'weeks' => $weeks,
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
