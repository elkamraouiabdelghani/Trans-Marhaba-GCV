<?php

namespace App\Http\Controllers;

use App\Models\FormationProcess;
use App\Models\FormationStep;
use App\Models\Driver;
use App\Models\DriverFormation;
use App\Models\Formation;
use App\Models\Flotte;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Schema;
use Carbon\Carbon;

class FormationProcessController extends Controller
{
    /**
     * Display a listing of all formation processes.
     */
    public function index(Request $request)
    {
        try {
            $query = FormationProcess::query()
                ->with(['validator', 'steps', 'driver', 'formation', 'flotte'])
                ->orderBy('created_at', 'desc');

            // Filter by status if provided
            if ($request->has('status') && $request->status !== 'all') {
                $query->where('status', $request->status);
            }

            // Filter by formation if provided
            if ($request->has('formation') && $request->formation !== 'all') {
                $query->where('formation_id', $request->formation);
            }

            // Filter by driver if provided
            if ($request->has('driver') && $request->driver !== 'all') {
                $query->where('driver_id', $request->driver);
            }

            // Filter by flotte if provided
            if ($request->has('flotte') && $request->flotte !== 'all') {
                $query->where('flotte_id', $request->flotte);
            }

            $formationProcesses = $query->get();

            // Calculate statistics
            $total = $formationProcesses->count();
            $draft = $formationProcesses->where('status', 'draft')->count();
            $inProgress = $formationProcesses->where('status', 'in_progress')->count();
            $validated = $formationProcesses->where('status', 'validated')->count();
            $rejected = $formationProcesses->where('status', 'rejected')->count();

            // Get filter options
            $drivers = Driver::orderBy('full_name')->get();
            $formations = Formation::active()->orderBy('name')->get();
            $flottes = Flotte::orderBy('name')->get();

            return view('formations.processes.index', compact(
                'formationProcesses',
                'total',
                'draft',
                'inProgress',
                'validated',
                'rejected',
                'drivers',
                'formations',
                'flottes'
            ));
        } catch (\Throwable $e) {
            Log::error('Failed to display formation processes', [
                'error' => $e->getMessage(),
            ]);
            return redirect()->route('formation-processes.index')
                ->with('error', __('messages.error_displaying_formation_processes'));
        }
    }

    /**
     * Show the form for creating a new formation process (Step 1).
     */
    public function create(Request $request)
    {
        $drivers = Driver::orderBy('full_name')->get();
        $formations = Formation::active()
            ->whereNull('flotte_id')
            ->orderBy('name')
            ->get();
        $flottes = Flotte::orderBy('name')->get();

        // Pre-select driver and formation type if provided in query parameters
        $selectedDriverId = $request->get('driver_id');
        $selectedFormationId = $request->get('formation_id');
        $selectedFlotteId = $request->get('flotte_id');

        // If driver is selected but no flotte is specified, try to get driver's flotte
        if ($selectedDriverId && !$selectedFlotteId) {
            $driver = Driver::with('flotte')->find($selectedDriverId);
            if ($driver && $driver->flotte_id) {
                $selectedFlotteId = $driver->flotte_id;
            }
        }

        return view('formations.processes.create', compact('drivers', 'formations', 'flottes', 'selectedDriverId', 'selectedFormationId', 'selectedFlotteId'));
    }

    /**
     * Store a newly created formation process (Step 1 data).
     */
    public function store(Request $request)
    {
        try {
            $validated = $request->validate([
                'site' => 'required|string|max:255',
                'flotte_id' => 'required|exists:flottes,id',
                'formation_id' => 'required|exists:formations,id',
                'driver_id' => 'required|exists:drivers,id',
                'theme' => 'required|string|max:255',
                'identification_besoin' => 'nullable|string',
            ]);

            // If driver doesn't have a flotte assigned, assign the selected flotte
            $driver = Driver::find($validated['driver_id']);
            if ($driver && !$driver->flotte_id) {
                $driver->update(['flotte_id' => $validated['flotte_id']]);
                Log::info('Driver assigned to flotte during formation process creation', [
                    'driver_id' => $driver->id,
                    'flotte_id' => $validated['flotte_id'],
                ]);
            }

            $formationProcess = FormationProcess::create([
                'driver_id' => $validated['driver_id'],
                'formation_id' => $validated['formation_id'],
                'site' => $validated['site'],
                'flotte_id' => $validated['flotte_id'],
                'theme' => $validated['theme'],
                'status' => 'draft',
                'current_step' => 1,
            ]);

            // Create Step 1 record and mark as validated (since it's the initial step)
            FormationStep::create([
                'formation_process_id' => $formationProcess->id,
                'step_number' => 1,
                'step_data' => $validated,
                'status' => 'validated',
                'validated_by' => Auth::id(),
                'validated_at' => now(),
            ]);

            // Update formation process to move to step 2
            $formationProcess->update([
                'current_step' => 2,
                'status' => 'in_progress',
            ]);

            // Automatically redirect to Step 2
            return redirect()->route('formation-processes.step', ['formationProcess' => $formationProcess->id, 'stepNumber' => 2])
                ->with('success', __('messages.step_saved'));
        } catch (\Throwable $e) {
            Log::error('Failed to create formation process', [
                'error' => $e->getMessage(),
            ]);
            return back()
                ->withInput()
                ->with('error', __('messages.step_save_error'));
        }
    }

