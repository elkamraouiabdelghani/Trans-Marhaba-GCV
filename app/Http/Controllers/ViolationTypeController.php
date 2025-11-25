<?php

namespace App\Http\Controllers;

use App\Models\ViolationType;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Throwable;

class ViolationTypeController extends Controller
{
    public function index(): View|RedirectResponse
    {
        try {
            $violationTypes = ViolationType::withCount('violations')
                ->latest()
                ->paginate(15);

            return view('violations.violation_types.index', compact('violationTypes'));
        } catch (Throwable $th) {
            report($th);

            return back()->with('error', __('messages.violation_types_load_error'));
        }
    }

    public function create(): RedirectResponse
    {
        // Redirect to index page where the create modal is available
        return redirect()->route('violation-types.index');
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255', 'unique:violation_types,name'],
            'description' => ['nullable', 'string'],
            'is_active' => ['nullable', 'boolean'],
        ]);

        $validated['is_active'] = $request->boolean('is_active', true);

        try {
            $violationType = ViolationType::create($validated);

            return redirect()
                ->route('violation-types.index')
                ->with('success', __('messages.violation_type_created'));
        } catch (Throwable $th) {
            report($th);

            return back()->withInput()->with('error', __('messages.violation_type_create_error'));
        }
    }

    public function show(ViolationType $violationType): RedirectResponse
    {
        // Redirect to index page
        return redirect()->route('violation-types.index');
    }

    public function edit(ViolationType $violationType): RedirectResponse
    {
        // Redirect to index page where the edit modal is available
        return redirect()->route('violation-types.index');
    }

    public function update(Request $request, ViolationType $violationType): RedirectResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255', 'unique:violation_types,name,' . $violationType->id],
            'description' => ['nullable', 'string'],
            'is_active' => ['nullable', 'boolean'],
        ]);

        $validated['is_active'] = $request->has('is_active') ? $request->boolean('is_active') : false;

        try {
            $violationType->update($validated);

            return redirect()
                ->route('violation-types.index')
                ->with('success', __('messages.violation_type_updated'));
        } catch (Throwable $th) {
            report($th);

            return back()->withInput()->with('error', __('messages.violation_type_update_error'));
        }
    }

    public function destroy(ViolationType $violationType): RedirectResponse
    {
        try {
            if ($violationType->violations()->exists()) {
                return back()->with('error', __('messages.violation_type_delete_error_has_violations'));
            }

            $violationType->delete();

            return redirect()
                ->route('violation-types.index')
                ->with('success', __('messages.violation_type_deleted'));
        } catch (Throwable $th) {
            report($th);

            return back()->with('error', __('messages.violation_type_delete_error'));
        }
    }
}
