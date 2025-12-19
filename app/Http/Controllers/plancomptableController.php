<?php

namespace App\Http\Controllers;
use Exception;
use Illuminate\Http\Request;
use Maatwebsite\Excel\Facades\Excel; // si vous utilisez Laravel Excel
use App\Models\Societe;              // vérifier le chemin exact vers votre modèle Societe
use App\Models\PlanComptable;         // idem pour PlanComptable
use App\Models\Fournisseur;         // idem pour PlanComptable
use App\Models\Client;        // idem pour PlanComptable

use App\Imports\PlanComptableImport;
use App\Exports\PlanComptableExport;
use Illuminate\Support\Str; // N'oubliez pas d'importer le helper Str
use App\Services\PlanComptableService;

use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

class PlanComptableController extends Controller
{
public function search(Request $request)
{
    $q = $request->query('q', '');
    $societeId = session('societeId');
    if (! $societeId) return response()->json([], 200);

    $query = PlanComptable::where('societe_id', $societeId);
    if ($q !== '') {
        $query->where(function($s) use ($q) {
            $s->where('compte', 'like', "%{$q}%")->orWhere('intitule', 'like', "%{$q}%");
        });
    }

    $rows = $query->select('compte','intitule')->orderBy('compte')->limit(1000)->get();
    return response()->json($rows);
}
/**
 * Génère et persiste un plan comptable "par défaut" si aucun compte n'existe pour la société en session.
 */


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
public function index()
{
    // Vérifie si une société est sélectionnée en session
    $societeId = session('societeId');
    $societe = $societeId ? \App\Models\Societe::find($societeId) : null;

    // Retourne la vue avec la société
    return view('plancomptable', compact('societe'));
}

public function getData(Request $request)
{
    $societeId = session('societeId');
    if (!$societeId) {
        return response()->json(['error' => 'Aucune société sélectionnée dans la session'], 400);
    }

    $societe = \App\Models\Societe::find($societeId);
    if (!$societe) {
        return response()->json(['error' => 'Société introuvable'], 404);
    }

    $expectedLength = (int) $societe->nombre_chiffre_compte;

    DB::beginTransaction();
    try {
        // Récupérer les lignes déjà créées pour cette société
        $existingPlans = \App\Models\PlanComptable::where('societe_id', $societeId)
                            ->pluck('compte')
                            ->toArray();

        // Récupérer toutes les lignes templates (societe_id NULL ou 0)
        $templates = \App\Models\PlanComptable::whereNull('societe_id')
                        ->orWhere('societe_id', 0)
                        ->orderBy('compte')
                        ->get();

        $toInsert = [];
        $now = now();

        foreach ($templates as $tpl) {
            $raw = preg_replace('/\D/', '', (string)$tpl->compte);
            if ($raw === '') continue;

            $comptePad = str_pad($raw, $expectedLength, '0', STR_PAD_RIGHT);

            // On n'insère que si cette combinaison compte+societe_id n'existe pas
            if (!in_array($comptePad, $existingPlans) && strlen($comptePad) === $expectedLength) {
                $toInsert[] = [
                    'societe_id' => $societeId,
                    'compte'     => $comptePad,
                    'intitule'   => $tpl->intitule,
                    'etat'       => $tpl->intitule ? 'ok' : 'manquant',
                    'created_at' => $now,
                    'updated_at' => $now,
                ];
            }
        }

        if (!empty($toInsert)) {
            DB::table('plan_comptable')->insert($toInsert);
        }

        // Récupérer toutes les lignes pour cette société
        $plans = \App\Models\PlanComptable::where('societe_id', $societeId)
                    ->orderBy('compte')
                    ->get();

        $data = $plans->map(function ($p) {
            return [
                'id' => $p->id,
                'compte' => (string)$p->compte,
                'intitule' => $p->intitule,
                'etat' => $p->etat,
            ];
        })->values()->all();

        DB::commit();

        return response()->json([
            'data' => $data,
            'meta' => ['processed' => count($plans), 'inserted' => count($toInsert)],
            'expected_length' => $expectedLength,
            'message' => 'Plan comptable chargé sans doublons'
        ], 200);

    } catch (\Throwable $e) {
        DB::rollBack();
        Log::error('getData error: '.$e->getMessage(), ['exception' => $e]);
        return response()->json(['error' => 'Erreur serveur'], 500);
    }
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


public function import(Request $request)
{
    set_time_limit(300);

    $request->validate([
        'file'             => 'required|file|mimes:xlsx,xls,csv',
        'colonne_compte'   => 'required|integer',
        'colonne_intitule' => 'required|integer',
    ]);

    $societeId = $request->input('societe_id', session('societeId'));
    if (!$societeId) {
        return response()->json(['success' => false, 'error' => 'Aucun ID de société fourni.'], 422);
    }

    $societe = \App\Models\Societe::find($societeId);
    if (!$societe) {
        return response()->json(['success' => false, 'error' => "Société introuvable pour l'ID {$societeId}."], 422);
    }

    $compteLength = (int) $societe->nombre_chiffre_compte;

    try {
        $rawRows           = $this->parseExcelFile(
            $request->file('file'),
            $request->colonne_compte,
            $request->colonne_intitule
        );
        $insertPlan        = [];
        $insertFournisseurs= [];
        $insertClients     = [];
        $rowsForFrontend   = [];

        foreach ($rawRows as $row) {
            $compte   = trim((string) ($row['compte']   ?? ''));
            $intitule = trim((string) ($row['intitule'] ?? ''));
            $etat     = 'ok';

            if ($compte === '' || $intitule === '') {
                $etat = 'manquant';  // jaune
            } elseif (!ctype_digit($compte) || strlen($compte) !== $compteLength) {
                $etat = 'erreur';    // rouge
            }

            $rowsForFrontend[] = compact('compte', 'intitule', 'etat');

            $insertPlan[] = [
                'societe_id' => $societeId,
                'compte'     => $compte,
                'intitule'   => $intitule,
                'etat'       => $etat, // <-- champ ajouté
            ];

            if (\Illuminate\Support\Str::startsWith($compte, '4411')) {
                $insertFournisseurs[] = [
                    'societe_id' => $societeId,
                    'compte'     => $compte,
                    'intitule'   => $intitule,
                ];
            }

            if (\Illuminate\Support\Str::startsWith($compte, '3421')) {
                $insertClients[] = [
                    'societe_id'         => $societeId,
                    'compte'             => $compte,
                    'intitule'           => $intitule,
                    'identifiant_fiscal' => '',
                    'ICE'                => '',
                    'type_client'        => '',
                ];
            }
        }

        PlanComptable::upsert(
            $insertPlan,
            ['societe_id', 'compte'],
            ['intitule', 'etat']
        );

        Fournisseur::upsert(
            $insertFournisseurs,
            ['societe_id', 'compte'],
            ['intitule']
        );

        Client::upsert(
            $insertClients,
            ['societe_id', 'compte'],
            ['intitule', 'identifiant_fiscal', 'ICE', 'type_client']
        );

        return response()->json([
            'success' => true,
            'message' => 'Importation terminée.',
            'rows'    => $rowsForFrontend,
        ]);
    } catch (\Throwable $e) {
        Log::error('Erreur import Plan Comptable', ['exception' => $e]);

        return response()->json([
            'success' => false,
            'error'   => 'Erreur serveur : ' . $e->getMessage(),
        ], 500);
    }
}




    /**
     * Parse le fichier Excel et renvoie un tableau brut de lignes
     * en ignorant la première ligne (en-têtes).
     */
    protected function parseExcelFile($file, $compteCol, $intituleCol)
    {
        $allSheets = Excel::toArray([], $file);
        $rows      = $allSheets[0] ?? [];

        $result = [];
        foreach (array_slice($rows, 1) as $row) {
            $result[] = [
                'compte'   => $row[$compteCol - 1]   ?? null,
                'intitule' => $row[$intituleCol - 1] ?? null,
            ];
        }

        return $result;
    }



// Méthode pour exporter en Excel
public function exportExcel()
{

    $societeId = session('societeId'); // Récupérer l'ID de la société depuis la session

    // Créer l'export avec l'ID de la société
    return Excel::download(new PlanComptableExport($societeId), 'plan_comptable_societe_' . $societeId . '.xlsx');
}

 public function destroy($id)
{
    $planComptable = PlanComptable::findOrFail($id);
    $planComptable->delete();

    return response()->json([
        'success' => true,
        'message' => 'Plan comptable supprimé avec succès.'
    ], 200);
}


/**
 * Supprimer plusieurs plans comptables (avec suppression en cascade)
 */
/**
 * Supprimer plusieurs plans comptables (avec suppression en cascade et events)
 */
// PlanComptableController.php
public function deleteSelected(Request $request)
{
    $ids = $request->input('ids');

    if (!is_array($ids) || empty($ids)) {
        return response()->json([
            'success' => false,
            'message' => 'Aucun ID fourni pour la suppression.'
        ], 400);
    }

    try {
        $deletedCount = PlanComptable::whereIn('id', $ids)->delete();

        return response()->json([
            'success' => true,
            'message' => $deletedCount . ' ligne(s) supprimée(s) avec succès.'
        ], 200);
    } catch (\Exception $e) {
        // Log::error('deleteSelected error', ['exception' => $e]); // optionnel
        return response()->json([
            'success' => false,
            'message' => 'Erreur lors de la suppression : ' . $e->getMessage()
        ], 500);
    }




}


 public function forSociete(Request $request)
    {
        $societeId = session('societeId') ?: (int) $request->query('societe_id', 0);
        if (! $societeId) {
            return response()->json(['error' => 'Aucune société sélectionnée'], 400);
        }

        $societe = Societe::find($societeId);
        if (! $societe) {
            return response()->json(['error' => 'Société introuvable'], 404);
        }

        $targetLen = (int) ($societe->nombre_chiffre_compte ?? 8);
        if ($targetLen <= 0) $targetLen = 8;

        $persist = (bool) $request->query('persist', false);

        if ($persist) {
            $count = PlanComptableService::persistForSociete($societeId, $targetLen);
            return response()->json(['status' => 'ok', 'message' => "Persisté {$count} lignes", 'societe_id' => $societeId]);
        }

        $rows = PlanComptableService::generateForSociete($societeId, $targetLen, 3600);
        return response()->json(['status' => 'ok', 'count' => count($rows), 'data' => $rows]);
    }
}








