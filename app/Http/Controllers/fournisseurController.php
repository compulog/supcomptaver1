<?php

namespace App\Http\Controllers;
use App\Imports\FournisseurImport;
use App\Models\Fournisseur;
use App\Models\racine;
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
    {// Récupérer l'ID de la société dans la session
        $societeId = session('societeId');

        // Vérifier si l'ID de la société existe
        if (!$societeId) {
            return response()->json(['error' => 'Aucune société sélectionnée dans la session'], 400);
        }

        // Récupérer tous les plans comptables pour la société spécifiée
        $fournisseurs = Fournisseur::where('societe_id', $societeId)->get();

        return response()->json($fournisseurs);
    }



    public function show($id)
    {
        $fournisseur = Fournisseur::findOrFail($id);
        return response()->json($fournisseur, 200);
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
        $fournisseurs = Fournisseur::where('societe_id', $societeId)->get();

        return response()->json($fournisseurs);
    }

    public function store(Request $request)
{
    $societeId = session('societeId');
    if (!$societeId) {
        return response()->json(['error' => 'Aucune société sélectionnée dans la session'], 400);
    }

    $validatedData = $request->validate([
        'compte' => 'nullable|string|max:' . ($request->nombre_chiffre_compte ?? 255),
        'intitule' => 'required|string|max:255',
        'identifiant_fiscal' => 'nullable|string|max:255',
        'ICE' => 'nullable|string|max:255',
        'nature_operation' => 'nullable|string|max:255',
        'rubrique_tva' => 'nullable|string|max:255',
        'designation' => 'nullable|string|max:255',
        'contre_partie' => 'nullable|string|max:255',
    ]);

    if (empty($validatedData['compte'])) {
        $validatedData['compte'] = $this->getNextCompte($societeId);
    }
    $validatedData['societe_id'] = $societeId;

    // Vérifier si un fournisseur avec ce compte existe déjà pour cette société
    $existingFournisseur = Fournisseur::where('societe_id', $societeId)
        ->where('compte', $validatedData['compte'])
        ->first();

    if ($existingFournisseur) {
        return response()->json([
            'error' => 'Le fournisseur avec ce compte existe déjà pour la société sélectionnée.'
        ], 422);
    }

    DB::beginTransaction();
    try {
        $fournisseur = Fournisseur::create($validatedData);
        PlanComptable::create([
            'societe_id' => $societeId,
            'compte' => $validatedData['compte'],
            'intitule' => $validatedData['intitule'],
        ]);
        DB::commit();
        return response()->json([
            'success' => true,
            'fournisseur' => $fournisseur,
        ]);
    } catch (\Exception $e) {
        DB::rollBack();
        Log::error('Erreur lors de la création du fournisseur : ' . $e->getMessage());
        return response()->json([
            'error' => 'Une erreur est survenue lors de la création du fournisseur.',
            'details' => $e->getMessage()
        ], 500);
    }
}

