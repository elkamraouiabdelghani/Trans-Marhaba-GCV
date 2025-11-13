<?php

namespace App\Http\Controllers;

use App\Http\Requests\ConcernTypeRequest;
use App\Models\ConcernType;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;
use Throwable;

class ConcernTypeController extends Controller
{
    public function index()
    {
        try {
            $concernTypes = ConcernType::withCount('driverConcerns')
                ->orderBy('name')
                ->get();

            return view('concerns.concern_types.index', [
                'concernTypes' => $concernTypes,
                'statusOptions' => [
                    'high' => __('messages.high'),
                    'medium' => __('messages.medium'),
                    'low' => __('messages.low'),
                ],
            ]);
        } catch (Throwable $exception) {
            report($exception);

            return redirect()
                ->route('dashboard')
                ->with('error', __('messages.concern_type_index_error'));
        }
    }

    public function store(ConcernTypeRequest $request)
    {
        try {
            ConcernType::create($request->validated());

            return redirect()
                ->route('concerns.concern-types.index')
                ->with('success', __('messages.concern_type_created'));
        } catch (Throwable $exception) {
            report($exception);

            return redirect()
                ->route('concerns.concern-types.index')
                ->with('error', __('messages.concern_type_create_error'));
        }
    }

    public function update(ConcernTypeRequest $request, ConcernType $concernType)
    {
        try {
            $concernType->update($request->validated());

            return redirect()
                ->route('concerns.concern-types.index')
                ->with('success', __('messages.concern_type_updated'));
        } catch (Throwable $exception) {
            report($exception);

            return redirect()
                ->route('concerns.concern-types.index')
                ->with('error', __('messages.concern_type_update_error'));
        }
    }

    public function destroy(ConcernType $concernType)
    {
        try {
            if ($concernType->driverConcerns()->exists()) {
                return redirect()
                    ->route('concerns.concern-types.index')
                    ->with('error', __('messages.concern_type_in_use'));
            }

            $concernType->delete();

            return redirect()
                ->route('concerns.concern-types.index')
                ->with('success', __('messages.concern_type_deleted'));
        } catch (Throwable $exception) {
            report($exception);

            return redirect()
                ->route('concerns.concern-types.index')
                ->with('error', __('messages.concern_type_delete_error'));
        }
    }
}

