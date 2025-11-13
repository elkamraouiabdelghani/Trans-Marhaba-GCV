<?php

namespace App\Http\Controllers;

use App\Http\Requests\OrganigramMemberRequest;
use App\Models\OrganigramMember;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class OrganigramMemberController extends Controller
{
    public function index()
    {
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
    }

    public function store(OrganigramMemberRequest $request)
    {
        OrganigramMember::create($request->validated());

        return redirect()
            ->route('organigram.index')
            ->with('success', __('messages.organigram_member_created'));
    }

    public function update(OrganigramMemberRequest $request, OrganigramMember $organigram)
    {
        $organigram->update([
            'name' => $request->validated()['name'],
            'updated_at' => now(),
        ]);

        // Increment revision
        $this->incrementRevision();

        return redirect()
            ->route('organigram.index')
            ->with('success', __('messages.organigram_member_updated'));
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

    public function destroy(OrganigramMember $organigram)
    {
        $organigram->delete();

        return redirect()
            ->route('organigram.index')
            ->with('success', __('messages.organigram_member_deleted'));
    }

    public function download()
    {
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
    }
}

