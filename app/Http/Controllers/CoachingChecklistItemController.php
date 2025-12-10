<?php

namespace App\Http\Controllers;

use App\Models\CoachingChecklistItem;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Throwable;

class CoachingChecklistItemController extends Controller
{
    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'coaching_checklist_category_id' => ['required', 'exists:coaching_checklist_categories,id'],
            'label' => ['required', 'string', 'max:255'],
            'score' => ['required', 'integer', 'min:1'],
            'is_active' => ['nullable', 'boolean'],
        ]);

        $validated['is_active'] = $request->has('is_active') ? $request->boolean('is_active') : false;

        try {
            $item = CoachingChecklistItem::create($validated);

            return redirect()
                ->route('coaching.checklists.categories.show', $validated['coaching_checklist_category_id'])
                ->with('success', __('messages.checklist_item_created') ?? 'Checklist item created successfully.');
        } catch (Throwable $th) {
            report($th);

            return back()->withInput()->with('error', __('messages.error_creating_checklist_item') ?? 'Error creating checklist item.');
        }
    }

    public function update(Request $request, CoachingChecklistItem $item): RedirectResponse
    {
        $validated = $request->validate([
            'coaching_checklist_category_id' => ['required', 'exists:coaching_checklist_categories,id'],
            'label' => ['required', 'string', 'max:255'],
            'score' => ['required', 'integer', 'min:1'],
            'is_active' => ['nullable', 'boolean'],
        ]);

        $validated['is_active'] = $request->has('is_active') ? $request->boolean('is_active') : false;

        try {
            $item->update($validated);

            return redirect()
                ->route('coaching.checklists.categories.show', $validated['coaching_checklist_category_id'])
                ->with('success', __('messages.checklist_item_updated') ?? 'Checklist item updated successfully.');
        } catch (Throwable $th) {
            report($th);

            return back()->withInput()->with('error', __('messages.error_updating_checklist_item') ?? 'Error updating checklist item.');
        }
    }

    public function destroy(Request $request, CoachingChecklistItem $item): RedirectResponse
    {
        try {
            // Get category ID before deleting the item
            $categoryId = $item->coaching_checklist_category_id;

            // Use transaction to ensure data integrity
            DB::transaction(function () use ($item) {
                // Delete all answers for this item first
                $item->answers()->delete();

                // Now delete the item
                $item->delete();
            });

            return redirect()
                ->route('coaching.checklists.categories.show', $categoryId)
                ->with('success', __('messages.checklist_item_deleted') ?? 'Checklist item deleted successfully.');
        } catch (Throwable $th) {
            report($th);

            return back()->with('error', __('messages.error_deleting_checklist_item') ?? 'Error deleting checklist item.');
        }
    }
}

