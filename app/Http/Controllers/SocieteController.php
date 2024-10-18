<?php


namespace App\Http\Controllers;

use App\Models\Societe;
use Illuminate\Http\Request;




class SocieteController extends Controller
{
   

    public function index()
    {
        $societes = Societe::all();
        return view('dashboard', ['societes' => $societes->toJson()]);
    }
    
    public function getData()
    {
        // Récupérer toutes les sociétés et renvoyer en JSON
        $societes = Societe::all();
        return response()->json($societes);
    }
        
    public function store(Request $request)
    {
        // Valider les données du formulaire
        $validatedData = $request->validate([
            'raison_sociale' => 'required|string|max:255',
            'forme_juridique' => 'required|string|max:255',
            'siege_social' => 'required|string|max:255',
            'patente' => 'required|string|max:255',
            'rc' => 'required|string|max:255',
            'centre_rc' => 'required|string|max:255',
            'identifiant_fiscal' => 'required|string|max:255',
            'ice' => 'required|string|max:255',
            'assujettie_partielle_tva' => 'required|boolean',
            'prorata_de_deduction' => 'required|numeric',
            'exercice_social' => 'required|string|max:255',
            'date_creation' => 'required|date',
            'nature_activite' => 'required|string|max:255',
            'activite' => 'required|string|max:255',
            'regime_declaration' => 'required|string|max:255',
            'fait_generateur' => 'required|date',
            'rubrique_tva' => 'required|string|max:255',
            'designation' => 'required|string|max:255',
        ]);

        // Créer la société
        $societe = Societe::create($validatedData);

        // Retourner une réponse JSON
        return response()->json([
            'success' => true,
            'societe' => $societe,
        ]);
    }



    // public function store(Request $request)
    // {
    //     $request->validate([
    //         'raison_sociale' => 'required|string',
    //         'forme_juridique' => 'required|string',
    //         // Validez les autres champs ici
    //     ]);

    //     Societe::create($request->all());
    //     return redirect()->route('dashboard')->with('success', 'Société ajoutée avec succès.');
    // }

    // public function update(Request $request, $id)
    // {
    //     $societe = Societe::findOrFail($id);
    //     $societe->update($request->all());
    //     return redirect()->route('dashboard')->with('success', 'Société mise à jour avec succès.');
    // }
    public function update(Request $request, $id)
{
    // Valider les données
    $validatedData = $request->validate([
        'raison_sociale' => 'required|string|max:255',
        'forme_juridique' => 'required|string|max:255',
        'siege_social' => 'required|string|max:255',
        'patente' => 'required|string|max:255',
        'rc' => 'required|string|max:255',
        'centre_rc' => 'required|string|max:255',
        'identifiant_fiscal' => 'required|string|max:255',
        'ice' => 'required|string|max:255',
        'assujettie_partielle_tva' => 'required|boolean',
        'prorata_de_deduction' => 'required|numeric',
        'exercice_social' => 'required|string|max:255',
        'date_creation' => 'required|date',
        'nature_activite' => 'required|string|max:255',
        'activite' => 'required|string|max:255',
        'regime_declaration' => 'required|string|max:255',
        'fait_generateur' => 'required|date',
        'rubrique_tva' => 'required|string|max:255',
        'designation' => 'required|string|max:255',
    ]);

    $societe = Societe::findOrFail($id);
    $societe->update($validatedData); // Mettre à jour les données

    return response()->json([
        'success' => true,
        'societe' => $societe, // Renvoie les données mises à jour
    ]);
}

    
    public function destroy($id)
    {
        $societe = Societe::findOrFail($id);
        $societe->delete();
        return redirect()->route('dashboard')->with('success', 'Société supprimée avec succès.');
    }
}
