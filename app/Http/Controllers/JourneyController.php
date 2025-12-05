<?php

namespace App\Http\Controllers;

use App\Models\Journey;
use App\Models\JourneyBlackPoint;
use App\Models\JourneyChecklistInstance;
use App\Models\JourneyChecklistItemAnswer;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;
use Illuminate\Http\Response;
use App\Models\JourneyChecklist;
use Carbon\Carbon;
use Barryvdh\DomPDF\Facade\Pdf;
use App\Exports\JourneysExport;
use Maatwebsite\Excel\Facades\Excel;
use Throwable;

class JourneyController extends Controller
{
    /**
     * Display a listing of journeys.
     */
    public function index(Request $request): View|RedirectResponse
    {
        try {
            // Check if table exists, if not return empty results
            if (!Schema::hasTable('journeys')) {
            return view('journeys.index', [
                'journeys' => new \Illuminate\Pagination\LengthAwarePaginator([], 0, 15),
                'allJourneys' => collect([]),
                'allJourneysForFilter' => collect([]),
                'filters' => $request->only(['journey_id']),
                'isFiltered' => false,
            ]);
            }

            $journeysQuery = Journey::query()
                ->with('latestChecklist')
                ->orderByDesc('created_at');

            // Journey filter (by ID)
            $isFiltered = $request->filled('journey_id');
            if ($isFiltered) {
                $journeysQuery->where('id', $request->input('journey_id'));
                // Load black points when filtering by specific journey
                $journeysQuery->with('blackPoints');
            }

            $journeys = $journeysQuery->paginate(15)->withQueryString();

            // Get filtered journeys for map (without pagination, but with same filters)
            $allJourneys = (clone $journeysQuery)->get();

            // Get all journeys for the dropdown filter
            $allJourneysForFilter = Journey::query()
                ->orderBy('name')
                ->get(['id', 'name']);

            return view('journeys.index', [
                'journeys' => $journeys,
                'allJourneys' => $allJourneys,
                'allJourneysForFilter' => $allJourneysForFilter,
                'filters' => $request->only(['journey_id']),
                'isFiltered' => $isFiltered,
            ]);
        } catch (Throwable $exception) {
            Log::error('Failed to load journeys index', [
                'error' => $exception->getMessage(),
                'trace' => $exception->getTraceAsString()
            ]);
            
            return view('journeys.index', [
                'journeys' => new \Illuminate\Pagination\LengthAwarePaginator([], 0, 15),
                'allJourneys' => collect([]),
                'allJourneysForFilter' => collect([]),
                'filters' => $request->only(['journey_id']),
                'isFiltered' => false,
            ])->with('error', __('messages.error_loading_journeys') ?? 'Error loading journeys.');
        }
    }

    /**
     * Show the form for creating a new journey.
     */
    public function create(): View|RedirectResponse
    {
        try {
            // Check if table exists
            if (!Schema::hasTable('journeys')) {
                return redirect()
                    ->route('journeys.index')
                    ->with('error', __('messages.error_creating_journey') ?? 'Error creating journey. Please run the migration: php artisan migrate');
            }

            // Load active checklist items
            $checklistItems = JourneyChecklist::where('is_active', true)
                ->orderBy('donnees')
                ->get();

            return view('journeys.create', compact('checklistItems'));
        } catch (Throwable $exception) {
            Log::error('Failed to load journey create form', [
                'error' => $exception->getMessage(),
                'trace' => $exception->getTraceAsString()
            ]);

            return redirect()
                ->route('journeys.index')
                ->with('error', __('messages.error_loading_journey_form') ?? 'Error loading journey form.');
        }
    }

