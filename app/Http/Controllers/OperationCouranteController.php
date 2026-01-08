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
use App\Models\Dossier; 
use Carbon\Carbon;
use Complex\Operations;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use OpenAI\Laravel\Facades\OpenAI;  // 👈 the Laravel facade

use Illuminate\Support\Facades\Session;
use Smalot\PdfParser\Parser;
use GuzzleHttp\Client as GuzzleClient;

use App\Models\Lettrage;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Storage;
use Illuminate\Http\Response;
use Illuminate\Support\Str; 

class OperationCouranteController extends Controller
{
public function updateRow(Request $request)
{
    try {
        $societeId = session('societeId');
        if (!$societeId) {
            return response()->json(['error' => 'Societe ID non trouvé'], 400);
        }

        $validated = $request->validate([
            'id' => 'required|integer|exists:operation_courante,id',
            'date' => 'nullable|string',
            'date_livr' => 'nullable|string',
            'numero_facture' => 'nullable|string|max:255',
            'numero_dossier' => 'nullable|string|max:255',
            'compte' => 'nullable|string|max:255',
            'libelle' => 'nullable|string|max:255',
            'debit' => 'nullable|numeric',
            'credit' => 'nullable|numeric',
            'contre_partie' => 'nullable|string|max:255',
            'piece_justificative' => 'nullable|string|max:255',
            'mode_pay' => 'nullable|string|max:255',
            'rubrique_tva' => 'nullable|string|max:255',
            'compte_tva' => 'nullable|string|max:255',
            'prorat_de_deduction' => 'nullable|string|in:Oui,Non',
            'file_id' => 'nullable|integer',
            'fact_lettrer' => 'nullable', 
            'date_lettrage' => 'nullable|string'
        ]);

        $op = OperationCourante::where('id', $request->id)
            ->where('societe_id', $societeId)
            ->firstOrFail();

        $anciennePiece = $op->piece_justificative;
        $ancienCompte = $op->compte;
        $ancienContrePartie = $op->contre_partie;
        $ancienneProrata = $op->prorat_de_deduction;
        $ancienneValeurLettrage = $op->fact_lettrer;

        // -------------------------------
        // Fonction pour parser plusieurs formats de date
        // -------------------------------
        $parseDateMultiFormat = function ($dateString) {
            if (!$dateString) return null;
            $formats = ['d/m/Y', 'Y-m-d', 'm/d/Y'];
            foreach ($formats as $format) {
                try {
                    $parsed = Carbon::createFromFormat($format, $dateString);
                    if ($parsed !== false) return $parsed->format('Y-m-d');
                } catch (\Exception $e) {}
            }
            return false;
        };

        DB::beginTransaction();

        // -------------------------------
        // Gestion du lettrage / délettrage
        // -------------------------------
        $nouvelleValeurLettrage = $request->fact_lettrer ?? null;
        if ($ancienneValeurLettrage !== $nouvelleValeurLettrage) {

            // Cas suppression totale ou partielle
                // if (empty($nouvelleValeurLettrage)) {

                //         // 🔹 Récupérer toutes les lignes de lettrage liées (lettrage_id OU id_operation)
                //         $lignesLettrage = Lettrage::where(function($q) use ($op) {
                //             $q->where('lettrage_id', $op->id)
                //             ->orWhere('id_operation', $op->id);
                //         })->get();

                //         foreach ($lignesLettrage as $ligne) {
                //             $facture = OperationCourante::find($ligne->id_operation);
                //             if ($facture) {

                //                 // 🔹 Restitution du montant lettré
                //                 $facture->reste_montant_lettre += $ligne->Acompte;

                //                 $montantInitial = $facture->debit ?? $facture->credit ?? 0;
                //                 if ($facture->reste_montant_lettre > $montantInitial) {
                //                     $facture->reste_montant_lettre = $montantInitial;
                //                 }

                //                 $facture->save();
                //             }
                //         }

                //         // 🔹 Suppression de toutes les lignes de lettrage liées
                //         Lettrage::where(function($q) use ($op) {
                //             $q->where('lettrage_id', $op->id)
                //             ->orWhere('id_operation', $op->id);
                //         })->delete();

                //         // 🔹 Réinitialisation du paiement
                //         $op->fact_lettrer = null;
                //         $op->date_lettrage = null;
                //         $op->reste_montant_lettre = ($op->debit ?? $op->credit ?? 0);
                //         $op->save();

                //         // 🔹 Remise correcte de la facture principale (comme dans le 1er code)
                //         if (!empty($ancienneValeurLettrage)) {
                //             $valeurs = explode('|', $ancienneValeurLettrage);
                //             $idFacture = $valeurs[0] ?? null;

                //             if ($idFacture) {
                //                 $facture = OperationCourante::find($idFacture);
                //                 if ($facture) {
                //                     $montant = $facture->debit ?? $facture->credit ?? 0;
                //                     $facture->reste_montant_lettre = (float) $montant;
                //                     $facture->save();
                //                 }
                //             }
                //         }

                //         // 🔹 Synchronisation des lignes ayant la même pièce justificative
                //         if (!empty($op->piece_justificative)) {
                //             $opsLiees = OperationCourante::where('piece_justificative', $op->piece_justificative)
                //                 ->where('id', '!=', $op->id)
                //                 ->get();

                //             foreach ($opsLiees as $ligne) {
                //                 $ligne->fact_lettrer = null;
                //                 $ligne->date_lettrage = null;
                //                 $ligne->reste_montant_lettre = $ligne->debit ?? $ligne->credit ?? 0;
                //                 $ligne->save();
                //             }
                //         }
                //     }
                                if (empty($nouvelleValeurLettrage)) {

    // 🔹 Récupérer uniquement les lettrages du paiement
    $lignesLettrage = Lettrage::where('lettrage_id', $op->id)->get();

    // 🔹 Factures concernées
    $factureIds = $lignesLettrage->pluck('id_operation')->unique();

    // 🔹 Supprimer d’abord les lettrages
    Lettrage::where('lettrage_id', $op->id)->delete();

    // 🔹 Recalcul propre des factures
    foreach ($factureIds as $factureId) {

        $facture = OperationCourante::find($factureId);
        if (!$facture) continue;

        $montantInitial = (float) ($facture->debit ?? $facture->credit ?? 0);

        // 🔹 Total réellement lettré (après suppression)
        $totalLettre = Lettrage::where('id_operation', $factureId)->sum('Acompte');

        $facture->reste_montant_lettre = max(
            0,
            $montantInitial - $totalLettre
        );

        $facture->save();
    }

    // 🔹 Réinitialisation du paiement
    $montantPaiement = (float) ($op->debit ?? $op->credit ?? 0);

    $op->fact_lettrer = null;
    $op->date_lettrage = null;
    $op->reste_montant_lettre = $montantPaiement;
    $op->save();

    // 🔹 Synchronisation des pièces justificatives
    if (!empty($op->piece_justificative)) {

        $opsLiees = OperationCourante::where('piece_justificative', $op->piece_justificative)
            ->where('id', '!=', $op->id)
            ->get();

        foreach ($opsLiees as $ligne) {
            $ligne->fact_lettrer = null;
            $ligne->date_lettrage = null;
            $ligne->reste_montant_lettre = (float) ($ligne->debit ?? $ligne->credit ?? 0);
            $ligne->save();
        }
    }
    }
                    else {
                // Ajout / modification lettrage
                $factures = is_array($nouvelleValeurLettrage)
                    ? $nouvelleValeurLettrage
                    : explode('&', $nouvelleValeurLettrage);

                $acompteDisponible = $op->debit ?? $op->credit ?? 0;

                foreach ($factures as $factureStr) {
                    $parts = explode('|', $factureStr);
                    if (count($parts) !== 4) continue;

                    $factureId = intval(trim($parts[0]));
                    $numero = trim($parts[1]);
                    $facture = OperationCourante::find($factureId);
                    if (!$facture) continue;

                    $ligneExistante = Lettrage::where('lettrage_id', $op->id)
                        ->where('id_operation', $factureId)
                        ->first();

                    $montantLettrer = min($acompteDisponible, $facture->reste_montant_lettre);
                    if (!$ligneExistante && $montantLettrer > 0) {
                        Lettrage::create([
                            'NFacture' => $numero,
                            'Acompte' => $montantLettrer,
                            'compte' => $op->compte,
                            'id_operation' => $factureId,
                            'id_user' => auth()->id(),
                            'lettrage_id' => $op->id,
                        ]);
                        $facture->reste_montant_lettre -= $montantLettrer;
                        $facture->save();
                        $acompteDisponible -= $montantLettrer;
                    }
                }

                $op->fact_lettrer = $nouvelleValeurLettrage;
                $op->date_lettrage = now();
                $op->reste_montant_lettre = $acompteDisponible;
            }
        }

        // -------------------------------
        // Mise à jour des autres champs
        // -------------------------------
        $op->date = $parseDateMultiFormat($request->date);
        $op->date_livr = $parseDateMultiFormat($request->date_livr);
        $op->numero_facture = $request->numero_facture;
        $op->numero_dossier = $request->numero_dossier;
        $op->compte = $request->compte;
        $op->libelle = $request->libelle;
        $op->debit = $request->debit ? floatval($request->debit) : null;
        $op->credit = $request->credit ? floatval($request->credit) : null;
        $op->contre_partie = $request->contre_partie;
        $op->piece_justificative = $request->piece_justificative;
        $op->mode_pay = $request->mode_pay;
        $op->rubrique_tva = $request->rubrique_tva;
        $op->compte_tva = $request->compte_tva;
        $op->prorat_de_deduction = $request->prorat_de_deduction;
        $op->file_id = $request->file_id;
        $op->updated_at = now();
        $op->save();

        // -------------------------------
        // Propagation des changements aux autres lignes avec même piece_justificative
        // -------------------------------
        OperationCourante::where('societe_id', $societeId)
            ->where('piece_justificative', $anciennePiece)
            ->where('id', '!=', $op->id)
            ->update([
                'piece_justificative' => $op->piece_justificative,
                'date' => $op->date,
                'date_livr' => $op->date_livr,
                'numero_facture' => $op->numero_facture,
                'numero_dossier' => $op->numero_dossier,
                'libelle' => $op->libelle,
                'prorat_de_deduction' => $op->prorat_de_deduction,
                'file_id' => $op->file_id,
                'updated_at' => now(),
            ]);

        if ($ancienCompte !== $op->compte) {
            OperationCourante::where('societe_id', $societeId)
                ->where('piece_justificative', $op->piece_justificative)
                ->where('contre_partie', $ancienCompte)
                ->update(['contre_partie' => $op->compte, 'updated_at' => now()]);
        }

        if ($ancienContrePartie !== $op->contre_partie) {
            OperationCourante::where('societe_id', $societeId)
                ->where('piece_justificative', $op->piece_justificative)
                ->where('compte', $ancienContrePartie)
                ->update(['compte' => $op->contre_partie, 'updated_at' => now()]);
        }

        if ($ancienneProrata !== $op->prorat_de_deduction) {
            OperationCourante::where('societe_id', $societeId)
                ->where('piece_justificative', $op->piece_justificative)
                ->where('id', '!=', $op->id)
                ->update(['prorat_de_deduction' => $op->prorat_de_deduction, 'updated_at' => now()]);
        }

        DB::commit();

        return response()->json([
            'success' => true,
            'message' => '✅ Ligne mise à jour et lettrage géré avec succès.',
            'id' => $op->id,
        ]);

    } catch (\Exception $e) {
        DB::rollBack();
        \Log::error('Erreur updateRow : ' . $e->getMessage());
        return response()->json(['error' => $e->getMessage()], 500);
    }
}