    /**
     * Display the specified formation process progress.
     */
    public function show(FormationProcess $formationProcess)
    {
        try {
            $formationProcess->load(['steps', 'validator', 'driver', 'formation', 'flotte']);

            // Get all steps with their status
            $steps = [];
            for ($i = 1; $i <= 8; $i++) {
                $step = $formationProcess->getStep($i);
                $steps[$i] = [
                    'step' => $step,
                    'can_access' => $formationProcess->canProceedToStep($i),
                    'is_validated' => $step && $step->isValidated(),
                    'is_rejected' => $step && $step->isRejected(),
                    'is_pending' => $step && $step->isPending(),
                ];
            }

            return view('formations.processes.show', compact('formationProcess', 'steps'));
        } catch (\Throwable $e) {
            Log::error('Failed to display formation process', [
                'error' => $e->getMessage(),
            ]);
            return redirect()->route('formation-processes.index')
                ->with('error', __('messages.error_displaying_formation_process'));
        }
    }

    /**
     * Show the form for a specific step.
     */
    public function step(FormationProcess $formationProcess, int $stepNumber)
    {
        try {
            // Validate step number
            if ($stepNumber < 1 || $stepNumber > 7) {
                return redirect()->route('formation-processes.show', $formationProcess->id)
                    ->with('error', __('messages.invalid_step'));
            }

            // Check if formation process is rejected
            if ($formationProcess->isRejected()) {
                return redirect()->route('formation-processes.show', $formationProcess->id)
                    ->with('error', __('messages.formation_process_rejected'));
            }

            $formationProcess->load(['steps', 'validator', 'driver', 'formation', 'flotte']);

            // Get all steps with their status
            $steps = [];
            for ($i = 1; $i <= 7; $i++) {
                $step = $formationProcess->getStep($i);
                $steps[$i] = [
                    'step' => $step,
                    'can_access' => $formationProcess->canProceedToStep($i),
                    'is_validated' => $step && $step->isValidated(),
                    'is_rejected' => $step && $step->isRejected(),
                    'is_pending' => $step && $step->isPending(),
                ];
            }

            if ($formationProcess->isValidated()) {
                // Allow viewing validated formation processes
                $step = $formationProcess->getStep($stepNumber);
                if (!$step) {
                    return redirect()->route('formation-processes.show', $formationProcess->id)
                        ->with('error', __('messages.step_not_found'));
                }
                return view('formations.processes.show', compact('formationProcess', 'stepNumber', 'step', 'steps'));
            }

            // Get the step
            $step = $formationProcess->getStep($stepNumber);
            
            $canAccess = false;
        
            if ($stepNumber === 1) {
                $canAccess = true;
            } elseif ($step && $step->isValidated()) {
                // Can access validated steps for viewing
                $canAccess = true;
            } elseif ($formationProcess->current_step === $stepNumber) {
                // Can access current step for editing
                $canAccess = true;
            } else {
                // Check if all previous steps are validated
                $canAccess = $formationProcess->canProceedToStep($stepNumber);
            }

            if (!$canAccess) {
                return redirect()->route('formation-processes.show', $formationProcess->id)
                    ->with('error', __('messages.must_validate_previous_steps'));
            }

            // Get or create step record if it doesn't exist
            if (!$step) {
                $step = FormationStep::create([
                    'formation_process_id' => $formationProcess->id,
                    'step_number' => $stepNumber,
                    'status' => 'pending',
                ]);
            }

            return view('formations.processes.show', compact('formationProcess', 'stepNumber', 'step', 'steps'));
        } catch (\Throwable $e) {
            Log::error('Failed to display step', [
                'error' => $e->getMessage(),
            ]);
            return redirect()->route('formation-processes.index')
                ->with('error', __('messages.error_displaying_step'));
        }
    }

