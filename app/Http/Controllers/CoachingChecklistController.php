<?php

namespace App\Http\Controllers;

use App\Models\CoachingChecklistCategory;
use App\Models\CoachingChecklistItem;
use App\Models\CoachingSession;
use App\Models\CoachingSessionChecklist;
use App\Models\CoachingSessionChecklistItemAnswer;
use App\Models\User;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;
use Throwable;

class CoachingChecklistController extends Controller
{
    /**
    * Show the checklist creation form for a coaching session.
    */
    public function create(CoachingSession $coachingCabine): RedirectResponse|View
    {
        // If checklist already exists, redirect to show
        if ($coachingCabine->checklist) {
            return redirect()
                ->route('coaching.checklists.show', [$coachingCabine, $coachingCabine->checklist])
                ->with('info', __('messages.coaching_checklist_already_exists') ?? 'A checklist already exists for this session.');
        }

        $categories = CoachingChecklistCategory::query()
            ->where('is_active', true)
            ->with(['items' => function ($q) {
                $q->where('is_active', true)
                    ->orderBy('sort_order')
                    ->orderBy('id');
            }])
            ->orderBy('sort_order')
            ->orderBy('id')
            ->get();

        if ($categories->isEmpty()) {
            return redirect()
                ->route('coaching-cabines.index')
                ->with('error', __('messages.coaching_checklist_no_template') ?? 'No checklist template defined.');
        }

        // Load driver with assigned vehicle
        $coachingCabine->load(['driver.assignedVehicle']);

        return view('coaching_cabines.checklists.create', [
            'session' => $coachingCabine,
            'categories' => $categories,
        ]);
    }

    /**
    * Store a completed checklist.
    */
    public function store(Request $request, CoachingSession $coachingCabine): RedirectResponse
    {
        if ($coachingCabine->checklist) {
            return redirect()
                ->route('coaching.checklists.show', [$coachingCabine, $coachingCabine->checklist])
                ->with('info', __('messages.coaching_checklist_already_exists') ?? 'A checklist already exists for this session.');
        }

        $validated = $request->validate([
            'answers' => ['required', 'array'],
            'answers.*.checked' => ['nullable', 'boolean'],
            'answers.*.comment' => ['nullable', 'string'],
            'meta' => ['nullable', 'array'],
            'meta.name' => ['nullable', 'string', 'max:255'],
            'meta.company' => ['nullable', 'string', 'max:255'],
            'meta.realized_date' => ['nullable', 'date'],
            'meta.vehicle_tractor_registration' => ['nullable', 'string', 'max:255'],
            'meta.vehicle_tanker_registration' => ['nullable', 'string', 'max:255'],
            'meta.test_alcohol_drug' => ['nullable', 'array'],
            'meta.test_alcohol_drug.alcohol' => ['nullable', 'string', 'max:255'],
            'meta.test_alcohol_drug.drugs' => ['nullable', 'string', 'max:255'],
            'meta.epi_control' => ['nullable', 'array'],
            'meta.epi_control.exists' => ['nullable', 'boolean'],
            'meta.adr_equipment_control' => ['nullable', 'array'],
            'meta.adr_equipment_control.exists' => ['nullable', 'boolean'],
            'meta.topics_covered' => ['nullable', 'array'],
            'meta.topics_covered.*' => ['nullable', 'string'],
            'meta.notes' => ['nullable', 'string'],
        ]);

        // Load active items to ensure validity
        $itemIds = array_keys($validated['answers']);
        $items = CoachingChecklistItem::whereIn('id', $itemIds)
            ->where('is_active', true)
            ->get()
            ->keyBy('id');

        if ($items->isEmpty()) {
            return back()->with('error', __('messages.coaching_checklist_no_items') ?? 'No checklist items to save.');
        }

        try {
            DB::beginTransaction();

            $meta = $validated['meta'] ?? [];

            $checklist = CoachingSessionChecklist::create([
                'coaching_session_id' => $coachingCabine->id,
                'completed_by' => Auth::id(),
                'completed_at' => now(),
                'status' => 'completed',
                'meta' => $meta,
            ]);

            foreach ($validated['answers'] as $itemId => $answer) {
                if (!isset($items[$itemId])) {
                    continue;
                }
                // Only create answer if checkbox is checked
                if (!empty($answer['checked'])) {
                    CoachingSessionChecklistItemAnswer::create([
                        'coaching_session_checklist_id' => $checklist->id,
                        'coaching_checklist_item_id' => $itemId,
                        'score' => $items[$itemId]->score ?? 1, // Use score from item template
                        'comment' => $answer['comment'] ?? null,
                    ]);
                }
            }

            DB::commit();

            return redirect()
                ->route('coaching.checklists.show', [$coachingCabine, $checklist])
                ->with('success', __('messages.coaching_checklist_saved') ?? 'Checklist saved.');
        } catch (Throwable $e) {
            DB::rollBack();

            report($e);
            return back()->with('error', __('messages.coaching_checklist_save_error') ?? 'Unable to save checklist.');
        }
    }

    /**
    * Show a checklist.
    */
    public function show(CoachingSession $coachingCabine, CoachingSessionChecklist $checklist): View
    {
        if ($checklist->coaching_session_id !== $coachingCabine->id) {
            abort(404);
        }
        $checklist->load([
            'answers.item.category',
            'session.driver',
            'session.flotte',
            'completedByUser',
        ]);

        return view('coaching_cabines.checklists.show', [
            'session' => $coachingCabine,
            'checklist' => $checklist,
        ]);
    }

    /**
    * Download checklist as PDF.
    */
    public function pdf(CoachingSession $coachingCabine, CoachingSessionChecklist $checklist)
    {
        if ($checklist->coaching_session_id !== $coachingCabine->id) {
            abort(404);
        }
        $checklist->load([
            'answers.item.category',
            'session.driver',
            'session.flotte',
            'completedByUser',
        ]);

        $pdf = Pdf::loadView('coaching_cabines.checklists.pdf', [
            'session' => $coachingCabine,
            'checklist' => $checklist,
        ])
            ->setPaper('a4', 'portrait')
            ->setOption('isHtml5ParserEnabled', true)
            ->setOption('isRemoteEnabled', true);

        $filename = 'coaching_checklist_' . $coachingCabine->id . '.pdf';

        return $pdf->download($filename);
    }
}