 public function storeVenteOperation(Request $request)
{
    // dd($request->all());
    $societeId = session('societeId');

    DB::transaction(function () use ($request, $societeId) {

        /* ============================
           VALIDATION
        ============================ */
        $request->validate([
            'date' => 'required|date',
            'date_livraison' => 'nullable|date',
            'numero_facture' => 'nullable|string',
            'numero_dossier' => 'nullable|string',
            'compte' => 'nullable|string',
            'libelle' => 'nullable|string',
            'debit' => 'nullable|numeric',
            'credit' => 'nullable|numeric',
            'contre_partie' => 'nullable|string',
            'piece_justificative' => 'nullable|string',
            'code_journal' => 'required|string',
            'taux_ras_tva' => 'nullable|string',
            'nature_op' => 'nullable|string',
            'mode_pay' => 'nullable|string',
            'file_id' => 'nullable|integer',
            'saisie_choisie' => 'nullable|string',
            'fact_lettrer' => 'nullable|string',
        ]);

        $factletOriginal = $request->fact_lettrer ?? '';

        /* ============================
           OPÉRATION PRINCIPALE
        ============================ */
        $operation1 = OperationCourante::create([
            'date' => $request->date,
            'date_livr' => $request->date_livraison,
            'numero_dossier' => $request->numero_dossier,
            'numero_facture' => $request->numero_facture,
            'compte' => $request->compte,
            'libelle' => $request->libelle,
            'debit' => $request->debit,
            'credit' => $request->credit,
            'contre_partie' => $request->contre_partie,
            'piece_justificative' => $request->piece_justificative,
            'type_journal' => $request->code_journal,
            'taux_ras_tva' => $request->taux_ras_tva,
            'nature_op' => $request->nature_op,
            // 'prorat_de_deduction' => $request->prorat_de_deduction,
            'mode_pay' => $request->mode_pay,
            'categorie' => 'Ventes',
            'file_id' => $request->file_id,
            'saisie_choisie' => $request->saisie_choisie,
            'societe_id' => $societeId,
            'fact_lettrer' => $factletOriginal,
            'reste_montant_lettre' => !empty($factletOriginal)
                ? 0.00
                : (($request->debit ?? 0) > 0 ? $request->debit : $request->credit),
        ]);

        if ($request->saisie_choisie !== 'contre-partie') {
            return;
        }

        /* ============================
           TVA
        ============================ */
        // $fournisseur = Fournisseur::where('compte', $request->compte)
        //     ->where('societe_id', $societeId)
        //     ->first();
        $journal = Journal::where('code_journal', $request->code_journal)
            ->where('societe_id', $societeId)
            ->first();

        $totalDebitTVA = 0;
        $totalCreditTVA = 0;

        if ($journal && !empty($journal->rubrique_tva)) {
        // dd($journal->rubrique_tva);
            $rubriques = explode('/', $journal->rubrique_tva);
            $societe = Societe::find($societeId);

            foreach ($rubriques as $rubrique) {

                $racine = Racine::where('Num_racines', $rubrique)
                    ->where('societe_id', $societeId)
                    ->first();
                // dd($racine);
                if (!$racine) continue;

                $taux = $racine->Taux ?? 0;
                $debitTVA = 0;
                $creditTVA = 0;

                // if ($request->prorat_de_deduction === 'Oui') {
                //     if ($request->credit > 0) {
                //         $debitTVA = (($request->credit / (1 + $taux / 100)) * ($taux / 100))
                //             * ($societe->prorata_de_deduction / 100);
                //     }
                //     if ($request->debit > 0) {
                //         $creditTVA = (($request->debit / (1 + $taux / 100)) * ($taux / 100))
                //             * ($societe->prorata_de_deduction / 100);
                //     }
                // } else {
                    if ($request->credit > 0)
                        $debitTVA = ($request->credit / (1 + $taux / 100)) * ($taux / 100);
                    if ($request->debit > 0)
                        $creditTVA = ($request->debit / (1 + $taux / 100)) * ($taux / 100);
                // }

                OperationCourante::create([
                    'date' => $request->date,
                    'date_livr' => $request->date_livraison,
                    'numero_dossier' => $request->numero_dossier,
                    'numero_facture' => $request->numero_facture,
                    'compte' => $racine->compte_tva,
                    'libelle' => $request->libelle,
                    'debit' => $debitTVA,
                    'credit' => $creditTVA,
                    'contre_partie' => $request->compte,
                    'rubrique_tva' => $rubrique,
                    // 'prorat_de_deduction' => $request->prorat_de_deduction,
                    'piece_justificative' => $request->piece_justificative,
                    'type_journal' => $request->code_journal,
                    'categorie' => 'Ventes',
                    'file_id' => $request->file_id,
                    'saisie_choisie' => $request->saisie_choisie,
                    'societe_id' => $societeId,
                    'fact_lettrer' => $factletOriginal,
                    'reste_montant_lettre' => 0.00,
                ]);

                $totalDebitTVA += $debitTVA;
                $totalCreditTVA += $creditTVA;
            }
        }

        /* ============================
           CONTRE-PARTIE
        ============================ */
        $operation2 = OperationCourante::create([
            'date' => $request->date,
            'date_livr' => $request->date_livraison,
            'numero_dossier' => $request->numero_dossier,
            'numero_facture' => $request->numero_facture,
            'compte' => $request->contre_partie,
            'libelle' => $request->libelle,
            'debit' => $request->credit > 0 ? $request->credit - $totalDebitTVA : 0,
            'credit' => $request->debit > 0 ? $request->debit - $totalCreditTVA : 0,
            'contre_partie' => $request->compte,
            // 'prorat_de_deduction' => $request->prorat_de_deduction,
            'piece_justificative' => $request->piece_justificative,
            'type_journal' => $request->code_journal,
            'taux_ras_tva' => $request->taux_ras_tva,
            'nature_op' => $request->nature_op,
            'mode_pay' => $request->mode_pay,
            'categorie' => 'Ventes',
            'file_id' => $request->file_id,
            'saisie_choisie' => $request->saisie_choisie,
            'societe_id' => $societeId,
            'fact_lettrer' => $factletOriginal,
            'reste_montant_lettre' => !empty($factletOriginal)
                ? 0.00
                : (($request->debit ?? 0) > 0 ? $request->debit : $request->credit),
        ]);

        /* ============================
           LETTRAGE (LOGIQUE BANQUE)
        ============================ */
        if (!empty($factletOriginal)) {

            $factures = explode('&', $factletOriginal);

            $acompte = ($request->debit ?? 0) > 0 ? $request->debit : ($request->credit ?? 0);
            $resteAcompte = $acompte;

            foreach ($factures as $factureStr) {

                $parts = explode('|', trim($factureStr));
                if (count($parts) < 2 || $resteAcompte <= 0) continue;

                $operationId = (int) $parts[0];
                $numero = $parts[1];

                $facture = OperationCourante::find($operationId);
                if (!$facture || $facture->reste_montant_lettre <= 0) continue;

                $montant = min($resteAcompte, $facture->reste_montant_lettre);

                Lettrage::create([
                    'NFacture' => $numero,
                    'Acompte' => $montant,
                    'compte' => $request->compte,
                    'id_operation' => $operationId,
                    'id_user' => auth()->id(),
                    'lettrage_id' => $operation1->id,
                ]);

                $facture->reste_montant_lettre -= $montant;
                $facture->reste_montant_lettre = max($facture->reste_montant_lettre, 0);
                $facture->save();

                $resteAcompte -= $montant;
            }
        }
    });

    return response()->json([
        'success' => true,
        'message' => 'Opération achat + TVA + contre-partie + lettrage enregistrées avec succès.'
    ]);
}

public function storeAchatOperation(Request $request)
{
    $societeId = session('societeId');

    DB::transaction(function () use ($request, $societeId) {

        /* ============================
           VALIDATION
        ============================ */
        $request->validate([
            'date' => 'required|date',
            'date_livraison' => 'nullable|date',
            'numero_facture' => 'nullable|string',
            'numero_dossier' => 'nullable|string',
            'compte' => 'nullable|string',
            'libelle' => 'nullable|string',
            'debit' => 'nullable|numeric',
            'credit' => 'nullable|numeric',
            'contre_partie' => 'nullable|string',
            'piece_justificative' => 'nullable|string',
            'code_journal' => 'required|string',
            'taux_ras_tva' => 'nullable|string',
            'nature_op' => 'nullable|string',
            'mode_pay' => 'nullable|string',
            'file_id' => 'nullable|integer',
            'saisie_choisie' => 'nullable|string',
            'fact_lettrer' => 'nullable|string',
        ]);

        $factletOriginal = $request->fact_lettrer ?? '';

        /* ============================
           OPÉRATION PRINCIPALE
        ============================ */
        $operation1 = OperationCourante::create([
            'date' => $request->date,
            'date_livr' => $request->date_livraison,
            'numero_dossier' => $request->numero_dossier,
            'numero_facture' => $request->numero_facture,
            'compte' => $request->compte,
            'libelle' => $request->libelle,
            'debit' => $request->debit,
            'credit' => $request->credit,
            'contre_partie' => $request->contre_partie,
            'piece_justificative' => $request->piece_justificative,
            'type_journal' => $request->code_journal,
            'taux_ras_tva' => $request->taux_ras_tva,
            'nature_op' => $request->nature_op,
            'prorat_de_deduction' => $request->prorat_de_deduction,
            'mode_pay' => $request->mode_pay,
            'categorie' => 'Achats',
            'file_id' => $request->file_id,
            'saisie_choisie' => $request->saisie_choisie,
            'societe_id' => $societeId,
            'fact_lettrer' => $factletOriginal,
            'reste_montant_lettre' => !empty($factletOriginal)
                ? 0.00
                : (($request->debit ?? 0) > 0 ? $request->debit : $request->credit),
        ]);

        if ($request->saisie_choisie !== 'contre-partie') {
            return;
        }

        /* ============================
           TVA
        ============================ */
        $fournisseur = Fournisseur::where('compte', $request->compte)
            ->where('societe_id', $societeId)
            ->first();

        $totalDebitTVA = 0;
        $totalCreditTVA = 0;

        if ($fournisseur && $fournisseur->rubrique_tva) {

            $rubriques = explode('/', $fournisseur->rubrique_tva);
            $societe = Societe::find($societeId);

            foreach ($rubriques as $rubrique) {

                $racine = Racine::where('Num_racines', $rubrique)
                    ->where('societe_id', $societeId)
                    ->first();

                if (!$racine) continue;

                $taux = $racine->Taux ?? 0;
                $debitTVA = 0;
                $creditTVA = 0;

                if ($request->prorat_de_deduction === 'Oui') {
                    if ($request->credit > 0) {
                        $debitTVA = (($request->credit / (1 + $taux / 100)) * ($taux / 100))
                            * ($societe->prorata_de_deduction / 100);
                    }
                    if ($request->debit > 0) {
                        $creditTVA = (($request->debit / (1 + $taux / 100)) * ($taux / 100))
                            * ($societe->prorata_de_deduction / 100);
                    }
                } else {
                    if ($request->credit > 0)
                        $debitTVA = ($request->credit / (1 + $taux / 100)) * ($taux / 100);
                    if ($request->debit > 0)
                        $creditTVA = ($request->debit / (1 + $taux / 100)) * ($taux / 100);
                }

                OperationCourante::create([
                    'date' => $request->date,
                    'date_livr' => $request->date_livraison,
                    'numero_dossier' => $request->numero_dossier,
                    'numero_facture' => $request->numero_facture,
                    'compte' => $racine->compte_tva,
                    'libelle' => $request->libelle,
                    'debit' => $debitTVA,
                    'credit' => $creditTVA,
                    'contre_partie' => $request->compte,
                    'rubrique_tva' => $rubrique,
                    'prorat_de_deduction' => $request->prorat_de_deduction,
                    'piece_justificative' => $request->piece_justificative,
                    'type_journal' => $request->code_journal,
                    'categorie' => 'Achats',
                    'file_id' => $request->file_id,
                    'saisie_choisie' => $request->saisie_choisie,
                    'societe_id' => $societeId,
                    'fact_lettrer' => $factletOriginal,
                    'reste_montant_lettre' => 0.00,
                ]);

                $totalDebitTVA += $debitTVA;
                $totalCreditTVA += $creditTVA;
            }
        }

        /* ============================
           CONTRE-PARTIE
        ============================ */
        $operation2 = OperationCourante::create([
            'date' => $request->date,
            'date_livr' => $request->date_livraison,
            'numero_dossier' => $request->numero_dossier,
            'numero_facture' => $request->numero_facture,
            'compte' => $request->contre_partie,
            'libelle' => $request->libelle,
            'debit' => $request->credit > 0 ? $request->credit - $totalDebitTVA : 0,
            'credit' => $request->debit > 0 ? $request->debit - $totalCreditTVA : 0,
            'contre_partie' => $request->compte,
            'prorat_de_deduction' => $request->prorat_de_deduction,
            'piece_justificative' => $request->piece_justificative,
            'type_journal' => $request->code_journal,
            'taux_ras_tva' => $request->taux_ras_tva,
            'nature_op' => $request->nature_op,
            'mode_pay' => $request->mode_pay,
            'categorie' => 'Achats',
            'file_id' => $request->file_id,
            'saisie_choisie' => $request->saisie_choisie,
            'societe_id' => $societeId,
            'fact_lettrer' => $factletOriginal,
            'reste_montant_lettre' => !empty($factletOriginal)
                ? 0.00
                : (($request->debit ?? 0) > 0 ? $request->debit : $request->credit),
        ]);

        /* ============================
           LETTRAGE (LOGIQUE BANQUE)
        ============================ */
        if (!empty($factletOriginal)) {

            $factures = explode('&', $factletOriginal);

            $acompte = ($request->debit ?? 0) > 0 ? $request->debit : ($request->credit ?? 0);
            $resteAcompte = $acompte;

            foreach ($factures as $factureStr) {

                $parts = explode('|', trim($factureStr));
                if (count($parts) < 2 || $resteAcompte <= 0) continue;

                $operationId = (int) $parts[0];
                $numero = $parts[1];

                $facture = OperationCourante::find($operationId);
                if (!$facture || $facture->reste_montant_lettre <= 0) continue;

                $montant = min($resteAcompte, $facture->reste_montant_lettre);

                Lettrage::create([
                    'NFacture' => $numero,
                    'Acompte' => $montant,
                    'compte' => $request->compte,
                    'id_operation' => $operationId,
                    'id_user' => auth()->id(),
                    'lettrage_id' => $operation1->id,
                ]);

                $facture->reste_montant_lettre -= $montant;
                $facture->reste_montant_lettre = max($facture->reste_montant_lettre, 0);
                $facture->save();

                $resteAcompte -= $montant;
            }
        }
    });

    return response()->json([
        'success' => true,
        'message' => 'Opération achat + TVA + contre-partie + lettrage enregistrées avec succès.'
    ]);
}



public function replaceAccounts(Request $request)
{
    $validated = $request->validate([
        'ancien_compte' => 'required|string',
        'nouveau_compte' => 'required|string',
        'scope' => 'nullable|string'  
    ]);

    $societeId = session('societeId');
    if (!$societeId) {
        return response()->json(['error' => 'Societe ID non trouvé dans la session.'], 400);
    }

    $ancien = trim($validated['ancien_compte']);
    $nouveau = trim($validated['nouveau_compte']);
    $scope = $validated['scope'] ?? 'all';

    DB::beginTransaction();
    try {
         
        $query = OperationCourante::where('societe_id', $societeId)
                    ->where(function ($q) {
                        $q->whereNull('fact_lettrer')->orWhere('fact_lettrer', '');
                    });

       
        if($scope !== 'all'){
            switch($scope){
                case 'achat': $query->where('categorie', 'Achats'); break;
                case 'vente': $query->where('categorie', 'Ventes'); break;
                case 'divers': $query->where('categorie', 'Opérations Diverses'); break;
            }
        }

        $operations = $query->where(function($q) use($ancien){
            $q->where('compte', $ancien)->orWhere('contre_partie', $ancien);
        })->get();

        $compteUpdated = 0;
        $contreUpdated = 0;

        foreach ($operations as $op) {
            $debit = $op->debit ?? 0;
            $credit = $op->credit ?? 0;
            $reste = $op->reste_montant_lettre ?? 0;

            if($reste == $debit || $reste == $credit){
                if($op->compte == $ancien){
                    $op->compte = $nouveau;
                    $compteUpdated++;
                }
                if($op->contre_partie == $ancien){
                    $op->contre_partie = $nouveau;
                    $contreUpdated++;
                }
                $op->updated_at = now();
                $op->save();
            }
        }

        DB::commit();

        return response()->json([
            'message' => 'Mise à jour terminée avec succès.',
            'compte_mis_a_jour' => $compteUpdated,
            'contre_partie_mis_a_jour' => $contreUpdated,
        ]);

    } catch (\Throwable $e) {
        DB::rollBack();
        Log::error('replaceAccounts error: '.$e->getMessage(), compact('ancien','nouveau','scope'));
        return response()->json(['error' => 'Erreur serveur lors de la mise à jour.'], 500);
    }
}

