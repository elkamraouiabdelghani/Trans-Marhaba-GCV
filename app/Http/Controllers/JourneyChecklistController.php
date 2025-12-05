<?php

namespace App\Http\Controllers;

use App\Models\JourneyChecklist;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Illuminate\Support\Facades\Log;
use Throwable;

class JourneyChecklistController extends Controller
{
    /**
     * Display a listing of the journey checklist items.
     */
    public function index(): View|RedirectResponse
    {
        try {
            $items = JourneyChecklist::query()
                ->latest()
                ->paginate(20);

            return view('journeys.journeys-checklist.index', compact('items'));
        } catch (Throwable $exception) {
            Log::error('Failed to load journeys checklist index', [
                'error' => $exception->getMessage(),
                'trace' => $exception->getTraceAsString(),
            ]);

            return back()->with(
                'error',
                __('messages.error_loading_journeys_checklist') ?? 'Error loading journeys checklist.'
            );
        }
    }

    /**
     * Store a newly created journey checklist item.
     */
    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'donnees' => ['required', 'string', 'max:255'],
            'cirees_appreciation' => ['required', 'string', 'max:255'],
            'is_active' => ['nullable', 'boolean'],
        ]);

        $validated['is_active'] = $request->has('is_active') ? (bool) $request->input('is_active') : true;

        try {
            JourneyChecklist::create($validated);

            return redirect()
                ->route('journeys.journeys-checklist.index')
                ->with('success', __('messages.journey_checklist_item_created') ?? 'Journey checklist item created successfully.');
        } catch (Throwable $exception) {
            Log::error('Failed to create journeys checklist item', [
                'error' => $exception->getMessage(),
                'trace' => $exception->getTraceAsString(),
                'data' => $validated,
            ]);

            return back()
                ->withInput()
                ->with('error', __('messages.error_creating_journey_checklist_item') ?? 'Error creating journey checklist item.');
        }
    }

    /**
     * Update the specified journey checklist item.
     */
    public function update(Request $request, JourneyChecklist $journeys_checklist): RedirectResponse
    {
        $validated = $request->validate([
            'donnees' => ['required', 'string', 'max:255'],
            'cirees_appreciation' => ['required', 'string', 'max:255'],
            'is_active' => ['nullable', 'boolean'],
        ]);

        $validated['is_active'] = $request->has('is_active') ? (bool) $request->input('is_active') : true;

        try {
            $journeys_checklist->update($validated);

            return redirect()
                ->route('journeys.journeys-checklist.index')
                ->with('success', __('messages.journey_checklist_item_updated') ?? 'Journey checklist item updated successfully.');
        } catch (Throwable $exception) {
            Log::error('Failed to update journeys checklist item', [
                'error' => $exception->getMessage(),
                'trace' => $exception->getTraceAsString(),
                'item_id' => $journeys_checklist->id,
            ]);

            return back()
                ->withInput()
                ->with('error', __('messages.error_updating_journey_checklist_item') ?? 'Error updating journey checklist item.');
        }
    }

    /**
     * Remove the specified journey checklist item.
     */
    public function destroy(JourneyChecklist $journeys_checklist): RedirectResponse
    {
        try {
            $journeys_checklist->delete();

            return redirect()
                ->route('journeys.journeys-checklist.index')
                ->with('success', __('messages.journey_checklist_item_deleted') ?? 'Journey checklist item deleted successfully.');
        } catch (Throwable $exception) {
            Log::error('Failed to delete journeys checklist item', [
                'error' => $exception->getMessage(),
                'trace' => $exception->getTraceAsString(),
                'item_id' => $journeys_checklist->id ?? null,
            ]);

            return back()
                ->with('error', __('messages.error_deleting_journey_checklist_item') ?? 'Error deleting journey checklist item.');
        }
    }

    /**
     * Toggle the active status of a journey checklist item.
     */
    public function toggleStatus(Request $request, JourneyChecklist $journeys_checklist): \Illuminate\Http\JsonResponse
    {
        try {
            $request->validate([
                'is_active' => ['required'],
            ]);

            $isActive = filter_var($request->input('is_active'), FILTER_VALIDATE_BOOLEAN);

            $journeys_checklist->update([
                'is_active' => $isActive,
            ]);

            return response()->json([
                'success' => true,
                'message' => __('messages.journey_checklist_item_status_updated') ?? 'Journey checklist item status updated successfully.',
            ]);
        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
                'errors' => $e->errors(),
            ], 422);
        } catch (Throwable $exception) {
            Log::error('Failed to toggle journeys checklist item status', [
                'error' => $exception->getMessage(),
                'trace' => $exception->getTraceAsString(),
                'item_id' => $journeys_checklist->id ?? null,
            ]);

            return response()->json([
                'success' => false,
                'message' => __('messages.error_updating_journey_checklist_item') ?? 'Error updating journey checklist item status.',
            ], 500);
        }
    }
}


