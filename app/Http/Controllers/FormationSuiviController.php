<?php

namespace App\Http\Controllers;

use App\Models\Driver;
use App\Models\DriverFormation;
use App\Models\DriverTbtFormation;
use App\Models\Flotte;
use App\Models\Formation;
use App\Models\TbtFormation;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Barryvdh\DomPDF\Facade\Pdf;

class FormationSuiviController extends Controller
{
    public function index(Request $request): View
    {
        $currentYear = now()->year;

        $type = $request->input('type', 'formation'); // formation | tbt
        $theme = $request->input('theme');
        $flotteId = $request->input('flotte_id');
        $formationType = $request->input('formation_type');
        $year = $request->input('year', $currentYear);
        $driverId = $request->input('driver_id');

        // Filters data
        $drivers = Driver::orderBy('full_name')->get();
        $flottes = Flotte::orderBy('name')->get();
        $formationTypes = Formation::typeOptions();
        $formationThemesAll = Formation::select('theme')->whereNotNull('theme')->distinct()->orderBy('theme')->pluck('theme');
        $tbtThemesAll = TbtFormation::select('title')->whereNotNull('title')->distinct()->orderBy('title')->pluck('title');
        $themes = $type === 'tbt' ? $tbtThemesAll : $formationThemesAll;

        $formationYears = Formation::whereNotNull('realizing_date')
            ->selectRaw('YEAR(realizing_date) as year')
            ->distinct()
            ->orderBy('year', 'desc')
            ->pluck('year');
        if (!$formationYears->contains($year)) {
            $formationYears->push($year);
            $formationYears = $formationYears->sortDesc()->values();
        }

        $tbtYears = TbtFormation::select('year')->distinct()->orderBy('year', 'desc')->pluck('year');
        if (!$tbtYears->contains($year)) {
            $tbtYears->push($year);
            $tbtYears = $tbtYears->sortDesc()->values();
        }

        $chartData = [
            'labels' => [],
            'planned' => [],
            'realized' => [],
            'totals' => [
                'planned' => 0,
                'realized' => 0,
            ],
        ];

        // Prepare driver collection once
        $activeDrivers = Driver::where(function ($q2) {
            $q2->whereNull('status')->orWhere('status', '!=', 'terminated');
        })->get()->keyBy('id');

        if ($type === 'tbt') {
            // Existing driver TBT formations
            $query = DriverTbtFormation::with(['driver', 'tbtFormation'])
                ->whereIn('driver_id', $activeDrivers->keys())
                ->whereHas('tbtFormation', function ($q) use ($year) {
                    $q->where('year', $year);
                });

            if ($driverId) {
                $query->where('driver_id', $driverId);
            }

            $rows = $query->get();

            $counts = [];
            $existingPairs = [];

            foreach ($rows as $row) {
                $dId = $row->driver_id;
                $existingPairs[$dId][$row->tbt_formation_id] = $row->status;
                $counts[$dId]['planned'] = ($counts[$dId]['planned'] ?? 0) + ($row->status === 'planned' ? 1 : 0);
                $counts[$dId]['done'] = ($counts[$dId]['done'] ?? 0) + ($row->status === 'done' ? 1 : 0);
            }

            // Include planned TBT formations for all drivers
            $plannedTbt = TbtFormation::where('status', 'planned')
                ->where('year', $year);
            if ($theme) {
                $plannedTbt->where('title', $theme);
            }
            $plannedList = $plannedTbt->get();

            foreach ($plannedList as $p) {
                foreach ($activeDrivers as $driver) {
                    if ($driverId && (int)$driverId !== (int)$driver->id) {
                        continue;
                    }
                    // avoid double counting if already exists
                    if (isset($existingPairs[$driver->id][$p->id])) {
                        continue;
                    }
                    $counts[$driver->id]['planned'] = ($counts[$driver->id]['planned'] ?? 0) + 1;
                }
            }

            // Build chart data
            foreach ($counts as $dId => $c) {
                $driver = $activeDrivers->get($dId);
                $chartData['labels'][] = $driver?->full_name ?? ('Driver #' . $dId);
                $chartData['planned'][] = $c['planned'] ?? 0;
                $chartData['realized'][] = $c['done'] ?? 0;
                $chartData['totals']['planned'] += $c['planned'] ?? 0;
                $chartData['totals']['realized'] += $c['done'] ?? 0;
            }
        } else {
            // Formation
            $query = DriverFormation::with(['driver', 'formation'])
                ->whereIn('driver_id', $activeDrivers->keys());

            if ($driverId) {
                $query->where('driver_id', $driverId);
            }

            if ($theme) {
                $query->whereHas('formation', function ($q) use ($theme) {
                    $q->where('theme', $theme);
                });
            }

            if ($flotteId) {
                $query->whereHas('driver', function ($q) use ($flotteId) {
                    $q->where('flotte_id', $flotteId);
                });
            }

            if ($formationType) {
                $query->whereHas('formation', function ($q) use ($formationType) {
                    $q->where('type', $formationType);
                });
            }

            if ($year) {
                $query->where(function ($q) use ($year) {
                    $q->whereYear('done_at', $year)
                        ->orWhereYear('planned_at', $year)
                        ->orWhereHas('formation', function ($fq) use ($year) {
                            $fq->whereYear('realizing_date', $year);
                        });
                });
            }

            $rows = $query->get();

            $counts = [];
            $existingPairs = [];

            foreach ($rows as $row) {
                $dId = $row->driver_id;
                $existingPairs[$dId][$row->formation_id] = $row->status;
                $counts[$dId]['planned'] = ($counts[$dId]['planned'] ?? 0) + ($row->status === 'planned' ? 1 : 0);
                $counts[$dId]['done'] = ($counts[$dId]['done'] ?? 0) + ($row->status === 'done' ? 1 : 0);
            }

            // Include planned formations (status planned) as planned for drivers
            $plannedFormations = Formation::where('status', 'planned')
                ->when($theme, fn($q) => $q->where('theme', $theme))
                ->when($formationType, fn($q) => $q->where('type', $formationType))
                ->when($year, fn($q) => $q->whereYear('realizing_date', $year));

            $plannedList = $plannedFormations->get();

            foreach ($plannedList as $pf) {
                $driversForFormation = $pf->flotte_id
                    ? $activeDrivers->filter(fn($d) => (int)$d->flotte_id === (int)$pf->flotte_id)
                    : $activeDrivers;

                foreach ($driversForFormation as $driver) {
                    if ($driverId && (int)$driverId !== (int)$driver->id) {
                        continue;
                    }
                    // skip if already has a record
                    if (isset($existingPairs[$driver->id][$pf->id])) {
                        continue;
                    }
                    // respect outer flotte filter
                    if ($flotteId && (int)$driver->flotte_id !== (int)$flotteId) {
                        continue;
                    }
                    $counts[$driver->id]['planned'] = ($counts[$driver->id]['planned'] ?? 0) + 1;
                }
            }

            foreach ($counts as $dId => $c) {
                $driver = $activeDrivers->get($dId);
                $chartData['labels'][] = $driver?->full_name ?? ('Driver #' . $dId);
                $chartData['planned'][] = $c['planned'] ?? 0;
                $chartData['realized'][] = $c['done'] ?? 0;
                $chartData['totals']['planned'] += $c['planned'] ?? 0;
                $chartData['totals']['realized'] += $c['done'] ?? 0;
            }
        }

        return view('formations.suivi', compact(
            'type',
            'theme',
            'flotteId',
            'formationType',
            'year',
            'driverId',
            'drivers',
            'flottes',
            'formationTypes',
            'themes',
            'formationThemesAll',
            'tbtThemesAll',
            'formationYears',
            'tbtYears',
            'chartData'
        ));
    }

    public function pdf(Request $request)
    {
        // Reuse index logic for data
        $response = $this->index($request);
        $viewData = $response->getData();
        
        // Get chart image if provided (from POST request)
        $chartImage = $request->input('chart_image');
        
        // Convert view data to array and add chart image
        $data = (array) $viewData;
        $data['chartImage'] = $chartImage;

        $pdf = Pdf::loadView('formations.suivi_pdf', $data)
            ->setPaper('a4', 'landscape')
            ->setOption('isHtml5ParserEnabled', true)
            ->setOption('isRemoteEnabled', true);

        $filename = 'formations_suivi_' . date('Y-m-d') . '.pdf';

        return $pdf->download($filename);
    }
}

