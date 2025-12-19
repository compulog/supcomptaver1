<?php

namespace App\Http\Controllers;
use App\Imports\FournisseurImport;
use App\Models\Fournisseur;
use App\Models\Racine;
use App\Models\PlanComptable;
use App\Models\societe;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Maatwebsite\Excel\Facades\Excel;
use Illuminate\Support\Facades\Log;


class FournisseurController extends Controller
{
    public function index()
    {
        return view('fournisseurs.index');
    }

    public function show($id)
    {
        $fournisseur = Fournisseur::findOrFail($id);
        return response()->json($fournisseur, 200);
    }

    /**
     * Endpoint JSON utilisé par Tabulator
     */
    // public function getData(Request $request)
    // {
    //     Log::debug('getData appelé', ['session' => session()->all()]);

    //     $societeId = session('societeId');
    //     Log::debug('societeId', ['societeId' => $societeId]);

    //     if (!$societeId) {
    //         Log::warning('societeId manquant en session');
    //         return response()->json(['error' => 'Aucune société sélectionnée dans la session'], 400);
    //     }

    //     $fournisseurs = Fournisseur::where('societe_id', $societeId)
    //         ->select('id','compte','intitule','identifiant_fiscal','ICE','rubrique_tva','designation','contre_partie')
    //         ->orderBy('compte')
    //         ->get()
    //         ->map(function ($f) {
    //             $arr = $f->toArray();
    //             $arr += [
    //                 'missing_compte' => 0,
    //                 'missing_intitule' => 0,
    //                 'invalid_compte_format' => 0,
    //                 'invalid_length_compte' => 0,
    //                 'highlight_ice' => null,
    //                 'invalid' => 0,
    //             ];
    //             return $arr;
    //         });

    //     $comptesPlan = PlanComptable::where('societe_id', $societeId)
    //         ->where('compte', 'like', '4411%')
    //         ->select('id','compte','intitule')
    //         ->orderBy('compte')
    //         ->get();

    //     $comptesMap = $comptesPlan->mapWithKeys(function($c){
    //         return [$c->compte => ($c->compte . ($c->intitule ? ' - ' . $c->intitule : ''))];
    //     })->toArray();

    //     Log::debug('comptesPlan count', ['count' => $comptesPlan->count()]);

    //     return response()->json([
    //         'fournisseurs' => $fournisseurs,
    //         'comptes_plan' => $comptesPlan,
    //         'comptes_map'  => $comptesMap,
    //     ], 200);
    // }


