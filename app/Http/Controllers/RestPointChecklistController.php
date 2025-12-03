<?php

namespace App\Http\Controllers;

use App\Models\RestPoint;
use App\Models\RestPointChecklist;
use App\Models\RestPointChecklistCategory;
use App\Models\RestPointChecklistItem;
use App\Models\RestPointChecklistItemAnswer;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\View\View;
use Barryvdh\DomPDF\Facade\Pdf;
use Throwable;

class RestPointChecklistController extends Controller
{
    /**
     * Show the checklist form for a given rest point.
     */
    public function create(RestPoint $rest_point): View|RedirectResponse
    {
        try {
            // Enforce 6-month rule: allow new checklist only if none exists
            // or if we are within the allowed window before the 6-month due date.
            $lastChecklist = $rest_point->checklists()
                ->orderByDesc('completed_at')
                ->first();

            if ($lastChecklist && $lastChecklist->completed_at) {
                $dueDate = $lastChecklist->completed_at->copy()->addMonthsNoOverflow(6);
                // Allow creating a new checklist from 2 weeks before due date
                $openFrom = $dueDate->copy()->subWeeks(2);

                if (now()->lt($openFrom)) {
                    return redirect()
                        ->route('rest-points.show', $rest_point)
                        ->with(
                            'error',
                            __('messages.checklist_not_due_yet')
                                ?? 'A new checklist can only be created 6 months after the last one.'
                        );
                }
            }

            $categories = RestPointChecklistCategory::query()
                ->where('is_active', true)
                ->with(['items' => function ($query) {
                    $query->where('is_active', true);
                }])
                ->get();

            if ($categories->isEmpty()) {
                return redirect()
                    ->route('rest-points.index')
                    ->with('error', __('messages.error_loading_rest_points') ?? 'No checklist template is defined for rest points.');
            }

            return view('rest-points.checklists.create', [
                'restPoint' => $rest_point,
                'categories' => $categories,
            ]);
        } catch (Throwable $exception) {
            Log::error('Failed to load rest point checklist form', [
                'error' => $exception->getMessage(),
                'trace' => $exception->getTraceAsString(),
                'rest_point_id' => $rest_point->id ?? null,
            ]);

            return redirect()
                ->route('rest-points.index')
                ->with('error', __('messages.error_loading_rest_points') ?? 'Error loading rest point checklist form.');
        }
    }

    /**
     * Store a newly submitted checklist for a rest point.
     */
    public function store(Request $request, RestPoint $rest_point): RedirectResponse
    {
        $validated = $request->validate([
            'answers' => ['required', 'array'],
            'answers.*.value' => ['required', 'in:yes,no'],
            'answers.*.comment' => ['nullable', 'string'],
            'checklist_status' => ['nullable', 'in:pending,accepted,rejected'],
            'checklist_documents.*' => ['nullable', 'image', 'max:5120'],
        ]);

        try {
            // Double-check 6-month rule on store as well.
            $lastChecklist = $rest_point->checklists()
                ->orderByDesc('completed_at')
                ->first();

            if ($lastChecklist && $lastChecklist->completed_at) {
                $dueDate = $lastChecklist->completed_at->copy()->addMonthsNoOverflow(6);
                $openFrom = $dueDate->copy()->subWeeks(2);

                if (now()->lt($openFrom)) {
                    return redirect()
                        ->route('rest-points.show', $rest_point)
                        ->with(
                            'error',
                            __('messages.checklist_not_due_yet')
                                ?? 'A new checklist can only be created 6 months after the last one.'
                        );
                }
            }

            DB::beginTransaction();

            // Determine status (default to accepted if not provided)
            $status = $request->input('checklist_status', 'accepted');
            if (! in_array($status, ['pending', 'accepted', 'rejected'], true)) {
                $status = 'accepted';
            }

            // Handle document uploads
            $documents = [];
            if ($request->hasFile('checklist_documents')) {
                foreach ($request->file('checklist_documents') as $file) {
                    if ($file && $file->isValid()) {
                        $path = $file->store('rest-points/checklists', 'public');
                        $documents[] = $path;
                    }
                }
            }

            $checklist = RestPointChecklist::create([
                'rest_point_id' => $rest_point->id,
                'completed_by' => Auth::id(),
                'completed_at' => now(),
                'status' => $status,
                'notes' => $request->input('notes'),
                'documents' => ! empty($documents) ? $documents : null,
            ]);

            $itemIds = array_keys($validated['answers']);
            $items = RestPointChecklistItem::whereIn('id', $itemIds)
                ->where('is_active', true)
                ->get()
                ->keyBy('id');

            foreach ($validated['answers'] as $itemId => $answerData) {
                // Skip unknown items
                if (!isset($items[$itemId])) {
                    continue;
                }

                RestPointChecklistItemAnswer::create([
                    'rest_points_checklist_id' => $checklist->id,
                    'rest_points_checklist_item_id' => $itemId,
                    'is_checked' => $answerData['value'] === 'yes',
                    'comment' => $answerData['comment'] ?? null,
                ]);
            }

            DB::commit();

            // After creating a new checklist, go back to the rest point page
            // so that next_inspection_due is recalculated from this latest checklist.
            return redirect()
                ->route('rest-points.show', $rest_point)
                ->with('success', __('messages.rest_point_created') ?? 'Checklist saved successfully.');
        } catch (Throwable $exception) {
            DB::rollBack();

            Log::error('Failed to store rest point checklist', [
                'error' => $exception->getMessage(),
                'trace' => $exception->getTraceAsString(),
                'rest_point_id' => $rest_point->id ?? null,
            ]);

            return redirect()
                ->back()
                ->withInput()
                ->with('error', __('messages.error_creating_rest_point') ?? 'Error saving rest point checklist.');
        }
    }