    /**
     * Store a newly created journey.
     */
    public function store(Request $request): RedirectResponse
    {
        try {
            $validated = $request->validate([
                'name' => ['required', 'string', 'max:255'],
                'from_latitude' => ['required', 'numeric', 'between:-90,90'],
                'from_longitude' => ['required', 'numeric', 'between:-180,180'],
                'from_location_name' => ['nullable', 'string', 'max:255'],
                'to_latitude' => ['required', 'numeric', 'between:-90,90'],
                'to_longitude' => ['required', 'numeric', 'between:-180,180'],
                'to_location_name' => ['nullable', 'string', 'max:255'],
                'details' => ['nullable', 'string'],
            ]);

            // Validate coordinates are set
            if (empty($validated['from_latitude']) || empty($validated['from_longitude']) ||
                empty($validated['to_latitude']) || empty($validated['to_longitude'])) {
                return back()
                    ->withInput()
                    ->with('error', __('messages.location_required') ?? 'Please select both from and to locations on the map before submitting.');
            }

            DB::beginTransaction();

            try {
                $journey = Journey::create($validated);

                // Handle black points if provided
                if ($request->has('black_points') && is_array($request->input('black_points'))) {
                    foreach ($request->input('black_points') as $blackPointData) {
                        if (!empty($blackPointData['name']) && 
                            !empty($blackPointData['latitude']) && 
                            !empty($blackPointData['longitude'])) {
                            JourneyBlackPoint::create([
                                'journey_id' => $journey->id,
                                'name' => $blackPointData['name'],
                                'latitude' => $blackPointData['latitude'],
                                'longitude' => $blackPointData['longitude'],
                                'description' => $blackPointData['description'] ?? null,
                            ]);
                        }
                    }
                }

                // Handle checklist if provided (optional)
                $checklistData = $request->input('checklist', []);
                
                if (!empty($checklistData)) {
                    // Get all active items to validate against
                    $activeItems = JourneyChecklist::where('is_active', true)
                        ->pluck('id')
                        ->toArray();

                    // Build a list of valid answers (only for submitted items with weight and score)
                    $validAnswers = [];

                    foreach ($checklistData as $itemId => $answerData) {
                        // Ensure item is a valid active checklist item
                        if (!in_array((int) $itemId, $activeItems, true)) {
                            DB::rollBack();
                            return back()
                                ->withInput()
                                ->with('error', __('messages.invalid_checklist_item') ?? 'Invalid checklist item detected.');
                        }

                        // Only keep answers where weight and score are provided
                        if (isset($answerData['weight']) && isset($answerData['score']) && 
                            !empty($answerData['weight']) && !empty($answerData['score'])) {
                            $validAnswers[(int) $itemId] = $answerData;
                        }
                    }

                    // If user provided any valid answers, create the checklist
                    if (!empty($validAnswers)) {
                        // Get status from request (default to 'accepted')
                        $checklistStatus = $request->input('checklist_status', 'accepted');
                        if (!in_array($checklistStatus, ['pending', 'accepted', 'rejected'], true)) {
                            $checklistStatus = 'accepted';
                        }

                        // Handle file uploads
                        $documents = [];
                        if ($request->hasFile('checklist_documents')) {
                            foreach ($request->file('checklist_documents') as $file) {
                                if ($file->isValid()) {
                                    $path = $file->store('journeys/checklists', 'public');
                                    $documents[] = $path;
                                }
                            }
                        }

                        // Create checklist record
                        $checklist = JourneyChecklistInstance::create([
                            'journey_id' => $journey->id,
                            'completed_by' => Auth::id(),
                            'completed_at' => now(),
                            'status' => $checklistStatus,
                            'notes' => $request->input('checklist_notes'),
                            'documents' => !empty($documents) ? $documents : null,
                        ]);

                        // Create answers for each valid item
                        foreach ($validAnswers as $itemId => $answerData) {
                            JourneyChecklistItemAnswer::create([
                                'journey_checklist_id' => $checklist->id,
                                'journeys_checklist_id' => $itemId,
                                'weight' => (int) $answerData['weight'],
                                'score' => (int) $answerData['score'],
                                'note' => isset($answerData['note']) ? (float) $answerData['note'] : null, // Will be auto-calculated if null
                                'comment' => $answerData['comment'] ?? null,
                            ]);
                        }
                    }
                }

                DB::commit();

                return redirect()
                    ->route('journeys.show', $journey)
                    ->with('success', __('messages.journey_created') ?? 'Journey created successfully.');
            } catch (Throwable $transactionException) {
                DB::rollBack();
                throw $transactionException;
            }
        } catch (\Illuminate\Validation\ValidationException $e) {
            throw $e;
        } catch (Throwable $exception) {
            Log::error('Failed to create journey', [
                'error' => $exception->getMessage(),
                'trace' => $exception->getTraceAsString(),
                'data' => $request->except(['_token']),
            ]);
            
            $errorMessage = __('messages.error_creating_journey') ?? 'Error creating journey.';
            
            if (config('app.debug')) {
                $errorMessage .= ' ' . $exception->getMessage();
            }
            
            return back()
                ->withInput()
                ->with('error', $errorMessage);
        }
    }

    /**
     * Display the specified journey.
     */
    public function show(Journey $journey): View|RedirectResponse
    {
        try {
            // Load journey with relationships
            $journey->load(['blackPoints', 'checklists.completedByUser', 'checklists.answers.templateItem']);

            // Get the latest checklist
            $checklist = JourneyChecklistInstance::where('journey_id', $journey->id)
                ->with([
                    'completedByUser',
                    'answers.templateItem',
                ])
                ->latest('completed_at')
                ->first();

            // Load full checklist history for this journey
            $checklistsHistory = JourneyChecklistInstance::where('journey_id', $journey->id)
                ->with('completedByUser')
                ->orderByDesc('completed_at')
                ->get();

            // Calculate next inspection due date (6 months after last checklist completion)
            $nextInspectionDue = null;
            if ($checklist && $checklist->completed_at) {
                $nextInspectionDue = $checklist->completed_at->copy()->addMonthsNoOverflow(6);
            }

            // Load active checklist items for form
            $checklistItems = \App\Models\JourneyChecklist::where('is_active', true)
                ->orderBy('donnees')
                ->get();

            return view('journeys.show', [
                'journey' => $journey,
                'checklist' => $checklist,
                'checklistsHistory' => $checklistsHistory,
                'checklistItems' => $checklistItems,
                'nextInspectionDue' => $nextInspectionDue,
            ]);
        } catch (Throwable $exception) {
            Log::error('Failed to load journey', [
                'error' => $exception->getMessage(),
                'journey_id' => $journey->id ?? null,
                'trace' => $exception->getTraceAsString()
            ]);

            return redirect()
                ->route('journeys.index')
                ->with('error', __('messages.error_loading_journey') ?? 'Error loading journey.');
        }
    }

    /**
     * Show the form for editing the specified journey.
     */
    public function edit(Journey $journey): View|RedirectResponse
    {
        try {
            // Load journey with relationships
            $journey->load(['blackPoints']);
            
            // Load active checklist items
            $checklistItems = JourneyChecklist::where('is_active', true)
                ->orderBy('donnees')
                ->get();
            
            // Load existing checklist if any
            $existingChecklist = JourneyChecklistInstance::where('journey_id', $journey->id)
                ->with(['answers.templateItem'])
                ->latest('completed_at')
                ->first();

            return view('journeys.edit', [
                'journey' => $journey,
                'checklistItems' => $checklistItems,
                'existingChecklist' => $existingChecklist,
            ]);
        } catch (Throwable $exception) {
            Log::error('Failed to load journey edit form', [
                'error' => $exception->getMessage(),
                'journey_id' => $journey->id ?? null,
                'trace' => $exception->getTraceAsString()
            ]);

            return redirect()
                ->route('journeys.index')
                ->with('error', __('messages.error_loading_journey_form') ?? 'Error loading journey form.');
        }
    }

