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

   

    public function checkPassword(Request $request)
    {
        // Valider que le mot de passe est bien présent
        $request->validate([
            'password' => 'required|string',
        ]);

        // Récupérer l'utilisateur actuellement connecté
        $user = Auth::user();

        // Vérifier si le mot de passe correspond à celui de l'utilisateur
        if (Hash::check($request->password, $user->password)) {
            return response()->json(['success' => true]);
        } else {
            return response()->json(['success' => false], 401); // Mot de passe incorrect
        }
    }
   

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
            'compte' => 'nullable|string|max:255', // Le compte peut être généré automatiquement si vide
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
            $nextCompte = $this->generateNextCompte();
            $validatedData['compte'] = $nextCompte; // Attribuer le nouveau compte
        }

        // Ajouter l'ID de la société au tableau de données validées
        $validatedData['societe_id'] = $societeId;

        try {
            // Créer un nouveau fournisseur avec les données validées
            $fournisseur = Fournisseur::create($validatedData);

            return response()->json([
                'success' => true,
                'fournisseur' => $fournisseur,
            ]);
        } catch (\Exception $e) {
            return response()->json(['error' => 'Une erreur est survenue lors de la création du fournisseur: ' . $e->getMessage()], 500);
        }
    }
    
 // Modifier un fournisseur
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
     
     $fournisseur->save(); // Enregistrer les modifications

     return response()->json(['message' => 'Fournisseur mis à jour avec succès', 'fournisseur' => $fournisseur], 200);
 }

public function getRubriquesTva()
{
 // Récupérer les rubriques TVA avec type = 'Achat', groupées par 'categorie'
$rubriques = Racine::select('categorie', 'Nom_racines', 'Taux', 'Num_racines')
->where('type', 'Achat') // Assurez-vous que 'type' est bien le nom de la colonne
->having('Taux', '>', 0) // Ne garder que les rubriques avec Taux supérieur à 0
->get();

// Organiser les rubriques par catégorie
$rubriquesParCategorie = [];
foreach ($rubriques as $rubrique) {
$rubriquesParCategorie[$rubrique->categorie]['rubriques'][] = [
    'Nom_racines' => $rubrique->Nom_racines,
    'Num_racines' => $rubrique->Num_racines,
    'Taux' => $rubrique->Taux,
];
}

// Passer les rubriques organisées à votre vue ou à votre réponse AJAX
return response()->json(['rubriques' => $rubriquesParCategorie]);



}

