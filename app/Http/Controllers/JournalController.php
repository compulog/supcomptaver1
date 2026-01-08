<?php

namespace App\Http\Controllers;

use App\Models\PlanComptable;
use App\Models\Journal;
use App\Models\Racine;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

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
        $societeId = session('societeId');

        if (!$societeId) {
            return response()->json(['error' => 'Aucune société sélectionnée dans la session'], 400);
        }

        // -- (ton bloc d'insertion des journaux par défaut inchangé) --
        $journauxDefaut = [
            ['code_journal' => 'AN','intitule' => 'A NOUVEAU','type_journal' => 'Opérations Diverses'],
            ['code_journal' => 'OD','intitule' => 'OPERATIONS DIVERSES','type_journal' => 'Opérations Diverses'],
            ['code_journal' => 'CL','intitule' => 'CLÔTURE','type_journal' => 'Opérations Diverses'],
        ];

        foreach ($journauxDefaut as $data) {
            $exists = Journal::where('societe_id', $societeId)
                ->whereRaw('TRIM(UPPER(code_journal)) = ?', [strtoupper(trim($data['code_journal']))])
                ->exists();

            if (! $exists) {
                Journal::create([
                    'societe_id' => $societeId,
                    'code_journal' => strtoupper(trim($data['code_journal'])),
                    'intitule' => $data['intitule'],
                    'type_journal' => $data['type_journal'],
                    'contre_partie' => null,
                    'identifiant_fiscal' => null,
                    'ice' => null,
                    'rubrique_tva' => null,
                ]);
            }
        }

        // Récupérer tous les journaux (tri comme avant)
        $journaux = Journal::where('societe_id', $societeId)
            ->orderByRaw("CASE WHEN code_journal = 'AN' THEN 1 WHEN code_journal = 'OD' THEN 2 WHEN code_journal = 'CL' THEN 3 ELSE 4 END")
            ->orderBy('code_journal')
            ->get();

        // Extraire tous les codes de compte à rechercher (gère aussi les valeurs déjà formattées "compte - intitule")
        $compteCodes = $journaux->pluck('contre_partie')
            ->filter(function($v){ return $v !== null && trim($v) !== ''; })
            ->map(function($v){
                $v = trim($v);
                // si "compte - intitule" -> récupérer la partie compte (avant ' - ')
                if (Str::contains($v, ' - ')) {
                    return trim(explode(' - ', $v, 2)[0]);
                }
                return $v;
            })
            ->unique()
            ->values()
            ->all();

        // Récupérer en bloc les lignes du plan comptable correspondantes pour éviter N+1
        $plans = [];
        if (!empty($compteCodes)) {
            $plans = PlanComptable::where('societe_id', $societeId)
                ->whereIn('compte', $compteCodes)
                ->get()
                ->keyBy(function($item){ return trim($item->compte); });
        }

        // Construire un champ affichable 'contre_partie_affiche' pour chaque journal
        $journaux = $journaux->map(function($j) use ($plans) {
            $raw = $j->contre_partie;
            $display = null;

            if ($raw !== null && trim($raw) !== '') {
                $rawTrim = trim($raw);
                if (Str::contains($rawTrim, ' - ')) {
                    // déjà au bon format => on garde tel quel
                    $display = $rawTrim;
                } else {
                    // raw contient probablement le code compte -> chercher l'intitule
                    $compte = $rawTrim;
                    $plan = $plans->get($compte);
                    if ($plan) {
                        $display = $compte . ' - ' . $plan->intitule;
                    } else {
                        $display = $compte . ' (compte non trouvé)';
                    }
                }
            }

            // attribut additionnel accessible côté client: contre_partie_affiche
            $j->contre_partie_affiche = $display;
            return $j;
        });

        return response()->json($journaux);
    }

    public function getRubriquesTVAVente()
{
    $societeId = session('societeId');

    if (empty($societeId)) {
        return response()->json([
            'error' => 'Aucune société sélectionnée dans la session.'
        ], 400);
    }

    $rubriques = Racine::select('Num_racines', 'categorie', 'Nom_racines', 'Taux', 'compte_tva')
        ->where('societe_id', $societeId) // ✅ Filtrage par société
        ->whereIn('type', ['Ca imposable', 'CA non imposable'])
        ->get();

    if ($rubriques->isEmpty()) {
        return response()->json([
            'message' => 'Aucune rubrique trouvée pour cette société.'
        ], 404);
    }

    $rubriquesParCategorie = [];

    foreach ($rubriques as $rubrique) {
        if (!isset($rubriquesParCategorie[$rubrique->categorie])) {
            $rubriquesParCategorie[$rubrique->categorie] = [
                'categorie' => $rubrique->categorie,
                'rubriques' => [],
            ];
        }

        $rubriquesParCategorie[$rubrique->categorie]['rubriques'][] = [
            'Num_racines' => $rubrique->Num_racines,
            'Nom_racines' => $rubrique->Nom_racines,
            'Taux' => $rubrique->Taux,
            'compte_tva' => $rubrique->compte_tva,
        ];
    }

    return response()->json([
        'rubriques' => array_values($rubriquesParCategorie),
    ]);
}


    public function getComptesAchats()
    {
        $societeId = session('societeId');
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
        $societeId = session('societeId');
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
        $societeId = session('societeId');
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
        $societeId = session('societeId');
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

    public function getComptesOP()
    {
        $societeId = session('societeId');
        if (!$societeId) {
            return response()->json(['error' => 'Aucune société sélectionnée dans la session'], 400);
        }

        $comptes = PlanComptable::where('societe_id', $societeId)
            ->get(['compte', 'intitule']);

        return response()->json($comptes);
    }

    /**
     * GET /plancomptable
     * Retourne la liste des comptes (filtrage optionnel q pour select2/ajax)
     */
    public function listPlanComptable(Request $request)
    {
        $societeId = $request->input('societe_id') ?? session('societeId');
        if (!$societeId) {
            return response()->json([], 200);
        }

        $q = $request->query('q', null);

        $query = PlanComptable::where('societe_id', $societeId);

        if ($q) {
            $query->where(function($sub) use ($q) {
                $sub->where('compte', 'like', "%{$q}%")
                    ->orWhere('intitule', 'like', "%{$q}%");
            });
        }

        $list = $query->orderBy('compte')->get(['compte', 'intitule']);

        return response()->json($list);
    }

    /**
     * POST /plancomptable
     * Crée un compte dans le plan comptable (utilisé par le modal + ajout)
     */
    public function storePlanComptable(Request $request)
    {
        $societeId = $request->input('societe_id') ?? session('societeId');
        if (!$societeId) {
            return response()->json(['success' => false, 'message' => 'Société non fournie'], 400);
        }

        $rules = [
            'compte'   => 'required|string|max:50',
            'intitule' => 'required|string|max:255',
            'societe_id' => 'required|integer|exists:societes,id',
        ];

        // validation
        $validated = $request->validate($rules);

        // doublon
        $exists = PlanComptable::where('societe_id', $societeId)
            ->where('compte', trim($validated['compte']))
            ->exists();

        if ($exists) {
            return response()->json([
                'success' => false,
                'message' => 'Ce compte existe déjà pour la société.',
                'errors'  => ['compte' => ['Compte déjà existant.']]
            ], 422);
        }

        DB::beginTransaction();
        try {
            $pc = new PlanComptable();
            $pc->compte = trim($validated['compte']);
            $pc->intitule = $validated['intitule'];
            $pc->societe_id = $societeId;
            if (Auth::check()) $pc->created_by = Auth::id();
            $pc->save();

            DB::commit();

            return response()->json([
                'success' => true,
                'created' => true,
                'data' => [
                    'compte' => $pc->compte,
                    'intitule' => $pc->intitule,
                    'id' => $pc->id,
                ],
                'message' => 'Compte créé'
            ], 201);
        } catch (\Throwable $e) {
            DB::rollBack();
            Log::error('Échec création PlanComptable : ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Erreur serveur lors de la création du compte.',
            ], 500);
        }
    }

    public function store(Request $request)
    {
        $societeId = session('societeId');
        if (!$societeId) {
            return response()->json(['error' => 'Aucune société sélectionnée dans la session'], 400);
        }

        $rules = [
            'code_journal'       => 'required|string|max:255',
            'type_journal'       => 'required|string|max:255',
            'intitule'           => 'required|string|max:255',
            'contre_partie'      => 'nullable|string|max:255',
            'identifiant_fiscal' => 'nullable|digits_between:7,8',
            'ice'                => 'nullable|digits:15',
            'rubrique_tva'       => 'nullable|string',
        ];
        $validated = $request->validate($rules);

        $duplicate = Journal::withoutTrashed()
            ->where('societe_id', $societeId)
            ->where('code_journal', $validated['code_journal'])
            ->exists();

        if ($duplicate) {
            return response()->json(['error' => 'Ce code journal existe déjà pour cette société'], 400);
        }

        $validated['societe_id'] = $societeId;

        try {
            Journal::create($validated);
            return response()->json(['message' => 'Journal ajouté avec succès'], 201);
        } catch (\Exception $e) {
            Log::error('Échec création Journal : '.$e->getMessage());
            return response()->json(['error' => 'Erreur interne lors de la création du journal'], 500);
        }
    }

    /**
     * Vérifie l'existence d'un code journal non supprimé
     */
    public function checkJournal(Request $request)
    {
        $codeJournal = $request->get('code_journal');
        $societeId   = session('societeId');

        $exists = Journal::withoutTrashed()
            ->when($societeId, function ($query, $societeId) {
                $query->where('societe_id', $societeId);
            })
            ->where('code_journal', $codeJournal)
            ->exists();

        return response()->json(['exists' => $exists]);
    }

    // Méthode pour afficher les détails d'un journal spécifique
    public function edit($id)
    {
        $journal = Journal::findOrFail($id);
        return response()->json($journal);
    }

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
            'identifiant_fiscal'  => 'nullable|digits_between:7,8',
            'ice' => 'nullable|digits:15',
            'rubrique_tva' => 'nullable|integer',
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
        $request->validate([
            'ids'   => 'required|array',
            'ids.*' => 'integer',
        ]);

        $sessionSocieteId = session('societeId');
        if (!$sessionSocieteId) {
            return response()->json(['error' => 'Aucune société sélectionnée dans la session'], 400);
        }

        $deletable   = [];
        $nonDeletable = [];

        foreach ($request->ids as $id) {
            $journal = Journal::find($id);
            if (!$journal || $journal->societe_id !== $sessionSocieteId) {
                continue;
            }

            $mouvemented = DB::table('operation_courante')
                ->where('type_journal', $journal->code_journal)
                ->where('societe_id', $sessionSocieteId)
                ->exists();

            if ($mouvemented) {
                $nonDeletable[] = $journal->code_journal;
            } else {
                $deletable[] = $journal->id;
            }
        }

        if (!empty($nonDeletable)) {
            return response()->json([
                'error' => 'Impossible de supprimer (mouvementés) : '.implode(', ', $nonDeletable)
            ], 400);
        }

        try {
            // Hard delete : supprime physiquement pour libérer le code
            $deletedCount = Journal::whereIn('id', $deletable)->forceDelete();

            return response()->json([
                'status'  => 'success',
                'message' => "{$deletedCount} journal(s) définitivement supprimé(s)",
            ]);
        } catch (\Exception $e) {
            Log::error('Échec suppression Journal : '.$e->getMessage());
            return response()->json([
                'status'  => 'error',
                'message' => 'Erreur lors de la suppression',
            ], 500);
        }
    }

}