    /**
     * Update the specified journey.
     */
    public function update(Request $request, Journey $journey): RedirectResponse
    {
        try {
            $validated = $request->validate([
                'name' => ['required', 'string', 'max:255'],
                'from_latitude' => ['required', 'numeric', 'between:-90,90'],
                'from_longitude' => ['required', 'numeric', 'between:-180,180'],
                'from_location_name' => ['nullable', 'string', 'max:255'],
                'to_latitude' => ['required', 'numeric', 'between:-90,90'],
                'to_longitude' => ['required', 'numeric', 'between:-180,180'],
                'to_location_name' => ['nullable', 'string', 'max:255'],
                'details' => ['nullable', 'string'],
            ]);

            // Validate coordinates are set
            if (empty($validated['from_latitude']) || empty($validated['from_longitude']) ||
                empty($validated['to_latitude']) || empty($validated['to_longitude'])) {
                return back()
                    ->withInput()
                    ->with('error', __('messages.location_required') ?? 'Please select both from and to locations on the map before submitting.');
            }

            DB::beginTransaction();

            try {
                $journey->update($validated);

                // Handle black points - delete existing and recreate
                $journey->blackPoints()->delete();
                
                if ($request->has('black_points') && is_array($request->input('black_points'))) {
                    foreach ($request->input('black_points') as $blackPointData) {
                        if (!empty($blackPointData['name']) && 
                            !empty($blackPointData['latitude']) && 
                            !empty($blackPointData['longitude'])) {
                            JourneyBlackPoint::create([
                                'journey_id' => $journey->id,
                                'name' => $blackPointData['name'],
                                'latitude' => $blackPointData['latitude'],
                                'longitude' => $blackPointData['longitude'],
                                'description' => $blackPointData['description'] ?? null,
                            ]);
                        }
                    }
                }

                // Handle checklist if provided (optional)
                $checklistData = $request->input('checklist', []);
                
                if (!empty($checklistData)) {
                    // Get all active items to validate against
                    $activeItems = JourneyChecklist::where('is_active', true)
                        ->pluck('id')
                        ->toArray();

                    // Build a list of valid answers (only for submitted items with weight and score)
                    $validAnswers = [];

                    foreach ($checklistData as $itemId => $answerData) {
                        // Ensure item is a valid active checklist item
                        if (!in_array((int) $itemId, $activeItems, true)) {
                            DB::rollBack();
                            return back()
                                ->withInput()
                                ->with('error', __('messages.invalid_checklist_item') ?? 'Invalid checklist item detected.');
                        }

                        // Only keep answers where weight and score are provided
                        if (isset($answerData['weight']) && isset($answerData['score']) && 
                            !empty($answerData['weight']) && !empty($answerData['score'])) {
                            $validAnswers[(int) $itemId] = $answerData;
                        }
                    }

                    // If user provided any valid answers, create/update the checklist
                    if (!empty($validAnswers)) {
                        // Get status from request (default to 'accepted')
                        $checklistStatus = $request->input('checklist_status', 'accepted');
                        if (!in_array($checklistStatus, ['pending', 'accepted', 'rejected'], true)) {
                            $checklistStatus = 'accepted';
                        }

                        // Handle file uploads
                        $newDocuments = [];
                        if ($request->hasFile('checklist_documents')) {
                            foreach ($request->file('checklist_documents') as $file) {
                                if ($file->isValid()) {
                                    $path = $file->store('journeys/checklists', 'public');
                                    $newDocuments[] = $path;
                                }
                            }
                        }

                        // Get existing checklist if any
                        $existingChecklist = JourneyChecklistInstance::where('journey_id', $journey->id)
                            ->latest('completed_at')
                            ->first();
                        
                        // If there's an existing checklist, update it instead of creating a new one
                        if ($existingChecklist) {
                            // Get existing documents
                            $existingDocuments = $existingChecklist->documents ?? [];
                            if (!is_array($existingDocuments)) {
                                $existingDocuments = [];
                            }

                            // Merge existing documents with new ones
                            $allDocuments = array_merge($existingDocuments, $newDocuments);

                            // Update existing checklist record
                            $existingChecklist->update([
                                'status' => $checklistStatus,
                                'notes' => $request->input('checklist_notes', $existingChecklist->notes),
                                'documents' => !empty($allDocuments) ? $allDocuments : null,
                            ]);

                            // Delete existing answers and create new ones
                            $existingChecklist->answers()->delete();

                            // Create answers for each valid item
                            foreach ($validAnswers as $itemId => $answerData) {
                                JourneyChecklistItemAnswer::create([
                                    'journey_checklist_id' => $existingChecklist->id,
                                    'journeys_checklist_id' => $itemId,
                                    'weight' => (int) $answerData['weight'],
                                    'score' => (int) $answerData['score'],
                                    'note' => isset($answerData['note']) ? (float) $answerData['note'] : null,
                                    'comment' => $answerData['comment'] ?? null,
                                ]);
                            }
                        } else {
                            // No existing checklist, create a new one
                            // Create new checklist record
                            $checklist = JourneyChecklistInstance::create([
                                'journey_id' => $journey->id,
                                'completed_by' => Auth::id(),
                                'completed_at' => now(),
                                'status' => $checklistStatus,
                                'notes' => $request->input('checklist_notes'),
                                'documents' => !empty($newDocuments) ? $newDocuments : null,
                            ]);

                            // Create answers for each valid item
                            foreach ($validAnswers as $itemId => $answerData) {
                                JourneyChecklistItemAnswer::create([
                                    'journey_checklist_id' => $checklist->id,
                                    'journeys_checklist_id' => $itemId,
                                    'weight' => (int) $answerData['weight'],
                                    'score' => (int) $answerData['score'],
                                    'note' => isset($answerData['note']) ? (float) $answerData['note'] : null,
                                    'comment' => $answerData['comment'] ?? null,
                                ]);
                            }
                        }
                    }
                }

                DB::commit();

                return redirect()
                    ->route('journeys.show', $journey)
                    ->with('success', __('messages.journey_updated') ?? 'Journey updated successfully.');
            } catch (Throwable $transactionException) {
                DB::rollBack();
                throw $transactionException;
            }
        } catch (\Illuminate\Validation\ValidationException $e) {
            throw $e;
        } catch (Throwable $exception) {
            Log::error('Failed to update journey', [
                'error' => $exception->getMessage(),
                'journey_id' => $journey->id,
                'trace' => $exception->getTraceAsString(),
            ]);
            
            $errorMessage = __('messages.error_updating_journey') ?? 'Error updating journey.';
            
            if (config('app.debug')) {
                $errorMessage .= ' ' . $exception->getMessage();
            }
            
            return back()
                ->withInput()
                ->with('error', $errorMessage);
        }
    }