     public function transferJournalACH(Request $request)
    {
        $data = $request->validate([
            'to' => 'required|string',
            'assignments' => 'required|array',
        ]);

        $to = $data['to'];
        $assignments = $data['assignments'];

        $results = ['updated' => [], 'created' => [], 'skipped' => [], 'errors' => []];

        DB::beginTransaction();
        try {
            foreach ($assignments as $a) {
                try {
                    // 1) Update by id
                    if (!empty($a['id'])) {
                        $count = DB::table('operation_courante')
                            ->where('id', $a['id'])
                            ->update(['type_journal' => $to, 'updated_at' => now()]);
                        if ($count) {
                            $results['updated'][] = ['id' => $a['id']];
                        } else {
                            $results['skipped'][] = ['id' => $a['id'], 'reason' => 'not_found'];
                        }
                        continue;
                    }

                    // 2) Update by piece_justificative if present (operation_courante)
                    if (!empty($a['piece_justificative'])) {
                        $count = DB::table('operation_courante')
                            ->whereRaw('TRIM(piece_justificative) = ?', [trim($a['piece_justificative'])])
                            ->update(['code_journal' => $to, 'type_journal' => $to, 'updated_at' => now()]);
                        if ($count) {
                            $results['updated'][] = ['piece_justificative' => $a['piece_justificative'], 'count' => $count];
                        } else {
                            $results['skipped'][] = ['piece_justificative' => $a['piece_justificative'], 'reason' => 'not_found'];
                        }
                        continue;
                    }

                    // 3) temp_row with raw data -> try match then create in operation_courante
                    if (!empty($a['temp_row']) && !empty($a['raw']) && is_array($a['raw'])) {
                        $raw = $a['raw'];

                        $numero = trim((string)($raw['numero_facture'] ?? $raw['numero'] ?? ''));
                        $compte = trim((string)($raw['compte'] ?? ''));
                        $debit  = isset($raw['debit']) ? floatval($raw['debit']) : null;
                        $credit = isset($raw['credit']) ? floatval($raw['credit']) : null;
                        $date   = isset($raw['date']) ? substr(trim((string)$raw['date']),0,10) : null;

                        $query = \App\Models\OperationCourante::query();
                        if ($numero !== '') $query->where('numero_facture', $numero);
                        if ($compte !== '') $query->where('compte', $compte);
                        if ($date) $query->whereDate('date', $date);
                        if ($debit !== null) $query->where('debit', $debit);
                        if ($credit !== null) $query->where('credit', $credit);

                        $found = $query->first();

                        if ($found) {
                            $found->type_journal = $to;
                            $found->save();
                            $results['updated'][] = ['matched_id' => $found->id, 'by' => 'raw_match'];
                            continue;
                        }

                        $createData = [
                            'date' => $raw['date'] ?? now()->format('Y-m-d H:i:s'),
                            'numero_facture' => $numero ?: ($raw['numero_facture'] ?? null),
                            'numero_dossier' => $raw['numero_dossier'] ?? ($raw['numeroDossier'] ?? null),
                            'compte' => $compte ?: ($raw['compte_tva'] ?? ''),
                            'debit' => $raw['debit'] ?? 0,
                            'credit' => $raw['credit'] ?? 0,
                            'contre_partie' => $raw['contre_partie'] ?? ($raw['contrePartie'] ?? null),
                            'libelle' => $raw['libelle'] ?? null,
                            'piece_justificative' => $raw['piece_justificative'] ?? null,
                            'rubrique_tva' => $raw['rubrique_tva'] ?? null,
                            'compte_tva' => $raw['compte_tva'] ?? null,
                            'type_journal' => $to,
                            'categorie' => $raw['categorie'] ?? null,
                            'societe_id' => session('societeId') ?? null,
                        ];

                        $createData = array_filter($createData, function($v){ return $v !== null; });

                        $new = \App\Models\OperationCourante::create($createData);
                        if ($new && $new->id) {
                            $results['created'][] = ['id' => $new->id];
                            continue;
                        } else {
                            $results['errors'][] = ['raw' => $raw, 'reason' => 'create_failed'];
                            continue;
                        }
                    }

                    // 4) skip
                    $results['skipped'][] = ['assignment' => $a, 'reason' => 'insufficient_identifiers'];
                } catch (\Throwable $innerEx) {
                    Log::error('transferJournalACH per-assignment error: '.$innerEx->getMessage(), ['assignment'=>$a]);
                    $results['errors'][] = ['assignment'=>$a, 'message'=>$innerEx->getMessage()];
                }
            } // end foreach

            DB::commit();
            return response()->json(['ok' => true, 'results' => $results]);
        } catch (\Exception $ex) {
            DB::rollBack();
            Log::error('transferJournalACH error: '.$ex->getMessage());
            return response()->json(['ok' => false, 'message' => 'Erreur serveur lors du transfert', 'details' => $ex->getMessage()], 500);
        }
    }

    public function transferJournalVTE(Request $request)
    {
        $data = $request->validate([
            'to' => 'required|string',
            'assignments' => 'required|array',
        ]);

        $to = $data['to'];
        $assignments = $data['assignments'];

        $results = ['updated' => [], 'created' => [], 'skipped' => [], 'errors' => []];

        DB::beginTransaction();
        try {
            foreach ($assignments as $a) {
                try {
                    // 1) Update by id
                    if (!empty($a['id'])) {
                        $count = DB::table('operation_courante')
                            ->where('id', $a['id'])
                            ->update(['type_journal' => $to, 'updated_at' => now()]);
                        if ($count) $results['updated'][] = ['id' => $a['id']];
                        else $results['skipped'][] = ['id' => $a['id'], 'reason' => 'not_found'];
                        continue;
                    }

                    // 2) Update by piece_justificative if present (operation_courante)
                    if (!empty($a['piece_justificative'])) {
                        $count = DB::table('operation_courante')
                            ->whereRaw('TRIM(piece_justificative) = ?', [trim($a['piece_justificative'])])
                            ->update(['code_journal' => $to, 'type_journal' => $to, 'updated_at' => now()]);
                        if ($count) {
                            $results['updated'][] = ['piece_justificative' => $a['piece_justificative'], 'count' => $count];
                        } else {
                            $results['skipped'][] = ['piece_justificative' => $a['piece_justificative'], 'reason' => 'not_found'];
                        }
                        continue;
                    }

                    // 3) temp_row with raw -> match or create (VTE fields)
                    if (!empty($a['temp_row']) && !empty($a['raw']) && is_array($a['raw'])) {
                        $raw = $a['raw'];

                        $numero = trim((string)($raw['numero_facture'] ?? $raw['numero'] ?? ''));
                        $numero_dossier = trim((string)($raw['numero_dossier'] ?? $raw['numeroDossier'] ?? ''));
                        $compte = trim((string)($raw['compte'] ?? ''));
                        $debit  = isset($raw['debit']) ? floatval($raw['debit']) : null;
                        $credit = isset($raw['credit']) ? floatval($raw['credit']) : null;
                        $date   = isset($raw['date']) ? substr(trim((string)$raw['date']),0,10) : null;

                        $query = \App\Models\OperationCourante::query();
                        if ($numero !== '') $query->where('numero_facture', $numero);
                        if ($compte !== '') $query->where('compte', $compte);
                        if ($date) $query->whereDate('date', $date);
                        if ($debit !== null) $query->where('debit', $debit);
                        if ($credit !== null) $query->where('credit', $credit);

                        $found = $query->first();

                        if ($found) {
                            $found->type_journal = $to;
                            $found->save();
                            $results['updated'][] = ['matched_id' => $found->id, 'by' => 'raw_match'];
                            continue;
                        }

                        $createData = [
                            'date' => $raw['date'] ?? now()->format('Y-m-d H:i:s'),
                            'numero_facture' => $numero ?: ($raw['numero_facture'] ?? null),
                            'numero_dossier' => $numero_dossier ?: ($raw['numero_dossier'] ?? null),
                            'compte' => $compte ?: ($raw['compte_tva'] ?? ''),
                            'debit' => $raw['debit'] ?? 0,
                            'credit' => $raw['credit'] ?? 0,
                            'date_livr' => $raw['date_livr'] ?? ($raw['dateLivr'] ?? null),
                            'contre_partie' => $raw['contre_partie'] ?? ($raw['contrePartie'] ?? null),
                            'libelle' => $raw['libelle'] ?? null,
                            'piece_justificative' => $raw['piece_justificative'] ?? null,
                            'rubrique_tva' => $raw['rubrique_tva'] ?? null,
                            'compte_tva' => $raw['compte_tva'] ?? null,
                            'type_journal' => $to,
                            'categorie' => $raw['categorie'] ?? null,
                            'societe_id' => session('societeId') ?? null,
                        ];

                        $createData = array_filter($createData, function($v){ return $v !== null; });

                        $new = \App\Models\OperationCourante::create($createData);
                        if ($new && $new->id) {
                            $results['created'][] = ['id' => $new->id];
                            continue;
                        } else {
                            $results['errors'][] = ['raw' => $raw, 'reason' => 'create_failed'];
                            continue;
                        }
                    }

                    // 4) skip
                    $results['skipped'][] = ['assignment' => $a, 'reason' => 'insufficient_identifiers'];
                } catch (\Throwable $innerEx) {
                    Log::error('transferJournalVTE per-assignment error: '.$innerEx->getMessage(), ['assignment'=>$a]);
                    $results['errors'][] = ['assignment'=>$a, 'message'=>$innerEx->getMessage()];
                }
            } // end foreach

            DB::commit();
            return response()->json(['ok' => true, 'results' => $results]);
        } catch (\Exception $ex) {
            DB::rollBack();
            Log::error('transferJournalVTE error: '.$ex->getMessage());
            return response()->json(['ok' => false, 'message' => 'Erreur serveur lors du transfert', 'details' => $ex->getMessage()], 500);
        }
    }