public function checkCompte(Request $request)
{
    $request->validate([
        'compte' => 'required|string',
        'societe_id' => 'required|integer'
    ]);

    $exists = Fournisseur::where('societe_id', $request->societe_id)
                ->where('compte', $request->compte)
                ->exists();

    return response()->json(['exists' => $exists]);
}





 // Méthode pour afficher le formulaire d'édition
 public function edit($id)
 {
 $fournisseur = Fournisseur::findOrFail($id);
return response()->json($fournisseur);


 }

 // Méthode pour mettre à jour le fournisseur
 public function update(Request $request, $id)
{
    // Validation des données
    $validator = Validator::make($request->all(), [
        'compte' => 'required|string|max:255',
        'intitule' => 'required|string|max:255',
        'identifiant_fiscal' => 'nullable|string|max:255',
        'ICE' => 'nullable|string|max:15',
        'nature_operation' => 'nullable|string',
        'rubrique_tva' => 'nullable|string',
        'designation' => 'nullable|string|max:255',
        'contre_partie' => 'nullable|string|max:255',
        'invalid' => 'nullable|boolean', // Ajout de la validation pour le champ invalid
    ]);

    if ($validator->fails()) {
        return response()->json(['message' => 'Erreur de validation', 'errors' => $validator->errors()], 422);
    }

    // Mise à jour des données
    $fournisseur = Fournisseur::findOrFail($id);

    $fournisseur->compte = $request->input('compte');
    $fournisseur->intitule = $request->input('intitule');
    $fournisseur->identifiant_fiscal = $request->input('identifiant_fiscal');
    $fournisseur->ICE = $request->input('ICE');
    $fournisseur->nature_operation = $request->input('nature_operation');
    $fournisseur->rubrique_tva = $request->input('rubrique_tva');
    $fournisseur->designation = $request->input('designation');
    $fournisseur->contre_partie = $request->input('contre_partie');

    // Vérification et mise à jour de la validité (invalid)
    $nombre_chiffre_compte = $fournisseur->societe->nombre_chiffre_compte; // Obtenez la longueur attendue
    $compte = $request->input('compte');
    $fournisseur->invalid = (strlen($compte) != $nombre_chiffre_compte) ? 1 : 0; // Calcul automatique basé sur le compte

    $fournisseur->save(); // Enregistrer les modifications

    return response()->json([
        'message' => 'Fournisseur mis à jour avec succès',
        'fournisseur' => $fournisseur,
    ], 200);
}




 // Récupère les rubriques TVA pour un type d'opération 'Achat'
 public function getRubriquesTva()
 {
     // Liste des numéros de racines à exclure
     $exclusions = ['190', '182', '200', '201', '205'];

     $rubriques = Racine::select('Num_racines','categorie', 'Nom_racines', 'Taux' )
         ->where('type', 'Achat')
         ->whereNotIn('Num_racines', $exclusions)  // Exclure les numéros de racines spécifiés
         ->get();

     $rubriquesParCategorie = [];
     foreach ($rubriques as $rubrique) {
         $rubriquesParCategorie[$rubrique->categorie]['rubriques'][] = [
             'Nom_racines' => $rubrique->Nom_racines,
             'Num_racines' => $rubrique->Num_racines,
             'Taux' => $rubrique->Taux,
         ];
     }

     return response()->json(['rubriques' => $rubriquesParCategorie]);
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
    // Valider le fichier et les paramètres de colonnes
    $request->validate([
        'file' => 'required|file|mimes:xlsx,xls,csv',
        'colonne_compte' => 'required|integer|min:1',
        'colonne_intitule' => 'required|integer|min:1',
        // Les autres colonnes sont optionnelles mais doivent être entières si renseignées
        'colonne_identifiant_fiscal' => 'nullable|integer|min:1',
        'colonne_ICE' => 'nullable|integer|min:1',
        'colonne_nature_operation' => 'nullable|integer|min:1',
        'colonne_rubrique_tva' => 'nullable|integer|min:1',
        'colonne_designation' => 'nullable|integer|min:1',
        'colonne_contre_partie' => 'nullable|integer|min:1',
    ]);

    try {
        // Récupérer l'ID de la société et le nombre de chiffres du compte depuis la session
        $societe_id = session('societeId');
        $societe = Societe::findOrFail($societe_id);
        $nombre_chiffre_compte = $societe->nombre_chiffre_compte;

        // Récupérer le mapping des colonnes depuis le formulaire
        $mapping = [
            'colonne_compte'            => (int)$request->input('colonne_compte'),
            'colonne_intitule'          => (int)$request->input('colonne_intitule'),
            'colonne_identifiant_fiscal'=> $request->input('colonne_identifiant_fiscal') ? (int)$request->input('colonne_identifiant_fiscal') : null,
            'colonne_ICE'               => $request->input('colonne_ICE') ? (int)$request->input('colonne_ICE') : null,
            'colonne_nature_operation'  => $request->input('colonne_nature_operation') ? (int)$request->input('colonne_nature_operation') : null,
            'colonne_rubrique_tva'      => $request->input('colonne_rubrique_tva') ? (int)$request->input('colonne_rubrique_tva') : null,
            'colonne_designation'       => $request->input('colonne_designation') ? (int)$request->input('colonne_designation') : null,
            'colonne_contre_partie'     => $request->input('colonne_contre_partie') ? (int)$request->input('colonne_contre_partie') : null,
        ];

        // Lancer l'import en passant le mapping et les paramètres
        Excel::import(new FournisseurImport(
            $societe_id,
            $nombre_chiffre_compte,
            $mapping
        ), $request->file('file'));

        return back()->with('success', 'Importation réussie!');
    } catch (\Exception $e) {
        return back()->with('error', 'Erreur lors de l\'importation: ' . $e->getMessage());
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
