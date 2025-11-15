<?php

namespace App\Http\Controllers;

use App\Models\ChangementType;
use App\Models\PrincipaleCretaire;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;
use Throwable;

class PrincipaleCretaireController extends Controller
{
    public function index(Request $request): View|RedirectResponse
    {
        try {
            $query = PrincipaleCretaire::with('changementType')
                ->withCount('sousCretaires')
                ->latest();

            if ($request->filled('changement_type_id')) {
                $query->where('changement_type_id', $request->input('changement_type_id'));
            }

            $principaleCretaires = $query->paginate(15)->withQueryString();
            $changementTypes = ChangementType::orderBy('name')->get();

            return view('principale_cretaires.index', compact('principaleCretaires', 'changementTypes'));
        } catch (Throwable $th) {
            report($th);

            return back()->with('error', 'Impossible de charger les principales-cretaires.');
        }
    }

    public function create(): View|RedirectResponse
    {
        try {
            $changementTypes = ChangementType::orderBy('name')->get();

            return view('principale_cretaires.create', compact('changementTypes'));
        } catch (Throwable $th) {
            report($th);

            return back()->with('error', 'Impossible d\'afficher le formulaire de création.');
        }
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'changement_type_id' => ['required', 'exists:changement_types,id'],
            'name' => ['required', 'string', 'max:255'],
            'code' => [
                'required',
                'string',
                'max:50',
                Rule::unique('principale_cretaires')->where(function ($query) use ($request) {
                    return $query->where('changement_type_id', $request->input('changement_type_id'));
                }),
            ],
            'description' => ['nullable', 'string'],
            'is_active' => ['nullable', 'boolean'],
        ]);

        $validated['is_active'] = $request->has('is_active') ? $request->boolean('is_active') : false;

        try {
            $principaleCretaire = PrincipaleCretaire::create($validated);

            return redirect()
                ->route('principale-cretaires.show', $principaleCretaire)
                ->with('success', __('messages.principale_cretaires_created'));
        } catch (Throwable $th) {
            report($th);

            return back()->withInput()->with('error', __('messages.principale_cretaires_create_error'));
        }
    }

    public function show(PrincipaleCretaire $principaleCretaire): View|RedirectResponse
    {
        try {
            $principaleCretaire->load(['changementType', 'sousCretaires']);

            return view('changements.principale_cretaires.show', compact('principaleCretaire'));
        } catch (Throwable $th) {
            report($th);

            return back()->with('error', 'Impossible de charger le détail de la principale-cretaire.');
        }
    }

    public function edit(PrincipaleCretaire $principaleCretaire): View|RedirectResponse
    {
        try {
            $changementTypes = ChangementType::orderBy('name')->get();

            return view('principale_cretaires.edit', compact('principaleCretaire', 'changementTypes'));
        } catch (Throwable $th) {
            report($th);

            return back()->with('error', 'Impossible de charger le formulaire de modification.');
        }
    }

    public function update(Request $request, PrincipaleCretaire $principaleCretaire): RedirectResponse
    {
        $validated = $request->validate([
            'changement_type_id' => ['required', 'exists:changement_types,id'],
            'name' => ['required', 'string', 'max:255'],
            'code' => [
                'required',
                'string',
                'max:50',
                Rule::unique('principale_cretaires')
                    ->where(function ($query) use ($request) {
                        return $query->where('changement_type_id', $request->input('changement_type_id'));
                    })
                    ->ignore($principaleCretaire->id),
            ],
            'description' => ['nullable', 'string'],
            'is_active' => ['nullable', 'boolean'],
        ]);

        $validated['is_active'] = $request->has('is_active') ? $request->boolean('is_active') : false;

        try {
            $principaleCretaire->update($validated);

            // Redirect back to principale cretaire show page if form came from show page
            $formContext = $request->input('form_context');
            if ($formContext === 'edit_principale_cretaire_show_page') {
                return redirect()
                    ->route('principale-cretaires.show', $principaleCretaire)
                    ->with('success', 'Principale-cretaire mise à jour.');
            }

            // Redirect back to changement type show page if form came from changement type show page
            if ($formContext === 'edit_principale_cretaire_show') {
                return redirect()
                    ->route('changement-types.show', $validated['changement_type_id'])
                    ->with('success', 'Principale-cretaire mise à jour.');
            }

            return redirect()
                ->route('principale-cretaires.index')
                ->with('success', 'Principale-cretaire mise à jour.');
        } catch (Throwable $th) {
            report($th);

            return back()->withInput()->with('error', 'Erreur lors de la mise à jour de la principale-cretaire.');
        }
    }

    public function destroy(Request $request, PrincipaleCretaire $principaleCretaire): RedirectResponse
    {
        try {
            if ($principaleCretaire->sousCretaires()->exists()) {
                return back()->with('error', 'Impossible de supprimer une principale-cretaire liée à des sous-cretaires.');
            }

            $changementTypeId = $principaleCretaire->changement_type_id;
            $principaleCretaire->delete();

            // Redirect back to principale cretaire show page if form came from show page
            $formContext = $request->input('form_context');
            if ($formContext === 'delete_principale_cretaire_show_page') {
                return redirect()
                    ->route('changement-types.show', $changementTypeId)
                    ->with('success', 'Principale-cretaire supprimée.');
            }

            // Redirect back to changement type show page if form came from changement type show page
            if ($formContext === 'delete_principale_cretaire_show') {
                return redirect()
                    ->route('changement-types.show', $changementTypeId)
                    ->with('success', 'Principale-cretaire supprimée.');
            }

            return redirect()
                ->route('principale-cretaires.index')
                ->with('success', 'Principale-cretaire supprimée.');
        } catch (Throwable $th) {
            report($th);

            return back()->with('error', 'Erreur lors de la suppression de la principale-cretaire.');
        }
    }
}
