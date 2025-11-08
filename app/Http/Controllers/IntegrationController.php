<?php

namespace App\Http\Controllers;

use App\Models\IntegrationCandidate;
use App\Models\IntegrationStep;
use App\Models\Driver;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Hash;

class IntegrationController extends Controller
{
    /**
     * Display a listing of all integrations.
     */
    public function index(Request $request): View
    {
        try {
            $query = IntegrationCandidate::query()
                ->with(['validator', 'steps'])
                ->orderBy('created_at', 'desc');

            // Filter by status if provided
            if ($request->has('status') && $request->status !== 'all') {
                $query->where('status', $request->status);
            }

            // Filter by type if provided
            if ($request->has('type') && $request->type !== 'all') {
                $query->where('type', $request->type);
            }

            $integrations = $query->get();

            // Calculate statistics
            $total = $integrations->count();
            $draft = $integrations->where('status', 'draft')->count();
            $inProgress = $integrations->where('status', 'in_progress')->count();
            $validated = $integrations->where('status', 'validated')->count();
            $rejected = $integrations->where('status', 'rejected')->count();

            // Count by type
            $drivers = $integrations->where('type', 'driver')->count();
            $administration = $integrations->where('type', 'administration')->count();

            return view('integrations.index', compact(
                'integrations',
                'total',
                'draft',
                'inProgress',
                'validated',
                'rejected',
                'drivers',
                'administration'
            ));
        } catch (\Throwable $e) {
            Log::error('Failed to display integrations', [
                'error' => $e->getMessage(),
            ]);
            return redirect()->route('integrations.index')
                ->with('error', __('messages.error_displaying_integrations'));
        }
    }

    /**
     * Show the form for creating a new integration (Step 1).
     */
    public function create(): View
    {
        return view('integrations.create');
    }

    /**
     * Store a newly created integration candidate (Step 1 data).
     */
    public function store(Request $request): RedirectResponse
    {
        try {
            $validated = $request->validate([
                'identification_besoin' => 'required|string',
                'poste_type' => 'required|in:chauffeur,administration',
                'description_poste' => 'required|string',
                'prospection_method' => 'required|in:reseaux_social,bouche_a_oreil,autre',
                'prospection_date' => 'nullable|date',
                'nombre_candidats' => 'nullable|integer|min:0',
                'notes_prospection' => 'nullable|string',
            ]);
            // Determine type based on poste_type
            $type = $validated['poste_type'] === 'chauffeur' ? 'driver' : 'administration';

            $candidate = IntegrationCandidate::create([
                'type' => $type,
                'poste_type' => $validated['poste_type'],
                'identification_besoin' => $validated['identification_besoin'],
                'description_poste' => $validated['description_poste'],
                'prospection_method' => $validated['prospection_method'],
                'prospection_date' => $validated['prospection_date'] ?? null,
                'nombre_candidats' => $validated['nombre_candidats'] ?? null,
                'notes_prospection' => $validated['notes_prospection'] ?? null,
                'status' => 'draft',
                'current_step' => 1,
            ]);

            // Create Step 1 record and mark as validated (since it's the initial step)
            IntegrationStep::create([
                'integration_candidate_id' => $candidate->id,
                'step_number' => 1,
                'step_data' => $validated,
                'status' => 'validated',
                'validated_by' => auth()->id(),
                'validated_at' => now(),
            ]);

            // Update candidate to move to step 2
            $candidate->update([
                'current_step' => 2,
                'status' => 'in_progress',
            ]);

            // Automatically redirect to Step 2
            return redirect()->route('integrations.step', ['integration' => $candidate->id, 'stepNumber' => 2])
                ->with('success', __('messages.step_saved'));
        } catch (\Throwable $e) {
            Log::error('Failed to create integration candidate', [
                'error' => $e->getMessage(),
            ]);
            return back()
                ->withInput()
                ->with('error', __('messages.step_save_error'));
        }
    }

    /**
     * Display the specified integration progress.
     */
    public function show(IntegrationCandidate $integration): View
    {
        try {
            $integration->load(['steps', 'validator']);

            // Get all steps with their status
            $steps = [];
            for ($i = 1; $i <= 8; $i++) {
                $step = $integration->getStep($i);
                $steps[$i] = [
                    'step' => $step,
                    'can_access' => $integration->canProceedToStep($i),
                    'is_validated' => $step && $step->isValidated(),
                    'is_rejected' => $step && $step->isRejected(),
                    'is_pending' => $step && $step->isPending(),
                ];
            }

            return view('integrations.show', compact('integration', 'steps'));
        } catch (\Throwable $e) {
            Log::error('Failed to display integration', [
                'error' => $e->getMessage(),
            ]);
            return redirect()->route('integrations.index')
                ->with('error', __('messages.error_displaying_integration'));
        }
    }

