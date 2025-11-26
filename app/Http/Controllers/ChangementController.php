<?php

namespace App\Http\Controllers;

use App\Models\Changement;
use App\Models\ChangementStep;
use App\Models\ChangementChecklistResult;
use App\Models\ChangementType;
use App\Models\PrincipaleCretaire;
use App\Models\SousCretaire;
use App\Models\Driver;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Throwable;

class ChangementController extends Controller
{
    /**
     * Display a listing of all changements.
     */
    public function index(Request $request): View|RedirectResponse
    {
        try {
            $query = Changement::query()
                ->with(['changementType', 'steps', 'subject'])
                ->orderBy('created_at', 'desc');

            // Filter by status if provided
            if ($request->has('status') && $request->status !== 'all') {
                $query->where('status', $request->status);
            }

            // Filter by changement type if provided
            if ($request->has('changement_type_id') && $request->changement_type_id !== 'all') {
                $query->where('changement_type_id', $request->changement_type_id);
            }

            // Filter by date range if provided
            if ($request->has('date_from') && $request->date_from) {
                $query->where('date_changement', '>=', $request->date_from);
            }
            if ($request->has('date_to') && $request->date_to) {
                $query->where('date_changement', '<=', $request->date_to);
            }

            // Get stats before pagination (from base query without status filter)
            $baseQuery = Changement::query()
                ->with(['changementType', 'steps', 'subject'])
                ->orderBy('created_at', 'desc');

            // Apply same filters except status
            if ($request->has('changement_type_id') && $request->changement_type_id !== 'all') {
                $baseQuery->where('changement_type_id', $request->changement_type_id);
            }
            if ($request->has('date_from') && $request->date_from) {
                $baseQuery->where('date_changement', '>=', $request->date_from);
            }
            if ($request->has('date_to') && $request->date_to) {
                $baseQuery->where('date_changement', '<=', $request->date_to);
            }

            $totalCount = (clone $baseQuery)->count();
            $draftCount = (clone $baseQuery)->where('status', 'draft')->count();
            $inProgressCount = (clone $baseQuery)->where('status', 'in_progress')->count();
            $completedCount = (clone $baseQuery)->where('status', 'completed')->count();
            $approvedCount = (clone $baseQuery)->where('status', 'approved')->count();

            $changements = $query->paginate(15);

            // Get changement types for filter
            $changementTypes = ChangementType::where('is_active', true)
                ->orderBy('name')
                ->get();

            return view('changements.index', compact('changements', 'changementTypes', 'totalCount', 'draftCount', 'inProgressCount', 'completedCount', 'approvedCount'));
        } catch (Throwable $e) {
            Log::error('Failed to display changements', [
                'error' => $e->getMessage(),
            ]);
            return redirect()->route('changements.index')
                ->with('error', __('messages.changements_index_error'));
        }
    }

    /**
     * Show the form for creating a new changement (Step 1).
     */
    public function create(): View
    {
        $changementTypes = ChangementType::where('is_active', true)
            ->orderBy('name')
            ->get();

        // Get active drivers and users for subject selection
        $drivers = Driver::where('status', 'active')
            ->orWhere('status', 'actif')
            ->orderBy('full_name')
            ->get();

        // Get users for administrative members (excluding admin role)
        $users = User::where('role', '!=', 'admin')
            ->where(function($query) {
                $query->where('status', 'active')
                      ->orWhere('status', 'actif');
            })
            ->orderBy('name')
            ->get();

        return view('changements.create', compact('changementTypes', 'drivers', 'users'));
    }

