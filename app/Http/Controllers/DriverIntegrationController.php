<?php

namespace App\Http\Controllers;

use App\Models\Driver;
use App\Models\DriverIntegration;
use App\Models\DriverIntegrationStep;
use App\Models\FormationType;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\View\View;
use Illuminate\Http\RedirectResponse;

class DriverIntegrationController extends Controller
{
    /**
     * Show the integrations index page with list of integrated drivers
     * If driver_id is provided in query, create integration and redirect to step
     */
    public function index(Request $request): View|RedirectResponse
    {
        $driverId = $request->input('driver_id');
        
        // If driver_id is provided, create integration and go to first step
        if ($driverId) {
            $driver = Driver::findOrFail($driverId);
            
            // Check if integration already exists
            $integration = $driver->integration;
            
            if (!$integration) {
                $integration = DriverIntegration::create([
                    'driver_id' => $driver->id,
                    'current_step' => DriverIntegration::STEP_IDENTIFICATION_BESOIN,
                    'status' => 'in_progress',
                    'started_at' => now(),
                ]);
            }
            
            return redirect()->route('drivers.integrations.step', [
                'integration' => $integration->id,
                'step' => DriverIntegration::STEP_IDENTIFICATION_BESOIN
            ]);
        }
        
        // Show initial page to select driver
        $drivers = Driver::where('is_integrated', 0)->get();

        // Get all drivers with integrations (in progress, validated, or rejected)
        $integrations = DriverIntegration::with(['driver', 'steps'])
            ->orderBy('created_at', 'desc')
            ->get();
        
        // Calculate progress for each integration
        $integrationsWithProgress = $integrations->map(function ($integration) {
            $stepsOrder = DriverIntegration::getStepsOrder();
            $completedSteps = $integration->steps()->where('status', 'passed')->count();
            $totalSteps = count($stepsOrder);
            $progressPercentage = $totalSteps > 0 ? round(($completedSteps / $totalSteps) * 100) : 0;
            
            $currentStepLabel = null;
            if ($integration->current_step) {
                $currentStepLabel = $this->getStepLabel($integration->current_step);
            }
            
            return [
                'integration' => $integration,
                'driver' => $integration->driver,
                'progress_percentage' => $progressPercentage,
                'completed_steps' => $completedSteps,
                'total_steps' => $totalSteps,
                'current_step_label' => $currentStepLabel,
            ];
        });
        
        return view('drivers.integrations.index', [
            'drivers' => $drivers,
            'integrationsWithProgress' => $integrationsWithProgress,
        ]);
    }

    /**
     * Create a new integration for a driver
     */
    public function create(Request $request): RedirectResponse
    {
        $request->validate([
            'driver_id' => 'required|exists:drivers,id',
        ]);

        $driver = Driver::findOrFail($request->driver_id);
        
        // Check if integration already exists
        $integration = $driver->integration;
        
        if (!$integration) {
            $integration = DriverIntegration::create([
                'driver_id' => $driver->id,
                'current_step' => DriverIntegration::STEP_IDENTIFICATION_BESOIN,
                'status' => 'in_progress',
                'started_at' => now(),
            ]);
        }
        
        return redirect()->route('drivers.integrations.step', [
            'integration' => $integration->id,
            'step' => DriverIntegration::STEP_IDENTIFICATION_BESOIN
        ]);
    }