public function getNextCompte()
{
    // Récupérer tous les comptes qui commencent par "4411" et les trier par ordre croissant
    $comptes = Fournisseur::where('compte', 'like', '4411%')
        ->orderBy('compte', 'asc')
        ->pluck('compte')
        ->toArray();

    $prefix = '4411'; // Le préfixe du compte
    $nextCompte = $prefix . '0001'; // Compte de départ

    if (count($comptes) > 0) {
        // Vérifier s'il y a des comptes manquants
        $lastSequence = (int)substr($comptes[count($comptes) - 1], 4); // Extraire le dernier numéro de compte existant
        $nextCompte = $prefix . str_pad($lastSequence + 1, 4, '0', STR_PAD_LEFT); // Générer le prochain compte

        // Vérifier s'il y a des numéros manquants entre les comptes
        for ($i = 0; $i < count($comptes) - 1; $i++) {
            $currentSequence = (int)substr($comptes[$i], 4); // Extraire la séquence du compte actuel
            $nextSequence = (int)substr($comptes[$i + 1], 4); // Extraire la séquence du compte suivant

            // Si la séquence suivante est plus grande de 1, cela signifie qu'il y a un compte manquant
            if ($nextSequence - $currentSequence > 1) {
                // Générer le premier numéro manquant
                $nextCompte = $prefix . str_pad($currentSequence + 1, 4, '0', STR_PAD_LEFT);
                break; // Une fois le premier compte manquant trouvé, on sort de la boucle
            }
        }
    }

    return response()->json(['next_compte' => $nextCompte]);
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
     */
    public function import(Request $request)
    {
        // Validation des données
        $request->validate([
            'file' => 'required|file|mimes:xlsx,xls,csv',
            'colonne_compte' => 'required|integer',
            'colonne_intitule' => 'required|integer',
            'colonne_identifiant_fiscal' => 'required|integer',
            'colonne_ICE' => 'required|integer',
            'colonne_nature_operation' => 'required|integer',
            'colonne_rubrique_tva' => 'required|integer',
            'colonne_designation' => 'required|integer',
            'colonne_contre_partie' => 'required|integer',
        ]);

        // Récupérer l'ID de la société à partir de la session
        $societeId = session('societeId');

        // Si un ID de société est envoyé dans la requête, le mettre à jour
        if ($request->has('societe_id')) {
            $societeId = $request->societe_id;
        }

        try {
            // Parse les données du fichier Excel
            $importedData = $this->parseExcelFile(
                $request->file('file'),
                $request->colonne_compte,
                $request->colonne_intitule,
                $request->colonne_identifiant_fiscal,
                $request->colonne_ICE,
                $request->colonne_nature_operation,
                $request->colonne_rubrique_tva,
                $request->colonne_designation,
                $request->colonne_contre_partie
            );

            // Insérer les données si le compte n'existe pas déjà pour la société
            foreach ($importedData as $data) {
                // Vérifier si le fournisseur existe déjà pour cette société
                $existingFournisseur = Fournisseur::where('compte', $data['compte'])
                                                  ->where('societe_id', $societeId)
                                                  ->first();

                // Si le fournisseur n'existe pas, insérer le nouveau fournisseur
                if (!$existingFournisseur) {
                    Fournisseur::create([
                        'compte' => $data['compte'],
                        'intitule' => $data['intitule'],
                        'identifiant_fiscal' => $data['identifiant_fiscal'],
                        'ICE' => $data['ICE'],
                        'nature_operation' => $data['nature_operation'],
                        'rubrique_tva' => $data['rubrique_tva'],
                        'designation' => $data['designation'],
                        'contre_partie' => $data['contre_partie'],
                        'societe_id' => $societeId, // Associer l'ID de la société actuel
                    ]);
                }
            }

            // Retourner à la page précédente avec un message de succès
            return redirect()->back()->with('success', 'Importation des fournisseurs réussie.');
        } catch (\Exception $e) {
            // En cas d'erreur
            return redirect()->back()->with('error', 'Erreur lors de l\'importation : ' . $e->getMessage());
        }
    }

    /**
     * Parse le fichier Excel (en ignorant la première ligne).
     */
    protected function parseExcelFile($file, $compteColumn, $intituleColumn, $identifiantFiscalColumn, $ICEColumn, $natureOperationColumn, $rubriqueTvaColumn, $designationColumn, $contrePartieColumn)
    {
        // Utilisation de Laravel Excel pour lire le fichier
        $data = Excel::toArray([], $file);  // Lire toutes les feuilles du fichier Excel

        // Extraire les données en ignorant la première ligne (index 0)
        $importedData = [];
        foreach (array_slice($data[0], 1) as $row) {  // On commence à partir de la deuxième ligne (index 1)
            $importedData[] = [
                'compte' => $row[$compteColumn - 1],  // Compte basé sur l'index de la colonne
                'intitule' => $row[$intituleColumn - 1],  // Intitulé basé sur l'index de la colonne
                'identifiant_fiscal' => $row[$identifiantFiscalColumn - 1],  // Identifiant Fiscal
                'ICE' => $row[$ICEColumn - 1],  // ICE
                'nature_operation' => $row[$natureOperationColumn - 1],  // Nature de l'opération
                'rubrique_tva' => $row[$rubriqueTvaColumn - 1],  // Rubrique TVA
                'designation' => $row[$designationColumn - 1],  // Désignation
                'contre_partie' => $row[$contrePartieColumn - 1],  // Contre partie
            ];
        }

        return $importedData;
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
