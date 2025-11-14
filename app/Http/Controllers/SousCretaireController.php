<?php

namespace App\Http\Controllers;

use App\Models\PrincipaleCretaire;
use App\Models\SousCretaire;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Throwable;

class SousCretaireController extends Controller
{
    public function index(Request $request): View|RedirectResponse
    {
        try {
            $query = SousCretaire::with('principaleCretaire.changementType')
                ->latest();

            if ($request->filled('principale_cretaire_id')) {
                $query->where('principale_cretaire_id', $request->input('principale_cretaire_id'));
            }

            $sousCretaires = $query->paginate(15)->withQueryString();
            $principaleCretaires = PrincipaleCretaire::with('changementType')
                ->orderBy('name')
                ->get();

            return view('sous_cretaires.index', compact('sousCretaires', 'principaleCretaires'));
        } catch (Throwable $th) {
            report($th);

            return back()->with('error', 'Impossible de charger les sous-cretaires.');
        }
    }

    public function create(): View|RedirectResponse
    {
        try {
            $principaleCretaires = PrincipaleCretaire::with('changementType')
                ->orderBy('name')
                ->get();

            return view('sous_cretaires.create', compact('principaleCretaires'));
        } catch (Throwable $th) {
            report($th);

            return back()->with('error', 'Impossible d\'afficher le formulaire de création.');
        }
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'principale_cretaire_id' => ['required', 'exists:principale_cretaires,id'],
            'name' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'is_active' => ['nullable', 'boolean'],
        ]);

        $validated['is_active'] = $request->has('is_active') ? $request->boolean('is_active') : false;

        try {
            $sousCretaire = SousCretaire::create($validated);

            // Redirect back to principale cretaire show page if form came from show page
            $formContext = $request->input('form_context');
            if ($formContext === 'create_sous_cretaire_show') {
                return redirect()
                    ->route('principale-cretaires.show', $validated['principale_cretaire_id'])
                    ->with('success', 'Sous-cretaire créé avec succès.');
            }

            return redirect()
                ->route('sous-cretaires.index')
                ->with('success', 'Sous-cretaire créé avec succès.');
        } catch (Throwable $th) {
            report($th);

            return back()->withInput()->with('error', 'Erreur lors de la création du sous-cretaire.');
        }
    }

    public function show(SousCretaire $sousCretaire): View|RedirectResponse
    {
        try {
            $sousCretaire->load('principaleCretaire.changementType');

            return view('sous_cretaires.show', compact('sousCretaire'));
        } catch (Throwable $th) {
            report($th);

            return back()->with('error', 'Impossible de charger le détail du sous-cretaire.');
        }
    }

    public function edit(SousCretaire $sousCretaire): View|RedirectResponse
    {
        try {
            $principaleCretaires = PrincipaleCretaire::with('changementType')
                ->orderBy('name')
                ->get();

            return view('sous_cretaires.edit', compact('sousCretaire', 'principaleCretaires'));
        } catch (Throwable $th) {
            report($th);

            return back()->with('error', 'Impossible de charger le formulaire de modification.');
        }
    }

    public function update(Request $request, SousCretaire $sousCretaire): RedirectResponse
    {
        $validated = $request->validate([
            'principale_cretaire_id' => ['required', 'exists:principale_cretaires,id'],
            'name' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'is_active' => ['nullable', 'boolean'],
        ]);

        $validated['is_active'] = $request->has('is_active') ? $request->boolean('is_active') : false;

        try {
            $sousCretaire->update($validated);

            // Redirect back to principale cretaire show page if form came from show page
            $formContext = $request->input('form_context');
            if ($formContext === 'edit_sous_cretaire_show') {
                return redirect()
                    ->route('principale-cretaires.show', $validated['principale_cretaire_id'])
                    ->with('success', 'Sous-cretaire mis à jour.');
            }

            return redirect()
                ->route('sous-cretaires.index')
                ->with('success', 'Sous-cretaire mis à jour.');
        } catch (Throwable $th) {
            report($th);

            return back()->withInput()->with('error', 'Erreur lors de la mise à jour du sous-cretaire.');
        }
    }

    public function destroy(Request $request, SousCretaire $sousCretaire): RedirectResponse
    {
        try {
            $principaleCretaireId = $sousCretaire->principale_cretaire_id;
            $sousCretaire->delete();

            // Redirect back to principale cretaire show page if form came from show page
            $formContext = $request->input('form_context');
            if ($formContext === 'delete_sous_cretaire_show') {
                return redirect()
                    ->route('principale-cretaires.show', $principaleCretaireId)
                    ->with('success', 'Sous-cretaire supprimé.');
            }

            return redirect()
                ->route('sous-cretaires.index')
                ->with('success', 'Sous-cretaire supprimé.');
        } catch (Throwable $th) {
            report($th);

            return back()->with('error', 'Erreur lors de la suppression du sous-cretaire.');
        }
    }
}