    public function transferJournalOP(Request $request)
    {
        $data = $request->validate([
            'to' => 'required|string',
            'assignments' => 'required|array',
        ]);

        $to = $data['to'];
        $assignments = $data['assignments'];

        $results = ['updated' => [], 'created' => [], 'skipped' => [], 'errors' => []];

        DB::beginTransaction();
        try {
            foreach ($assignments as $a) {
                try {
                    // 1) Update by id
                    if (!empty($a['id'])) {
                        $count = DB::table('operation_courante')
                            ->where('id', $a['id'])
                            ->update(['type_journal' => $to, 'updated_at' => now()]);
                        if ($count) $results['updated'][] = ['id' => $a['id']];
                        else $results['skipped'][] = ['id' => $a['id'], 'reason' => 'not_found'];
                        continue;
                    }

                    // 2) Update by piece_justificative if present (operation_courante)
                    if (!empty($a['piece_justificative'])) {
                        $count = DB::table('operation_courante')
                            ->whereRaw('TRIM(piece_justificative) = ?', [trim($a['piece_justificative'])])
                            ->update(['code_journal' => $to, 'type_journal' => $to, 'updated_at' => now()]);
                        if ($count) {
                            $results['updated'][] = ['piece_justificative' => $a['piece_justificative'], 'count' => $count];
                        } else {
                            $results['skipped'][] = ['piece_justificative' => $a['piece_justificative'], 'reason' => 'not_found'];
                        }
                        continue;
                    }

                    // 3) temp_row with raw -> match or create (OP fields)
                    if (!empty($a['temp_row']) && !empty($a['raw']) && is_array($a['raw'])) {
                        $raw = $a['raw'];

                        $numero = trim((string)($raw['numero_facture'] ?? $raw['numero'] ?? ''));
                        $compte = trim((string)($raw['compte'] ?? ''));
                        $debit  = isset($raw['debit']) ? floatval($raw['debit']) : null;
                        $credit = isset($raw['credit']) ? floatval($raw['credit']) : null;
                        $date   = isset($raw['date']) ? substr(trim((string)$raw['date']),0,10) : null;

                        $query = \App\Models\OperationCourante::query();
                        if ($numero !== '') $query->where('numero_facture', $numero);
                        if ($compte !== '') $query->where('compte', $compte);
                        if ($date) $query->whereDate('date', $date);
                        if ($debit !== null) $query->where('debit', $debit);
                        if ($credit !== null) $query->where('credit', $credit);

                        $found = $query->first();

                        if ($found) {
                            $found->type_journal = $to;
                            $found->save();
                            $results['updated'][] = ['matched_id' => $found->id, 'by' => 'raw_match'];
                            continue;
                        }

                        $createData = [
                            'date' => $raw['date'] ?? now()->format('Y-m-d H:i:s'),
                            'numero_facture' => $numero ?: ($raw['numero_facture'] ?? null),
                            'compte' => $compte ?: ($raw['compte_tva'] ?? ''),
                            'debit' => $raw['debit'] ?? 0,
                            'credit' => $raw['credit'] ?? 0,
                            'contre_partie' => $raw['contre_partie'] ?? ($raw['contrePartie'] ?? null),
                            'libelle' => $raw['libelle'] ?? null,
                            'piece_justificative' => $raw['piece_justificative'] ?? null,
                            'date_lettrage' => $raw['date_lettrage'] ?? null,
                            'fact_lettrer' => $raw['fact_lettrer'] ?? null,
                            'reste_montant_lettre' => $raw['reste_montant_lettre'] ?? null,
                            'type_journal' => $to,
                            'categorie' => $raw['categorie'] ?? null,
                            'societe_id' => session('societeId') ?? null,
                        ];

                        $createData = array_filter($createData, function($v){ return $v !== null; });

                        $new = \App\Models\OperationCourante::create($createData);
                        if ($new && $new->id) {
                            $results['created'][] = ['id' => $new->id];
                            continue;
                        } else {
                            $results['errors'][] = ['raw' => $raw, 'reason' => 'create_failed'];
                            continue;
                        }
                    }

                    // 4) skip
                    $results['skipped'][] = ['assignment' => $a, 'reason' => 'insufficient_identifiers'];
                } catch (\Throwable $innerEx) {
                    Log::error('transferJournalOP per-assignment error: '.$innerEx->getMessage(), ['assignment'=>$a]);
                    $results['errors'][] = ['assignment'=>$a, 'message'=>$innerEx->getMessage()];
                }
            } // end foreach

            DB::commit();
            return response()->json(['ok' => true, 'results' => $results]);
        } catch (\Exception $ex) {
            DB::rollBack();
            Log::error('transferJournalOP error: '.$ex->getMessage());
            return response()->json(['ok' => false, 'message' => 'Erreur serveur lors du transfert', 'details' => $ex->getMessage()], 500);
        }
    }
//  public function storeFile(Request $request)
//     {
//         $request->validate([
//             'file' => ['required','file','max:51200'],
//             'piece_justificative' => ['nullable','string'],
//         ]);

//         $user = $request->user();
//         $societeId = session('societeId') ?? ($user->societe_id ?? null);
//         if (! $societeId) {
//             return response()->json(['success'=>false,'message'=>'Aucune société en session'], 400);
//         }

//         DB::beginTransaction();
//         try {
//             $uploaded = $request->file('file');
//             if (! $uploaded) throw new \Exception('Aucun fichier reçu');

//             $originalName = $uploaded->getClientOriginalName();
//             $mime = $uploaded->getClientMimeType();
//             $size = $uploaded->getSize();

//             $safeName = preg_replace('/[^A-Za-z0-9\-\_\.]/','_', $originalName);
//             $filename = date('Ymd_His') . '_' . Str::random(8) . '_' . $safeName;

//             // store physically on public disk
//             $relativePath = $uploaded->storeAs("files/{$societeId}", $filename, 'public');
//             if (! $relativePath) throw new \Exception('Échec stockage fichier');

//             $publicUrl = asset('storage/' . $relativePath);

//             // create DB record in files table
//             $file = File::create([
//                 'name' => $originalName,
//                 'path' => $relativePath,
//                 'file_data' => [
//                     'original_name' => $originalName,
//                     'mime' => $mime,
//                     'size' => $size,
//                     'uploaded_by' => $user ? $user->id : null,
//                     'uploaded_at' => now()->toDateTimeString(),
//                 ],
//                 'type' => $mime,
//                 'folders' => null,
//                 'societe_id' => $societeId,
//                 'updated_by' => $user ? $user->id : null,
//             ]);

//             if (! $file || ! $file->id) throw new \Exception('Impossible de créer le record files.');

//             // optional: assign immediately to all operation_courante rows with same piece_justificative
//             $piece = $request->input('piece_justificative') ? trim($request->input('piece_justificative')) : null;
//             $affected = 0;
//             if ($piece) {
//                 $q = OperationCourante::whereRaw('TRIM(piece_justificative) = ?', [$piece])
//                     ->where('societe_id', $societeId);
//                 $affected = $q->update(['file_id' => $file->id]);
//             }

//             DB::commit();

//             Log::info('storeFile: OK', ['file_id'=>$file->id,'piece'=>$piece,'affected'=>$affected,'path'=>$relativePath]);

//             return response()->json([
//                 'success' => true,
//                 'file' => [
//                     'id' => $file->id,
//                     'name' => $file->name,
//                     'path' => $file->path,
//                     'file_data' => $file->file_data,
//                     'type' => $file->type,
//                     'url' => $publicUrl,
//                 ],
//                 'assign' => [
//                     'piece_justificative' => $piece,
//                     'affected_rows' => $affected,
//                 ]
//             ], 201);

//         } catch (\Throwable $e) {
//             DB::rollBack();
//             Log::error('storeFile error: '.$e->getMessage(), ['trace'=>$e->getTraceAsString()]);
//             return response()->json(['success'=>false,'message'=>'Erreur upload fichier','details'=>$e->getMessage()], 500);
//         }
//     }


//      public function assignFile(Request $request)
//     {
//         $request->validate([
//             'assignments' => ['required','array','min:1'],
//             'assignments.*.file_id' => ['required','integer','exists:files,id'],
//             'assignments.*.id' => ['nullable','integer'],
//             'assignments.*.piece_justificative' => ['nullable','string'],
//         ]);

//         $user = $request->user();
//         $societeId = session('societeId') ?? ($user->societe_id ?? null);

//         $results = ['updated' => [], 'errors' => []];
//         $affectedTotal = 0;

//         DB::beginTransaction();
//         try {
//             foreach ($request->input('assignments') as $idx => $item) {
//                 $fileId = (int)($item['file_id'] ?? 0);
//                 $lineId = $item['id'] ?? null;
//                 $piece = isset($item['piece_justificative']) ? trim((string)$item['piece_justificative']) : null;

//                 $fileModel = File::find($fileId);
//                 if (! $fileModel) {
//                     $results['errors'][] = ['index'=>$idx,'reason'=>'file_not_found','file_id'=>$fileId];
//                     continue;
//                 }

//                 if (!empty($lineId)) {
//                     $op = OperationCourante::where('id', $lineId)
//                         ->when($societeId, fn($q) => $q->where('societe_id', $societeId))
//                         ->first();
//                     if (! $op) {
//                         $results['errors'][] = ['index'=>$idx,'reason'=>'line_not_found','id'=>$lineId];
//                         continue;
//                     }
//                     $op->file_id = $fileModel->id;
//                     $op->save();
//                     $results['updated'][] = ['by'=>'id','id'=>$op->id,'file_id'=>$fileModel->id];
//                     $affectedTotal++;
//                     continue;
//                 }

//                 if (!empty($piece)) {
//                     $q = OperationCourante::whereRaw('TRIM(piece_justificative) = ?', [$piece]);
//                     if ($societeId) $q->where('societe_id', $societeId);
//                     $affected = $q->update(['file_id' => $fileModel->id]);
//                     $results['updated'][] = ['by'=>'piece','piece_justificative'=>$piece,'file_id'=>$fileModel->id,'affected'=>$affected];
//                     $affectedTotal += (int)$affected;
//                     continue;
//                 }

//                 $results['errors'][] = ['index'=>$idx,'reason'=>'missing_id_or_piece'];
//             }

//             DB::commit();
//             return response()->json(['success'=>true,'results'=>$results,'affected_total'=>$affectedTotal], 200);
//         } catch (\Throwable $e) {
//             DB::rollBack();
//             Log::error('assignFile error: '.$e->getMessage(), ['trace'=>$e->getTraceAsString()]);
//             return response()->json(['success'=>false,'message'=>'Erreur serveur','details'=>$e->getMessage()], 500);
//         }
//     }

//   public function preview($id, Request $request)
// {
//     $user = $request->user();
//     $societeId = session('societeId') ?? ($user->societe_id ?? null);

//     $file = File::find($id);
//     if (! $file) abort(404, 'Fichier introuvable');

//     if ($file->societe_id && $societeId && $file->societe_id != $societeId) {
//         abort(403, 'Accès refusé');
//     }

//     /** @var \Illuminate\Filesystem\FilesystemAdapter $disk */
//     $disk = Storage::disk('public');

//     if (! $disk->exists($file->path)) abort(404, 'Fichier manquant sur le serveur');

//     $fullPath = $disk->path($file->path);
//     $mime = data_get($file->file_data, 'mime') ?: mime_content_type($fullPath);

//     $inlineTypes = ['application/pdf', 'image/png', 'image/jpeg', 'image/jpg', 'image/gif'];
//     if (in_array($mime, $inlineTypes)) {
//         return response()->file($fullPath, ['Content-Type' => $mime]);
//     }

//     // Intelephense ne devrait plus râler ici grâce au phpdoc ci-dessus
//     return $disk->download($file->path, $file->name);
// }

// public function checkNumeroFacture(Request $request)
// {
//     $numero = $request->query('numero');
//     $societeId = $request->query('societe_id') ?? session('societeId') ?? null;
//     $exercice = $request->query('exercice'); // année (ex: 2025)
//     $filtreSelectionne = $request->query('filtreSelectionne'); // ex: 'libre'
//     $pieceJustificative = $request->query('piece_justificative'); // pièce saisie

//     if (!$numero) {
//         return response()->json(['exists' => false]);
//     }

//     // NOTE : on ignore volontairement tout paramètre lié au "code journal" ou "categorie"
//     try {
//         $query = \App\Models\OperationCourante::where('numero_facture', $numero);

//         // Appliquer societe_id si fourni (tolérant si la colonne n'existe pas)
//         if ($societeId) {
//             try {
//                 $query->where('societe_id', $societeId);
//             } catch (\Throwable $e) {
//                 // ignore si la colonne n'existe pas
//             }
//         }

//         // Appliquer filtre exercice (whereYear) si fourni (tolérant si la colonne date n'existe pas)
//         if ($exercice) {
//             try {
//                 $query->whereYear('date', $exercice);
//             } catch (\Throwable $e) {
//                 // ignore si pas de champ date
//             }
//         }

//         $factures = $query->get();
//     } catch (\Throwable $e) {
//         Log::error('checkNumeroFacture error: '.$e->getMessage());
//         return response()->json(['exists' => false, 'error' => 'query_error']);
//     }

//     if ($factures->isEmpty()) {
//         return response()->json(['exists' => false]);
//     }

//     // helper pour récupérer la pièce justificative selon différents noms possibles
//     $getPiece = function ($row) {
//         return $row->piece_justificative
//             ?? $row->piece_justif
//             ?? $row->piece
//             ?? null;
//     };

//     // Si filtre == 'libre', on autorise seulement si toutes les pièces sont identiques
//     if ($filtreSelectionne === 'libre') {
//         foreach ($factures as $facture) {
//             $factPiece = $getPiece($facture);
//             if ($factPiece !== $pieceJustificative) {
//                 return response()->json([
//                     'exists'  => true,
//                     // on renvoie la categorie (ou type_journal si absent) de la ligne trouvée
//                     'journal' => $facture->categorie ?? ($facture->type_journal ?? '—'),
//                     'periode' => $facture->date ? \Carbon\Carbon::parse($facture->date)->format('m-Y') : null,
//                     'id'      => $facture->id ?? null,
//                     'message' => 'numéro trouvé avec pièce justificative différente'
//                 ]);
//             }
//         }
//         // toutes les pièces identiques -> autorisé
//         return response()->json(['exists' => false]);
//     }

//     // Sinon (autres filtres), renvoyer la première occurrence trouvée (parmi toutes les catégories)
//     $first = $factures->first();
//     return response()->json([
//         'exists'  => true,
//         'journal' => $first->categorie ?? ($first->type_journal ?? '—'),
//             'type_journal' => $first->type_journal, // on renvoie la valeur du champ
//  'periode' => $first->date ? \Carbon\Carbon::parse($first->date)->format('m-Y') : null,
//         'id'      => $first->id ?? null,
//     ]);
// }

 public function storeFile(Request $request)
    {
        $request->validate([
            'file' => ['required','file','max:51200'],
            'piece_justificative' => ['nullable','string'],
        ]);

        $user = $request->user();
        $societeId = session('societeId') ?? ($user->societe_id ?? null);
        if (! $societeId) {
            return response()->json(['success'=>false,'message'=>'Aucune société en session'], 400);
        }

        DB::beginTransaction();
        try {
            $uploaded = $request->file('file');
            if (! $uploaded) throw new \Exception('Aucun fichier reçu');

            $originalName = $uploaded->getClientOriginalName();
            $mime = $uploaded->getClientMimeType();
            $size = $uploaded->getSize();

            $safeName = preg_replace('/[^A-Za-z0-9\-\_\.]/','_', $originalName);
            $filename = date('Ymd_His') . '_' . Str::random(8) . '_' . $safeName;

            // store physically on public disk
            $relativePath = $uploaded->storeAs("files/{$societeId}", $filename, 'public');
            if (! $relativePath) throw new \Exception('Échec stockage fichier');

            $publicUrl = asset('storage/' . $relativePath);

            // create DB record in files table
            $file = File::create([
                'name' => $originalName,
                'path' => $relativePath,
                'file_data' => [
                    'original_name' => $originalName,
                    'mime' => $mime,
                    'size' => $size,
                    'uploaded_by' => $user ? $user->id : null,
                    'uploaded_at' => now()->toDateTimeString(),
                ],
                'type' => $mime,
                'folders' => null,
                'societe_id' => $societeId,
                'updated_by' => $user ? $user->id : null,
            ]);

            if (! $file || ! $file->id) throw new \Exception('Impossible de créer le record files.');

            // optional: assign immediately to all operation_courante rows with same piece_justificative
            $piece = $request->input('piece_justificative') ? trim($request->input('piece_justificative')) : null;
            $affected = 0;
            if ($piece) {
                $q = OperationCourante::whereRaw('TRIM(piece_justificative) = ?', [$piece])
                    ->where('societe_id', $societeId);
                $affected = $q->update(['file_id' => $file->id]);
            }

            DB::commit();

            Log::info('storeFile: OK', ['file_id'=>$file->id,'piece'=>$piece,'affected'=>$affected,'path'=>$relativePath]);

            return response()->json([
                'success' => true,
                'file' => [
                    'id' => $file->id,
                    'name' => $file->name,
                    'path' => $file->path,
                    'file_data' => $file->file_data,
                    'type' => $file->type,
                    'url' => $publicUrl,
                ],
                'assign' => [
                    'piece_justificative' => $piece,
                    'affected_rows' => $affected,
                ]
            ], 201);

        } catch (\Throwable $e) {
            DB::rollBack();
            Log::error('storeFile error: '.$e->getMessage(), ['trace'=>$e->getTraceAsString()]);
            return response()->json(['success'=>false,'message'=>'Erreur upload fichier','details'=>$e->getMessage()], 500);
        }
    }