    /**
     * Save step data.
     */
    public function saveStep(Request $request, FormationProcess $formationProcess, int $stepNumber)
    {
        try {
            // Validate step number
            if ($stepNumber < 1 || $stepNumber > 7) {
                return redirect()->route('formation-processes.show', $formationProcess->id)
                    ->with('error', __('messages.invalid_step'));
            }

            // Check if formation process is rejected
            if ($formationProcess->isRejected()) {
                return redirect()->route('formation-processes.show', $formationProcess->id)
                    ->with('error', __('messages.formation_process_rejected'));
            }

            // Get the step
            $step = $formationProcess->getStep($stepNumber);
        
            // Access rules for saving:
            // 1. Can always update validated steps (even if formation process is finalized)
            // 2. Can save current step (if not finalized)
            // 3. Cannot save future steps unless previous are validated (if not finalized)
            $canSave = false;
            
            if ($step && $step->isValidated()) {
                // Can always update validated steps, even if formation process is finalized
                $canSave = true;
            } elseif ($formationProcess->isValidated()) {
                // If formation process is finalized, only allow updates to validated steps
                return redirect()->route('formation-processes.show', $formationProcess->id)
                    ->with('error', __('messages.formation_process_already_finalized'));
            } elseif ($formationProcess->current_step === $stepNumber) {
                // Can always save current step
                $canSave = true;
            } elseif ($stepNumber === 1) {
                // Can always save step 1
                $canSave = true;
            } else {
                // Check if can proceed to this step (all previous validated)
                $canSave = $formationProcess->canProceedToStep($stepNumber);
            }

            if (!$canSave) {
                return redirect()->route('formation-processes.show', $formationProcess->id)
                    ->with('error', __('messages.cannot_modify_step'));
            }

            // Get or create step
            $step = $formationProcess->getStep($stepNumber);
            if (!$step) {
                $step = FormationStep::create([
                    'formation_process_id' => $formationProcess->id,
                    'step_number' => $stepNumber,
                    'status' => 'pending',
                ]);
            }

            // Validate based on step number
            $validated = $this->validateStepData($request, $stepNumber);

            // If Step 1 is being saved, update formation process and assign flotte to driver if needed
            if ($stepNumber === 1 && isset($validated['driver_id']) && isset($validated['flotte_id'])) {
                // Update formation process with step 1 data
                $formationProcess->update([
                    'driver_id' => $validated['driver_id'],
                    'formation_id' => $validated['formation_id'] ?? $formationProcess->formation_id,
                    'site' => $validated['site'] ?? $formationProcess->site,
                    'flotte_id' => $validated['flotte_id'],
                    'theme' => $validated['theme'] ?? $formationProcess->theme,
                ]);

                // If driver doesn't have a flotte, assign the selected flotte
                $driver = Driver::find($validated['driver_id']);
                if ($driver && !$driver->flotte_id) {
                    $driver->update(['flotte_id' => $validated['flotte_id']]);
                    Log::info('Driver assigned to flotte during step 1 save', [
                        'driver_id' => $driver->id,
                        'flotte_id' => $validated['flotte_id'],
                        'formation_process_id' => $formationProcess->id,
                    ]);
                }
            }

            // Handle file uploads for specific steps
            if ($stepNumber === 4) {
                $validated = $this->handleStep4Uploads($request, $validated);
            } elseif ($stepNumber === 5) {
                $validated = $this->handleStep5Uploads($request, $validated);
            } elseif ($stepNumber === 7) {
                $validated = $this->handleStep7Uploads($request, $validated);
            }

            // Update step data
            $step->setStepDataArray($validated);
            $step->save();

            // Refresh step to get latest data
            $step->refresh();

            // Update formation process status
            if ($formationProcess->status === 'draft') {
                $formationProcess->update(['status' => 'in_progress']);
            }

            // Refresh formation process to get latest data
            $formationProcess->refresh();

            // Check if this is a "validate and next" action or "update" action
            $submitAction = $request->input('submit_action');
            if ($submitAction === 'update') {
                // Just save the data without validating or moving to next step
                return redirect()->route('formation-processes.step', ['formationProcess' => $formationProcess->id, 'stepNumber' => $stepNumber])
                    ->with('success', __('messages.step_updated_successfully'));
            }
            if ($submitAction === 'validate') {
                // Refresh step and integration to get latest data
                $step->refresh();
                $formationProcess->refresh();
                $formationProcess->load('steps');

                // Validate the step
                try {
                    // Step 1: All fields must be filled (basic validation)
                    if ($stepNumber === 1) {
                        $stepData = $step->step_data ?? [];
                        $requiredFields = ['site', 'flotte_id', 'formation_id', 'driver_id', 'theme'];
                        foreach ($requiredFields as $field) {
                            if (empty($stepData[$field])) {
                                return redirect()->route('formation-processes.step', ['formationProcess' => $formationProcess->id, 'stepNumber' => $stepNumber])
                                    ->with('error', __('messages.all_fields_step1_required'));
                            }
                        }
                    }

                    // Step 2: date_prevu required
                    if ($stepNumber === 2) {
                        $stepData = $step->step_data ?? [];
                        if (empty($stepData['date_prevu'])) {
                            return redirect()->route('formation-processes.step', ['formationProcess' => $formationProcess->id, 'stepNumber' => $stepNumber])
                                ->with('error', __('messages.date_prevu_required'));
                        }
                    }

                    // Step 3: Budget validation required
                    if ($stepNumber === 3) {
                        $stepData = $step->step_data ?? [];
                        if (empty($stepData)) {
                            return redirect()->route('formation-processes.step', ['formationProcess' => $formationProcess->id, 'stepNumber' => $stepNumber])
                                ->with('error', __('messages.please_save_step_before_validation'));
                        }
                        if (empty($stepData['budget_approved'])) {
                            return redirect()->route('formation-processes.step', ['formationProcess' => $formationProcess->id, 'stepNumber' => $stepNumber])
                                ->with('error', __('messages.budget_approval_required'));
                        }
                    }

                    // Step 4: animateur required
                    if ($stepNumber === 4) {
                        $stepData = $step->step_data ?? [];
                        if (empty($stepData['animateur'])) {
                            return redirect()->route('formation-processes.step', ['formationProcess' => $formationProcess->id, 'stepNumber' => $stepNumber])
                                ->with('error', __('messages.animateur_required'));
                        }
                    }

                    // Step 5: Attendance sheet required (formerly step 6)
                    if ($stepNumber === 5) {
                        $stepData = $step->step_data ?? [];
                        if (empty($stepData)) {
                            return redirect()->route('formation-processes.step', ['formationProcess' => $formationProcess->id, 'stepNumber' => $stepNumber])
                                ->with('error', __('messages.please_save_step_before_validation'));
                        }
                    }

                    // Step 6: note_formation and feedback_formation required (formerly step 7)
                    if ($stepNumber === 6) {
                        $stepData = $step->step_data ?? [];
                        if (empty($stepData)) {
                            return redirect()->route('formation-processes.step', ['formationProcess' => $formationProcess->id, 'stepNumber' => $stepNumber])
                                ->with('error', __('messages.please_save_step_before_validation'));
                        }
                        if (empty($stepData['note_formation']) || empty($stepData['feedback_formation'])) {
                            return redirect()->route('formation-processes.step', ['formationProcess' => $formationProcess->id, 'stepNumber' => $stepNumber])
                                ->with('error', __('messages.note_and_feedback_required'));
                        }
                    }

                    // Step 7: Final validation info required (formerly step 8)
                    if ($stepNumber === 7) {
                        $stepData = $step->step_data ?? [];
                        if (empty($stepData)) {
                            return redirect()->route('formation-processes.step', ['formationProcess' => $formationProcess->id, 'stepNumber' => $stepNumber])
                                ->with('error', __('messages.please_save_step_before_validation'));
                        }
                        
                        if (empty($stepData['final_validation_date']) || empty($stepData['validated_by'])) {
                            return redirect()->route('formation-processes.step', ['formationProcess' => $formationProcess->id, 'stepNumber' => $stepNumber])
                                ->with('error', __('messages.final_validation_info_required'));
                        }
                    }

                    // Validate the step
                    $step->validateStep(Auth::id(), $request->input('notes'));

                    // Move to next step if this step is validated and it's the current step
                    if ($stepNumber < 7 && $formationProcess->current_step === $stepNumber) {
                        $formationProcess->moveToNextStep();
                        
                        // Automatically redirect to next step after validation
                        return redirect()->route('formation-processes.step', ['formationProcess' => $formationProcess->id, 'stepNumber' => $stepNumber + 1])
                            ->with('success', __('messages.step_validated_successfully') . ' ' . __('messages.redirecting_to_next_step'));
                    }

                    // If not redirected to next step, redirect back to current step
                    return redirect()->route('formation-processes.step', ['formationProcess' => $formationProcess->id, 'stepNumber' => $stepNumber])
                        ->with('success', __('messages.step_validated_successfully'));
                } catch (\Throwable $e) {
                    Log::error('Failed to validate step in saveStep', [
                        'formation_process_id' => $formationProcess->id,
                        'step_number' => $stepNumber,
                        'error' => $e->getMessage(),
                    ]);
                    return back()->with('error', __('messages.error_validating_step'));
                }
            }

            // Redirect back to the step page after saving
            return redirect()->route('formation-processes.step', ['formationProcess' => $formationProcess->id, 'stepNumber' => $stepNumber])
                ->with('success', __('messages.step_saved'));
        } catch (\Throwable $e) {
            Log::error('Failed to save step', [
                'formation_process_id' => $formationProcess->id,
                'step_number' => $stepNumber,
                'error' => $e->getMessage(),
            ]);
            return back()
                ->withInput()
                ->with('error', __('messages.step_save_error'));
        }
    }