    /**
     * Show a specific step of the integration process
     */
    public function showStep(DriverIntegration $integration, string $step): View|RedirectResponse
    {
        $stepsOrder = DriverIntegration::getStepsOrder();
        
        // Validate step key
        if (!in_array($step, $stepsOrder)) {
            return redirect()->route('drivers.integrations')
                ->with('error', __('messages.invalid_step'));
        }

        // For step 1, if integration doesn't exist yet, create a placeholder
        // This allows the form to work even if integration was just created
        if ($step === DriverIntegration::STEP_IDENTIFICATION_BESOIN && !$integration->driver_id) {
            // This is OK - user will select driver in step 1 form
            // Ensure integration has a current_step set
            if (!$integration->current_step) {
                $integration->update(['current_step' => DriverIntegration::STEP_IDENTIFICATION_BESOIN]);
            }
        } elseif (!$integration->driver_id) {
            // For other steps, driver must be set
            return redirect()->route('drivers.integrations.step', [
                'integration' => $integration->id,
                'step' => DriverIntegration::STEP_IDENTIFICATION_BESOIN
            ])->with('error', __('messages.select_driver_first'));
        }

        // Load existing step data
        $stepData = [];
        $existingStep = $integration->getStep($step);
        if ($existingStep) {
            $stepData = $existingStep->payload ?? [];
        }

        // Add driver_id to stepData for step 1 if not already set
        if ($step === DriverIntegration::STEP_IDENTIFICATION_BESOIN && $integration->driver_id) {
            $stepData['driver_id'] = $integration->driver_id;
        }

        // Calculate progress
        $currentIndex = array_search($step, $stepsOrder);
        $completedSteps = $integration->steps()->where('status', 'passed')->count();
        $totalSteps = count($stepsOrder);
        $progressPercentage = $totalSteps > 0 ? round(($completedSteps / $totalSteps) * 100) : 0;

        // Get step label and all step labels for sidebar
        $stepLabel = $this->getStepLabel($step);
        $stepLabels = [];
        foreach ($stepsOrder as $stepKey) {
            $stepLabels[$stepKey] = $this->getStepLabel($stepKey);
        }

        return view('drivers.integrations.step', [
            'integration' => $integration,
            'driver' => $integration->driver,
            'step' => $step,
            'stepLabel' => $stepLabel,
            'stepLabels' => $stepLabels,
            'stepData' => $stepData,
            'stepsOrder' => $stepsOrder,
            'currentIndex' => $currentIndex,
            'progressPercentage' => $progressPercentage,
            'canGoNext' => $this->canProceedToNextStep($integration, $step),
            'canGoPrevious' => $currentIndex > 0,
        ]);
    }

    /**
     * Save step data and navigate
     */
    public function saveStep(Request $request, DriverIntegration $integration, string $step): RedirectResponse
    {
        $stepsOrder = DriverIntegration::getStepsOrder();
        
        // Validate step key
        if (!in_array($step, $stepsOrder)) {
            return redirect()->route('drivers.integrations')
                ->with('error', __('messages.invalid_step'));
        }

        try {
            // Get and validate step data based on step type
            $stepData = $this->validateStepData($request, $step);
            
            // Handle step 1: create integration if driver is selected
            if ($step === DriverIntegration::STEP_IDENTIFICATION_BESOIN && isset($stepData['driver_id'])) {
                $driver = Driver::findOrFail($stepData['driver_id']);
                
                // Update integration with driver if not already set
                if (!$integration->driver_id) {
                    $integration->update([
                        'driver_id' => $driver->id,
                        'current_step' => DriverIntegration::STEP_IDENTIFICATION_BESOIN,
                        'status' => 'in_progress',
                        'started_at' => $integration->started_at ?? now(),
                    ]);
                }
                
                // Remove driver_id from stepData as it's stored in integration
                unset($stepData['driver_id']);
            }

            // Handle step-specific logic (rejections)
            $shouldReject = $this->handleStepLogic($integration, $step, $stepData);
            
            if ($shouldReject) {
                $integration->update(['status' => 'rejected']);
                return redirect()->route('drivers.integrations')
                    ->with('error', __('messages.integration_rejected'));
            }

            // Determine step status based on step type
            $stepStatus = $this->determineStepStatus($step, $stepData);

            // Save step data
            $integrationStep = $integration->steps()->updateOrCreate(
                ['step_key' => $step],
                [
                    'payload' => $stepData,
                    'status' => $stepStatus,
                    'validated_by' => auth()->id(),
                    'validated_at' => now(),
                ]
            );

            // Check if this is the final step and decision is validated
            $isFinalStep = $step === DriverIntegration::STEP_VALIDATION_FINALE;
            $isValidated = isset($stepData['decision']) && $stepData['decision'] === 'validated';
            
            if ($isFinalStep && $isValidated && $stepStatus === 'passed') {
                // Mark integration as validated and completed
                $integration->update([
                    'status' => 'validated',
                    'completed_at' => now(),
                    'current_step' => $step,
                ]);
                
                // Mark driver as integrated
                if ($integration->driver_id) {
                    $driver = Driver::find($integration->driver_id);
                    if ($driver) {
                        $driver->update([
                            'is_integrated' => true,
                            'date_integration' => now()->toDateString(),
                        ]);
                    }
                }
            } else {
                // Update integration current step
                $integration->update(['current_step' => $step]);
            }

            // Determine next action
            $action = $request->input('action', 'save');
            
            if ($action === 'next') {
                return $this->goToNextStep($integration, $step);
            } elseif ($action === 'previous') {
                return $this->goToPreviousStep($integration, $step);
            } else {
                // Just save, stay on current step
                return redirect()->route('drivers.integrations.step', [
                    'integration' => $integration->id,
                    'step' => $step
                ])->with('success', __('messages.step_saved'));
            }

        } catch (\Exception $e) {
            Log::error('Failed to save integration step', [
                'integration_id' => $integration->id,
                'step' => $step,
                'error' => $e->getMessage(),
            ]);

            return redirect()->back()
                ->withInput()
                ->with('error', __('messages.step_save_error'));
        }
    }

