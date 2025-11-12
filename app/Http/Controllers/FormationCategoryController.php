<?php

namespace App\Http\Controllers;

use App\Http\Requests\FormationCategoryRequest;
use App\Models\FormationCategory;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Log;
use Illuminate\View\View;

class FormationCategoryController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(): View
    {
        try {
            $categories = FormationCategory::orderBy('name')->get();

            return view('formations.categories.index', compact('categories'));
        } catch (\Throwable $e) {
            Log::error('Failed to load formation categories', [
                'error' => $e->getMessage(),
            ]);

            return view('formations.categories.index', ['categories' => collect()])
                ->with('error', __('messages.formation_category_index_error'));
        }
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create(): RedirectResponse
    {
        return redirect()
            ->route('formation-categories.index')
            ->with('showCategoryModal', 'create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(FormationCategoryRequest $request): RedirectResponse
    {
        try {
            FormationCategory::create($request->validated());
        } catch (\Throwable $e) {
            Log::error('Failed to create formation category', [
                'payload' => $request->validated(),
                'error' => $e->getMessage(),
            ]);

            return back()
                ->withInput()
                ->with('error', __('messages.formation_category_create_error'));
        }

        return redirect()
            ->route('formation-categories.index')
            ->with('success', __('messages.formation_category_created'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(FormationCategory $formationCategory): RedirectResponse
    {
        return redirect()
            ->route('formation-categories.index')
            ->with('showCategoryModal', 'edit')
            ->with('editCategory', $formationCategory->only(['id', 'name', 'code']));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(FormationCategoryRequest $request, FormationCategory $formationCategory): RedirectResponse
    {
        try {
            $formationCategory->update($request->validated());
        } catch (\Throwable $e) {
            Log::error('Failed to update formation category', [
                'id' => $formationCategory->id,
                'payload' => $request->validated(),
                'error' => $e->getMessage(),
            ]);

            return back()
                ->withInput()
                ->with('error', __('messages.formation_category_update_error'));
        }

        return redirect()
            ->route('formation-categories.index')
            ->with('success', __('messages.formation_category_updated'));
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(FormationCategory $formationCategory): RedirectResponse
    {
        try {
            $formationCategory->delete();
        } catch (\Throwable $e) {
            Log::error('Failed to delete formation category', [
                'id' => $formationCategory->id,
                'error' => $e->getMessage(),
            ]);

            return redirect()
                ->route('formation-categories.index')
                ->with('error', __('messages.formation_category_delete_error'));
        }

        return redirect()
            ->route('formation-categories.index')
            ->with('success', __('messages.formation_category_deleted'));
    }
}