    /**
     * Validate a step (admin only).
     */
    public function validateStep(Request $request, FormationProcess $formationProcess, int $stepNumber)
    {
        try {
            $step = $formationProcess->getStep($stepNumber);
            if (!$step) {
                return redirect()->route('formation-processes.step', ['formationProcess' => $formationProcess->id, 'stepNumber' => $stepNumber])
                    ->with('error', __('messages.step_not_found'));
            }

            // Step 1: All fields must be filled (basic validation)
            if ($stepNumber === 1) {
                $stepData = $step->step_data ?? [];
                $requiredFields = ['site', 'flotte_id', 'formation_id', 'driver_id', 'theme'];
                foreach ($requiredFields as $field) {
                    if (empty($stepData[$field])) {
                        return redirect()->route('formation-processes.step', ['formationProcess' => $formationProcess->id, 'stepNumber' => $stepNumber])
                            ->with('error', __('messages.all_fields_step1_required'));
                    }
                }
            }

            // Step 2: date_prevu required
            if ($stepNumber === 2) {
                $stepData = $step->step_data ?? [];
                if (empty($stepData['date_prevu'])) {
                    return redirect()->route('formation-processes.step', ['formationProcess' => $formationProcess->id, 'stepNumber' => $stepNumber])
                        ->with('error', __('messages.date_prevu_required'));
                }
            }

            // Step 3: Budget validation required
            if ($stepNumber === 3) {
                $stepData = $step->step_data ?? [];
                if (empty($stepData)) {
                    return redirect()->route('formation-processes.step', ['formationProcess' => $formationProcess->id, 'stepNumber' => $stepNumber])
                        ->with('error', __('messages.please_save_step_before_validation'));
                }
                if (empty($stepData['budget_approved'])) {
                    return redirect()->route('formation-processes.step', ['formationProcess' => $formationProcess->id, 'stepNumber' => $stepNumber])
                        ->with('error', __('messages.budget_approval_required'));
                }
            }

            // Step 4: animateur required
            if ($stepNumber === 4) {
                $stepData = $step->step_data ?? [];
                if (empty($stepData['animateur'])) {
                    return redirect()->route('formation-processes.step', ['formationProcess' => $formationProcess->id, 'stepNumber' => $stepNumber])
                        ->with('error', __('messages.animateur_required'));
                }
            }

            // Step 5: Attendance sheet required (formerly step 6)
            if ($stepNumber === 5) {
                $stepData = $step->step_data ?? [];
                if (empty($stepData)) {
                    return redirect()->route('formation-processes.step', ['formationProcess' => $formationProcess->id, 'stepNumber' => $stepNumber])
                        ->with('error', __('messages.please_save_step_before_validation'));
                }
            }

            // Step 6: note_formation and feedback_formation required (formerly step 7)
            if ($stepNumber === 6) {
                $stepData = $step->step_data ?? [];
                if (empty($stepData)) {
                    return redirect()->route('formation-processes.step', ['formationProcess' => $formationProcess->id, 'stepNumber' => $stepNumber])
                        ->with('error', __('messages.please_save_step_before_validation'));
                }
                if (empty($stepData['note_formation']) || empty($stepData['feedback_formation'])) {
                    return redirect()->route('formation-processes.step', ['formationProcess' => $formationProcess->id, 'stepNumber' => $stepNumber])
                        ->with('error', __('messages.note_and_feedback_required'));
                }
            }

            // Step 7: Final validation info required (formerly step 8)
            if ($stepNumber === 7) {
                $stepData = $step->step_data ?? [];
                if (empty($stepData)) {
                    return redirect()->route('formation-processes.step', ['formationProcess' => $formationProcess->id, 'stepNumber' => $stepNumber])
                        ->with('error', __('messages.please_save_step_before_validation'));
                }
                
                if (empty($stepData['final_validation_date']) || empty($stepData['validated_by'])) {
                    return redirect()->route('formation-processes.step', ['formationProcess' => $formationProcess->id, 'stepNumber' => $stepNumber])
                        ->with('error', __('messages.final_validation_info_required'));
                }
            }

            $step->validateStep(Auth::id(), $request->input('notes'));

            // Move to next step if this step is validated and it's the current step
            if ($stepNumber < 7 && $formationProcess->current_step === $stepNumber) {
                $formationProcess->moveToNextStep();
                
                // Automatically redirect to next step after validation
                return redirect()->route('formation-processes.step', ['formationProcess' => $formationProcess->id, 'stepNumber' => $stepNumber + 1])
                    ->with('success', __('messages.step_validated_successfully') . ' ' . __('messages.redirecting_to_next_step'));
            }

            // If not redirected to next step, redirect back to current step
            return redirect()->route('formation-processes.step', ['formationProcess' => $formationProcess->id, 'stepNumber' => $stepNumber])
                ->with('success', __('messages.step_validated_successfully'));
        } catch (\Throwable $e) {
            Log::error('Failed to validate step', [
                'formation_process_id' => $formationProcess->id,
                'step_number' => $stepNumber,
                'error' => $e->getMessage(),
            ]);
            return back()->with('error', __('messages.error_validating_step'));
        }
    }

