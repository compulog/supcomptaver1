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
use App\Models\File; // Assurez-vous d'importer le modèle File
use App\Models\Folder; 
use Carbon\Carbon;
use Complex\Operations;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use OpenAI\Laravel\Facades\OpenAI;  // 👈 the Laravel facade

use Illuminate\Support\Facades\Session;
use Smalot\PdfParser\Parser;
use GuzzleHttp\Client as GuzzleClient;


class OperationCouranteController extends Controller
{


/**
     * Clôture de l'exercice donné
     *
     * Attendu en requête :
     *   - "annee": ex. "2025"
     *   - "societe_id": récupéré depuis la session
     *   - "code_journal": facultatif si vous souhaitez ne clore qu'un seul journal (ex. "ac3", "ach7", etc.)
     *
     * Cette méthode solde tous les comptes de charge (classe 6) et produits (classe 7),
     * calcule le résultat net et le transfert au compte 120 ou 129, puis marque l'exercice comme clôturé.
     */
    public function closeExercice(Request $request)
    {
        $societeId = session('societeId');
        if (!$societeId) {
            return response()->json(['error' => 'Aucune société en session'], 400);
        }

        $annee = $request->input('annee');
        if (!$annee || !preg_match('/^\d{4}$/', $annee)) {
            return response()->json(['error' => 'Année invalide'], 400);
        }

        // (Optionnel) Ne clore qu’un journal spécifique :
        $codeJournal = $request->input('code_journal'); // ex. "ac3" ou "ach7"

        // Démarre une transaction pour atomicité
        DB::beginTransaction();
        try {
            // 1) Récupérer toutes les opérations de l’exercice pour la société
            $query = OperationCourante::where('societe_id', $societeId)
                ->whereYear('date', $annee);
            if ($codeJournal) {
                $query->where('type_journal', $codeJournal);
            }
            $operations = $query->get();

            // 2) Séparer les montants par compte : classe 6 = charges, 7 = produits
            // On suppose que le champ "compte" commence par "6" ou "7" selon le Plan Comptable
            $totalCharges = 0.0;
            $totalProduits = 0.0;

            // Nous allons sommer par compte pour générer les écritures de clôture
            $chargesParCompte = [];  // ex. ["6xxxx" => somme]
            $produitsParCompte = []; // ex. ["7xxxx" => somme]

            foreach ($operations as $op) {
                $compte = $op->compte;
                $montant = floatval($op->debit) - floatval($op->credit);
                // En classe 6, le solde du compte est débité (charges)
                if (strpos($compte, '6') === 0) {
                    $chargesParCompte[$compte] = ($chargesParCompte[$compte] ?? 0) + $montant;
                    $totalCharges += $montant;
                }
                // En classe 7, le solde du compte est crédité (produits)
                elseif (strpos($compte, '7') === 0) {
                    // Attention : en classe 7, base est en "credit" (op->credit) – "débit"
                    $soldeProduit = floatval($op->credit) - floatval($op->debit);
                    $produitsParCompte[$compte] = ($produitsParCompte[$compte] ?? 0) + $soldeProduit;
                    $totalProduits += $soldeProduit;
                }
            }

            // 3) Calculer le résultat net
            // En compta OHADA/Morocco : Résultat = Produits – Charges
            $resultNet = $totalProduits - $totalCharges;

            // 4) Générer les écritures de clôture
            $ecrituresCloture = [];

            // 4.1) Pour chaque compte de produits (classe 7), on le "débéte" pour revenir à 0
            foreach ($produitsParCompte as $compteProd => $montantProd) {
                if ($montantProd <= 0) continue; // ignore comptes soldés à 0 ou négatifs
                $ecrituresCloture[] = [
                    'date'               => Carbon::createFromDate($annee, 12, 31)->format('Y-m-d H:i:s'),
                    'date_livr'               => Carbon::createFromDate($annee, 12, 31)->format('Y-m-d H:i:s'),

                    'numero_facture'     => 'CL' . $annee,       // convention de clôture
                    'compte'             => $compteProd,
                    'debit'              => $montantProd,        // solder le crédit sur compte 7 par un débit
                    'credit'             => 0,
                    'contre_partie'      => null,                // sera précisé plus bas sur ligne Résultat
                    'rubrique_tva'       => null,
                    'compte_tva'         => null,
                    'type_journal'       => 'CL',                // journal spécial « Clôture »
                    'categorie'          => 'Clôture',
                    'prorat_de_deduction'=> null,
                    'piece_justificative'=> null,                // sera mis à jour dans updatePieceJustificative
                    'libelle'            => "Clôture compte $compteProd",
                    'filtre_selectionne' => null,
                    'societe_id'         => $societeId,
                    'numero_piece'       => null,                // sera ignoré ou généré via Controller store()
                ];
            }

            // 4.2) Pour chaque compte de charges (classe 6), on le "crédite" pour revenir à 0
            foreach ($chargesParCompte as $compteCh => $montantCh) {
                if ($montantCh <= 0) continue;
                $ecrituresCloture[] = [
                    'date'               => Carbon::createFromDate($annee, 12, 31)->format('Y-m-d H:i:s'),
                    'numero_facture'     => 'CL' . $annee,
                    'compte'             => $compteCh,
                    'debit'              => 0,
                    'credit'             => $montantCh,       // solder le compte 6 par un crédit
                    'contre_partie'      => null,
                    'rubrique_tva'       => null,
                    'compte_tva'         => null,
                    'type_journal'       => 'CL',
                    'categorie'          => 'Clôture',
                    'prorat_de_deduction'=> null,
                    'piece_justificative'=> null,
                    'libelle'            => "Clôture compte $compteCh",
                    'filtre_selectionne' => null,
                    'societe_id'         => $societeId,
                    'numero_piece'       => null,
                ];
            }

            // 4.3) Écriture du résultat net, direction selon signe
            if ($resultNet !== 0) {
                if ($resultNet > 0) {
                    // Bénéfice → créditer le compte 120 et débiter la somme totale des soldes de comptes 7
                    $ecrituresCloture[] = [
                        'date'               => Carbon::createFromDate($annee, 12, 31)->format('Y-m-d H:i:s'),
                        'numero_facture'     => 'CL' . $annee,
                        'compte'             => '120',             // compte Résultat de l’exercice (bénéfice)
                        'debit'              => 0,
                        'credit'             => $resultNet,
                        'contre_partie'      => null,
                        'rubrique_tva'       => null,
                        'compte_tva'         => null,
                        'type_journal'       => 'CL',
                        'categorie'          => 'Clôture',
                        'prorat_de_deduction'=> null,
                        'piece_justificative'=> null,
                        'libelle'            => "Bénéfice exercice $annee",
                        'filtre_selectionne' => null,
                        'societe_id'         => $societeId,
                        'numero_piece'       => null,
                    ];
                } else {
                    // Perte (résultat < 0) → débiter le compte 129 et créditer la somme totale des soldes de comptes 6
                    $montantPerte = abs($resultNet);
                    $ecrituresCloture[] = [
                        'date'               => Carbon::createFromDate($annee, 12, 31)->format('Y-m-d H:i:s'),
                        'numero_facture'     => 'CL' . $annee,
                        'compte'             => '129',             // compte Perte de l’exercice
                        'debit'              => $montantPerte,
                        'credit'             => 0,
                        'contre_partie'      => null,
                        'rubrique_tva'       => null,
                        'compte_tva'         => null,
                        'type_journal'       => 'CL',
                        'categorie'          => 'Clôture',
                        'prorat_de_deduction'=> null,
                        'piece_justificative'=> null,
                        'libelle'            => "Perte exercice $annee",
                        'filtre_selectionne' => null,
                        'societe_id'         => $societeId,
                        'numero_piece'       => null,
                    ];
                }
            }

            // 5) Insérer toutes les écritures de clôture en base
            foreach ($ecrituresCloture as $ligneCloture) {
                OperationCourante::create($ligneCloture);
            }

            // 6) Mettre à jour le statut de clôture (vous pouvez créer un champ supplémentaire dans votre table exercices)
            // Par exemple, si vous avez un modèle Exercice avec un champ "cloture" :
            // Exercice::where('societe_id', $societeId)->where('annee', $annee)->update(['cloture' => true]);

            DB::commit();
            return response()->json([
                'message'       => "Exercice $annee clôturé avec succès.",
                'totalCharges'  => $totalCharges,
                'totalProduits' => $totalProduits,
                'resultNet'     => $resultNet,
            ], 200);
        }
        catch (\Exception $e) {
            DB::rollBack();
            Log::error("Erreur lors de la clôture de l’exercice $annee : " . $e->getMessage());
            return response()->json([
                'error' => "Impossible de clore l’exercice : " . $e->getMessage()
            ], 500);
        }
    }



