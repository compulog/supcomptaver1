<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
use Illuminate\Http\Request;
use App\Models\Racine;
use App\Models\PlanComptable;
use App\Models\Fournisseur;
use Illuminate\Validation\Rule;
use App\Models\Societe;

class RacineController extends Controller
{
    /**
     * Affiche la vue et, en AJAX, retourne les rubriques
     * avec leurs options de compte_tva filtrées selon vos règles.
     */
public function index(Request $request)
{
    $societeId = $request->header('X-Societe-Id') ?? session('societeId');
    if (! $societeId) {
        return response()->json(['error' => 'ID de société manquant'], 400);
    }

    // Info société (nombre_chiffre_compte)
    $societe = Societe::find($societeId);
    $expectedLen = $societe ? (int)$societe->nombre_chiffre_compte : null;

    $getPrefix = function($typeRaw, $categorieRaw) {
        $t = trim((string)($typeRaw ?? ''));
        $c = trim((string)($categorieRaw ?? ''));

        if ($t === 'CA non imposable') {
            return null;
        }

        if ($t === 'Les déductions') {
            if ($c === 'Immobilisations') return '34551';
            if ($c === 'Prestations de service' || $c === 'Autres achats non immobilisés') return '34552';
            return '34552';
        }

        if ($t === 'CA imposable') return '4455';

        return null;
    };

    // 1) Récupère racines de base (societe_id = 0)
    $baseRacines = Racine::where('societe_id', 0)->get();

    // 2) Codes déjà présents pour la société
    $existingCodes = Racine::where('societe_id', $societeId)
                            ->pluck('Num_racines')
                            ->map(fn($v) => (string)$v)
                            ->toArray();

    // 3) Prépare inserts pour codes manquants et calcule compte_tva pour chaque insertion
    $now = Carbon::now();
    $inserts = [];

    foreach ($baseRacines as $r) {
        $code = (string)$r->Num_racines;
        if (in_array($code, $existingCodes, true)) continue;

        if ((float)($r->Taux ?? 0) === 0.0) {
            $compte_tva = null;
        } else {
            $prefix = $getPrefix($r->type, $r->categorie);
            $compte_tva = null;
            if ($prefix !== null) {
                $query = PlanComptable::where('societe_id', $societeId)
                                      ->where('compte', 'like', $prefix . '%');
                if ($expectedLen && $expectedLen > 0) {
                    $query->whereRaw('CHAR_LENGTH(compte) = ?', [$expectedLen]);
                }
                $first = $query->orderBy('compte')->first(['compte']);
                if ($first) $compte_tva = $first->compte;
                else {
                    $fallback = PlanComptable::where('societe_id', $societeId)
                                ->where('compte', 'like', $prefix . '%')
                                ->orderBy('compte')
                                ->first(['compte']);
                    $compte_tva = $fallback->compte ?? null;
                }
            }
        }

        $inserts[] = [
            'societe_id'  => $societeId,
            'Num_racines' => $r->Num_racines,
            'Nom_racines' => $r->Nom_racines,
            'Taux'        => $r->Taux,
            'type'        => $r->type,
            'categorie'   => $r->categorie,
            'compte_tva'  => $compte_tva,
            'is_hidden'   => $r->is_hidden ?? false,
            'created_at'  => $now,
            'updated_at'  => $now,
        ];
    }

    if (!empty($inserts)) {
        DB::table('racines')->insertOrIgnore($inserts);
    }

    // 5) Mettre à jour les racines EXISTANTES pour la société qui ont compte_tva vide
    $toUpdate = Racine::where('societe_id', $societeId)
                ->where(function($q){ $q->whereNull('compte_tva')->orWhere('compte_tva', ''); })
                ->get();

    foreach ($toUpdate as $r) {
        if ((float)($r->Taux ?? 0) === 0.0) continue;

        $prefix = $getPrefix($r->type, $r->categorie);
        $compte_tva = null;
        if ($prefix !== null) {
            $q = PlanComptable::where('societe_id', $societeId)
                              ->where('compte', 'like', $prefix . '%');
            if ($expectedLen && $expectedLen > 0) {
                $q->whereRaw('CHAR_LENGTH(compte) = ?', [$expectedLen]);
            }
            $first = $q->orderBy('compte')->first(['compte']);
            if ($first) $compte_tva = $first->compte;
            else {
                $fallback = PlanComptable::where('societe_id', $societeId)
                            ->where('compte', 'like', $prefix . '%')
                            ->orderBy('compte')
                            ->first(['compte']);
                $compte_tva = $fallback->compte ?? null;
            }
        }

        if ($compte_tva) {
            $r->compte_tva = $compte_tva;
            $r->save();
        }
    }

    // 6) Recharge toutes les racines pour la société (tri par type puis Num_racines)
    $racines = Racine::where('societe_id', $societeId)
                ->orderByRaw('LOWER(type) ASC')
                ->orderBy('Num_racines')
                ->get();

    // 7) Construire payload avec compte_options
    $payload = $racines->map(function($r) use ($societeId, $expectedLen, $getPrefix) {
        $prefix = $getPrefix($r->type, $r->categorie);

        $comptesQuery = PlanComptable::where('societe_id', $societeId);
        if ($prefix !== null) {
            $comptesQuery->where('compte', 'like', $prefix . '%');
        }

        if ($expectedLen && $expectedLen > 0) {
            $comptesQuery->whereRaw('CHAR_LENGTH(compte) = ?', [$expectedLen]);
        }

        $comptesRaw = $comptesQuery->orderBy('compte')->get(['compte','intitule']);

        if ($comptesRaw->isEmpty() && $prefix !== null) {
            $fallback = PlanComptable::where('societe_id', $societeId)
                        ->where('compte', 'like', $prefix . '%')
                        ->orderBy('compte')
                        ->get(['compte','intitule']);
            $comptesRaw = $fallback;
        }

        $comptes = $comptesRaw->mapWithKeys(fn($row) => [(string)$row->compte => "{$row->compte} - {$row->intitule}"])
                              ->toArray();

        $comptes['__ADD__'] = '+ Ajouter un compte…';

        $mouvementee = Fournisseur::where('societe_id', $societeId)
                        ->where('rubrique_tva', $r->Num_racines)
                        ->exists();

        return array_merge($r->toArray(), [
            'compte_options' => $comptes,
            'mouvementee'    => $mouvementee,
            'is_base'        => false,
            'original_Num_racines' => $r->Num_racines,
            'original_Nom_racines' => $r->Nom_racines,
            'original_Taux'        => $r->Taux,
            'original_type'        => $r->type,
            'original_categorie'   => $r->categorie,
            'original_compte_tva'  => ((float)($r->Taux ?? 0) === 0.0) ? '' : ($r->compte_tva ?? ''),
        ]);
    })->toArray();

    // --- Groupement par type TEL QUEL (pas de normalisation)
    $groupedRaw = collect($payload)
        ->groupBy(function($item) {
            $t = isset($item['type']) && trim((string)$item['type']) !== '' ? $item['type'] : 'Autres';
            return $t;
        })
        ->map(fn($group) => $group->values())
        ->toArray();

    // Ordre demandé EXACT
    $desiredOrder = [
        'CA non imposable',
        'CA imposable',
        'Les déductions',
    ];

    $ordered = [];
    foreach ($desiredOrder as $key) {
        if (array_key_exists($key, $groupedRaw)) {
            $ordered[$key] = $groupedRaw[$key];
            unset($groupedRaw[$key]);
        } else {
            $ordered[$key] = [];
        }
    }

    if (!empty($groupedRaw)) {
        ksort($groupedRaw, SORT_NATURAL | SORT_FLAG_CASE);
        foreach ($groupedRaw as $k => $v) {
            $ordered[$k] = $v;
        }
    }

    // --------- DÉTECTION SÛRE DE REQUÊTE AJAX / JSON ----------
    $isAjax = $request->ajax() // X-Requested-With: XMLHttpRequest
             || $request->header('X-Requested-With') === 'XMLHttpRequest' // sécurité
             || $request->query('ajax') === '1'; // forcer via ?ajax=1

    if ($isAjax) {
        // Si Tabulator attend un tableau (pas un objet groupé), tu peux renvoyer collect($ordered)->flatMap(...)
        // mais si tu veux renvoyer groupé (comme tu fais), on renvoie $ordered
        return response()->json($ordered);
    }

    // sinon renvoyer la vue normale
    return view('Rubrique_Tva', [
        'racines' => collect($payload),
        'racines_grouped' => collect($ordered),
    ]);
}





public function update(Request $request, $id)
{
    $societeId = $request->header('X-Societe-Id') ?? session('societeId');

    $racine = Racine::where('id', $id)
        ->where(function ($q) use ($societeId) {
            $q->where('societe_id', $societeId)
              ->orWhere('societe_id', 0);
        })
        ->first();

    if (! $racine) {
        return response()->json(['error' => 'Rubrique non trouvée.'], 404);
    }

    // Vérifie si la rubrique est mouvementée (utilisée)
    $isUsed = \App\Models\Fournisseur::where('societe_id', $societeId)
        ->where('rubrique_tva', $racine->Num_racines)
        ->exists();

    // Règles de validation dynamiques
    $rules = [];

    // Toujours autorisé : modification du compte_tva ou catégorie
    if ($request->has('compte_tva')) {
        $rules['compte_tva'] = 'nullable|string';
    }

    if ($request->has('categorie')) {
        $rules['categorie'] = 'nullable|string|max:100';
    }

    if (! $isUsed) {
        // Si la rubrique n’est pas utilisée, autorise aussi les champs critiques
        if ($request->has('Num_racines')) {
            $rules['Num_racines'] = [
                'required',
                Rule::unique('racines')->ignore($racine->id)->where(function ($query) use ($societeId) {
                    return $query->where('societe_id', $societeId);
                }),
            ];
        }

        if ($request->has('Nom_racines')) {
            $rules['Nom_racines'] = 'required|string|max:255';
        }

        if ($request->has('Taux')) {
            $rules['Taux'] = 'required|numeric|min:0';
        }

        if ($request->has('type')) {
            $rules['type'] = 'required|string|in:CA imposable,CA non imposable,Les déductions';
        }
    } else {
        // Si mouvementée, ces champs ne doivent pas être modifiés
        $protected = ['Num_racines', 'Nom_racines', 'Taux', 'type'];
        foreach ($protected as $field) {
            if ($request->has($field)) {
                return response()->json([
                    'error' => "Impossible de modifier le champ '$field' : la rubrique est utilisée par un fournisseur."
                ], 422);
            }
        }
    }

    $validated = $request->validate($rules);

    $racine->update($validated);

    return response()->json([
        'message' => 'Rubrique mise à jour avec succès.',
        'id' => $racine->id,
    ]);
}


