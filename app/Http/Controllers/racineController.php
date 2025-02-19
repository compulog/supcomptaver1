<?php


namespace App\Http\Controllers;

use App\Models\racine;
use Illuminate\Http\Request;

class racineController extends Controller
{
    // Afficher toutes les racines
    public function index()
    {
        $racines = racine::all();

        return view('racines.index', compact('racines'));
    }

    // Afficher le formulaire pour ajouter une nouvelle racine
    public function create()
    {
        return view('racines.create');

    }

    // Ajouter une nouvelle racine
    public function store(Request $request)
    {
        $request->validate([
            'type' => 'required|string|max:255',
            'categorie' => 'required|string|max:255',
            'Num_racines' => 'required|string|max:255',
            'Nom_racines' => 'required|string|max:255',
            'Taux' => 'required|numeric',
        ]);

        // Créer un nouvel enregistrement dans la table racines
        racine::create([
            'type' => $request->type,
            'categorie' => $request->categorie,
            'Num_racines' => $request->Num_racines,
            'Nom_racines' => $request->Nom_racines,
            'Taux' => $request->Taux,
        ]);

        return redirect()->route('racines.index')->with('success', 'Racine ajoutée avec succès');
    }

    // Afficher le formulaire d'édition d'une racine
    public function edit($id)
    {
        $racine = Racine::findOrFail($id);
        return view('racines.edit', compact('racine'));
    }

    // Mettre à jour une racine
    public function update(Request $request, $id)
    {
        $request->validate([
            'type' => 'required|string|max:255',
            'categorie' => 'required|string|max:255',
            'Num_racines' => 'required|string|max:255',
            'Nom_racines' => 'required|string|max:255',
            'Taux' => 'required|numeric',
        ]);

        $racine = Racine::findOrFail($id);
        $racine->update([
            'type' => $request->type,
            'categorie' => $request->categorie,
            'Num_racines' => $request->Num_racines,
            'Nom_racines' => $request->Nom_racines,
            'Taux' => $request->Taux,
        ]);

        return redirect()->route('racines.index')->with('success', 'Racine mise à jour avec succès');
    }

    // Supprimer une racine
    public function destroy($id)
    {
        $racine = racine::findOrFail($id);
        $racine->delete();

        return redirect()->route('racines.index')->with('success', 'Racine supprimée avec succès');
    }

}