    /**
     * Show the form for a specific step.
     */
    public function step(IntegrationCandidate $integration, int $stepNumber): View|RedirectResponse
    {
        try {
            // Validate step number
            if ($stepNumber < 1 || $stepNumber > 8) {
                return redirect()->route('integrations.show', $integration->id)
                    ->with('error', __('messages.invalid_step'));
            }

            // Check if integration is rejected or already validated
            if ($integration->isRejected()) {
                return redirect()->route('integrations.show', $integration->id)
                    ->with('error', __('messages.integration_rejected'));
            }

            $integration->load(['steps', 'validator']);

            // Get all steps with their status
            $steps = [];
            for ($i = 1; $i <= 8; $i++) {
                $step = $integration->getStep($i);
                $steps[$i] = [
                    'step' => $step,
                    'can_access' => $integration->canProceedToStep($i),
                    'is_validated' => $step && $step->isValidated(),
                    'is_rejected' => $step && $step->isRejected(),
                    'is_pending' => $step && $step->isPending(),
                ];
            }

            if ($integration->isValidated()) {
                // Allow viewing validated integrations
                $step = $integration->getStep($stepNumber);
                if (!$step) {
                    return redirect()->route('integrations.show', $integration->id)
                        ->with('error', __('messages.step_not_found'));
                }
                return view('integrations.show', compact('integration', 'stepNumber', 'step', 'steps'));
            }

            // Get the step
            $step = $integration->getStep($stepNumber);
            
            $canAccess = false;
        
            if ($stepNumber === 1) {
                $canAccess = true;
            } elseif ($step && $step->isValidated()) {
                // Can access validated steps for viewing
                $canAccess = true;
            } elseif ($integration->current_step === $stepNumber) {
                // Can access current step for editing
                $canAccess = true;
            } else {
                // Check if all previous steps are validated
                $canAccess = $integration->canProceedToStep($stepNumber);
            }

            if (!$canAccess) {
                return redirect()->route('integrations.show', $integration->id)
                    ->with('error', __('messages.must_validate_previous_steps'));
            }

            // Get or create step record if it doesn't exist
            if (!$step) {
                $step = IntegrationStep::create([
                    'integration_candidate_id' => $integration->id,
                    'step_number' => $stepNumber,
                    'status' => 'pending',
                ]);
            }

            return view('integrations.show', compact('integration', 'stepNumber', 'step', 'steps'));
        } catch (\Throwable $e) {
            Log::error('Failed to display step', [
                'error' => $e->getMessage(),
            ]);
            return redirect()->route('integrations.index')
                ->with('error', __('messages.error_displaying_step'));
        }
    }