    /**
     * Remove the specified journey.
     */
    public function destroy(Journey $journey): RedirectResponse
    {
        try {
            DB::beginTransaction();

            try {
                // Delete related data (cascade will handle most, but we'll be explicit)
                $journey->blackPoints()->delete();
                $journey->checklists()->delete();
                
                // Delete the journey
                $journey->delete();

                DB::commit();

                return redirect()
                    ->route('journeys.index')
                    ->with('success', __('messages.journey_deleted') ?? 'Journey deleted successfully.');
            } catch (Throwable $transactionException) {
                DB::rollBack();
                throw $transactionException;
            }
        } catch (Throwable $exception) {
            Log::error('Failed to delete journey', [
                'error' => $exception->getMessage(),
                'journey_id' => $journey->id ?? null,
                'trace' => $exception->getTraceAsString()
            ]);

            return back()
                ->with('error', __('messages.error_deleting_journey') ?? 'Error deleting journey.');
        }
    }

    /**
     * Store a newly created black point for a journey.
     */
    public function storeBlackPoint(Request $request, Journey $journey): RedirectResponse
    {
        try {
            $validated = $request->validate([
                'name' => ['required', 'string', 'max:255'],
                'latitude' => ['required', 'numeric', 'between:-90,90'],
                'longitude' => ['required', 'numeric', 'between:-180,180'],
                'description' => ['nullable', 'string'],
            ]);

            $validated['journey_id'] = $journey->id;

            JourneyBlackPoint::create($validated);

            return redirect()
                ->route('journeys.show', $journey)
                ->with('success', __('messages.black_point_created') ?? 'Black point created successfully.');
        } catch (\Illuminate\Validation\ValidationException $e) {
            throw $e;
        } catch (Throwable $exception) {
            Log::error('Failed to create black point', [
                'error' => $exception->getMessage(),
                'journey_id' => $journey->id,
                'trace' => $exception->getTraceAsString(),
            ]);

            return back()
                ->withInput()
                ->with('error', __('messages.error_creating_black_point') ?? 'Error creating black point.');
        }
    }

    /**
     * Update the specified black point.
     */
    public function updateBlackPoint(Request $request, JourneyBlackPoint $blackPoint): RedirectResponse
    {
        try {
            $validated = $request->validate([
                'name' => ['required', 'string', 'max:255'],
                'latitude' => ['required', 'numeric', 'between:-90,90'],
                'longitude' => ['required', 'numeric', 'between:-180,180'],
                'description' => ['nullable', 'string'],
            ]);

            $blackPoint->update($validated);

            return redirect()
                ->route('journeys.show', $blackPoint->journey)
                ->with('success', __('messages.black_point_updated') ?? 'Black point updated successfully.');
        } catch (\Illuminate\Validation\ValidationException $e) {
            throw $e;
        } catch (Throwable $exception) {
            Log::error('Failed to update black point', [
                'error' => $exception->getMessage(),
                'black_point_id' => $blackPoint->id,
                'trace' => $exception->getTraceAsString(),
            ]);

            return back()
                ->withInput()
                ->with('error', __('messages.error_updating_black_point') ?? 'Error updating black point.');
        }
    }

    /**
     * Remove the specified black point.
     */
    public function destroyBlackPoint(JourneyBlackPoint $blackPoint): RedirectResponse
    {
        try {
            $journey = $blackPoint->journey;
            $blackPoint->delete();

            return redirect()
                ->route('journeys.show', $journey)
                ->with('success', __('messages.black_point_deleted') ?? 'Black point deleted successfully.');
        } catch (Throwable $exception) {
            Log::error('Failed to delete black point', [
                'error' => $exception->getMessage(),
                'black_point_id' => $blackPoint->id ?? null,
                'trace' => $exception->getTraceAsString()
            ]);

            return back()
                ->with('error', __('messages.error_deleting_black_point') ?? 'Error deleting black point.');
        }
    }

