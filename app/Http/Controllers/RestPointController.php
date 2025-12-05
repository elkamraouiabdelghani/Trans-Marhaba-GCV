<?php

namespace App\Http\Controllers;

use App\Exports\RestPointsExport;
use App\Http\Requests\RestPointRequest;
use App\Models\RestPoint;
use App\Models\RestPointChecklist;
use App\Models\RestPointChecklistCategory;
use App\Models\RestPointChecklistItem;
use App\Models\RestPointChecklistItemAnswer;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;
use Illuminate\Support\Carbon;
use Maatwebsite\Excel\Facades\Excel;
use Throwable;

class RestPointController extends Controller
{
    /**
     * Display a listing of rest points with map
     */
    public function index(Request $request): View|RedirectResponse
    {
        try {
            // Check if table exists, if not return empty results
            if (!Schema::hasTable('rest_points')) {
                return view('rest-points.index', [
                    'restPoints' => new \Illuminate\Pagination\LengthAwarePaginator([], 0, 15),
                    'allRestPoints' => collect([]),
                    'types' => [
                        'area' => __('messages.area') ?? 'Area',
                        'station' => __('messages.station') ?? 'Station',
                        'parking' => __('messages.parking') ?? 'Parking',
                        'other' => __('messages.other') ?? 'Other',
                    ],
                    'filters' => $request->only(['type', 'search']),
                ]);
            }

            $restPointsQuery = RestPoint::query()
                ->with('latestChecklist')
                ->orderByDesc('created_at');

            // Filter by type
            if ($request->filled('type')) {
                $restPointsQuery->where('type', $request->input('type'));
            }

            // Search filter
            if ($request->filled('search')) {
                $search = $request->input('search');
                $restPointsQuery->where(function ($query) use ($search) {
                    $query->where('name', 'like', "%{$search}%")
                        ->orWhere('description', 'like', "%{$search}%");
                });
            }

            $restPoints = $restPointsQuery->paginate(15)->withQueryString();

            // Get filtered rest points for map (without pagination, but with same filters)
            $allRestPoints = (clone $restPointsQuery)->get();

            return view('rest-points.index', [
                'restPoints' => $restPoints,
                'allRestPoints' => $allRestPoints,
                'types' => [
                    'area' => __('messages.area') ?? 'Area',
                    'station' => __('messages.station') ?? 'Station',
                    'parking' => __('messages.parking') ?? 'Parking',
                    'other' => __('messages.other') ?? 'Other',
                ],
                'filters' => $request->only(['type', 'search']),
            ]);
        } catch (Throwable $exception) {
            Log::error('Failed to load rest points index', [
                'error' => $exception->getMessage(),
                'trace' => $exception->getTraceAsString()
            ]);
            
            // Instead of redirecting, show the page with empty data
            return view('rest-points.index', [
                'restPoints' => new \Illuminate\Pagination\LengthAwarePaginator([], 0, 15),
                'allRestPoints' => collect([]),
                'types' => [
                    'area' => __('messages.area') ?? 'Area',
                    'station' => __('messages.station') ?? 'Station',
                    'parking' => __('messages.parking') ?? 'Parking',
                    'other' => __('messages.other') ?? 'Other',
                ],
                'filters' => $request->only(['type', 'search']),
            ])->with('error', __('messages.error_loading_rest_points') ?? 'Error loading rest points. Please run the migration: php artisan migrate');
        }
    }


    /**
     * Show the form for creating a new rest point
     */
    public function create(Request $request): View|RedirectResponse
    {
        try {
            // Check if table exists, if not redirect back to index with message
            if (!Schema::hasTable('rest_points')) {
                return redirect()
                    ->route('rest-points.index')
                    ->with('error', __('messages.error_creating_rest_point') ?? 'Error creating rest point. Please run the migration: php artisan migrate');
            }

            // Load active categories with their active items for checklist
            $categories = RestPointChecklistCategory::where('is_active', true)
                ->with(['items' => function ($query) {
                    $query->where('is_active', true);
                }])
                ->orderBy('name')
                ->get();

            return view('rest-points.create', [
                'types' => [
                    'area' => __('messages.area') ?? 'Area',
                    'station' => __('messages.station') ?? 'Station',
                    'parking' => __('messages.parking') ?? 'Parking',
                    'other' => __('messages.other') ?? 'Other',
                ],
                'categories' => $categories,
                'backUrl' => route('rest-points.index'),
            ]);
        } catch (Throwable $exception) {
            Log::error('Failed to load rest point create form', [
                'error' => $exception->getMessage(),
                'trace' => $exception->getTraceAsString(),
            ]);

            return redirect()
                ->route('rest-points.index')
                ->with('error', __('messages.error_creating_rest_point') ?? 'Error creating rest point.');
        }
    }


