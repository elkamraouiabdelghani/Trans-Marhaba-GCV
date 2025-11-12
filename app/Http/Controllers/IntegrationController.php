<?php

namespace App\Http\Controllers;

use App\Http\Requests\SaveIntegrationStepRequest;
use App\Http\Requests\StoreIntegrationRequest;
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
    private const DRIVER_ONLY_STEPS = [5, 6, 8];

    /**
     * Display a listing of all integrations.
     */
    public function index(Request $request)
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
    public function create()
    {
        return view('integrations.create');
    }

    /**
     * Store a newly created integration candidate (Step 1 data).
     */
    public function store(StoreIntegrationRequest $request)
    {
        try {
            $validated = $request->validated();
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
    public function show(IntegrationCandidate $integration)
    {
        try {
            $integration->load(['steps', 'validator']);

            $stepNumbers = $this->getApplicableStepNumbers($integration);

            // Get all steps with their status
            $steps = [];
            foreach ($stepNumbers as $i) {
                $step = $integration->getStep($i);
                $steps[$i] = [
                    'step' => $step,
                    'can_access' => $integration->canProceedToStep($i),
                    'is_validated' => $step && $step->isValidated(),
                    'is_rejected' => $step && $step->isRejected(),
                    'is_pending' => $step && $step->isPending(),
                ];
            }

            return view('integrations.show', compact('integration', 'steps', 'stepNumbers'));
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
    public function step(IntegrationCandidate $integration, int $stepNumber)
    {
        try {
            // Validate step number
            if ($stepNumber < 1 || $stepNumber > 9) {
                return redirect()->route('integrations.show', $integration->id)
                    ->with('error', __('messages.invalid_step'));
            }

            $stepNumbers = $this->getApplicableStepNumbers($integration);
            if (!in_array($stepNumber, $stepNumbers, true)) {
                $redirectStep = $this->resolveRedirectStep($integration, $stepNumber);
                return redirect()->route('integrations.step', ['integration' => $integration->id, 'stepNumber' => $redirectStep])
                    ->with('info', __('messages.step_not_required_for_admin'));
            }

            // Check if integration is rejected or already validated
            if ($integration->isRejected()) {
                return redirect()->route('integrations.show', $integration->id)
                    ->with('error', __('messages.integration_rejected'));
            }

            $integration->load(['steps', 'validator']);

            // Get all steps with their status
            $steps = [];
            foreach ($stepNumbers as $i) {
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
                return view('integrations.show', compact('integration', 'stepNumber', 'step', 'steps', 'stepNumbers'));
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

            return view('integrations.show', compact('integration', 'stepNumber', 'step', 'steps', 'stepNumbers'));
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
    public function saveStep(SaveIntegrationStepRequest $request, IntegrationCandidate $integration, int $stepNumber)
    {
        try {
            // Validate step number
            if ($stepNumber < 1 || $stepNumber > 9) {
                return redirect()->route('integrations.show', $integration->id)
                    ->with('error', __('messages.invalid_step'));
            }

            if ($integration->type !== 'driver' && in_array($stepNumber, self::DRIVER_ONLY_STEPS, true)) {
                $redirectStep = $this->resolveRedirectStep($integration, $stepNumber);
                return redirect()->route('integrations.step', ['integration' => $integration->id, 'stepNumber' => $redirectStep])
                    ->with('info', __('messages.step_not_required_for_admin'));
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

            // Validate based on step number via FormRequest
            $validated = $request->validated();

            if ($stepNumber === 2) {
                if (!array_key_exists('email', $validated) || $validated['email'] === '') {
                    $validated['email'] = null;
                }
            }

            $existingStep2Documents = [];
            $existingStep3Documents = [];
            $existingDocuments = [];
            $existingContracts = [];

            if ($stepNumber === 2) {
                $stepData = $step->step_data ?? [];
                $existingStep2Documents = is_array($stepData) ? ($stepData['documents'] ?? []) : [];
                if (!is_array($existingStep2Documents)) {
                    $existingStep2Documents = [];
                }
            }

            if ($stepNumber === 3) {
                $stepData = $step->step_data ?? [];
                $existingStep3Documents = is_array($stepData) ? ($stepData['documents_files'] ?? []) : [];
                if (!is_array($existingStep3Documents)) {
                    $existingStep3Documents = [];
                }
            }

            // Get existing documents for steps 4, 5, 6, 8, 9
            if (in_array($stepNumber, [4, 5, 6, 8, 9])) {
                $stepData = $step->step_data ?? [];
                $existingDocuments = is_array($stepData) ? ($stepData['documents'] ?? []) : [];
                if (!is_array($existingDocuments)) {
                    $existingDocuments = [];
                }
            }

            // Get existing contracts for step 7
            if ($stepNumber === 7) {
                $stepData = $step->step_data ?? [];
                $existingContracts = is_array($stepData) ? ($stepData['contract_paths'] ?? []) : [];
                if (!is_array($existingContracts)) {
                    // Try to get from old single contract_path format
                    if (isset($stepData['contract_path']) && !empty($stepData['contract_path'])) {
                        $existingContracts = [[
                            'name' => basename($stepData['contract_path']),
                            'path' => $stepData['contract_path'],
                        ]];
                    } else {
                        $existingContracts = [];
                    }
                }
            }

            // Handle file uploads for specific steps
            if ($stepNumber === 2) {
                $validated = $this->handleStep2Uploads($request, $validated);
            } elseif ($stepNumber === 3) {
                $validated = $this->handleStep3Uploads($request, $validated);
            } elseif ($stepNumber === 4) {
                $validated = $this->handleStep4Uploads($request, $validated);
            } elseif ($stepNumber === 5) {
                $validated = $this->handleStep5Uploads($request, $validated);
            } elseif ($stepNumber === 6) {
                $validated = $this->handleStep6Uploads($request, $validated);
            } elseif ($stepNumber === 7) {
                $validated = $this->handleStep7Uploads($request, $validated);
            } elseif ($stepNumber === 8) {
                $validated = $this->handleStep8Uploads($request, $validated);
            } elseif ($stepNumber === 9) {
                $validated = $this->handleStep9Uploads($request, $validated);
            }

            if ($stepNumber === 2 && isset($validated['documents']) && is_array($validated['documents'])) {
                $mergedDocs = array_merge($existingStep2Documents, $validated['documents']);
                $uniqueDocs = collect($mergedDocs)
                    ->filter(fn($doc) => is_array($doc) && !empty($doc['path'] ?? null))
                    ->unique('path')
                    ->values()
                    ->all();
                $validated['documents'] = $uniqueDocs;
            }

            if ($stepNumber === 3 && isset($validated['documents_files']) && is_array($validated['documents_files'])) {
                $mergedDocs = array_merge($existingStep3Documents, $validated['documents_files']);
                $uniqueDocs = collect($mergedDocs)
                    ->filter(fn($doc) => is_array($doc) && !empty($doc['path'] ?? null))
                    ->unique('path')
                    ->values()
                    ->all();
                $validated['documents_files'] = $uniqueDocs;
            } elseif ($stepNumber === 3 && !empty($existingStep3Documents)) {
                // Preserve existing documents if no new files uploaded
                $validated['documents_files'] = $existingStep3Documents;
            }

            // Merge documents for steps 4, 5, 6, 8, 9
            if (in_array($stepNumber, [4, 5, 6, 8, 9]) && isset($validated['documents']) && is_array($validated['documents'])) {
                $mergedDocs = array_merge($existingDocuments, $validated['documents']);
                $uniqueDocs = collect($mergedDocs)
                    ->filter(fn($doc) => is_array($doc) && !empty($doc['path'] ?? null))
                    ->unique('path')
                    ->values()
                    ->all();
                $validated['documents'] = $uniqueDocs;
            } elseif (in_array($stepNumber, [4, 5, 6, 8, 9]) && !empty($existingDocuments)) {
                // Preserve existing documents if no new files uploaded
                $validated['documents'] = $existingDocuments;
            }

            // Merge contracts for step 7
            if ($stepNumber === 7 && isset($validated['contract_paths']) && is_array($validated['contract_paths'])) {
                $mergedContracts = array_merge($existingContracts, $validated['contract_paths']);
                $uniqueContracts = collect($mergedContracts)
                    ->filter(fn($contract) => is_array($contract) && !empty($contract['path'] ?? null))
                    ->unique('path')
                    ->values()
                    ->all();
                $validated['contract_paths'] = $uniqueContracts;
                // Keep backward compatibility with single contract_path
                if (count($uniqueContracts) === 1) {
                    $validated['contract_path'] = $uniqueContracts[0]['path'];
                }
            } elseif ($stepNumber === 7 && !empty($existingContracts)) {
                // Preserve existing contracts if no new files uploaded
                $validated['contract_paths'] = $existingContracts;
                if (count($existingContracts) === 1) {
                    $validated['contract_path'] = $existingContracts[0]['path'];
                }
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
            // Refresh the steps relationship to ensure we have the latest step data
            $integration->load('steps');

            // Create driver after saving step 2 data (for driver type integrations)
            if (in_array($stepNumber, [2, 3], true) && $integration->type === 'driver') {
                try {
                    if ($stepNumber === 2) {
                        Log::info('Attempting to create driver from step 2', [
                            'integration_id' => $integration->id,
                            'step_id' => $step->id,
                        ]);
                        $this->createDriverFromStep2($integration);
                    }

                    $this->syncDriverDocuments($integration);
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

            // Check if this is a validation request
            $submitAction = $request->input('submit_action');
            
            if ($submitAction === 'validate') {
                // Refresh step and integration to ensure we have the latest data
                $step->refresh();
                $integration->refresh();
                $integration->load('steps');
                
                // Validate the step (this will check all requirements, mark as validated, and redirect to next step)
                return $this->validateStep($request, $integration, $stepNumber);
            }

            // Redirect back to the step page after saving (only if not validating)
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
    public function validateStep(Request $request, IntegrationCandidate $integration, int $stepNumber)
    {
        try {
            if ($integration->type !== 'driver' && in_array($stepNumber, self::DRIVER_ONLY_STEPS, true)) {
                $redirectStep = $this->resolveRedirectStep($integration, $stepNumber);
                return redirect()->route('integrations.step', ['integration' => $integration->id, 'stepNumber' => $redirectStep])
                    ->with('info', __('messages.step_not_required_for_admin'));
            }

            // Refresh integration to ensure we have latest data
            $integration->refresh();
            $integration->load('steps');
            
            $step = $integration->getStep($stepNumber);
            if (!$step) {
                return redirect()->route('integrations.step', ['integration' => $integration->id, 'stepNumber' => $stepNumber])
                    ->with('error', __('messages.step_not_found'));
            }
            
            // Refresh step to ensure we have the latest data
            $step->refresh();

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
                $requiredFields = [
                    'full_name',
                    'phone',
                    'cin',
                    'date_of_birth',
                    'address',
                ];

                if ($integration->type === 'driver') {
                    $requiredFields = array_merge($requiredFields, [
                    'license_number',
                    'license_type',
                    'license_issue_date',
                    ]);
                } else {
                    $requiredFields[] = 'email';
                }

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

            // Step 4: Must pass oral test (result = 'passed') to continue, else reject
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

            // Step 5: Must pass written test (result = 'passed') to continue, else reject
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

            // Step 6: Must pass driving test (result = 'passed') to continue, else reject
            if ($stepNumber === 6) {
                $stepData = $step->step_data ?? [];
                if (empty($stepData)) {
                    return redirect()->route('integrations.step', ['integration' => $integration->id, 'stepNumber' => $stepNumber])
                        ->with('error', __('messages.please_save_step_before_validation'));
                }
                
                $result = $step->getStepData('result');
                if ($result !== 'passed') {
                    $this->rejectIntegration($integration, __('messages.step6_failed'), auth()->id());
                    $step->rejectStep(
                        __('messages.result_must_be_passed'),
                        auth()->id()
                    );
                    return redirect()->route('integrations.step', ['integration' => $integration->id, 'stepNumber' => $stepNumber])
                        ->with('error', __('messages.step6_failed'));
                }
            }

            // Step 7: All sub-steps (validation, induction, contract) required
            if ($stepNumber === 7) {
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
                            ->with('error', __('messages.step7_substeps_required'));
                    }
                }
            }

            // Step 8: Must pass accompaniment (result = 'passed') to continue, else reject
            if ($stepNumber === 8) {
                $stepData = $step->step_data ?? [];
                if (empty($stepData)) {
                    return redirect()->route('integrations.step', ['integration' => $integration->id, 'stepNumber' => $stepNumber])
                        ->with('error', __('messages.please_save_step_before_validation'));
                }
                
                $result = $step->getStepData('result');
                if ($result !== 'passed') {
                    $this->rejectIntegration($integration, __('messages.step8_failed'), auth()->id());
                    $step->rejectStep(
                        __('messages.result_must_be_passed'),
                        auth()->id()
                    );
                    return redirect()->route('integrations.step', ['integration' => $integration->id, 'stepNumber' => $stepNumber])
                        ->with('error', __('messages.step8_failed'));
                }
            }

            // Step 9: Final validation unlocks promotion
            if ($stepNumber === 9) {
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

            // Mark the step as validated
            $step->validateStep(auth()->id(), $request->input('notes'));

            // Refresh integration to get latest step status
            $integration->refresh();

            // Move to next step if this step is validated and it's the current step
            if ($integration->current_step === $stepNumber && $stepNumber < 9) {
                $integration->moveToNextStep();
                $integration->refresh();
            }

            // Automatically redirect to next step after validation (if not the last step)
            if ($stepNumber < 9) {
                $nextStep = $this->getNextAllowedStep($integration, $stepNumber + 1);
                return redirect()->route('integrations.step', ['integration' => $integration->id, 'stepNumber' => $nextStep])
                    ->with('success', __('messages.step_validated_successfully') . ' ' . __('messages.redirecting_to_next_step'));
            }

            // If it's the last step, redirect back to current step
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
    public function rejectStep(Request $request, IntegrationCandidate $integration, int $stepNumber)
    {
        try {
            if ($integration->type !== 'driver' && in_array($stepNumber, self::DRIVER_ONLY_STEPS, true)) {
                $redirectStep = $this->resolveRedirectStep($integration, $stepNumber);
                return redirect()->route('integrations.step', ['integration' => $integration->id, 'stepNumber' => $redirectStep])
                    ->with('info', __('messages.step_not_required_for_admin'));
            }

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
            if (in_array($stepNumber, [3, 4, 5, 6, 8])) {
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
    public function finalize(IntegrationCandidate $integration)
    {
        try {
            // Check if Step 9 is validated
            $step9 = $integration->getStep(9);
            if (!$step9 || !$step9->isValidated()) {
                return redirect()->route('integrations.show', $integration->id)
                    ->with('error', __('messages.step9_must_be_validated'));
            }

            // Check if all applicable steps are validated
            foreach ($this->getApplicableStepNumbers($integration) as $i) {
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

            $errorMessage = $e->getMessage() === 'integration_email_exists'
                ? __('messages.user_email_exists')
                : __('messages.error_finalizing_integration');

            return redirect()->route('integrations.show', $integration->id)
                ->with('error', $errorMessage);
        }
    }

    /**
     * Get applicable step numbers depending on integration type.
     */
    private function getApplicableStepNumbers(IntegrationCandidate $integration): array
    {
        return $integration->type === 'driver'
            ? range(1, 9)
            : array_merge(range(1, 4), [7, 9]);
    }

    /**
     * Get the next allowed step number based on integration type.
     */
    private function getNextAllowedStep(IntegrationCandidate $integration, int $stepNumber): int
    {
        $steps = $this->getApplicableStepNumbers($integration);
        foreach ($steps as $step) {
            if ($step >= $stepNumber) {
                return $step;
            }
        }

        return end($steps);
    }

    /**
     * Determine the most appropriate redirect step for the integration.
     */
    private function resolveRedirectStep(IntegrationCandidate $integration, int $fallbackStep): int
    {
        $stepNumbers = $this->getApplicableStepNumbers($integration);
        if (in_array($integration->current_step, $stepNumbers, true)) {
            return $integration->current_step;
        }

        return $this->getNextAllowedStep($integration, $fallbackStep);
    }

    /**
     * Handle file uploads for Step 2.
     */
    private function handleStep2Uploads(Request $request, array $validated): array
    {
        try {
            // Handle photo upload
            if ($request->hasFile('photo')) {
                $photoPath = $request->file('photo')->store('integration/photos', 'uploads');
                $validated['photo_path'] = $photoPath;
            }

            // Handle documents upload
            if ($request->hasFile('documents')) {
                $documents = [];
                foreach ($request->file('documents') as $file) {
                    $docPath = $file->store('integration/documents', 'uploads');
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
        // Documents reviewed are stored as array of document names/paths
        if ($request->hasFile('documents_files')) {
            $documents = [];
            foreach ($request->file('documents_files') as $file) {
                $path = $file->store('integration/document-verification', 'uploads');
                $documents[] = [
                    'name' => $file->getClientOriginalName(),
                    'path' => $path,
                ];
            }
            $validated['documents_files'] = $documents;
        }

        return $validated;
    }

    /**
     * Handle file uploads for Step 4 (Oral Test).
     */
    private function handleStep4Uploads(Request $request, array $validated): array
    {
        if ($request->hasFile('documents')) {
            $documents = [];
            foreach ($request->file('documents') as $file) {
                $path = $file->store('integration/step4-documents', 'uploads');
                $documents[] = [
                    'name' => $file->getClientOriginalName(),
                    'path' => $path,
                ];
            }
            $validated['documents'] = $documents;
        }

        return $validated;
    }

    /**
     * Handle file uploads for Step 5 (Written Test).
     */
    private function handleStep5Uploads(Request $request, array $validated): array
    {
        if ($request->hasFile('documents')) {
            $documents = [];
            foreach ($request->file('documents') as $file) {
                $path = $file->store('integration/step5-documents', 'uploads');
                $documents[] = [
                    'name' => $file->getClientOriginalName(),
                    'path' => $path,
                ];
            }
            $validated['documents'] = $documents;
        }

        return $validated;
    }

    /**
     * Handle file uploads for Step 6 (Driving Test).
     */
    private function handleStep6Uploads(Request $request, array $validated): array
    {
        if ($request->hasFile('documents')) {
            $documents = [];
            foreach ($request->file('documents') as $file) {
                $path = $file->store('integration/step6-documents', 'uploads');
                $documents[] = [
                    'name' => $file->getClientOriginalName(),
                    'path' => $path,
                ];
            }
            $validated['documents'] = $documents;
        }

        return $validated;
    }

    /**
     * Handle file uploads for Step 7 (Contract).
     */
    private function handleStep7Uploads(Request $request, array $validated): array
    {
        if ($request->hasFile('contract')) {
            $contracts = [];
            foreach ($request->file('contract') as $file) {
                $path = $file->store('integration/contracts', 'uploads');
                $contracts[] = [
                    'name' => $file->getClientOriginalName(),
                    'path' => $path,
                ];
            }
            $validated['contract_paths'] = $contracts;
            // Keep backward compatibility with single contract_path
            if (count($contracts) === 1) {
                $validated['contract_path'] = $contracts[0]['path'];
            }
        }

        return $validated;
    }

    /**
     * Handle file uploads for Step 8 (Accompaniment).
     */
    private function handleStep8Uploads(Request $request, array $validated): array
    {
        if ($request->hasFile('documents')) {
            $documents = [];
            foreach ($request->file('documents') as $file) {
                $path = $file->store('integration/step8-documents', 'uploads');
                $documents[] = [
                    'name' => $file->getClientOriginalName(),
                    'path' => $path,
                ];
            }
            $validated['documents'] = $documents;
        }

        return $validated;
    }

    /**
     * Handle file uploads for Step 9 (Final Validation).
     */
    private function handleStep9Uploads(Request $request, array $validated): array
    {
        if ($request->hasFile('documents')) {
            $documents = [];
            foreach ($request->file('documents') as $file) {
                $path = $file->store('integration/step9-documents', 'uploads');
                $documents[] = [
                    'name' => $file->getClientOriginalName(),
                    'path' => $path,
                ];
            }
            $validated['documents'] = $documents;
        }

        return $validated;
    }

    /**
     * Synchronize driver documents from step data (steps 2 & 3).
     */
    private function syncDriverDocuments(IntegrationCandidate $integration): void
    {
        if (!$integration->driver_id) {
            return;
        }

        $driver = Driver::find($integration->driver_id);
        if (!$driver) {
            return;
        }

        $documents = collect($driver->documents ?? []);

        foreach ([2, 3] as $stepNumber) {
            $step = $integration->getStep($stepNumber);
            if (!$step) {
                continue;
            }

            $stepData = $step->step_data ?? [];
            if (!is_array($stepData)) {
                $stepData = [];
            }

            $key = $stepNumber === 2 ? 'documents' : 'documents_files';
            if (!empty($stepData[$key]) && is_array($stepData[$key])) {
                $documents = $documents->merge($stepData[$key]);
            }
        }

        $documents = $documents
            ->filter(fn($doc) => is_array($doc) && !empty($doc['path'] ?? null))
            ->unique('path')
            ->values()
            ->all();

        $driver->update(['documents' => $documents]);
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

            $email = $step2Data['email'] ?? null;
            if (empty($email)) {
                throw new \Exception('Email is required to promote integration candidate to user.');
            }

            if (User::where('email', $email)->exists()) {
                throw new \RuntimeException('integration_email_exists');
            }

            $user = User::create([
                'name' => $step2Data['full_name'] ?? '',
                'email' => $email,
                'email_verified_at' => now(),
                'password' => $tempPassword,
                'phone' => $step2Data['phone'] ?? null,
                'department' => 'other',
                'role' => 'manager',
                'status' => 'inactive',
            ]);
            
        } catch (\Throwable $e) {
            Log::error('Failed to promote to user', [
                'error' => $e->getMessage(),
            ]);
            throw $e;
        }
    }
}