    /**
     * Serve a journey checklist document.
     */
    public function checklistDocument(string $encoded)
    {
        try {
            $path = base64_decode($encoded, true);

            if ($path === false) {
                abort(404);
            }

            // Basic security check – prevent directory traversal
            if (str_contains($path, '..')) {
                abort(403);
            }

            if (! Storage::disk('public')->exists($path)) {
                abort(404);
            }

            return response()->file(Storage::disk('public')->path($path));
        } catch (Throwable $exception) {
            Log::error('Failed to serve journey checklist document', [
                'error' => $exception->getMessage(),
                'trace' => $exception->getTraceAsString(),
                'encoded' => $encoded,
            ]);

            abort(500);
        }
    }

    /**
     * Download a completed checklist as PDF.
     */
    public function checklistPdf(Request $request, JourneyChecklistInstance $checklist)
    {
        try {
            $journey = $checklist->journey;

            if ($checklist->journey_id !== $journey->id) {
                return redirect()
                    ->route('journeys.index')
                    ->with('error', __('messages.error_loading_journeys') ?? 'Checklist does not belong to this journey.');
            }

            // Load relationships for rendering
            $checklist->load([
                'answers.templateItem',
                'completedByUser',
                'journey',
            ]);

            // Load active checklist items (structure of the checklist)
            $checklistItems = JourneyChecklist::where('is_active', true)
                ->orderBy('donnees')
                ->get();

            // Map answers by item ID for quick lookup
            $answersByItemId = [];
            foreach ($checklist->answers as $answer) {
                $answersByItemId[$answer->journeys_checklist_id] = $answer;
            }

            $completedAt = $checklist->completed_at;
            $nextInspectionDue = $completedAt
                ? $completedAt->copy()->addMonthsNoOverflow(6)
                : null;

            $mapImageBase64 = $request->input('map_image');

            $pdf = PDF::loadView('journeys.checklists.pdf', [
                'journey' => $journey,
                'checklist' => $checklist,
                'checklistItems' => $checklistItems,
                'answersByItemId' => $answersByItemId,
                'completedAt' => $completedAt,
                'nextInspectionDue' => $nextInspectionDue,
                'mapImageBase64' => $mapImageBase64,
            ])->setPaper('a4', 'portrait');

            $fileName = sprintf(
                'journey-%d-checklist-%d-%s.pdf',
                $journey->id,
                $checklist->id,
                now()->format('Ymd_His')
            );

            return $pdf->download($fileName);
        } catch (Throwable $exception) {
            Log::error('Failed to generate journey checklist PDF', [
                'error' => $exception->getMessage(),
                'trace' => $exception->getTraceAsString(),
                'journey_id' => $checklist->journey_id ?? null,
                'checklist_id' => $checklist->id ?? null,
            ]);

            return redirect()
                ->route('journeys.show', $checklist->journey)
                ->with('error', __('messages.error_exporting_pdf') ?? 'Error generating checklist PDF.');
        }
    }

    /**
     * Store a newly created checklist for a journey.
     */
    public function storeChecklist(Request $request, Journey $journey): RedirectResponse
    {
        try {
            // Enforce 6-month rule: allow new checklist only if none exists
            // or if we are within the allowed window before the 6-month due date.
            $lastChecklist = $journey->checklists()
                ->orderByDesc('completed_at')
                ->first();

            if ($lastChecklist && $lastChecklist->completed_at) {
                $dueDate = $lastChecklist->completed_at->copy()->addMonthsNoOverflow(6);
                // Allow creating a new checklist from 2 weeks before due date
                $openFrom = $dueDate->copy()->subWeeks(2);

                if (now()->lt($openFrom)) {
                    return redirect()
                        ->route('journeys.show', $journey)
                        ->with(
                            'error',
                            __('messages.checklist_not_due_yet')
                                ?? 'A new checklist can only be created 6 months after the last one.'
                        );
                }
            }

            // Validate checklist data
            $checklistData = $request->input('checklist', []);
            if (empty($checklistData)) {
                return back()
                    ->withInput()
                    ->with('error', __('messages.checklist_required') ?? 'Please provide checklist answers.');
            }

            // Get all active items to validate against
            $activeItems = JourneyChecklist::where('is_active', true)
                ->pluck('id')
                ->toArray();

            if (empty($activeItems)) {
                return back()
                    ->withInput()
                    ->with('error', __('messages.no_checklist_items') ?? 'No active checklist items found.');
            }

            // Build a list of valid answers (only for submitted items with weight and score)
            $validAnswers = [];

            foreach ($checklistData as $itemId => $answerData) {
                // Ensure item is a valid active checklist item
                if (!in_array((int) $itemId, $activeItems, true)) {
                    return back()
                        ->withInput()
                        ->with('error', __('messages.invalid_checklist_item') ?? 'Invalid checklist item detected.');
                }

                // Only keep answers where weight and score are provided
                if (isset($answerData['weight']) && isset($answerData['score']) && 
                    !empty($answerData['weight']) && !empty($answerData['score'])) {
                    $validAnswers[(int) $itemId] = $answerData;
                }
            }

            if (empty($validAnswers)) {
                return back()
                    ->withInput()
                    ->with('error', __('messages.checklist_answers_required') ?? 'Please provide at least one checklist answer.');
            }

            // Double-check 6-month rule on store as well
            $lastChecklist = $journey->checklists()
                ->orderByDesc('completed_at')
                ->first();

            if ($lastChecklist && $lastChecklist->completed_at) {
                $dueDate = $lastChecklist->completed_at->copy()->addMonthsNoOverflow(6);
                $openFrom = $dueDate->copy()->subWeeks(2);

                if (now()->lt($openFrom)) {
                    return redirect()
                        ->route('journeys.show', $journey)
                        ->with(
                            'error',
                            __('messages.checklist_not_due_yet')
                                ?? 'A new checklist can only be created 6 months after the last one.'
                        );
                }
            }

            DB::beginTransaction();

            try {
                // Get status from request (default to 'accepted')
                $checklistStatus = $request->input('checklist_status', 'accepted');
                if (!in_array($checklistStatus, ['pending', 'accepted', 'rejected'], true)) {
                    $checklistStatus = 'accepted';
                }

                // Handle file uploads
                $documents = [];
                if ($request->hasFile('checklist_documents')) {
                    foreach ($request->file('checklist_documents') as $file) {
                        if ($file->isValid()) {
                            $path = $file->store('journeys/checklists', 'public');
                            $documents[] = $path;
                        }
                    }
                }

                // Create checklist record
                $checklist = JourneyChecklistInstance::create([
                    'journey_id' => $journey->id,
                    'completed_by' => Auth::id(),
                    'completed_at' => now(),
                    'status' => $checklistStatus,
                    'notes' => $request->input('checklist_notes'),
                    'documents' => !empty($documents) ? $documents : null,
                ]);

                // Create answers for each valid item
                foreach ($validAnswers as $itemId => $answerData) {
                    JourneyChecklistItemAnswer::create([
                        'journey_checklist_id' => $checklist->id,
                        'journeys_checklist_id' => $itemId,
                        'weight' => (int) $answerData['weight'],
                        'score' => (int) $answerData['score'],
                        'note' => isset($answerData['note']) ? (float) $answerData['note'] : null,
                        'comment' => $answerData['comment'] ?? null,
                    ]);
                }

                DB::commit();

                return redirect()
                    ->route('journeys.show', $journey)
                    ->with('success', __('messages.journey_checklist_created') ?? 'Checklist created successfully.');
            } catch (Throwable $transactionException) {
                DB::rollBack();
                throw $transactionException;
            }
        } catch (\Illuminate\Validation\ValidationException $e) {
            throw $e;
        } catch (Throwable $exception) {
            Log::error('Failed to store journey checklist', [
                'error' => $exception->getMessage(),
                'trace' => $exception->getTraceAsString(),
                'journey_id' => $journey->id ?? null,
            ]);

            return back()
                ->withInput()
                ->with('error', __('messages.error_creating_journey_checklist') ?? 'Error creating journey checklist.');
        }
    }