    /**
     * Reject a step (admin only).
     */
    public function rejectStep(Request $request, FormationProcess $formationProcess, int $stepNumber)
    {
        try {
            $step = $formationProcess->getStep($stepNumber);
            if (!$step) {
                return redirect()->route('formation-processes.show', $formationProcess->id)
                    ->with('error', __('messages.step_not_found'));
            }

            $request->validate([
                'rejection_reason' => 'required|string',
            ]);

            $step->rejectStep(
                $request->input('rejection_reason'),
                Auth::id(),
                $request->input('notes')
            );

            // Reject formation process if critical step is rejected
            if (in_array($stepNumber, [3, 6])) {
                $this->rejectFormationProcess($formationProcess, $request->input('rejection_reason'), Auth::id());
            }

            return redirect()->route('formation-processes.show', $formationProcess->id)
                ->with('success', __('messages.step_rejected'));
        } catch (\Throwable $e) {
            Log::error('Failed to reject step', [
                'formation_process_id' => $formationProcess->id,
                'step_number' => $stepNumber,
                'error' => $e->getMessage(),
            ]);
            return back()->with('error', __('messages.error_rejecting_step'));
        }
    }

    /**
     * Finalize formation process (Step 8 completion - create/update DriverFormation).
     */
    public function finalize(FormationProcess $formationProcess)
    {
        try {
            // Check if Step 7 is validated (formerly step 8)
            $step7 = $formationProcess->getStep(7);
            if (!$step7 || !$step7->isValidated()) {
                return redirect()->route('formation-processes.show', $formationProcess->id)
                    ->with('error', __('messages.step7_must_be_validated'));
            }

            // Check if all steps are validated
            for ($i = 1; $i <= 7; $i++) {
                $step = $formationProcess->getStep($i);
                if (!$step || !$step->isValidated()) {
                    return redirect()->route('formation-processes.show', $formationProcess->id)
                        ->with('error', __('messages.all_steps_must_be_validated'));
                }
            }

            // Fetch step 7 data for final validation details
            $step7Data = $step7->step_data ?? [];
            $finalValidationDate = null;

            if (!empty($step7Data['final_validation_date'])) {
                try {
                    $finalValidationDate = Carbon::parse($step7Data['final_validation_date'])->startOfDay();
                } catch (\Throwable $e) {
                    Log::warning('Unable to parse final validation date for formation process', [
                        'formation_process_id' => $formationProcess->id,
                        'raw_value' => $step7Data['final_validation_date'],
                        'error' => $e->getMessage(),
                    ]);
                }
            }

            // Create or update DriverFormation
            $driverFormation = $this->createOrUpdateDriverFormation($formationProcess);

            // Update driver formation validation status for associated formation
            DriverFormation::where('formation_process_id', $formationProcess->id)
                ->where('formation_id', $formationProcess->formation_id)
                ->update(['validation_status' => 'validated']);

            // Generate formation report
            $reportPath = $this->generateFormationReport($formationProcess);

            if ($reportPath) {
                if ($driverFormation) {
                    $driverFormation->update(['certificate_path' => $reportPath]);
                }

                if ($step7) {
                    $step7->setStepDataArray([
                        'completion_certificate_path' => $reportPath,
                        'report_path' => $reportPath,
                    ]);
                    $step7->save();
                }
            }

            // Mark formation process as validated
            $formationProcess->update([
                'status' => 'validated',
                'validated_at' => $finalValidationDate ?? now(),
                'validated_by' => $step7->validated_by ?? Auth::id(),
            ]);
            
            // Refresh to get latest data
            $formationProcess->refresh();

            Log::info('Formation process finalized', [
                'formation_process_id' => $formationProcess->id,
                'validated_by' => $formationProcess->validated_by,
                'validated_at' => $formationProcess->validated_at,
                'driver_formation_id' => $formationProcess->driver_formation_id,
            ]);

            return redirect()->route('drivers.show', $formationProcess->driver_id)
                ->with('success', __('messages.formation_process_finalized_successfully'));
        } catch (\Throwable $e) {
            Log::error('Failed to finalize formation process', [
                'formation_process_id' => $formationProcess->id,
                'error' => $e->getMessage(),
            ]);
            return redirect()->route('formation-processes.show', $formationProcess->id)
                ->with('error', __('messages.error_finalizing_formation_process'));
        }
    }

