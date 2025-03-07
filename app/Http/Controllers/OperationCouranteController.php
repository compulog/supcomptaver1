<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\OperationCourante;
use App\Models\racine;
use App\Models\Societe;
use App\Models\PlanComptable;
use App\Models\Fournisseur;
use App\Models\Journal;
use App\Models\Client;
use App\Models\File; // Assurez-vous d'importer le modèle File

use Carbon\Carbon;
use Complex\Operations;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

use Illuminate\Support\Facades\Session;



class OperationCouranteController extends Controller
{
    public function index()
{
    // Récupérer l'ID de la société depuis la session
    $societeId = session('societeId'); 
    
    // Initialiser la variable $files
    $files = null;

    if ($societeId) {
        // Récupérer tous les fichiers associés à la société (filtrés par societe_id)
        $files = File::where('societe_id', $societeId)
        ->where('type', 'caisse')
        ->get();

        $files_banque = File::where('societe_id', $societeId)
        ->where('type', 'banque')
        ->get();
        $files_achat = File::where('societe_id', $societeId)
        ->where('type', 'achat')
        ->get();
        $files_vente = File::where('societe_id', $societeId)
        ->where('type', 'vente')
        ->get();

    }

    // Passer la variable $files à la vue avec compact()
    return view('Operation_Courante', compact('files')); // Chemin de votre vue Blade
}


    public function updateField(Request $request, $id)
{
    Log::info("Requête reçue pour mise à jour", ['id' => $id, 'data' => $request->all()]);

    // Vérification de la société active
    $societeId = session('societeId');
    if (!$societeId) {
        Log::error("Aucune société sélectionnée.");
        return response()->json(['error' => 'Aucune société sélectionnée'], 400);
    }

    // Vérification de l'existence de la ligne
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
    ], [
        'field.required' => 'Le champ "field" est obligatoire.',
        'value.required' => 'Le champ "value" est obligatoire.',
    ]);

    try {
        $field = $validatedData['field'];
        $value = $validatedData['value'];

        // Vérifier que le champ existe dans la table pour éviter des erreurs SQL
        if (!Schema::hasColumn('operation_courante', $field)) {
            Log::error("Champ invalide : $field");
            return response()->json(['error' => "Le champ '$field' n'existe pas."], 400);
        }

        // Mise à jour de la ligne en cours
        $ligne->{$field} = $value;
        $ligne->save();

        Log::info("Mise à jour réussie pour ID : $id");

        // Mettre à jour les autres lignes avec le même numéro de facture
        $numeroFacture = $ligne->numero_facture;
        $updatedRows = OperationCourante::where('numero_facture', $numeroFacture)
            ->where('societe_id', $societeId)
            ->where('id', '!=', $id) // Exclure la ligne actuelle
            ->update([$field => $value]);

        Log::info("Mise à jour de $updatedRows lignes avec le même numéro de facture.");

        return response()->json([
            'message' => 'Mise à jour réussie pour toutes les lignes associées.',
            'ligne' => $ligne
        ]);
    } catch (\Exception $e) {
        Log::error("Erreur lors de la mise à jour", ['error' => $e->getMessage()]);
        return response()->json(['error' => 'Erreur interne lors de la mise à jour'], 500);
    }
}