     public function assignFile(Request $request)
    {
        $request->validate([
            'assignments' => ['required','array','min:1'],
            'assignments.*.file_id' => ['required','integer','exists:files,id'],
            'assignments.*.id' => ['nullable','integer'],
            'assignments.*.piece_justificative' => ['nullable','string'],
        ]);

        $user = $request->user();
        $societeId = session('societeId') ?? ($user->societe_id ?? null);

        $results = ['updated' => [], 'errors' => []];
        $affectedTotal = 0;

        DB::beginTransaction();
        try {
            foreach ($request->input('assignments') as $idx => $item) {
                $fileId = (int)($item['file_id'] ?? 0);
                $lineId = $item['id'] ?? null;
                $piece = isset($item['piece_justificative']) ? trim((string)$item['piece_justificative']) : null;

                $fileModel = File::find($fileId);
                if (! $fileModel) {
                    $results['errors'][] = ['index'=>$idx,'reason'=>'file_not_found','file_id'=>$fileId];
                    continue;
                }

                if (!empty($lineId)) {
                    $op = OperationCourante::where('id', $lineId)
                        ->when($societeId, fn($q) => $q->where('societe_id', $societeId))
                        ->first();
                    if (! $op) {
                        $results['errors'][] = ['index'=>$idx,'reason'=>'line_not_found','id'=>$lineId];
                        continue;
                    }
                    $op->file_id = $fileModel->id;
                    $op->save();
                    $results['updated'][] = ['by'=>'id','id'=>$op->id,'file_id'=>$fileModel->id];
                    $affectedTotal++;
                    continue;
                }

                if (!empty($piece)) {
                    $q = OperationCourante::whereRaw('TRIM(piece_justificative) = ?', [$piece]);
                    if ($societeId) $q->where('societe_id', $societeId);
                    $affected = $q->update(['file_id' => $fileModel->id]);
                    $results['updated'][] = ['by'=>'piece','piece_justificative'=>$piece,'file_id'=>$fileModel->id,'affected'=>$affected];
                    $affectedTotal += (int)$affected;
                    continue;
                }

                $results['errors'][] = ['index'=>$idx,'reason'=>'missing_id_or_piece'];
            }

            DB::commit();
            return response()->json(['success'=>true,'results'=>$results,'affected_total'=>$affectedTotal], 200);
        } catch (\Throwable $e) {
            DB::rollBack();
            Log::error('assignFile error: '.$e->getMessage(), ['trace'=>$e->getTraceAsString()]);
            return response()->json(['success'=>false,'message'=>'Erreur serveur','details'=>$e->getMessage()], 500);
        }
    }

  public function preview($id, Request $request)
{
    $user = $request->user();
    $societeId = session('societeId') ?? ($user->societe_id ?? null);

    $file = File::find($id);
    if (! $file) abort(404, 'Fichier introuvable');

    if ($file->societe_id && $societeId && $file->societe_id != $societeId) {
        abort(403, 'Accès refusé');
    }

    /** @var \Illuminate\Filesystem\FilesystemAdapter $disk */
    $disk = Storage::disk('public');

    if (! $disk->exists($file->path)) abort(404, 'Fichier manquant sur le serveur');

    $fullPath = $disk->path($file->path);
    $mime = data_get($file->file_data, 'mime') ?: mime_content_type($fullPath);

    $inlineTypes = ['application/pdf', 'image/png', 'image/jpeg', 'image/jpg', 'image/gif'];
    if (in_array($mime, $inlineTypes)) {
        return response()->file($fullPath, ['Content-Type' => $mime]);
    }

    // Intelephense ne devrait plus râler ici grâce au phpdoc ci-dessus
    return $disk->download($file->path, $file->name);
}

public function checkNumeroFacture(Request $request)
{
    $numero = $request->query('numero');
    $societeId = $request->query('societe_id') ?? session('societeId') ?? null;
    $exercice = $request->query('exercice'); // année (ex: 2025)
    $filtreSelectionne = $request->query('filtreSelectionne'); // ex: 'libre'
    $pieceJustificative = $request->query('piece_justificative'); // pièce saisie

    if (!$numero) {
        return response()->json(['exists' => false]);
    }

    // NOTE : on ignore volontairement tout paramètre lié au "code journal" ou "categorie"
    try {
        $query = \App\Models\OperationCourante::where('numero_facture', $numero);

        // Appliquer societe_id si fourni (tolérant si la colonne n'existe pas)
        if ($societeId) {
            try {
                $query->where('societe_id', $societeId);
            } catch (\Throwable $e) {
                // ignore si la colonne n'existe pas
            }
        }

        // Appliquer filtre exercice (whereYear) si fourni (tolérant si la colonne date n'existe pas)
        if ($exercice) {
            try {
                $query->whereYear('date', $exercice);
            } catch (\Throwable $e) {
                // ignore si pas de champ date
            }
        }

        $factures = $query->get();
    } catch (\Throwable $e) {
        Log::error('checkNumeroFacture error: '.$e->getMessage());
        return response()->json(['exists' => false, 'error' => 'query_error']);
    }

    if ($factures->isEmpty()) {
        return response()->json(['exists' => false]);
    }

    // helper pour récupérer la pièce justificative selon différents noms possibles
    $getPiece = function ($row) {
        return $row->piece_justificative
            ?? $row->piece_justif
            ?? $row->piece
            ?? null;
    };

    // Si filtre == 'libre', on autorise seulement si toutes les pièces sont identiques
    if ($filtreSelectionne === 'libre') {
        foreach ($factures as $facture) {
            $factPiece = $getPiece($facture);
            if ($factPiece !== $pieceJustificative) {
                return response()->json([
                    'exists'  => true,
                    // on renvoie la categorie (ou type_journal si absent) de la ligne trouvée
                    'journal' => $facture->categorie ?? ($facture->type_journal ?? '—'),
                    'periode' => $facture->date ? \Carbon\Carbon::parse($facture->date)->format('m-Y') : null,
                    'id'      => $facture->id ?? null,
                    'message' => 'numéro trouvé avec pièce justificative différente'
                ]);
            }
        }
        // toutes les pièces identiques -> autorisé
        return response()->json(['exists' => false]);
    }

    // Sinon (autres filtres), renvoyer la première occurrence trouvée (parmi toutes les catégories)
    $first = $factures->first();
    return response()->json([
        'exists'  => true,
        'journal' => $first->categorie ?? ($first->type_journal ?? '—'),
            'type_journal' => $first->type_journal, // on renvoie la valeur du champ
 'periode' => $first->date ? \Carbon\Carbon::parse($first->date)->format('m-Y') : null,
        'id'      => $first->id ?? null,
    ]);
}
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
//     public function closeExercice(Request $request)
//     {
//         $societeId = session('societeId');
//         if (!$societeId) {
//             return response()->json(['error' => 'Aucune société en session'], 400);
//         }

//         $annee = $request->input('annee');
//         if (!$annee || !preg_match('/^\d{4}$/', $annee)) {
//             return response()->json(['error' => 'Année invalide'], 400);
//         }

//         // (Optionnel) Ne clore qu’un journal spécifique :
//         $codeJournal = $request->input('code_journal'); // ex. "ac3" ou "ach7"

//         // Démarre une transaction pour atomicité
//         DB::beginTransaction();
//         try {
//             // 1) Récupérer toutes les opérations de l’exercice pour la société
//             $query = OperationCourante::where('societe_id', $societeId)
//                 ->whereYear('date', $annee);
//             if ($codeJournal) {
//                 $query->where('type_journal', $codeJournal);
//             }
//             $operations = $query->get();

//             // 2) Séparer les montants par compte : classe 6 = charges, 7 = produits
//             // On suppose que le champ "compte" commence par "6" ou "7" selon le Plan Comptable
//             $totalCharges = 0.0;
//             $totalProduits = 0.0;

//             // Nous allons sommer par compte pour générer les écritures de clôture
//             $chargesParCompte = [];  // ex. ["6xxxx" => somme]
//             $produitsParCompte = []; // ex. ["7xxxx" => somme]

//             foreach ($operations as $op) {
//                 $compte = $op->compte;
//                 $montant = floatval($op->debit) - floatval($op->credit);
//                 // En classe 6, le solde du compte est débité (charges)
//                 if (strpos($compte, '6') === 0) {
//                     $chargesParCompte[$compte] = ($chargesParCompte[$compte] ?? 0) + $montant;
//                     $totalCharges += $montant;
//                 }
//                 // En classe 7, le solde du compte est crédité (produits)
//                 elseif (strpos($compte, '7') === 0) {
//                     // Attention : en classe 7, base est en "credit" (op->credit) – "débit"
//                     $soldeProduit = floatval($op->credit) - floatval($op->debit);
//                     $produitsParCompte[$compte] = ($produitsParCompte[$compte] ?? 0) + $soldeProduit;
//                     $totalProduits += $soldeProduit;
//                 }
//             }

//             // 3) Calculer le résultat net
//             // En compta OHADA/Morocco : Résultat = Produits – Charges
//             $resultNet = $totalProduits - $totalCharges;

//             // 4) Générer les écritures de clôture
//             $ecrituresCloture = [];

//             // 4.1) Pour chaque compte de produits (classe 7), on le "débéte" pour revenir à 0
//             foreach ($produitsParCompte as $compteProd => $montantProd) {
//                 if ($montantProd <= 0) continue; // ignore comptes soldés à 0 ou négatifs
//                 $ecrituresCloture[] = [
//                     'date'               => Carbon::createFromDate($annee, 12, 31)->format('Y-m-d H:i:s'),
//                     'date_livr'               => Carbon::createFromDate($annee, 12, 31)->format('Y-m-d H:i:s'),

//                     'numero_facture'     => 'CL' . $annee,       // convention de clôture
//                     'compte'             => $compteProd,
//                     'debit'              => $montantProd,        // solder le crédit sur compte 7 par un débit
//                     'credit'             => 0,
//                     'contre_partie'      => null,                // sera précisé plus bas sur ligne Résultat
//                     'rubrique_tva'       => null,
//                     'compte_tva'         => null,
//                     'type_journal'       => 'CL',                // journal spécial « Clôture »
//                     'categorie'          => 'Clôture',
//                     'prorat_de_deduction'=> null,
//                     'piece_justificative'=> null,                // sera mis à jour dans updatePieceJustificative
//                     'libelle'            => "Clôture compte $compteProd",
//                     'filtre_selectionne' => null,
//                     'societe_id'         => $societeId,
//                     'numero_piece'       => null,                // sera ignoré ou généré via Controller store()
//                 ];
//             }

//             // 4.2) Pour chaque compte de charges (classe 6), on le "crédite" pour revenir à 0
//             foreach ($chargesParCompte as $compteCh => $montantCh) {
//                 if ($montantCh <= 0) continue;
//                 $ecrituresCloture[] = [
//                     'date'               => Carbon::createFromDate($annee, 12, 31)->format('Y-m-d H:i:s'),
//                     'numero_facture'     => 'CL' . $annee,
//                     'compte'             => $compteCh,
//                     'debit'              => 0,
//                     'credit'             => $montantCh,       // solder le compte 6 par un crédit
//                     'contre_partie'      => null,
//                     'rubrique_tva'       => null,
//                     'compte_tva'         => null,
//                     'type_journal'       => 'CL',
//                     'categorie'          => 'Clôture',
//                     'prorat_de_deduction'=> null,
//                     'piece_justificative'=> null,
//                     'libelle'            => "Clôture compte $compteCh",
//                     'filtre_selectionne' => null,
//                     'societe_id'         => $societeId,
//                     'numero_piece'       => null,
//                 ];
//             }

//             // 4.3) Écriture du résultat net, direction selon signe
//             if ($resultNet !== 0) {
//                 if ($resultNet > 0) {
//                     // Bénéfice → créditer le compte 120 et débiter la somme totale des soldes de comptes 7
//                     $ecrituresCloture[] = [
//                         'date'               => Carbon::createFromDate($annee, 12, 31)->format('Y-m-d H:i:s'),
//                         'numero_facture'     => 'CL' . $annee,
//                         'compte'             => '120',             // compte Résultat de l’exercice (bénéfice)
//                         'debit'              => 0,
//                         'credit'             => $resultNet,
//                         'contre_partie'      => null,
//                         'rubrique_tva'       => null,
//                         'compte_tva'         => null,
//                         'type_journal'       => 'CL',
//                         'categorie'          => 'Clôture',
//                         'prorat_de_deduction'=> null,
//                         'piece_justificative'=> null,
//                         'libelle'            => "Bénéfice exercice $annee",
//                         'filtre_selectionne' => null,
//                         'societe_id'         => $societeId,
//                         'numero_piece'       => null,
//                     ];
//                 } else {
//                     // Perte (résultat < 0) → débiter le compte 129 et créditer la somme totale des soldes de comptes 6
//                     $montantPerte = abs($resultNet);
//                     $ecrituresCloture[] = [
//                         'date'               => Carbon::createFromDate($annee, 12, 31)->format('Y-m-d H:i:s'),
//                         'numero_facture'     => 'CL' . $annee,
//                         'compte'             => '129',             // compte Perte de l’exercice
//                         'debit'              => $montantPerte,
//                         'credit'             => 0,
//                         'contre_partie'      => null,
//                         'rubrique_tva'       => null,
//                         'compte_tva'         => null,
//                         'type_journal'       => 'CL',
//                         'categorie'          => 'Clôture',
//                         'prorat_de_deduction'=> null,
//                         'piece_justificative'=> null,
//                         'libelle'            => "Perte exercice $annee",
//                         'filtre_selectionne' => null,
//                         'societe_id'         => $societeId,
//                         'numero_piece'       => null,
//                     ];
//                 }
//             }

//             // 5) Insérer toutes les écritures de clôture en base
//             foreach ($ecrituresCloture as $ligneCloture) {
//                 OperationCourante::create($ligneCloture);
//             }

//             // 6) Mettre à jour le statut de clôture (vous pouvez créer un champ supplémentaire dans votre table exercices)
//             // Par exemple, si vous avez un modèle Exercice avec un champ "cloture" :
//             // Exercice::where('societe_id', $societeId)->where('annee', $annee)->update(['cloture' => true]);

//             DB::commit();
//             return response()->json([
//                 'message'       => "Exercice $annee clôturé avec succès.",
//                 'totalCharges'  => $totalCharges,
//                 'totalProduits' => $totalProduits,
//                 'resultNet'     => $resultNet,
//             ], 200);
//         }
//         catch (\Exception $e) {
//             DB::rollBack();
//             Log::error("Erreur lors de la clôture de l’exercice $annee : " . $e->getMessage());
//             return response()->json([
//                 'error' => "Impossible de clore l’exercice : " . $e->getMessage()
//             ], 500);
//         }
//     }



//  /**
//      * Upload, parse PDF page par page, extraire avec l'IA,
//      * puis persister directement dans operation_courante.
//      */
//     public function extractPdf(Request $request, Parser $parser)
//     {
//         // 1) Validation
//         $request->validate([
//             'pdf' => 'required|file|mimes:pdf|max:10240',
//         ]);

//         // 2) Store temporarily
//         $path = $request->file('pdf')->store('pdfs');
//         $file = storage_path("app/{$path}");

//         // 3) Retrieve current company ID
//         $socId = Session::get('societeId');
//         if (! $socId) {
//             return response()->json(['error' => 'Société non définie'], 400);
//         }

//         // 4) Instantiate Guzzle for OpenAI
//         $guzzle = new GuzzleClient([
//             'base_uri' => config('services.openai.base_uri', 'https://api.openai.com'),
//             'timeout'  => config('services.openai.timeout', 60),
//         ]);

//         $created = [];

//         // 5) Parse PDF and loop pages
//         $pdf = $parser->parseFile($file);
//         $pages = $pdf->getPages();

//         foreach ($pages as $page) {
//             // Take up to first 3000 chars per page
//             $chunk = mb_substr($page->getText(), 0, 3000);

//             // 6) Call OpenAI chat/completions
//             $response = $guzzle->post('/v1/chat/completions', [
//                 'headers' => [
//                     'Authorization' => 'Bearer ' . config('services.openai.key'),
//                     'Content-Type'  => 'application/json',
//                 ],
//                 'json' => [
//                     'model'       => 'gpt-4',
//                     'temperature' => 0.0,
//                     'messages'    => [
//                         [
//                             'role'    => 'system',
//                             'content' => 'Extrait JSON de lignes de facture : date, numero_facture, compte, libelle, debit, credit, contre_partie, rubrique_tva.'
//                         ],
//                         [
//                             'role'    => 'user',
//                             'content' => $chunk,
//                         ],
//                     ],
//                 ],
//             ]);

//             $body = (string) $response->getBody();
//             $json = json_decode($body, true);

//             // Extract content field
//             $content = $json['choices'][0]['message']['content'] ?? '[]';
//             $rows = json_decode($content, true) ?: [];

//             // 7) Persist each extracted line
//             foreach ($rows as $r) {
//                 $record = OperationCourante::create([
//                     'date'           => $r['date']           ?? now()->format('Y-m-d H:i:s'),
//                     'numero_facture' => $r['numero_facture'] ?? null,
//                     'compte'         => $r['compte']         ?? null,
//                     'libelle'        => $r['libelle']        ?? null,
//                     'debit'          => $r['debit']          ?? 0,
//                     'credit'         => $r['credit']         ?? 0,
//                     'contre_partie'  => $r['contre_partie']  ?? null,
//                     'rubrique_tva'   => $r['rubrique_tva']   ?? null,
//                     'type_journal'   => 'Achats',
//                     'categorie'      => 'Achat',
//                     'societe_id'     => $socId,
//                 ]);

//                 $created[] = $record;
//             }
//         }

//         // 8) Return created records
//         return response()->json($created);
//     }

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
    $folders_achat = Folder::where('societe_id', $societeId)
                            ->where('folder_id', $folderId)
                            ->get();
    $folders_ventes  = Folder::where('societe_id', $societeId)
                            ->where('folder_id', $folderId)
                            ->where('type_folder', 'vente')
                            ->get();
    // Récupération des fichiers du dossier sélectionné
    $files_banque = File::where('societe_id', $societeId)
                        ->where('folders', $folderId)
                        ->get();
    $files_achat = File::where('societe_id', $societeId)
                        ->where('folders', $folderId)
                        ->get();
    $files_ventes  = File::where('societe_id', $societeId)
                        ->where('folders', $folderId)
                        ->where('type', 'vente')
                        ->get();