 /**
     * Upload, parse PDF page par page, extraire avec l'IA,
     * puis persister directement dans operation_courante.
     */
    public function extractPdf(Request $request, Parser $parser)
    {
        // 1) Validation
        $request->validate([
            'pdf' => 'required|file|mimes:pdf|max:10240',
        ]);

        // 2) Store temporarily
        $path = $request->file('pdf')->store('pdfs');
        $file = storage_path("app/{$path}");

        // 3) Retrieve current company ID
        $socId = Session::get('societeId');
        if (! $socId) {
            return response()->json(['error' => 'Société non définie'], 400);
        }

        // 4) Instantiate Guzzle for OpenAI
        $guzzle = new GuzzleClient([
            'base_uri' => config('services.openai.base_uri', 'https://api.openai.com'),
            'timeout'  => config('services.openai.timeout', 60),
        ]);

        $created = [];

        // 5) Parse PDF and loop pages
        $pdf = $parser->parseFile($file);
        $pages = $pdf->getPages();

        foreach ($pages as $page) {
            // Take up to first 3000 chars per page
            $chunk = mb_substr($page->getText(), 0, 3000);

            // 6) Call OpenAI chat/completions
            $response = $guzzle->post('/v1/chat/completions', [
                'headers' => [
                    'Authorization' => 'Bearer ' . config('services.openai.key'),
                    'Content-Type'  => 'application/json',
                ],
                'json' => [
                    'model'       => 'gpt-4',
                    'temperature' => 0.0,
                    'messages'    => [
                        [
                            'role'    => 'system',
                            'content' => 'Extrait JSON de lignes de facture : date, numero_facture, compte, libelle, debit, credit, contre_partie, rubrique_tva.'
                        ],
                        [
                            'role'    => 'user',
                            'content' => $chunk,
                        ],
                    ],
                ],
            ]);

            $body = (string) $response->getBody();
            $json = json_decode($body, true);

            // Extract content field
            $content = $json['choices'][0]['message']['content'] ?? '[]';
            $rows = json_decode($content, true) ?: [];

            // 7) Persist each extracted line
            foreach ($rows as $r) {
                $record = OperationCourante::create([
                    'date'           => $r['date']           ?? now()->format('Y-m-d H:i:s'),
                    'numero_facture' => $r['numero_facture'] ?? null,
                    'compte'         => $r['compte']         ?? null,
                    'libelle'        => $r['libelle']        ?? null,
                    'debit'          => $r['debit']          ?? 0,
                    'credit'         => $r['credit']         ?? 0,
                    'contre_partie'  => $r['contre_partie']  ?? null,
                    'rubrique_tva'   => $r['rubrique_tva']   ?? null,
                    'type_journal'   => 'Achats',
                    'categorie'      => 'Achat',
                    'societe_id'     => $socId,
                ]);

                $created[] = $record;
            }
        }

        // 8) Return created records
        return response()->json($created);
    }



//     public function selectFolder(Request $request)
// {

//     $folderId = $request->query('id');
//     $societeId = session('societeId');

//     // Optionnel : récupération réelle du dossier
//     // $folder = Folder::find($folderId);
//     $folder = Folder::where('societe_id', $societeId)->where('folder_id', $folderId)->first();
//     if (!$folder) {
//         abort(404, 'Dossier introuvable.');
//     }

//     // Récupération des fichiers où le champ folders est égal à l'ID du dossier
//     $files = File::where('societe_id', $societeId)->where('folders', $folderId)->get();
// dd($files);
//     // Traitement ou affichage
//     $folders_banque = [$folder];
//     $files_banque = $files;
// }

public function selectFolder(Request $request)
{
    $folderId = $request->query('id');
    $societeId = session('societeId');

    // Vérification du dossier parent
    $parentFolder = Folder::where('societe_id', $societeId)->where('id', $folderId)->first();
    if (!$parentFolder) {
        return response()->json(['error' => 'Dossier introuvable.'], 404);
    }

    // Récupération des sous-dossiers du dossier sélectionné
    $folders_banque = Folder::where('societe_id', $societeId)
                            ->where('folder_id', $folderId)
                            ->get();

    // Récupération des fichiers du dossier sélectionné
    $files_banque = File::where('societe_id', $societeId)
                        ->where('folders', $folderId)
                        ->get();

    return response()->json([
        'folders_banque' => $folders_banque,
        'files_banque' => $files_banque
    ]);
}