public function store(Request $request)
{
    Log::info('Début de la sauvegarde des lignes');

    // Récupération de l'ID de la société depuis la session
    $societeId = session('societeId');
    Log::info("ID société: $societeId");

    if (!$societeId) {
        Log::error('Aucune société sélectionnée dans la session');
        return response()->json(['error' => 'Aucune société sélectionnée dans la session'], 400);
    }

    // Validation des données, ajout du champ "categorie"
    $validatedData = $request->validate([
        'lignes'                         => 'required|array',
        'lignes.*.id'                    => 'nullable',
        'lignes.*.date'                  => 'nullable|date',
        'lignes.*.numero_dossier'        => 'nullable|string',

        'lignes.*.numero_facture'        => 'nullable|string',
        'lignes.*.compte'                => 'nullable|string',
        'lignes.*.debit'                 => 'nullable|numeric|min:0',
        'lignes.*.credit'                => 'nullable|numeric|min:0',
        'lignes.*.contre_partie'         => 'nullable|string',
        'lignes.*.rubrique_tva'          => 'nullable|string',
        'lignes.*.compte_tva'            => 'nullable|string',
        'lignes.*.type_journal'          => 'nullable|string',
        'lignes.*.categorie'             => 'nullable|string', // Nouveau champ pour la catégorie
        'lignes.*.prorat_de_deduction'    => 'nullable|string',
        'lignes.*.piece_justificative'    => 'nullable|string',
        'lignes.*.libelle'               => 'nullable|string',
        'lignes.*.filtre_selectionne'    => 'nullable|string|in:libre,contre-partie',
    ]);

    Log::info('Validation des données réussie');

    try {
        $responseData = [];
        foreach ($validatedData['lignes'] as $ligneData) {
            Log::info('Traitement de la ligne', $ligneData);

            // Traitement de la date
            $lineDateObj = null;
            if (!empty($ligneData['date'])) {
                try {
                    $lineDateObj = Carbon::parse($ligneData['date']);
                } catch (\Exception $e) {
                    $lineDateObj = Carbon::now();
                }
            } else {
                $lineDateObj = Carbon::now();
            }

            // Vérification de la ligne vide (optionnelle)
            if ($lineDateObj &&
                $lineDateObj->format('Y-m-d') === Carbon::now()->format('Y-m-d') &&
                (empty($ligneData['numero_facture']) || $ligneData['numero_facture'] === 'N/A') &&
                empty($ligneData['compte']) &&
                (((isset($ligneData['debit']) ? $ligneData['debit'] : 0)) == 0) &&
                (((isset($ligneData['credit']) ? $ligneData['credit'] : 0)) == 0)
            ) {
                Log::info("Ligne vide ignorée");
                continue; // Ne pas insérer cette ligne
            }

            // Génération du numéro de pièce
            $mois = $lineDateObj->format('m'); // Mois au format MM
            // Récupérer le code journal depuis le champ type_journal (ou autre, selon votre logique)
            $codeJournal = $ligneData['type_journal'];
            $numeroFacture = $ligneData['numero_facture'] ?? null;

            // Récupérer le dernier numéro de pièce utilisé pour le même numéro de facture et mois
            $lastRecord = OperationCourante::where('societe_id', $societeId)
                ->where('numero_facture', $numeroFacture)
                ->whereMonth('date', $mois)
                ->orderBy('id', 'desc')
                ->first();

            $increment = $lastRecord ? (intval(substr($lastRecord->numero_piece, -4)) + 1) : 1;
            $numeroPiece = 'P' . $mois . $codeJournal . str_pad($increment, 4, '0', STR_PAD_LEFT);

            // Préparation des données à enregistrer, avec le champ "categorie"
            $data = [
                'numero_facture'      => $numeroFacture,
                'numero_dossier'              => $ligneData['numero_dossier'] ?? null,

                'compte'              => $ligneData['compte'] ?? null,
                'debit'               => $ligneData['debit'] ?? 0,
                'credit'              => $ligneData['credit'] ?? 0,
                'contre_partie'       => $ligneData['contre_partie'] ?? null,
                'rubrique_tva'        => $ligneData['rubrique_tva'] ?? null,
                'compte_tva'          => $ligneData['compte_tva'] ?? null,
                'type_journal'        => $ligneData['type_journal'] ?? null,
                'categorie'           => $ligneData['categorie'] ?? null, // Enregistrement de la catégorie
                'prorat_de_deduction' => $ligneData['prorat_de_deduction'] ?? null,
                'piece_justificative' => $ligneData['piece_justificative'] ?? null,
                'libelle'             => $ligneData['libelle'] ?? null,
                'filtre_selectionne'  => $ligneData['filtre_selectionne'] ?? null,
                'societe_id'          => $societeId,
                'numero_piece'        => $numeroPiece,
            ];

            // Formatage de la date pour l'insertion en base (format MySQL)
            $data['date'] = $lineDateObj->format('Y-m-d H:i:s');

            // Vérifier si la ligne a déjà été enregistrée dans la session
            $sessionLines = session('lignes_en_cours', []);
            $isDuplicate = false;
            foreach ($sessionLines as $existingLine) {
                if ($existingLine['numero_facture'] === $data['numero_facture'] && $existingLine['compte'] === $data['compte']) {
                    $isDuplicate = true;
                    break;
                }
            }
            if ($isDuplicate) {
                Log::info("Ligne déjà saisie, pas de réenregistrement.");
                continue;
            }

            // Ajouter la ligne dans la session pour éviter le double enregistrement
            $sessionLines[] = $data;
            session(['lignes_en_cours' => $sessionLines]);

            // Insérer ou mettre à jour la ligne dans la base de données
            $existingLigne = OperationCourante::where('societe_id', $societeId)
                ->where('numero_facture', $data['numero_facture'])
                ->where('compte', $data['compte'])
                ->where('debit', $data['debit'])
                ->where('credit', $data['credit'])
                ->where('date', $data['date'])
                ->first();

            if ($existingLigne) {
                $existingLigne->update($data);
                $record = $existingLigne;
            } else {
                $record = OperationCourante::create($data);
            }

            // Reformater la date pour l'affichage
            $record->date = Carbon::parse($record->date)->format('d/m/Y');
            $responseData[] = $record;
        }

        Log::info('Opérations enregistrées avec succès');

        return response()->json($responseData, 200);
    } catch (\Exception $e) {
        Log::error('Erreur lors de la sauvegarde des lignes: ' . $e->getMessage());
        return response()->json(['error' => 'Une erreur est survenue lors de la sauvegarde des lignes.'], 500);
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
    $operationType = $request->input('operation_type'); // "Achats" ou "Ventes"

    // Initialisation de la requête de base
    $query = OperationCourante::where('societe_id', $societeId);

    // Filtrage par type d'opération (catégorie)
    if ($operationType) {
        $query->where('categorie', $operationType);
    }

    // Filtrer par code_journal, mois et année selon les conditions existantes
    if ($codeJournal && (!$mois || !$annee)) {
        $query->where('type_journal', $codeJournal);
    } elseif ($mois && $annee && !$codeJournal) {
        $query->whereYear('date', $annee)->whereMonth('date', $mois);
    } elseif ($mois && $annee && $codeJournal) {
        $query->where('type_journal', $codeJournal)
              ->whereYear('date', $annee)
              ->whereMonth('date', $mois);
    }

    $operations = $query->get();

    // Ajouter une ligne vide en tête si nécessaire
    $operations->prepend([
         'id' => '',
         'date' => '',
         'debit' => '',
         'credit' => '',
         'type_journal' => '',
    ]);

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

public function getContrePartiesVentes(Request $request)
{
    $codeJournal = $request->query('code_journal');

    if (!$codeJournal) {
        return response()->json(['error' => 'Code journal manquant.'], 400);
    }

    // Récupérer les valeurs distinctes de contre_partie pour le code journal et type "Ventes"
    $contreParties = Journal::where('code_journal', $codeJournal)
        ->where('type_journal', 'Ventes')
        ->distinct()
        ->pluck('contre_partie')
        ->filter()      // Supprime les valeurs nulles
        ->values();     // Réindexe la collection pour retourner un tableau simple

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

       // Filtrer par societe_id et par type_journal 'Achats'
       $journaux = Journal::select('code_journal', 'intitule', 'type_journal')
           ->where('societe_id', $societeId)
           ->where('type_journal', 'Achats')
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
    ->where('societe_id', $societeId)

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
    ->where('societe_id', $societeId)

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
    ->where('societe_id', $societeId)

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
    ->where('societe_id', $societeId)

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
// public function getRubriqueSociete()
// {
//     $societeId = session('societeId');
//     $societe = Societe::find($societeId);

//     if (!$societe) {
//         return response()->json(['error' => 'Société introuvable'], 400);
//     }

//     // Retourne la rubrique sous forme de chaîne ou, si besoin, sous forme de tableau
//     return response()->json([
//         'rubriques' => $societe->rubrique_tva, // Par exemple "103"
//     ]);
// }


public function getRubriqueSociete()
{
    // Récupération de l'ID de la société depuis la session
    $societeId = session('societeId');
    $societe = Societe::find($societeId);

    if (!$societe) {
        return response()->json(['error' => 'Société introuvable'], 400);
    }

    // La valeur de la rubrique dans la société (par exemple "103")
    $rubrique = $societe->rubrique_tva;

    // Jointure avec la table "racines" pour récupérer la ligne où num_racines = rubrique
    $racine = DB::table('racines')
        ->select('num_racines', 'nom_racines', 'taux')
        ->where('num_racines', $rubrique)
        ->first();

    if (!$racine) {
        return response()->json(['error' => 'Aucune correspondance dans la table racine'], 400);
    }

    return response()->json([
        'rubrique'    => $rubrique,            // Par exemple "103"
        'nom_racines' => $racine->nom_racines,   // Le nom récupéré dans la table racines
        'taux'        => $racine->taux,          // Le taux récupéré dans la table racines
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
        $codeJournal = $request->input('code_journal'); // Vous pouvez utiliser cette variable si nécessaire

        if (!$societeId) {
            return response()->json(['error' => 'Aucune société sélectionnée'], 400);
        }

        // Récupérer les fournisseurs pour la société qui possèdent une contre-partie renseignée
        $comptes = Fournisseur::where('societe_id', $societeId)
            ->whereNotNull('contre_partie')
            ->get(['contre_partie', 'intitule']);

        return response()->json($comptes);
    }

    public function getAllContreParties(Request $request)
{
    $societeId = $request->input('societe_id');

    if (!$societeId) {
        return response()->json(['error' => 'Aucune société sélectionnée'], 400);
    }

    // Récupérer toutes les contre-parties uniques des fournisseurs d'une société donnée
    $contreParties = Fournisseur::where('societe_id', $societeId)
        ->whereNotNull('contre_partie')
        ->distinct()
        ->pluck('contre_partie')
        ->map(function ($contrePartie) {
            return ['contre_partie' => $contrePartie]; // Transformer en format objet
        });

    return response()->json($contreParties);
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

public function getComptes(Request $request)
{
    // Récupérer les comptes de la table fournisseurs, avec filtrage sur "4411%"
    $comptesFournisseurs = Fournisseur::select('compte', 'intitule')
        ->where('compte', 'like', '4411%')
        ->get()
        ->toArray();

    // Récupérer tous les comptes de la table plan_comptable sans filtrer
    $comptesPlan = PlanComptable::select('compte', 'intitule')
        ->get()
        ->toArray();

    // Marquer l'origine pour distinguer les deux sources
    $comptesFournisseurs = array_map(function ($compte) {
        $compte['origine'] = 'fournisseurs';
        return $compte;
    }, $comptesFournisseurs);

    $comptesPlan = array_map(function ($compte) {
        $compte['origine'] = 'plan_comptable';
        return $compte;
    }, $comptesPlan);

    // Créer un tableau associatif indexé par "compte" pour les comptes fournisseurs
    $fournisseursAssoc = [];
    foreach ($comptesFournisseurs as $compte) {
        $fournisseursAssoc[$compte['compte']] = $compte;
    }

    // Fusionner : pour chaque compte du plan_comptable, si ce compte n'existe pas déjà dans fournisseurs, l'ajouter
    $comptesResultat = $fournisseursAssoc; // On commence par les comptes fournisseurs
    foreach ($comptesPlan as $comptePlan) {
        if (!isset($fournisseursAssoc[$comptePlan['compte']])) {
            $comptesResultat[$comptePlan['compte']] = $comptePlan;
        }
    }

    // Convertir le tableau associatif en tableau indexé et trier par le champ "compte"
    $comptes = array_values($comptesResultat);
    usort($comptes, function ($a, $b) {
        return strcmp($a['compte'], $b['compte']);
    });

    // Retourner la réponse JSON
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
        $societeId = session('societeId');

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
        // Récupère le paramètre 'societe_id' depuis la query string (GET)
        $societeId = session('societeId');

        // Si le paramètre n'est pas présent, retourne une erreur
        if (!$societeId) {
            return response()->json(['error' => 'Aucune société sélectionnée'], 400);
        }

        try {
            // Récupère les fournisseurs de la société donnée dont le compte commence par '44'
            $fournisseurs = Fournisseur::where('societe_id', $societeId)
                ->where('compte', 'LIKE', '44%')
                ->get(['compte', 'intitule', 'contre_partie', 'rubrique_tva']);

            // Pour chaque fournisseur, ajoute le taux de TVA correspondant
            $fournisseursAvecDetails = $fournisseurs->map(function ($fournisseur) {
                $rubriqueTva = $fournisseur->rubrique_tva;
                // Recherche dans la table Racine le taux de TVA associé à la rubrique
                $racine = Racine::where('num_racines', $rubriqueTva)->first();
                $tauxTva = $racine ? $racine->Taux : 0;
                $fournisseur->taux_tva = $tauxTva;
                return $fournisseur;
            });

            return response()->json($fournisseursAvecDetails);
        } catch (\Exception $e) {
            // En cas d'erreur, retourne le message d'erreur
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
