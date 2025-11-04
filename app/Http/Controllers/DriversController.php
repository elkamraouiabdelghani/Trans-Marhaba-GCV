<?php

namespace App\Http\Controllers;

use App\Models\Driver;
use Illuminate\View\View;
use Illuminate\Http\Request;
use Carbon\Carbon;

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

        return view('drivers.index', compact('drivers', 'total', 'active', 'inactive'));
    }

    public function show(Request $request, Driver $driver): View
    {
        // Load relationships
        $driver->load(['assignedVehicle', 'flotte', 'integration.steps']);

        // Get filter parameters
        $violationType = $request->get('violation_type');
        $severity = $request->get('severity');
        $dateFrom = $request->get('date_from', Carbon::now()->subWeek()->format('Y-m-d'));
        $dateTo = $request->get('date_to', Carbon::now()->format('Y-m-d'));

        // No placeholders: return empty violations until real data is wired
        $violations = [];

        // Calculate totals
        $totalViolations = count($violations);
        $totalDrivingHoursThisWeek = 0;

        // Prepare timeline data for Gantt chart
        $timelineData = [];

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

        // Calculate integration progress if exists
        $integration = $driver->integration;
        $integrationProgress = null;
        $currentStepLabel = null;
        
        if ($integration) {
            $stepsOrder = \App\Models\DriverIntegration::getStepsOrder();
            $completedSteps = $integration->steps()->where('status', 'passed')->count();
            $totalSteps = count($stepsOrder);
            $progressPercentage = $totalSteps > 0 ? round(($completedSteps / $totalSteps) * 100) : 0;
            
            $currentStepLabel = null;
            if ($integration->current_step) {
                $controller = new \App\Http\Controllers\DriverIntegrationController();
                $currentStepLabel = $controller->getStepLabel($integration->current_step);
            }
            
            $integrationProgress = [
                'percentage' => $progressPercentage,
                'completed_steps' => $completedSteps,
                'total_steps' => $totalSteps,
                'current_step' => $integration->current_step,
                'current_step_label' => $currentStepLabel,
                'status' => $integration->status,
                'started_at' => $integration->started_at,
            ];
        }

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
            'integrationProgress'
        ));
    }

    // Removed placeholder violations method; wire actual data when available

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
}