    return response()->json([
        'folders_banque' => $folders_banque,
        'files_banque' => $files_banque,
         'folders_achat' => $folders_achat,
        'files_achat' => $files_achat,
         'folders_ventes'  => $folders_ventes,
        'files_ventes'    => $files_ventes
    ]);
}


public function index(Request $request)
{
    // 1) Récupérer l'ID de la société depuis la session
    $societeId = session('societeId');

    // Récupérer les dossiers manuels non supprimés
    $dossierManuel = Dossier::where('societe_id', $societeId)
                            ->whereNull('deleted_at')
                            ->get();

    // Dossiers bancaires ou autres types non supprimés
    $folders_banque = Folder::where('societe_id', $societeId)
    ->where(function ($query) {
        $query->where('type_folder', 'banque')
              ->orWhereNotIn('type_folder', ['achat', 'vente', 'caisse', 'impot', 'paie', 'dossier_permanant']);
    })
    ->whereNull('deleted_at')
    ->where(function ($query) {
        $query->whereNull('folder_id')
              ->orWhere('folder_id', 0);
    })
    ->get();


    $folders_achat = Folder::where('societe_id', $societeId)
    ->where(function ($query) {
        $query->where('type_folder', 'Ventes')
              ->orWhereNotIn('type_folder', ['banque', 'achat', 'caisse', 'impot', 'paie', 'dossier_permanant']);
    })
    ->whereNull('deleted_at')
    ->where(function ($query) {
        $query->whereNull('folder_id')
              ->orWhere('folder_id', 0);
    })
    ->get();

    $folders_ventes = Folder::where('societe_id', $societeId)
        ->where(function ($query) {
        $query->where('type_folder', 'vente')
              ->orWhereNotIn('type_folder', ['banque', 'vente', 'caisse', 'impot', 'paie', 'dossier_permanant']);
    })
    ->whereNull('deleted_at')
    ->where(function ($query) {
        $query->whereNull('folder_id')
              ->orWhere('folder_id', 0);
    })
    ->get();

    // 2) Récupérer l'id à éditer depuis la query-string (ou null)
    $editId = $request->query('edit');

    // 3) Initialiser les collections
    $planComptable = collect();
    $files         = collect();
    $files_banque  = collect();
    $files_achat   = collect();
    $files_vente   = collect();

    if ($societeId) {
        // Plan comptable non supprimé
        $planComptable = PlanComptable::where('societe_id', $societeId)
                            ->get();

        $files = File::where('societe_id', $societeId)
                     ->where('type', 'caisse')
                     ->whereNull('deleted_at')
                     ->get();

       $files_banque = File::where('societe_id', $societeId)
            ->where(function ($query) {
                $query->where('type', 'banque')
                    ->orWhereNotIn('type', ['achat', 'vente', 'caisse', 'impot', 'paie', 'dossier_permanant']);
            })
            ->whereNull('deleted_at')
            ->where(function ($query) {
                $query->whereNull('folders')
                    ->orWhere('folders', 0);
            })
            ->get();


        $files_achat = File::where('societe_id', $societeId)
    ->where(function ($query) {
        $query->where('type', 'achat')
              ->orWhereNotIn('type', ['banque', 'vente', 'caisse', 'impot', 'paie', 'dossier_permanant']);
    })
    ->whereNull('deleted_at')
    ->where(function ($query) {
        $query->whereNull('folders')
              ->orWhere('folders', 0);
    })
    ->get();

        $files_ventes = File::where('societe_id', $societeId)
                               ->where(function ($query) {
        $query->where('type', 'vente')
              ->orWhereNotIn('type', ['banque', 'achat', 'caisse', 'impot', 'paie', 'dossier_permanant']);
    })
    ->whereNull('deleted_at')
    ->where(function ($query) {
        $query->whereNull('folders')
              ->orWhere('folders', 0);
    })
    ->get();
    }

    return view('Operation_Courante', compact(
        'files',
        'planComptable',
        'files_banque',
        'files_achat',
        'files_ventes',
        'editId',
        'folders_banque',
        'folders_achat',
        'folders_ventes',
        'dossierManuel'
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

public function checkExists(Request $request)
    {
        $piece = $request->input('piece');
        if (! $piece) {
            return response()->json(['exists' => false]);
        }

        $exists = DB::table('operation_courante')
            ->where('piece_justificative', $piece)
            ->exists();

        return response()->json(['exists' => (bool) $exists]);
    }

public function store(Request $request)
{
    Log::info('Début de la sauvegarde des lignes (store général)');

    $societeId = session('societeId');
    Log::info("ID société: " . ($societeId ?? 'NULL'));

    if (! $societeId) {
        Log::error('Aucune société sélectionnée en session');
        return response()->json(['error' => 'Aucune société sélectionnée en session'], 400);
    }

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
        'lignes.*.client_ref'          => 'nullable|string',
        'lignes.*.file_id'             => 'nullable',
        'lignes.*.fact_lettrer'        => 'nullable|string',
    ]);

    Log::debug('Payload reçu (lignes):', $request->input('lignes'));

    $responseData = [];

    DB::beginTransaction();
    try {
        // découper en paquets pour robustesse (taille paramétrable)
        $chunks = array_chunk($validatedData['lignes'], 200);

        foreach ($chunks as $chunk) {
            foreach ($chunk as $ligneData) {
                Log::debug('Traitement ligne payload (store général)', $ligneData);

                // Exclure Opérations Diverses du store général
                if (isset($ligneData['categorie']) && $ligneData['categorie'] === "Opérations Diverses") {
                    Log::info("Ligne 'Opérations Diverses' détectée et exclue du store général (id si fourni): " . ($ligneData['id'] ?? 'none'));
                    continue;
                }

                // Normalisation dates
                $lineDateObj = !empty($ligneData['date']) ? Carbon::parse($ligneData['date']) : Carbon::now();
                $dateSql = $lineDateObj->format('Y-m-d H:i:s');
                $dateLivrSql = !empty($ligneData['date_livr'])
                    ? Carbon::parse($ligneData['date_livr'])->format('Y-m-d H:i:s')
                    : $dateSql;

                // Skip ligne vraiment vide (toujours utile pour ignorer lignes totalement vides)
                if (
                    $lineDateObj->format('Y-m-d') === Carbon::now()->format('Y-m-d')
                    && (empty($ligneData['numero_facture']) || $ligneData['numero_facture'] === 'N/A')
                    && empty($ligneData['compte'])
                    && ((isset($ligneData['debit'])  ? $ligneData['debit']  : 0) == 0)
                    && ((isset($ligneData['credit']) ? $ligneData['credit'] : 0) == 0)
                ) {
                    Log::info("Ligne vide ignorée (skip)");
                    continue;
                }

                // Build data de base
                $debit  = isset($ligneData['debit']) ? $ligneData['debit'] : 0;
                $credit = isset($ligneData['credit']) ? $ligneData['credit'] : 0;
                // montant = débit si non nul, sinon crédit
                $montant = ($debit != 0) ? $debit : $credit;

                $data = [
                    'numero_facture'      => $ligneData['numero_facture']       ?? null,
                    'compte'              => $ligneData['compte']               ?? null,
                    'debit'               => $debit,
                    'credit'              => $credit,
                    'contre_partie'       => $ligneData['contre_partie']        ?? null,
                    'numero_dossier'      => $ligneData['numero_dossier']       ?? null,
                    'rubrique_tva'        => $ligneData['rubrique_tva']         ?? null,
                    'compte_tva'          => $ligneData['compte_tva']           ?? null,
                    'prorat_de_deduction' => $ligneData['prorat_de_deduction']  ?? null,
                    'type_journal'        => $ligneData['type_journal']         ?? null,
                    'categorie'           => $ligneData['categorie']            ?? null,
                    'piece_justificative' => $ligneData['piece_justificative']  ?? null,
                    'libelle'             => $ligneData['libelle']              ?? null,
                    'filtre_selectionne'  => $ligneData['filtre_selectionne']   ?? null,
                    'societe_id'          => $societeId,
                    'date'                => $dateSql,
                    'date_livr'           => $dateLivrSql,
                    'client_ref'          => $ligneData['client_ref']          ?? null,
                ];

                        // --- FALLBACK: si pas de compte principal mais compte_tva fourni, utiliser compte_tva comme compte (optionnel) ---
            if (empty($data['compte']) && !empty($data['compte_tva'])) {
                $data['compte'] = $data['compte_tva'];
                Log::debug("Fallback compte <- compte_tva pour ligne (compte vide) : {$data['compte']}");
            }

            // Assure que $data['compte'] n'est jamais NULL (MySQL refuse NULL si colonne NOT NULL)
            if ($data['compte'] === null) {
                $data['compte'] = ''; // ou 'COMPTE_VIDE' si tu préfères un flag visible
            }

            // remplir reste_montant_lettre avec le débit ou le crédit (débit prioritaire)
            $data['reste_montant_lettre'] = $montant;

            // Matching / create : si compte vide on force create pour éviter collision updateOrCreate
            $record = null;

            if (!empty($ligneData['id'])) {
                $existingById = OperationCourante::find($ligneData['id']);
                if ($existingById) {
                    $toUpdate = array_merge($data, [
                        'client_ref' => $data['client_ref'] ?? $existingById->client_ref
                    ]);
                    $existingById->update($toUpdate);
                    $record = $existingById->fresh();
                }
            }

            if (!$record && !empty($data['client_ref'])) {
                $query = OperationCourante::where('societe_id', $societeId)
                    ->where('client_ref', $data['client_ref']);

                if (!empty($data['compte'])) $query->where('compte', $data['compte']);
                if (!empty($data['debit']))  $query->where('debit', $data['debit']);
                if (!empty($data['credit'])) $query->where('credit', $data['credit']);

                $existingByClientRef = $query->first();

                if ($existingByClientRef) {
                    $existingByClientRef->update($data);
                    $record = $existingByClientRef->fresh();
                }
            }

            if (!$record) {
                // si compte vide -> create pour éviter matching ambigu
                if (empty($data['compte'])) {
                    $record = OperationCourante::create($data);
                } else {
                    $lookup = [
                        'societe_id'    => $societeId,
                        'numero_facture'=> $data['numero_facture'],
                        'compte'        => $data['compte'],
                        'debit'         => $data['debit'],
                        'credit'        => $data['credit'],
                        'date'          => Carbon::parse($data['date'])->format('Y-m-d 00:00:00'),
                    ];
                    $record = OperationCourante::updateOrCreate($lookup, $data);
                }
            }
                // Gestion file_id (si fourni)
                            if (array_key_exists('file_id', $ligneData)) {
                                $incomingFileId = $ligneData['file_id'];
                                if ($incomingFileId !== null && $incomingFileId !== '') {
                                    if (is_numeric($incomingFileId)) {
                                        $candidateId = (int) $incomingFileId;
                                        $fileModel = \App\Models\File::find($candidateId);
                                        if ($fileModel) {
                                            if (isset($fileModel->societe_id) && $fileModel->societe_id && $fileModel->societe_id != $societeId) {
                                                Log::warning("file_id {$candidateId} n'appartient pas à la société {$societeId} — ignore.");
                                            } else {
                                                if ($record->file_id !== $candidateId) {
                                                    $record->file_id = $candidateId;
                                                    $record->save();
                                                }
                                            }
                                        } else {
                                            Log::warning("file_id fourni introuvable: {$candidateId}");
                                        }
                                    } else {
                                        Log::warning("file_id non numérique ignoré: " . substr((string)$incomingFileId,0,200));
                                    }
                                }
                            }

                            // Persist client_ref si reçu
                            if (!empty($data['client_ref']) && ($record->client_ref !== $data['client_ref'])) {
                                $record->client_ref = $data['client_ref'];
                                $record->save();
                            }

                            // push dans réponse
                            $record = $record->fresh();
                            $respItem = $record->toArray();
                            $respItem['date_formatted'] = Carbon::parse($record->date)->format('d/m/Y');
                            $respItem['date_livr_formatted'] = $record->date_livr ? Carbon::parse($record->date_livr)->format('d/m/Y') : null;
                            $responseData[] = $respItem;
                        } // end foreach chunk
                    } // end foreach chunks

                    DB::commit();
                    Log::info('Opérations enregistrées avec succès');
                    return response()->json(['data' => $responseData], 200);
                } catch (\Exception $e) {
                    DB::rollBack();
                    Log::error('Erreur sauvegarde lignes (store général): ' . $e->getMessage());
                    Log::error($e->getTraceAsString());
                    return response()->json([
                        'error'   => 'Erreur lors de la sauvegarde des lignes.',
                        'details' => $e->getMessage(),
                    ], 500);
                }
}

 public function storeOperationDiverses(Request $request): JsonResponse
{
    Log::info('Début de la sauvegarde des lignes (Opérations Diverses)');

    $societeId = session('societeId');
    Log::info("ID société: " . ($societeId ?? 'NULL'));

    if (! $societeId) {
        Log::error('Aucune société sélectionnée en session');
        return response()->json(['error' => 'Aucune société sélectionnée en session'], 400);
    }

    // Récupérer racine 142 (utilisée pour calcul TVA dans contre-partie si nécessaire)
    $racine = Racine::where('societe_id', $societeId)
        ->where('num_racines', 142)
        ->first();

    // taux brut depuis la racine (flexible sur le nom de colonne)
    $tauxRaw = $racine->taux ?? $racine->Taux ?? null;
    $taux = 0.0;
    if (is_numeric($tauxRaw)) {
        $t = (float) $tauxRaw;
        // normaliser si enregistré en pourcentage (ex: 20 => 0.20)
        $taux = ($t > 1) ? ($t / 100.0) : $t;
    }

    // validation : on ne demande plus date_livr
    $validatedData = $request->validate([
        'lignes'                       => 'required|array',
        'lignes.*.id'                  => 'nullable|integer',
        'lignes.*.date'                => 'nullable|date',
        'lignes.*.date_lettrage'       => 'nullable|date',
        'lignes.*.numero_dossier'      => 'nullable|string',
        'lignes.*.numero_facture'      => 'nullable|string',
        'lignes.*.compte'              => 'nullable|string',
        'lignes.*.debit'               => 'nullable|numeric|min:0',
        'lignes.*.credit'              => 'nullable|numeric|min:0',
        'lignes.*.contre_partie'       => 'nullable|string',
        'lignes.*.rubrique_tva'        => 'nullable', // on forcera en string plus bas
        'lignes.*.compte_tva'          => 'nullable',
        'lignes.*.type_journal'        => 'nullable|string',
        'lignes.*.categorie'           => 'nullable|string',
        'lignes.*.prorat_de_deduction' => 'nullable|string',
        'lignes.*.libelle'             => 'nullable|string',
        'lignes.*.filtre_selectionne'  => 'nullable|string|in:libre,contre-partie',
        'lignes.*.piece_justificative' => 'nullable|string',
        'lignes.*.client_ref'          => 'nullable|string',
        'lignes.*.fact_lettrer'        => 'nullable',
        'lignes.*.file_id'             => 'nullable|integer',
    ]);

    Log::debug('Payload reçu (lignes Diverses):', $request->input('lignes'));

    $responseData = [];

    DB::beginTransaction();
    try {
        $userId = $request->user()->id ?? null;

        // helper local pour vérifier si une écriture "équivalente" existe déjà
        $existsSimilar = function(array $criteria) {
            $q = OperationCourante::where('societe_id', $criteria['societe_id'])
                ->where('numero_facture', $criteria['numero_facture'] ?? null)
                ->where('compte', $criteria['compte'] ?? null)
                ->where('contre_partie', $criteria['contre_partie'] ?? null)
                ->where('debit', $criteria['debit'] ?? 0)
                ->where('credit', $criteria['credit'] ?? 0);

            if (!empty($criteria['piece_justificative'])) {
                $q->where('piece_justificative', $criteria['piece_justificative']);
            }
            if (!empty($criteria['date'])) {
                // comparaison sur la date (jour) pour robustesse
                $q->whereDate('date', Carbon::parse($criteria['date'])->format('Y-m-d'));
            }
            return $q->exists();
        };

        foreach ($validatedData['lignes'] as $ligneData) {
            Log::debug('Traitement ligne payload (Diverses)', $ligneData);

            // Si catégorie présente et différente -> ignorer
            if (!empty($ligneData['categorie']) && $ligneData['categorie'] !== "Opérations Diverses") {
                Log::warning("Ligne ignorée : categorie != 'Opérations Diverses'.");
                continue;
            }

            // Dates normalisées : date et date_lettrage (si fournie)
            $lineDateObj = !empty($ligneData['date']) ? Carbon::parse($ligneData['date']) : Carbon::now();
            $dateSql = $lineDateObj->format('Y-m-d H:i:s');
            $dateLettrageSql = !empty($ligneData['date_lettrage']) ? Carbon::parse($ligneData['date_lettrage'])->format('Y-m-d H:i:s') : $dateSql;

            // Skip ligne vraiment vide (heuristique existante)
            if (
                $lineDateObj->format('Y-m-d') === Carbon::now()->format('Y-m-d')
                && (empty($ligneData['numero_facture']) || $ligneData['numero_facture'] === 'N/A')
                && empty($ligneData['compte'])
                && ((isset($ligneData['debit'])  ? $ligneData['debit']  : 0) == 0)
                && ((isset($ligneData['credit']) ? $ligneData['credit'] : 0) == 0)
            ) {
                Log::info("Ligne Diverses vide ignorée (skip)");
                continue;
            }

            $debit  = isset($ligneData['debit']) ? floatval($ligneData['debit']) : 0.0;
            $credit = isset($ligneData['credit']) ? floatval($ligneData['credit']) : 0.0;
            $montant = $debit != 0 ? $debit : $credit;

            $hasFactLettrer = isset($ligneData['fact_lettrer']) && $ligneData['fact_lettrer'] !== null && $ligneData['fact_lettrer'] !== '';

            // coercions
            $compteVal = isset($ligneData['compte']) ? trim((string)$ligneData['compte']) : null;
            $contrePartieVal = isset($ligneData['contre_partie']) ? trim((string)$ligneData['contre_partie']) : null;
            $numeroFactureVal = isset($ligneData['numero_facture']) && $ligneData['numero_facture'] !== '' ? trim((string)$ligneData['numero_facture']) : null;
            $rubriqueTvaVal = isset($ligneData['rubrique_tva']) && $ligneData['rubrique_tva'] !== '' ? (string)$ligneData['rubrique_tva'] : null;
            $compteTvaVal = isset($ligneData['compte_tva']) && $ligneData['compte_tva'] !== '' ? (string)$ligneData['compte_tva'] : null;
            $filtreSelectionne = $ligneData['filtre_selectionne'] ?? null;

            // Préparer données opération principale
            $data = [
                'numero_facture'      => $numeroFactureVal,
                'compte'              => $compteVal,
                'debit'               => $debit,
                'credit'              => $credit,
                'contre_partie'       => $contrePartieVal,
                'numero_dossier'      => $ligneData['numero_dossier']       ?? null,
                'rubrique_tva'        => $rubriqueTvaVal !== null ? (string)$rubriqueTvaVal : null,
                'compte_tva'          => $compteTvaVal !== null ? (string)$compteTvaVal : null,
                'prorat_de_deduction' => $ligneData['prorat_de_deduction']  ?? null,
                'type_journal'        => $ligneData['type_journal']         ?? null,
                'categorie'           => "Opérations Diverses",
                'piece_justificative' => $ligneData['piece_justificative']  ?? null,
                'libelle'             => $ligneData['libelle']              ?? null,
                'filtre_selectionne'  => $filtreSelectionne,
                'societe_id'          => $societeId,
                'date'                => $dateSql,
                'date_lettrage'       => $dateLettrageSql,
                'client_ref'          => $ligneData['client_ref']          ?? null,
                'fact_lettrer'        => $ligneData['fact_lettrer']        ?? null,
                'file_id'             => $ligneData['file_id'] ?? null,
            ];

            // Reste montant lettre
            $data['reste_montant_lettre'] = $hasFactLettrer ? 0.00 : ($montant ?? 0.00);

            // Skip si compte manquant
            if (empty($data['compte'])) {
                Log::warning("Ligne Diverses ignorée (compte vide): " . json_encode($data));
                continue;
            }

            // Valeurs par défaut pour compatibilité (N/A conservé par choix)
            if (empty($data['numero_facture'])) $data['numero_facture'] = 'N/A';
            if (empty($data['piece_justificative'])) $data['piece_justificative'] = 'AUTO-'.$societeId;

            // Doublons (mêmes règles)
            $existing = OperationCourante::where([
                ['societe_id',    $societeId],
                ['numero_facture',$data['numero_facture']],
                ['compte',        $data['compte']],
            ])->get();

            if ($existing->count() >= 2) {
                Log::info("Deux lignes 'Opérations Diverses' existantes, ligne ignorée.");
                continue;
            }

            // Récupérer record existant si id fourni
            $record = null;
            $oldFact = null;
            if (!empty($ligneData['id'])) {
                $record = OperationCourante::lockForUpdate()->find($ligneData['id']);
                if ($record) $oldFact = $record->fact_lettrer ?? null;
            }

            // Tentative matching par client_ref
            if (!$record && !empty($data['client_ref'])) {
                $query = OperationCourante::where('societe_id', $societeId)
                    ->where('client_ref', $data['client_ref']);
                if (!empty($data['compte'])) $query->where('compte', $data['compte']);
                if (!empty($data['debit']))  $query->where('debit', $data['debit']);
                if (!empty($data['credit'])) $query->where('credit', $data['credit']);

                $existingByClientRef = $query->lockForUpdate()->first();
                if ($existingByClientRef) {
                    $record = $existingByClientRef;
                    $oldFact = $record->fact_lettrer ?? null;
                    $record->update($data);
                }
            }

            // create or update principal
            if (!$record) {
                $lookup = [
                    'societe_id'    => $societeId,
                    'numero_facture'=> $data['numero_facture'],
                    'compte'        => $data['compte'],
                    'debit'         => $data['debit'],
                    'credit'        => $data['credit'],
                    'date'          => Carbon::parse($data['date'])->format('Y-m-d 00:00:00'),
                    'date_lettrage' => Carbon::parse($data['date_lettrage'])->format('Y-m-d 00:00:00'),
                ];
                $record = OperationCourante::updateOrCreate($lookup, $data);
            } else {
                $record->update($data);
            }

            // ===== LETTRAGE (inchangé) =====
            $factLettrerRaw = $ligneData['fact_lettrer'] ?? $record->fact_lettrer ?? null;
            if (!empty($factLettrerRaw)) {
                // calcul acompte
                $acompte = 0;
                if (!empty($data['debit']) && $data['debit'] != 0) $acompte = $data['debit'];
                elseif (!empty($data['credit']) && $data['credit'] != 0) $acompte = $data['credit'];

                $factures = array_filter(array_map('trim', explode('&', $factLettrerRaw)));
                if (count($factures) === 1) {
                    $factureStr = trim($factures[0]);
                    if (!empty($factureStr)) {
                        $parts = explode('|', $factureStr);
                        if (count($parts) === 4) {
                            $operationId = intval(trim($parts[0]));
                            $numero = trim($parts[1]);
                            $montantFact = floatval(trim($parts[2]));
                            $dateFact = trim($parts[3]);
                            $operation = OperationCourante::find($operationId);
                            if ($operation) {
                                if ($acompte > ($operation->reste_montant_lettre ?? 0)) {
                                    Log::warning("Acompte supérieur au reste à lettrer pour l'op {$operationId} (acompte={$acompte} reste={$operation->reste_montant_lettre})");
                                } else {
                                    Lettrage::create([
                                        'NFacture' => $numero,
                                        'Acompte' => $acompte,
                                        'compte' => $data['compte'],
                                        'id_operation' => $operationId,
                                        'id_user' => $userId,
                                    ]);
                                    if (Schema::hasColumn('operation_courante', 'reste_montant_lettre')) {
                                        $operation->reste_montant_lettre = max(0, ($operation->reste_montant_lettre ?? 0) - $acompte);
                                        $operation->save();
                                    }
                                }
                            }
                        }
                    }
                } else {
                    // répartition multiple
                    $resteAcompte = $acompte;
                    foreach ($factures as $factureStr) {
                        $factureStr = trim($factureStr);
                        if (empty($factureStr)) continue;
                        $parts = explode('|', $factureStr);
                        if (count($parts) === 4) {
                            $operationId = intval(trim($parts[0]));
                            $numero = trim($parts[1]);
                            $montantFact = floatval(trim($parts[2]));
                            $dateFact = trim($parts[3]);
                            $operation = OperationCourante::find($operationId);
                            if ($operation && $resteAcompte > 0) {
                                $restTarget = $operation->reste_montant_lettre ?? 0;
                                if ($resteAcompte > $restTarget) {
                                    Lettrage::create([
                                        'NFacture' => $numero,
                                        'Acompte' => $restTarget,
                                        'compte' => $data['compte'],
                                        'id_operation' => $operationId,
                                        'id_user' => $userId,
                                    ]);
                                    $resteAcompte -= $restTarget;
                                    if (Schema::hasColumn('operation_courante', 'reste_montant_lettre')) {
                                        $operation->reste_montant_lettre = 0;
                                        $operation->save();
                                    }
                                } else {
                                    Lettrage::create([
                                        'NFacture' => $numero,
                                        'Acompte' => $resteAcompte,
                                        'compte' => $data['compte'],
                                        'id_operation' => $operationId,
                                        'id_user' => $userId,
                                    ]);
                                    if (Schema::hasColumn('operation_courante', 'reste_montant_lettre')) {
                                        $operation->reste_montant_lettre = max(0, $operation->reste_montant_lettre - $resteAcompte);
                                        $operation->save();
                                    }
                                    $resteAcompte = 0;
                                    break;
                                }
                            }
                        }
                    }
                    if ($resteAcompte > 0) {
                        Log::warning("L'acompte ({$acompte}) est supérieur au total des restes à lettrer (reste non appliqué: {$resteAcompte}).");
                    }
                }

                // Nettoyage factures pour stockage
                $facturesNettoyees = [];
                foreach ($factures as $factureStr) {
                    $parts = explode('|', trim($factureStr));
                    if (count($parts) === 4) {
                        $facturesNettoyees[] = implode('|', array_slice($parts, 1));
                    } else {
                        $facturesNettoyees[] = trim($factureStr);
                    }
                }
                $cleaned = implode(' & ', $facturesNettoyees);
                $record->fact_lettrer = $cleaned;
                $record->save();
            } // fin lettrage

            // ===== CREATION CONTRE-PARTIE AUTOMATIQUE (si demandé) =====
            if (!empty($data['filtre_selectionne']) && $data['filtre_selectionne'] === 'contre-partie' && !empty($data['contre_partie'])) {
                $compteStarts3421 = str_starts_with(trim((string)$data['compte']), '3421');
                $contreStarts6147 = str_starts_with(trim((string)$data['contre_partie']), '6147');
                $isCredit = (!empty($data['credit']) && floatval($data['credit']) > 0);

                // CAS TVA spécifique
                if ($contreStarts6147 && $compteStarts3421 && $isCredit) {
                    // calcul TVA + imputation
                    $compteTVA = $racine->compte_tva ?? $racine->compte ?? null;
                    if (empty($compteTVA)) $compteTVA = '44571'; // fallback sûr

                    $creditOrig = floatval($data['credit']);
                    $debitTVA = round( ($creditOrig / (1 + $taux)) * $taux, 2 );
                    $debitImputation = round( $creditOrig - $debitTVA, 2 );
                    $somme = round($debitTVA + $debitImputation, 2);
                    $diff = round($creditOrig - $somme, 2);
                    if (abs($diff) >= 0.01) {
                        $debitImputation = round($debitImputation + $diff, 2);
                    }

                    // même numero_facture que la ligne principale
                    $numeroFactForCreated = $record->numero_facture;

                    // construire payloads
                    $contrePartieData1 = [
                        'date' => $data['date'],
                        'fact_lettrer' => $record->fact_lettrer,
                        'compte' => (string) $compteTVA,
                        'contre_partie' => $data['compte'],
                        'libelle' => $data['libelle'],
                        'debit' => $debitTVA,
                        'credit' => 0,
                        'piece_justificative' => $data['piece_justificative'],
                        'date_lettrage' => $data['date_lettrage'] ?? $data['date'],
                        'type_journal' => $data['type_journal'] ?? null,
                        'numero_facture' => $numeroFactForCreated,
                        'societe_id' => $societeId,
                        'categorie' => 'Opérations Diverses',
                        'file_id' => $data['file_id'] ?? null,
                        'reste_montant_lettre' => !empty($record->fact_lettrer) ? 0.00 : ($debitTVA ?? 0.00),
                    ];
                    $contrePartieData2 = [
                        'date' => $data['date'],
                        'fact_lettrer' => $record->fact_lettrer,
                        'compte' => (string) $data['contre_partie'],
                        'contre_partie' => $data['compte'],
                        'libelle' => $data['libelle'],
                        'debit' => $debitImputation,
                        'credit' => 0,
                        'piece_justificative' => $data['piece_justificative'],
                        'date_lettrage' => $data['date_lettrage'] ?? $data['date'],
                        'type_journal' => $data['type_journal'] ?? null,
                        'numero_facture' => $numeroFactForCreated,
                        'societe_id' => $societeId,
                        'categorie' => 'Opérations Diverses',
                        'file_id' => $data['file_id'] ?? null,
                        'reste_montant_lettre' => !empty($record->fact_lettrer) ? 0.00 : ($debitImputation ?? 0.00),
                    ];

                    // garde création si similaire n'existe pas
                    if (!empty($contrePartieData1['compte'])) {
                        if (!$existsSimilar([
                            'societe_id' => $societeId,
                            'numero_facture' => $contrePartieData1['numero_facture'],
                            'compte' => $contrePartieData1['compte'],
                            'contre_partie' => $contrePartieData1['contre_partie'],
                            'debit' => $contrePartieData1['debit'],
                            'credit' => $contrePartieData1['credit'],
                            'piece_justificative' => $contrePartieData1['piece_justificative'],
                            'date' => $contrePartieData1['date'],
                        ])) {
                            OperationCourante::create($contrePartieData1);
                        } else {
                            Log::info('Ligne TVA déjà existante -> création ignorée', $contrePartieData1);
                        }
                    } else {
                        Log::warning('Tentative création ligne TVA ignorée : compte TVA vide', $contrePartieData1);
                    }

                    if (!empty($contrePartieData2['compte'])) {
                        if (!$existsSimilar([
                            'societe_id' => $societeId,
                            'numero_facture' => $contrePartieData2['numero_facture'],
                            'compte' => $contrePartieData2['compte'],
                            'contre_partie' => $contrePartieData2['contre_partie'],
                            'debit' => $contrePartieData2['debit'],
                            'credit' => $contrePartieData2['credit'],
                            'piece_justificative' => $contrePartieData2['piece_justificative'],
                            'date' => $contrePartieData2['date'],
                        ])) {
                            OperationCourante::create($contrePartieData2);
                        } else {
                            Log::info('Ligne imputation déjà existante -> création ignorée', $contrePartieData2);
                        }
                    } else {
                        Log::warning('Tentative création ligne imputation ignorée : compte imputation vide', $contrePartieData2);
                    }
                } else {
                    // CAS GÉNÉRAL -> miroir simple (une écriture)
                    $numeroFactForCreated = $record->numero_facture;
                    $contrePartieData = [
                        'date' => $data['date'],
                        'fact_lettrer' => $record->fact_lettrer,
                        'compte' => (string)$data['contre_partie'],
                        'contre_partie' => $data['compte'],
                        'libelle' => $data['libelle'],
                        'debit' => $data['credit'] ?? 0,
                        'credit' => $data['debit'] ?? 0,
                        'piece_justificative' => $data['piece_justificative'],
                        'date_lettrage' => $data['date_lettrage'] ?? $data['date'],
                        'type_journal' => $data['type_journal'] ?? null,
                        'numero_facture' => $numeroFactForCreated,
                        'societe_id' => $societeId,
                        'categorie' => 'Opérations Diverses',
                        'file_id' => $data['file_id'] ?? null,
                        'reste_montant_lettre' => !empty($record->fact_lettrer) ? 0.00 : (($data['credit'] ?? $data['debit']) ?? 0.00),
                    ];

                    if (!empty($contrePartieData['compte'])) {
                        if (!$existsSimilar([
                            'societe_id' => $societeId,
                            'numero_facture' => $contrePartieData['numero_facture'],
                            'compte' => $contrePartieData['compte'],
                            'contre_partie' => $contrePartieData['contre_partie'],
                            'debit' => $contrePartieData['debit'],
                            'credit' => $contrePartieData['credit'],
                            'piece_justificative' => $contrePartieData['piece_justificative'],
                            'date' => $contrePartieData['date'],
                        ])) {
                            OperationCourante::create($contrePartieData);
                        } else {
                            Log::info('Ligne miroir déjà existante -> création ignorée', $contrePartieData);
                        }
                    } else {
                        Log::warning('Tentative création contre-partie miroir ignorée : compte vide', $contrePartieData);
                    }
                }
            } // fin condition contre-partie auto

            // Préparer réponse (rafraîchir record)
            $record = $record->fresh();
            $respItem = $record->toArray();
            $respItem['date_formatted'] = Carbon::parse($record->date)->format('d/m/Y');
            $respItem['date_lettrage_formatted'] = $record->date_lettrage ? Carbon::parse($record->date_lettrage)->format('d/m/Y') : null;
            $responseData[] = $respItem;
        } // end foreach

        DB::commit();
        Log::info('Opérations Diverses enregistrées avec succès');
        return response()->json(['data' => $responseData], 200);
    } catch (\Exception $e) {
        DB::rollBack();
        Log::error('Erreur sauvegarde lignes (Diverses): ' . $e->getMessage());
        Log::error($e->getTraceAsString());
        return response()->json([
            'error'   => 'Erreur lors de la sauvegarde des lignes Diverses.',
            'details' => $e->getMessage(),
        ], 500);
    }
}

public function searchFactureOP(Request $request)
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

    // Si le compte est envoyé avec " - intitule", ne garder que le numéro
    if (strpos($compte, ' - ') !== false) {
        $compte = trim(explode(' - ', $compte)[0]);
    }

    $query = OperationCourante::where('compte', $compte)
                              ->where('societe_id', $societeId)
                              ->where('type_journal', '!=', 'AN')
                              ->where('reste_montant_lettre', '>', 0);

    // Filtre debit / credit
    if ($debit && floatval($debit) > 0) {
        $query->where('debit', '>', 0);
    } elseif ($credit && floatval($credit) > 0) {
        $query->where('credit', '>', 0);
    }

    $operations = $query->get()->map(function ($operation) use ($debit, $credit) {
        if ($debit && floatval($debit) > 0) {
            $operation->debit = $operation->reste_montant_lettre;
            $operation->credit = null;
        } elseif ($credit && floatval($credit) > 0) {
            $operation->credit = $operation->reste_montant_lettre;
            $operation->debit = null;
        }
        return $operation;
    });

    return response()->json($operations);
}

