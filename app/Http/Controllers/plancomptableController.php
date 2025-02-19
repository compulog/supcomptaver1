<?php

namespace App\Http\Controllers;
use Exception;
use Illuminate\Http\Request;
use Maatwebsite\Excel\Facades\Excel; // si vous utilisez Laravel Excel
use App\Models\Societe;              // vérifier le chemin exact vers votre modèle Societe
use App\Models\PlanComptable;         // idem pour PlanComptable
use App\Imports\PlanComptableImport;
use App\Exports\PlanComptableExport;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

class PlanComptableController extends Controller
{
    public function ajouterContrePartie(Request $request)
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

    // Vérifier si le compte existe déjà dans la base de données pour cette société
    $existingAccount = PlanComptable::where('compte', $request->input('compte'))
        ->where('societe_id', $societeId)
        ->first();

    if ($existingAccount) {
        return response()->json(['message' => 'Ce compte existe déjà pour cette société.'], 400);
    }

    // Créer le nouveau compte dans la table plan_comptable
    $compte = PlanComptable::create([
        'compte' => $request->input('compte'),
        'intitule' => $request->input('intitule'),
        'societe_id' => $societeId,
    ]);

    // Retourner la réponse avec le nouveau compte créé
    return response()->json([
        'message' => 'Compte ajouté avec succès.',
        'contre_partie' => $compte,
    ]);
}




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

    // Si l'ID de la société est modifié (par exemple, depuis un formulaire ou une sélection), le mettre à jour
    if ($request->has('societe_id')) {
        $societeId = $request->societe_id;
    }

    // Vérifier si l'ID de la société existe
    if (!$societeId) {
        return response()->json(['error' => 'Aucune société sélectionnée dans la session'], 400);
    }

    // Vérifier si le compte existe déjà pour cette société
    $existingPlanComptable = PlanComptable::where('compte', $request->compte)
                                          ->where('societe_id', $societeId)
                                          ->first();

    // Si le compte n'existe pas, créer un nouveau plan comptable
    if (!$existingPlanComptable) {
        $planComptable = new PlanComptable();
        $planComptable->compte = $request->compte;
        $planComptable->intitule = $request->intitule;
        $planComptable->societe_id = $societeId;  // Associer l'ID de la société
        $planComptable->save();

        // Retourner une réponse JSON de succès avec les données du plan comptable ajouté
        return response()->json([
            'success' => true,
            'message' => 'Plan comptable ajouté avec succès!',
            'data' => [
                'compte' => $planComptable->compte,
                'intitule' => $planComptable->intitule,
            ]
        ]);
    } else {
        // Si le compte existe déjà, retourner une erreur
        return response()->json(['error' => 'Le compte existe déjà pour cette société'], 400);
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




    // Afficher le formulaire d'importation
    public function showImportForm()
    {
        return view('plancomptable.import'); // La vue avec le formulaire d'import
    }

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

            // Récupérer la société pour obtenir le nombre de chiffres autorisé dans le compte
            $societe = Societe::find($societeId);
            $compteLength = 8;  // Valeur par défaut de 8 chiffres

            // Vérifier si la société existe et récupérer le nombre de chiffres du compte
            if ($societe && isset($societe->nombre_chiffre_compte)) {
                $compteLength = $societe->nombre_chiffre_compte;
            }

            // Filtrer les données pour ne garder que les lignes où le compte respecte la longueur autorisée
            $validData = array_filter($importedData, function ($data) use ($compteLength) {
                return strlen($data['compte']) === $compteLength;
            });

            // Si aucune donnée valide n'est trouvée
            if (empty($validData)) {
                return redirect()->back()->with('error', "Aucune ligne ne respecte la longueur autorisée de {$compteLength} chiffres.");
            }

            // Insérer les données valides si le compte n'existe pas déjà pour la société
            foreach ($validData as $data) {
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
     // Valider que le tableau 'ids' est bien fourni
     $request->validate([
        'ids' => 'required|array',
        'ids.*' => 'integer',  // Chaque ID doit être un entier
    ]);

    try {
        // Supprimer les lignes avec les IDs reçus
        PlanComptable::whereIn('id', $request->ids)->delete();

        return response()->json(['status' => 'success']);
    } catch (\Exception $e) {
        return response()->json(['status' => 'error', 'message' => 'Erreur lors de la suppression.']);
    }
}


}









