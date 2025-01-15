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

    public function getOperations(Request $request)
{
    // Valider les données entrantes
    $validatedData = $request->validate([
        'type_journal' => 'required|string|in:Achats,Ventes,Trésoreries,Opérations Diverses', // Types valides
        'code_journal' => 'required|string', // Assurez-vous que le code_journal est fourni
        'start_date' => 'required|date', // Date de début
        'end_date' => 'required|date|after_or_equal:start_date', // Date de fin
    ]);

    $typeJournal = $validatedData['type_journal'];
    $codeJournal = $validatedData['code_journal'];
    $startDate = $validatedData['start_date'];
    $endDate = $validatedData['end_date'];

    // Récupérer les opérations filtrées par type_journal, code_journal et plage de dates
    $operations = OperationCourante::where('type_journal', $typeJournal)
        ->where('code_journal', $codeJournal)
        ->whereBetween('created_at', [$startDate, $endDate])
        ->get();

    // Si aucune opération n'est trouvée
    if ($operations->isEmpty()) {
        return response()->json([
            'success' => true,
            'message' => 'Aucune opération trouvée pour ce type de journal, code journal ou plage de dates.',
            'data' => []
        ], 200);
    }

    // Retourner les opérations trouvées
    return response()->json([
        'success' => true,
        'data' => $operations,
    ], 200);
}



public function saveOrUpdateRowData(Request $request)
{
    // Récupère le societe_id depuis la session
    $societeId = session('societe_id');  // Vous pouvez remplacer 'societe_id' par la clé utilisée pour la session

    // Vérifier si societe_id existe dans la session
    if (!$societeId) {
        return response()->json(['success' => false, 'error' => 'La société n\'est pas définie dans la session.']);
    }

    // Validation des données reçues
    $validator = Validator::make($request->all(), [
        'numero_facture' => 'required|string',
        'compte' => 'required|string',
        'libelle' => 'required|string',
        'debit' => 'nullable|numeric',
        'credit' => 'nullable|numeric',
        'contre_Partie' => 'nullable|string',
        'rubrique_tva' => 'nullable|string',
        'compte_tva' => 'nullable|string',
        'prorat_de_deduction' => 'nullable|string',
        'piece_justificative' => 'nullable|string',
        'date' => 'nullable|date',
        'type_journal' => 'required|string', // Validation pour code_journal
        // Ajoutez d'autres validations selon les besoins
    ]);

    // Si la validation échoue
    if ($validator->fails()) {
        return response()->json(['success' => false, 'errors' => $validator->errors()]);
    }

    try {
        $rowData = $request->all(); // Récupère les données envoyées depuis le frontend

        // Ajouter societe_id aux données de l'opération
        $rowData['societe_id'] = $societeId;

        // Vérifier si l'ID existe déjà dans les données
        if (isset($rowData['id']) && $rowData['id']) {
            // Mise à jour de la ligne existante
            $operation = OperationCourante::find($rowData['id']);
            if ($operation) {
                $operation->update($rowData); // Mise à jour
                return response()->json(['success' => true]);
            } else {
                return response()->json(['success' => false, 'error' => 'Ligne non trouvée pour mise à jour.']);
            }
        }

        // Si l'ID n'existe pas, créer une nouvelle ligne
        $operation = new OperationCourante($rowData);
        $operation->save(); // Création
        return response()->json(['success' => true]);

    } catch (\Exception $e) {
        // Capture et renvoi de l'erreur
        return response()->json(['success' => false, 'error' => $e->getMessage()]);
    }
}




public function getEmptyRow()
{
    return response()->json([
        'id' => null,
        'date' => null,
        'numero_dossier' => null,
        'numero_facture' => null,
        'compte' => null,
        'libelle' => null,
        'debit' => null,
        'credit' => null,
        'contre_partie' => null,
        'rubrique_tva' => null,
        'compte_tva' => null,
        'prorat_de_deduction' => null,
        'piece_justificative' => null,
        'type_journal' => null,
        'societe_id' => session('societeId'),
    ]);
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

public function getJournauxTRE()
{
    $societeId = session('societeId');
    $societe = Societe::find($societeId);

    if (!$societe) {
        return response()->json(['error' => 'Société introuvable'], 400);
    }

    // Filtrer par type_journal 'Trésorerie'
    $journaux = Journal::select('code_journal', 'intitule', 'type_journal')
        ->where('type_journal', 'Trésoreries') // Filtrer par type_journal
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