    /**
     * Validate step data based on step type
     */
    protected function validateStepData(Request $request, string $step): array
    {
        $rules = $this->getStepValidationRules($step);
        $validated = $request->validate($rules);
        
        // Convert checkbox values to boolean
        return $this->normalizeStepData($validated, $step);
    }

    /**
     * Get validation rules for a specific step
     */
    protected function getStepValidationRules(string $step): array
    {
        return match($step) {
            DriverIntegration::STEP_IDENTIFICATION_BESOIN => [
                'identification_besoin' => 'required|string',
                'description_poste' => 'required|string',
                'prospection' => 'required|string',
                'driver_id' => 'required|exists:drivers,id',
            ],
            DriverIntegration::STEP_DESCRIPTION_POSTE => [
                'description' => 'required|string',
                'responsabilites' => 'required|string',
                'qualifications' => 'required|string',
                'conditions' => 'nullable|string',
            ],
            DriverIntegration::STEP_PROSPECTION => [
                'details' => 'required|string',
                'date_debut' => 'nullable|date',
                'candidats_contactes' => 'nullable|integer|min:0',
            ],
            DriverIntegration::STEP_SELECTION_DOSSIER => [
                'review_date' => 'required|date',
                'reviewed_by' => 'required|string',
                'evaluation' => 'required|string',
                'decision' => 'required|in:accepted,rejected,pending',
            ],
            DriverIntegration::STEP_VERIFICATION_DOCUMENTAIRE => [
                'verification_result' => 'required|in:passed,failed',
                'verified_by' => 'required|string',
                'verification_date' => 'required|date',
                'notes' => 'nullable|string',
            ],
            DriverIntegration::STEP_SELECTION_ENTRETIEN => [
                'interview_date' => 'required|date',
                'interviewed_by' => 'required|string',
                'evaluation' => 'required|string',
                'decision' => 'required|in:accepted,rejected,pending',
            ],
            DriverIntegration::STEP_TEST_ORAL_ECRIT => [
                'test_date' => 'required|date',
                'oral_score' => 'required|numeric|min:0|max:100',
                'written_score' => 'required|numeric|min:0|max:100',
                'test_result' => 'required|in:passed,failed',
                'evaluator' => 'required|string',
            ],
            DriverIntegration::STEP_TEST_CONDUITE => [
                'test_date' => 'required|date',
                'score' => 'required|numeric|min:0|max:100',
                'test_result' => 'required|in:passed,failed',
                'instructor' => 'required|string',
                'notes' => 'nullable|string',
            ],
            DriverIntegration::STEP_VALIDATION => [
                'validated_by' => 'required|string',
                'validation_date' => 'required|date',
                'decision' => 'required|in:validated,rejected',
                'notes' => 'nullable|string',
            ],
            DriverIntegration::STEP_INDUCTION => [
                'induction_date' => 'required|date',
                'conducted_by' => 'required|string',
                'completed' => 'required|boolean',
            ],
            DriverIntegration::STEP_SIGNATURE_CONTRAT => [
                'contract_signed_date' => 'required|date',
                'signed_by' => 'required|string',
                'contract_path' => 'nullable|string',
            ],
            DriverIntegration::STEP_ACCOMPAGNEMENT => [
                'accompaniment_result' => 'required|in:passed,failed',
                'accompanied_by' => 'required|string',
                'end_date' => 'required|date',
                'notes' => 'nullable|string',
            ],
            DriverIntegration::STEP_VALIDATION_FINALE => [
                'validated_by' => 'required|string',
                'validation_date' => 'required|date',
                'decision' => 'required|in:validated,rejected',
                'notes' => 'nullable|string',
            ],
            default => [],
        };
    }

