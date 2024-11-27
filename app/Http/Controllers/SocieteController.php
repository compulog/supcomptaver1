<?php

namespace App\Http\Controllers;
use Maatwebsite\Excel\Facades\Excel;

use App\Models\Societe;
use Illuminate\Http\Request;
use App\Imports\SociétésImport;
use App\Imports\SocietesImport;
use App\Models\Racine; // Assurez-vous que le modèle Racine est importé
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;  // Pour loguer les erreurs
class SocieteController extends Controller
{
 
  public function deleteSelected(Request $request)
{
    try {
        $ids = $request->input('ids'); // Récupère les IDs des sociétés à supprimer
        
        // Vérifier si des IDs ont été envoyés
        if (empty($ids)) {
            return response()->json(['error' => 'Aucun ID fourni pour la suppression.'], 400);
        }

        // Suppression des sociétés par leurs IDs
        Societe::whereIn('id', $ids)->delete();

        return response()->json(['message' => 'Sociétés supprimées avec succès.']);
    } catch (\Exception $e) {
        // Retourne l'erreur avec le message d'exception
        return response()->json(['error' => 'Une erreur est survenue lors de la suppression: ' . $e->getMessage()], 500);
    }
}

    
    
  // Vérification du mot de passe de l'utilisateur
  public function checkPassword(Request $request)
  {
      // Valider que le mot de passe est bien envoyé
      $request->validate([
          'password' => 'required|string',
      ]);

      // Vérifier si le mot de passe correspond à celui de l'utilisateur actuellement connecté
      $user = Auth::user();

      if (Hash::check($request->password, $user->password)) {
          // Si le mot de passe est correct, retourner une réponse JSON avec succès
          return response()->json(['success' => true]);
      }

      // Si le mot de passe est incorrect, retourner une erreur
      return response()->json(['success' => false, 'message' => 'Mot de passe incorrect.'], 403);
  }
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
   ->having('Taux' , '>=' , 0)
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
            'assujettie_partielle_tva' => 'nullable|boolean',
            'prorata_de_deduction' => 'nullable|string|max:255',
            'regime_declaration' => 'required|string|max:255',
            'fait_generateur' => 'required|string',
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

  // Fonction pour afficher le formulaire
  public function showImportForm()
  {
      return view('import'); // La vue contenant le formulaire d'importation
  }

  // Fonction pour gérer l'importation du fichier
 
  public function import(Request $request)
    {
        // Valider le fichier importé
        $validated = $request->validate([
            'file' => 'required|file|mimes:xlsx,csv',
        ]);

        $file = $request->file('file');
        
        // Création d'un tableau de correspondances basé sur l'entrée de l'utilisateur
        $mappings = [
            'raison_sociale' => $request->input('raison_sociale'),
            'forme_juridique' => $request->input('forme_juridique'),
            'siege_social' => $request->input('siege_social'),
            'patente' => $request->input('patente'),
            'rc' => $request->input('rc'),
            'centre_rc' => $request->input('centre_rc'),
            'identifiant_fiscal' => $request->input('identifiant_fiscal'),
            'ice' => $request->input('ice'),
            'date_creation' => $request->input('date_creation'),
            'exercice_social_debut' => $request->input('exercice_social_debut'),
            'exercice_social_fin' => $request->input('exercice_social_fin'),
            'modele_comptable' => $request->input('modele_comptable'),
            'nombre_chiffre_compte' => $request->input('nombre_chiffre_compte'),
            'nature_activite' => $request->input('nature_activite'),
            'activite' => $request->input('activite'),
            'assujettie_partielle_tva' => $request->input('assujettie_partielle_tva'),
            'prorata_de_deduction' => $request->input('prorata_de_deduction'),
            'regime_declaration' => $request->input('regime_declaration'),
            'fait_generateur' => $request->input('fait_generateur'),
            'rubrique_tva' => $request->input('rubrique_tva'),
            'designation' => $request->input('designation'),
        ];
        
        // Traitement avec une classe d'importation personnalisée
        $import = new SocietesImport($mappings);
        
        // Importer les données
        Excel::import($import, $file);
        
        return redirect()->back()->with('success', 'Sociétés importées avec succès');
    }



// Autres méthodes du contrôleur (à conserver

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