    /**
     * Store a newly created rest point
     */
    public function store(RestPointRequest $request): RedirectResponse
    {
        try {
            // Check if table exists
            if (!Schema::hasTable('rest_points')) {
                return back()
                    ->withInput()
                    ->with('error', __('messages.error_creating_rest_point') ?? 'Error creating rest point. Please run the migration: php artisan migrate');
            }

            // Validate coordinates are set
            $latitude = $request->input('latitude');
            $longitude = $request->input('longitude');
            
            if (empty($latitude) || empty($longitude)) {
                return back()
                    ->withInput()
                    ->with('error', __('messages.location_required') ?? 'Please select a location on the map before submitting.');
            }

            // Start database transaction
            DB::beginTransaction();

            try {
                // Create rest point
                $restPoint = RestPoint::create([
                    'name' => $request->input('name'),
                    'type' => $request->input('type'),
                    'latitude' => (float) $latitude,
                    'longitude' => (float) $longitude,
                    'description' => $request->input('description'),
                    'created_by' => Auth::id(),
                ]);

                // Validate and create checklist if provided (optional)
                $checklistData = $request->input('checklist', []);
                
                if (!empty($checklistData)) {
                    // Get all active items to validate against
                    $activeItems = RestPointChecklistItem::where('is_active', true)
                        ->pluck('id')
                        ->toArray();

                    // Build a list of valid answers (only for submitted/checked items)
                    $validAnswers = [];

                    foreach ($checklistData as $itemId => $answerData) {
                        // Ensure item is a valid active checklist item
                        if (!in_array((int) $itemId, $activeItems, true)) {
                            DB::rollBack();
                            return back()
                                ->withInput()
                                ->with('error', __('messages.invalid_checklist_item') ?? 'Invalid checklist item detected.');
                        }

                        // Only keep answers where a Yes/No value was actually provided
                        if (isset($answerData['is_checked']) && in_array($answerData['is_checked'], ['0', '1'], true)) {
                            $validAnswers[(int) $itemId] = $answerData;
                        }
                    }

                    // If user didn't answer any item, skip creating a checklist entirely
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
                                    $path = $file->store('rest-points/checklists', 'public');
                                    $documents[] = $path;
                                }
                            }
                        }

                        // Create checklist record
                        $checklist = RestPointChecklist::create([
                            'rest_point_id' => $restPoint->id,
                            'completed_by' => Auth::id(),
                            'completed_at' => now(),
                            'status' => $checklistStatus,
                            'notes' => $request->input('checklist_notes'),
                            'documents' => !empty($documents) ? $documents : null,
                        ]);

                        // Create answers for each valid item
                        foreach ($validAnswers as $itemId => $answerData) {
                            RestPointChecklistItemAnswer::create([
                                'rest_points_checklist_id' => $checklist->id,
                                'rest_points_checklist_item_id' => $itemId,
                                'is_checked' => $answerData['is_checked'] === '1',
                                'comment' => !empty($answerData['comment']) ? $answerData['comment'] : null,
                            ]);
                        }
                    }
                }

                DB::commit();

                return redirect()
                    ->route('rest-points.index')
                    ->with('success', __('messages.rest_point_created') ?? 'Rest point created successfully.');
            } catch (Throwable $transactionException) {
                DB::rollBack();
                throw $transactionException;
            }
        } catch (\Illuminate\Validation\ValidationException $e) {
            // Re-throw validation exceptions so they're handled properly
            throw $e;
        } catch (Throwable $exception) {
            Log::error('Failed to create rest point', [
                'error' => $exception->getMessage(),
                'trace' => $exception->getTraceAsString(),
                'file' => $exception->getFile(),
                'line' => $exception->getLine(),
                'data' => $request->except(['_token']),
            ]);
            
            $errorMessage = __('messages.error_creating_rest_point') ?? 'Error creating rest point.';
            
            // Add more specific error message for debugging
            if (config('app.debug')) {
                $errorMessage .= ' ' . $exception->getMessage();
            }
            
            return back()
                ->withInput()
                ->with('error', $errorMessage);
        }
    }

    /**
     * Show the form for editing the specified rest point
     */
    public function edit(RestPoint $restPoint): View|RedirectResponse
    {
        try {
            // Load active categories with their active items for checklist
            $categories = RestPointChecklistCategory::where('is_active', true)
                ->with(['items' => function ($query) {
                    $query->where('is_active', true);
                }])
                ->orderBy('name')
                ->get();

            // Load latest checklist (if any) with answers
            $existingChecklist = RestPointChecklist::where('rest_point_id', $restPoint->id)
                ->with('answers')
                ->orderByDesc('completed_at')
                ->first();

            // Map answers by item ID for easy access in view
            $answersByItemId = [];
            if ($existingChecklist) {
                foreach ($existingChecklist->answers as $answer) {
                    $answersByItemId[$answer->rest_points_checklist_item_id] = $answer;
                }
            }

            return view('rest-points.edit', [
                'restPoint' => $restPoint,
                'types' => [
                    'area' => __('messages.area') ?? 'Area',
                    'station' => __('messages.station') ?? 'Station',
                    'parking' => __('messages.parking') ?? 'Parking',
                    'other' => __('messages.other') ?? 'Other',
                ],
                'categories' => $categories,
                'existingChecklist' => $existingChecklist,
                'answersByItemId' => $answersByItemId,
                'backUrl' => route('rest-points.index'),
            ]);
        } catch (Throwable $exception) {
            Log::error('Failed to load rest point edit form', [
                'error' => $exception->getMessage(),
                'rest_point_id' => $restPoint->id,
                'trace' => $exception->getTraceAsString(),
            ]);

            return redirect()
                ->route('rest-points.index')
                ->with('error', __('messages.error_updating_rest_point') ?? 'Error loading rest point edit form.');
        }
    }


    /**
     * Update the specified rest point
     */
    public function update(RestPointRequest $request, RestPoint $restPoint): RedirectResponse
    {
        try {
            // Validate coordinates are set
            $latitude = $request->input('latitude');
            $longitude = $request->input('longitude');
            
            if (empty($latitude) || empty($longitude)) {
                return back()
                    ->withInput()
                    ->with('error', __('messages.location_required') ?? 'Please select a location on the map before submitting.');
            }

            // Start database transaction
            DB::beginTransaction();

            try {
                Log::info('Updating rest point', [
                    'rest_point_id' => $restPoint->id,
                    'has_checklist_data' => $request->has('checklist'),
                    'checklist_count' => count($request->input('checklist', [])),
                ]);
                // Update rest point
                $restPoint->update([
                    'name' => $request->input('name'),
                    'type' => $request->input('type'),
                    'latitude' => (float) $latitude,
                    'longitude' => (float) $longitude,
                    'description' => $request->input('description'),
                ]);

                // Handle checklist data
                $checklistData = $request->input('checklist', []);
                
                Log::info('Processing checklist data', [
                    'checklist_data_count' => count($checklistData),
                    'checklist_keys' => array_keys($checklistData),
                ]);

                if (! empty($checklistData)) {
                    // Get or create checklist
                    $checklist = RestPointChecklist::where('rest_point_id', $restPoint->id)->first();

                    // Get all active items to validate against (normalize to integers)
                    $activeItems = RestPointChecklistItem::where('is_active', true)
                        ->pluck('id')
                        ->map(fn ($id) => (int) $id)
                        ->toArray();

                    // Build a list of valid answers (only for submitted/checked items)
                    $validAnswers = [];

                    // Validate each submitted answer (for both new and existing checklists)
                    foreach ($checklistData as $itemId => $answerData) {
                        $itemIdInt = (int) $itemId;
                        if (! in_array($itemIdInt, $activeItems, true)) {
                            DB::rollBack();
                            Log::warning('Invalid checklist item', [
                                'item_id' => $itemId,
                                'item_id_int' => $itemIdInt,
                                'active_items' => $activeItems,
                            ]);

                            return back()
                                ->withInput()
                                ->with('error', __('messages.invalid_checklist_item') ?? 'Invalid checklist item detected.');
                        }

                        if (isset($answerData['is_checked']) && in_array($answerData['is_checked'], ['0', '1'], true)) {
                            $validAnswers[$itemIdInt] = $answerData;
                        }
                    }

                    // If user didn't answer any item, don't touch checklist/answers
                    if (empty($validAnswers)) {
                        Log::info('No valid checklist answers submitted; skipping checklist update/creation', [
                            'rest_point_id' => $restPoint->id,
                        ]);
                        goto checklist_done;
                    }
                    
                    if (! $checklist) {
                        // Create new checklist if it doesn't exist
                        // Get status from request (default to 'accepted')
                        $checklistStatus = $request->input('checklist_status', 'accepted');
                        if (!in_array($checklistStatus, ['pending', 'accepted', 'rejected'])) {
                            $checklistStatus = 'accepted';
                        }

                        // Handle file uploads
                        $documents = [];
                        if ($request->hasFile('checklist_documents')) {
                            foreach ($request->file('checklist_documents') as $file) {
                                if ($file->isValid()) {
                                    $path = $file->store('rest-points/checklists', 'public');
                                    $documents[] = $path;
                                }
                            }
                        }

                        try {
                            $checklist = RestPointChecklist::create([
                                'rest_point_id' => $restPoint->id,
                                'completed_by' => Auth::id(),
                                'completed_at' => now(),
                                'status' => $checklistStatus,
                                'notes' => $request->input('checklist_notes'),
                                'documents' => ! empty($documents) ? $documents : null,
                            ]);
                            
                            Log::info('Created new checklist for rest point', [
                                'rest_point_id' => $restPoint->id,
                                'checklist_id' => $checklist->id,
                            ]);
                        } catch (Throwable $checklistException) {
                            DB::rollBack();
                            Log::error('Failed to create checklist', [
                                'rest_point_id' => $restPoint->id,
                                'error' => $checklistException->getMessage(),
                                'trace' => $checklistException->getTraceAsString(),
                            ]);
                            throw $checklistException;
                        }
                    } else {
                        // Update existing checklist
                        // Get status from request (default to existing status or 'accepted')
                        $checklistStatus = $request->input('checklist_status', $checklist->status ?? 'accepted');
                        if (!in_array($checklistStatus, ['pending', 'accepted', 'rejected'])) {
                            $checklistStatus = $checklist->status ?? 'accepted';
                        }

                        // Handle existing documents (keep ones that weren't removed)
                        $existingDocuments = $checklist->documents ?? [];
                        $keptDocuments = $request->input('existing_documents');

                        // If the field is completely missing, assume user did not change existing documents
                        if ($keptDocuments === null) {
                            $finalDocuments = $existingDocuments;
                            $removedDocuments = [];
                        } else {
                            $keptDocuments = is_array($keptDocuments) ? $keptDocuments : [];
                            $finalDocuments = array_values(array_intersect($existingDocuments, $keptDocuments));
                            $removedDocuments = array_diff($existingDocuments, $keptDocuments);
                        }

                        // Handle new file uploads
                        if ($request->hasFile('checklist_documents')) {
                            foreach ($request->file('checklist_documents') as $file) {
                                if ($file->isValid()) {
                                    $path = $file->store('rest-points/checklists', 'public');
                                    $finalDocuments[] = $path;
                                }
                            }
                        }

                        // Delete removed documents from storage
                        if (!empty($removedDocuments)) {
                            foreach ($removedDocuments as $docPath) {
                                if (Storage::disk('public')->exists($docPath)) {
                                    Storage::disk('public')->delete($docPath);
                                }
                            }
                        }

                        try {
                            $checklist->update([
                                'completed_by' => Auth::id(),
                                'completed_at' => now(),
                                'status' => $checklistStatus,
                                'notes' => $request->input('checklist_notes'),
                                'documents' => ! empty($finalDocuments) ? $finalDocuments : null,
                            ]);
                            
                            Log::info('Updated existing checklist', [
                                'rest_point_id' => $restPoint->id,
                                'checklist_id' => $checklist->id,
                            ]);
                        } catch (Throwable $checklistException) {
                            DB::rollBack();
                            Log::error('Failed to update checklist', [
                                'rest_point_id' => $restPoint->id,
                                'checklist_id' => $checklist->id,
                                'error' => $checklistException->getMessage(),
                                'trace' => $checklistException->getTraceAsString(),
                            ]);
                            throw $checklistException;
                        }
                    }

                    // Update or create answers for each valid item
                    // This will update existing answers or create new ones if they don't exist
                    foreach ($validAnswers as $itemIdInt => $answerData) {
                        try {
                            RestPointChecklistItemAnswer::updateOrCreate(
                                [
                                    'rest_points_checklist_id' => $checklist->id,
                                    'rest_points_checklist_item_id' => $itemIdInt,
                                ],
                                [
                                    'is_checked' => $answerData['is_checked'] === '1',
                                    'comment' => !empty($answerData['comment']) ? trim($answerData['comment']) : null,
                                ]
                            );
                        } catch (Throwable $answerException) {
                            DB::rollBack();
                            Log::error('Failed to save checklist answer', [
                                'rest_point_id' => $restPoint->id,
                                'checklist_id' => $checklist->id,
                                'item_id' => $itemIdInt,
                                'error' => $answerException->getMessage(),
                                'trace' => $answerException->getTraceAsString(),
                            ]);
                            throw $answerException;
                        }
                    }
                    
                    Log::info('Checklist answers processed successfully', [
                        'rest_point_id' => $restPoint->id,
                        'checklist_id' => $checklist->id,
                        'answers_count' => count($validAnswers),
                    ]);
                } else {
                    checklist_done:
                    // No checklist data provided or no valid answers; nothing to do
                }

                DB::commit();

                Log::info('Rest point updated successfully', [
                    'rest_point_id' => $restPoint->id,
                ]);

                return redirect()
                    ->route('rest-points.show', $restPoint)
                    ->with('success', __('messages.rest_point_updated') ?? 'Rest point updated successfully.');
            } catch (Throwable $transactionException) {
                DB::rollBack();
                
                Log::error('Transaction failed during rest point update', [
                    'rest_point_id' => $restPoint->id,
                    'error' => $transactionException->getMessage(),
                    'trace' => $transactionException->getTraceAsString(),
                ]);
                
                throw $transactionException;
            }
        } catch (\Illuminate\Validation\ValidationException $e) {
            // Re-throw validation exceptions so they're handled properly
            throw $e;
        } catch (Throwable $exception) {
            Log::error('Failed to update rest point', [
                'error' => $exception->getMessage(),
                'rest_point_id' => $restPoint->id,
                'file' => $exception->getFile(),
                'line' => $exception->getLine(),
                'data' => $request->except(['_token', '_method']),
                'trace' => $exception->getTraceAsString(),
            ]);
            
            $errorMessage = __('messages.error_updating_rest_point') ?? 'Error updating rest point.';
            
            // Include actual error message in debug mode
            if (config('app.debug')) {
                $errorMessage .= ' ' . $exception->getMessage();
            }
            
            return back()
                ->withInput()
                ->with('error', $errorMessage);
        }
    }

    /**
     * Display the specified rest point
     */
    public function show(RestPoint $restPoint): View|RedirectResponse
    {
        try {
            // Load rest point with relationships
            $restPoint->load(['createdBy', 'checklists']);

            // Get the latest checklist with relationships
            $checklist = RestPointChecklist::where('rest_point_id', $restPoint->id)
                ->with([
                    'completedByUser',
                    'answers.item',
                ])
                ->latest('completed_at')
                ->first();

            // Load full checklist history for this rest point
            $checklistsHistory = RestPointChecklist::where('rest_point_id', $restPoint->id)
                ->orderByDesc('completed_at')
                ->get();

            // Load categories with items for displaying checklist results
            $categories = RestPointChecklistCategory::where('is_active', true)
                ->with(['items' => function ($query) {
                    $query->where('is_active', true);
                }])
                ->orderBy('name')
                ->get();

            // Map answers by item ID for easy access
            $answersByItemId = [];
            if ($checklist && $checklist->answers) {
                foreach ($checklist->answers as $answer) {
                    $answersByItemId[$answer->rest_points_checklist_item_id] = $answer;
                }
            }

            return view('rest-points.show', [
                'restPoint' => $restPoint,
                'checklist' => $checklist,
                'checklistsHistory' => $checklistsHistory,
                'categories' => $categories,
                'answersByItemId' => $answersByItemId,
                'types' => [
                    'area' => __('messages.area') ?? 'Area',
                    'station' => __('messages.station') ?? 'Station',
                    'parking' => __('messages.parking') ?? 'Parking',
                    'other' => __('messages.other') ?? 'Other',
                ],
            ]);
        } catch (Throwable $exception) {
            Log::error('Failed to load rest point show page', [
                'error' => $exception->getMessage(),
                'rest_point_id' => $restPoint->id,
                'trace' => $exception->getTraceAsString(),
            ]);

            return redirect()
                ->route('rest-points.index')
                ->with('error', __('messages.error_loading_rest_point') ?? 'Error loading rest point details.');
        }
    }

    /**
     * Yearly planning view for rest point inspections
     */
    public function planning(Request $request): View|RedirectResponse
    {
        try {
            $year = (int) $request->input('year', now()->year);

            // Determine available years based on checklist completion dates
            // and their next-inspection due dates (completed_at + 6 months)
            $completedDates = RestPointChecklist::query()
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

            $years = range($minYear, $maxYear);

            // Load all rest points with all their checklists
            $restPoints = RestPoint::with(['checklists' => function ($query) {
                $query->orderBy('completed_at');
            }])
                ->orderBy('name')
                ->get();

            // Build planning data matrix
            $planningData = [];
            $totalPlanned = 0;
            $totalRealized = 0;

            foreach ($restPoints as $restPoint) {
                // Realized: number of checklists completed in this month/year
                $realized = array_fill(1, 12, 0);
                // Planned: number of checklists whose next due date falls in this month/year
                $planned = array_fill(1, 12, 0);

                foreach ($restPoint->checklists as $checklist) {
                    if (! $checklist->completed_at) {
                        continue;
                    }

                    // Realized: checklist completed in this month of selected year
                    if ((int) $checklist->completed_at->year === $year) {
                        $month = (int) $checklist->completed_at->month;
                        $realized[$month]++;
                    }

                    // Planned: due date 6 months after effective inspection date
                    $effective = $checklist->effective_inspection_date;
                    if ($effective instanceof Carbon) {
                        $due = $effective->copy()->addMonthsNoOverflow(6);
                        if ((int) $due->year === $year) {
                            $planned[(int) $due->month]++;
                        }
                    }
                }

                // Ensure that every realized checklist is also counted as planned
                // (every realization is assumed to be planned before).
                // Also compute NJ (total realized) per month.
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
                    'restPoint' => $restPoint,
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
                $months[$m] = Carbon::createFromDate($year, $m, 1)->translatedFormat('F');
            }

            return view('rest-points.planning', [
                'year' => $year,
                'years' => $years,
                'months' => $months,
                'planningData' => $planningData,
            ]);
        } catch (Throwable $exception) {
            Log::error('Failed to load rest points planning view', [
                'error' => $exception->getMessage(),
                'trace' => $exception->getTraceAsString(),
            ]);

            return redirect()
                ->route('rest-points.index')
                ->with('error', __('messages.error_loading_rest_points') ?? 'Error loading rest points planning.');
        }
    }

    /**
     * Export rest points yearly planning to PDF
     */
    public function planningPdf(Request $request)
    {
        try {
            $year = (int) $request->input('year', now()->year);

            // Reuse same data preparation as planning()
            $completedDates = RestPointChecklist::query()
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

            // Load all rest points with all their checklists
            $restPoints = RestPoint::with(['checklists' => function ($query) {
                $query->orderBy('completed_at');
            }])
                ->orderBy('name')
                ->get();

            $planningData = [];
            $totalPlanned = 0;
            $totalRealized = 0;

            foreach ($restPoints as $restPoint) {
                $realized = array_fill(1, 12, 0);
                $planned = array_fill(1, 12, 0);

                foreach ($restPoint->checklists as $checklist) {
                    if (! $checklist->completed_at) {
                        continue;
                    }

                    if ((int) $checklist->completed_at->year === $year) {
                        $month = (int) $checklist->completed_at->month;
                        $realized[$month]++;
                    }

                    $effective = $checklist->effective_inspection_date;
                    if ($effective instanceof Carbon) {
                        $due = $effective->copy()->addMonthsNoOverflow(6);
                        if ((int) $due->year === $year) {
                            $planned[(int) $due->month]++;
                        }
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

                // Accumulate global stats while we build rows
                for ($m = 1; $m <= 12; $m++) {
                    $totalPlanned += $planned[$m] ?? 0;
                    $totalRealized += $realized[$m] ?? 0;
                }

                $planningData[] = [
                    'restPoint' => $restPoint,
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
                $months[$m] = Carbon::createFromDate($year, $m, 1)->translatedFormat('F');
            }

            $pdf = Pdf::loadView('rest-points.pdf.planning', [
                'year' => $year,
                'months' => $months,
                'planningData' => $planningData,
                'stats' => [
                    'planned' => $totalPlanned,
                    'realized' => $totalRealized,
                    'percentage' => $percentage,
                ],
            ])->setPaper('a3', 'landscape');

            $fileName = 'rest-points-planning-' . $year . '-' . now()->format('Ymd_His') . '.pdf';

            return $pdf->download($fileName);
        } catch (Throwable $exception) {
            Log::error('Failed to export rest points planning PDF', [
                'error' => $exception->getMessage(),
                'trace' => $exception->getTraceAsString(),
            ]);

            return back()->with('error', __('messages.error_exporting_pdf') ?? 'Error exporting planning PDF.');
        }
    }

    /**
     * Export rest points map to PDF
     */
    public function exportPdf(Request $request)
    {
        try {
            $restPointsQuery = RestPoint::query()
                ->orderByDesc('created_at');

            // Apply same filters as index
            if ($request->filled('type')) {
                $restPointsQuery->where('type', $request->input('type'));
            }

            if ($request->filled('search')) {
                $search = $request->input('search');
                $restPointsQuery->where(function ($query) use ($search) {
                    $query->where('name', 'like', "%{$search}%")
                        ->orWhere('description', 'like', "%{$search}%");
                });
            }

            $restPoints = $restPointsQuery->get();

            if ($restPoints->isEmpty()) {
                return back()->with('error', __('messages.no_rest_points_found') ?? 'No rest points found to export.');
            }

            // Get map image from request (captured by html2canvas)
            $mapImageBase64 = $request->input('map_image');
            
            // If no map image provided, try to generate static map URL as fallback
            $staticMapUrl = null;
            if (empty($mapImageBase64)) {
                $staticMapUrl = $this->generateStaticMapUrl($restPoints);
                
                if ($staticMapUrl) {
                    try {
                        $response = Http::timeout(10)->get($staticMapUrl);
                        if ($response->successful()) {
                            $imageContent = $response->body();
                            if (!empty($imageContent)) {
                                $mapImageBase64 = 'data:image/png;base64,' . base64_encode($imageContent);
                            }
                        }
                    } catch (\Exception $e) {
                        Log::warning('Failed to download map image for PDF', [
                            'url' => $staticMapUrl,
                            'error' => $e->getMessage(),
                        ]);
                    }
                }
            }

            $pdf = Pdf::loadView('rest-points.pdf.map', [
                'restPoints' => $restPoints,
                'staticMapUrl' => $staticMapUrl,
                'mapImageBase64' => $mapImageBase64,
            ])
                ->setPaper('a4', 'landscape')
                ->setOption('isHtml5ParserEnabled', true)
                ->setOption('isRemoteEnabled', true);

            $fileName = 'rest-points-map-' . now()->format('Ymd_His') . '.pdf';

            return $pdf->download($fileName);
        } catch (Throwable $exception) {
            Log::error('Failed to export rest points PDF', [
                'error' => $exception->getMessage(),
                'trace' => $exception->getTraceAsString(),
            ]);
            
            return back()->with('error', __('messages.error_exporting_pdf') ?? 'Error exporting PDF.');
        }
    }

    /**
     * Generate static map URL with markers for all rest points
     */
    private function generateStaticMapUrl($restPoints): ?string
    {
        if ($restPoints->isEmpty()) {
            return null;
        }

        $lats = $restPoints->pluck('latitude')->filter();
        $lngs = $restPoints->pluck('longitude')->filter();

        if ($lats->isEmpty() || $lngs->isEmpty()) {
            return null;
        }

        $centerLat = $lats->avg();
        $centerLng = $lngs->avg();

        // Calculate zoom level based on spread of points
        $latSpread = $lats->max() - $lats->min();
        $lngSpread = $lngs->max() - $lngs->min();
        $maxSpread = max($latSpread, $lngSpread);

        if ($maxSpread > 5) {
            $zoom = 6;
        } elseif ($maxSpread > 1) {
            $zoom = 7;
        } elseif ($maxSpread > 0.5) {
            $zoom = 8;
        } elseif ($maxSpread > 0.1) {
            $zoom = 9;
        } else {
            $zoom = 10;
        }

        // Try Google Maps Static API first (if API key is available)
        $googleApiKey = env('GOOGLE_MAPS_API_KEY', '');
        if (!empty($googleApiKey)) {
            $markerParams = [];
            
            // Group markers by type for different colors
            $markersByType = [
                'area' => [],
                'station' => [],
                'parking' => [],
                'other' => []
            ];

            foreach ($restPoints as $point) {
                if ($point->latitude && $point->longitude) {
                    $type = $point->type ?? 'other';
                    $markersByType[$type][] = $point->latitude . ',' . $point->longitude;
                }
            }

            // Add markers with different colors for each type
            if (!empty($markersByType['area'])) {
                $markerParams[] = 'markers=color:green|' . implode('|', array_slice($markersByType['area'], 0, 50));
            }
            if (!empty($markersByType['station'])) {
                $markerParams[] = 'markers=color:blue|' . implode('|', array_slice($markersByType['station'], 0, 50));
            }
            if (!empty($markersByType['parking'])) {
                $markerParams[] = 'markers=color:yellow|' . implode('|', array_slice($markersByType['parking'], 0, 50));
            }
            if (!empty($markersByType['other'])) {
                $markerParams[] = 'markers=color:gray|' . implode('|', array_slice($markersByType['other'], 0, 50));
            }

            return 'https://maps.googleapis.com/maps/api/staticmap?' .
                'center=' . urlencode($centerLat . ',' . $centerLng) .
                '&zoom=' . $zoom .
                '&size=800x600' .
                '&maptype=roadmap' .
                (!empty($markerParams) ? '&' . implode('&', $markerParams) : '') .
                '&key=' . $googleApiKey;
        }

        // Fallback: Use a free service that supports markers
        // Using a service that can render markers on static maps
        // For now, we'll use OpenStreetMap static map
        // Note: Most free OSM static map services don't support markers easily
        // The map will show the center area, and markers are listed in the table below
        
        $allMarkers = [];
        foreach ($restPoints as $point) {
            if ($point->latitude && $point->longitude) {
                $allMarkers[] = $point->latitude . ',' . $point->longitude;
            }
        }

        // Use OpenStreetMap static map (basic, without markers in the image)
        // Markers will be shown in the detailed table below
        return 'https://staticmap.openstreetmap.de/staticmap.php?' .
            'center=' . urlencode($centerLat . ',' . $centerLng) .
            '&zoom=' . $zoom .
            '&size=800x600' .
            '&maptype=mapnik';
    }

    /**
     * Export rest points to Excel
     */
    public function export(Request $request)
    {
        try {
            $restPointsQuery = RestPoint::query()
                ->orderByDesc('created_at');

            // Apply same filters as index
            if ($request->filled('type')) {
                $restPointsQuery->where('type', $request->input('type'));
            }

            if ($request->filled('search')) {
                $search = $request->input('search');
                $restPointsQuery->where(function ($query) use ($search) {
                    $query->where('name', 'like', "%{$search}%")
                        ->orWhere('description', 'like', "%{$search}%");
                });
            }

            $restPoints = $restPointsQuery->get();

            $fileName = 'rest-points-' . now()->format('Ymd_His') . '.xlsx';

            return Excel::download(new RestPointsExport($restPoints), $fileName);
        } catch (Throwable $exception) {
            Log::error('Failed to export rest points', [
                'error' => $exception->getMessage(),
                'trace' => $exception->getTraceAsString(),
            ]);
            
            return back()->with('error', __('messages.error_exporting') ?? 'Error exporting rest points.');
        }
    }

    /**
     * Remove the specified rest point
     */
    public function destroy(RestPoint $restPoint): RedirectResponse
    {
        try {
            DB::beginTransaction();

            // Delete all related checklists, their answers, and documents
            $restPoint->load(['checklists.answers']);

            foreach ($restPoint->checklists as $checklist) {
                // Delete checklist documents from storage
                if (is_array($checklist->documents) && ! empty($checklist->documents)) {
                    foreach ($checklist->documents as $docPath) {
                        if ($docPath && Storage::disk('public')->exists($docPath)) {
                            Storage::disk('public')->delete($docPath);
                        }
                    }
                }

                // Delete answers
                $checklist->answers()->delete();

                // Delete checklist itself
                $checklist->delete();
            }

            // Finally delete the rest point
            $restPoint->delete();

            DB::commit();

            return redirect()
                ->route('rest-points.index')
                ->with('success', __('messages.rest_point_deleted') ?? 'Rest point and related data deleted successfully.');
        } catch (Throwable $exception) {
            DB::rollBack();

            Log::error('Failed to delete rest point and related data', [
                'error' => $exception->getMessage(),
                'rest_point_id' => $restPoint->id,
                'trace' => $exception->getTraceAsString(),
            ]);

            return back()->with('error', __('messages.error_deleting_rest_point') ?? 'Error deleting rest point.');
        }
    }
}