    /**
     * Validate step data based on step number.
     */
    private function validateStepData(Request $request, int $stepNumber): array
    {
        try {
            $rules = [];

            switch ($stepNumber) {
                case 2:
                    $rules = [
                        'theme' => 'nullable|string|max:255',
                        'date_prevu' => 'required|date',
                        'plan_details' => 'nullable|string',
                    ];
                    break;

                case 3:
                    $rules = [
                        'budget_amount' => 'nullable|numeric|min:0',
                        'budget_approved' => 'required|boolean',
                        'validation_notes' => 'nullable|string',
                        'validated_by_dg' => 'nullable|string|max:255',
                        'validated_by_dga' => 'nullable|string|max:255',
                    ];
                    break;

                case 4:
                    $rules = [
                        'animateur' => 'required|string|max:255',
                        'trainer_contract_path' => 'nullable|string',
                        'training_program_path' => 'nullable|string',
                        'trainer_contract' => 'nullable|file|mimes:pdf,doc,docx|max:5120',
                        'training_program' => 'nullable|file|mimes:pdf,doc,docx|max:5120',
                    ];
                    break;

                case 5:
                    $rules = [
                        'actual_start_date' => 'nullable|date',
                        'attendance_sheet_path' => 'nullable|string',
                        'training_materials_path' => 'nullable|string',
                        'attendance_sheet' => 'nullable|file|mimes:pdf,doc,docx,xlsx|max:5120',
                        'training_materials' => 'nullable|file|mimes:pdf,doc,docx|max:5120',
                        'delivery_notes' => 'nullable|string',
                    ];
                    break;

                case 6:
                    $rules = [
                        'note_formation' => 'required|integer|min:0|max:100',
                        'feedback_formation' => 'required|string',
                        'feedback_tbx' => 'nullable|string',
                        'nbr' => 'nullable|string',
                        'evaluation_questionnaire_path' => 'nullable|string',
                        'evaluation_questionnaire' => 'nullable|file|mimes:pdf,doc,docx|max:5120',
                    ];
                    break;

                case 7:
                    $rules = [
                        'final_validation_date' => 'required|date',
                        'validated_by' => 'required|string|max:255',
                        'application_notes' => 'nullable|string',
                        'follow_up_required' => 'nullable|boolean',
                        'completion_certificate_path' => 'nullable|string',
                        'completion_certificate' => 'nullable|file|mimes:pdf,doc,docx|max:5120',
                    ];
                    break;
            }

            return $request->validate($rules);
        } catch (\Throwable $e) {
            Log::error('Failed to validate step data', [
                'error' => $e->getMessage(),
            ]);
            throw $e;
        }
    }