  public function getData(Request $request)
{
    Log::debug('getData Fournisseurs (plan_comptable inclus) appelé', ['session' => session()->all(), 'request' => $request->all()]);

    $societeId = session('societeId');

    if (!$societeId) {
        return response()->json(['error' => 'Aucune société sélectionnée dans la session'], 400);
    }

    // Récupérer la société pour obtenir nombre_chiffre_compte
    $societe = \App\Models\Societe::find($societeId);
    $length = $societe ? (int) $societe->nombre_chiffre_compte : 0;

    if ($length <= 0) {
        return response()->json(['error' => 'La longueur des comptes n\'est pas définie pour la société'], 400);
    }

    // Helper : normalisation / formatage selon la règle convenue
    $formatCompte = function($compteRaw, int $len) {
        $compteRaw = $compteRaw ?? '';
        $digits = preg_replace('/\D/', '', (string)$compteRaw);
        $curLen = strlen($digits);

        if ($curLen === 0) {
            // si vide -> 00..01 (dernier chiffre = 1)
            return str_repeat('0', max(0, $len - 1)) . '1';
        }

        if ($curLen >= $len) {
            // tronquer à gauche (garder les premiers chiffres)
            return substr($digits, 0, $len);
        }

        // compléter : insérer des zéros AVANT le dernier chiffre
        $prefix = $curLen > 1 ? substr($digits, 0, $curLen - 1) : '';
        $last   = substr($digits, -1);
        $zerosToAdd = $len - $curLen;

        return $prefix . str_repeat('0', $zerosToAdd) . $last;
    };

    // -----------------------------------------------------------------------
    // 1) Charger fournisseurs existants (ordre par id pour déterminisme)
    // -----------------------------------------------------------------------
    $fournisseursRaw = \App\Models\Fournisseur::where('societe_id', $societeId)
        ->select('id','compte','intitule','identifiant_fiscal','ICE','rubrique_tva','designation','contre_partie')
        ->orderBy('id')
        ->get();

    $seen = []; // map normalized_compte => true
    $fournisseurs = collect();

    foreach ($fournisseursRaw as $f) {
        $raw = $f->compte ?? '';
        $normalized = $formatCompte($raw, $length);

        // ne renvoyer que si la normalisation a bien donné la longueur voulue
        if (strlen($normalized) !== $length) {
            continue;
        }

        // enregistrer comme vu pour éviter doublons venant du plan_comptable
        if (!isset($seen[$normalized])) {
            $seen[$normalized] = true;

            $fournisseurs->push([
                'id' => $f->id,
                'compte' => $raw,
                'normalized_compte' => $normalized,
                'intitule' => $f->intitule,
                'identifiant_fiscal' => $f->identifiant_fiscal,
                'ICE' => $f->ICE,
                'rubrique_tva' => $f->rubrique_tva,
                'designation' => $f->designation,
                'contre_partie' => $f->contre_partie,
                'source' => 'fournisseur',
                // flags pour front
                'missing_compte' => 0,
                'missing_intitule' => 0,
                'invalid_compte_format' => 0,
                'invalid_length_compte' => 0,
                'highlight_ice' => null,
                'invalid' => 0,
            ]);
        }
    }

    // -----------------------------------------------------------------------
    // 2) Charger comptes du plan comptable commençant par 4411*
    //    et ajouter uniquement ceux qui ne sont pas déjà présents dans fournisseurs
    // -----------------------------------------------------------------------
    $comptesPlanRaw = \App\Models\PlanComptable::where('societe_id', $societeId)
        ->where('compte', 'like', '4411%')
        ->select('id','compte','intitule')
        ->orderBy('id')
        ->get();

    foreach ($comptesPlanRaw as $cp) {
        $raw = $cp->compte ?? '';
        $normalized = $formatCompte($raw, $length);

        // ne renvoyer que si la normalisation a bien donné la longueur voulue
        if (strlen($normalized) !== $length) {
            continue;
        }

        // si déjà vu dans fournisseurs -> skip (on respecte la priorité fournisseurs)
        if (isset($seen[$normalized])) {
            continue;
        }

        // sinon, on ajoute en tant que fournisseur issu du plan comptable
        $seen[$normalized] = true;

        $fournisseurs->push([
            'id' => 'plan-'.$cp->id,              // id composite pour front (éviter collision)
            'compte' => $raw,
            'normalized_compte' => $normalized,
            'intitule' => $cp->intitule,
            'identifiant_fiscal' => null,
            'ICE' => null,
            'rubrique_tva' => null,
            'designation' => null,
            'contre_partie' => null,
            'source' => 'plan_comptable',
            'missing_compte' => 0,
            'missing_intitule' => 0,
            'invalid_compte_format' => 0,
            'invalid_length_compte' => 0,
            'highlight_ice' => null,
            'invalid' => 0,
        ]);
    }

    // -----------------------------------------------------------------------
    // 3) Construire comptes_map utile pour selects (depuis comptesPlanRaw filtré)
    //    on renvoie map uniquement pour ceux des comptesPlanRaw (après normalisation)
    // -----------------------------------------------------------------------
    $comptesMap = [];
    foreach ($comptesPlanRaw as $cp) {
        $normalized = $formatCompte($cp->compte ?? '', $length);
        if (strlen($normalized) !== $length) continue;
        // si ce compte est déjà utilisé par un fournisseur, ne pas lister dans la map
        if (isset($seen[$normalized]) && collect($fournisseurs)->contains(fn($x) => $x['normalized_compte'] === $normalized && $x['source'] === 'fournisseur')) {
            // si existant comme fournisseur, on peut quand même l'ignorer dans la map
            // (ou inclure selon préférence) — ici on inclut tous les comptesPlanRaw non-filterés
        }
        $comptesMap[$normalized] = ($normalized . ($cp->intitule ? ' - ' . $cp->intitule : ''));
    }

    // renvoyer la liste fusionnée (fournisseurs + plan_comptable non-dup)
    return response()->json([
        'fournisseurs' => $fournisseurs->values(),
        'comptes_plan' => [],      // vide pour compatibilité front (tu peux renvoyer $comptesPlanRaw si besoin)
        'comptes_map' => $comptesMap,
        'length_choisie' => $length,
        'count' => $fournisseurs->count(),
    ], 200);
}

