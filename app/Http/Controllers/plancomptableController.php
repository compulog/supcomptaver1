<?php

namespace App\Http\Controllers;
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
     * Traite l'importation du fichier Excel
     */
    public function import(Request $request)
    {
        // Validation des données envoyées par le formulaire
        $validatedData = $request->validate([
            'file' => 'required|mimes:xlsx,xls,csv',  // Validation du fichier Excel
            
            'colonne_compte' => 'required|integer', // Validation de la colonne compte
            'colonne_intitule' => 'required|integer', // Validation de la colonne intitulé
        ]);

        try {
            // Récupérer l'ID de la société
            $societeId = $request->input('societe_id');
            
            // Importation du fichier avec les colonnes spécifiées et la société associée
            Excel::import(new PlanComptableImport(
                $societeId,
                $request->colonne_compte,
                $request->colonne_intitule
            ), $request->file('file'));

            return redirect()->route('plancomptable.index')
                             ->with('success', 'Plan comptable importé avec succès pour la société ID ' . $societeId);
        } catch (\Exception $e) {
            return back()->with('error', 'Erreur lors de l\'importation : ' . $e->getMessage());
        }
    }
    
    

// Méthode pour exporter en Excel
public function exportExcel()
{
    return Excel::download(new PlanComptableExport, 'plan_comptable.xlsx');
}


}









