<?php

namespace App\Http\Controllers;
use App\Imports\FournisseurImport;

use App\Models\Fournisseur;
use App\Models\Racine;

use Illuminate\Http\Request;


use Maatwebsite\Excel\Facades\Excel;


class FournisseurController extends Controller
{
    public function index()
    {
        return view('fournisseurs'); // Assurez-vous que cela correspond à votre vue
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
            'designation' => 'required|string|max:255',
            'contre_partie' => 'required|string|max:255',
        ]);

        $fournisseur = Fournisseur::create($validatedData);

        return response()->json([
            'success' => true,
            'fournisseur' => $fournisseur,
        ]);
    }
 // Modifier un fournisseur
 public function update(Request $request, $id)
 {
     // Validation des données
     $validatedData = $request->validate([
         'compte' => 'required|string|max:255',
         'intitule' => 'required|string|max:255',
         'identifiant_fiscal' => 'required|string|max:255',
         'ICE' => 'required|string|max:15', // Limité à 15 caractères
         'nature_operation' => 'required|string|max:255',
         'rubrique_tva' => 'required|string|max:255',
         'designation' => 'required|string|max:255',
         'contre_partie' => 'required|string|max:255',
     ]);
    // Recherche et mise à jour du fournisseur
    $fournisseur = Fournisseur::findOrFail($id);
    $fournisseur->update($validatedData);

    return response()->json(['message' => 'Fournisseur modifié avec succès', 'fournisseur' => $fournisseur]);
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
        $validatedData = $request->validate([
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
