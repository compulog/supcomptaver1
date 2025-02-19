<?php

namespace App\Http\Controllers;
use App\Imports\FournisseurImport;
use App\Imports\FournisseursImport;
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
        // Vérifier si 'societeId' existe dans la session
        $societeId = session('societeId');
        Log::debug('societeId dans la session : ' . $societeId);

        if (!$societeId) {
            return response()->json(['error' => 'Aucune société sélectionnée dans la session'], 400);
        }

        // Validation des données
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

        // Si le compte n'est pas spécifié, générer un compte unique
        if (empty($validatedData['compte'])) {
            $validatedData['compte'] = $this->getNextCompte($societeId); // Appel à la méthode pour générer le compte
        }

        // Ajouter l'ID de la société au tableau de données validées
        $validatedData['societe_id'] = $societeId;

        DB::beginTransaction();

        try {
            // Vérifier si un fournisseur avec ce compte existe déjà pour cette société
            $existingFournisseur = Fournisseur::where('societe_id', $societeId)
                ->where('compte', $validatedData['compte'])
                ->first();

            if ($existingFournisseur) {
                return response()->json([
                    'error' => 'Le fournisseur avec ce compte existe déjà pour la société sélectionnée.'
                ], 422);
            }

            // Créer un nouveau fournisseur
            $fournisseur = Fournisseur::create($validatedData);

            // Ajouter ce fournisseur dans la table plan_comptable
            PlanComptable::create([
                'societe_id' => $societeId,
                'compte' => $validatedData['compte'],
                'intitule' => $validatedData['intitule'],
            ]);

            DB::commit(); // Commit les deux opérations si tout va bien

            return response()->json([
                'success' => true,
                'fournisseur' => $fournisseur,
            ]);
        } catch (\Exception $e) {
            DB::rollBack(); // Annule si une erreur se produit
            Log::error('Erreur lors de la création du fournisseur : ' . $e->getMessage());

            return response()->json([
                'error' => 'Une erreur est survenue lors de la création du fournisseur.',
                'details' => $e->getMessage()
            ], 500);
        }
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




    public function destroy($id)
    {
        $fournisseur = Fournisseur::findOrFail($id);
        $fournisseur->delete();

        return response()->json(['success' => true]);
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
        // Valider le fichier importé
        $request->validate([
            'file' => 'required|file|mimes:xlsx,xls,csv',  // Vérifie que le fichier est valide
        ]);

        try {
            // Récupérer l'ID de la société et le nombre de chiffres du compte depuis la session
            $societe_id = session('societeId');  // Récupérer l'ID de la société stockée dans la session
            $societe = Societe::find($societe_id);  // Trouver la société par son ID
            $nombre_chiffre_compte = $societe->nombre_chiffre_compte;  // Récupérer le nombre de chiffres du compte

            // Charger le fichier Excel et importer les données
            Excel::import(new FournisseurImport(), $request->file('file'));

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


    public function deleteSelected(Request $request)
    {
        // Valider que le tableau 'ids' est bien fourni
        $request->validate([
            'ids' => 'required|array',
            'ids.*' => 'integer',  // Chaque ID doit être un entier
        ]);

        try {
            // Supprimer les lignes avec les IDs reçus
            $deletedCount = Fournisseur::whereIn('id', $request->ids)->delete();

            return response()->json([
                'status' => 'success',
                'message' => "{$deletedCount} lignes supprimées"
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Erreur lors de la suppression.',
                'error' => $e->getMessage()  // Retour de l'erreur spécifique
            ]);
        }
    }

}