    /**
     * Update an existing checklist for a journey.
     */
    public function updateChecklist(Request $request, JourneyChecklistInstance $checklist): RedirectResponse
    {
        try {
            $journey = $checklist->journey;

            // Validate checklist data
            $checklistData = $request->input('checklist', []);
            if (empty($checklistData)) {
                return back()
                    ->withInput()
                    ->with('error', __('messages.checklist_required') ?? 'Please provide checklist answers.');
            }

            // Get all active items to validate against
            $activeItems = JourneyChecklist::where('is_active', true)
                ->pluck('id')
                ->toArray();

            if (empty($activeItems)) {
                return back()
                    ->withInput()
                    ->with('error', __('messages.no_checklist_items') ?? 'No active checklist items found.');
            }

            // Build a list of valid answers
            $validAnswers = [];

            foreach ($checklistData as $itemId => $answerData) {
                // Ensure item is a valid active checklist item
                if (!in_array((int) $itemId, $activeItems, true)) {
                    return back()
                        ->withInput()
                        ->with('error', __('messages.invalid_checklist_item') ?? 'Invalid checklist item detected.');
                }

                // Only keep answers where weight and score are provided
                if (isset($answerData['weight']) && isset($answerData['score']) && 
                    !empty($answerData['weight']) && !empty($answerData['score'])) {
                    $validAnswers[(int) $itemId] = $answerData;
                }
            }

            if (empty($validAnswers)) {
                return back()
                    ->withInput()
                    ->with('error', __('messages.checklist_answers_required') ?? 'Please provide at least one checklist answer.');
            }

            DB::beginTransaction();

            try {
                // Get status from request (default to existing status or 'accepted')
                $checklistStatus = $request->input('checklist_status', $checklist->status ?? 'accepted');
                if (!in_array($checklistStatus, ['pending', 'accepted', 'rejected'], true)) {
                    $checklistStatus = $checklist->status ?? 'accepted';
                }

                // Handle file uploads (append to existing)
                $existingDocuments = $checklist->documents ?? [];
                if (!is_array($existingDocuments)) {
                    $existingDocuments = [];
                }

                $newDocuments = [];
                if ($request->hasFile('checklist_documents')) {
                    foreach ($request->file('checklist_documents') as $file) {
                        if ($file->isValid()) {
                            $path = $file->store('journeys/checklists', 'public');
                            $newDocuments[] = $path;
                        }
                    }
                }

                $allDocuments = array_merge($existingDocuments, $newDocuments);

                // Update checklist record
                $checklist->update([
                    'status' => $checklistStatus,
                    'notes' => $request->input('checklist_notes', $checklist->notes),
                    'documents' => !empty($allDocuments) ? $allDocuments : null,
                ]);

                // Delete existing answers and create new ones
                $checklist->answers()->delete();

                foreach ($validAnswers as $itemId => $answerData) {
                    JourneyChecklistItemAnswer::create([
                        'journey_checklist_id' => $checklist->id,
                        'journeys_checklist_id' => $itemId,
                        'weight' => (int) $answerData['weight'],
                        'score' => (int) $answerData['score'],
                        'note' => isset($answerData['note']) ? (float) $answerData['note'] : null,
                        'comment' => $answerData['comment'] ?? null,
                    ]);
                }

                DB::commit();

                return redirect()
                    ->route('journeys.show', $journey)
                    ->with('success', __('messages.journey_checklist_updated') ?? 'Checklist updated successfully.');
            } catch (Throwable $transactionException) {
                DB::rollBack();
                throw $transactionException;
            }
        } catch (\Illuminate\Validation\ValidationException $e) {
            throw $e;
        } catch (Throwable $exception) {
            Log::error('Failed to update journey checklist', [
                'error' => $exception->getMessage(),
                'trace' => $exception->getTraceAsString(),
                'checklist_id' => $checklist->id ?? null,
            ]);

            return back()
                ->withInput()
                ->with('error', __('messages.error_updating_journey_checklist') ?? 'Error updating journey checklist.');
        }
    }