    /**
     * Handle file uploads for Step 4.
     */
    private function handleStep4Uploads(Request $request, array $validated): array
    {
        try {
            // Handle trainer contract upload
            if ($request->hasFile('trainer_contract')) {
                $contractPath = $request->file('trainer_contract')->store('formation/contracts', 'uploads');
                $validated['trainer_contract_path'] = $contractPath;
            }

            // Handle training program upload
            if ($request->hasFile('training_program')) {
                $programPath = $request->file('training_program')->store('formation/programs', 'uploads');
                $validated['training_program_path'] = $programPath;
            }

            return $validated;
        } catch (\Throwable $e) {
            Log::error('Failed to handle step 4 uploads', [
                'error' => $e->getMessage(),
            ]);
            throw $e;
        }
    }

    /**
     * Handle file uploads for Step 5 (formerly Step 6).
     */
    private function handleStep5Uploads(Request $request, array $validated): array
    {
        try {
            // Handle attendance sheet upload
            if ($request->hasFile('attendance_sheet')) {
                $attendancePath = $request->file('attendance_sheet')->store('formation/attendance', 'uploads');
                $validated['attendance_sheet_path'] = $attendancePath;
            }

            // Handle training materials upload
            if ($request->hasFile('training_materials')) {
                $materialsPath = $request->file('training_materials')->store('formation/materials', 'uploads');
                $validated['training_materials_path'] = $materialsPath;
            }

            return $validated;
        } catch (\Throwable $e) {
            Log::error('Failed to handle step 6 uploads', [
                'error' => $e->getMessage(),
            ]);
            throw $e;
        }
    }

    /**
     * Handle file uploads for Step 7 (formerly Step 8).
     */
    private function handleStep7Uploads(Request $request, array $validated): array
    {
        try {
            // Handle completion certificate upload
            if ($request->hasFile('completion_certificate')) {
                $certificatePath = $request->file('completion_certificate')->store('formation/certificates', 'uploads');
                $validated['completion_certificate_path'] = $certificatePath;
            }

            return $validated;
        } catch (\Throwable $e) {
            Log::error('Failed to handle step 8 uploads', [
                'error' => $e->getMessage(),
            ]);
            throw $e;
        }
    }

    /**
     * Create or update DriverFormation record.
     */
    private function createOrUpdateDriverFormation(FormationProcess $formationProcess): ?DriverFormation
    {
        try {
            // Get step data
            $step1 = $formationProcess->getStep(1);
            $step2 = $formationProcess->getStep(2);
            $step5 = $formationProcess->getStep(5); // Training Delivery (formerly step 6)
            $step6 = $formationProcess->getStep(6); // Training Evaluation (formerly step 7)
            $step7 = $formationProcess->getStep(7); // Final Validation (formerly step 8)

            $progressPercent = $step6 ? (int) ($step6->getStepData('note_formation') ?? 0) : 0;

            // Attempt to retrieve existing DriverFormation linked to this process
            $driverFormation = null;

            if ($formationProcess->driver_formation_id) {
                $driverFormation = DriverFormation::find($formationProcess->driver_formation_id);
            }

            if (!$driverFormation) {
                $driverFormation = DriverFormation::where('formation_process_id', $formationProcess->id)
                    ->where('driver_id', $formationProcess->driver_id)
                    ->where('formation_id', $formationProcess->formation_id)
                    ->first();
            }

            if ($driverFormation) {
                $driverFormation->fill([
                    'status' => $step5 && $step5->getStepData('actual_start_date') ? 'done' : 'planned',
                    'planned_at' => $step2 ? $step2->getStepData('date_prevu') : null,
                    'done_at' => $step5 ? $step5->getStepData('actual_start_date') : null,
                    'certificate_path' => $step7 ? $step7->getStepData('completion_certificate_path') : null,
                    'notes' => $step6 ? $step6->getStepData('feedback_formation') : null,
                    'progress_percent' => $progressPercent,
                    'validation_status' => 'validated',
                ])->save();
            } else {
                // Create new DriverFormation
                $driverFormation = DriverFormation::create([
                    'driver_id' => $formationProcess->driver_id,
                    'formation_id' => $formationProcess->formation_id,
                    'formation_process_id' => $formationProcess->id,
                    'status' => $step5 && $step5->getStepData('actual_start_date') ? 'done' : 'planned',
                    'planned_at' => $step2 ? $step2->getStepData('date_prevu') : null,
                    'done_at' => $step5 ? $step5->getStepData('actual_start_date') : null,
                    'certificate_path' => $step7 ? $step7->getStepData('completion_certificate_path') : null,
                    'notes' => $step6 ? $step6->getStepData('feedback_formation') : null,
                    'progress_percent' => $progressPercent,
                    'validation_status' => 'validated',
                ]);
            }

            // Link DriverFormation to FormationProcess when the column exists
            if ($driverFormation && Schema::hasColumn('formation_processes', 'driver_formation_id')) {
                $formationProcess->update(['driver_formation_id' => $driverFormation->id]);
            }

            if ($driverFormation) {
                Log::info('DriverFormation created/updated', [
                    'formation_process_id' => $formationProcess->id,
                    'driver_formation_id' => $driverFormation->id,
                ]);
            }

            return $driverFormation;
        } catch (\Throwable $e) {
            Log::error('Failed to create/update DriverFormation', [
                'formation_process_id' => $formationProcess->id,
                'error' => $e->getMessage(),
            ]);
            throw $e;
        }
    }