    /**
     * Display a completed checklist.
     */
    public function show(RestPoint $rest_point, RestPointChecklist $checklist): View|RedirectResponse
    {
        try {
            if ($checklist->rest_point_id !== $rest_point->id) {
                return redirect()
                    ->route('rest-points.index')
                    ->with('error', __('messages.error_loading_rest_points') ?? 'Checklist does not belong to this rest point.');
            }

            $checklist->load([
                'answers.item.category',
                'completedByUser',
                'restPoint',
            ]);

            return view('rest-points.checklists.show', [
                'restPoint' => $rest_point,
                'checklist' => $checklist,
            ]);
        } catch (Throwable $exception) {
            Log::error('Failed to display rest point checklist', [
                'error' => $exception->getMessage(),
                'trace' => $exception->getTraceAsString(),
                'rest_point_id' => $rest_point->id ?? null,
                'checklist_id' => $checklist->id ?? null,
            ]);

            return redirect()
                ->route('rest-points.index')
                ->with('error', __('messages.error_loading_rest_points') ?? 'Error displaying rest point checklist.');
        }
    }

    /**
     * Download a completed checklist as PDF.
     */
    public function pdf(Request $request, RestPoint $rest_point, RestPointChecklist $checklist)
    {
        try {
            if ($checklist->rest_point_id !== $rest_point->id) {
                return redirect()
                    ->route('rest-points.index')
                    ->with('error', __('messages.error_loading_rest_points') ?? 'Checklist does not belong to this rest point.');
            }

            // Load relationships for rendering
            $checklist->load([
                'answers.item.category',
                'completedByUser',
                'restPoint',
            ]);

            // Load active categories and items (structure of the checklist)
            $categories = RestPointChecklistCategory::where('is_active', true)
                ->with(['items' => function ($query) {
                    $query->where('is_active', true);
                }])
                ->orderBy('name')
                ->get();

            // Map answers by item ID for quick lookup
            $answersByItemId = [];
            foreach ($checklist->answers as $answer) {
                $answersByItemId[$answer->rest_points_checklist_item_id] = $answer;
            }

            $effectiveDate = $checklist->effective_inspection_date;
            $nextInspectionDue = $effectiveDate
                ? $effectiveDate->copy()->addMonthsNoOverflow(6)
                : null;

            $mapImageBase64 = $request->input('map_image');

            $pdf = Pdf::loadView('rest-points.checklists.pdf', [
                'restPoint' => $rest_point,
                'checklist' => $checklist,
                'categories' => $categories,
                'answersByItemId' => $answersByItemId,
                'effectiveInspectionDate' => $effectiveDate,
                'nextInspectionDue' => $nextInspectionDue,
                'mapImageBase64' => $mapImageBase64,
            ])->setPaper('a4', 'portrait');

            $fileName = sprintf(
                'rest-point-%d-checklist-%d-%s.pdf',
                $rest_point->id,
                $checklist->id,
                now()->format('Ymd_His')
            );

            return $pdf->download($fileName);
        } catch (Throwable $exception) {
            Log::error('Failed to generate rest point checklist PDF', [
                'error' => $exception->getMessage(),
                'trace' => $exception->getTraceAsString(),
                'rest_point_id' => $rest_point->id ?? null,
                'checklist_id' => $checklist->id ?? null,
            ]);

            return redirect()
                ->route('rest-points.show', $rest_point)
                ->with('error', __('messages.error_exporting_pdf') ?? 'Error generating checklist PDF.');
        }
    }
}


