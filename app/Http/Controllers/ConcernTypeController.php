<?php

namespace App\Http\Controllers;

use App\Http\Requests\ConcernTypeRequest;
use App\Models\ConcernType;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class ConcernTypeController extends Controller
{
    public function index(): View
    {
        $concernTypes = ConcernType::withCount('driverConcerns')
            ->orderBy('name')
            ->get();

        return view('concern_types.index', [
            'concernTypes' => $concernTypes,
            'statusOptions' => [
                'high' => __('messages.high'),
                'medium' => __('messages.medium'),
                'low' => __('messages.low'),
            ],
        ]);
    }

    public function store(ConcernTypeRequest $request): RedirectResponse
    {
        ConcernType::create($request->validated());

        return redirect()
            ->route('concern-types.index')
            ->with('success', __('messages.concern_type_created'));
    }

    public function update(ConcernTypeRequest $request, ConcernType $concernType): RedirectResponse
    {
        $concernType->update($request->validated());

        return redirect()
            ->route('concern-types.index')
            ->with('success', __('messages.concern_type_updated'));
    }

    public function destroy(ConcernType $concernType): RedirectResponse
    {
        if ($concernType->driverConcerns()->exists()) {
            return redirect()
                ->route('concern-types.index')
                ->with('error', __('messages.concern_type_in_use'));
        }

        $concernType->delete();

        return redirect()
            ->route('concern-types.index')
            ->with('success', __('messages.concern_type_deleted'));
    }
}