   public function index(Request $request)
{
    // 1) Récupérer l'ID de la société depuis la session
    $societeId = session('societeId');
$folders_banque = Folder::where('societe_id', $societeId)->where('type_folder', 'banque')->get();

    // 2) Récupérer l'id à éditer depuis la query-string (ou null)
    $editId = $request->query('edit');

    // 3) Tes requêtes existantes
    $planComptable = collect();
    $files         = collect();
    $files_banque  = collect();
    $files_achat   = collect();
    $files_vente   = collect();

    if ($societeId) {
        $planComptable = PlanComptable::where('societe_id', $societeId)->get();

        $files        = File::where('societe_id', $societeId)->where('type', 'caisse')->get();
        $files_banque = File::where('societe_id', $societeId)->where('type', 'banque')->get();
        $files_achat  = File::where('societe_id', $societeId)->where('type', 'achat')->get();
        $files_vente  = File::where('societe_id', $societeId)->where('type', 'vente')->get();
    }
return view('Operation_Courante', compact(
    'files',
    'planComptable',
    'files_banque',
    'files_achat',
    'files_vente',
    'editId',
    'folders_banque'
));

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

    $societeId = session('societeId');
    Log::info("ID société: $societeId");

    if (! $societeId) {
        Log::error('Aucune société sélectionnée en session');
        return response()->json(['error' => 'Aucune société sélectionnée en session'], 400);
    }

    // 1️⃣ Validation du payload
    $validatedData = $request->validate([
        'lignes'                       => 'required|array',
        'lignes.*.id'                  => 'nullable|integer',
        'lignes.*.date'                => 'nullable|date',
        'lignes.*.date_livr'           => 'nullable|date',
        'lignes.*.numero_dossier'      => 'nullable|string',
        'lignes.*.numero_facture'      => 'nullable|string',
        'lignes.*.compte'              => 'nullable|string',
        'lignes.*.debit'               => 'nullable|numeric|min:0',
        'lignes.*.credit'              => 'nullable|numeric|min:0',
        'lignes.*.contre_partie'       => 'nullable|string',
        'lignes.*.rubrique_tva'        => 'nullable|string',
        'lignes.*.compte_tva'          => 'nullable|string',
        'lignes.*.type_journal'        => 'nullable|string',
        'lignes.*.categorie'           => 'nullable|string',
        'lignes.*.prorat_de_deduction' => 'nullable|string',
        'lignes.*.libelle'             => 'nullable|string',
        'lignes.*.filtre_selectionne'  => 'nullable|string|in:libre,contre-partie',
        'lignes.*.piece_justificative' => 'nullable|string',
    ]);

    Log::info('Validation des données réussie');

    $responseData = [];

    try {
        foreach ($validatedData['lignes'] as $ligneData) {
            Log::info('Traitement de la ligne', $ligneData);

            // 2️⃣ Préparation de la date principale
            $lineDateObj = !empty($ligneData['date'])
                ? \Carbon\Carbon::parse($ligneData['date'])
                : \Carbon\Carbon::now();

            // 2a) Ignorer les lignes vides du jour
            if (
                $lineDateObj->format('Y-m-d') === \Carbon\Carbon::now()->format('Y-m-d')
                && (empty($ligneData['numero_facture']) || $ligneData['numero_facture'] === 'N/A')
                && empty($ligneData['compte'])
                && ((isset($ligneData['debit'])  ? $ligneData['debit']  : 0) == 0)
                && ((isset($ligneData['credit']) ? $ligneData['credit'] : 0) == 0)
            ) {
                Log::info("Ligne vide ignorée");
                continue;
            }

            // 3️⃣ Construction du tableau de données
            $data = [
                'numero_facture'      => $ligneData['numero_facture']      ?? null,
                'compte'              => $ligneData['compte']              ?? null,
                'debit'               => $ligneData['debit']               ?? 0,
                'credit'              => $ligneData['credit']              ?? 0,
                'contre_partie'       => $ligneData['contre_partie']       ?? null,
                'numero_dossier'      => $ligneData['numero_dossier']      ?? null,
                'rubrique_tva'        => $ligneData['rubrique_tva']        ?? null,
                'compte_tva'          => $ligneData['compte_tva']          ?? null,
                'prorat_de_deduction' => $ligneData['prorat_de_deduction'] ?? null,
                'type_journal'        => $ligneData['type_journal']        ?? null,
                'categorie'           => $ligneData['categorie']           ?? null,
                'piece_justificative' => $ligneData['piece_justificative'] ?? null,
                'libelle'             => $ligneData['libelle']             ?? null,
                'filtre_selectionne'  => $ligneData['filtre_selectionne']  ?? null,
                'societe_id'          => $societeId,
                'date'                => $lineDateObj->format('Y-m-d H:i:s'),
                'date_livr'           => $ligneData['date_livr']
                                          ? \Carbon\Carbon::parse($ligneData['date_livr'])->format('Y-m-d H:i:s')
                                          : $lineDateObj->format('Y-m-d H:i:s'),
            ];

            // 4️⃣ Opérations Diverses spécifiques
            if (($data['categorie'] ?? '') === "Opérations Diverses") {
                $existing = \App\Models\OperationCourante::where([
                    ['societe_id',    $societeId],
                    ['numero_facture',$data['numero_facture']],
                    ['compte',        $data['compte']],
                ])->get();

                if ($existing->count() >= 2) {
                    Log::info("Deux lignes 'Opérations Diverses' existantes, ignorée.");
                    continue;
                }

                Log::info("Création directe Opérations Diverses");
                $record = \App\Models\OperationCourante::create($data);
                $record->date     = \Carbon\Carbon::parse($record->date)->format('d/m/Y');
                $record->piece_justificative = $data['piece_justificative'];
                $responseData[] = $record;
                continue;
            }

            // 5️⃣ Gestion doublons en session
            $sessionLines = session('lignes_en_cours', []);
            $isDuplicate  = collect($sessionLines)->contains(fn($existing) =>
                ($existing['numero_facture'] ?? '') === ($data['numero_facture'] ?? '') &&
                ($existing['compte']         ?? '') === ($data['compte'] ?? '')
            );

            if ($isDuplicate) {
                Log::info("Doublon détecté en session, ignoré.");
                continue;
            }

            $sessionLines[]             = $data;
            session(['lignes_en_cours' => $sessionLines]);

            // 6️⃣ Création ou mise à jour en base (sans date_livr dans le where)
            $existingLigne = \App\Models\OperationCourante::where([
                ['societe_id',    $societeId],
                ['numero_facture',$data['numero_facture']],
                ['compte',        $data['compte']],
                ['debit',         $data['debit']],
                ['credit',        $data['credit']],
                ['date',          $data['date']],
            ])->first();

            if ($existingLigne) {
                // Mise à jour y compris date_livr
                $existingLigne->update([
                    'date_livr'           => $data['date_livr'],
                    'piece_justificative' => $data['piece_justificative'],
                ]);
                $record = $existingLigne;
            } else {
                $record = \App\Models\OperationCourante::create($data);
            }

            // 7️⃣ Formatage pour réponse
            $record->date     = \Carbon\Carbon::parse($record->date)->format('d/m/Y');
            $record->piece_justificative = $data['piece_justificative'];
            $responseData[]  = $record;
        }

        Log::info('Opérations enregistrées avec succès');
        return response()->json(['data' => $responseData], 200);

    } catch (\Exception $e) {
        Log::error('Erreur sauvegarde lignes: '.$e->getMessage());
        Log::error($e->getTraceAsString());
        return response()->json([
            'error'   => 'Erreur lors de la sauvegarde des lignes.',
            'details' => $e->getMessage(),
        ], 500);
    }
}

public function getOperations(Request $request)
{
    $societeId = session('societeId');
    if (!$societeId) {
        return response()->json(['error' => 'Aucune société sélectionnée dans la session'], 400);
    }

    // 🔧 Séparer mois et année si nécessaire
    $moisInput = $request->input('mois');
    $annee = $request->input('annee');
    $codeJournal = $request->input('code_journal');
    $operationType = $request->input('operation_type');

    // 🔁 Si mois contient un tiret (ex : "02-2025"), on découpe
    $mois = $moisInput;
    if (strpos($moisInput, '-') !== false) {
        [$mois, $anneeFromMois] = explode('-', $moisInput);
        if (!$annee) $annee = $anneeFromMois;
    }
    // dd($mois);

    // 🔍 Requête de base
    $query = OperationCourante::where('societe_id', $societeId);

    if ($operationType) {
        $query->where('categorie', $operationType);
    }

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

    // ✅ Ajouter ligne vide en tête
    $operations->prepend([
        'id' => '',
        'date' => '',
        'date_livr' => '',
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
    // Récupérer le code journal (optionnel)
    $codeJournal = trim($request->query('code_journal'));

    // Récupération des contre-parties depuis les journaux (type "Ventes")
    $journalQuery = \App\Models\Journal::query();
    $journalQuery->where('type_journal', 'Ventes');
    if ($codeJournal !== "") {
        $journalQuery->where('code_journal', $codeJournal);
    }
    $journalCP = $journalQuery->distinct()
                    ->pluck('contre_partie')
                    ->filter()
                    ->values();

    // Récupération des comptes du plan comptable commençant par "7"
    $planCP = \App\Models\PlanComptable::where('compte', 'like', '7%')
                    ->distinct()
                    ->pluck('compte')
                    ->filter()
                    ->values();

    // Fusion des deux listes en supprimant les doublons
    $merged = $journalCP->merge($planCP)->unique()->values();

    Log::info("Contre-parties retournées :", $merged->toArray());

    return response()->json($merged);
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
    $journaux = Journal::select('code_journal', 'intitule', 'type_journal', 'contre_partie')
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
    $rubriques = Racine::select('Num_racines','categorie', 'Nom_racines', 'Taux','compte_tva')
        ->where('type', 'Ca imposable')
        // ->whereIn('Num_racines')  // Ajouter la condition pour les num_racines autorisés

        ->get();

    // Organiser les rubriques par catégorie
    $rubriquesParCategorie = [];
    foreach ($rubriques as $rubrique) {
        $rubriquesParCategorie[$rubrique->categorie]['rubriques'][] = [
            'Nom_racines' => $rubrique->Nom_racines,
            'Num_racines' => $rubrique->Num_racines,
            'Taux' => $rubrique->Taux,
            'compte_tva' => $rubrique->compte_tva,
        ];
    }

    // Retourner les rubriques organisées
    return response()->json(['rubriques' => $rubriquesParCategorie]);
}

    // Récupère les rubriques TVA pour un type d'opération 'Achat'
   public function getRubriquesTva()
{
    // 1) Numéros de racines à exclure
    $exclusions = ['147', '151', '152', '148', '144'];

    // 2) Récupération des rubriques dans l'ordre inversé de la base
    $rubriques = Racine::select('Num_racines', 'categorie', 'Nom_racines', 'Taux','compte_tva')
        ->where('type', 'Les déductions')
        ->whereNotIn('Num_racines', $exclusions)
        ->orderBy('categorie', 'desc') // Inverser l'ordre de la base
        ->get();

    // 3) Regroupement par catégorie principale
    $categoriesTemp = [];
    foreach ($rubriques as $rubrique) {
        [$main, $sub] = array_map('trim', explode('/', $rubrique->categorie) + [1 => null]);

        if (!isset($categoriesTemp[$main])) {
            $categoriesTemp[$main] = [
                'subCategories' => [],
                'rubriques'     => []
            ];
        }

        if ($sub && !in_array($sub, $categoriesTemp[$main]['subCategories'])) {
            $categoriesTemp[$main]['subCategories'][] = $sub;
        }

        $categoriesTemp[$main]['rubriques'][] = [
            'Num_racines' => $rubrique->Num_racines,
            'Nom_racines' => $rubrique->Nom_racines,
            'Taux'        => $rubrique->Taux,
            'compte_tva' => $rubrique->compte_tva,

        ];
    }

    // 4) Numérotation et préparation de la structure finale dans l'ordre inversé
    $categories = [];
    $counter = 1;
    // Parcours des clés dans l'ordre obtenu (déjà inversé)
    foreach (array_keys($categoriesTemp) as $name) {
        $data = $categoriesTemp[$name];
        $categories[] = [
            'categoryId'   => $counter,
            'categoryName' => "$counter. $name",
            'subCategories'=> $data['subCategories'],
            'rubriques'    => $data['rubriques'],
        ];
        $counter++;
    }

    // 5) Retour de la réponse JSON
    return response()->json([
        'categories' => $categories
    ]);
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


public function getPlanComptable(Request $request)
{
    // Récupération de l'identifiant de la société depuis la query string
    $societeId = $request->query('societe_id');

    if (!$societeId) {
        return response()->json(['error' => 'Aucune société sélectionnée'], 400);
    }

    try {
        // Récupérer tous les comptes du plan comptable pour la société donnée
        $comptes = PlanComptable::where('societe_id', $societeId)
            ->select('compte')
            ->distinct() // Pour obtenir uniquement des comptes uniques
            ->get();

        // Vérifier si des comptes sont trouvés
        if ($comptes->isEmpty()) {
            return response()->json(['error' => 'Aucun compte trouvé pour cette société'], 404);
        }

        return response()->json($comptes, 200);
    } catch (\Exception $e) {
        Log::error('Erreur lors de la récupération des comptes : ' . $e->getMessage());
        return response()->json(['error' => 'Erreur serveur lors de la récupération des comptes'], 500);
    }
}

public function getFiles(Request $request)
{
    $societeId = session('societeId');

    if (!$societeId) {
        return response()->json(['error' => 'Aucune société trouvée dans la session'], 404);
    }

    // Exemple de filtrage similaire à votre code
    $query = File::where('societe_id', $societeId)
                 ->where('type', 'achat');



    $files = $query->get();
    return response()->json($files);
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
    $societe_id = $request->get('societe_id');

    if (!$societe_id) {
        return response()->json(['error' => 'ID de société manquant'], 400);
    }

    // Facultatif : log pour debug
    logger("Requête comptes TVA achats pour societe_id = $societe_id");

    // Récupérer les comptes TVA pour les achats qui commencent par '4455'
    $ComptesTva = PlanComptable::where('societe_id', $societe_id)
        ->where('compte', 'like', '4455%')
        ->get(['compte', 'intitule']);

    if ($ComptesTva->isEmpty()) {
        return response()->json(['error' => 'Aucun compte TVA pour les achats trouvé'], 404);
    }

    return response()->json($ComptesTva);
}


public function getCompteTvaVente(Request $request)
{
    $societe_id = $request->get('societe_id');
    if (!$societe_id) {
        return response()->json(['error' => 'ID de société manquant'], 400);
    }

    // Debug temporaire
    logger("Requête pour societe_id = $societe_id");

    $ComptesTva = PlanComptable::where('compte', 'like', '3455%')
        ->where('societe_id', $societe_id)
        ->get(['compte', 'intitule']);

    if ($ComptesTva->isEmpty()) {
        return response()->json(['error' => 'Aucun compte TVA pour les ventes trouvé'], 404);
    }

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
    $societeId = session('societeId');

    if (!$societeId) {
        return response()->json(['error' => 'Aucune société sélectionnée'], 400);
    }

    try {
        $fournisseurs = Fournisseur::where('societe_id', $societeId)
            ->where('compte', 'LIKE', '44%')
            ->get(['compte', 'intitule', 'contre_partie', 'rubrique_tva']);

        $fournisseursAvecDetails = $fournisseurs->map(function ($f) {
            // Valeurs par défaut
            $f->taux_tva = 0;
            $f->compte_tva = null;

            if (empty($f->rubrique_tva)) {
                // Aucun traitement si rubrique TVA manquante
                return $f;
            }

            // Extraire le numéro depuis la rubrique_tva (ex: "140: Service" => 140)
            $split = explode(':', $f->rubrique_tva);
            $code_rubrique = isset($split[0]) ? trim(preg_replace('/[^\d]/', '', $split[0])) : null;

            if (!$code_rubrique) {
                // Rubrique TVA mal formatée
                return $f;
            }

            // Rechercher dans la table racines
            $racine = Racine::where('num_racines', $code_rubrique)->first();

            if ($racine) {
                $f->taux_tva = (float) $racine->Taux;
                $f->compte_tva = $racine->compte_tva; // ✅ ici on récupère `compte_tva` et non `num_racines`
            }

            return $f;
        });

        return response()->json($fournisseursAvecDetails);
    } catch (\Exception $e) {
        return response()->json([
            'error' => 'Erreur lors de la récupération des fournisseurs : ' . $e->getMessage()
        ], 500);
    }
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



// OperationCouranteController.php

public function edit(string $piece)
{
    $societeId = session('societeId');
    if (! $societeId) {
        abort(403, "Société non définie en session.");
    }

    // 1) charger vos planComptable, files, etc. comme avant...
    $planComptable = PlanComptable::where('societe_id', $societeId)->get();
    $files         = File::where('societe_id', $societeId)->where('type','caisse')->get();
    $files_banque  = File::where('societe_id', $societeId)->where('type','banque')->get();
    $files_achat   = File::where('societe_id', $societeId)->where('type','achat')->get();
    $files_vente   = File::where('societe_id', $societeId)->where('type','vente')->get();

    // 2) récupérer toutes les lignes de cette pièce
    $lignes = OperationCourante::where('societe_id', $societeId)
                ->where('piece_justificative', $piece)
                ->orderBy('date')
                ->get();

    if ($lignes->isEmpty()) {
        abort(404, "Aucune écriture pour la pièce “{$piece}”.");
    }

    // 3) extraire les codes journaux uniques
    $journaux = $lignes->pluck('type_journal')->unique()->values();

    return view('Operation_Courante', [
        'planComptable' => $planComptable,
        'files'         => $files,
        'files_banque'  => $files_banque,
        'files_achat'   => $files_achat,
        'files_vente'   => $files_vente,
        'lignesPiece'   => $lignes,
              'journaux'      => $journaux,
        'editPiece'     => $piece,
    ]);
}

public function apiByPiece(string $piece)
{
    $societeId = session('societeId');
    if (! $societeId) {
        return response()->json([], 403);
    }

    $lignes = OperationCourante::where('societe_id', $societeId)
                ->where('piece_justificative', $piece)
                ->orderBy('date')
                ->get();

    return response()->json($lignes);
}



}
