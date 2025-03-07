<?php

namespace App\Http\Controllers;
use App\Models\PlanComptable;
use App\Models\Journal;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

class JournalController extends Controller
{


        // Afficher tous les journaux
        public function index()
        {
           // Récupérer l'ID de la société dans la session
    $societeId = session('societeId');

    // Vérifier si l'ID de la société existe dans la session
    if (!$societeId) {
        return response()->json(['error' => 'Aucune société sélectionnée dans la session'], 400);
    }

    // Récupérer tous les journaux qui appartiennent à la société spécifiée
    $journaux = Journal::where('societe_id', $societeId)->get();

    // Retourner les journaux associés à la société en format JSON
    return response()->json($journaux);
        }


        public function getData()
        {
            // Récupérer l'ID de la société dans la session
            $societeId = session('societeId');

            // Vérifier si l'ID de la société existe
            if (!$societeId) {
                return response()->json(['error' => 'Aucune société sélectionnée dans la session'], 400);
            }

            // Récupérer tous les plans comptables pour la société spécifiée
            $journaux = Journal::where('societe_id', $societeId)->get();

            return response()->json($journaux);
        }



        public function getComptesAchats()
        {
            // Récupérer l'ID de la société dans la session
            $societeId = session('societeId');

            // Vérifier si l'ID de la société existe dans la session
            if (!$societeId) {
                return response()->json(['error' => 'Aucune société sélectionnée dans la session'], 400);
            }

            $comptes = PlanComptable::where('societe_id', $societeId)
                ->where(function($query) {
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
                ->get(['compte', 'intitule']);

            return response()->json($comptes);
        }

        public function getComptesVentes()
        {
            // Récupérer l'ID de la société dans la session
            $societeId = session('societeId');

            // Vérifier si l'ID de la société existe dans la session
            if (!$societeId) {
                return response()->json(['error' => 'Aucune société sélectionnée dans la session'], 400);
            }

            $comptes = PlanComptable::where('societe_id', $societeId)
                ->where(function($query) {
                    $query->where('compte', 'LIKE', '711%')
                          ->orWhere('compte', 'LIKE', '712%')
                          ->orWhere('compte', 'LIKE', '718%')
                          ->orWhere('compte', 'LIKE', '732%')
                          ->orWhere('compte', 'LIKE', '738%')
                          ->orWhere('compte', 'LIKE', '733%');
                })
                ->get(['compte', 'intitule']);

            return response()->json($comptes);
        }

        public function getComptesCaisse()
        {
            // Récupérer l'ID de la société dans la session
            $societeId = session('societeId');

            // Vérifier si l'ID de la société existe dans la session
            if (!$societeId) {
                return response()->json(['error' => 'Aucune société sélectionnée dans la session'], 400);
            }

            $comptes = PlanComptable::where('societe_id', $societeId)
                ->where('compte', 'LIKE', '516%')
                ->get(['compte', 'intitule']);

            return response()->json($comptes);
        }

        public function getComptesBanque()
        {
            // Récupérer l'ID de la société dans la session
            $societeId = session('societeId');

            // Vérifier si l'ID de la société existe dans la session
            if (!$societeId) {
                return response()->json(['error' => 'Aucune société sélectionnée dans la session'], 400);
            }

            $comptes = PlanComptable::where('societe_id', $societeId)
                ->where(function ($query) {
                    $query->where('compte', 'LIKE', '514%')
                          ->orWhere('compte', 'LIKE', '554%');
                })
                ->get(['compte', 'intitule']);

            return response()->json($comptes);
        }


        public function store(Request $request)
        {
            // Récupérer l'ID de la société depuis la session
            $societeId = session('societeId');
            if (!$societeId) {
                return response()->json(['error' => 'Aucune société sélectionnée dans la session'], 400);
            }

            // Définition des règles de validation
            $rules = [
                'code_journal'  => 'required|string|max:255',
                'type_journal'  => 'required|string|max:255',
                'intitule'      => 'required|string|max:255',
                'contre_partie' => 'nullable|string|max:255',
                'if'            => 'nullable|digits_between:7,8',
                'ice'           => 'nullable|digits:15',
            ];

            // Valider les données de la requête
            $validatedData = $request->validate($rules);

            // Si le type de journal est "Opérations Diverses", on force la valeur de contre_partie à null
            if ($validatedData['type_journal'] === 'Opérations Diverses') {
                $validatedData['contre_partie'] = null;
            }

            // Vérifier si un journal avec ce code existe déjà pour cette même société
            $exists = Journal::where('societe_id', $societeId)
                             ->where('code_journal', $validatedData['code_journal'])
                             ->exists();

            if ($exists) {
                return response()->json(['error' => 'Ce code journal existe déjà pour cette société'], 400);
            }

            // Ajouter l'ID de la société aux données validées
            $validatedData['societe_id'] = $societeId;

            // Créer le nouveau journal
            Journal::create($validatedData);

            return response()->json(['message' => 'Journal ajouté avec succès']);
        }


        public function checkJournal(Request $request)
{
    // Récupérer le code journal depuis la requête
    $codeJournal = $request->get('code_journal');

    // Si nécessaire, vous pouvez récupérer l'ID de la société par exemple depuis la session
    $societeId = session('societeId');

    // Vérifier si un journal avec ce code (et éventuellement pour cette société) existe déjà
    $exists = Journal::when($societeId, function ($query, $societeId) {
                        $query->where('societe_id', $societeId);
                    })
                    ->where('code_journal', $codeJournal)
                    ->exists();

    // Retourner la réponse JSON
    return response()->json(['exists' => $exists]);
}

    // Méthode pour afficher les détails d'un journal spécifique
    public function edit($id)
    {
        $journal = Journal::findOrFail($id);
        return response()->json($journal);
    }
// JournalController.php

// Méthode pour récupérer un journal
public function show($id)
{
    $journal = Journal::find($id);

    if ($journal) {
        return response()->json([
            'success' => true,
            'data' => $journal
        ]);
    } else {
        return response()->json(['success' => false], 404);
    }
}

// Méthode pour mettre à jour un journal
public function update(Request $request, $id)
{
    $journal = Journal::find($id);

    if (!$journal) {
        return response()->json(['success' => false], 404);
    }

    // Validation des données
    $validated = $request->validate([
        'code_journal' => 'required|string',
        'type_journal' => 'nullable|string',
        'intitule' => 'nullable|string',
        'contre_partie' => 'nullable|string',
        'if' => 'nullable|digits_between:7,8',
        'ice' => 'nullable|digits:15',
    ]);

    // Mise à jour du journal
    $journal->update($validated);

    return response()->json(['success' => true]);
}


public function destroy(Request $request, $id)
{
    // Récupérer l'ID de la société depuis la session
    $sessionSocieteId = session('societeId');
    if (!$sessionSocieteId) {
        return response()->json(['error' => 'Aucune société sélectionnée dans la session'], 400);
    }

    // Récupérer le journal à supprimer
    $journal = Journal::findOrFail($id);

    // Vérifier que le journal appartient à la société de la session
    if ($journal->societe_id != $sessionSocieteId) {
        return response()->json(['error' => "Ce journal n'appartient pas à la société sélectionnée"], 400);
    }

    // Vérifier dans operation_courante si le journal est "mouvementé"
    $isMouvemented = DB::table('operation_courante')
                        ->where('type_journal', $journal->code_journal)
                        ->where('societe_id', $journal->societe_id)
                        ->exists();

    if ($isMouvemented) {
        return response()->json([
            'error' => "Le journal '{$journal->code_journal}' est déjà mouvementé et ne peut pas être supprimé !"
        ], 400);
    }

    // Procéder à la suppression si aucune opération n'est liée
    $journal->delete();
    return response()->json(['message' => 'Journal supprimé avec succès.']);
}



public function deleteSelected(Request $request)
{
    // Valider que le tableau 'ids' est bien fourni
    $request->validate([
        'ids' => 'required|array',
        'ids.*' => 'integer',
    ]);

    // Récupérer l'ID de la société depuis la session
    $sessionSocieteId = session('societeId');
    if (!$sessionSocieteId) {
        return response()->json(['error' => 'Aucune société sélectionnée dans la session'], 400);
    }

    $ids = $request->ids;
    $nonDeletable = [];
    $deletableIds = [];

    // Parcourir chaque journal et vérifier s'il est supprimable
    foreach ($ids as $id) {
        $journal = Journal::find($id);
        if ($journal) {
            // Vérifier que le journal appartient à la société de la session
            if ($journal->societe_id != $sessionSocieteId) {
                $nonDeletable[] = $journal->code_journal;
                continue;
            }

            // Vérifier si le journal est mouvementé dans operation_courante :
            // Le journal est mouvementé si un enregistrement existe où
            // 'type_journal' = $journal->code_journal ET 'societe_id' = $journal->societe_id
            $isMouvemented = DB::table('operation_courante')
                                ->where('type_journal', $journal->code_journal)
                                ->where('societe_id', $journal->societe_id)
                                ->exists();
            if ($isMouvemented) {
                $nonDeletable[] = $journal->code_journal;
            } else {
                $deletableIds[] = $journal->id;
            }
        }
    }

    if (!empty($nonDeletable)) {
        return response()->json([
            'error' => "Les journaux suivants sont déjà mouvementés et ne peuvent pas être supprimés : " . implode(', ', $nonDeletable)
        ], 400);
    }

    try {
        $deletedCount = Journal::whereIn('id', $deletableIds)->delete();
        return response()->json([
            'status' => 'success',
            'message' => "{$deletedCount} journaux supprimés"
        ]);
    } catch (\Exception $e) {
        return response()->json([
            'status' => 'error',
            'message' => 'Erreur lors de la suppression.',
            'error' => $e->getMessage()
        ], 500);
    }
}




}
