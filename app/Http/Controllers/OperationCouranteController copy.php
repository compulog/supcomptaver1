<?php



namespace App\Http\Controllers;



use Illuminate\Http\Request;

use App\Models\OperationCourante;

use App\Models\Racine;

use App\Models\Societe;

use App\Models\PlanComptable;

use App\Models\Fournisseur;

use App\Models\Journal;

use App\Models\Client;



use Carbon\Carbon;

use Complex\Operations;

use Illuminate\Support\Facades\Log;

use Illuminate\Support\Facades\Validator;

use Illuminate\Support\Facades\Session;







class OperationCouranteController extends Controller

{

    public function index()

    {

        return view('operations'); // Chemin de votre vue Blade

    }



  public function updateField(Request $request, $id)

{

    Log::info("Requête de mise à jour reçue", ['id' => $id, 'data' => $request->all()]);



    // Vérifiez si une société est active

    $societeId = session('societeId');

    if (!$societeId) {

        Log::error("Aucune société sélectionnée.");

        return response()->json(['error' => 'Aucune société sélectionnée'], 400);

    }



    // Vérifiez si la ligne existe

    $ligne = OperationCourante::where('id', $id)

                ->where('societe_id', $societeId)

                ->first();



    if (!$ligne) {

        Log::error("Ligne non trouvée pour ID : $id et société : $societeId");

        return response()->json(['error' => 'Ligne non trouvée'], 404);

    }



    // Validation des données

    $validatedData = $request->validate([

        'field' => 'required|string',

        'value' => 'required',

    ]);



    // Mise à jour de la ligne actuelle

    try {

        $field = $validatedData['field'];

        $value = $validatedData['value'];



        // Mettre à jour la ligne spécifique

        $ligne->{$field} = $value;

        $ligne->save();



        Log::info("Mise à jour réussie pour la ligne ID : $id");



        // Récupérer le numéro de facture pour appliquer la mise à jour à toutes les lignes ayant le même numéro

        $numeroFacture = $ligne->numero_facture;



        // Appliquer la modification pour toutes les autres lignes ayant le même numero_facture

        $updatedRows = OperationCourante::where('numero_facture', $numeroFacture)

            ->where('societe_id', $societeId)

            ->where('id', '!=', $id)  // Exclure la ligne actuelle

            ->update([$field => $value]); // Appliquer la mise à jour à toutes les lignes concernées



        Log::info("Mise à jour de $updatedRows lignes avec le même numéro de facture.");



        return response()->json([

            'message' => 'Champ mis à jour avec succès pour toutes les lignes',

            'ligne' => $ligne

        ]);

    } catch (\Exception $e) {

        Log::error("Erreur lors de la mise à jour", ['error' => $e->getMessage()]);

        return response()->json(['error' => 'Erreur interne lors de la mise à jour'], 500);

    }

}







public function store(Request $request)

{

    // Récupérer l'ID de la société depuis la session

    $societeId = session('societeId');



    if (!$societeId) {

        return response()->json(['error' => 'Aucune société sélectionnée dans la session'], 400);

    }



    // Valider les données sans champs obligatoires stricts

    $validatedData = $request->validate([

        'lignes' => 'required|array',

        'lignes.*.date' => 'required|date',

        'lignes.*.numero_facture' => 'required|string',

        'lignes.*.compte' => 'required|string',

        'lignes.*.debit' => 'required|numeric|min:0',

        'lignes.*.credit' => 'required|numeric|min:0',

        'lignes.*.contre_partie' => 'nullable|string',

        'lignes.*.rubrique_tva' => 'nullable|string',

        'lignes.*.compte_tva' => 'nullable|string',

        'lignes.*.type_journal' => 'required|string',

        'lignes.*.prorat_de_deduction' => 'nullable|string',

        'lignes.*.piece_justificative' => 'nullable|string',

        'lignes.*.libelle' => 'nullable|string',

    ]);



    try {

        foreach ($validatedData['lignes'] as $ligneData) {

            if (isset($ligneData['id']) && $ligneData['id'] !== null) {

                // Mise à jour de la ligne existante

                $ligneExistante = OperationCourante::find($ligneData['id']);



                if ($ligneExistante && $ligneExistante->societe_id === $societeId) {

                    $ligneExistante->update([

                        'date' => $ligneData['date'],

                        'numero_facture' => $ligneData['numero_facture'],

                        'compte' => $ligneData['compte'],

                        'debit' => $ligneData['debit'],

                        'credit' => $ligneData['credit'],

                        'contre_partie' => $ligneData['contre_partie'] ?? null, // Ne pas forcer si null

                        'rubrique_tva' => $ligneData['rubrique_tva'] ?? null,

                        'compte_tva' => $ligneData['compte_tva'] ?? null,

                        'type_journal' => $ligneData['type_journal'],

                        'prorat_de_deduction' => $ligneData['prorat_de_deduction'] ?? null,

                        'piece_justificative' => $ligneData['piece_justificative'] ?? null,

                        'libelle' => $ligneData['libelle'] ?? null,

                        'updated_at' => now(),

                    ]);

                }

            } else {

                // Création d'une nouvelle ligne

                OperationCourante::create([

                    'date' => $ligneData['date'],

                    'numero_facture' => $ligneData['numero_facture'],

                    'compte' => $ligneData['compte'],

                    'debit' => $ligneData['debit'],

                    'credit' => $ligneData['credit'],

                    'contre_partie' => $ligneData['contre_partie'] ?? null, // Ne pas forcer si null

                    'rubrique_tva' => $ligneData['rubrique_tva'] ?? null,

                    'compte_tva' => $ligneData['compte_tva'] ?? null,

                    'type_journal' => $ligneData['type_journal'],

                    'prorat_de_deduction' => $ligneData['prorat_de_deduction'] ?? null,

                    'piece_justificative' => $ligneData['piece_justificative'] ?? null,

                    'libelle' => $ligneData['libelle'] ?? null,

                    'societe_id' => $societeId,

                    'created_at' => now(),

                    'updated_at' => now(),

                ]);

            }

        }



        return response()->json(['message' => 'Lignes enregistrées avec succès'], 200);

    } catch (\Exception $e) {

        return response()->json([

            'message' => 'Erreur lors de l\'enregistrement',

            'error' => $e->getMessage(),

            'trace' => $e->getTrace(),

        ], 500);

    }

}