    /**
     * Reject formation process and clean up related data.
     */
    private function rejectFormationProcess(FormationProcess $formationProcess, string $reason, ?int $userId = null): void
    {
        try {
            Log::info('Rejecting formation process', [
                'formation_process_id' => $formationProcess->id,
                'reason' => $reason,
                'user_id' => $userId,
            ]);

            // Delete all formation steps
            $stepsCount = $formationProcess->steps()->count();
            $formationProcess->steps()->delete();
            Log::info('Formation steps deleted', [
                'formation_process_id' => $formationProcess->id,
                'steps_deleted' => $stepsCount,
            ]);

            // Mark formation process as rejected
            $formationProcess->markAsRejected($reason, $userId);
            
            // Reset formation process to initial state
            $updatePayload = ['current_step' => 1];

            if (Schema::hasColumn('formation_processes', 'driver_formation_id')) {
                $updatePayload['driver_formation_id'] = null;
            }

            $formationProcess->update($updatePayload);

            // Refresh to get latest data
            $formationProcess->refresh();

            Log::info('Formation process rejected successfully', [
                'formation_process_id' => $formationProcess->id,
                'status' => $formationProcess->status,
                'rejected_at' => $formationProcess->rejected_at,
                'rejection_reason' => $formationProcess->rejection_reason,
            ]);
        } catch (\Throwable $e) {
            Log::error('Failed to reject formation process', [
                'formation_process_id' => $formationProcess->id,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);
            throw $e;
        }
    }

    /**
     * Generate a PDF report for the finalized formation process.
     */
    private function generateFormationReport(FormationProcess $formationProcess): ?string
    {
        try {
            $formationProcess->loadMissing(['driver', 'formation', 'flotte', 'steps']);

            $step1Data = optional($formationProcess->getStep(1))->step_data ?? [];
            $step2Data = optional($formationProcess->getStep(2))->step_data ?? [];
            $step4Data = optional($formationProcess->getStep(4))->step_data ?? [];
            $step5Data = optional($formationProcess->getStep(5))->step_data ?? []; // Training Delivery (formerly step 6)
            $step6Data = optional($formationProcess->getStep(6))->step_data ?? []; // Training Evaluation (formerly step 7)

            // Determine status based on actual_start_date
            $statusLabel = !empty($step5Data['actual_start_date']) 
                ? __('messages.status_realized')
                : __('messages.status_planned');

            $data = [
                'site' => $step1Data['site'] ?? $formationProcess->site ?? __('messages.not_available'),
                'flotte' => $formationProcess->flotte?->name ?? __('messages.not_available'),
                'type' => $formationProcess->formation?->name ?? __('messages.not_available'),
                'driver' => $formationProcess->driver?->full_name ?? __('messages.not_available'),
                'theme' => $step1Data['theme'] ?? $formationProcess->theme ?? __('messages.not_available'),
                'animateur' => $step4Data['animateur'] ?? __('messages.not_available'),
                'start_date' => $this->formatReportDate($step5Data['actual_start_date'] ?? null),
                'status' => $statusLabel,
                'date_prevu' => $this->formatReportDate($step2Data['date_prevu'] ?? null),
                'feedback_tbx' => $step6Data['feedback_tbx'] ?? __('messages.not_available'),
                'nbr' => $step6Data['nbr'] ?? __('messages.not_available'),
                'note_formation' => isset($step6Data['note_formation'])
                    ? $step6Data['note_formation'] . '%'
                    : __('messages.not_available'),
                'feedback_formation' => $step6Data['feedback_formation'] ?? __('messages.not_available'),
                'generated_at' => now()->format('d/m/Y H:i'),
            ];

            $pdf = Pdf::loadView('formations.processes.reports.summary', $data)->setPaper('a4');

            $directory = 'formation-reports';
            if (!Storage::disk('uploads')->exists($directory)) {
                Storage::disk('uploads')->makeDirectory($directory);
            }

            $fileName = sprintf(
                '%s/formation-report-%d-%s.pdf',
                $directory,
                $formationProcess->id,
                now()->format('YmdHis')
            );

            Storage::disk('uploads')->put($fileName, $pdf->output());

            Log::info('Formation report generated', [
                'formation_process_id' => $formationProcess->id,
                'report_path' => $fileName,
            ]);

            return $fileName;
        } catch (\Throwable $e) {
            Log::error('Failed to generate formation report', [
                'formation_process_id' => $formationProcess->id,
                'error' => $e->getMessage(),
            ]);

            return null;
        }
    }

    /**
     * Format dates for report output.
     */
    private function formatReportDate(?string $value): string
    {
        if (empty($value)) {
            return __('messages.not_available');
        }

        try {
            return Carbon::parse($value)->format('d/m/Y');
        } catch (\Throwable $e) {
            Log::warning('Failed to format report date', [
                'value' => $value,
                'error' => $e->getMessage(),
            ]);

            return __('messages.not_available');
        }
    }
}
