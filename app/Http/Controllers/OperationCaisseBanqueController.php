<?php

namespace App\Http\Controllers;
use App\Models\PlanComptable;
use Illuminate\Http\Request;
use App\Models\Societe;
use App\Models\OperationCourante;
use App\Models\Racine;
use App\Models\Lettrage;
use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Shared\Date;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use App\Models\File;  

class OperationCaisseBanqueController extends Controller
{

public function destroy(Request $request)
{                
        $societeId = session('societeId');

        // Vérifier si le societeId est valide, sinon renvoyer une erreur
        if (!$societeId) {
            return response()->json(['error' => 'Societe ID non trouvé dans la session.'], 400);
        }

        // Validation des IDs des opérations à supprimer
        $validatedData = $request->validate([
            'ids' => 'required|array', // S'assurer que 'ids' est un tableau
            'ids.*' => 'exists:operation_courante,id', // Vérifier que chaque ID existe
        ]);

        // Supprimer les opérations
        OperationCourante::whereIn('id', $validatedData['ids'])
                        ->where('societe_id', $societeId)
                        ->delete();

        // Retourner une réponse JSON
        return response()->json(['message' => 'Opérations supprimées avec succès.']);
}

public function storeBanque(Request $request)
{
    // dd($request->all());
    $societeId = session('societeId');

    $racine = Racine::where('societe_id', $societeId)
        ->where('num_racines', 142)
        ->first();

    $taux = is_numeric($racine->Taux) ? (float) $racine->Taux : 0;

    $validatedData = $request->validate([
        'date' => 'required|date',
        'numero_dossier' => 'nullable|string',
        'fact_lettrer' => 'nullable|string',
        'compte' => 'nullable|string',
        'libelle' => 'nullable|string',
        'debit' => 'nullable|numeric',
        'credit' => 'nullable|numeric',
        'contre_partie' => 'nullable|string',
        'piece_justificative' => 'nullable|string',
        'taux_ras_tva' => 'nullable|string',
        'nature_op' => 'nullable|string',
        'date_lettrage' => 'nullable|date',
        'mode_pay' => 'nullable|string',
        'type_journal' => 'nullable|string',
        'saisie_choisie' => 'required|string',
        'file_id' => 'nullable|integer',
    ]);

    /* =======================================================
       🔥 SAUVEGARDE DE LA VALEUR ORIGINALE AVEC ID
    ======================================================= */
    $factletOriginal = $validatedData['fact_lettrer'] ?? '';

    $validatedData['file_id'] = $request->input('file_id');
    $validatedData['numero_facture'] = 'pas de facture';
    $validatedData['societe_id'] = $societeId;
    $validatedData['categorie'] = 'Banque';

    $validatedData['reste_montant_lettre'] = !empty($factletOriginal)
        ? 0.00
        : ($validatedData['debit'] ?? $validatedData['credit'] ?? 0.00);

    /* =======================================================
       ✅ OPÉRATION PRINCIPALE (AVEC fact_lettrer + ID)
    ======================================================= */
    $operationPrincipale = OperationCourante::create($validatedData);

    /* =======================================================
       🔗 TRAITEMENT DU LETTRAGE
    ======================================================= */
    if (!empty($factletOriginal)) {

        $factures = explode('&', $factletOriginal);

        $acompte = !empty($validatedData['debit']) && $validatedData['debit'] != 0
            ? $validatedData['debit']
            : (!empty($validatedData['credit']) ? $validatedData['credit'] : 0);

        $resteAcompte = $acompte;

        foreach ($factures as $factureStr) {

            $factureStr = trim($factureStr);
            if (empty($factureStr)) continue;

            $parts = explode('|', $factureStr);
            if (count($parts) !== 4) continue;

            $operationId = (int) trim($parts[0]);
            $numero      = trim($parts[1]);

            if ($operationId <= 0) continue;

            $operation = OperationCourante::find($operationId);
            if (!$operation || $resteAcompte <= 0) break;

            $montantLettrable = min($resteAcompte, $operation->reste_montant_lettre);

            Lettrage::create([
                'NFacture'    => $numero,
                'Acompte'     => $montantLettrable,
                'compte'      => $validatedData['compte'],
                'id_operation'=> $operationId,
                'id_user'     => auth()->id(),
                'lettrage_id' => $operationPrincipale->id,
            ]);

            $operation->reste_montant_lettre -= $montantLettrable;
            $operation->reste_montant_lettre = max($operation->reste_montant_lettre, 0);
            $operation->save();

            $resteAcompte -= $montantLettrable;
        }

        if ($resteAcompte > 0) {
            echo "<script>alert('L\\'acompte est supérieur au total des restes à lettrer.');</script>";
        }
    }

    /* =======================================================
       🔁 CONTRE-PARTIES (AVEC fact_lettrer + ID)
    ======================================================= */
    if ($validatedData['saisie_choisie'] === 'contre-partie') {

        if (str_starts_with($validatedData['compte'], '6147')) {

            $contrePartieData1 = [
                'date' => $validatedData['date'],
                'fact_lettrer' => $factletOriginal,
                'compte' => $racine->compte_tva,
                'contre_partie' => $validatedData['contre_partie'],
                'libelle' => $validatedData['libelle'],
                'debit' => $validatedData['debit'] * ($taux / 100),
                'credit' => $validatedData['credit'],
                'piece_justificative' => $validatedData['piece_justificative'],
                'taux_ras_tva' => $validatedData['taux_ras_tva'],
                'nature_op' => $validatedData['nature_op'],
                'date_lettrage' => $validatedData['date_lettrage'],
                'mode_pay' => $validatedData['mode_pay'],
                'type_journal' => $validatedData['type_journal'],
                'numero_facture' => 'pas de facture',
                'societe_id' => $societeId,
                'categorie' => 'Banque',
                'file_id' => $validatedData['file_id'],
                'reste_montant_lettre' => !empty($factletOriginal) ? 0.00 : ($validatedData['debit'] ?? 0),
            ];

            $contrePartieData2 = [
                'date' => $validatedData['date'],
                'fact_lettrer' => $factletOriginal,
                'compte' => $validatedData['contre_partie'],
                'contre_partie' => $validatedData['compte'],
                'libelle' => $validatedData['libelle'],
                'debit' => $validatedData['credit'],
                'credit' => $validatedData['debit'] + ($validatedData['debit'] * ($taux / 100)),
                'piece_justificative' => $validatedData['piece_justificative'],
                'taux_ras_tva' => $validatedData['taux_ras_tva'],
                'nature_op' => $validatedData['nature_op'],
                'date_lettrage' => $validatedData['date_lettrage'],
                'mode_pay' => $validatedData['mode_pay'],
                'type_journal' => $validatedData['type_journal'],
                'numero_facture' => 'pas de facture',
                'societe_id' => $societeId,
                'categorie' => 'Banque',
                'file_id' => $validatedData['file_id'],
                'reste_montant_lettre' => !empty($factletOriginal) ? 0.00 : ($validatedData['credit'] ?? 0),
            ];

            OperationCourante::create($contrePartieData1);
            OperationCourante::create($contrePartieData2);

        } else {

            $contrePartieData = [
                'date' => $validatedData['date'],
                'fact_lettrer' => $factletOriginal,
                'compte' => $validatedData['contre_partie'],
                'contre_partie' => $validatedData['compte'],
                'libelle' => $validatedData['libelle'],
                'debit' => $validatedData['credit'],
                'credit' => $validatedData['debit'],
                'piece_justificative' => $validatedData['piece_justificative'],
                'taux_ras_tva' => $validatedData['taux_ras_tva'],
                'nature_op' => $validatedData['nature_op'],
                'date_lettrage' => $validatedData['date_lettrage'],
                'mode_pay' => $validatedData['mode_pay'],
                'type_journal' => $validatedData['type_journal'],
                'numero_facture' => 'pas de facture',
                'societe_id' => $societeId,
                'categorie' => 'Banque',
                'file_id' => $validatedData['file_id'],
                'reste_montant_lettre' => !empty($factletOriginal) ? 0.00 : ($validatedData['credit'] ?? 0),
            ];

            OperationCourante::create($contrePartieData);
        }
    }

    return response()->json(['message' => 'Données enregistrées avec succès.']);
}

public function getBanque(Request $request)
{
    // Récupérer le societe_id depuis la session
    $societeId = session('societeId');

    // Vérifier si le societeId est valide, sinon renvoyer une erreur
    if (!$societeId) {
        return response()->json(['error' => 'Societe ID non trouvé dans la session.'], 400);
    }

    // Récupérer les opérations courantes pour la société avec la catégorie 'banque'
    // et inclure les fichiers associés via la relation 'files'
    $operations = OperationCourante::with('file')
                        ->where('societe_id', $societeId)
                        ->where('categorie', 'banque')
                        ->get();

    // Vérifier si des opérations ont été trouvées
    if ($operations->isEmpty()) {
        return response()->json(['message' => 'Aucune donnée trouvée pour cette société avec la catégorie "banque".'], 404);
    }

    // Retourner les données récupérées sous forme de réponse JSON
    return response()->json($operations);
}

public function get(Request $request)
{
        // Récupérer le societe_id depuis la session
        $societeId = session('societeId');
        
        // Vérifier si le societeId est valide, sinon renvoyer une erreur
        if (!$societeId) {
            return response()->json(['error' => 'Societe ID non trouvé dans la session.'], 400);
        }

        // Récupérer les opérations courantes pour la société et avec la catégorie 'caisse'
        $operations = OperationCourante::where('societe_id', $societeId)
                                    ->where('categorie', 'caisse')
                                    ->get();

        // Vérifier si des opérations ont été trouvées
        if ($operations->isEmpty()) {
            return response()->json(['message' => 'Aucune donnée trouvée pour cette société avec la catégorie "caisse".'], 404);
        }

        // Retourner les données récupérées sous forme de réponse JSON
        return response()->json($operations);
}

public function importerOperationCouranteBanque(Request $request)
{
    $societeId = session('societeId');

    $fichier = $request->file('importFile');
    if (!$fichier) {
        return response()->json(['error' => 'Aucun fichier fourni.'], 400);
    }

    try {
        $spreadsheet = IOFactory::load($fichier->getRealPath());
    } catch (\Exception $e) {
        return response()->json(['error' => 'Erreur lors de la lecture du fichier : ' . $e->getMessage()], 500);
    }

    $sheet = $spreadsheet->getActiveSheet();
    $rows = $sheet->toArray();

    if (count($rows) <= 1) {
        return response()->json(['error' => 'Le fichier est vide ou ne contient qu’un en-tête.'], 400);
    }

    // Récupère la valeur directe (non un index)
    $typeJournalValue = (int) $request->input('typeJournal');

    // Indices des colonnes dans le fichier
    $indexes = [
        'date'             => (int) $request->input('date'),
        'modePaiement'     => (int) $request->input('modePaiement'),
        'compte'           => (int) $request->input('compte'),
        'libelle'          => (int) $request->input('libelle'),
        'debit'            => (int) $request->input('debit'),
        'credit'           => (int) $request->input('credit'),
        'nFactureLettre'   => (int) $request->input('nFactureLettre'),
        'tauxRasTva'       => (int) $request->input('tauxRasTva'),
        'natureOperation'  => (int) $request->input('natureOperation'),
        'dateLettrage'     => (int) $request->input('dateLettrage'),
        'contrePartie'     => (int) $request->input('contrePartie'),
        // Ne PAS inclure 'typeJournal' ici
    ];

    $donnees = array_slice($rows, 1);
    $importees = 0;
    $erreurs = [];

    foreach ($donnees as $ligneIndex => $ligne) {
        if (!array_filter($ligne)) continue;

        try {
            // Ici on récupère une valeur selon la colonne dans le fichier
            $get = function($key) use ($indexes, $ligne) {
                return isset($indexes[$key], $ligne[$indexes[$key]]) ? trim($ligne[$indexes[$key]]) : null;
            };

            // Pour typeJournal, on utilise la variable récupérée directement
            $typeJournal = $typeJournalValue;

            // Gestion des dates et montants (reste inchangé)
            $convertirDate = function ($val) {
                if (is_numeric($val)) {
                    return Date::excelToDateTimeObject($val)->format('Y-m-d');
                } elseif (is_string($val) && strtotime($val)) {
                    return date('Y-m-d', strtotime($val));
                }
                return null;
            };

            $dateImport = $convertirDate($get('date'));
            $dateLettrage = $convertirDate($get('dateLettrage'));

            if (!$dateImport) {
                throw new \Exception("La date est invalide ou absente.");
            }

            $debit = floatval(str_replace(',', '.', preg_replace('/[^0-9,.-]/', '', $get('debit'))));
            $credit = floatval(str_replace(',', '.', preg_replace('/[^0-9,.-]/', '', $get('credit'))));

            OperationCourante::create([
                'date'               => $dateImport,
                'mode_paiement'      => $get('modePaiement'),
                'compte'             => $get('compte'),
                'libelle'            => $get('libelle'),
                'debit'              => $debit ?: 0,
                'credit'             => $credit ?: 0,
                'n_facture_lettrée'  => $get('nFactureLettre'),
                'taux_ras_tva'       => $get('tauxRasTva'),
                'nature_operation'   => $get('natureOperation'),
                'date_lettrage'      => $dateLettrage,
                'contre_partie'      => $get('contrePartie'),
                'type_journal'       => $typeJournal ?: 1, // valeur reçue ou 1 par défaut
                'numero_facture'     => 'pas de facture',
                'societe_id'         => $societeId,
                'categorie'          => 'Banque',
            ]);

            $importees++;
        } catch (\Exception $e) {
            $messageErreur = "Ligne $ligneIndex : Erreur à l’importation – " . $e->getMessage();
            \Log::error($messageErreur, ['ligne' => $ligne]);
            $erreurs[] = $messageErreur;
            continue;
        }
    }

    return response()->json([
        'message' => "$importees opérations importées avec succès.",
        'erreurs' => $erreurs,
    ]);
}

public function importerOperationCouranteCaisse(Request $request)
{
    $societeId = session('societeId');

    // Correction ici : on récupère le bon champ de fichier
    $fichier = $request->file('importFileCaisse');
    if (!$fichier) {
        return response()->json(['error' => 'Aucun fichier fourni.'], 400);
    }

    try {
        $spreadsheet = IOFactory::load($fichier->getRealPath());
    } catch (\Exception $e) {
        return response()->json(['error' => 'Erreur lors de la lecture du fichier : ' . $e->getMessage()], 500);
    }

    $sheet = $spreadsheet->getActiveSheet();
    $rows = $sheet->toArray();

    if (count($rows) <= 1) {
        return response()->json(['error' => 'Le fichier est vide ou ne contient qu’un en-tête.'], 400);
    }

    // Récupère la valeur directe de typeJournal (peut être null ou vide)
    $typeJournalValue = (int) $request->input('typeJournal');

    // Correction des noms de champs pour ceux du formulaire "Caisse"
    $indexes = [
        'date'             => (int) $request->input('dateCaisse'),
        'modePaiement'     => (int) $request->input('modePaiementCaisse'),
        'compte'           => (int) $request->input('compteCaisse'),
        'libelle'          => (int) $request->input('libelleCaisse'),
        'debit'            => (int) $request->input('debitCaisse'),
        'credit'           => (int) $request->input('creditCaisse'),
        'nFactureLettre'   => (int) $request->input('nFactureLettreCaisse'),
        'tauxRasTva'       => (int) $request->input('tauxRasTvaCaisse'),
        'natureOperation'  => (int) $request->input('natureOperationCaisse'),
        'dateLettrage'     => (int) $request->input('dateLettrageCaisse'),
        'contrePartie'     => (int) $request->input('contrePartieCaisse'),
    ];

    $donnees = array_slice($rows, 1);
    $importees = 0;
    $erreurs = [];

    foreach ($donnees as $ligneIndex => $ligne) {
        if (!array_filter($ligne)) continue;

        try {
            $get = function($key) use ($indexes, $ligne) {
                return isset($indexes[$key], $ligne[$indexes[$key]]) ? trim($ligne[$indexes[$key]]) : null;
            };

            $typeJournal = $typeJournalValue;

            $convertirDate = function ($val) {
                if (is_numeric($val)) {
                    return \PhpOffice\PhpSpreadsheet\Shared\Date::excelToDateTimeObject($val)->format('Y-m-d');
                } elseif (is_string($val) && strtotime($val)) {
                    return date('Y-m-d', strtotime($val));
                }
                return null;
            };

            $dateImport = $convertirDate($get('date'));
            $dateLettrage = $convertirDate($get('dateLettrage'));

            if (!$dateImport) {
                throw new \Exception("La date est invalide ou absente.");
            }

            $debit = floatval(str_replace(',', '.', preg_replace('/[^0-9,.-]/', '', $get('debit'))));
            $credit = floatval(str_replace(',', '.', preg_replace('/[^0-9,.-]/', '', $get('credit'))));

            OperationCourante::create([
                'date'               => $dateImport,
                'mode_paiement'      => $get('modePaiement'),
                'compte'             => $get('compte'),
                'libelle'            => $get('libelle'),
                'debit'              => $debit ?: 0,
                'credit'             => $credit ?: 0,
                'n_facture_lettrée'  => $get('nFactureLettre'),
                'taux_ras_tva'       => $get('tauxRasTva'),
                'nature_operation'   => $get('natureOperation'),
                'date_lettrage'      => $dateLettrage,
                'contre_partie'      => $get('contrePartie'),
                'type_journal'       => $typeJournal ?: 1, // 1 par défaut
                'numero_facture'     => 'pas de facture',
                'societe_id'         => $societeId,
                'categorie'          => 'Caisse', // fixe ici car c’est pour la caisse
            ]);

            $importees++;
        } catch (\Exception $e) {
            $messageErreur = "Ligne $ligneIndex : Erreur à l’importation – " . $e->getMessage();
            \Log::error($messageErreur, ['ligne' => $ligne]);
            $erreurs[] = $messageErreur;
        }
    }

    return response()->json([
        'message' => "$importees opérations importées avec succès.",
        'erreurs' => $erreurs,
    ]);
}

public function searchFacture(Request $request)
{
    $compte = $request->input('compte');
    $debit = $request->input('debit');
    $credit = $request->input('credit');
    $societeId = session('societeId');

    if (!$compte) {
        return response()->json(['error' => 'Champ compte manquant.'], 400);
    }

    if (!$societeId) {
        return response()->json(['error' => 'Société non définie dans la session.'], 400);
    }

    $query = OperationCourante::where('compte', $compte)
                              ->where('societe_id', $societeId)
                              ->where('type_journal', '!=', 'AN')
                              ->where('reste_montant_lettre', '>', 0);

    if (!is_null($debit)) {
        $query->where(function ($q) {
            $q->whereNull('debit')->orWhere('debit', 0);
        })->whereNotNull('credit')->where('credit', '>', 0);
    }
    elseif (!is_null($credit)) {
        $query->where(function ($q) {
            $q->whereNull('credit')->orWhere('credit', 0);
        })->whereNotNull('debit')->where('debit', '>', 0);
    }

    $operations = $query->get()->map(function ($operation) use ($debit, $credit) {
        if (!is_null($debit)) {
            $operation->debit = $operation->reste_montant_lettre;
            $operation->credit = null;
        } elseif (!is_null($credit)) {
            $operation->credit = $operation->reste_montant_lettre;
            $operation->debit = null;
        }
        return $operation;
    });

    return response()->json($operations);
}

public function updateBanqueOperation(Request $request)
{
    $data = $request->input('data');

    $validatedData = $request->validate([
        'data.id' => 'required|integer|exists:operation_courante,id',
        'data.date' => 'required|string',
        'data.compte' => 'nullable|string',
        'data.libelle' => 'nullable|string',
        'data.debit' => 'nullable|numeric',
        'data.credit' => 'nullable|numeric',
        'data.piece_justificative' => 'nullable|string',
    ]);

    $operation = OperationCourante::find($data['id']);
    if (!$operation) {
        return response()->json(['error' => 'Opération non trouvée.'], 404);
    }

    // 🔹 Fonction pour parser plusieurs formats de date
    function parseDateMultiFormat($dateString)
    {
        $formats = ['d/m/Y', 'Y-m-d', 'm/d/Y'];
        foreach ($formats as $format) {
            try {
                $parsed = Carbon::createFromFormat($format, $dateString);
                if ($parsed !== false) return $parsed->format('Y-m-d');
            } catch (\Exception $e) {}
        }
        return false;
    }

    DB::beginTransaction();
    try {
        $ancienneValeurLettrage = $operation->fact_lettrer;
        $nouvelleValeurLettrage = $data['fact_lettrer'] ?? null;

        // ===================================================
        // 🔹 GESTION DU LETTRAGE / DÉLETTRAGE
        // ===================================================
        if ($ancienneValeurLettrage !== $nouvelleValeurLettrage) {

            // ---------------------------------------------------
            // 1️⃣ CAS : SUPPRESSION PARTIELLE OU TOTALE DE LETTRAGE
            // ---------------------------------------------------
            if (empty($nouvelleValeurLettrage)) {
                // 🔹 Récupérer toutes les lignes de lettrage associées
                $lignesLettrage = Lettrage::where('lettrage_id', $operation->id)->get();
                $totalRestitué = 0;

                foreach ($lignesLettrage as $ligne) {
                    $facture = OperationCourante::find($ligne->id_operation);
                    if ($facture) {
                        // 🔹 Restituer le montant lettré à la facture
                        $facture->reste_montant_lettre += $ligne->Acompte;
                        $montantInitial = $facture->debit ?? $facture->credit ?? 0;
                        if ($facture->reste_montant_lettre > $montantInitial) {
                            $facture->reste_montant_lettre = $montantInitial;
                        }
                        $facture->save();
                        $totalRestitué += $ligne->Acompte;
                    }
                }

                // 🔹 Supprimer toutes les lignes de lettrage
                Lettrage::where('lettrage_id', $operation->id)->delete();

                // 🔹 Réinitialiser le paiement principal
                $operation->fact_lettrer = null;
                $operation->date_lettrage = null;
                $operation->reste_montant_lettre = ($operation->debit ?? $operation->credit ?? 0);
                $operation->save();
                $valeursiffac = explode('|', $ancienneValeurLettrage);

                // On prend la première valeur
                $idfac = $valeursiffac[0];
                //  dd($idfac);
                $opsLieesfaclet = OperationCourante::where('id', '=', $idfac)
                                                    ->get();

                foreach ($opsLieesfaclet as $op) {
                
                    $value = (isset($op->debit) && $op->debit > 0) ? $op->debit : ($op->credit ?? 0);
                    $op->reste_montant_lettre = (float) ($value ?? 0);
                    //   dd($op->debit, $op->credit);
                    $op->save();
                }

                                    //    dd($opsLieesfaclet->reste_montant_lettre  );
                    // 🔹 Mettre à jour les opérations avec même pièce justificative
                    if (!empty($operation->piece_justificative)) {
                        $opsLiees = OperationCourante::where('piece_justificative', $operation->piece_justificative)
                            ->where('id', '!=', $operation->id)
                            ->get();
                        //  $opsLieesfaclet = OperationCourante::where('id', '=', $facture->id)
                        //                         ->get();
                    
                        foreach ($opsLiees as $op) {
                            $op->fact_lettrer = null;
                            $op->date_lettrage = null;
                            // $op->reste_montant_lettre = $op->debit ?? $op->credit ?? 0;
                            $op->reste_montant_lettre = $op->debit ?? $op->credit ?? 0;

                            $op->save();
                        }
                    }
            }

            // ---------------------------------------------------
            // 2️⃣ CAS : MODIFICATION OU AJOUT D’UN NOUVEAU LETTRAGE
            // ---------------------------------------------------
            else {
                // 🔹 Récupérer les factures à lettrer depuis la nouvelle valeur
                $factures = is_array($nouvelleValeurLettrage)
                    ? $nouvelleValeurLettrage
                    : explode('&', $nouvelleValeurLettrage);

                // 🔹 Supprimer uniquement les lettrages qui ne figurent plus dans la nouvelle liste
                $lignesExistantes = Lettrage::where('lettrage_id', $operation->id)->get();
                $idsFacturesNouvelles = [];

                foreach ($factures as $factureStr) {
                    $parts = explode('|', $factureStr);
                    if (count($parts) === 4) {
                        $idsFacturesNouvelles[] = intval(trim($parts[0]));
                    }
                }

                foreach ($lignesExistantes as $ligne) {
                    if (!in_array($ligne->id_operation, $idsFacturesNouvelles)) {
                        // 🔹 On supprime ce lettrage spécifique
                        $facture = OperationCourante::find($ligne->id_operation);
                        if ($facture) {
                            $facture->reste_montant_lettre += $ligne->Acompte;
                            $montantInitial = $facture->debit ?? $facture->credit ?? 0;
                            if ($facture->reste_montant_lettre > $montantInitial) {
                                $facture->reste_montant_lettre = $montantInitial;
                            }
                            $facture->save();
                        }
                        $ligne->delete();
                    }
                }

                // 🔹 Met à jour les factures restantes ou nouvelles
                $acompteDisponible = $operation->debit ?? $operation->credit ?? 0;
                foreach ($factures as $factureStr) {
                    $parts = explode('|', $factureStr);
                    if (count($parts) !== 4) continue;

                    $factureId = intval(trim($parts[0]));
                    $numero = trim($parts[1]);
                    $facture = OperationCourante::find($factureId);
                    if (!$facture) continue;

                    $ligneExistante = Lettrage::where('lettrage_id', $operation->id)
                        ->where('id_operation', $factureId)
                        ->first();

                    $montantLettrer = min($acompteDisponible, $facture->reste_montant_lettre);
                    if (!$ligneExistante && $montantLettrer > 0) {
                        Lettrage::create([
                            'NFacture' => $numero,
                            'Acompte' => $montantLettrer,
                            'compte' => $operation->compte,
                            'id_operation' => $factureId,
                            'id_user' => auth()->id(),
                            'lettrage_id' => $operation->id,
                        ]);
                        $facture->reste_montant_lettre -= $montantLettrer;
                        if ($facture->reste_montant_lettre < 0) $facture->reste_montant_lettre = 0;
                        $facture->save();
                        $acompteDisponible -= $montantLettrer;
                    }
                }

                // 🔹 Mettre à jour le paiement
                $operation->fact_lettrer = $nouvelleValeurLettrage;
                $operation->date_lettrage = now();
                $operation->reste_montant_lettre = $acompteDisponible;
                $operation->save();

                // 🔹 Synchroniser les lignes avec même pièce justificative
                if (!empty($operation->piece_justificative)) {
                    $opsLiees = OperationCourante::where('piece_justificative', $operation->piece_justificative)
                        ->where('id', '!=', $operation->id)
                        ->get();
                    foreach ($opsLiees as $op) {
                        $op->fact_lettrer = $operation->fact_lettrer;
                        $op->date_lettrage = $operation->date_lettrage;
                        $op->save();
                    }
                }
            }
        }

        // ===================================================
        // 🔹 MISE À JOUR DES AUTRES CHAMPS
        // ===================================================
        $parsedDate = parseDateMultiFormat($data['date']);
        if ($parsedDate === false) {
            return response()->json(['error' => 'Format de date invalide pour "date"'], 400);
        }
        $operation->date = $parsedDate;

        if (!empty($data['date_lettrage'])) {
            $parsedDateLettrage = parseDateMultiFormat($data['date_lettrage']);
            if ($parsedDateLettrage === false) {
                return response()->json(['error' => 'Format de date invalide pour "date_lettrage"'], 400);
            }
            $operation->date_lettrage = $parsedDateLettrage;
        }

        $oldContrePartie = $operation->contre_partie;
        $newCompte = $data['compte'] ?? $operation->compte;

        $operation->numero_dossier = $data['numero_dossier'] ?? $operation->numero_dossier;
        $operation->compte = $newCompte;
        $operation->libelle = $data['libelle'] ?? $operation->libelle;
        if (array_key_exists('debit', $data)) $operation->debit = $data['debit'];
        if (array_key_exists('credit', $data)) $operation->credit = $data['credit'];
        $operation->contre_partie = $data['contre_partie'] ?? $operation->contre_partie;
        $operation->piece_justificative = $data['piece_justificative'] ?? $operation->piece_justificative;
        $operation->taux_ras_tva = $data['taux_ras_tva'] ?? $operation->taux_ras_tva;
        $operation->nature_op = $data['nature_op'] ?? $operation->nature_op;
        $operation->mode_pay = $data['mode_pay'] ?? $operation->mode_pay;
        $operation->type_journal = $data['type_journal'] ?? $operation->type_journal;
        $operation->file_id = $data['file_id'] ?? $operation->file_id;
        $operation->save();

        DB::commit();
        return response()->json(['message' => '✅ Opération mise à jour avec succès']);
    } catch (\Exception $e) {
        DB::rollBack();
        return response()->json(['error' => 'Erreur lors de la mise à jour : ' . $e->getMessage()], 500);
    }
}

public function storeCaisse(Request $request) {
    // dd($request->all());
    // ✅ Transformation de 'facture' en 'fact_lettrer' si présent dans le request
    if ($request->has('facture')) {
        $request->merge([
            'fact_lettrer' => is_array($request->facture) ? implode(' & ', $request->facture) : $request->facture
        ]);
    }

    $societeId = session('societeId');

    $racine = Racine::where('societe_id', $societeId)
        ->where('num_racines', 142)
        ->first();

    $taux = is_numeric($racine->Taux) ? (float) $racine->Taux : 0;

    $validatedData = $request->validate([
        'date' => 'required|date',
        'numero_dossier' => 'nullable|string',
        'fact_lettrer' => 'nullable|string',
        'compte' => 'nullable|string',
        'libelle' => 'nullable|string',
        'debit' => 'nullable|numeric',
        'credit' => 'nullable|numeric',
        'contre_partie' => 'nullable|string',
        'piece_justificative' => 'nullable|string',
        'taux_ras_tva' => 'nullable|string',
        'nature_op' => 'nullable|string',
        'date_lettrage' => 'nullable|string',
        'mode_pay' => 'nullable|string',
        'type_journal' => 'nullable|string',
        'saisie_choisie' => 'required|string',
    ]);

    $validatedData['fact_lettrer'] = $validatedData['fact_lettrer'] ?? '';

    if (!empty($validatedData['date_lettrage'])) {
        $dateLettrage = \DateTime::createFromFormat('d/m/Y', $validatedData['date_lettrage'])
            ?: \DateTime::createFromFormat('m/d/Y', $validatedData['date_lettrage']);

        if (!$dateLettrage) {
            return response()->json(['error' => 'Format de date_lettrage invalide. Utilisez jj/mm/aaaa ou mm/jj/aaaa.'], 422);
        }

        $validatedData['date_lettrage'] = $dateLettrage->format('Y-m-d');
    }

    $validatedData['numero_facture'] = 'pas de facture';
    $validatedData['societe_id'] = $societeId;
    $validatedData['categorie'] = 'Caisse';

    $validatedData['reste_montant_lettre'] = !empty($validatedData['fact_lettrer'])
        ? 0.00
        : ($validatedData['debit'] ?? $validatedData['credit'] ?? 0.00);
    // dd($validatedData);
    $operationPrincipale = OperationCourante::create($validatedData);
    $factlet01 = $validatedData['fact_lettrer'];
    // ✅ Traitement du lettrage
    if (!empty($validatedData['fact_lettrer'])) {
        $factures = explode('&', $validatedData['fact_lettrer']);

        $acompte = 0;
        if (!empty($validatedData['debit']) && $validatedData['debit'] != 0) {
            $acompte = $validatedData['debit'];
        } elseif (!empty($validatedData['credit']) && $validatedData['credit'] != 0) {
            $acompte = $validatedData['credit'];
        }

        if (count($factures) === 1) {
            $factureStr = trim($factures[0]);
            if (!empty($factureStr)) {
                $parts = explode('|', $factureStr);
                if (count($parts) === 4) {
                    $operationId = intval(trim($parts[0]));
                    $numero = trim($parts[1]);
                    $montant = floatval(trim($parts[2]));
                    $date = trim($parts[3]);

                    $operation = OperationCourante::find($operationId);
                    if ($operation) {
                        if ($acompte > $operation->reste_montant_lettre) {
                            echo "<script>alert('L\'acompte est supérieur au reste à lettrer pour cette facture.');</script>";
                        } else {
                            Lettrage::create([
                                'NFacture' => $numero,
                                'Acompte' => $acompte,
                                'compte' => $validatedData['compte'],
                                'id_operation' => $operationId,
                                'id_user' => auth()->id(),
                                'lettrage_id' => $operationPrincipale->id,
                            ]);

                            $operation->reste_montant_lettre -= $acompte;
                            $operation->reste_montant_lettre = max($operation->reste_montant_lettre, 0);
                            $operation->save();
                        }
                    }
                }
            }
        } else {
            $resteAcompte = $acompte;

            foreach ($factures as $factureStr) {
                $factureStr = trim($factureStr);
                if (!empty($factureStr)) {
                    $parts = explode('|', $factureStr);
                    if (count($parts) === 4) {
                        $operationId = intval(trim($parts[0]));
                        $numero = trim($parts[1]);
                        $montant = floatval(trim($parts[2]));
                        $date = trim($parts[3]);

                        $operation = OperationCourante::find($operationId);
                        if ($operation && $resteAcompte > 0) {
                            $montantLettrable = min($resteAcompte, $operation->reste_montant_lettre);

                            Lettrage::create([
                                'NFacture' => $numero,
                                'Acompte' => $montantLettrable,
                                'compte' => $validatedData['compte'],
                                'id_operation' => $operationId,
                                'id_user' => auth()->id(),
                                'lettrage_id' => $operationPrincipale->id,
                            ]);

                            $operation->reste_montant_lettre -= $montantLettrable;
                            $operation->reste_montant_lettre = max($operation->reste_montant_lettre, 0);
                            $operation->save();

                            $resteAcompte -= $montantLettrable;
                            if ($resteAcompte <= 0) break;
                        }
                    }
                }
            }

            if ($resteAcompte > 0) {
                echo "<script>alert('L\'acompte est supérieur au total des restes à lettrer des factures.');</script>";
            }
        }

        // ✅ Nettoyage du champ fact_lettrer
        $facturesNettoyees = [];
        foreach ($factures as $factureStr) {
            $parts = explode('|', trim($factureStr));
            if (count($parts) === 4) {
                $facturesNettoyees[] = implode('|', array_slice($parts, 1));
            } else {
                $facturesNettoyees[] = trim($factureStr);
            }
        }

        $validatedData['fact_lettrer'] = implode(' & ', $facturesNettoyees);
    }

    // ✅ Contre-partie automatique
    if ($validatedData['saisie_choisie'] === 'contre-partie') {
        if (str_starts_with($validatedData['compte'], '6147')) {
            $contrePartieData1 = [
                'date' => $validatedData['date'],
                // 'fact_lettrer' => $validatedData['fact_lettrer'],
                'fact_lettrer' => $factlet01,
                'compte' => $racine->compte_tva,
                'contre_partie' => $validatedData['contre_partie'],
                'libelle' => $validatedData['libelle'],
                'debit' => $validatedData['debit'] * ($taux / 100),
                'credit' => $validatedData['credit'],
                'piece_justificative' => $validatedData['piece_justificative'],
                'taux_ras_tva' => $validatedData['taux_ras_tva'],
                'nature_op' => $validatedData['nature_op'],
                'date_lettrage' => $validatedData['date_lettrage'],
                'mode_pay' => $validatedData['mode_pay'],
                'type_journal' => $validatedData['type_journal'],
                'numero_facture' => 'pas de facture',
                'societe_id' => $societeId,
                'categorie' => 'Caisse',
                'reste_montant_lettre' => !empty($validatedData['fact_lettrer']) ? 0.00 : ($validatedData['debit'] ?? $validatedData['credit'] ?? 0.00),
            ];

            $contrePartieData2 = [
                'date' => $validatedData['date'],
                // 'fact_lettrer' => $validatedData['fact_lettrer'],
                'fact_lettrer' => $factlet01,
                'compte' => $validatedData['contre_partie'],
                'contre_partie' => $validatedData['compte'],
                'libelle' => $validatedData['libelle'],
                'debit' => $validatedData['credit'],
                'credit' => $validatedData['debit'] + $validatedData['debit'] * ($taux / 100),
                'piece_justificative' => $validatedData['piece_justificative'],
                'taux_ras_tva' => $validatedData['taux_ras_tva'],
                'nature_op' => $validatedData['nature_op'],
                'date_lettrage' => $validatedData['date_lettrage'],
                'mode_pay' => $validatedData['mode_pay'],
                'type_journal' => $validatedData['type_journal'],
                'numero_facture' => 'pas de facture',
                'societe_id' => $societeId,
                'categorie' => 'Caisse',
                'reste_montant_lettre' => !empty($validatedData['fact_lettrer']) ? 0.00 : ($validatedData['credit'] ?? $validatedData['debit'] ?? 0.00),
            ];
    //  dd($contrePartieData1, $contrePartieData2);
            OperationCourante::create($contrePartieData1);
            OperationCourante::create($contrePartieData2);
        } else {
            $contrePartieData = [
                'date' => $validatedData['date'],
                // 'fact_lettrer' => $validatedData['fact_lettrer'],
                'fact_lettrer' => $factlet01,
                'compte' => $validatedData['contre_partie'],
                'contre_partie' => $validatedData['compte'],
                'libelle' => $validatedData['libelle'],
                'debit' => $validatedData['credit'],
                'credit' => $validatedData['debit'],
                'piece_justificative' => $validatedData['piece_justificative'],
                'taux_ras_tva' => $validatedData['taux_ras_tva'],
                'nature_op' => $validatedData['nature_op'],
                'date_lettrage' => $validatedData['date_lettrage'],
                'mode_pay' => $validatedData['mode_pay'],
                'type_journal' => $validatedData['type_journal'],
                'numero_facture' => 'pas de facture',
                'societe_id' => $societeId,
                'categorie' => 'Caisse',
                'reste_montant_lettre' => !empty($validatedData['fact_lettrer']) ? 0.00 : ($validatedData['credit'] ?? $validatedData['debit'] ?? 0.00),
            ];
    //   dd($contrePartieData);
            OperationCourante::create($contrePartieData);
        }
    }

    return response()->json(['message' => 'Données enregistrées avec succès.']);
}

public function transfereBanque(Request $request){
    $societeId = session('societeId');

    if (!$societeId) {
        return response()->json(['error' => 'Société non trouvée en session.'], 400);
    }

     $data = $request->only(['lignes', 'code_journal']);
    $lignes = is_array($data['lignes'] ?? null) ? $data['lignes'] : [];
    $codeJournal = $data['code_journal'] ?? null;

    if (empty($lignes) || !$codeJournal) {
        return response()->json(['error' => 'Paramètres invalides : lignes et code_journal requis.'], 422);
    }

     $ids = [];
    foreach ($lignes as $ln) {
        if (is_array($ln) && isset($ln['id']) && is_numeric($ln['id'])) {
            $ids[] = (int) $ln['id'];
        }
    }
    $ids = array_values(array_unique($ids));

    if (empty($ids)) {
        return response()->json(['error' => 'Aucun id valide trouvé dans les lignes.'], 422);
    }

    \DB::beginTransaction();
    try {
         $updated = \App\Models\OperationCourante::whereIn('id', $ids)
            ->where('societe_id', $societeId)
            ->update(['type_journal' => $codeJournal, 'updated_at' => now()]);

        \DB::commit();

        return response()->json([
            'success' => true,
            'updated_count' => $updated,
            'ids' => $ids,
            'code_journal' => $codeJournal,
        ]);
    } catch (\Throwable $e) {
        \DB::rollBack();
        \Log::error('transfereBanque error: '.$e->getMessage(), ['ids' => $ids, 'code_journal' => $codeJournal]);
        return response()->json(['error' => 'Erreur serveur lors de la mise à jour.'], 500);
    }
}

public function transfereCaisse(Request $request){
    $societeId = session('societeId');

    if (!$societeId) {
        return response()->json(['error' => 'Société non trouvée en session.'], 400);
   
    }

    
    $data = $request->only(['lignes', 'code_journal']);
    $lignes = is_array($data['lignes'] ?? null) ? $data['lignes'] : [];
    $codeJournal = $data['code_journal'] ?? null;

    if (empty($lignes) || !$codeJournal) {
        return response()->json(['error' => 'Paramètres invalides : lignes et code_journal requis.'], 422);
    }

     
    $ids = [];
    foreach ($lignes as $ln) {
        if (is_array($ln) && isset($ln['id']) && is_numeric($ln['id'])) {
            $ids[] = (int) $ln['id'];
        }
    }
    $ids = array_values(array_unique($ids));

    if (empty($ids)) {
        return response()->json(['error' => 'Aucun id valide trouvé dans les lignes.'], 422);
    }

    \DB::beginTransaction();
    try {
         $updated = \App\Models\OperationCourante::whereIn('id', $ids)
            ->where('societe_id', $societeId)
            ->update(['type_journal' => $codeJournal, 'updated_at' => now()]);

        \DB::commit();

        return response()->json([
            'success' => true,
            'updated_count' => $updated,
            'ids' => $ids,
            'code_journal' => $codeJournal,
        ]);
    } catch (\Throwable $e) {
        \DB::rollBack();
        \Log::error('transfereCaisse error: '.$e->getMessage(), ['ids' => $ids, 'code_journal' => $codeJournal]);
        return response()->json(['error' => 'Erreur serveur lors de la mise à jour.'], 500);
    }
}

public function getSoldeInitialCaisse(Request $request){
    $societeId = session('societeId');
    if (!$societeId) {
        return response()->json(['error' => 'Societe ID non trouvé dans la session.'], 400);
    }

    $contrePartie = trim((string) ($request->input('contre_partie') ?? ''));
    if ($contrePartie === '') {
        return response()->json([
            'solde_initial_db' => 0.00,
            'solde_initial_cr' => 0.00,
        ]);
    }
    // dd($contrePartie);
        // Cherche les écritures d'ouverture (type_journal = 'AN') dont le compte = contre_partie fournie
        $query = OperationCourante::where('societe_id', $societeId)
            ->where('categorie', 'Opérations Diverses')
            ->where('type_journal', 'AN')
            ->where(function($q) use ($contrePartie) {
                $q->where('compte', $contrePartie);
            });

        $soldeInitialDB = (float) $query->sum('debit');
        $soldeInitialCR = (float) $query->sum('credit');
    // dd( $soldeInitialCR, $soldeInitialDB);
        return response()->json([
            'solde_initial_db' => $soldeInitialDB,
            'solde_initial_cr' => $soldeInitialCR,
    ]);
}

public function modifierTousCompteCaisse(Request $request)
{
    
    $validated = $request->validate([
        'ancien_compte' => 'required|string',
        'nouveau_compte' => 'required|string',
    ]);

   
    $societeId = session('societeId');
    if (!$societeId) {
        return response()->json(['error' => 'Societe ID non trouvé dans la session.'], 400);
    }

    $ancien = trim($validated['ancien_compte']);
    $nouveau = trim($validated['nouveau_compte']);

    \DB::beginTransaction();

    try {
      
        $comptesPrincipaux = OperationCourante::where('societe_id', $societeId)
            // ->where('categorie', 'Caisse')
            ->where('compte', $ancien)
            ->where(function ($q) {
                $q->whereNull('fact_lettrer')
                  ->orWhere('fact_lettrer', '');
            })
            ->get();

        $compteUpdatedCount = 0;

        foreach ($comptesPrincipaux as $op) {
            $debit = $op->debit ?? 0;
            $credit = $op->credit ?? 0;
            $reste = $op->reste_montant_lettre ?? 0;

           
            if ($reste == $debit || $reste == $credit) {
                $op->compte = $nouveau;
                $op->updated_at = now();
                $op->save();
                $compteUpdatedCount++;
            }
        }


        $contreParties = OperationCourante::where('societe_id', $societeId)
            // ->where('categorie', 'Caisse')
            ->where('contre_partie', $ancien)
            ->where(function ($q) {
                $q->whereNull('fact_lettrer')
                  ->orWhere('fact_lettrer', '');
            })
            ->get();

        $contreUpdatedCount = 0;

        foreach ($contreParties as $op) {
            $debit = $op->debit ?? 0;
            $credit = $op->credit ?? 0;
            $reste = $op->reste_montant_lettre ?? 0;

            if ($reste == $debit || $reste == $credit) {
                $op->contre_partie = $nouveau;
                $op->updated_at = now();
                $op->save();
                $contreUpdatedCount++;
            }
        }

        \DB::commit();

        return response()->json([
            'message' => 'Mise à jour terminée avec succès.',
            'compte_mis_a_jour' => $compteUpdatedCount,
            'contre_partie_mis_a_jour' => $contreUpdatedCount,
        ]);
    } catch (\Throwable $e) {
        \DB::rollBack();
        \Log::error('modifierTousCompteCaisse error: ' . $e->getMessage(), [
            'ancien' => $ancien,
            'nouveau' => $nouveau,
        ]);
        return response()->json(['error' => 'Erreur serveur lors de la mise à jour.'], 500);
    }
}

public function modifierTousCompteBanque(Request $request)
{
    
    $validated = $request->validate([
        'ancien_compte' => 'required|string',
        'nouveau_compte' => 'required|string',
    ]);

   
    $societeId = session('societeId');
    if (!$societeId) {
        return response()->json(['error' => 'Societe ID non trouvé dans la session.'], 400);
    }

    $ancien = trim($validated['ancien_compte']);
    $nouveau = trim($validated['nouveau_compte']);

    \DB::beginTransaction();

    try {
      
        $comptesPrincipaux = OperationCourante::where('societe_id', $societeId)
            // ->where('categorie', 'Banque')
            ->where('compte', $ancien)
            ->where(function ($q) {
                $q->whereNull('fact_lettrer')
                  ->orWhere('fact_lettrer', '');
            })
            ->get();

        $compteUpdatedCount = 0;

        foreach ($comptesPrincipaux as $op) {
            $debit = $op->debit ?? 0;
            $credit = $op->credit ?? 0;
            $reste = $op->reste_montant_lettre ?? 0;

           
            if ($reste == $debit || $reste == $credit) {
                $op->compte = $nouveau;
                $op->updated_at = now();
                $op->save();
                $compteUpdatedCount++;
            }
        }


        $contreParties = OperationCourante::where('societe_id', $societeId)
            // ->where('categorie', 'Banque')
            ->where('contre_partie', $ancien)
            ->where(function ($q) {
                $q->whereNull('fact_lettrer')
                  ->orWhere('fact_lettrer', '');
            })
            ->get();

        $contreUpdatedCount = 0;

        foreach ($contreParties as $op) {
            $debit = $op->debit ?? 0;
            $credit = $op->credit ?? 0;
            $reste = $op->reste_montant_lettre ?? 0;

            if ($reste == $debit || $reste == $credit) {
                $op->contre_partie = $nouveau;
                $op->updated_at = now();
                $op->save();
                $contreUpdatedCount++;
            }
        }

        \DB::commit();

        return response()->json([
            'message' => 'Mise à jour terminée avec succès.',
            'compte_mis_a_jour' => $compteUpdatedCount,
            'contre_partie_mis_a_jour' => $contreUpdatedCount,
        ]);
    } catch (\Throwable $e) {
        \DB::rollBack();
        \Log::error('modifierTousCompteCaisse error: ' . $e->getMessage(), [
            'ancien' => $ancien,
            'nouveau' => $nouveau,
        ]);
        return response()->json(['error' => 'Erreur serveur lors de la mise à jour.'], 500);
    }
}

public function getSoldeActuel(Request $request)
{
    // dd($request->all());
    $codeJournal = $request->input('code_journal');
    $contrePartie = $request->input('contre_partie');

    // 🔹 1. Récupérer la dernière date saisie pour ce journal
    $derniereDate = OperationCourante::where('type_journal', $codeJournal)
        ->max('date');

    if (!$derniereDate) {
        return response()->json([
            'resultats' => [],
            'solde_initial' => 0,
            'cumul_credit' => 0,
            'cumul_debit' => 0,
            'solde_actuel' => 0
        ]);
    }

    $derniereDate = Carbon::parse($derniereDate);

    // 🔹 2. Déterminer le mois correspondant
    $debutMois = $derniereDate->copy()->startOfMonth();
    $finMois = $derniereDate->copy()->endOfMonth();

    // 🔹 3. Récupérer les opérations du mois de la dernière saisie
    $resultats = OperationCourante::where('type_journal', $codeJournal)
        ->whereBetween('date', [$debutMois, $finMois])
        ->where('compte', '!=', $contrePartie)
        ->get();

    // 🔹 4. Récupérer le solde initial (type AN + contre_partie)
    $operationSoldeInitial = OperationCourante::where('type_journal', 'AN')
        ->where('compte', $contrePartie)
        ->first();

    $soldeInitial = 0;

    if ($operationSoldeInitial) {
        if (!empty($operationSoldeInitial->debit) && $operationSoldeInitial->debit != 0) {
            $soldeInitial = $operationSoldeInitial->debit;
        } elseif (!empty($operationSoldeInitial->credit) && $operationSoldeInitial->credit != 0) {
            $soldeInitial = -$operationSoldeInitial->credit;
        }
    }

    // 🔹 5. Calculer les cumuls
    $cumulCredit = $resultats->sum('credit');
    $cumulDebit  = $resultats->sum('debit');

    // 🔹 6. Calculer le solde actuel
    $soldeActuel = $soldeInitial + $cumulCredit - $cumulDebit;
    // dd($soldeActuel);
        // 🔹 7. Retourner les données
        return response()->json([
            'soldeActuel' => $soldeActuel,
        ]);
}

public function getFileUrl($fileId)
{
    // dd($fileId);
    $file = File::where('id', $fileId)->first();
    // if (!$file) return response()->json(['error' => 'Not found'], 404);
    // dd(($file->path));
    return response()->json([
        'file_url' => $file->path,
        'path' => $file->path
    ]);
}

}