    /**
     * Normalize step data (handle checkboxes, etc.)
     */
    protected function normalizeStepData(array $data, string $step): array
    {
        // Handle checkbox fields
        if (isset($data['documents']) && is_array($data['documents'])) {
            foreach ($data['documents'] as $key => $value) {
                $data['documents'][$key] = (bool) $value;
            }
        }

        if (isset($data['methodes']) && is_array($data['methodes'])) {
            foreach ($data['methodes'] as $key => $value) {
                $data['methodes'][$key] = (bool) $value;
            }
        }

        // Handle completed checkbox
        if (isset($data['completed'])) {
            $data['completed'] = (bool) ($data['completed'] ?? false);
        }

        return $data;
    }

    /**
     * Handle step-specific logic (rejections, etc.)
     */
    protected function handleStepLogic(DriverIntegration $integration, string $step, array $stepData): bool
    {
        return match($step) {
            DriverIntegration::STEP_VERIFICATION_DOCUMENTAIRE => 
                ($stepData['verification_result'] ?? '') === 'failed',
            DriverIntegration::STEP_TEST_ORAL_ECRIT => 
                ($stepData['test_result'] ?? '') === 'failed',
            DriverIntegration::STEP_TEST_CONDUITE => 
                ($stepData['test_result'] ?? '') === 'failed',
            DriverIntegration::STEP_ACCOMPAGNEMENT => 
                ($stepData['accompaniment_result'] ?? '') === 'failed',
            default => false,
        };
    }

    /**
     * Check if can proceed to next step
     */
    protected function canProceedToNextStep(DriverIntegration $integration, string $currentStep): bool
    {
        $stepsOrder = DriverIntegration::getStepsOrder();
        $currentIndex = array_search($currentStep, $stepsOrder);
        
        if ($currentIndex === false || $currentIndex >= count($stepsOrder) - 1) {
            return false;
        }

        // Check if current step is passed
        $currentStepRecord = $integration->getStep($currentStep);
        return $currentStepRecord && $currentStepRecord->status === 'passed';
    }

    /**
     * Navigate to next step
     */
    protected function goToNextStep(DriverIntegration $integration, string $currentStep): RedirectResponse
    {
        $stepsOrder = DriverIntegration::getStepsOrder();
        $currentIndex = array_search($currentStep, $stepsOrder);
        
        if ($currentIndex !== false && $currentIndex < count($stepsOrder) - 1) {
            $nextStep = $stepsOrder[$currentIndex + 1];
            return redirect()->route('drivers.integrations.step', [
                'integration' => $integration->id,
                'step' => $nextStep
            ]);
        }

        return redirect()->route('drivers.integrations.step', [
            'integration' => $integration->id,
            'step' => $currentStep
        ]);
    }