    /**
     * Création d’une nouvelle rubrique
     */
public function store(Request $request)
{
        $societeId = session('societeId');

    $data = $request->validate([
        'Num_racines' => [
            'required',
            'string',
            'max:50',
            Rule::unique('racines')->where(function ($query) use ($societeId) {
                return $query->where('societe_id', $societeId);
            }),
        ],
        'Nom_racines' => 'required|string|max:255',
        'Taux'        => 'required|numeric',
        'type'        => 'required|string',
        'categorie'   => 'nullable|string',
        'compte_tva'  => 'nullable|string',
        'is_hidden'   => 'boolean',
        'societe_id'  => 'required|integer', // <-- Ajout validation obligatoire ici
    ],
    [
        'Num_racines.unique' => 'Ce code existe déjà pour votre société.',
    ]);

    $racine = Racine::create([
        'societe_id'  => $data['societe_id'],
        'Num_racines' => $data['Num_racines'],
        'Nom_racines' => $data['Nom_racines'],
        'Taux'        => $data['Taux'],
        'type'        => $data['type'],
        'categorie'   => $data['categorie']  ?? null,
        'compte_tva'  => $data['compte_tva'] ?? null,
        'is_hidden'   => $data['is_hidden']  ?? false,
    ]);

    return response()->json(['id' => $racine->id]);
}

