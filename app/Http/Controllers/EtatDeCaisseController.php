<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Transaction;
use App\Models\SoldeMensuel;
use App\Models\journal;
use Illuminate\Support\Facades\File;

use Illuminate\Support\Facades\Auth;
class EtatDeCaisseController extends Controller
{



 public function upload(Request $request)
    {
        if ($request->hasFile('file') && $request->has('transaction_id')) {
            $file = $request->file('file');
            $transactionId = $request->input('transaction_id');

            $filename = time() . '_' . $file->getClientOriginalName();
            $destinationPath = public_path('uploads');

            if (!File::exists($destinationPath)) {
                File::makeDirectory($destinationPath, 0755, true);
            }

            $file->move($destinationPath, $filename);

            $url = url('uploads/' . $filename);

            $transaction = Transaction::find($transactionId);
            if ($transaction) {
                $transaction->attachment_url = $url;
                $transaction->attachmentName = $filename; // Ou original name
                $transaction->save();
            }

            return response()->json([
                'success' => true,
                'filename' => $filename,
                'url' => $url,
            ]);
        }

        return response()->json(['success' => false, 'message' => 'Fichier ou transaction manquant'], 400);
    }

    public function view($filename)
    {
    $path = storage_path('app/public/uploads/' . $filename);

        if (!file_exists($path)) {
            abort(404, 'Fichier non trouvé.');
        }

        return response()->file($path);
    }



    // Méthode pour afficher la page de l'état de caisse

public function index()
{
    // Récupérer l'ID de la société à partir de la session
    $societeId = session('societeId');

    // Vérifier si l'ID de la société est défini
    if (!$societeId) {
        return response()->json(['success' => false, 'message' => 'Aucune société définie dans la session.'], 400);
    }

    // Charger la relation updatedBy pour avoir le nom de l'utilisateur
    $transactions = Transaction::with('updatedBy')
        ->where('societe_id', $societeId)
        ->orderBy('created_at', 'asc')
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
                // 1) clé created_at pour la date de création de la transaction
                'created_at'       => $transaction->created_at
                                         ? $transaction->created_at->toDateTimeString()
                                         : null,

                // 2) clé updated_at pour la date de dernière modification
                'updated_at'       => $transaction->updated_at
                                         ? $transaction->updated_at->toDateTimeString()
                                         : null,
                'updated_by' => $transaction->updatedBy ? $transaction->updatedBy->name : 'Inconnu',

                'attachment_url' => $transaction->attachment_url ?? null,
                'attachmentName' => $transaction->attachmentName ?? null,
            ];
        });

    // Récupérer tous les soldes mensuels pour la société
    $soldesMensuels = SoldeMensuel::where('societe_id', $societeId)->get();
    $journauxCaisse = Journal::where('societe_id', $societeId)
        ->where('type_journal', 'caisse')
        ->get();

    // Passer les données à la vue
    return view('etat_de_caisse', [
        'transactions' => $transactions,
        'soldesMensuels' => $soldesMensuels,
        'journauxCaisse' => $journauxCaisse,
    ]);
}


public function save(Request $request)
{
    $societeId = session('societeId');

    $request->validate([
        'date' => 'required|date',
        'ref' => 'nullable|string|max:50',
        'libelle' => 'nullable|string',
        'recette' => 'nullable|numeric',
        'depense' => 'nullable|numeric',
        'journal_code' => 'nullable|string|max:10',
        'user_response' => 'nullable|string',
        'file' => 'nullable|file|max:10240', // 10 MB max
    ]);

    try {
        $ref = $request->input('ref');
        $isRefValid = !empty($ref) && $ref !== '0';

        // 🧩 Traitement du fichier (s’il existe)
        $attachmentUrl = null;
        $attachmentName = null;

        if ($request->hasFile('file')) {
            $file = $request->file('file');
            $attachmentName = time() . '_' . $file->getClientOriginalName();
            $destinationPath = public_path('uploads');

            if (!File::exists($destinationPath)) {
                File::makeDirectory($destinationPath, 0755, true);
            }

            $file->move($destinationPath, $attachmentName);
            $attachmentUrl = url('uploads/' . $attachmentName);
        }

        $data = [
            'date' => $request->input('date'),
            'reference' => $isRefValid ? $ref : null,
            'libelle' => $request->input('libelle'),
            'recette' => $request->input('recette', 0),
            'depense' => $request->input('depense', 0),
            'societe_id' => $societeId,
            'code_journal' => $request->input('journal_code'),
            'updated_by' => auth()->id(),
        ];

        // Ajouter les infos fichier si présent
        if ($attachmentUrl) {
            $data['attachment_url'] = $attachmentUrl;
            $data['attachmentName'] = $attachmentName;
        }

        if ($request->input('user_response') === 'continue') {
            Transaction::create($data);

            return response()->json([
                'success' => true,
                'message' => 'Transaction créée avec succès.',
                'attachment_url' => $attachmentUrl,
                'attachmentName' => $attachmentName,
            ]);
        }

        elseif ($request->input('user_response') === '0') {
            if (!$isRefValid) {
                Transaction::create($data);

                return response()->json([
                    'success' => true,
                    'message' => 'Transaction créée (sans référence).',
                    'attachment_url' => $attachmentUrl,
                    'attachmentName' => $attachmentName,
                ]);
            }

            $transaction = Transaction::where('reference', $ref)
                                      ->where('societe_id', $societeId)
                                      ->first();

            if ($transaction) {
                $transaction->update($data);

                return response()->json([
                    'success' => true,
                    'message' => 'Transaction mise à jour avec succès.',
                    'attachment_url' => $attachmentUrl,
                    'attachmentName' => $attachmentName,
                ]);
            } else {
                Transaction::create($data);

                return response()->json([
                    'success' => true,
                    'message' => 'Transaction créée avec succès.',
                    'attachment_url' => $attachmentUrl,
                    'attachmentName' => $attachmentName,
                ]);
            }
        } else {
            return response()->json(['success' => false, 'message' => 'Aucune action définie.']);
        }

    } catch (\Exception $e) {
        return response()->json(['success' => false, 'message' => $e->getMessage()]);
    }
}



public function edit($id)
{
    $etatcaisse = Transaction::findOrFail($id);
    return response()->json($etatcaisse);
}

}
