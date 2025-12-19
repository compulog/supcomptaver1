<?php

namespace App\Http\Controllers;
use App\Models\File;
use App\Models\ReleveBancaire;
use App\Models\Transaction;
use App\Models\SoldeMensuel;
use App\Models\Journal;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class ReleveBancaireController extends Controller
{

public function viewFile(Request $request)
{
    // dd($request->all());
    $request->validate([
        'mois' => 'required|integer|min:1|max:12',
        'annee' => 'required|integer|min:2000|max:2100',
        'code_journal' => 'required|string|max:50',
    ]);

    $societeId = session('societeId');

    if (!$societeId) {
        return response()->json(['message' => 'Société non trouvée.'], 403);
    }

    // 🔍 On récupère le relevé via les 3 critères
    $releve = ReleveBancaire::where('mois', $request->mois)
                ->where('annee', $request->annee)
                ->where('code_journal', $request->code_journal)
                ->first();

    if (!$releve) {
        return response()->json(['message' => 'Releve bancaire non trouve.'], 404);
    }
// dd($releve->idfile);
    // 🔗 Ensuite on récupère le fichier associé
    $file = File::where('id', $releve->idfile)
                ->where('societe_id', $societeId)
                ->first();

    if (!$file) {
        return response()->json(['message' => 'Fichier associé non trouvé.'], 404);
    }
$filePath = Storage::disk('public')->path(str_replace('storage/', '', $file->path));

if (!file_exists($filePath)) {
    return response()->json(['message' => 'Fichier introuvable sur le serveur.'], 404);
}

return response()->file($filePath);

}


public function upload(Request $request)
{
    // Forcer la conversion au cas où ce sont des chaînes
    $request->merge([
        'mois' => (int) $request->mois,
        'annee' => (int) $request->annee,
    ]);

    // Validation
    $request->validate([
        'code_journal' => 'required|string|max:50',
        'mois' => 'required|integer|min:1|max:12',
        'annee' => 'required|integer|min:2000|max:2100',
        'file_id' => 'required|integer|exists:files,id',
    ]);

    $societeId = session('societeId');

    // 🔹 Chercher si un relevé existe déjà pour ce code journal, mois et année
    $existingReleve = ReleveBancaire::where('code_journal', $request->code_journal)
        ->where('mois', $request->mois)
        ->where('annee', $request->annee)
        ->first();

    if ($existingReleve) {
        // ✅ Mettre à jour le relevé existant
        $existingReleve->update([
            'idfile' => $request->file_id,
        ]);

        $releve = $existingReleve;
        $message = 'Relevé bancaire mis à jour avec succès.';
    } else {
        // ✅ Créer un nouveau relevé
        $releve = ReleveBancaire::create([
            'idfile' => $request->file_id,
            'code_journal' => $request->code_journal,
            'mois' => $request->mois,
            'annee' => $request->annee,
        ]);

        $message = 'Relevé bancaire enregistré avec succès.';
    }

    // 🔹 Récupérer le chemin du fichier
    $path = null;
    if ($releve->file) {
        $path = $releve->file->path;
    }

    // 🔹 Préparer la réponse JSON
    return response()->json([
        'message' => $message,
        'file_id' => $request->file_id,
        'releve_id' => $releve->id,
        'path' => $path,
    ]);
}



public function uploadetatdecaisse(Request $request)
{
    
    $request->merge([
        'mois' => (int) $request->mois,
        'annee' => (int) $request->annee,
    ]);

    $fileKey = $request->hasFile('etat_de_caisse') ? 'etat_de_caisse' : 'releve_bancaire';

    $rules = [
        $fileKey         => 'required|file|mimes:pdf,jpg,jpeg,png|max:20480', 
        'code_journal'   => 'required|string|max:50',
        'mois'           => 'required|integer|min:1|max:12',
        'annee'          => 'required|integer|min:2000|max:2100',
    ];

    $request->validate($rules);

    $uploadedFile = $request->file($fileKey);

    if ($fileKey === 'etat_de_caisse') {
        $storageDir = 'etats';
        $typeLabel = 'etat de caisse';
    } else {
        $storageDir = 'releves';
        $typeLabel = 'relever bancaire';
    }

    $path = $uploadedFile->store($storageDir, 'public');
    $societeId = session('societeId');

    $file = File::create([
        'name'       => $uploadedFile->getClientOriginalName(),
        'path'       => $path,
        'type'       => $typeLabel,
        'mois'       => $request->mois,
        'annee'      => $request->annee,
        'societe_id' => $societeId,
    ]);

   $releve = ReleveBancaire::create([
        'idfile'       => $file->id,
        'code_journal' => $request->code_journal,
        'mois'         => $request->mois,
        'annee'        => $request->annee,
    ]);

    return response()->json([
        'message'    => 'Fichier enregistré avec succès.',
        'file_id'    => $file->id,
        'releve_id'  => $releve->id,
        'path'       => $path,
        'type'       => $typeLabel,
    ]);
}

public function viewFilecaisse(Request $request)
{
    // Récupérer l'ID de la société à partir de la session
    $societeId = session('societeId');

    if (!$societeId) {
        return response()->json(['success' => false, 'message' => 'Aucune société définie dans la session.'], 400);
    }

    // Récupérer les paramètres du request
    $codeJournal = $request->input('code_journal');
    $mois = $request->input('mois');
    $annee = $request->input('annee');

    // Construire la requête
    $transactionsQuery = Transaction::with('updatedBy')
        ->where('societe_id', $societeId);

    // Filtrer par code_journal si fourni
    if ($codeJournal) {
        $transactionsQuery->where('code_journal', $codeJournal);
    }

    // Filtrer par mois et année si fournis
    if ($mois && $annee) {
        $transactionsQuery->whereYear('date', $annee)
                          ->whereMonth('date', $mois);
    }

    // Récupérer et formater les transactions
    $transactions = $transactionsQuery->orderBy('created_at', 'asc')
        ->get()
        ->map(function($transaction) {
            return [
                'id' => $transaction->id,
                'date' => $transaction->date,
                'reference' => $transaction->reference,
                'libelle' => $transaction->libelle,
                'recette' => $transaction->recette,
                'depense' => $transaction->depense,
                'societe_id' => $transaction->societe_id,
                'code_journal' => $transaction->code_journal,
                'created_at' => $transaction->created_at ? $transaction->created_at->toDateTimeString() : null,
                'updated_at' => $transaction->updated_at ? $transaction->updated_at->toDateTimeString() : null,
                'updated_by' => $transaction->updatedBy ? $transaction->updatedBy->name : 'Inconnu',
                'attachment_url' => $transaction->attachment_url ?? null,
                'attachmentName' => $transaction->attachmentName ?? null,
            ];
        });

    // Récupérer les autres informations
    $soldesMensuels = SoldeMensuel::where('societe_id', $societeId)->get();
    $journauxCaisse = Journal::where('societe_id', $societeId)
        ->where('type_journal', 'caisse')
        ->get();

    // Retourner toutes les informations en JSON
    return response()->json([
        'success' => true,
        'transactions' => $transactions,
        'soldesMensuels' => $soldesMensuels,
        'journauxCaisse' => $journauxCaisse,
    ]);
}

}