    /**
     * Save step data.
     */
    public function saveStep(Request $request, IntegrationCandidate $integration, int $stepNumber): RedirectResponse
    {
        try {
            // Validate step number
            if ($stepNumber < 1 || $stepNumber > 8) {
                return redirect()->route('integrations.show', $integration->id)
                    ->with('error', __('messages.invalid_step'));
            }

            // Check if integration is rejected
            if ($integration->isRejected()) {
                return redirect()->route('integrations.show', $integration->id)
                    ->with('error', __('messages.integration_rejected'));
            }

            // Check if integration is already validated (read-only)
            if ($integration->isValidated()) {
                return redirect()->route('integrations.show', $integration->id)
                    ->with('error', __('messages.integration_already_finalized'));
            }

            // Get the step
            $step = $integration->getStep($stepNumber);
        
            // Access rules for saving:
            // 1. Can save current step
            // 2. Can save validated steps (for updates)
            // 3. Cannot save future steps unless previous are validated
            $canSave = false;
            
            if ($integration->current_step === $stepNumber) {
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
                $canSave = $integration->canProceedToStep($stepNumber);
            }

            if (!$canSave) {
                return redirect()->route('integrations.show', $integration->id)
                    ->with('error', __('messages.cannot_modify_step'));
            }

            // Get or create step
            $step = $integration->getStep($stepNumber);
            if (!$step) {
                $step = IntegrationStep::create([
                    'integration_candidate_id' => $integration->id,
                    'step_number' => $stepNumber,
                    'status' => 'pending',
                ]);
            }

            // Validate based on step number
            $validated = $this->validateStepData($request, $stepNumber);

            // Handle file uploads for specific steps
            if ($stepNumber === 2) {
                $validated = $this->handleStep2Uploads($request, $validated);
            } elseif ($stepNumber === 3) {
                $validated = $this->handleStep3Uploads($request, $validated);
            } elseif ($stepNumber === 6) {
                $validated = $this->handleStep6Uploads($request, $validated);
            }

            // Update step data
            $step->setStepDataArray($validated);
            $step->save();

            // Refresh step to get latest data
            $step->refresh();

            // Update integration status
            if ($integration->status === 'draft') {
                $integration->update(['status' => 'in_progress']);
            }

            // Refresh integration to get latest data
            $integration->refresh();

            // Create driver after saving step 2 data (for driver type integrations)
            if ($stepNumber === 2 && $integration->type === 'driver') {
                try {
                    Log::info('Attempting to create driver from step 2', [
                        'integration_id' => $integration->id,
                        'step_id' => $step->id,
                    ]);
                    $this->createDriverFromStep2($integration);
                } catch (\Throwable $e) {
                    // Log the error with full details
                    Log::error('Failed to create driver from step 2', [
                        'integration_id' => $integration->id,
                        'error' => $e->getMessage(),
                        'file' => $e->getFile(),
                        'line' => $e->getLine(),
                        'trace' => $e->getTraceAsString(),
                    ]);
                    // Continue - step is saved, driver creation can be retried
                }
            }

            // Redirect back to the step page after saving
            return redirect()->route('integrations.step', ['integration' => $integration->id, 'stepNumber' => $stepNumber])
                ->with('success', __('messages.step_saved'));
        } catch (\Throwable $e) {
            Log::error('Failed to save step', [
                'integration_id' => $integration->id,
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
    public function validateStep(Request $request, IntegrationCandidate $integration, int $stepNumber): RedirectResponse
    {
        try {
            $step = $integration->getStep($stepNumber);
            if (!$step) {
                return redirect()->route('integrations.step', ['integration' => $integration->id, 'stepNumber' => $stepNumber])
                    ->with('error', __('messages.step_not_found'));
            }

            // Step 1: All fields must be filled (basic validation)
            if ($stepNumber === 1) {
                $stepData = $step->step_data ?? [];
                $requiredFields = ['identification_besoin', 'poste_type', 'description_poste', 'prospection_method'];
                foreach ($requiredFields as $field) {
                    if (empty($stepData[$field])) {
                        return redirect()->route('integrations.step', ['integration' => $integration->id, 'stepNumber' => $stepNumber])
                            ->with('error', __('messages.all_fields_step1_required'));
                    }
                }
            }

            // Step 2: All basic info must be filled
            if ($stepNumber === 2) {
                $stepData = $step->step_data ?? [];
                $requiredFields = ['full_name', 'email', 'phone', 'cin', 'date_of_birth', 'address'];
                foreach ($requiredFields as $field) {
                    if (empty($stepData[$field])) {
                        return redirect()->route('integrations.step', ['integration' => $integration->id, 'stepNumber' => $stepNumber])
                            ->with('error', __('messages.all_basic_info_required'));
                    }
                }
            }

            // Step 3: Must pass verification (result = 'passed') to continue, else reject
            if ($stepNumber === 3) {
                $stepData = $step->step_data ?? [];
                
                // Check if step data exists
                if (empty($stepData)) {
                    return redirect()->route('integrations.step', ['integration' => $integration->id, 'stepNumber' => $stepNumber])
                        ->with('error', __('messages.please_save_step_before_validation'));
                }
                
                $result = $step->getStepData('result');
                if (empty($result)) {
                    return redirect()->route('integrations.step', ['integration' => $integration->id, 'stepNumber' => $stepNumber])
                        ->with('error', __('messages.please_save_step_before_validation'));
                }
                
                if ($result !== 'passed') {
                    $this->rejectIntegration($integration, __('messages.step3_failed'), auth()->id());
                    $step->rejectStep(
                        __('messages.result_must_be_passed'),
                        auth()->id()
                    );
                    return redirect()->route('integrations.step', ['integration' => $integration->id, 'stepNumber' => $stepNumber])
                        ->with('error', __('messages.step3_failed'));
                }
            }

            // Step 4: Must pass test (result = 'passed') to continue, else reject
            if ($stepNumber === 4) {
                $stepData = $step->step_data ?? [];
                if (empty($stepData)) {
                    return redirect()->route('integrations.step', ['integration' => $integration->id, 'stepNumber' => $stepNumber])
                        ->with('error', __('messages.please_save_step_before_validation'));
                }
                
                $result = $step->getStepData('result');
                if ($result !== 'passed') {
                    $this->rejectIntegration($integration, __('messages.step4_failed'), auth()->id());
                    $step->rejectStep(
                        __('messages.result_must_be_passed'),
                        auth()->id()
                    );
                    return redirect()->route('integrations.step', ['integration' => $integration->id, 'stepNumber' => $stepNumber])
                        ->with('error', __('messages.step4_failed'));
                }
            }

            // Step 5: Must pass driving test (result = 'passed') to continue, else reject
            if ($stepNumber === 5) {
                $stepData = $step->step_data ?? [];
                if (empty($stepData)) {
                    return redirect()->route('integrations.step', ['integration' => $integration->id, 'stepNumber' => $stepNumber])
                        ->with('error', __('messages.please_save_step_before_validation'));
                }
                
                $result = $step->getStepData('result');
                if ($result !== 'passed') {
                    $this->rejectIntegration($integration, __('messages.step5_failed'), auth()->id());
                    $step->rejectStep(
                        __('messages.result_must_be_passed'),
                        auth()->id()
                    );
                    return redirect()->route('integrations.step', ['integration' => $integration->id, 'stepNumber' => $stepNumber])
                        ->with('error', __('messages.step5_failed'));
                }
            }

            // Step 6: All sub-steps (validation, induction, contract) required
            if ($stepNumber === 6) {
                $stepData = $step->step_data ?? [];
                if (empty($stepData)) {
                    return redirect()->route('integrations.step', ['integration' => $integration->id, 'stepNumber' => $stepNumber])
                        ->with('error', __('messages.please_save_step_before_validation'));
                }
                
                $requiredFields = [
                    'validation_date',
                    'validated_by',
                    'induction_date',
                    'induction_conducted_by',
                    'contract_signed_date',
                ];
                foreach ($requiredFields as $field) {
                    if (empty($stepData[$field])) {
                        return redirect()->route('integrations.step', ['integration' => $integration->id, 'stepNumber' => $stepNumber])
                            ->with('error', __('messages.step6_substeps_required'));
                    }
                }
            }

            // Step 7: Must pass accompaniment (result = 'passed') to continue, else reject
            if ($stepNumber === 7) {
                $stepData = $step->step_data ?? [];
                if (empty($stepData)) {
                    return redirect()->route('integrations.step', ['integration' => $integration->id, 'stepNumber' => $stepNumber])
                        ->with('error', __('messages.please_save_step_before_validation'));
                }
                
                $result = $step->getStepData('result');
                if ($result !== 'passed') {
                    $this->rejectIntegration($integration, __('messages.step7_failed'), auth()->id());
                    $step->rejectStep(
                        __('messages.result_must_be_passed'),
                        auth()->id()
                    );
                    return redirect()->route('integrations.step', ['integration' => $integration->id, 'stepNumber' => $stepNumber])
                        ->with('error', __('messages.step7_failed'));
                }
            }

            // Step 8: Final validation unlocks promotion
            if ($stepNumber === 8) {
                $stepData = $step->step_data ?? [];
                if (empty($stepData)) {
                    return redirect()->route('integrations.step', ['integration' => $integration->id, 'stepNumber' => $stepNumber])
                        ->with('error', __('messages.please_save_step_before_validation'));
                }
                
                if (empty($stepData['final_validation_date']) || empty($stepData['validated_by'])) {
                    return redirect()->route('integrations.step', ['integration' => $integration->id, 'stepNumber' => $stepNumber])
                        ->with('error', __('messages.final_validation_info_required'));
                }
            }

            $step->validateStep(auth()->id(), $request->input('notes'));

            // Move to next step if this step is validated and it's the current step
            if ($stepNumber < 8 && $integration->current_step === $stepNumber) {
                $integration->moveToNextStep();
                
                // Automatically redirect to next step after validation
                return redirect()->route('integrations.step', ['integration' => $integration->id, 'stepNumber' => $stepNumber + 1])
                    ->with('success', __('messages.step_validated_successfully') . ' ' . __('messages.redirecting_to_next_step'));
            }

            // If not redirected to next step, redirect back to current step
            return redirect()->route('integrations.step', ['integration' => $integration->id, 'stepNumber' => $stepNumber])
                ->with('success', __('messages.step_validated_successfully'));
        } catch (\Throwable $e) {
            Log::error('Failed to validate step', [
                'integration_id' => $integration->id,
                'step_number' => $stepNumber,
                'error' => $e->getMessage(),
            ]);
            return back()->with('error', __('messages.error_validating_step'));
        }
    }

    /**
     * Reject a step (admin only).
     */
    public function rejectStep(Request $request, IntegrationCandidate $integration, int $stepNumber): RedirectResponse
    {
        try {
            $step = $integration->getStep($stepNumber);
            if (!$step) {
                return redirect()->route('integrations.show', $integration->id)
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

            // Reject integration if critical step is rejected
            if (in_array($stepNumber, [3, 4, 5, 7])) {
                $this->rejectIntegration($integration, $request->input('rejection_reason'), auth()->id());
            }

            return redirect()->route('integrations.show', $integration->id)
                ->with('success', __('messages.step_rejected'));
        } catch (\Throwable $e) {
            Log::error('Failed to reject step', [
                'integration_id' => $integration->id,
                'step_number' => $stepNumber,
                'error' => $e->getMessage(),
            ]);
            return back()->with('error', __('messages.error_rejecting_step'));
        }
    }

    /**
     * Finalize integration (Step 8 completion - promote to Driver/User).
     */
    public function finalize(IntegrationCandidate $integration): RedirectResponse
    {
        try {
            // Check if Step 8 is validated
            $step8 = $integration->getStep(8);
            if (!$step8 || !$step8->isValidated()) {
                return redirect()->route('integrations.show', $integration->id)
                    ->with('error', __('messages.step8_must_be_validated'));
            }

            // Check if all steps are validated
            for ($i = 1; $i <= 8; $i++) {
                $step = $integration->getStep($i);
                if (!$step || !$step->isValidated()) {
                    return redirect()->route('integrations.show', $integration->id)
                        ->with('error', __('messages.all_steps_must_be_validated'));
                }
            }

            if ($integration->type === 'driver') {
                $this->promoteToDriver($integration);
                
                // Mark driver as integrated
                if ($integration->driver_id) {
                    $driver = Driver::find($integration->driver_id);
                    if ($driver) {
                        $driver->update(['is_integrated' => 1]);
                        Log::info('Driver marked as integrated', [
                            'integration_id' => $integration->id,
                            'driver_id' => $driver->id,
                        ]);
                    }
                }
            } else {
                $this->promoteToUser($integration);
            }

            // Mark integration as validated (sets validated_by and validated_at)
            $integration->markAsValidated(auth()->id());
            
            // Refresh to get latest data
            $integration->refresh();

            Log::info('Integration finalized', [
                'integration_id' => $integration->id,
                'validated_by' => $integration->validated_by,
                'validated_at' => $integration->validated_at,
                'driver_id' => $integration->driver_id,
            ]);

            // If driver type, redirect to driver edit page to complete driver information
            if ($integration->type === 'driver' && $integration->driver_id) {
                $driver = Driver::find($integration->driver_id);
                if ($driver) {
                    return redirect()->route('drivers.edit', $driver)
                        ->with('success', __('messages.integration_finalized_successfully') . ' ' . __('messages.complete_driver_information'));
                }
            }

            return redirect()->route('integrations.index')
                ->with('success', __('messages.integration_finalized_successfully'));
        } catch (\Throwable $e) {
            Log::error('Failed to finalize integration', [
                'integration_id' => $integration->id,
                'error' => $e->getMessage(),
            ]);
            return redirect()->route('integrations.show', $integration->id)
                ->with('error', __('messages.error_finalizing_integration'));
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
                        'full_name' => 'required|string|max:255',
                        'email' => 'required|email|max:255',
                        'phone' => 'required|string|max:20',
                        'cin' => 'required|string|max:50',
                        'date_of_birth' => 'required|date',
                        'address' => 'required|string',
                        'photo' => 'nullable|image|max:2048',
                        'documents' => 'nullable|array',
                        'documents.*' => 'file|max:5120',
                    ];
                    break;

                case 3:
                    $rules = [
                        'verification_date' => 'required|date',
                        'verified_by' => 'required|string|max:255',
                        'result' => 'required|in:passed,failed',
                        'documents_reviewed' => 'nullable|array',
                        'notes' => 'nullable|string',
                    ];
                    break;

                case 4:
                    $rules = [
                        'test_date' => 'required|date',
                        'evaluator' => 'required|string|max:255',
                        'oral_score' => 'nullable|integer|min:0|max:100',
                        'written_score' => 'nullable|integer|min:0|max:100',
                        'result' => 'required|in:passed,failed',
                        'notes' => 'nullable|string',
                    ];
                    break;

                case 5:
                    $rules = [
                        'test_date' => 'required|date',
                        'instructor' => 'required|string|max:255',
                        'score' => 'nullable|integer|min:0|max:100',
                        'result' => 'required|in:passed,failed',
                        'notes' => 'nullable|string',
                    ];
                    break;

                case 6:
                    $rules = [
                        'validation_date' => 'required|date',
                        'validated_by' => 'required|string|max:255',
                        'induction_date' => 'required|date',
                        'induction_conducted_by' => 'required|string|max:255',
                        'contract_signed_date' => 'required|date',
                        'contract_path' => 'nullable|string',
                        'contract' => 'nullable|file|mimes:pdf,doc,docx|max:5120',
                        'notes' => 'nullable|string',
                    ];
                    break;

                case 7:
                    $rules = [
                        'accompaniment_start_date' => 'required|date',
                        'accompaniment_end_date' => 'nullable|date|after_or_equal:accompaniment_start_date',
                        'accompanied_by' => 'required|string|max:255',
                        'result' => 'required|in:passed,failed',
                        'notes' => 'nullable|string',
                    ];
                    break;

                case 8:
                    $rules = [
                        'final_validation_date' => 'required|date',
                        'validated_by' => 'required|string|max:255',
                        'notes' => 'nullable|string',
                    ];
                    break;
            }

            return $request->validate($rules);
        } catch (\Throwable $e) {
            Log::error('Failed to validate step data', [
                'error' => $e->getMessage(),
            ]);
            return redirect()->route('integrations.index')
                ->with('error', __('messages.error_validating_step_data'));
        }
    }

    /**
     * Handle file uploads for Step 2.
     */
    private function handleStep2Uploads(Request $request, array $validated): array
    {
        try {
            // Handle photo upload
            if ($request->hasFile('photo')) {
                $photoPath = $request->file('photo')->store('integration/photos', 'public');
                $validated['photo_path'] = $photoPath;
            }

            // Handle documents upload
            if ($request->hasFile('documents')) {
                $documents = [];
                foreach ($request->file('documents') as $file) {
                    $docPath = $file->store('integration/documents', 'public');
                    $documents[] = [
                        'name' => $file->getClientOriginalName(),
                        'path' => $docPath,
                    ];
                }
                $validated['documents'] = $documents;
            }

            return $validated;
        } catch (\Throwable $e) {
            Log::error('Failed to handle step 2 uploads', [
                'error' => $e->getMessage(),
            ]);
            return redirect()->route('integrations.index')
                ->with('error', __('messages.error_handling_step_2_uploads'));
        }
    }

    /**
     * Handle file uploads for Step 3.
     */
    private function handleStep3Uploads(Request $request, array $validated): array
    {
        try {
            // Documents reviewed are stored as array of document names/paths
            // This can be extended if files need to be uploaded
            return $validated;
        } catch (\Throwable $e) {
            Log::error('Failed to handle step 3 uploads', [
                'error' => $e->getMessage(),
            ]);
            return redirect()->route('integrations.index')
                ->with('error', __('messages.error_handling_step_3_uploads'));
        }
    }

    /**
     * Handle file uploads for Step 6 (contract).
     */
    private function handleStep6Uploads(Request $request, array $validated): array
    {
        try {
            if ($request->hasFile('contract')) {
                $contractPath = $request->file('contract')->store('integration/contracts', 'public');
                $validated['contract_path'] = $contractPath;
            }

            return $validated;
        } catch (\Throwable $e) {
            Log::error('Failed to handle step 6 uploads', [
                'error' => $e->getMessage(),
            ]);
            return redirect()->route('integrations.index')
                ->with('error', __('messages.error_handling_step_6_uploads'));
        }
    }

    /**
     * Create driver from Step 2 data (called after saving step 2).
     */
    private function createDriverFromStep2(IntegrationCandidate $integration): void
    {
        // Refresh integration to get latest data
        $integration->refresh();
        
        // Check if driver already exists for this integration
        if ($integration->driver_id) {
            $driver = Driver::find($integration->driver_id);
            if ($driver) {
                Log::info('Driver already exists, updating instead', [
                    'integration_id' => $integration->id,
                    'driver_id' => $driver->id,
                ]);
                // Update existing driver with new step 2 data
                $this->updateDriverFromStep2($integration, $driver);
                return;
            }
        }

        $step2 = $integration->getStep(2);
        if (!$step2) {
            throw new \Exception('Step 2 data not found for integration: ' . $integration->id);
        }

        // Refresh step to get latest data
        $step2->refresh();

        // Collect all data from Step 2 - ensure it's an array
        $step2Data = $step2->step_data;
        
        Log::info('Step 2 data retrieved', [
            'integration_id' => $integration->id,
            'step2_id' => $step2->id,
            'step_data_type' => gettype($step2Data),
            'step_data_is_empty' => empty($step2Data),
        ]);
        
        // If step_data is a string (JSON), decode it
        if (is_string($step2Data)) {
            $step2Data = json_decode($step2Data, true);
            if (json_last_error() !== JSON_ERROR_NONE) {
                throw new \Exception('Failed to decode step_data JSON: ' . json_last_error_msg());
            }
            if ($step2Data === null) {
                $step2Data = [];
            }
        }
        
        // Ensure it's an array
        if (!is_array($step2Data)) {
            Log::warning('Step 2 data is not an array', [
                'integration_id' => $integration->id,
                'step_data_type' => gettype($step2Data),
                'step_data' => $step2Data,
            ]);
            $step2Data = [];
        }
        
        // Prepare driver data - only include fields that have values
        $driverData = [
            'status' => 'inactive',
        ];
        
        // Add fields only if they have values (based on Driver model fillable fields)
        $fields = [
            'full_name', 'email', 'phone', 'address', 'city', 'date_of_birth', 'cin',
            'visite_medical', 'visite_yeux', 'formation_imd', 'formation_16_module',
            'attestation_travail', 'carte_profession', 'n_cnss', 'rib',
            'license_type', 'license_issue_date', 'license_class',
            'assigned_vehicle_id', 'notes', 'documents', 'flotte_id'
        ];
        
        foreach ($fields as $field) {
            if (isset($step2Data[$field]) && $step2Data[$field] !== null && $step2Data[$field] !== '') {
                $driverData[$field] = $step2Data[$field];
            }
        }
        
        // Handle required field: license_number (must have a value)
        if (isset($step2Data['license_number']) && !empty($step2Data['license_number'])) {
            $driverData['license_number'] = $step2Data['license_number'];
        } else {
            // Generate placeholder license number from CIN
            $cin = $step2Data['cin'] ?? 'TEMP';
            $driverData['license_number'] = 'PENDING-' . strtoupper(substr($cin, 0, 6));
        }
        
        // Handle date_integration (set to today if not provided)
        if (isset($step2Data['date_integration']) && !empty($step2Data['date_integration'])) {
            $driverData['date_integration'] = $step2Data['date_integration'];
        } else {
            $driverData['date_integration'] = now()->format('Y-m-d');
        }
        
        // Handle license_issue_date (required field - set to today if not provided)
        if (isset($step2Data['license_issue_date']) && !empty($step2Data['license_issue_date'])) {
            $driverData['license_issue_date'] = $step2Data['license_issue_date'];
        } else {
            $driverData['license_issue_date'] = now()->format('Y-m-d');
        }

        Log::info('Driver data prepared', [
            'integration_id' => $integration->id,
            'driver_data' => $driverData,
        ]);

        // Create the driver
        try {
            $driver = Driver::create($driverData);
            Log::info('Driver created successfully', [
                'integration_id' => $integration->id,
                'driver_id' => $driver->id,
            ]);
        } catch (\Exception $e) {
            Log::error('Failed to create driver', [
                'integration_id' => $integration->id,
                'error' => $e->getMessage(),
                'driver_data' => $driverData,
            ]);
            throw new \Exception('Failed to create driver: ' . $e->getMessage());
        }

        // Link the driver to the integration candidate
        try {
            $integration->update(['driver_id' => $driver->id]);
            $integration->refresh();
            
            Log::info('Driver linked to integration candidate', [
                'integration_id' => $integration->id,
                'driver_id' => $driver->id,
                'integration_driver_id' => $integration->driver_id,
            ]);
        } catch (\Exception $e) {
            Log::error('Failed to link driver to integration', [
                'integration_id' => $integration->id,
                'driver_id' => $driver->id,
                'error' => $e->getMessage(),
            ]);
            throw new \Exception('Failed to link driver to integration: ' . $e->getMessage());
        }

        // Log creation for tracking
        Log::info('Driver created from step 2 successfully', [
            'integration_id' => $integration->id,
            'driver_id' => $driver->id,
            'driver_name' => $driver->full_name ?? 'N/A',
        ]);
    }

    /**
     * Update existing driver from Step 2 data.
     */
    private function updateDriverFromStep2(IntegrationCandidate $integration, Driver $driver): void
    {
        $step2 = $integration->getStep(2);
        if (!$step2) {
            return;
        }

        // Collect all data from Step 2 - ensure it's an array
        $step2Data = $step2->step_data;
        
        // If step_data is a string (JSON), decode it
        if (is_string($step2Data)) {
            $step2Data = json_decode($step2Data, true) ?? [];
        }
        
        // Ensure it's an array
        if (!is_array($step2Data)) {
            $step2Data = [];
        }
        
        // Prepare driver data for update - only update fields that have new values
        $driverData = [];
        
        // List of fields that can be updated (based on Driver model fillable fields)
        $fields = [
            'full_name', 'email', 'phone', 'address', 'city', 'date_of_birth', 'cin',
            'visite_medical', 'visite_yeux', 'formation_imd', 'formation_16_module',
            'attestation_travail', 'carte_profession', 'n_cnss', 'rib',
            'license_type', 'license_issue_date', 'license_class',
            'assigned_vehicle_id', 'notes', 'documents', 'flotte_id'
        ];
        
        foreach ($fields as $field) {
            if (isset($step2Data[$field]) && $step2Data[$field] !== null && $step2Data[$field] !== '') {
                $driverData[$field] = $step2Data[$field];
            }
        }
        
        // Handle date_integration if provided
        if (isset($step2Data['date_integration']) && !empty($step2Data['date_integration'])) {
            $driverData['date_integration'] = $step2Data['date_integration'];
        }
        
        // Handle license_number - only update if it's still a placeholder or if new value is provided
        if (isset($step2Data['license_number']) && !empty($step2Data['license_number'])) {
            // Update if new value provided
            $driverData['license_number'] = $step2Data['license_number'];
        } elseif (empty($driver->license_number) || strpos($driver->license_number, 'PENDING-') === 0) {
            // Generate placeholder if driver doesn't have a real license number yet
            $cin = $step2Data['cin'] ?? $driver->cin ?? 'TEMP';
            $driverData['license_number'] = 'PENDING-' . strtoupper(substr($cin, 0, 6));
        }

        // Only update if there's data to update
        if (!empty($driverData)) {
            $driver->update($driverData);
        }

        // Log update for tracking
        Log::info('Driver updated from step 2', [
            'integration_id' => $integration->id,
            'driver_id' => $driver->id,
            'driver_name' => $driver->full_name ?? 'N/A',
        ]);
    }

    /**
     * Promote integration candidate to Driver (used during finalization).
     */
    private function promoteToDriver(IntegrationCandidate $integration): void
    {
        // If driver already exists, just ensure it's properly linked
        if ($integration->driver_id) {
            $driver = Driver::find($integration->driver_id);
            if ($driver) {
                // Driver already exists, just update status if needed
                if ($driver->status === 'inactive') {
                    // Optionally activate the driver here
                }
                return;
            }
        }

        // If no driver exists, create one (shouldn't happen if step 2 was saved)
        $this->createDriverFromStep2($integration);
    }

    /**
     * Reject integration and clean up related data.
     */
    private function rejectIntegration(IntegrationCandidate $integration, string $reason, ?int $userId = null): void
    {
        try {
            Log::info('Rejecting integration', [
                'integration_id' => $integration->id,
                'reason' => $reason,
                'user_id' => $userId,
            ]);

            // Store driver_id before deletion
            $driverId = $integration->driver_id;

            // Delete the driver if it exists
            if ($driverId) {
                $driver = Driver::find($driverId);
                if ($driver) {
                    $driver->delete();
                    Log::info('Driver deleted during integration rejection', [
                        'integration_id' => $integration->id,
                        'driver_id' => $driverId,
                    ]);
                }
            }

            // Delete all integration steps
            $stepsCount = $integration->steps()->count();
            $integration->steps()->delete();
            Log::info('Integration steps deleted', [
                'integration_id' => $integration->id,
                'steps_deleted' => $stepsCount,
            ]);

            // Mark integration as rejected and reset to initial state
            // markAsRejected sets: status='rejected', rejected_at, rejection_reason, validated_by
            $integration->markAsRejected($reason, $userId);
            
            // Reset integration to initial state (driver_id and current_step)
            $integration->update([
                'driver_id' => null,
                'current_step' => 1,
            ]);

            // Refresh to get latest data
            $integration->refresh();

            Log::info('Integration rejected successfully', [
                'integration_id' => $integration->id,
                'status' => $integration->status,
                'rejected_at' => $integration->rejected_at,
                'rejection_reason' => $integration->rejection_reason,
                'driver_id' => $integration->driver_id,
                'current_step' => $integration->current_step,
            ]);
        } catch (\Throwable $e) {
            Log::error('Failed to reject integration', [
                'integration_id' => $integration->id,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);
            throw $e;
        }
    }

    /**
     * Promote integration candidate to User (administration).
     */
    private function promoteToUser(IntegrationCandidate $integration): void
    {
        try {
            $step2 = $integration->getStep(2);
            if (!$step2) {
                throw new \Exception('Step 2 data not found');
            }

            // Collect all data from Step 2 - ensure it's an array
            $step2Data = $step2->step_data;
            
            // If step_data is a string (JSON), decode it
            if (is_string($step2Data)) {
                $step2Data = json_decode($step2Data, true) ?? [];
            }
            
            // Ensure it's an array
            if (!is_array($step2Data)) {
                $step2Data = [];
            }

            // Generate a temporary password (should be changed on first login)
            // In production, consider generating a secure random password and sending it via email
            $tempPassword = Hash::make('password@trans-marhaba');

            $user = User::create([
                'name' => $step2Data['full_name'] ?? '',
                'email' => $step2Data['email'] ?? '',
                'role' => 'administration',
                'password' => $tempPassword,
                'statu' => 'inactive',
                'phone_numbre' => $step2Data['phone'] ?? null,
            ]);

            // Log promotion for tracking
            Log::info('User created from integration', [
                'integration_id' => $integration->id,
                'user_id' => $user->id,
                'user_name' => $user->name,
                'email' => $user->email,
            ]);
        } catch (\Throwable $e) {
            Log::error('Failed to promote to user', [
                'error' => $e->getMessage(),
            ]);
            throw $e;
        }
    }
}
