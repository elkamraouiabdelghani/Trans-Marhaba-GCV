<?php

namespace App\Http\Controllers;

use App\Exports\DrivingTimesStatsExport;
use App\Exports\ViolationsStatsExport;
use App\Models\Driver;
use App\Models\DriverActivity;
use App\Models\DriverViolation;
use App\Models\ViolationType;
use Barryvdh\DomPDF\Facade\Pdf;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Facades\Excel;

class ExportCenterController extends Controller
{
    public function index(Request $request)
    {
        $periodType = $request->get('period_type', 'month');
        $month = $request->get('month', now()->format('Y-m'));
        $quarter = $request->get('quarter');
        $year = $request->get('year', now()->format('Y'));
        $dateFrom = $request->get('date_from');
        $dateTo = $request->get('date_to');
        $showCharts = $request->get('show_charts', false) == '1' || $request->get('show_charts', false) === true;

        // Determine date range based on period type
        $dateRange = $this->getDateRange($periodType, $month, $quarter, $year, $dateFrom, $dateTo);
        $startDate = $dateRange['start'];
        $endDate = $dateRange['end'];

        // Violations Statistics
        $violationsStats = $this->getViolationsStats($startDate, $endDate);
        
        // Driving Times Statistics
        $drivingTimesStats = $this->getDrivingTimesStats($startDate, $endDate);

        return view('export-center.index', [
            'periodType' => $periodType,
            'month' => $month,
            'quarter' => $quarter,
            'year' => $year,
            'dateFrom' => $dateFrom,
            'dateTo' => $dateTo,
            'startDate' => $startDate,
            'endDate' => $endDate,
            'showCharts' => $showCharts,
            'violationsStats' => $violationsStats,
            'drivingTimesStats' => $drivingTimesStats,
        ]);
    }

    public function exportViolations(Request $request)
    {
        $periodType = $request->get('period_type', 'month');
        $month = $request->get('month', now()->format('Y-m'));
        $quarter = $request->get('quarter');
        $year = $request->get('year', now()->format('Y'));
        $dateFrom = $request->get('date_from');
        $dateTo = $request->get('date_to');

        $dateRange = $this->getDateRange($periodType, $month, $quarter, $year, $dateFrom, $dateTo);
        $violationsStats = $this->getViolationsStats($dateRange['start'], $dateRange['end']);

        $fileName = sprintf(
            'violations-stats-%s-%s.xlsx',
            $periodType,
            now()->format('Ymd_His')
        );

        return Excel::download(new ViolationsStatsExport($violationsStats), $fileName);
    }

    public function exportDrivingTimes(Request $request)
    {
        $periodType = $request->get('period_type', 'month');
        $month = $request->get('month', now()->format('Y-m'));
        $quarter = $request->get('quarter');
        $year = $request->get('year', now()->format('Y'));
        $dateFrom = $request->get('date_from');
        $dateTo = $request->get('date_to');

        $dateRange = $this->getDateRange($periodType, $month, $quarter, $year, $dateFrom, $dateTo);
        $drivingTimesStats = $this->getDrivingTimesStats($dateRange['start'], $dateRange['end']);

        $fileName = sprintf(
            'driving-times-stats-%s-%s.xlsx',
            $periodType,
            now()->format('Ymd_His')
        );

        return Excel::download(new DrivingTimesStatsExport($drivingTimesStats), $fileName);
    }

    public function exportViolationsPdf(Request $request)
    {
        $periodType = $request->get('period_type', 'month');
        $month = $request->get('month', now()->format('Y-m'));
        $quarter = $request->get('quarter');
        $year = $request->get('year', now()->format('Y'));
        $dateFrom = $request->get('date_from');
        $dateTo = $request->get('date_to');
        $includeCharts = $request->get('include_charts', false) == '1';

        $dateRange = $this->getDateRange($periodType, $month, $quarter, $year, $dateFrom, $dateTo);
        $violationsStats = $this->getViolationsStats($dateRange['start'], $dateRange['end']);

        $periodLabel = $this->getPeriodLabel($periodType, $month, $quarter, $year, $dateFrom, $dateTo);

        $pdf = Pdf::loadView('export-center.pdf.violations', [
            'stats' => $violationsStats,
            'periodLabel' => $periodLabel,
            'startDate' => $dateRange['start'],
            'endDate' => $dateRange['end'],
            'includeCharts' => $includeCharts,
        ])
        ->setPaper('a4', 'portrait')
        ->setOption('isHtml5ParserEnabled', true)
        ->setOption('isRemoteEnabled', true);

        $fileName = sprintf(
            'violations-stats-%s-%s.pdf',
            $periodType,
            now()->format('Ymd_His')
        );

        return $pdf->download($fileName);
    }

