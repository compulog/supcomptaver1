<?php

namespace App\Http\Controllers;
use Exception;
use App\Models\societe;
use App\Models\PlanComptable;
use Illuminate\Http\Request;
use Maatwebsite\Excel\Facades\Excel;
use App\Imports\PlanComptableImport;
use App\Exports\PlanComptableExport;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;

class PlanComptableController extends Controller
{
   
    // Méthode pour afficher tous les plans comptables d'une société
    public function index()
    {
        // Récupérer l'ID de la société dans la session
        $societeId = session('societeId');
        
        // Vérifier si l'ID de la société existe
        if (!$societeId) {
            return response()->json(['error' => 'Aucune société sélectionnée dans la session'], 400);
        }

        // Récupérer tous les plans comptables pour la société spécifiée
        $plansComptables = PlanComptable::where('societe_id', $societeId)->get();

        return response()->json($plansComptables);
    }

    // Méthode pour récupérer les données du plan comptable
    public function getData()
    {
        $plansComptables = PlanComptable::all();
        return response()->json($plansComptables);
    }

    // Méthode pour ajouter un nouveau plan comptable
    public function store(Request $request)
    {
        // Validation des données reçues
        $request->validate([
            'compte' => 'required|string|max:255',
            'intitule' => 'required|string|max:255',
        ]);

        // Récupérer l'ID de la société depuis la session
        $societeId = session('societeId');

        // Vérifier si l'ID de la société existe
        if (!$societeId) {
            return response()->json(['error' => 'Aucune société sélectionnée dans la session'], 400);
        }

        // Créer un nouveau plan comptable
        $planComptable = new PlanComptable();
        $planComptable->compte = $request->compte;
        $planComptable->intitule = $request->intitule;
        $planComptable->societe_id = $societeId;  // Associer l'ID de la société
        $planComptable->save();

        // Retourner une réponse JSON de succès
        return response()->json(['success' => 'Plan comptable ajouté avec succès!']);
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

      


    // Afficher le formulaire d'importation
    public function showImportForm()
    {
        return view('plancomptable.import'); // La vue avec le formulaire d'import
    }
    
    
    
        /**
         * Méthode pour gérer l'importation du plan comptable
         */
        public function import(Request $request)
        {
              // Validation des données
    $request->validate([
        'file' => 'required|file|mimes:xlsx,xls,csv',
        'colonne_compte' => 'required|integer',
        'colonne_intitule' => 'required|integer',
    ]);

    // Récupérer l'ID de la société à partir de la session
    $societeId = session('societeId');

    // Si l'ID de la société est modifié (par exemple, depuis un formulaire ou une sélection), le mettre à jour
    if ($request->has('societe_id')) {
        $societeId = $request->societe_id;
    }

    try {
        // Récupérer les données du fichier Excel
        $importedData = $this->parseExcelFile($request->file('file'), $request->colonne_compte, $request->colonne_intitule);

        // Insérer les données si le compte n'existe pas déjà pour la société
        foreach ($importedData as $data) {
            // Vérifier si le compte existe déjà pour la société
            $existingPlanComptable = PlanComptable::where('compte', $data['compte'])
                                                  ->where('societe_id', $societeId)
                                                  ->first();

            // Si le compte n'existe pas, insérer le nouveau plan comptable
            if (!$existingPlanComptable) {
                PlanComptable::create([
                    'compte' => $data['compte'],
                    'intitule' => $data['intitule'],
                    'societe_id' => $societeId,  // Utiliser l'ID de la société actuel
                ]);
            }
        }

        // Retourner à la page précédente avec un message de succès
        return redirect()->back()->with('success', 'Importation réussie.');
    } catch (\Exception $e) {
        // En cas d'erreur
        return redirect()->back()->with('error', 'Erreur lors de l\'importation : ' . $e->getMessage());
    }
        }
    /**
     * Parser le fichier Excel (en ignorant la première ligne)
     */
    protected function parseExcelFile($file, $compteColumn, $intituleColumn)
    {
        // Utilisation de la bibliothèque Laravel Excel pour lire le fichier
        $data = Excel::toArray([], $file);  // Lire toutes les feuilles du fichier Excel

        // Extraire les données en ignorant la première ligne (index 0)
        $importedData = [];
        foreach (array_slice($data[0], 1) as $row) {  // On commence à partir de la deuxième ligne (index 1)
            $importedData[] = [
                'compte' => $row[$compteColumn - 1],  // Compte basé sur l'index de la colonne
                'intitule' => $row[$intituleColumn - 1],  // Intitulé basé sur l'index de la colonne
            ];
        }

        return $importedData;
    }
        
    
    

// Méthode pour exporter en Excel
public function exportExcel()
{
    
    $societeId = session('societeId'); // Récupérer l'ID de la société depuis la session
    
    // Créer l'export avec l'ID de la société
    return Excel::download(new PlanComptableExport($societeId), 'plan_comptable_societe_' . $societeId . '.xlsx');
}


// PlanComptableController.php
public function deleteSelected(Request $request)
{
    $ids = $request->input('ids'); // Récupérer les IDs envoyés depuis le frontend
    
    // Suppression des lignes dans la base de données
    try {
        PlanComptable::whereIn('id', $ids)->delete(); // Suppression des enregistrements correspondants
        return response()->json(['status' => 'success']);
    } catch (\Exception $e) {
        return response()->json(['status' => 'error', 'message' => $e->getMessage()]);
    }
}


}