    /**
     * Retourne les catégories distinctes pour un type donné
     */
    public function getCategories(Request $request)
    {
        $type      = $request->query('type');
        $societeId = session('societeId');

        $query = Racine::query()
            ->where(function($q) use ($societeId) {
                $q->where('societe_id', $societeId)
                  ->orWhere('societe_id', 0);
            })
            ->whereRaw('LOWER(type) = ?', [strtolower($type)])
            ->where('is_hidden', false);


        $liste = $query->distinct()->pluck('categorie')->toArray();

        return response()->json($liste);
    }

    /**
     * Retourne tous les comptes TVA correspondant au type
     * (utilisé pour autocomplete et ajout dynamique)
     */
    public function getCompteTvaType(Request $request)
    {
        $type = $request->query('type');
        $societeId = session('societeId');

        $t = strtolower($type);
        if ($t === 'les déductions') {
            $prefix = '345';
        } elseif ($t === 'ca imposable') {
            $prefix = '445';
        } else {
            // CA non imposable, on renvoie vide pour forcer le +Ajouter
            return response()->json([]);
        }

        $data = PlanComptable::where('societe_id', $societeId)
            ->where('compte', 'like', $prefix.'%')
            ->orderBy('compte')
            ->get(['compte','intitule']);

        return response()->json($data);
    }

