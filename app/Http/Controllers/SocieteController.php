<?php

namespace App\Http\Controllers;

use App\Models\Societe;
use Illuminate\Http\Request;
use App\Imports\SociétésImport;
use Maatwebsite\Excel\Facades\Excel; // Assurez-vous d'importer la façade Excel
use App\Imports\SocietesImport;
use App\Models\Racine; // Assurez-vous que le modèle Racine est importé
class SocieteController extends Controller
{
  

   // Assurez-vous d'importer votre modèle Racine
// Dans votre contrôleur (par exemple SocieteController)

// public function dashboard()
// {
//     // Récupérer toutes les entrées de la table racines
//     $racines = Racine::all();


//     // Passer la variable à la vue
//     return view('dashboard', ['racines' => $racines]); 
// }

 
   public function getRubriquesTVA()
   {
       // Récupération des rubriques TVA
       $rubriques = Racine::select('categorie', 'Nom_racines','Taux','Num_racines')
   ->where('type','vente')
   ->having('Taux' , '>' , 0)
   ->get();
       // Vérifiez ce que retourne la requête
       // dd($rubriques); // Décommentez pour déboguer
   
// Organiser les rubriques par catégorie
$rubriquesParCategorie = [];
foreach ($rubriques as $rubrique) {
$rubriquesParCategorie[$rubrique->categorie]['rubriques'][] = [
    'Nom_racines' => $rubrique->Nom_racines,
    'Num_racines' => $rubrique->Num_racines,
    'Taux' => $rubrique->Taux,
];
}

// Passer les rubriques organisées à votre vue ou à votre réponse AJAX
return response()->json(['rubriques' => $rubriquesParCategorie]);
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
            'forme_juridique' => 'required|string',
            'siege_social' => 'required|string|max:255',
            'patente' => 'required|string|max:255',
            'rc' => 'required|string|max:255',
            'centre_rc' => 'required|string|max:255',
            'identifiant_fiscal' => 'required|string|max:8',
            'ice' => 'required|string|max:15',
            'date_creation' => 'required|date',
            'exercice_social_debut' => 'required|date',
            'exercice_social_fin' => 'required|date',
            'nature_activite' => 'required|string',
            'activite' => 'required|string|max:255',
            'assujettie_partielle_tva' => 'required|boolean',
            'prorata_de_deduction' => 'required|string|max:255',
            'regime_declaration' => 'required|string|max:255',
            'fait_generateur' => 'required|date',
            'rubrique_tva' => 'required|string',
            'designation' => 'required|string|max:255',
            'nombre_chiffre_compte' => 'required|integer',
            'modele_comptable' => 'required|string|max:255',
        ]);

        // Créer une nouvelle société avec les données validées
        Societe::create($validatedData);

        // Rediriger ou répondre à l'utilisateur
        return redirect()->back()->with('success', 'Société ajoutée avec succès !');
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
    
    // Retourner une réponse JSON pour indiquer que la suppression a réussi
    return response()->json(['success' => true, 'message' => 'Société supprimée avec succès.']);
}


   
   
    
  public function import(Request $request)
    {
        // Validation
        $request->validate([
            'file' => 'required|mimes:xlsx,xls,csv|max:2048',
        ]);

        try {
            Excel::import(new SocietesImport, $request->file('file'));
            return response()->json(['success' => true, 'message' => 'Importation réussie !']);
        } catch (\Exception $e) {
            Log::error('Erreur lors de l\'importation : ' . $e->getMessage());
            return response()->json(['success' => false, 'message' => 'Une erreur est survenue lors de l\'importation.']);
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