 public function getOperations(Request $request)

 {

     $societeId = session('societeId');

     if (!$societeId) {

         return response()->json(['error' => 'Aucune société sélectionnée dans la session'], 400);

     }



     $mois = $request->input('mois');

     $annee = $request->input('annee');

     $codeJournal = $request->input('code_journal');



     // Initialisation de la requête de base

     $query = OperationCourante::where('societe_id', $societeId);



     // Appliquer les filtres en fonction des entrées de l'utilisateur

     if ($codeJournal && (!$mois || !$annee)) {

         // Filtrer uniquement par code_journal

         $query->where('type_journal', $codeJournal);

     } elseif ($mois && $annee && !$codeJournal) {

         // Filtrer uniquement par mois et année

         $query->whereYear('date', $annee)->whereMonth('date', $mois);

     } elseif ($mois && $annee && $codeJournal) {

         // Filtrer par code_journal, mois et année

         $query->where('type_journal', $codeJournal)

               ->whereYear('date', $annee)

               ->whereMonth('date', $mois);

     }



     // Exécution de la requête

     $operations = $query->get();



     // Ajouter une ligne vide au début des résultats

     $operations->prepend([

         'id' => '',

         'date' => '',

         'debit' => '',

         'credit' => '',

         'type_journal' => '',

     ]);



     // Retourner les données filtrées sous forme de JSON

     return response()->json($operations);

 }



public function deleteRows(Request $request)

{

    // Récupérer les identifiants des lignes à supprimer

    $rowIds = $request->input('rowIds');



    // Supposons que vous avez un modèle "TableRow" pour gérer la base de données

    try {

        OperationCourante::whereIn('id', $rowIds)->delete();

        return response()->json(['success' => true]);

    } catch (\Exception $e) {

        return response()->json(['error' => 'Erreur lors de la suppression des lignes.'], 500);

    }

}



public function getContreParties(Request $request)

{

    $codeJournal = $request->query('code_journal');



    if (!$codeJournal) {

        return response()->json(['error' => 'Code journal manquant.'], 400);

    }



    $contreParties = Journal::where('code_journal', $codeJournal)

        ->distinct()

        ->pluck('contre_partie')

        ->filter(); // Supprime les valeurs nulles



    return response()->json($contreParties);

}





















