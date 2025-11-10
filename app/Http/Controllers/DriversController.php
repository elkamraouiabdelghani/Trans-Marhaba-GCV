<?php

namespace App\Http\Controllers;

use App\Models\Driver;
use App\Models\Vehicle;
use App\Models\Flotte;
use App\Models\DriverFormation;
use App\Models\FormationType;
use Illuminate\View\View;
use Illuminate\Http\Request;
use Illuminate\Http\RedirectResponse;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;

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
        $driver->load([
            'assignedVehicle',
            'flotte',
            'formations.formationType',
            'formations.formationProcess.steps',
        ]);

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

        // Integration removed; set placeholders
        $integration = null;
        $integrationProgress = null;

        // Get formations for this driver
        $formations = DriverFormation::where('driver_id', $driver->id)
            ->with(['formationType', 'formationProcess'])
            ->orderBy('created_at', 'desc')
            ->get();

        $formationTypes = FormationType::orderBy('name')->get();

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
            'integrationProgress',
            'formations',
            'formationTypes'
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

    /**
     * Show the form for editing the specified driver.
     */
    public function edit(Driver $driver): View
    {
        try {
            $driver->load(['assignedVehicle', 'flotte']);
            
            // Get vehicles for dropdown
            $vehicles = Vehicle::all();
            
            // Get flottes for dropdown
            $flottes = Flotte::all();
            
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
     * Update the specified driver in storage.
     */
    public function update(Request $request, Driver $driver): RedirectResponse
    {
        try {
            $validated = $request->validate([
                'full_name' => 'required|string|max:255',
                'email' => 'nullable|email|max:255',
                'phone' => 'nullable|string|max:20',
                'address' => 'nullable|string',
                'city' => 'nullable|string|max:255',
                'date_of_birth' => 'nullable|date',
                'cin' => 'nullable|string|max:50',
                'visite_medical' => 'nullable|date',
                'visite_yeux' => 'nullable|date',
                'formation_imd' => 'nullable|date',
                'formation_16_module' => 'nullable|date',
                'date_integration' => 'nullable|date',
                'attestation_travail' => 'nullable|string',
                'carte_profession' => 'nullable|string',
                'n_cnss' => 'nullable|string|max:50',
                'rib' => 'nullable|string|max:50',
                'license_number' => 'required|string|max:50',
                'license_type' => 'nullable|string|max:50',
                'license_issue_date' => 'nullable|date',
                'license_class' => 'nullable|string|max:50',
                'status' => 'nullable|string',
                'assigned_vehicle_id' => 'nullable|exists:vehicles,id',
                'flotte_id' => 'nullable|exists:flottes,id',
                'notes' => 'nullable|string',
            ]);

            $driver->update($validated);
            
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
}