    /**
     * Store a newly created changement (Step 1 data).
     */
    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'changement_type_id' => ['required', 'exists:changement_types,id'],
            'subject_type' => ['nullable', 'in:driver,administrative'],
            'subject_id' => ['nullable', 'required_with:subject_type', 'integer'],
            'date_changement' => ['required', 'date'],
            'description_changement' => ['required', 'string'],
            'responsable_changement' => ['required', 'in:RH,DGA,QHSE'],
            'impact' => ['nullable', 'string'],
            'action' => ['required', 'string'],
        ]);

        try {
            // Determine subject_type and subject_id based on selection
            $subjectType = null;
            $subjectId = null;

            if ($request->filled('subject_type') && $request->filled('subject_id')) {
                if ($request->subject_type === 'driver') {
                    $subjectType = Driver::class;
                    // Validate driver exists
                    if (!Driver::find($request->subject_id)) {
                        return back()
                            ->withInput()
                            ->withErrors(['subject_id' => __('validation.exists', ['attribute' => 'driver'])]);
                    }
                } elseif ($request->subject_type === 'administrative') {
                    $subjectType = User::class;
                    // Validate user exists
                    if (!User::find($request->subject_id)) {
                        return back()
                            ->withInput()
                            ->withErrors(['subject_id' => __('validation.exists', ['attribute' => 'user'])]);
                    }
                }
                $subjectId = $request->subject_id;
            }

            $changement = Changement::create([
                'changement_type_id' => $validated['changement_type_id'],
                'subject_type' => $subjectType,
                'subject_id' => $subjectId,
                'date_changement' => $validated['date_changement'],
                'description_changement' => $validated['description_changement'],
                'responsable_changement' => $validated['responsable_changement'],
                'impact' => $validated['impact'] ?? null,
                'action' => $validated['action'] ?? null,
                'status' => 'draft',
                'current_step' => 1,
                'created_by' => Auth::id() ? (string) Auth::id() : null,
            ]);

            // Create Step 1 record and mark as validated (since it's the initial step)
            ChangementStep::create([
                'changement_id' => $changement->id,
                'step_number' => 1,
                'step_data' => $validated,
                'status' => 'validated',
                'validated_by' => Auth::id() ? (string) Auth::id() : null,
                'validated_at' => now(),
            ]);

            // Update changement to move to step 2
            $changement->update([
                'current_step' => 2,
                'status' => 'in_progress',
            ]);

            // Automatically redirect to Step 2
            return redirect()->route('changements.step', ['changement' => $changement->id, 'stepNumber' => 2])
                ->with('success', __('messages.changements_store_success'));
        } catch (Throwable $e) {
            Log::error('Failed to create changement', [
                'error' => $e->getMessage(),
            ]);
            return back()
                ->withInput()
                ->with('error', __('messages.changements_store_error'));
        }
    }

    /**
     * Display the specified changement progress.
     */
    public function show(Changement $changement): View|RedirectResponse
    {
        try {
            $changement->load(['steps', 'changementType', 'subject']);

            $stepNumbers = range(1, 6);

            // Get all steps with their status
            $steps = [];
            foreach ($stepNumbers as $i) {
                $step = $changement->getStep($i);
                $steps[$i] = [
                    'step' => $step,
                    'can_access' => $changement->canProceedToStep($i),
                    'is_validated' => $step && $step->isValidated(),
                    'is_rejected' => $step && $step->isRejected(),
                    'is_pending' => $step && $step->isPending(),
                ];
            }

            return view('changements.show', compact('changement', 'steps', 'stepNumbers'));
        } catch (Throwable $e) {
            Log::error('Failed to display changement', [
                'changement_id' => $changement->id,
                'error' => $e->getMessage(),
            ]);
            return redirect()->route('changements.index')
                ->with('error', __('messages.changements_show_error'));
        }
    }

    /**
     * Display a specific step form.
     */
    public function step(Changement $changement, int $stepNumber): View|RedirectResponse
    {
        try {
            // Validate step number
            if ($stepNumber < 1 || $stepNumber > 6) {
                return redirect()->route('changements.show', $changement->id)
                    ->with('error', __('messages.changements_step_invalid'));
            }

            // Check if changement is rejected or already validated
            if ($changement->isRejected()) {
                return redirect()->route('changements.show', $changement->id)
                    ->with('error', __('messages.changements_step_rejected'));
            }

            $changement->load(['steps', 'changementType', 'subject']);

            $stepNumbers = range(1, 6);

            // Get all steps with their status
            $steps = [];
            foreach ($stepNumbers as $i) {
                $step = $changement->getStep($i);
                $steps[$i] = [
                    'step' => $step,
                    'can_access' => $changement->canProceedToStep($i),
                    'is_validated' => $step && $step->isValidated(),
                    'is_rejected' => $step && $step->isRejected(),
                    'is_pending' => $step && $step->isPending(),
                ];
            }

            if ($changement->isValidated()) {
                // Allow viewing validated changements
                $step = $changement->getStep($stepNumber);
                if (!$step) {
                    return redirect()->route('changements.show', $changement->id)
                        ->with('error', __('messages.changements_step_not_found'));
                }
                return view('changements.show', compact('changement', 'stepNumber', 'step', 'steps', 'stepNumbers'));
            }

            // Get the step
            $step = $changement->getStep($stepNumber);
            
            $canAccess = false;
        
            if ($stepNumber === 1) {
                $canAccess = true;
            } elseif ($step && $step->isValidated()) {
                // Can access validated steps for viewing
                $canAccess = true;
            } elseif ($changement->current_step === $stepNumber) {
                // Can access current step for editing
                $canAccess = true;
            } else {
                // Check if all previous steps are validated
                $canAccess = $changement->canProceedToStep($stepNumber);
            }

            if (!$canAccess) {
                return redirect()->route('changements.show', $changement->id)
                    ->with('error', __('messages.changements_step_must_validate_previous'));
            }

            // Get or create step record if it doesn't exist
            if (!$step) {
                $step = ChangementStep::create([
                    'changement_id' => $changement->id,
                    'step_number' => $stepNumber,
                    'status' => 'pending',
                ]);
            }

            return view('changements.show', compact('changement', 'stepNumber', 'step', 'steps', 'stepNumbers'));
        } catch (Throwable $e) {
            Log::error('Failed to display step', [
                'error' => $e->getMessage(),
            ]);
            return redirect()->route('changements.index')
                ->with('error', __('messages.changements_step_error'));
        }
    }

    /**
     * Save step data.
     */
    public function saveStep(Request $request, Changement $changement, int $stepNumber): RedirectResponse
    {
        try {
            // Validate step number
            if ($stepNumber < 1 || $stepNumber > 6) {
                return redirect()->route('changements.show', $changement->id)
                    ->with('error', __('messages.changements_step_invalid'));
            }

            // Check if changement is rejected
            if ($changement->isRejected()) {
                return redirect()->route('changements.show', $changement->id)
                    ->with('error', __('messages.changements_step_rejected'));
            }

            // Check if changement is already validated (read-only)
            if ($changement->isValidated()) {
                return redirect()->route('changements.show', $changement->id)
                    ->with('error', __('messages.changements_save_step_already_finalized'));
            }

            // Get the step
            $step = $changement->getStep($stepNumber);
        
            // Access rules for saving:
            // 1. Can save current step
            // 2. Can save validated steps (for updates)
            // 3. Cannot save future steps unless previous are validated
            $canSave = false;
            
            if ($changement->current_step === $stepNumber) {
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
                $canSave = $changement->canProceedToStep($stepNumber);
            }

            if (!$canSave) {
                return redirect()->route('changements.show', $changement->id)
                    ->with('error', __('messages.changements_save_step_cannot_modify'));
            }

            // Get or create step
            $step = $changement->getStep($stepNumber);
            if (!$step) {
                $step = ChangementStep::create([
                    'changement_id' => $changement->id,
                    'step_number' => $stepNumber,
                    'status' => 'pending',
                ]);
            }

            // Validate based on step number
            $validated = [];
            if ($stepNumber >= 2 && $stepNumber <= 5) {
                // Steps 2-5: Store all form data in step_data JSON
                $validated = $request->except(['_token', 'submit_action']);
            }

            // Update step data
            if (!empty($validated)) {
                $step->setStepDataArray($validated);
                $step->save();
            }

            // Refresh step to get latest data
            $step->refresh();

            // Update changement status
            if ($changement->status === 'draft') {
                $changement->update(['status' => 'in_progress']);
            }

            // Refresh changement to get latest data
            $changement->refresh();
            $changement->load('steps');

            // Check if this is a validation request or update request
            $submitAction = $request->input('submit_action');
            
            if ($submitAction === 'validate') {
                // Refresh step and changement to ensure we have the latest data
                $step->refresh();
                $changement->refresh();
                $changement->load('steps');
                
                // Validate the step
                return $this->validateStep($request, $changement, $stepNumber);
            }

            // Redirect back to the step page after saving/updating
            $successMessage = $submitAction === 'update' 
                ? __('messages.changements_update_step_success')
                : __('messages.changements_save_step_success');
            
            return redirect()->route('changements.step', ['changement' => $changement->id, 'stepNumber' => $stepNumber])
                ->with('success', $successMessage);
        } catch (Throwable $e) {
            Log::error('Failed to save step', [
                'changement_id' => $changement->id,
                'step_number' => $stepNumber,
                'error' => $e->getMessage(),
            ]);
            return back()
                ->withInput()
                ->with('error', __('messages.changements_save_step_error'));
        }
    }

    /**
     * Validate a step.
     */
    public function validateStep(Request $request, Changement $changement, int $stepNumber): RedirectResponse
    {
        try {
            // Refresh changement to ensure we have latest data
            $changement->refresh();
            $changement->load('steps');
            
            $step = $changement->getStep($stepNumber);
            if (!$step) {
                return redirect()->route('changements.step', ['changement' => $changement->id, 'stepNumber' => $stepNumber])
                    ->with('error', __('messages.changements_step_not_found'));
            }
            
            // Refresh step to ensure we have the latest data
            $step->refresh();

            // Basic validation: step must have data
            if (empty($step->step_data) && $stepNumber > 1) {
                return redirect()->route('changements.step', ['changement' => $changement->id, 'stepNumber' => $stepNumber])
                    ->with('error', __('messages.changements_validate_step_save_first'));
            }

            // Mark the step as validated
            $step->validateStep(Auth::id() ? (string) Auth::id() : null, $request->input('notes'));

            // Refresh changement to get latest step status
            $changement->refresh();

            // Move to next step if this step is validated and it's the current step
            if ($changement->current_step === $stepNumber && $stepNumber < 6) {
                $changement->moveToNextStep();
                $changement->refresh();
            }

            // Automatically redirect to next step after validation (if not the last step)
            if ($stepNumber < 6) {
                $nextStep = $stepNumber + 1;
                return redirect()->route('changements.step', ['changement' => $changement->id, 'stepNumber' => $nextStep])
                    ->with('success', __('messages.changements_validate_step_success'));
            }

            // If it's step 5, redirect to checklist (step 6)
            if ($stepNumber === 5) {
                return redirect()->route('changements.checklist', $changement)
                    ->with('success', __('messages.changements_validate_step_success'));
            }

            // If it's the last step, redirect back to current step
            return redirect()->route('changements.step', ['changement' => $changement->id, 'stepNumber' => $stepNumber])
                ->with('success', __('messages.changements_validate_step_success'));
        } catch (Throwable $e) {
            Log::error('Failed to validate step', [
                'changement_id' => $changement->id,
                'step_number' => $stepNumber,
                'error' => $e->getMessage(),
            ]);
            return back()->with('error', __('messages.changements_validate_step_error'));
        }
    }

    /**
     * Reject a step.
     */
    public function rejectStep(Request $request, Changement $changement, int $stepNumber): RedirectResponse
    {
        try {
            $step = $changement->getStep($stepNumber);
            if (!$step) {
                return redirect()->route('changements.show', $changement->id)
                    ->with('error', __('messages.changements_step_not_found'));
            }

            $request->validate([
                'rejection_reason' => 'required|string',
            ]);

            $step->rejectStep(
                $request->input('rejection_reason'),
                Auth::id() ? (string) Auth::id() : null,
                $request->input('notes')
            );

            // Reject changement if critical step is rejected
            if (in_array($stepNumber, [2, 3, 4, 5])) {
                $changement->markAsRejected($request->input('rejection_reason'), Auth::id() ? (string) Auth::id() : null);
            }

            return redirect()->route('changements.show', $changement->id)
                ->with('success', __('messages.changements_reject_step_success'));
        } catch (Throwable $e) {
            Log::error('Failed to reject step', [
                'changement_id' => $changement->id,
                'step_number' => $stepNumber,
                'error' => $e->getMessage(),
            ]);
            return back()->with('error', __('messages.changements_reject_step_error'));
        }
    }

    /**
     * Display the checklist page (Step 6).
     */
    public function checklist(Changement $changement): View|RedirectResponse
    {
        try {
            // Ensure step 5 is validated
            $step5 = $changement->getStep(5);
            if (!$step5 || !$step5->isValidated()) {
                return redirect()->route('changements.show', $changement->id)
                    ->with('error', __('messages.changements_checklist_must_validate_step5'));
            }

            $changement->load(['changementType.principaleCretaires.sousCretaires', 'checklistResults', 'subject']);

            // Get all principale cretaire for this changement type
            $principaleCretaires = $changement->changementType->principaleCretaires()
                ->where('is_active', true)
                ->with('sousCretaires', function ($query) {
                    $query->where('is_active', true);
                })
                ->get();

            // Get existing checklist results
            $checklistResults = $changement->checklistResults()
                ->with('sousCretaire')
                ->get()
                ->keyBy('sous_cretaire_id');

            return view('changements.checklist', compact('changement', 'principaleCretaires', 'checklistResults'));
        } catch (Throwable $e) {
            Log::error('Failed to display checklist', [
                'changement_id' => $changement->id,
                'error' => $e->getMessage(),
            ]);
            return redirect()->route('changements.show', $changement->id)
                ->with('error', __('messages.changements_checklist_error'));
        }
    }

    /**
     * Save checklist results and generate PDF.
     */
    public function saveChecklist(Request $request, Changement $changement): RedirectResponse
    {
        try {
            // Ensure step 5 is validated
            $step5 = $changement->getStep(5);
            if (!$step5 || !$step5->isValidated()) {
                return redirect()->route('changements.show', $changement->id)
                    ->with('error', __('messages.changements_checklist_must_validate_step5'));
            }

            $request->validate([
                'checklist' => 'required|array',
                'checklist.*.status' => 'required|in:OK,KO,N/A',
                'checklist.*.observation' => 'nullable|string',
            ]);

            // Load changement type to get all sous cretaire
            $changement->load(['changementType.principaleCretaires.sousCretaires']);

            // Delete old checklist if it exists
            if ($changement->check_list_path) {
                // Delete old PDF file from uploads folder
                if (Storage::disk('uploads')->exists($changement->check_list_path)) {
                    Storage::disk('uploads')->delete($changement->check_list_path);
                }
            }

            // Delete old checklist results from database
            $changement->checklistResults()->delete();

            // Get all sous cretaire for this changement type
            $allSousCretaires = SousCretaire::whereHas('principaleCretaire', function ($query) use ($changement) {
                $query->where('changement_type_id', $changement->changement_type_id)
                    ->where('is_active', true);
            })
            ->where('is_active', true)
            ->pluck('id')
            ->toArray();

            // Save new checklist results
            foreach ($request->input('checklist', []) as $sousCretaireId => $data) {
                if (!in_array($sousCretaireId, $allSousCretaires)) {
                    continue; // Skip invalid sous cretaire IDs
                }

                ChangementChecklistResult::create([
                    'changement_id' => $changement->id,
                    'sous_cretaire_id' => $sousCretaireId,
                    'status' => $data['status'] ?? 'N/A',
                    'observation' => $data['observation'] ?? null,
                ]);
            }

            // Generate new PDF report
            $pdfService = new \App\Services\ChangementPdfService();
            $pdfPath = $pdfService->generateChecklistPdf($changement);

            // Update changement with PDF path
            $changement->update([
                'check_list_path' => $pdfPath,
                'status' => 'completed',
            ]);

            // Create step 6 record
            $step6 = $changement->getStep(6);
            if (!$step6) {
                ChangementStep::create([
                    'changement_id' => $changement->id,
                    'step_number' => 6,
                    'step_data' => ['checklist_completed' => true, 'pdf_path' => $pdfPath],
                    'status' => 'validated',
                    'validated_by' => Auth::id() ? (string) Auth::id() : null,
                    'validated_at' => now(),
                ]);
            } else {
                $step6->update([
                    'step_data' => array_merge($step6->step_data ?? [], ['checklist_completed' => true, 'pdf_path' => $pdfPath]),
                    'status' => 'validated',
                    'validated_by' => Auth::id() ? (string) Auth::id() : null,
                    'validated_at' => now(),
                ]);
            }

            return redirect()->route('changements.index')
                ->with('success', __('messages.changements_save_checklist_success'));
        } catch (Throwable $e) {
            Log::error('Failed to save checklist', [
                'changement_id' => $changement->id,
                'error' => $e->getMessage(),
            ]);
            return back()
                ->withInput()
                ->with('error', __('messages.changements_save_checklist_error'));
        }
    }

    /**
     * Download the checklist PDF.
     */
    public function downloadChecklist(Changement $changement)
    {
        try {
            if (!$changement->check_list_path || !Storage::disk('uploads')->exists($changement->check_list_path)) {
                return redirect()
                    ->route('changements.show', $changement->id)
                    ->with('error', __('messages.changements_checklist_not_found'));
            }

            $fileName = sprintf('checklist-changement-%d-%s.pdf', $changement->id, now()->format('YmdHis'));
            $filePath = Storage::disk('uploads')->path($changement->check_list_path);

            return response()->download($filePath, $fileName);
        } catch (Throwable $e) {
            Log::error('Failed to download changement checklist', [
                'changement_id' => $changement->id,
                'error' => $e->getMessage(),
            ]);

            return redirect()
                ->route('changements.show', $changement->id)
                ->with('error', __('messages.changements_checklist_download_error'));
        }
    }

    /**
     * Finalize changement process.
     */
    public function finalize(Changement $changement): RedirectResponse
    {
        try {
            // Check if Step 6 (checklist) is validated
            $step6 = $changement->getStep(6);
            if (!$step6 || !$step6->isValidated()) {
                return redirect()->route('changements.show', $changement->id)
                    ->with('error', __('messages.changements_finalize_complete_checklist'));
            }

            // Check if all steps are validated
            for ($i = 1; $i <= 6; $i++) {
                $step = $changement->getStep($i);
                if (!$step || !$step->isValidated()) {
                    return redirect()->route('changements.show', $changement->id)
                        ->with('error', __('messages.changements_finalize_all_steps_validated'));
                }
            }

            // Mark changement as approved
            $changement->markAsValidated(Auth::id() ? (string) Auth::id() : null);
            $changement->update(['status' => 'approved']);
            
            // Refresh to get latest data
            $changement->refresh();

            Log::info('Changement finalized', [
                'changement_id' => $changement->id,
                'validated_by' => $changement->validated_by,
                'validated_at' => $changement->validated_at,
            ]);

            return redirect()->route('changements.index')
                ->with('success', __('messages.changements_finalize_success'));
        } catch (Throwable $e) {
            Log::error('Failed to finalize changement', [
                'changement_id' => $changement->id,
                'error' => $e->getMessage(),
            ]);

            return redirect()->route('changements.show', $changement->id)
                ->with('error', __('messages.changements_finalize_error'));
        }
    }
}