    /**
     * Store : normalise rubrique_tva (array|string) -> "112/110/153"
     */
 public function store(Request $request)
    {
        //  dd($request->all());
        $societeId = session('societeId');
        if (!$societeId) {
            return response()->json(['error' => 'Aucune société sélectionnée dans la session'], 400);
        }

        $validatedData = $request->validate([
            'compte'            => 'nullable|string|max:' . ($request->nombre_chiffre_compte ?? 255),
            'intitule'          => 'required|string|max:255',
            'identifiant_fiscal'=> 'nullable|string|max:255',
            'ICE'               => 'nullable|string|max:255',
            'nature_operation'  => 'nullable|string|max:255',
            'rubrique_tva'      => 'nullable|string|max:1000',
            'designation'       => 'nullable|string|max:255',
            'contre_partie'     => 'nullable|string|max:255',
            'ville'             => 'nullable|string|max:255',
            'adresse'           => 'nullable|string|max:255',
            'RC'                => 'nullable|string|max:100',
            'delai_p'           => 'nullable|string|max:100',
        ]);

        // Prefer the dedicated concat field coming from front
        $concat = $request->input('rubrique_tva_concat', $request->input('rubrique_tva'));
        if ($concat) {
            $validatedData['rubrique_tva'] = $this->normalizeRubriqueConcat($concat);
        } else {
            // If nothing provided, ensure field exists (null or empty)
            $validatedData['rubrique_tva'] = $validatedData['rubrique_tva'] ?? '';
        }

        if (empty($validatedData['compte'])) {
            // If you already have getNextCompte implemented elsewhere keep it,
            // otherwise we attempt a simple fallback.
            if (method_exists($this, 'getNextCompte')) {
                $validatedData['compte'] = $this->getNextCompte($societeId);
            } else {
                // fallback: create a pseudo-compte by taking max numeric and +1
                $max = PlanComptable::where('societe_id', $societeId)->max('compte');
                $candidate = is_numeric($max) ? ((int)$max + 1) : date('YmdHis');
                $validatedData['compte'] = (string)$candidate;
            }
        }

        $validatedData['societe_id'] = $societeId;

        // Vérifier si un fournisseur avec ce compte existe déjà pour cette société
        $existingFournisseur = Fournisseur::where('societe_id', $societeId)
            ->where('compte', $validatedData['compte'])
            ->first();

        if ($existingFournisseur) {
            return response()->json([
                'error' => 'Ce compte existe déjà, vous ne pouvez pas le créer.'
            ], 422);
        }

        DB::beginTransaction();
        try {
            $fournisseur = Fournisseur::create($validatedData);

            // create plan comptable entry
            PlanComptable::create([
                'societe_id' => $societeId,
                'compte'     => $validatedData['compte'],
                'intitule'   => $validatedData['intitule'],
            ]);

            DB::commit();
            return response()->json([
                'success'     => true,
                'fournisseur' => $fournisseur,
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Erreur lors de la création du fournisseur : ' . $e->getMessage());
            return response()->json([
                'error'   => 'Une erreur est survenue lors de la création du fournisseur.',
                'details' => $e->getMessage()
            ], 500);
        }
    }

    public function checkCompte(Request $request)
    {
        $exists = Fournisseur::where('societe_id', $request->societe_id)
                    ->where('compte', $request->compte)
                    ->exists();

        return response()->json([
            'exists'  => $exists,
            'message' => $exists ? 'Ce compte existe déjà, vous ne pouvez pas le créer' : ''
        ]);
    }

    /**
     * Edit: return fournisseur + rubriques as array
     */
    public function edit($id)
    {
        $fournisseur = Fournisseur::findOrFail($id);

        // Split and normalize the rubriques (from "112/110/153" to ['112','110','153'])
        $raw = $fournisseur->rubrique_tva ?? '';
        $rubriques = [];
        if ($raw !== null && trim($raw) !== '') {
            // split on / , ; and keep numeric tokens
            $tokens = preg_split('/[\/,;]+/', $raw);
            foreach ($tokens as $t) {
                $t = trim($t);
                if ($t === '') continue;
                if (preg_match('/^\d+$/', $t)) $rubriques[] = $t;
            }
        }

        return response()->json([
            'fournisseur' => $fournisseur,
            'rubriques' => array_values($rubriques),
        ]);
    }

    /**
     * Update: replace rubrique_tva entirely with provided concat string.
     * Accepts rubrique_tva_concat from the front (preferred).
     */
    public function update(Request $request, $id)
    {
        $validator = Validator::make($request->all(), [
            'compte' => 'required|string|max:255',
            'intitule' => 'required|string|max:255',
            'identifiant_fiscal' => 'nullable|string|max:255',
            'ICE' => 'nullable|string|max:15',
            'nature_operation' => 'nullable|string',
            'rubrique_tva' => 'nullable|string',
            'designation' => 'nullable|string|max:255',
            'contre_partie' => 'nullable|string|max:255',
            'invalid' => 'nullable|boolean',
            'ville' => 'nullable|string|max:255',
            'adresse' => 'nullable|string|max:255',
            'RC' => 'nullable|string|max:100',
            'rc' => 'nullable|string|max:100',
            'delai_p' => 'nullable|string|max:100',
            'rubrique_tva_concat' => 'nullable|string|max:1000',
        ]);

        if ($validator->fails()) {
            return response()->json(['message' => 'Erreur de validation', 'errors' => $validator->errors()], 422);
        }

        try {
            DB::beginTransaction();

            $fournisseur = Fournisseur::findOrFail($id);

            // Assignations principales
            $fournisseur->compte = $request->input('compte');
            $fournisseur->intitule = $request->input('intitule');
            $fournisseur->identifiant_fiscal = $request->input('identifiant_fiscal');
            $fournisseur->ICE = $request->input('ICE');
            $fournisseur->nature_operation = $request->input('nature_operation');
            $fournisseur->designation = $request->input('designation');
            $fournisseur->contre_partie = $request->input('contre_partie');

            // RC support both RC or rc field from front
            $rcValue = $request->input('RC', $request->input('rc', null));
            $fournisseur->RC = $rcValue;

            // other simple fields
            $fournisseur->ville = $request->input('ville');
            $fournisseur->adresse = $request->input('adresse');
            $fournisseur->delai_p = $request->input('delai_p');

            // --- Rubriques: replace entirely using rubrique_tva_concat OR rubrique_tva ---
            $concat = $request->input('rubrique_tva_concat', $request->input('rubrique_tva'));
            if ($concat !== null) {
                $fournisseur->rubrique_tva = $this->normalizeRubriqueConcat($concat);
            } else {
                // if not provided, leave existing value untouched
            }

            // Calcul automatique du champ invalid basé sur la longueur du compte
            $nombre_chiffre_compte = optional($fournisseur->societe)->nombre_chiffre_compte ?? null;
            $compte = (string) $request->input('compte', '');
            if ($nombre_chiffre_compte) {
                $fournisseur->invalid = (strlen($compte) != (int) $nombre_chiffre_compte) ? 1 : 0;
            } else {
                $fournisseur->invalid = $request->input('invalid', $fournisseur->invalid ?? 0);
            }

            $fournisseur->save();

            DB::commit();

            return response()->json([
                'message' => 'Fournisseur mis à jour avec succès',
                'fournisseur' => $fournisseur->fresh(),
            ], 200);

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Erreur update fournisseur: '.$e->getMessage());
            return response()->json(['message' => 'Erreur serveur lors de la mise à jour', 'error' => $e->getMessage()], 500);
        }
    }

    /**
     * Normalize a raw concat string into tokens joined by '/'.
     * Accepts "112/110/153" or "112,110;153" etc. Returns "112/110/153" or ''.
     */
    protected function normalizeRubriqueConcat($raw)
    {
        if ($raw === null) return '';
        // split by slash, comma, semicolon, spaces; keep only numeric tokens
        $tokens = preg_split('/[\/,;|\s]+/', (string)$raw);
        $clean = [];
        foreach ($tokens as $t) {
            $t = trim($t);
            if ($t === '') continue;
            // accept only digits
            if (preg_match('/^\d+$/', $t)) $clean[] = $t;
        }
        // remove duplicates but preserve order
        $clean = array_values(array_unique($clean));
        return count($clean) ? implode('/', $clean) : '';
    }


public function getRubriquesTva()
{
    // 1) Vérification de la société active
    $societeId = session('societeId');
    if (empty($societeId)) {
        return response()->json([
            'error' => 'Aucune société sélectionnée dans la session.'
        ], 400);
    }

    // 2) Numéros de racines à exclure
    $exclusions = ['147', '151', '152', '148', '144'];

    // 3) Récupération des rubriques liées à la société active
    $rubriques = Racine::select('Num_racines', 'categorie', 'Nom_racines', 'Taux')
        ->where('societe_id', $societeId) // ✅ Filtrage par société
        ->where('type', 'Les déductions')
        ->whereNotIn('Num_racines', $exclusions)
        ->orderBy('categorie', 'desc') // Ordre inversé
        ->get();

    // 4) Regroupement par catégorie principale
    $categoriesTemp = [];
    foreach ($rubriques as $rubrique) {
        [$main, $sub] = array_map('trim', explode('/', $rubrique->categorie) + [1 => null]);

        if (!isset($categoriesTemp[$main])) {
            $categoriesTemp[$main] = [
                'subCategories' => [],
                'rubriques'     => [],
            ];
        }

        if ($sub && !in_array($sub, $categoriesTemp[$main]['subCategories'])) {
            $categoriesTemp[$main]['subCategories'][] = $sub;
        }

        $categoriesTemp[$main]['rubriques'][] = [
            'Num_racines' => $rubrique->Num_racines,
            'Nom_racines' => $rubrique->Nom_racines,
            'Taux'        => $rubrique->Taux,
        ];
    }

    // 5) Construction de la structure finale
    $categories = [];
    $counter = 1;
    foreach (array_keys($categoriesTemp) as $name) {
        $data = $categoriesTemp[$name];
        $categories[] = [
            'categoryId'    => $counter,
            'categoryName'  => "$counter. $name",
            'subCategories' => $data['subCategories'],
            'rubriques'     => $data['rubriques'],
        ];
        $counter++;
    }

    // 6) Si aucune rubrique trouvée
    if (empty($categories)) {
        return response()->json([
            'message' => 'Aucune rubrique trouvée pour cette société.'
        ], 404);
    }

    // 7) Retour de la réponse JSON
    return response()->json([
        'categories' => $categories,
    ]);
}

  /**
     * Génère un compte unique pour la société donnée.
     *
     * @param int $societeId
     * @return string
     */
public function getNextCompte($societeId)
{
    // Récupérer la société
    $societe = Societe::find($societeId);

    if (!$societe) {
        return response()->json(['success' => false, 'message' => 'Société introuvable'], 404);
    }

    $nombreChiffres = $societe->nombre_chiffre_compte; // Nombre de chiffres pour le compte
    $prefix = '4411'; // Préfixe des comptes

    // Valider le nombre de chiffres du compte
    if ($nombreChiffres < strlen($prefix) + 1) {
        return response()->json(['success' => false, 'message' => 'Le nombre de chiffres du compte est trop court.'], 400);
    }

    // Récupérer tous les comptes pour cette société triés par ordre croissant
    $comptesExistants = Fournisseur::where('societe_id', $societeId)
        ->where('compte', 'like', $prefix . '%')
        ->orderBy('compte', 'asc')
        ->pluck('compte')
        ->toArray();

    // Si aucun compte n'existe, retourner le premier
    if (empty($comptesExistants)) {
        $chiffresRestants = $nombreChiffres - strlen($prefix);
        $firstCompte = $prefix . str_pad('1', $chiffresRestants, '0', STR_PAD_LEFT);
        return response()->json(['success' => true, 'nextCompte' => $firstCompte]);
    }

    // Extraire les séquences numériques des comptes existants
    $chiffresRestants = $nombreChiffres - strlen($prefix);
    $sequences = array_map(function ($compte) use ($prefix) {
        return (int)substr($compte, strlen($prefix));
    }, $comptesExistants);

    // Rechercher un trou dans la séquence
    $nextSequence = null;
    for ($i = 1; $i <= max($sequences); $i++) {
        if (!in_array($i, $sequences)) {
            $nextSequence = $i;
            break;
        }
    }

    // Si aucun trou n'est trouvé, prendre le numéro suivant après le plus grand
    if ($nextSequence === null) {
        $nextSequence = max($sequences) + 1;
    }

    // Générer le prochain compte avec le préfixe et le format approprié
    $nextCompte = $prefix . str_pad($nextSequence, $chiffresRestants, '0', STR_PAD_LEFT);

    return response()->json(['success' => true, 'nextCompte' => $nextCompte]);
}



public function getComptes()
{
    // Récupérer l'ID de la société depuis la session
    $societeId = session('societeId');

    // Vérifier si l'ID de la société est défini
    if (!$societeId) {
        return response()->json(['error' => 'Aucune société sélectionnée'], 400);
    }

    // Récupérer les comptes liés à cette société
    $comptes = PlanComptable::where('societe_id', $societeId) // Filtrer par société
        ->where(function ($query) {
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
        ->get(['compte', 'intitule']); // Récupérer uniquement les champs nécessaires

    return response()->json($comptes);
}






    // Affiche le formulaire d'importation
    public function showImportForm()
    {
        return view('import'); // La vue avec le formulaire d'importation
    }

    /**
     * Méthode pour gérer l'importation des fournisseurs



     * Gère l'importation du fichier Excel
     */
public function import(Request $request)
{
    $request->validate([
        'file' => 'required|file|mimes:xlsx,xls,csv',
        'colonne_compte' => 'required|integer|min:1',
        'colonne_intitule' => 'required|integer|min:1',
        'colonne_identifiant_fiscal' => 'nullable|integer|min:1',
        'colonne_ICE'                => 'nullable|integer|min:1',
        'colonne_nature_operation'   => 'nullable|integer|min:1',
        'colonne_rubrique_tva'       => 'nullable|integer|min:1',
        'colonne_designation'        => 'nullable|integer|min:1',
        'colonne_contre_partie'      => 'nullable|integer|min:1',
        'colonne_RC'      => 'nullable|integer|min:1',
        'colonne_ville'   => 'nullable|integer|min:1',
        'colonne_adresse' => 'nullable|integer|min:1',
        'colonne_delai_p' => 'nullable|integer|min:1',
    ]);

    try {
        $societe_id = session('societeId');
        if (! $societe_id) {
            return redirect()->back()->with('error', 'Société non définie en session.');
        }

        $societe = \App\Models\Societe::findOrFail($societe_id);
        $nombre_chiffre_compte = $societe->nombre_chiffre_compte;

        $mapping = [
            'compte'                => ((int)$request->input('colonne_compte')) - 1,
            'intitule'              => ((int)$request->input('colonne_intitule')) - 1,
            'identifiant_fiscal'    => $request->filled('colonne_identifiant_fiscal') ? ((int)$request->input('colonne_identifiant_fiscal') - 1) : null,
            'ICE'                   => $request->filled('colonne_ICE') ? ((int)$request->input('colonne_ICE') - 1) : null,
            'nature_operation'      => $request->filled('colonne_nature_operation') ? ((int)$request->input('colonne_nature_operation') - 1) : null,
            'rubrique_tva'          => $request->filled('colonne_rubrique_tva') ? ((int)$request->input('colonne_rubrique_tva') - 1) : null,
            'designation'           => $request->filled('colonne_designation') ? ((int)$request->input('colonne_designation') - 1) : null,
            'contre_partie'         => $request->filled('colonne_contre_partie') ? ((int)$request->input('colonne_contre_partie') - 1) : null,
            'RC'                    => $request->filled('colonne_RC') ? ((int)$request->input('colonne_RC') - 1) : null,
            'ville'                 => $request->filled('colonne_ville') ? ((int)$request->input('colonne_ville') - 1) : null,
            'adresse'               => $request->filled('colonne_adresse') ? ((int)$request->input('colonne_adresse') - 1) : null,
            'delai_p'               => $request->filled('colonne_delai_p') ? ((int)$request->input('colonne_delai_p') - 1) : null,
        ];

        Excel::import(new \App\Imports\FournisseurImport($societe_id, $nombre_chiffre_compte, $mapping), $request->file('file'));

        return $request->ajax()
            ? response()->json(['success' => 'Importation réussie'])
            : redirect()->back()->with('success', 'Importation réussie !');

    } catch (\Maatwebsite\Excel\Validators\ValidationException $e) {
        $failures = $e->failures();
        $messages = [];
        foreach ($failures as $f) {
            $messages[] = "Ligne " . $f->row() . " : " . implode(', ', $f->errors());
        }
        return redirect()->back()->with('error', 'Erreurs d\'import : ' . implode(' | ', $messages));

    } catch (\Exception $e) {
        Log::error('Import fournisseurs error: '.$e->getMessage());
        return redirect()->back()->withInput()->with('error', 'Erreur lors de l’import : ' . $e->getMessage());
    }
}


    /**
     * Parse le fichier Excel (en ignorant la première ligne).
     */



    public function verifierCompte(Request $request)
    {
        // Récupérer le compte et l'ID de la société
        $compte = $request->input('compte');
        $societeId = $request->input('societe_id');

        // Vérifier si le compte existe déjà pour cette société
        $exists = Fournisseur::where('compte', $compte)
                             ->where('societe_id', $societeId)
                             ->exists();

        // Retourner une réponse JSON avec le résultat
        return response()->json(['exists' => $exists]);
    }


    public function destroy(Request $request, $id)
    {
        // Récupérer l'ID de la société depuis la session
        $sessionSocieteId = session('societeId');
        if (!$sessionSocieteId) {
            return response()->json(['error' => 'Aucune société sélectionnée dans la session'], 400);
        }

        // Récupérer le fournisseur à supprimer
        $fournisseur = Fournisseur::findOrFail($id);

        // Vérifier que le fournisseur appartient à la société de la session
        if ($fournisseur->societe_id != $sessionSocieteId) {
            return response()->json(['error' => "Ce fournisseur n'appartient pas à la société sélectionnée"], 400);
        }

        // Vérifier dans la table 'operation_courante' que le fournisseur n'est pas mouvementé :
        // c'est-à-dire qu'il n'existe pas d'enregistrement où operation_courante.compte = $fournisseur->compte
        // pour la même société
        $isMouvemented = DB::table('operation_courante')
                            ->where('compte', $fournisseur->compte)
                            ->where('societe_id', $sessionSocieteId)
                            ->exists();

        if ($isMouvemented) {
            return response()->json([
                'error' => "Ce fournisseur '{$fournisseur->compte}' est déjà mouvementé, impossible de le supprimer !"
            ], 400);
        }

        // Procéder à la suppression
        $fournisseur->delete();
        return response()->json(['message' => 'Fournisseur supprimé avec succès.']);
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

        // Parcourir chaque fournisseur et vérifier s'il peut être supprimé
        foreach ($ids as $id) {
            $fournisseur = Fournisseur::find($id);
            if ($fournisseur) {
                // Vérifier que le fournisseur appartient à la société de la session
                if ($fournisseur->societe_id != $sessionSocieteId) {
                    $nonDeletable[] = $fournisseur->compte;
                    continue;
                }

                // Vérifier dans la table 'operation_courante' si le fournisseur est mouvementé :
                // c'est-à-dire que operation_courante.compte = $fournisseur->compte pour la même société
                $isMouvemented = DB::table('operation_courante')
                                    ->where('compte', $fournisseur->compte)
                                    ->where('societe_id', $sessionSocieteId)
                                    ->exists();
                if ($isMouvemented) {
                    $nonDeletable[] = $fournisseur->compte;
                } else {
                    $deletableIds[] = $fournisseur->id;
                }
            }
        }

        if (!empty($nonDeletable)) {
            return response()->json([
                'error' => "Les fournisseurs suivants sont déjà mouvementés et ne peuvent pas être supprimés : " . implode(', ', $nonDeletable)
            ], 400);
        }

        try {
            $deletedCount = Fournisseur::whereIn('id', $deletableIds)->delete();
            return response()->json([
                'status' => 'success',
                'message' => "{$deletedCount} fournisseurs supprimés"
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