   // Charger les journaux

   public function getJournauxACH()

{

    $societeId = session('societeId');

    $societe = Societe::find($societeId);



    if (!$societe) {

        return response()->json(['error' => 'Société introuvable'], 400);

    }



    // Filtrer par type_journal 'Achats'

    $journaux = Journal::select('code_journal', 'intitule', 'type_journal')

        ->where('type_journal', 'Achats') // Filtrer par type_journal

        ->get();



    return response()->json($journaux);

}



public function getJournauxVTE()

{

    $societeId = session('societeId');

    $societe = Societe::find($societeId);



    if (!$societe) {

        return response()->json(['error' => 'Société introuvable'], 400);

    }



    // Filtrer par type_journal 'Ventes'

    $journaux = Journal::select('code_journal', 'intitule', 'type_journal')

        ->where('type_journal', 'Ventes') // Filtrer par type_journal

        ->get();



    return response()->json($journaux);

}



public function getJournauxBanque()

{

    $societeId = session('societeId');

    $societe = Societe::find($societeId);



    if (!$societe) {

        return response()->json(['error' => 'Société introuvable'], 400);

    }



    // Filtrer par type_journal 'banque'

    $journaux = Journal::select('code_journal', 'intitule', 'type_journal')

        ->where('type_journal', 'Banque') // Filtrer par type_journal

        ->get();



    return response()->json($journaux);

}

public function getJournauxCaisse()

{

    $societeId = session('societeId');

    $societe = Societe::find($societeId);



    if (!$societe) {

        return response()->json(['error' => 'Société introuvable'], 400);

    }



    // Filtrer par type_journal 'caisse'

    $journaux = Journal::select('code_journal', 'intitule', 'type_journal')

        ->where('type_journal', 'Caisse') // Filtrer par type_journal

        ->get();



    return response()->json($journaux);

}



public function getJournauxOPE()

{

    $societeId = session('societeId');

    $societe = Societe::find($societeId);



    if (!$societe) {

        return response()->json(['error' => 'Société introuvable'], 400);

    }



    // Filtrer par type_journal 'Opérations'

    $journaux = Journal::select('code_journal', 'intitule', 'type_journal')

        ->where('type_journal', 'Opérations Diverses') // Filtrer par type_journal

        ->get();



    return response()->json($journaux);

}





   // Charger les périodes

   public function getPeriodes()

   {

       $societeId = session('societeId');

       $societe = Societe::find($societeId);



       if (!$societe) {

           return response()->json(['error' => 'Société introuvable'], 400);

       }



       // Définir la locale de Carbon sur le français

       \Carbon\Carbon::setLocale('fr');



       $debut = \Carbon\Carbon::parse($societe->exercice_social_debut);

       $fin = $debut->copy()->addYear();



       $periodes = [];

       while ($debut->lt($fin)) {

           // Formater la période au format "Mois Année" en français (ex. "Janvier 2024")

           $periodes[] = $debut->format('F Y');

           $debut->addMonth();

       }



       return response()->json($periodes);

   }





   public function getSessionProrata()