    /**
     * Display the yearly planning calendar for journey checklists.
     */
    public function planning(Request $request): View|RedirectResponse
    {
        try {
            $year = (int) $request->input('year', now()->year);

            // Determine available years based on checklist completion dates
            // and their next-inspection due dates (completed_at + 6 months)
            $completedDates = JourneyChecklistInstance::query()
                ->whereNotNull('completed_at')
                ->pluck('completed_at');

            if ($completedDates->isEmpty()) {
                $minYear = $maxYear = $year;
            } else {
                $minYear = $completedDates
                    ->map(fn ($dt) => Carbon::parse($dt)->year)
                    ->min();

                $maxCompletedYear = $completedDates
                    ->map(fn ($dt) => Carbon::parse($dt)->year)
                    ->max();

                $maxDueYear = $completedDates
                    ->map(fn ($dt) => Carbon::parse($dt)->addMonthsNoOverflow(6)->year)
                    ->max();

                $maxYear = max($maxCompletedYear, $maxDueYear);
            }

            // Add one year before and after
            if ($minYear > 2000) {
                $minYear = $minYear - 1;
            }
            $maxYear = $maxYear + 1;

            if ($year < $minYear) {
                $year = $minYear;
            } elseif ($year > $maxYear) {
                $year = $maxYear;
            }

            $years = range($minYear, $maxYear);

            // Load all journeys with all their checklists
            $journeys = Journey::with(['checklists' => function ($query) {
                $query->orderBy('completed_at');
            }])
                ->orderBy('name')
                ->get();

            // Build planning data matrix
            $planningData = [];
            $totalPlanned = 0;
            $totalRealized = 0;

            foreach ($journeys as $journey) {
                // Realized: number of checklists completed in this month/year
                $realized = array_fill(1, 12, 0);
                // Planned: number of checklists whose next due date falls in this month/year
                $planned = array_fill(1, 12, 0);

                foreach ($journey->checklists as $checklist) {
                    if (!$checklist->completed_at) {
                        continue;
                    }

                    // Realized: checklist completed in this month of selected year
                    if ((int) $checklist->completed_at->year === $year) {
                        $month = (int) $checklist->completed_at->month;
                        $realized[$month]++;
                    }

                    // Planned: due date 6 months after completed_at
                    $due = $checklist->completed_at->copy()->addMonthsNoOverflow(6);
                    if ((int) $due->year === $year) {
                        $planned[(int) $due->month]++;
                    }
                }

                // Ensure that every realized checklist is also counted as planned
                // (every realization is assumed to be planned before)
                $nj = [];
                for ($m = 1; $m <= 12; $m++) {
                    $realizedCount = $realized[$m] ?? 0;
                    $plannedCount = $planned[$m] ?? 0;

                    // P must be at least R
                    if ($realizedCount > $plannedCount) {
                        $planned[$m] = $realizedCount;
                    }

                    // NJ = total realized in that month
                    $nj[$m] = $realizedCount;
                }

                // Accumulate global stats while we build rows
                for ($m = 1; $m <= 12; $m++) {
                    $totalPlanned += $planned[$m] ?? 0;
                    $totalRealized += $realized[$m] ?? 0;
                }

                $planningData[] = [
                    'journey' => $journey,
                    'realized' => $realized,
                    'planned' => $planned,
                    'nj' => $nj,
                ];
            }

            $percentage = $totalPlanned > 0
                ? round(($totalRealized / $totalPlanned) * 100, 1)
                : 0;

            // Month labels
            $months = [];
            for ($m = 1; $m <= 12; $m++) {
                $months[$m] = Carbon::createFromDate($year, $m, 1)->locale('fr')->translatedFormat('F');
            }

            return view('journeys.planning', [
                'year' => $year,
                'years' => $years,
                'months' => $months,
                'planningData' => $planningData,
                'totalPlanned' => $totalPlanned,
                'totalRealized' => $totalRealized,
                'percentage' => $percentage,
            ]);
        } catch (Throwable $exception) {
            Log::error('Failed to load journeys planning view', [
                'error' => $exception->getMessage(),
                'trace' => $exception->getTraceAsString(),
            ]);

            return redirect()
                ->route('journeys.index')
                ->with('error', __('messages.error_loading_journeys') ?? 'Error loading journeys planning.');
        }
    }

