<?php

namespace App\Http\Controllers;

use App\Models\FormationType;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Illuminate\Support\Facades\Log;

class FormationTypeController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        try {
            $formationTypes = FormationType::orderBy('name')->get();

            return view('formation-types.index', compact('formationTypes'));
        } catch (\Throwable $e) {
            Log::error('Failed to load formation types', [
                'error' => $e->getMessage(),
            ]);
            return redirect()->route('formation-types.index')
                ->with('error', __('messages.formation_type_create_error'));
        }
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        try {
            return view('formation-types.create');
        } catch (\Throwable $e) {
            Log::error('Failed to load creation form', [
                'error' => $e->getMessage(),
            ]);
            return redirect()->route('formation-types.index')
                ->with('error', __('messages.formation_type_create_error'));
        }
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255|unique:formation_types,name',
            'code' => 'required|string|max:255|unique:formation_types,code',
            'description' => 'nullable|string',
            'is_active' => 'sometimes|boolean',
            'obligatoire' => 'sometimes|boolean',
        ]);

        $validated['is_active'] = $request->has('is_active');
        $validated['obligatoire'] = $request->has('obligatoire');

        try {
            FormationType::create($validated);
        } catch (\Throwable $e) {
            Log::error('Failed to create formation type', [
                'error' => $e->getMessage(),
            ]);
            return back()
                ->withInput()
                ->with('error', __('messages.formation_type_create_error'));
        }

        return redirect()->route('formation-types.index')
            ->with('success', __('messages.formation_type_created'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(FormationType $formationType)
    {
        try {
            return view('formation-types.edit', compact('formationType'));
        } catch (\Throwable $e) {
            Log::error('Failed to load edit form', [
                'id' => $formationType->id,
                'error' => $e->getMessage(),
            ]);
            return redirect()->route('formation-types.index')
                ->with('error', __('messages.formation_type_update_error'));
        }
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, FormationType $formationType)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255|unique:formation_types,name,' . $formationType->id,
            'code' => 'required|string|max:255|unique:formation_types,code,' . $formationType->id,
            'description' => 'nullable|string',
            'is_active' => 'sometimes|boolean',
            'obligatoire' => 'sometimes|boolean',
        ]);

        $validated['is_active'] = $request->has('is_active');
        $validated['obligatoire'] = $request->has('obligatoire');

        try {
            $formationType->update($validated);
        } catch (\Throwable $e) {
            Log::error('Failed to update formation type', [
                'id' => $formationType->id,
                'error' => $e->getMessage(),
            ]);
            return back()
                ->withInput()
                ->with('error', __('messages.formation_type_update_error'));
        }

        return redirect()->route('formation-types.index')
            ->with('success', __('messages.formation_type_updated'));
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(FormationType $formationType)
    {
        try {
            $formationType->delete();
        } catch (\Throwable $e) {
            Log::error('Failed to delete formation type', [
                'id' => $formationType->id,
                'error' => $e->getMessage(),
            ]);
            return redirect()->route('formation-types.index')
                ->with('error', __('messages.formation_type_delete_error'));
        }

        return redirect()->route('formation-types.index')
            ->with('success', __('messages.formation_type_deleted'));
    }
}
