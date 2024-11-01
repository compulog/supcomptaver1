<?php

namespace App\Http\Controllers;

use App\Models\PlanComptable;
use Illuminate\Http\Request;
use Maatwebsite\Excel\Facades\Excel;
use App\Imports\PlanComptableImport;
use App\Exports\PlanComptableExport;
use Illuminate\Support\Facades\DB;

class PlanComptableController extends Controller
{
    // Afficher la liste des plans comptables
    public function index()
    {
        return view('plancomptable'); // Assurez-vous que cela correspond à votre vue
      
    }

    

    public function getData()
    {
        $PlanComptable = PlanComptable::all();
        return response()->json($PlanComptable);
    }

    // Ajouter un nouveau plan comptable
    public function store(Request $request)
    {// Validation des données
        $request->validate([
            'compte' => 'required|string|max:255',
            'intitule' => 'required|string|max:255',
        ]);

        try {
            // Enregistrement du plan comptable
            $planComptable = new PlanComptable();
            $planComptable->compte = $request->compte;
            $planComptable->intitule = $request->intitule;
            $planComptable->save();

            return response()->json(['message' => 'Plan comptable ajouté avec succès.'], 201);
        } catch (\Exception $e) {
            return response()->json(['message' => 'Erreur lors de l\'enregistrement des données.'], 500);
        }
    }



    public function edit(Request $request, $id)
    {
        $validatedData = $request->validate([
            'compte' => 'required|string|max:255',
            'intitule' => 'required|string|max:255',
        ]);

        $planComptable = PlanComptable::findOrFail($id);
        $planComptable->compte = $validatedData['compte'];
        $planComptable->intitule = $validatedData['intitule'];
        $planComptable->save();

        return response()->json(['success' => true]);
    }

    // Modifier un plan comptable existant
    public function update(Request $request, $id)
    {
        // Validation des données
    $validatedData = $request->validate([
        'compte' => 'required|string|max:255',
        'intitule' => 'required|string|max:255',
    ]);

    // Mise à jour du plan comptable
    $planComptable = PlanComptable::find($id);
    if ($planComptable) {
        $planComptable->update($validatedData);
        return response()->json(['success' => true, 'message' => 'Mise à jour réussie']);
    } else {
        return response()->json(['success' => false, 'message' => 'Plan comptable non trouvé'], 404);
    }
    }

    // Supprimer un plan comptable
    public function destroy($id)
    {
        // Récupérer le plan comptable par ID
        $planComptable = PlanComptable::findOrFail($id);
        
        // Supprimer le plan comptable
        $planComptable->delete();

        // Retourner une réponse JSON
        return response()->json(['success' => true]);
    }

    // Importer des plans comptables à partir d'un fichier Excel
    public function import(Request $request)
    {
        $request->validate([
            'file' => 'required|file|mimes:xlsx,xls,csv',
            'colonne_compte' => 'required|integer',
            'colonne_intitule' => 'required|integer',
        ]);
    
        // Importation du fichier Excel sans en-tête
        Excel::import(new PlanComptableImport($request->colonne_compte, $request->colonne_intitule), $request->file('file'));
    
        return response()->json(['message' => 'Importation réussie !'], 200);
    }


// Vider tous les enregistrements dans le plan comptable
public function viderPlanComptable()
    {
        // Supprimer tous les enregistrements en utilisant le modèle Eloquent
        PlanComptable::query()->delete(); // Cela supprimera tous les enregistrements

        // Alternativement, vous pouvez utiliser truncate si vous voulez remettre à zéro les ID
        PlanComptable::truncate();

        return response()->json(['success' => true, 'message' => 'Plan comptable vidé avec succès.']);
    }

// Méthode pour exporter en Excel
public function exportExcel()
{
    return Excel::download(new PlanComptableExport, 'plan_comptable.xlsx');
}

}