    /**
     * Masque / remonte une rubrique
     */
    public function toggleVisibility($id)
    {
        $societeId = session('societeId');
        $racine = Racine::where(function($q) use ($societeId) {
                    $q->where('societe_id', $societeId)
                      ->orWhere('societe_id', 0);
                })->findOrFail($id);

        $racine->is_hidden = !$racine->is_hidden;
        $racine->save();

        return response()->json([
            'success' => true,
            'message' => $racine->is_hidden
                ? 'Rubrique masquée avec succès'
                : 'Rubrique affichée avec succès'
        ]);
    }

    /**
     * Recherche générique dans le plan comptable
     */
    public function searchPlanComptable(Request $request)
    {
        $q = $request->query('q', '');
        $societeId = session('societeId');

        $items = PlanComptable::where('societe_id', $societeId)
            ->where(function($qb) use ($q) {
                $qb->where('compte', 'like', "%{$q}%")
                   ->orWhere('intitule', 'like', "%{$q}%");
            })
            ->limit(50)
            ->get(['compte','intitule']);

        return response()->json($items);
    }

    /**
     * Enregistrement d’un nouveau compte dans le plan comptable
     */
    public function storePlanComptable(Request $request)
    {
        $societeId = session('societeId');
        $data = $request->validate([
            'compte'   => 'required|string|max:20',
            'intitule' => 'required|string|max:255',
        ]);

        $pc = PlanComptable::create([
            'societe_id' => $societeId,
            'compte'     => $data['compte'],
            'intitule'   => $data['intitule'],
        ]);

        return response()->json([
            'compte'   => $pc->compte,
            'intitule' => $pc->intitule,
        ]);
    }

    public function checkFournisseurs($id)
    {
        $societeId = session('societeId');

        try {
            // On ne cherche que la rubrique de cette société
            $racine = Racine::where('id', $id)
                            ->where('societe_id', $societeId)
                            ->first();

            if (! $racine) {
                return response()->json([
                    'error' => 'Rubrique non trouvée pour cette société.',
                    'used'  => false,
                ], 404);
            }

            // Vérifie dans fournisseurs de la même société
            $used = Fournisseur::where('societe_id', $societeId)
                               ->where('rubrique_tva', $racine->Num_racines)
                               ->exists();

            return response()->json(['used' => $used]);
        } catch (\Throwable $e) {
            return response()->json([
                'error' => 'Erreur lors de la vérification.',
                'used'  => false,
            ], 200);
        }
    }

    /**
     * DELETE /racines/{id}
     */
    public function destroy($id)
    {
        $societeId = session('societeId');

        try {
            // On ne supprime que la rubrique appartenant à cette société
            $racine = Racine::where('id', $id)
                            ->where('societe_id', $societeId)
                            ->first();

            if (! $racine) {
                return response()->json([
                    'error' => 'Rubrique non trouvée pour cette société.',
                ], 404);
            }

            // Vérifie avant suppression dans fournisseurs de la même société
            $isUsed = Fournisseur::where('societe_id', $societeId)
                                 ->where('rubrique_tva', $racine->Num_racines)
                                 ->exists();

            if ($isUsed) {
                return response()->json([
                    'error' => 'Impossible de supprimer : utilisée par un fournisseur.',
                ], 422);
            }

            $racine->delete();

            return response()->json([
                'message' => 'Rubrique supprimée avec succès.',
            ]);
        } catch (\Throwable $e) {
            return response()->json([
                'error' => 'Erreur lors de la suppression.',
            ], 500);
        }
    }
}
