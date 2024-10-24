<?php

namespace App\Http\Controllers;

use App\Models\Societe;
use Illuminate\Http\Request;
use App\Imports\SociétésImport;
use Maatwebsite\Excel\Facades\Excel; // Assurez-vous d'importer la façade Excel

class SocieteController extends Controller
{
    public function index()
    {
        $societes = Societe::all(); // Changer 'Societes' en 'Societe'
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
            'nombre_chiffre_compte' => 'required|numeric', // Nouveau champ
            'exercice_social_debut' => 'required|date', // Nouveau champ
            'exercice_social_fin' => 'required|date', // Nouveau champ
            'date_creation' => 'required|date',
            'nature_activite' => 'nullable|string|max:255',
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

    public function update(Request $request, $id)
    {
        // Validation des données
        $validatedData = $request->validate([
            'raison_sociale' => 'required|string|max:255',
            'ice' => 'required|string|max:15',
            'rc' => 'required|string|max:50',
            'identifiant_fiscal' => 'required|string|max:50',
            'patente' => 'nullable|string|max:50',
            'centre_rc' => 'nullable|string|max:50',
            'forme_juridique' => 'nullable|string|max:50',
            'chiffre_compte' => 'required|integer', // Nouveau champ
            'exercice_social_debut' => 'required|date', // Nouveau champ
            'exercice_social_fin' => 'required|date', // Nouveau champ
            'date_creation' => 'nullable|date',
            'assujettie_partielle_tva' => 'nullable|boolean',
            'prorata_de_deduction' => 'nullable|numeric',
            'nature_activite' => 'nullable|string|max:50',
            'activite' => 'nullable|string|max:50',
            'regime_declaration' => 'nullable|string|max:50',
            'fait_generateur' => 'nullable|date',
            'rubrique_tva' => 'nullable|string|max:50',
            'designation' => 'nullable|string|max:50',
        ]);

        // Trouver la société par ID
        $societe = Societe::findOrFail($id);

        // Mettre à jour les données de la société
        $societe->update($validatedData);

        return response()->json(['message' => 'Société mise à jour avec succès!', 'societe' => $societe], 200);
    }

    public function destroy($id)
    {
        $societe = Societe::findOrFail($id);
        $societe->delete();
        return redirect()->route('dashboard')->with('success', 'Société supprimée avec succès.');
    }

    public function import(Request $request)
    {
        // Validation des données d'entrée
        $request->validate([
            'file' => 'required|mimes:xlsx,xls,csv',
            'colonne_nom_entreprise' => 'required|string',
            'colonne_forme_juridique' => 'required|string',
            'colonne_siege_social' => 'required|string',
            // Ajoutez des règles de validation pour les autres colonnes si nécessaire
        ]);

        // Récupérer le fichier
        $file = $request->file('file');

        // Importer les données
        Excel::import(new SociétésImport($request->all()), $file);

        return redirect()->back()->with('success', 'Sociétés importées avec succès.');
    }

    public function show($id)
    {
        $societe = Societe::find($id);
    
        if (!$societe) {
            return response()->json(['error' => 'Société non trouvée'], 404);
        }
    
        return response()->json($societe);
    }
}
