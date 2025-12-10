<?php

namespace App\Http\Controllers;

use App\Models\CoachingChecklistCategory;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Throwable;

class CoachingChecklistCategoryController extends Controller
{
    public function index()
    {
        try {
            $categories = CoachingChecklistCategory::withCount('items')
                ->latest()
                ->paginate(15);

            return view('coaching_cabines.checklists.categories.index', compact('categories'));
        } catch (Throwable $th) {
            report($th);

            return back()->with('error', __('messages.error_loading_checklist_categories') ?? 'Error loading checklist categories.');
        }
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'is_active' => ['nullable', 'boolean'],
        ]);

        $validated['is_active'] = $request->has('is_active') ? $request->boolean('is_active') : false;

        try {
            $category = CoachingChecklistCategory::create($validated);

            return redirect()
                ->route('coaching.checklists.categories.show', $category)
                ->with('success', __('messages.checklist_category_created') ?? 'Checklist category created successfully.');
        } catch (Throwable $th) {
            report($th);

            return back()->withInput()->with('error', __('messages.error_creating_checklist_category') ?? 'Error creating checklist category.');
        }
    }

    public function show(CoachingChecklistCategory $category)
    {
        try {
            $category->load('items');

            return view('coaching_cabines.checklists.categories.show', compact('category'));
        } catch (Throwable $th) {
            report($th);

            return back()->with('error', __('messages.error_loading_checklist_category') ?? 'Error loading checklist category.');
        }
    }

    public function update(Request $request, CoachingChecklistCategory $category)
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'is_active' => ['nullable', 'boolean'],
        ]);

        $validated['is_active'] = $request->has('is_active') ? $request->boolean('is_active') : false;

        try {
            $category->update($validated);

            $formContext = $request->input('form_context');
            if ($formContext === 'edit_category_show') {
                return redirect()
                    ->route('coaching.checklists.categories.show', $category)
                    ->with('success', __('messages.checklist_category_updated') ?? 'Checklist category updated successfully.');
            }

            return redirect()
                ->route('coaching.checklists.categories.index')
                ->with('success', __('messages.checklist_category_updated') ?? 'Checklist category updated successfully.');
        } catch (Throwable $th) {
            report($th);

            return back()->withInput()->with('error', __('messages.error_updating_checklist_category') ?? 'Error updating checklist category.');
        }
    }

    public function destroy(Request $request, CoachingChecklistCategory $category)
    {
        try {
            // Delete all items and their answers first
            $items = $category->items()->get();
            
            foreach ($items as $item) {
                // Delete all answers for this item
                $item->answers()->delete();
                // Delete the item
                $item->delete();
            }

            // Now delete the category
            $category->delete();

            return redirect()
                ->route('coaching.checklists.categories.index')
                ->with('success', __('messages.checklist_category_deleted') ?? 'Checklist category deleted successfully.');
        } catch (Throwable $th) {
            report($th);

            return back()->with('error', __('messages.error_deleting_checklist_category') ?? 'Error deleting checklist category.');
        }
    }

    public function toggleStatus(Request $request, CoachingChecklistCategory $category): \Illuminate\Http\JsonResponse
    {
        try {
            $request->validate([
                'is_active' => ['required'],
            ]);

            // Convert to boolean - handle both boolean and string values
            $isActive = filter_var($request->input('is_active'), FILTER_VALIDATE_BOOLEAN);

            $category->update(['is_active' => $isActive]);

            return response()->json([
                'success' => true,
                'message' => __('messages.checklist_category_status_updated') ?? 'Category status updated successfully.',
            ]);
        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
                'errors' => $e->errors(),
            ], 422);
        } catch (Throwable $th) {
            report($th);

            return response()->json([
                'success' => false,
                'message' => __('messages.error_updating_checklist_category') ?? 'Error updating category status.',
            ], 500);
        }
    }
}