    public function exportDrivingTimesPdf(Request $request)
    {
        $periodType = $request->get('period_type', 'month');
        $month = $request->get('month', now()->format('Y-m'));
        $quarter = $request->get('quarter');
        $year = $request->get('year', now()->format('Y'));
        $dateFrom = $request->get('date_from');
        $dateTo = $request->get('date_to');
        $includeCharts = $request->get('include_charts', false) == '1';

        $dateRange = $this->getDateRange($periodType, $month, $quarter, $year, $dateFrom, $dateTo);
        $drivingTimesStats = $this->getDrivingTimesStats($dateRange['start'], $dateRange['end']);

        $periodLabel = $this->getPeriodLabel($periodType, $month, $quarter, $year, $dateFrom, $dateTo);

        $pdf = Pdf::loadView('export-center.pdf.driving-times', [
            'stats' => $drivingTimesStats,
            'periodLabel' => $periodLabel,
            'startDate' => $dateRange['start'],
            'endDate' => $dateRange['end'],
            'includeCharts' => $includeCharts,
        ])
        ->setPaper('a4', 'portrait')
        ->setOption('isHtml5ParserEnabled', true)
        ->setOption('isRemoteEnabled', true);

        $fileName = sprintf(
            'driving-times-stats-%s-%s.pdf',
            $periodType,
            now()->format('Ymd_His')
        );

        return $pdf->download($fileName);
    }

    private function getPeriodLabel($periodType, $month, $quarter, $year, $dateFrom, $dateTo): string
    {
        switch ($periodType) {
            case 'month':
                if ($month) {
                    [$y, $m] = explode('-', $month);
                    return Carbon::create($y, $m, 1)->format('F Y');
                }
                return now()->format('F Y');
            case 'quarter':
                if ($quarter && $year) {
                    return $quarter . ' ' . $year;
                }
                return 'Q' . ceil(now()->month / 3) . ' ' . now()->year;
            case 'year':
                return $year ?? now()->year;
            case 'custom':
                if ($dateFrom && $dateTo) {
                    return Carbon::parse($dateFrom)->format('d/m/Y') . ' - ' . Carbon::parse($dateTo)->format('d/m/Y');
                }
                return now()->format('F Y');
            default:
                return now()->format('F Y');
        }
    }

    private function getDateRange($periodType, $month, $quarter, $year, $dateFrom, $dateTo): array
    {
        switch ($periodType) {
            case 'quarter':
                if ($quarter && $year) {
                    $quarterNumber = (int) str_replace('Q', '', $quarter);
                    $startDate = Carbon::create($year, ($quarterNumber - 1) * 3 + 1, 1)->startOfMonth();
                    $endDate = $startDate->copy()->addMonths(2)->endOfMonth();
                } else {
                    $startDate = now()->startOfQuarter();
                    $endDate = now()->endOfQuarter();
                }
                break;

            case 'year':
                $startDate = Carbon::create($year, 1, 1)->startOfYear();
                $endDate = Carbon::create($year, 12, 31)->endOfYear();
                break;

            case 'custom':
                $startDate = $dateFrom ? Carbon::parse($dateFrom)->startOfDay() : now()->startOfMonth();
                $endDate = $dateTo ? Carbon::parse($dateTo)->endOfDay() : now()->endOfMonth();
                break;

            case 'month':
            default:
                if ($month) {
                    [$year, $monthNum] = explode('-', $month);
                    $startDate = Carbon::create($year, $monthNum, 1)->startOfMonth();
                    $endDate = $startDate->copy()->endOfMonth();
                } else {
                    $startDate = now()->startOfMonth();
                    $endDate = now()->endOfMonth();
                }
                break;
        }

        return [
            'start' => $startDate,
            'end' => $endDate,
        ];
    }