    /**
     * Navigate to previous step
     */
    protected function goToPreviousStep(DriverIntegration $integration, string $currentStep): RedirectResponse
    {
        $stepsOrder = DriverIntegration::getStepsOrder();
        $currentIndex = array_search($currentStep, $stepsOrder);
        
        if ($currentIndex !== false && $currentIndex > 0) {
            $previousStep = $stepsOrder[$currentIndex - 1];
            return redirect()->route('drivers.integrations.step', [
                'integration' => $integration->id,
                'step' => $previousStep
            ]);
        }

        return redirect()->route('drivers.integrations.step', [
            'integration' => $integration->id,
            'step' => $currentStep
        ]);
    }

    /**
     * Determine step status based on step data
     */
    protected function determineStepStatus(string $step, array $stepData): string
    {
        // Steps that can be rejected
        if (in_array($step, [
            DriverIntegration::STEP_VERIFICATION_DOCUMENTAIRE,
            DriverIntegration::STEP_TEST_ORAL_ECRIT,
            DriverIntegration::STEP_TEST_CONDUITE,
            DriverIntegration::STEP_ACCOMPAGNEMENT,
        ])) {
            $resultKey = match($step) {
                DriverIntegration::STEP_VERIFICATION_DOCUMENTAIRE => 'verification_result',
                DriverIntegration::STEP_TEST_ORAL_ECRIT => 'test_result',
                DriverIntegration::STEP_TEST_CONDUITE => 'test_result',
                DriverIntegration::STEP_ACCOMPAGNEMENT => 'accompaniment_result',
                default => 'result',
            };
            
            return ($stepData[$resultKey] ?? '') === 'failed' ? 'failed' : 'passed';
        }

        // Steps with decision field
        if (in_array($step, [
            DriverIntegration::STEP_SELECTION_DOSSIER,
            DriverIntegration::STEP_SELECTION_ENTRETIEN,
            DriverIntegration::STEP_VALIDATION,
            DriverIntegration::STEP_VALIDATION_FINALE,
        ])) {
            $decision = $stepData['decision'] ?? 'pending';
            return match($decision) {
                'accepted', 'validated' => 'passed',
                'rejected' => 'failed',
                default => 'pending',
            };
        }

        // Default: passed if data is saved
        return 'passed';
    }

    /**
     * Get step label
     */
    public function getStepLabel(string $stepKey): string
    {
        $labels = [
            DriverIntegration::STEP_IDENTIFICATION_BESOIN => __('messages.identification_besoin'),
            DriverIntegration::STEP_DESCRIPTION_POSTE => __('messages.description_poste'),
            DriverIntegration::STEP_PROSPECTION => __('messages.prospection'),
            DriverIntegration::STEP_SELECTION_DOSSIER => __('messages.selection_dossier'),
            DriverIntegration::STEP_VERIFICATION_DOCUMENTAIRE => __('messages.verification_documentaire'),
            DriverIntegration::STEP_SELECTION_ENTRETIEN => __('messages.selection_entretien'),
            DriverIntegration::STEP_TEST_ORAL_ECRIT => __('messages.test_oral_ecrit'),
            DriverIntegration::STEP_TEST_CONDUITE => __('messages.test_conduite'),
            DriverIntegration::STEP_VALIDATION => __('messages.validation'),
            DriverIntegration::STEP_INDUCTION => __('messages.induction'),
            DriverIntegration::STEP_SIGNATURE_CONTRAT => __('messages.signature_contrat'),
            DriverIntegration::STEP_ACCOMPAGNEMENT => __('messages.accompagnement'),
            DriverIntegration::STEP_VALIDATION_FINALE => __('messages.validation_finale'),
        ];

        return $labels[$stepKey] ?? $stepKey;
    }
}
