<?php

namespace App\Http\Controllers;

use App\Models\ChangementType;
use Illuminate\Http\Request;
use Throwable;

class ChangementTypeController extends Controller
{
    public function index()
    {
        try {
            $changementTypes = ChangementType::withCount('principaleCretaires')
                ->latest()
                ->paginate(15);

            return view('changements.changement_types.index', compact('changementTypes'));
        } catch (Throwable $th) {
            report($th);

            return back()->with('error', "Impossible de charger les types de changement pour le moment.");
        }
    }

    public function create()
    {
        try {
            return view('changements.changement_types.create');
        } catch (Throwable $th) {
            report($th);

            return back()->with('error', "Impossible d'afficher le formulaire de création.");
        }
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'is_active' => ['nullable', 'boolean'],
        ]);

        $validated['is_active'] = $request->boolean('is_active', true);

        try {
            $changementType = ChangementType::create($validated);

            return redirect()
                ->route('changement-types.show', $changementType)
                ->with('success', __('messages.changement_types_created'));
        } catch (Throwable $th) {
            report($th);

            return back()->withInput()->with('error', __('messages.changement_types_create_error'));
        }
    }

    public function show(ChangementType $changementType)
    {
        try {
            $changementType->load('principaleCretaires.sousCretaires');

            return view('changements.changement_types.show', compact('changementType'));
        } catch (Throwable $th) {
            report($th);

            return back()->with('error', 'Impossible de charger le détail du type de changement.');
        }
    }

    public function edit(ChangementType $changementType)
    {
        try {
            return view('changements.changement_types.edit', compact('changementType'));
        } catch (Throwable $th) {
            report($th);

            return back()->with('error', 'Impossible de charger le formulaire de modification.');
        }
    }

    public function update(Request $request, ChangementType $changementType)
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'is_active' => ['nullable', 'boolean'],
        ]);

        $validated['is_active'] = $request->has('is_active') ? $request->boolean('is_active') : false;

        try {
            $changementType->update($validated);

            // Redirect back to show page if form came from show page, otherwise to index
            $formContext = $request->input('form_context');
            if ($formContext === 'edit_changement_type_show') {
                return redirect()
                    ->route('changement-types.show', $changementType)
                    ->with('success', 'Type de changement mis à jour.');
            }

            return redirect()
                ->route('changement-types.index')
                ->with('success', 'Type de changement mis à jour.');
        } catch (Throwable $th) {
            report($th);

            return back()->withInput()->with('error', 'Erreur lors de la mise à jour du type de changement.');
        }
    }

    public function destroy(ChangementType $changementType)
    {
        try {
            if ($changementType->principaleCretaires()->exists()) {
                return back()->with('error', 'Impossible de supprimer un type lié à des principales-cretaires.');
            }

            $changementType->delete();

            return redirect()
                ->route('changement-types.index')
                ->with('success', 'Type de changement supprimé.');
        } catch (Throwable $th) {
            report($th);

            return back()->with('error', 'Erreur lors de la suppression du type de changement.');
        }
    }
}