    private function getViolationsStats(Carbon $startDate, Carbon $endDate): array
    {
        $query = DriverViolation::whereBetween('violation_date', [$startDate, $endDate]);

        $total = $query->count();
        $confirmed = (clone $query)->where('status', 'confirmed')->count();
        $rejected = (clone $query)->where('status', 'rejected')->count();
        $pending = (clone $query)->where('status', 'pending')->count();

        // Top 5 drivers with violations
        $topDrivers = DriverViolation::whereBetween('violation_date', [$startDate, $endDate])
            ->select('driver_id', DB::raw('COUNT(*) as violation_count'))
            ->with('driver')
            ->groupBy('driver_id')
            ->orderByDesc('violation_count')
            ->limit(5)
            ->get()
            ->map(function ($item) use ($startDate, $endDate) {
                $driver = $item->driver;
                $violations = DriverViolation::where('driver_id', $item->driver_id)
                    ->whereBetween('violation_date', [$startDate, $endDate])
                    ->get();
                
                return [
                    'driver_id' => $item->driver_id,
                    'driver_name' => $driver->full_name ?? 'N/A',
                    'total_count' => $item->violation_count,
                    'confirmed_count' => $violations->where('status', 'confirmed')->count(),
                    'rejected_count' => $violations->where('status', 'rejected')->count(),
                    'pending_count' => $violations->where('status', 'pending')->count(),
                ];
            });

        // Violations by type
        $byType = DriverViolation::whereBetween('violation_date', [$startDate, $endDate])
            ->select('violation_type_id', DB::raw('COUNT(*) as count'))
            ->with('violationType')
            ->groupBy('violation_type_id')
            ->get()
            ->map(function ($item) {
                return [
                    'type_id' => $item->violation_type_id,
                    'type_name' => $item->violationType->name ?? 'N/A',
                    'count' => $item->count,
                ];
            });

        return [
            'total' => $total,
            'confirmed' => $confirmed,
            'rejected' => $rejected,
            'pending' => $pending,
            'top_drivers' => $topDrivers,
            'by_type' => $byType,
            'start_date' => $startDate,
            'end_date' => $endDate,
        ];
    }

    private function getDrivingTimesStats(Carbon $startDate, Carbon $endDate): array
    {
        $query = DriverActivity::whereBetween('activity_date', [$startDate, $endDate]);

        // Get all activities in period
        $activities = $query->get();

        // Calculate total driving hours
        $totalHours = $activities->sum(function ($activity) {
            return $this->timeToDecimal($activity->driving_time);
        });

        // Get unique driver count
        $uniqueDrivers = $activities->pluck('driver_id')->unique()->count();
        $averagePerDriver = $uniqueDrivers > 0 ? $totalHours / $uniqueDrivers : 0;

        // Top 5 drivers by driving hours
        $topDrivers = DriverActivity::whereBetween('activity_date', [$startDate, $endDate])
            ->select('driver_id', DB::raw('COUNT(*) as activity_count'))
            ->with('driver')
            ->groupBy('driver_id')
            ->get()
            ->map(function ($item) use ($startDate, $endDate) {
                $driverActivities = DriverActivity::where('driver_id', $item->driver_id)
                    ->whereBetween('activity_date', [$startDate, $endDate])
                    ->get();
                
                $totalHours = $driverActivities->sum(function ($activity) {
                    return $this->timeToDecimal($activity->driving_time);
                });

                return [
                    'driver_id' => $item->driver_id,
                    'driver_name' => $item->driver->full_name ?? 'N/A',
                    'total_hours' => round($totalHours, 2),
                    'activity_count' => $item->activity_count,
                ];
            })
            ->sortByDesc('total_hours')
            ->take(5)
            ->values();

        return [
            'total_hours' => round($totalHours, 2),
            'average_per_driver' => round($averagePerDriver, 2),
            'unique_drivers' => $uniqueDrivers,
            'top_drivers' => $topDrivers,
            'start_date' => $startDate,
            'end_date' => $endDate,
        ];
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
}