   {

       // Récupérer l'ID de la société depuis la session

       $societeId = session('societeId');



       // Vérifier si une société est associée à cet ID

       $societe = Societe::find($societeId);



       if (!$societe) {

           return response()->json(['error' => 'Société introuvable'], 400);

       }



       // Vérifier si la valeur de prorata_de_deduction existe

       $prorataDeDeduction = $societe->prorata_de_deduction ?? 0; // Valeur par défaut 0 si non défini



       // Retourner la valeur de prorata_de_deduction

       return response()->json([

           'prorata_de_deduction' => $prorataDeDeduction,

       ]);

   }









   public function getSessionSocial()

{

    $societeId = session('societeId');

    $societe = Societe::find($societeId);



    if (!$societe) {

        return response()->json(['error' => 'Société introuvable'], 400);

    }



    return response()->json([

        'exercice_social_debut' => $societe->exercice_social_debut,

    ]);

}

public function getSocieteDetails()

{

    $societeId = session('societeId');

    $societe = Societe::find($societeId);



    if (!$societe) {

        return response()->json(['error' => 'Société introuvable'], 400);

    }



    return response()->json([

        'assujettie_partielle_tva' => $societe->assujettie_partielle_tva,

    ]);

}



public function getRubriquesTVAVente() {

    // Définir les num_racines autorisés

    // $numRacinesAutorises = ['120', '121', '122', '123', '124', '125', '126', '127', '128', '129'];



    // Récupérer les rubriques TVA pour les ventes, incluant les num_racines spécifiques

    $rubriques = Racine::select('Num_racines','categorie', 'Nom_racines', 'Taux' )

        ->where('type', 'vente')

        // ->whereIn('Num_racines')  // Ajouter la condition pour les num_racines autorisés



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



    // Retourner les rubriques organisées

    return response()->json(['rubriques' => $rubriquesParCategorie]);

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



public function getTva(Request $request)

{

    $rubriqueTva = $request->input('rubrique_tva'); // Ex: "Nom_racines"



    if (!$rubriqueTva) {

        return response()->json(['error' => 'La rubrique TVA est obligatoire'], 400);

    }



    // Rechercher la rubrique dans la base de données

    $rubrique = OperationCourante::where('rubrique_tva', $rubriqueTva)->first();



    if (!$rubrique) {

        return response()->json(['error' => 'Rubrique introuvable'], 404);

    }



    return response()->json(['taux' => $rubrique->Taux]);

}





    // Récupère les comptes de la société depuis le plan comptable

    public function getComptesjrx(Request $request)

{

    $societeId = $request->input('societe_id');

    $codeJournal = $request->input('code_journal'); // Récupérer le code_journal



    if (!$societeId) {

        return response()->json(['error' => 'Aucune société sélectionnée'], 400);

    }



    // Récupérer les comptes liés à cette société et au code_journal

    $comptes = Fournisseur::where('societe_id', $societeId) // Filtrer par société

        ->where('contre_partie')

        ->get(['contre_partie', 'intitule']); // Récupérer uniquement les champs nécessaires



    return response()->json($comptes);

}



public function getDetailsParCompte(Request $request)

{

    $compte = $request->query('compte'); // Récupérer le compte en paramètre



    if (!$compte) {

        return response()->json(['error' => 'Le compte est manquant'], 400);

    }



    // Rechercher les détails dans la table des Journaux

    $details = Fournisseur::where('compte', $compte)->first(['contre_partie', 'rubrique_tva']);



    if (!$details) {

        return response()->json(['error' => 'Aucun détail trouvé pour ce compte'], 404);

    }



    return response()->json($details);

}



public function getComptesjrxCP(Request $request)

{

    $societeId = $request->input('societe_id');

    $codeJournal = $request->input('code_journal'); // Récupérer le code_journal



    if (!$societeId) {

        return response()->json(['error' => 'Aucune société sélectionnée'], 400);

    }



    if (!$codeJournal) {

        return response()->json(['error' => 'Aucun code journal sélectionné'], 400);

    }



    // Récupérer les comptes liés à cette société et au code_journal

    $comptes = Journal::where('societe_id', $societeId) // Filtrer par société

        ->where('code_journal', $codeJournal) // Filtrer par code_journal

        ->get(['contre_partie', 'intitule']); // Récupérer uniquement les champs nécessaires



    return response()->json($comptes);

}





public function getCompteTvaAch(Request $request)

{

    // Récupérer les comptes TVA pour les achats, ceux qui commencent par '4456'

    $ComptesTva = PlanComptable::where('compte', 'like', '4455%')  // Comptes d'achats commençant par '4456'

        ->get(['compte', 'intitule']); // Récupérer le compte et son intitulé



    // Vérifier si des comptes ont été trouvés

    if ($ComptesTva->isEmpty()) {

        return response()->json(['error' => 'Aucun compte TVA pour les achats trouvé'], 404);

    }



    // Retourner les comptes sous forme de JSON

    return response()->json($ComptesTva);

}



public function getCompteTvaVente(Request $request)

{

    // Récupérer les comptes TVA pour les ventes, ceux qui commencent par '3455'

    $ComptesTva = PlanComptable::where('compte', 'like', '3455%')  // Comptes de ventes commençant par '3455'

        ->get(['compte', 'intitule']); // Récupérer le compte et son intitulé



    // Vérifier si des comptes ont été trouvés

    if ($ComptesTva->isEmpty()) {

        return response()->json(['error' => 'Aucun compte TVA pour les ventes trouvé'], 404);

    }



    // Retourner les comptes sous forme de JSON

    return response()->json($ComptesTva);

}







public function getTypeJournal(Request $request)

{

    $societeId = session('societe_id'); // Récupérer l'ID de la société depuis la session

    $codeJournal = $request->input('code_journal'); // Récupérer le code journal



    // Vérifier si le code journal existe dans la table journaux

    $journal = Journal::where('societe_id', $societeId)

        ->where('code_journal', $codeJournal)

        ->first();



    if (!$journal) {

        return response()->json(['error' => 'Code journal non trouvé'], 400);

    }



    // Retourner le type de journal associé

    return response()->json(['type_journal' => $journal->type_journal]);

}







    // Affiche le formulaire des opérations courantes

    public function showForm()

    {

        return view('operationcourante.form');

    }







    public function getClients(Request $request)

    {

        // Vérifie que le societe_id est bien présent dans la requête

        $societeId = $request->input('societe_id');



        if (!$societeId) {

            return response()->json(['error' => 'Aucune société sélectionnée'], 400); // Erreur si pas de société

        }



        try {

            $clients = Client::where('societe_id', $societeId)

                ->get(['compte', 'intitule', 'type_client']); // Récupère les informations des clients



            if ($clients->isEmpty()) {

                return response()->json(['message' => 'Aucun client trouvé'], 200); // Si pas de clients trouvés

            }



            return response()->json($clients); // Retourne les clients

        } catch (\Exception $e) {

            Log::error('Erreur dans getClients: ' . $e->getMessage());

            return response()->json(['error' => 'Erreur lors de la récupération des clients'], 500); // Erreur serveur

        }

    }





    public function getFournisseursAvecDetails(Request $request)

    {

        $societeId = $request->input('societe_id');



        if (!$societeId) {

            return response()->json(['error' => 'Aucune société sélectionnée'], 400);

        }



        try {

            // Récupère les fournisseurs de la société donnée

            $fournisseurs = Fournisseur::where('societe_id', $societeId)

                ->where('compte', 'LIKE', '44%') // Filtre sur les comptes commençant par '44'

                ->get(['compte', 'intitule', 'contre_partie', 'rubrique_tva']);



            // Ajoute le taux TVA à chaque fournisseur

            $fournisseursAvecDetails = $fournisseurs->map(function ($fournisseur) {

                $rubriqueTva = $fournisseur->rubrique_tva;



                // Récupère le taux de TVA depuis la table Racine

                $racine = Racine::where('num_racines', $rubriqueTva)->first();

                $tauxTva = $racine ? $racine->Taux : 0;



                // Ajout des détails supplémentaires

                $fournisseur->taux_tva = $tauxTva; // Taux TVA

                return $fournisseur;

            });



            return response()->json($fournisseursAvecDetails);

        } catch (\Exception $e) {

            // Capture les erreurs

            return response()->json(['error' => 'Erreur lors de la récupération des fournisseurs : ' . $e->getMessage()], 500);

        }

    }





public function getTransactions()

{

    $societeId = Session::get('societe_id'); // Récupération du societe_id depuis la session

    $prorataDeduction = Societe::where('id', $societeId)->value('prorata_de_deduction'); // Récupération de prorata_de_deduction



    $transactions = OperationCourante::query()

        ->when(

            request('prorata') === 'OUI' && request('type_journal') === 'Achats',

            function ($query) use ($prorataDeduction) {

                $query->whereNotNull('rubrique_tva')

                    ->whereNotNull('compte_tva')

                    ->selectRaw('((credit / (1 + taux_tva)) * taux_tva) * ?', [$prorataDeduction]);

            }

        )

        ->when(

            request('prorata') === 'OUI' && request('type_journal') === 'Ventes',

            function ($query) use ($prorataDeduction) {

                $query->selectRaw('((debit / (1 + taux_tva)) * taux_tva) * ?', [$prorataDeduction]);

            }

        )

        ->when(

            (empty(request('prorata')) || request('prorata') === 'NON') && request('type_journal') === 'Achats',

            function ($query) {

                $query->whereNull('rubrique_tva')

                    ->whereNull('compte_tva')

                    ->selectRaw('credit * taux_tva');

            }

        )

        ->when(

            (empty(request('prorata')) || request('prorata') === 'NON') && request('type_journal') === 'Ventes',

            function ($query) {

                $query->selectRaw('debit * taux_tva');

            }

        )

        ->get();



    return response()->json($transactions);

}



public function updateRow(Request $request, $id)

    {



        $validated = $request->validate([

            'date' => 'required|date',

            'numero_facture' => 'required',

            'numero_dossier' => 'required',

            'compte' => 'required',

            'debit' => 'nullable|numeric',

            'credit' => 'nullable|numeric',

            'contre_partie' => 'nullable',

            'rubrique_tva' => 'nullable',

            'compte_tva' => 'nullable',

            'prorat_de_deduction' => 'nullable|numeric',

            'piece_justificative' => 'nullable',

            'type_journal' => 'required',

            'societe_id' => 'required|exists:societes,id',

        ]);



        $row = OperationCourante::find($id);

        if (!$row) {

            return response()->json(['error' => 'Donnée introuvable'], 404);

        }



        $row->update($validated);

        return response()->json(['message' => 'Donnée mise à jour avec succès']);

    }



    public function deleteRow($id)

    {

        $row = OperationCourante::find($id);

        if (!$row) {

            return response()->json(['error' => 'Donnée introuvable'], 404);

        }



        $row->delete();

        return response()->json(['message' => 'Donnée supprimée avec succès']);

    }







public function upload(Request $request)

    {

        if ($request->hasFile('file')) {

            $file = $request->file('file');

            $path = $file->store('uploads'); // Chemin de stockage

            return response()->json(['message' => 'Fichier uploadé avec succès', 'path' => $path]);

        }

        return response()->json(['error' => 'Aucun fichier reçu'], 400);

    }



public function deleteOperations(Request $request)

{

    // Validation des IDs envoyés

    $validated = $request->validate([

        'ids' => 'required|array',

        'ids.*' => 'exists:operation_courante,id'  // Vérifie que les IDs existent dans la table

    ]);



    // Supprimer les opérations par leurs IDs

    $deleted = OperationCourante::whereIn('id', $validated['ids'])->delete();



    // Vérifier si des lignes ont été supprimées

    if ($deleted) {

        return response()->json(['message' => 'Opérations supprimées avec succès'], 200);

    } else {

        return response()->json(['message' => 'Aucune opération trouvée pour ces IDs'], 400);

    }

}









}

