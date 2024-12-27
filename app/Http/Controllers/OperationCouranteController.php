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
        ]);

        $typeJournal = $validatedData['type_journal'];

        // Récupérer les opérations du type journal
        $operations = OperationCourante::where('type_journal', $typeJournal)->get();

        // Si aucune opération n'est trouvée
        if ($operations->isEmpty()) {
            return response()->json([
                'success' => true,
                'message' => 'Aucune opération trouvée pour ce type de journal.',
                'data' => []
            ], 200);
        }

        // Retourner les opérations trouvées
        return response()->json([
            'success' => true,
            'data' => $operations,
        ], 200);
    }


    public function saveOperation(Request $request)
{
    dd($request);
    try {
        // Vérifier si 'societeId' existe dans la session
        $societeId = session('societeId');
        if (!$societeId) {
            return response()->json([
                'success' => false,
                'error' => 'Aucune société sélectionnée dans la session.',
            ], 400);
        }

        // Valider les données entrantes
        $validatedData = $request->validate([
            'data' => 'required|array|min:1', // Doit contenir au moins un élément
            'data.*.id' => 'nullable|integer|exists:operation_courante,id',
            'data.*.date' => 'nullable|date_format:Y-m-d',  // Assurez-vous que le format attendu est correct
            'data.*.numero_dossier' => 'nullable|string|max:255',
            'data.*.numero_facture' => 'nullable|string|max:255',
            'data.*.compte' => 'nullable|string|max:255',
            'data.*.libelle' => 'nullable|string|max:255',
            'data.*.debit' => 'required|numeric|min:0',
            'data.*.credit' => 'required|numeric|min:0',
            'data.*.contre_partie' => 'nullable|string|max:255',
            'data.*.rubrique_tva' => 'nullable|string|max:255',
            'data.*.compte_tva' => 'nullable|string|max:255',
            'data.*.prorat_de_deduction' => 'nullable|numeric|min:0|max:100',
            'data.*.piece_justificative' => 'nullable|string|max:255',
            'data.*.type_journal' => 'nullable|string|in:Achats,Ventes,Trésoreries,Opérations Diverses',
        ]);

        $operations = $validatedData['data'];
        $savedOperations = [];

        foreach ($operations as $operation) {
            $operation['societe_id'] = $societeId;

            // Vérifier si une opération existe déjà pour mise à jour
            if (isset($operation['id'])) {
                $existingOperation = OperationCourante::find($operation['id']);
                if ($existingOperation) {
                    $existingOperation->update($operation);
                    $savedOperations[] = $existingOperation;
                }
            } else {
                // Créer une nouvelle opération si l'ID n'existe pas
                $newOperation = OperationCourante::create($operation);
                $savedOperations[] = $newOperation;
            }
        }

        // Retourner une réponse de succès
        return response()->json([
            'success' => true,
            'message' => 'Opérations enregistrées avec succès.',
            'updatedData' => $savedOperations,
        ], 200);

    } catch (\Illuminate\Validation\ValidationException $e) {
        // Retourner les erreurs de validation détaillées
        return response()->json([
            'success' => false,
            'error' => 'Validation échouée.',
            'details' => $e->errors(),
        ], 422);
    } catch (\Exception $e) {
        // Gestion des erreurs génériques
        Log::error('Erreur lors de l\'enregistrement des opérations : ' . $e->getMessage());
        return response()->json([
            'success' => false,
            'error' => 'Erreur interne du serveur.',
            'details' => $e->getMessage(),
        ], 500);
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
    $numRacinesAutorises = ['120', '121', '122', '123', '124', '125', '126', '127', '128', '129'];

    // Récupérer les rubriques TVA pour les ventes, incluant les num_racines spécifiques
    $rubriques = Racine::select('categorie', 'Nom_racines', 'Taux', 'Num_racines')
        ->where('type', 'vente')
        ->whereIn('Num_racines', $numRacinesAutorises)  // Ajouter la condition pour les num_racines autorisés
        ->having('Taux', '>=', 0)
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

    $rubriques = Racine::select('categorie', 'Nom_racines', 'Taux', 'Num_racines')
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


    // Récupère les comptes de la société depuis le plan comptable
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

public function createCompteTva(Request $request)
{
    $validated = $request->validate([
        'compte' => 'required|string|unique:plan_comptable,compte',
        'intitule' => 'required|string',
    ]);

    $societeId = session('societe_id');

    PlanComptable::create([
        'compte' => $validated['compte'],
        'intitule' => $validated['intitule'],
        'societe_id' => $societeId,
    ]);

    return response()->json(['message' => 'Compte créé avec succès.']);
}

public function getCompteTva(Request $request)
{
    // Utilisation de 'orWhere' pour récupérer les comptes qui commencent par '4455' ou '3455'
    $ComptesTva = PlanComptable::where(function($query) {
            $query->where('compte', 'like', '4455%')  // Comptes commençant par 4455
                  ->orWhere('compte', 'like', '3455%'); // Ou comptes commençant par 3455
        })
        ->get(['compte', 'intitule']); // Récupérer le compte et son intitulé

    // Vérifier si des comptes ont été trouvés
    if ($ComptesTva->isEmpty()) {
        return response()->json(['error' => 'Aucun compte TVA trouvé'], 404);
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

    // Récupère les fournisseurs associés à la société
    public function getFournisseurs()
    {
        $societeId = session('societe_id');

        if (!$societeId) {
            return response()->json(['error' => 'Aucune société sélectionnée'], 400);
        }

        try {
            $fournisseurs = Fournisseur::where('societe_id', $societeId)
                ->get(['compte', 'intitule', 'contre_partie', 'rubrique_tva']);

            return response()->json($fournisseurs);
        } catch (\Exception $e) {
            return response()->json(['error' => 'Erreur lors de la récupération des fournisseurs: ' . $e->getMessage()], 500);
        }
    }
    public function getClients()
{
    $societeId = session('societe_id');

    if (!$societeId) {
        return response()->json(['error' => 'Aucune société sélectionnée'], 400);
    }

    try {
        $clients = Client::where('societe_id', $societeId)
            ->get(['compte', 'intitule', 'type_client']);

        if ($clients->isEmpty()) {
            return response()->json(['message' => 'Aucun client trouvé'], 200);
        }

        return response()->json($clients);
    } catch (\Exception $e) {
        Log::error('Erreur dans getClients: ' . $e->getMessage());
        return response()->json(['error' => 'Erreur lors de la récupération des clients'], 500);
    }
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

    // Récupère les fournisseurs avec détails supplémentaires
    public function getFournisseursAvecDetails()
    {
        $societeId = session('societe_id');

        if (!$societeId) {
            return response()->json(['error' => 'Aucune société sélectionnée'], 400);
        }

        try {
            $fournisseurs = Fournisseur::where('societe_id', $societeId)
                ->where('compte', 'LIKE', '44%')
                ->get(['compte', 'intitule', 'contre_partie', 'rubrique_tva']);

            $fournisseursAvecDetails = $fournisseurs->map(function ($fournisseur) use ($societeId) {
                $rubriqueTva = Racine::where('type', 'Achat')
                    ->where('societe_id', $societeId)
                    ->first(['Num_racines', 'Nom_racines']);

                return [
                    'compte' => $fournisseur->compte,
                    'intitule' => $fournisseur->intitule,
                    'contre_partie' => "Contre-Partie Automatique pour " . $fournisseur->intitule,
                    'rubrique_tva' => $rubriqueTva ? [
                        'num_racines' => $rubriqueTva->Num_racines,
                        'nom_racines' => $rubriqueTva->Nom_racines,
                    ] : null,
                ];
            });

            return response()->json($fournisseursAvecDetails);
        } catch (\Exception $e) {
            return response()->json(['error' => 'Erreur lors de la récupération des fournisseurs avec détails: ' . $e->getMessage()], 500);
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
