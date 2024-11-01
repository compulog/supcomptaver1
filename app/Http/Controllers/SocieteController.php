<?php

namespace App\Http\Controllers;

use App\Models\Societe;
use Illuminate\Http\Request;
use App\Imports\SociétésImport;
use Maatwebsite\Excel\Facades\Excel; // Assurez-vous d'importer la façade Excel
use App\Imports\SocietesImport;
use App\Models\Racine; 
class SocieteController extends Controller
{

   // Assurez-vous d'importer votre modèle Racine

   public function getRubriquesTVA()
   {
       // Récupération des rubriques TVA
       $rubriques = Racine::where('type', 'achat')->pluck('Nom_racines', 'id');
   
       // Vérifiez ce que retourne la requête
       // dd($rubriques); // Décommentez pour déboguer
   
       return response()->json($rubriques);
   }
   
   

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
            'modele_comptable' => 'nullable|string|max:255', // Nouveau champ ajouté
        ]);

        // Créer la société
        $societe = Societe::create($validatedData);

        // Retourner une réponse JSON
        return response()->json([
            'success' => true,
            'societe' => $societe,
        ]);
    }

  // Mettre à jour une société
  

 
public function update(Request $request, $id)
{
    // Validation des données
    $request->validate([
        'raison_sociale' => 'required|string|max:255',
        'siege_social' => 'nullable|string|max:255',
        'ice' => 'required|string|max:15',
        'rc' => 'required|string|max:255',
        'identifiant_fiscal' => 'required|string|max:255',
        'patente' => 'nullable|string|max:255',
        'centre_rc' => 'nullable|string|max:255',
        'forme_juridique' => 'nullable|string|max:255',
        'exercice_social_debut' => 'nullable|date',
        'exercice_social_fin' => 'nullable|date',
        'date_creation' => 'nullable|date',
        'assujettie_partielle_tva' => 'nullable|string|max:255',
        'prorata_de_deduction' => 'nullable|string|max:255',
        'nature_activite' => 'nullable|string|max:255',
        'activite' => 'nullable|string|max:255',
        'regime_declaration' => 'nullable|string|max:255',
        'fait_generateur' => 'nullable|string|max:255',
        'rubrique_tva' => 'nullable|string|max:255',
        'designation' => 'nullable|string|max:255',
        'nombre_chiffre_compte' => 'nullable|integer',
        'modele_comptable' => 'required|string|max:255',
    ]);

     // Trouver la société par ID
     $societe = Societe::findOrFail($id);

     // Mettre à jour la société
     $societe->update($request->all());
 
     // Retourner une réponse JSON
     return response()->json(['message' => 'Société modifiée avec succès.', 'societe' => $societe]);
}


  

    public function destroy($id)
    {
        $societe = Societe::findOrFail($id);
        $societe->delete();
        return redirect()->route('dashboard')->with('success', 'Société supprimée avec succès.');
    }

   
   
    // Dans votre méthode d'importation
    public function import(Request $request)
    {
        $request->validate([
            'file' => 'required|file|mimes:xlsx,csv,xls',
        ]);
    
        try {
            Excel::import(new SocietesImport, $request->file('file'));
            return response()->json(['success' => true, 'message' => 'Sociétés importées avec succès !']);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => 'Erreur lors de l\'importation : ' . $e->getMessage()], 500);
        }
    }
    
    
    
    

    public function show($id)
    {
        $societe = Societe::find($id);
    
        if (!$societe) {
            return response()->json(['error' => 'Société non trouvée'], 404);
        }
    
        return response()->json($societe);
    }

    // Dans SocieteController.php
public function edit($id)
{
    $societe = Societe::findOrFail($id); // Recherchez la société par son ID
    return response()->json($societe); // Retourne les données sous forme JSON
}

}