    /**
     * Export journeys yearly planning to PDF
     */
    public function planningPdf(Request $request): Response
    {
        try {
            $year = (int) $request->input('year', now()->year);

            // Reuse same data preparation as planning()
            $completedDates = JourneyChecklistInstance::query()
                ->whereNotNull('completed_at')
                ->pluck('completed_at');

            if ($completedDates->isEmpty()) {
                $minYear = $maxYear = $year;
            } else {
                $minYear = $completedDates
                    ->map(fn ($dt) => Carbon::parse($dt)->year)
                    ->min();

                $maxCompletedYear = $completedDates
                    ->map(fn ($dt) => Carbon::parse($dt)->year)
                    ->max();

                $maxDueYear = $completedDates
                    ->map(fn ($dt) => Carbon::parse($dt)->addMonthsNoOverflow(6)->year)
                    ->max();

                $maxYear = max($maxCompletedYear, $maxDueYear);
            }

            if ($year < $minYear) {
                $year = $minYear;
            } elseif ($year > $maxYear) {
                $year = $maxYear;
            }

            // Load all journeys with all their checklists
            $journeys = Journey::with(['checklists' => function ($query) {
                $query->orderBy('completed_at');
            }])
                ->orderBy('name')
                ->get();

            $planningData = [];
            $totalPlanned = 0;
            $totalRealized = 0;

            foreach ($journeys as $journey) {
                $realized = array_fill(1, 12, 0);
                $planned = array_fill(1, 12, 0);

                foreach ($journey->checklists as $checklist) {
                    if (!$checklist->completed_at) {
                        continue;
                    }

                    if ((int) $checklist->completed_at->year === $year) {
                        $month = (int) $checklist->completed_at->month;
                        $realized[$month]++;
                    }

                    $due = $checklist->completed_at->copy()->addMonthsNoOverflow(6);
                    if ((int) $due->year === $year) {
                        $planned[(int) $due->month]++;
                    }
                }

                $nj = [];
                for ($m = 1; $m <= 12; $m++) {
                    $realizedCount = $realized[$m] ?? 0;
                    $plannedCount = $planned[$m] ?? 0;

                    if ($realizedCount > $plannedCount) {
                        $planned[$m] = $realizedCount;
                    }

                    $nj[$m] = $realizedCount;
                }

                for ($m = 1; $m <= 12; $m++) {
                    $totalPlanned += $planned[$m] ?? 0;
                    $totalRealized += $realized[$m] ?? 0;
                }

                $planningData[] = [
                    'journey' => $journey,
                    'realized' => $realized,
                    'planned' => $planned,
                    'nj' => $nj,
                ];
            }

            $percentage = $totalPlanned > 0
                ? round(($totalRealized / $totalPlanned) * 100, 1)
                : 0;

            // Month labels
            $months = [];
            for ($m = 1; $m <= 12; $m++) {
                $months[$m] = Carbon::createFromDate($year, $m, 1)->locale('fr')->translatedFormat('F');
            }

            $pdf = PDF::loadView('journeys.planning_pdf', [
                'year' => $year,
                'months' => $months,
                'planningData' => $planningData,
                'totalPlanned' => $totalPlanned,
                'totalRealized' => $totalRealized,
                'percentage' => $percentage,
            ])
                ->setPaper('a4', 'landscape')
                ->setOption('isHtml5ParserEnabled', true)
                ->setOption('isRemoteEnabled', true);

            $filename = 'journeys_planning_' . $year . '_' . date('Y-m-d') . '.pdf';

            return $pdf->download($filename);
        } catch (Throwable $exception) {
            Log::error('Failed to generate journeys planning PDF', [
                'error' => $exception->getMessage(),
                'trace' => $exception->getTraceAsString(),
            ]);

            abort(500, __('messages.error_generating_pdf') ?? 'Error generating PDF.');
        }
    }

    /**
     * Export journeys map to PDF
     */
    public function exportPdf(Request $request)
    {
        try {
            $journeysQuery = Journey::query()
                ->with('latestChecklist')
                ->orderByDesc('created_at');

            // Apply same filters as index
            if ($request->filled('journey_id')) {
                $journeysQuery->where('id', $request->input('journey_id'));
                // Load black points when filtering by specific journey
                $journeysQuery->with('blackPoints');
            }

            $journeys = $journeysQuery->get();

            if ($journeys->isEmpty()) {
                return back()->with('error', __('messages.no_journeys_found') ?? 'No journeys found to export.');
            }

            // Get map image from request (captured by html2canvas)
            $mapImageBase64 = $request->input('map_image');

            $pdf = Pdf::loadView('journeys.pdf.map', [
                'journeys' => $journeys,
                'mapImageBase64' => $mapImageBase64,
            ])
                ->setPaper('a4', 'landscape')
                ->setOption('isHtml5ParserEnabled', true)
                ->setOption('isRemoteEnabled', true);

            $fileName = 'journeys-map-' . now()->format('Ymd_His') . '.pdf';

            return $pdf->download($fileName);
        } catch (Throwable $exception) {
            Log::error('Failed to export journeys PDF', [
                'error' => $exception->getMessage(),
                'trace' => $exception->getTraceAsString(),
            ]);
            
            return back()->with('error', __('messages.error_exporting_pdf') ?? 'Error exporting PDF.');
        }
    }

    /**
     * Export journeys to Excel
     */
    public function export(Request $request)
    {
        try {
            $journeysQuery = Journey::query()
                ->with('latestChecklist')
                ->orderByDesc('created_at');

            // Apply same filters as index
            if ($request->filled('journey_id')) {
                $journeysQuery->where('id', $request->input('journey_id'));
            }

            $journeys = $journeysQuery->get();

            $fileName = 'journeys-' . now()->format('Ymd_His') . '.xlsx';

            return Excel::download(new JourneysExport($journeys), $fileName);
        } catch (Throwable $exception) {
            Log::error('Failed to export journeys', [
                'error' => $exception->getMessage(),
                'trace' => $exception->getTraceAsString(),
            ]);
            
            return back()->with('error', __('messages.error_exporting') ?? 'Error exporting journeys.');
        }
    }
}

