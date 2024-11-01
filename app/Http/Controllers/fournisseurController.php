<?php

namespace App\Http\Controllers;
use App\Imports\FournisseurImport;

use App\Models\Fournisseur;
use App\Models\Racine;
use App\Models\PlanComptable;

use Illuminate\Http\Request;

use Illuminate\Support\Facades\Validator;
use Maatwebsite\Excel\Facades\Excel;


class FournisseurController extends Controller
{
    public function index()
    {
        return view('fournisseurs'); // Assurez-vous que cela correspond à votre vue
    }

    public function show($id)
    {
        $fournisseur = Fournisseur::findOrFail($id);
        return response()->json($fournisseur, 200);
    }

    public function getData()
    {
        $fournisseurs = Fournisseur::all();
        return response()->json($fournisseurs);
    }

    public function store(Request $request)
    {
        $validatedData = $request->validate([
            'compte' => 'required|string|max:255',
            'intitule' => 'required|string|max:255',
            'identifiant_fiscal' => 'required|string|max:255',
            'ICE' => 'required|string|max:255',
            'nature_operation' => 'required|string|max:255',
            'rubrique_tva' => 'required|string|max:255',
            'designation' => 'nullable|string|max:255',
            'contre_partie' => 'nullable|string|max:255',
        ]);

        $fournisseur = Fournisseur::create($validatedData);

        return response()->json([
            'success' => true,
            'fournisseur' => $fournisseur,
        ]);
    }
 // Modifier un fournisseur
 // Méthode pour afficher le formulaire d'édition
 public function edit($id)
 {
 $fournisseur = Fournisseur::findOrFail($id);
return response()->json($fournisseur);

     
 }

 // Méthode pour mettre à jour le fournisseur
 public function update(Request $request, $id)
 {
     // Validation des données
     $validator = Validator::make($request->all(), [
         'compte' => 'required|string|max:255',
         'intitule' => 'required|string|max:255',
         'identifiant_fiscal' => 'required|string|max:255',
         'ICE' => 'required|string|max:15',
         'nature_operation' => 'required|string',
         'rubrique_tva' => 'required|string',
         'designation' => 'required|string|max:255',
         'contre_partie' => 'required|string|max:255',
     ]);

     if ($validator->fails()) {
         return response()->json(['message' => 'Erreur de validation', 'errors' => $validator->errors()], 422);
     }

     // Mise à jour des données
     $fournisseur = Fournisseur::findOrFail($id);
     $fournisseur->compte = $request->input('compte');
     $fournisseur->intitule = $request->input('intitule');
     $fournisseur->identifiant_fiscal = $request->input('identifiant_fiscal');
     $fournisseur->ICE = $request->input('ICE');
     $fournisseur->nature_operation = $request->input('nature_operation');
     $fournisseur->rubrique_tva = $request->input('rubrique_tva');
     $fournisseur->designation = $request->input('designation');
     $fournisseur->contre_partie = $request->input('contre_partie');
     
     $fournisseur->save(); // Enregistrer les modifications

     return response()->json(['message' => 'Fournisseur mis à jour avec succès', 'fournisseur' => $fournisseur], 200);
 }

public function getRubriquesTva()
{
 // Récupérer les rubriques TVA avec type = 'Achat', groupées par 'categorie'
$rubriques = Racine::select('categorie', 'Nom_racines', 'Taux', 'Num_racines')
->where('type', 'Achat') // Assurez-vous que 'type' est bien le nom de la colonne
->having('Taux', '>', 0) // Ne garder que les rubriques avec Taux supérieur à 0
->get();

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


public function getComptes()
{
    // Récupérer les comptes qui commencent par 21, 22, 23, 24, 25, ou 26
    $comptes = PlanComptable::where(function($query) {
        $query->where('compte', 'LIKE', '21%')
              ->orWhere('compte', 'LIKE', '22%')
              ->orWhere('compte', 'LIKE', '23%')
              ->orWhere('compte', 'LIKE', '24%')
              ->orWhere('compte', 'LIKE', '25%')
              ->orWhere('compte', 'LIKE', '613%')
              ->orWhere('compte', 'LIKE', '611%')
              ->orWhere('compte', 'LIKE', '614%')
              ->orWhere('compte', 'LIKE', '618%')
              ->orWhere('compte', 'LIKE', '631%')
              ->orWhere('compte', 'LIKE', '612%');
    })
    ->get(['compte', 'intitule']); // On ne récupère que les champs nécessaires

    return response()->json($comptes);
}



    public function destroy($id)
    {
        $fournisseur = Fournisseur::findOrFail($id);
        $fournisseur->delete();

        return response()->json(['success' => true]);
    }


    public function showImportForm()
    {
        return view('import'); // Assurez-vous que le nom de votre vue est correct
    }

    public function import(Request $request)
    {
        // Valider le fichier et les colonnes ici
        
        $validatedData= $request->validate([
            'file' => 'required|mimes:xlsx,xls,csv',
            'colonne_compte' => 'required|integer',
            'colonne_intitule' => 'required|integer',
            'colonne_identifiant_fiscal' => 'required|integer',
            'colonne_ICE' => 'required|integer',
            'colonne_nature_operation' => 'required|integer',
            'colonne_rubrique_tva' => 'required|integer',
            'colonne_designation' => 'required|integer',
            'colonne_contre_partie' => 'required|integer',
        ]);
    
        try {
            Excel::import(new FournisseurImport(
                $request->colonne_compte,
                $request->colonne_intitule,
                $request->colonne_identifiant_fiscal,
                $request->colonne_ICE,
                $request->colonne_nature_operation,
                $request->colonne_rubrique_tva,
                $request->colonne_designation,
                $request->colonne_contre_partie
            ), $request->file('file'));
    
            return redirect()->route('fournisseurs.index')->with('success', 'Fournisseurs importés avec succès !');
    
        } catch (\Exception $e) {
            // Gérer l'exception en retournant un message d'erreur
            return redirect()->back()->with('error', 'Erreur lors de l\'importation : ' . $e->getMessage());
        }
    }
    

}