// Route à ajouter : Route::get('/racine-tva/142', [RacineController::class, 'getTVA']);

public function getTVAop($num = 142)
{
    $societeId = session('societeId'); // récupérer la société de session
    $racine = Racine::where('societe_id', $societeId)
                     ->where('num_racines', $num)
                     ->first();

    if (!$racine) {
        return response()->json(['error' => 'Racine introuvable'], 404);
    }

    return response()->json([
        'num_racines' => $racine->num_racines,
        'compte_tva'  => $racine->compte_tva
    ]);
}
 public function getContrePartieOP($code)
    {
        $societeId = session('societeId'); // société en session

        $journal = Journal::where('societe_id', $societeId)
                          ->where('code_journal', $code)
                          ->first();

        if (!$journal) {
            return response()->json(['error' => 'Journal introuvable'], 404);
        }

        return response()->json([
            'code_journal' => $journal->code_journal,
            'contre_partie' => $journal->contre_partie // valeur qui sera affichée dans Tabulator
        ]);
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
    $operations->push([
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
       $journaux = Journal::select('code_journal', 'intitule', 'type_journal', 'contre_partie')
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
    $journaux = Journal::select('code_journal', 'intitule', 'type_journal', 'rubrique_tva', 'contre_partie')
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
    $journaux = Journal::select('code_journal', 'intitule', 'type_journal', 'contre_partie')
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
  // 1) Vérification de la société active
    $societeId = session('societeId');
    if (empty($societeId)) {
        return response()->json([
            'error' => 'Aucune société sélectionnée dans la session.'
        ], 400);
    }
    // Récupérer les rubriques TVA pour les ventes, incluant les num_racines spécifiques
    $rubriques = Racine::select('Num_racines','categorie', 'Nom_racines', 'Taux','compte_tva')
        ->where('type', 'Ca imposable')
        // ->whereIn('Num_racines')  // Ajouter la condition pour les num_racines autorisés
        ->where('societe_id', $societeId) // ✅ Filtrage par société

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
            ->select('compte', 'intitule')
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




// public function updateRow(Request $request, $id)
//     {

//         $validated = $request->validate([
//             'date' => 'required|date',
//             'numero_facture' => 'required',
//             'numero_dossier' => 'required',
//             'compte' => 'required',
//             'debit' => 'nullable|numeric',
//             'credit' => 'nullable|numeric',
//             'contre_partie' => 'nullable',
//             'rubrique_tva' => 'nullable',
//             'compte_tva' => 'nullable',
//             'prorat_de_deduction' => 'nullable|numeric',
//             'piece_justificative' => 'nullable',
//             'type_journal' => 'required',
//             'societe_id' => 'required|exists:societes,id',
//         ]);

//         $row = OperationCourante::find($id);
//         if (!$row) {
//             return response()->json(['error' => 'Donnée introuvable'], 404);
//         }

//         $row->update($validated);
//         return response()->json(['message' => 'Donnée mise à jour avec succès']);
//     }

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


public function selectFolderAchat(Request $request)
{
    $folderId = $request->query('id');
    $societeId = session('societeId');

    // Vérification du dossier parent
    $parentFolder = Folder::where('societe_id', $societeId)->where('id', $folderId)->first();
    if (!$parentFolder) {
        return response()->json(['error' => 'Dossier introuvable.'], 404);
    }

    // Récupération des sous-dossiers du dossier sélectionné (achat)
    $folders_achat = Folder::where('societe_id', $societeId)
                           ->where('folder_id', $folderId)
                           ->get();

    // Récupération des fichiers du dossier sélectionné (achat)
    $files_achat = File::where('societe_id', $societeId)
                       ->where('folders', $folderId)
                       ->get();

    return response()->json([
        'folders_achat' => $folders_achat,
        'files_achat' => $files_achat
    ]);
}

public function selectFolderVente(Request $request)
{
    $folderId = $request->query('id');
    $societeId = session('societeId');

    // Vérification du dossier parent
    $parentFolder = Folder::where('societe_id', $societeId)->where('id', $folderId)->first();
    if (!$parentFolder) {
        return response()->json(['error' => 'Dossier introuvable.'], 404);
    }

    // Récupération des sous-dossiers du dossier sélectionné (vente)
    $folders_vente = Folder::where('societe_id', $societeId)
                           ->where('folder_id', $folderId)
                           ->get();

    // Récupération des fichiers du dossier sélectionné (vente)
    $files_vente = File::where('societe_id', $societeId)
                       ->where('folders', $folderId)
                       ->get();

    return response()->json([
        'folders_vente' => $folders_vente,
        'files_vente' => $files_vente
    ]);
}



}
