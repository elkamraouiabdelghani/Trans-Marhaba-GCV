<?php

namespace App\Http\Controllers;

use App\Http\Requests\OrganigramMemberRequest;
use App\Models\OrganigramMember;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\View\View;
use Throwable;

class OrganigramMemberController extends Controller
{
    public function index(): View|RedirectResponse
    {
        try {
            $members = OrganigramMember::query()
                ->orderBy('position')
                ->orderBy('name')
                ->get();

            $positions = OrganigramMember::POSITIONS;
            $takenPositions = $members->pluck('position')->unique()->all();
            $availablePositions = array_values(array_diff($positions, $takenPositions));

            return view('organigram.index', [
                'members' => $members,
                'positions' => $positions,
                'availablePositions' => $availablePositions,
            ]);
        } catch (Throwable $exception) {
            Log::error('Failed to load organigram index', [
                'error' => $exception->getMessage(),
                'trace' => $exception->getTraceAsString(),
            ]);

            return redirect()
                ->back()
                ->with('error', __('messages.error_loading_organigram') ?? 'Error loading organigram.');
        }
    }

    public function store(OrganigramMemberRequest $request): RedirectResponse
    {
        try {
            OrganigramMember::create($request->validated());

            return redirect()
                ->route('organigram.index')
                ->with('success', __('messages.organigram_member_created'));
        } catch (Throwable $exception) {
            Log::error('Failed to create organigram member', [
                'error' => $exception->getMessage(),
                'trace' => $exception->getTraceAsString(),
                'request_data' => $request->validated(),
            ]);

            return redirect()
                ->back()
                ->withInput()
                ->with('error', __('messages.error_creating_organigram_member') ?? 'Error creating organigram member.');
        }
    }

    public function update(OrganigramMemberRequest $request, OrganigramMember $organigram): RedirectResponse
    {
        try {
            $organigram->update([
                'name' => $request->validated()['name'],
                'updated_at' => now(),
            ]);

            // Increment revision
            $this->incrementRevision();

            return redirect()
                ->route('organigram.index')
                ->with('success', __('messages.organigram_member_updated'));
        } catch (Throwable $exception) {
            Log::error('Failed to update organigram member', [
                'error' => $exception->getMessage(),
                'trace' => $exception->getTraceAsString(),
                'organigram_id' => $organigram->id,
                'request_data' => $request->validated(),
            ]);

            return redirect()
                ->back()
                ->withInput()
                ->with('error', __('messages.error_updating_organigram_member') ?? 'Error updating organigram member.');
        }
    }

    /**
     * Get the current revision number
     */
    private function getRevision(): int
    {
        $setting = DB::table('organigram_settings')->first();
        return $setting ? $setting->revision : 0;
    }

    /**
     * Increment the revision number
     */
    private function incrementRevision(): void
    {
        DB::table('organigram_settings')
            ->where('id', 1)
            ->increment('revision');
    }

    public function destroy(OrganigramMember $organigram): RedirectResponse
    {
        try {
            $organigram->delete();

            return redirect()
                ->route('organigram.index')
                ->with('success', __('messages.organigram_member_deleted'));
        } catch (Throwable $exception) {
            Log::error('Failed to delete organigram member', [
                'error' => $exception->getMessage(),
                'trace' => $exception->getTraceAsString(),
                'organigram_id' => $organigram->id,
            ]);

            return redirect()
                ->back()
                ->with('error', __('messages.error_deleting_organigram_member') ?? 'Error deleting organigram member.');
        }
    }

    public function download()
    {
        try {
            $members = OrganigramMember::query()
                ->orderBy('position')
                ->orderBy('name')
                ->get()
                ->groupBy('position');

            $lastUpdateDate = OrganigramMember::query()
                ->max('updated_at');

            $revision = $this->getRevision();

            $pdf = Pdf::loadView('organigram.pdf', [
                'members' => $members,
                'lastUpdateDate' => $lastUpdateDate,
                'revision' => $revision,
            ])->setPaper('a3', 'landscape');

            return $pdf->download('organigramme.pdf');
        } catch (Throwable $exception) {
            Log::error('Failed to generate organigram PDF', [
                'error' => $exception->getMessage(),
                'trace' => $exception->getTraceAsString(),
            ]);

            return redirect()
                ->route('organigram.index')
                ->with('error', __('messages.error_exporting_pdf') ?? 'Error generating organigram PDF.');
        }
    }
}

