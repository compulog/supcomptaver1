<?php

namespace App\Http\Controllers;


use Illuminate\Http\Request;
use App\Models\Racine;
use App\Models\PlanComptable;
use App\Models\Fournisseur;
use Illuminate\Validation\Rule;

class RacineController extends Controller
{
    /**
     * Affiche la vue et, en AJAX, retourne les rubriques
     * avec leurs options de compte_tva filtrées selon vos règles.
     */
  public function index(Request $request)
{
    $societeId = session('societeId');
    if (!$societeId) {
        return response()->json(['error' => 'ID de société manquant'], 400);
    }

    // Récupère toutes les rubriques pour cette société
    $racines = Racine::where(function ($q) use ($societeId) {
                    $q->where('societe_id', $societeId)
                      ->orWhere('societe_id', 0);
                })
                ->get();

    if ($request->ajax()) {
        $payload = $racines->map(function($r) use ($societeId) {
            // Vérifie si la rubrique est utilisée (mouvementée) dans la table fournisseurs
            $mouvementee = \App\Models\Fournisseur::where('societe_id', $societeId)
                                ->where('rubrique_tva', $r->Num_racines)
                                ->exists();

            // Si Taux = 0, on n'affiche pas de compte TVA
            if ((float) $r->Taux === 0.0) {
                return array_merge(
                    $r->toArray(),
                    [
                        'compte_options' => [],
                        'mouvementee' => $mouvementee,
                        // Champs originaux pour la comparaison côté JS
                        'original_Num_racines' => $r->Num_racines,
                        'original_Nom_racines' => $r->Nom_racines,
                        'original_Taux'        => $r->Taux,
                        'original_type'        => $r->type,
                        'original_categorie'   => $r->categorie,
                        'original_compte_tva'  => $r->compte_tva,
                    ]
                );
            }

            // Sinon, on applique la logique existante
            $t      = strtolower($r->type);
            $c      = strtolower($r->categorie ?? '');
            $prefix = null;

            if ($t === 'les déductions') {
                $prefix = ($c === 'immobilisations') ? '34551' : '34552';
            } elseif ($t === 'ca imposable') {
                $prefix = '4455';
            }

            $comptesRaw = PlanComptable::where('societe_id', $societeId)
                ->when($prefix !== null, function($q) use ($prefix) {
                    $q->where('compte', 'like', $prefix.'%');
                })
                ->orderBy('compte')
                ->get(['compte','intitule']);

            $comptes = $comptesRaw->mapWithKeys(function($row) {
                return [
                    (string)$row->compte => "{$row->compte} - {$row->intitule}"
                ];
            })->toArray();

            $comptes['__ADD__'] = '+ Ajouter un compte…';

            return array_merge(
                $r->toArray(),
                [
                    'compte_options' => $comptes,
                    'mouvementee' => $mouvementee,
                    // Champs originaux
                    'original_Num_racines' => $r->Num_racines,
                    'original_Nom_racines' => $r->Nom_racines,
                    'original_Taux'        => $r->Taux,
                    'original_type'        => $r->type,
                    'original_categorie'   => $r->categorie,
                    'original_compte_tva'  => $r->compte_tva,
                ]
            );
        });

        return response()->json($payload);
    }

    return view('Rubrique_Tva', compact('racines'));
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
        'societe_id'  => 'required|integer',
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
