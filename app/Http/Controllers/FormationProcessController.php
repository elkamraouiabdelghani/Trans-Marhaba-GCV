<?php

namespace App\Http\Controllers;

use App\Models\FormationProcess;
use App\Models\FormationStep;
use App\Models\Driver;
use App\Models\DriverFormation;
use App\Models\FormationType;
use App\Models\Flotte;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Carbon\Carbon;

class FormationProcessController extends Controller
{
    /**
     * Display a listing of all formation processes.
     */
    public function index(Request $request): View
    {
        try {
            $query = FormationProcess::query()
                ->with(['validator', 'steps', 'driver', 'formationType', 'flotte'])
                ->orderBy('created_at', 'desc');

            // Filter by status if provided
            if ($request->has('status') && $request->status !== 'all') {
                $query->where('status', $request->status);
            }

            // Filter by formation type if provided
            if ($request->has('formation_type') && $request->formation_type !== 'all') {
                $query->where('formation_type_id', $request->formation_type);
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
            $formationTypes = FormationType::active()->orderBy('name')->get();
            $flottes = Flotte::orderBy('name')->get();

            return view('formation-processes.index', compact(
                'formationProcesses',
                'total',
                'draft',
                'inProgress',
                'validated',
                'rejected',
                'drivers',
                'formationTypes',
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
    public function create(Request $request): View
    {
        $drivers = Driver::orderBy('full_name')->get();
        $formationTypes = FormationType::active()->orderBy('name')->get();
        $flottes = Flotte::orderBy('name')->get();

        // Pre-select driver and formation type if provided in query parameters
        $selectedDriverId = $request->get('driver_id');
        $selectedFormationTypeId = $request->get('formation_type_id');
        $selectedFlotteId = $request->get('flotte_id');

        // If driver is selected but no flotte is specified, try to get driver's flotte
        if ($selectedDriverId && !$selectedFlotteId) {
            $driver = Driver::with('flotte')->find($selectedDriverId);
            if ($driver && $driver->flotte_id) {
                $selectedFlotteId = $driver->flotte_id;
            }
        }

        return view('formation-processes.create', compact('drivers', 'formationTypes', 'flottes', 'selectedDriverId', 'selectedFormationTypeId', 'selectedFlotteId'));
    }

    /**
     * Store a newly created formation process (Step 1 data).
     */
    public function store(Request $request): RedirectResponse
    {
        try {
            $validated = $request->validate([
                'site' => 'required|string|max:255',
                'flotte_id' => 'required|exists:flottes,id',
                'formation_type_id' => 'required|exists:formation_types,id',
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
                'formation_type_id' => $validated['formation_type_id'],
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
                'validated_by' => auth()->id(),
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
    public function show(FormationProcess $formationProcess): View
    {
        try {
            $formationProcess->load(['steps', 'validator', 'driver', 'formationType', 'flotte']);

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

            return view('formation-processes.show', compact('formationProcess', 'steps'));
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
    public function step(FormationProcess $formationProcess, int $stepNumber): View|RedirectResponse
    {
        try {
            // Validate step number
            if ($stepNumber < 1 || $stepNumber > 8) {
                return redirect()->route('formation-processes.show', $formationProcess->id)
                    ->with('error', __('messages.invalid_step'));
            }

            // Check if formation process is rejected
            if ($formationProcess->isRejected()) {
                return redirect()->route('formation-processes.show', $formationProcess->id)
                    ->with('error', __('messages.formation_process_rejected'));
            }

            $formationProcess->load(['steps', 'validator', 'driver', 'formationType', 'flotte']);

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

            if ($formationProcess->isValidated()) {
                // Allow viewing validated formation processes
                $step = $formationProcess->getStep($stepNumber);
                if (!$step) {
                    return redirect()->route('formation-processes.show', $formationProcess->id)
                        ->with('error', __('messages.step_not_found'));
                }
                return view('formation-processes.show', compact('formationProcess', 'stepNumber', 'step', 'steps'));
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

            return view('formation-processes.show', compact('formationProcess', 'stepNumber', 'step', 'steps'));
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
    public function saveStep(Request $request, FormationProcess $formationProcess, int $stepNumber): RedirectResponse
    {
        try {
            // Validate step number
            if ($stepNumber < 1 || $stepNumber > 8) {
                return redirect()->route('formation-processes.show', $formationProcess->id)
                    ->with('error', __('messages.invalid_step'));
            }

            // Check if formation process is rejected
            if ($formationProcess->isRejected()) {
                return redirect()->route('formation-processes.show', $formationProcess->id)
                    ->with('error', __('messages.formation_process_rejected'));
            }

            // Check if formation process is already validated (read-only)
            if ($formationProcess->isValidated()) {
                return redirect()->route('formation-processes.show', $formationProcess->id)
                    ->with('error', __('messages.formation_process_already_finalized'));
            }

            // Get the step
            $step = $formationProcess->getStep($stepNumber);
        
            // Access rules for saving:
            // 1. Can save current step
            // 2. Can save validated steps (for updates)
            // 3. Cannot save future steps unless previous are validated
            $canSave = false;
            
            if ($formationProcess->current_step === $stepNumber) {
                // Can always save current step
                $canSave = true;
            } elseif ($step && $step->isValidated()) {
                // Can update validated steps
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
                    'formation_type_id' => $validated['formation_type_id'] ?? $formationProcess->formation_type_id,
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
            } elseif ($stepNumber === 6) {
                $validated = $this->handleStep6Uploads($request, $validated);
            } elseif ($stepNumber === 8) {
                $validated = $this->handleStep8Uploads($request, $validated);
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
    public function validateStep(Request $request, FormationProcess $formationProcess, int $stepNumber): RedirectResponse
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
                $requiredFields = ['site', 'flotte_id', 'formation_type_id', 'driver_id', 'theme'];
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

            // Step 5: start_date and status required
            if ($stepNumber === 5) {
                $stepData = $step->step_data ?? [];
                if (empty($stepData['start_date']) || empty($stepData['status'])) {
                    return redirect()->route('formation-processes.step', ['formationProcess' => $formationProcess->id, 'stepNumber' => $stepNumber])
                        ->with('error', __('messages.start_date_and_status_required'));
                }
            }

            // Step 6: Attendance sheet required
            if ($stepNumber === 6) {
                $stepData = $step->step_data ?? [];
                if (empty($stepData)) {
                    return redirect()->route('formation-processes.step', ['formationProcess' => $formationProcess->id, 'stepNumber' => $stepNumber])
                        ->with('error', __('messages.please_save_step_before_validation'));
                }
            }

            // Step 7: note_formation and feedback_formation required
            if ($stepNumber === 7) {
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

            // Step 8: Final validation info required
            if ($stepNumber === 8) {
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

            $step->validateStep(auth()->id(), $request->input('notes'));

            // Move to next step if this step is validated and it's the current step
            if ($stepNumber < 8 && $formationProcess->current_step === $stepNumber) {
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
    public function rejectStep(Request $request, FormationProcess $formationProcess, int $stepNumber): RedirectResponse
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
                auth()->id(),
                $request->input('notes')
            );

            // Reject formation process if critical step is rejected
            if (in_array($stepNumber, [3, 5, 7])) {
                $this->rejectFormationProcess($formationProcess, $request->input('rejection_reason'), auth()->id());
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
    public function finalize(FormationProcess $formationProcess): RedirectResponse
    {
        try {
            // Check if Step 8 is validated
            $step8 = $formationProcess->getStep(8);
            if (!$step8 || !$step8->isValidated()) {
                return redirect()->route('formation-processes.show', $formationProcess->id)
                    ->with('error', __('messages.step8_must_be_validated'));
            }

            // Check if all steps are validated
            for ($i = 1; $i <= 8; $i++) {
                $step = $formationProcess->getStep($i);
                if (!$step || !$step->isValidated()) {
                    return redirect()->route('formation-processes.show', $formationProcess->id)
                        ->with('error', __('messages.all_steps_must_be_validated'));
                }
            }

            // Create or update DriverFormation
            $this->createOrUpdateDriverFormation($formationProcess);

            // Generate formation report
            $reportPath = $this->generateFormationReport($formationProcess);

            // Mark formation process as validated
            $formationProcess->markAsValidated(auth()->id());
            
            // Refresh to get latest data
            $formationProcess->refresh();

            // Persist report path in Step 8 data if available
            if ($reportPath) {
                $step8Data = $step8->step_data ?? [];
                $step8Data['report_path'] = $reportPath;
                $step8->setStepDataArray($step8Data);
                $step8->save();
            }

            Log::info('Formation process finalized', [
                'formation_process_id' => $formationProcess->id,
                'validated_by' => $formationProcess->validated_by,
                'validated_at' => $formationProcess->validated_at,
                'driver_formation_id' => $formationProcess->driver_formation_id,
            ]);

            return redirect()->route('formation-processes.index')
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
     * Download the formation report, generating it if necessary.
     */
    public function downloadReport(FormationProcess $formationProcess)
    {
        try {
            if (!$formationProcess->isValidated()) {
                return back()->with('error', __('messages.formation_process_not_validated'));
            }

            $step8 = $formationProcess->getStep(8);

            if (!$step8 || !$step8->isValidated()) {
                return back()->with('error', __('messages.step8_must_be_validated'));
            }

            $reportPath = data_get($step8->step_data, 'report_path');

            if (!$reportPath || !Storage::disk('public')->exists($reportPath)) {
                $reportPath = $this->generateFormationReport($formationProcess);

                if ($reportPath) {
                    $step8->setStepDataArray(['report_path' => $reportPath]);
                    $step8->save();
                }
            }

            if (!$reportPath || !Storage::disk('public')->exists($reportPath)) {
                return back()->with('error', __('messages.error_generating_formation_report'));
            }

            $fileName = sprintf('formation-report-%d.pdf', $formationProcess->id);

            return Storage::disk('public')->download($reportPath, $fileName);
        } catch (\Throwable $e) {
            Log::error('Failed to download formation report', [
                'formation_process_id' => $formationProcess->id,
                'error' => $e->getMessage(),
            ]);

            return back()->with('error', __('messages.error_generating_formation_report'));
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
                        'start_date' => 'required|date',
                        'status' => 'required|in:planned,realized',
                        'location' => 'nullable|string|max:255',
                        'equipment_notes' => 'nullable|string',
                        'participants_list' => 'nullable|string',
                    ];
                    break;

                case 6:
                    $rules = [
                        'actual_start_date' => 'nullable|date',
                        'attendance_sheet_path' => 'nullable|string',
                        'training_materials_path' => 'nullable|string',
                        'attendance_sheet' => 'nullable|file|mimes:pdf,doc,docx,xlsx|max:5120',
                        'training_materials' => 'nullable|file|mimes:pdf,doc,docx|max:5120',
                        'delivery_notes' => 'nullable|string',
                    ];
                    break;

                case 7:
                    $rules = [
                        'note_formation' => 'required|integer|min:0|max:100',
                        'feedback_formation' => 'required|string',
                        'feedback_tbx' => 'nullable|string',
                        'nbr' => 'nullable|string',
                        'evaluation_questionnaire_path' => 'nullable|string',
                        'evaluation_questionnaire' => 'nullable|file|mimes:pdf,doc,docx|max:5120',
                    ];
                    break;

                case 8:
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
                $contractPath = $request->file('trainer_contract')->store('formation/contracts', 'public');
                $validated['trainer_contract_path'] = $contractPath;
            }

            // Handle training program upload
            if ($request->hasFile('training_program')) {
                $programPath = $request->file('training_program')->store('formation/programs', 'public');
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
     * Handle file uploads for Step 6.
     */
    private function handleStep6Uploads(Request $request, array $validated): array
    {
        try {
            // Handle attendance sheet upload
            if ($request->hasFile('attendance_sheet')) {
                $attendancePath = $request->file('attendance_sheet')->store('formation/attendance', 'public');
                $validated['attendance_sheet_path'] = $attendancePath;
            }

            // Handle training materials upload
            if ($request->hasFile('training_materials')) {
                $materialsPath = $request->file('training_materials')->store('formation/materials', 'public');
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
     * Handle file uploads for Step 8.
     */
    private function handleStep8Uploads(Request $request, array $validated): array
    {
        try {
            // Handle completion certificate upload
            if ($request->hasFile('completion_certificate')) {
                $certificatePath = $request->file('completion_certificate')->store('formation/certificates', 'public');
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
    private function createOrUpdateDriverFormation(FormationProcess $formationProcess): void
    {
        try {
            // Get step data
            $step1 = $formationProcess->getStep(1);
            $step2 = $formationProcess->getStep(2);
            $step5 = $formationProcess->getStep(5);
            $step7 = $formationProcess->getStep(7);
            $step8 = $formationProcess->getStep(8);

            // Check if DriverFormation already exists
            if ($formationProcess->driver_formation_id) {
                $driverFormation = DriverFormation::find($formationProcess->driver_formation_id);
                if ($driverFormation) {
                    // Update existing record
                    $driverFormation->update([
                        'status' => $step5 ? ($step5->getStepData('status') === 'realized' ? 'done' : 'planned') : 'planned',
                        'planned_at' => $step2 ? $step2->getStepData('date_prevu') : null,
                        'done_at' => $step5 && $step5->getStepData('status') === 'realized' ? $step5->getStepData('actual_start_date') : null,
                        'certificate_path' => $step8 ? $step8->getStepData('completion_certificate_path') : null,
                        'notes' => $step7 ? $step7->getStepData('feedback_formation') : null,
                        'progress_percent' => $step7 ? $step7->getStepData('note_formation') : 0,
                    ]);
                    return;
                }
            }

            // Create new DriverFormation
            $driverFormation = DriverFormation::create([
                'driver_id' => $formationProcess->driver_id,
                'formation_type_id' => $formationProcess->formation_type_id,
                'formation_process_id' => $formationProcess->id,
                'status' => $step5 ? ($step5->getStepData('status') === 'realized' ? 'done' : 'planned') : 'planned',
                'planned_at' => $step2 ? $step2->getStepData('date_prevu') : null,
                'done_at' => $step5 && $step5->getStepData('status') === 'realized' ? $step5->getStepData('actual_start_date') : null,
                'certificate_path' => $step8 ? $step8->getStepData('completion_certificate_path') : null,
                'notes' => $step7 ? $step7->getStepData('feedback_formation') : null,
                'progress_percent' => $step7 ? $step7->getStepData('note_formation') : 0,
            ]);

            // Link DriverFormation to FormationProcess
            $formationProcess->update(['driver_formation_id' => $driverFormation->id]);

            Log::info('DriverFormation created/updated', [
                'formation_process_id' => $formationProcess->id,
                'driver_formation_id' => $driverFormation->id,
            ]);
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
            $formationProcess->update([
                'driver_formation_id' => null,
                'current_step' => 1,
            ]);

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
            $formationProcess->loadMissing(['driver', 'formationType', 'flotte', 'steps']);

            $step1Data = optional($formationProcess->getStep(1))->step_data ?? [];
            $step2Data = optional($formationProcess->getStep(2))->step_data ?? [];
            $step4Data = optional($formationProcess->getStep(4))->step_data ?? [];
            $step5Data = optional($formationProcess->getStep(5))->step_data ?? [];
            $step7Data = optional($formationProcess->getStep(7))->step_data ?? [];

            $statusRaw = $step5Data['status'] ?? null;
            $statusLabel = match ($statusRaw) {
                'realized' => __('messages.status_realized'),
                'planned' => __('messages.status_planned'),
                default => __('messages.not_available'),
            };

            $data = [
                'site' => $step1Data['site'] ?? $formationProcess->site ?? __('messages.not_available'),
                'flotte' => $formationProcess->flotte?->name ?? __('messages.not_available'),
                'type' => $formationProcess->formationType?->name ?? __('messages.not_available'),
                'driver' => $formationProcess->driver?->full_name ?? __('messages.not_available'),
                'theme' => $step1Data['theme'] ?? $formationProcess->theme ?? __('messages.not_available'),
                'animateur' => $step4Data['animateur'] ?? __('messages.not_available'),
                'start_date' => $this->formatReportDate($step5Data['start_date'] ?? null),
                'status' => $statusLabel,
                'date_prevu' => $this->formatReportDate($step2Data['date_prevu'] ?? null),
                'feedback_tbx' => $step7Data['feedback_tbx'] ?? __('messages.not_available'),
                'nbr' => $step7Data['nbr'] ?? __('messages.not_available'),
                'note_formation' => isset($step7Data['note_formation'])
                    ? $step7Data['note_formation'] . '%'
                    : __('messages.not_available'),
                'feedback_formation' => $step7Data['feedback_formation'] ?? __('messages.not_available'),
                'generated_at' => now()->format('d/m/Y H:i'),
            ];

            $pdf = Pdf::loadView('formation-processes.reports.summary', $data)->setPaper('a4');

            $directory = 'formation-reports';
            if (!Storage::disk('public')->exists($directory)) {
                Storage::disk('public')->makeDirectory($directory);
            }

            $fileName = sprintf(
                '%s/formation-report-%d-%s.pdf',
                $directory,
                $formationProcess->id,
                now()->format('YmdHis')
            );

            Storage::disk('public')->put($fileName, $pdf->output());

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
