<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Transaction;  
use App\Models\SoldeMensuel; 
use App\Models\journal; 
class EtatDeCaisseController extends Controller
{

    // Méthode pour afficher la page de l'état de caisse
    public function index()
    {
        // Récupérer l'ID de la société à partir de la session
        $societeId = session('societeId');
    
        // Vérifier si l'ID de la société est défini
        if (!$societeId) {
            return response()->json(['success' => false, 'message' => 'Aucune société définie dans la session.'], 400);
        }
    
        // Récupérer toutes les transactions pour la société
        $transactions = Transaction::where('societe_id', $societeId)->get();
    
        // Récupérer tous les soldes mensuels pour la société
        $soldesMensuels = SoldeMensuel::where('societe_id', $societeId)->get();
        $journauxCaisse = Journal::where('societe_id', $societeId)
        ->where('type_journal', 'caisse')
        ->get();
        // Passer les données à la vue
        return view('etat_de_caisse', compact('transactions', 'soldesMensuels', 'journauxCaisse'));
    }
    
    public function save(Request $request)
{
    // Vérifier les données reçues
    \Log::info($request->all()); 
    $societeId = session('societeId'); 
    \Log::info('Societe ID : ' . $societeId);

  
    $request->validate([
        'date' => ' required|date',
        'ref' => 'nullable|string|max:50',
        'libelle' => 'nullable|string',
        'recette' => 'nullable|numeric',
        'depense' => 'nullable|numeric',
        'journal_code' => 'nullable|string|max:10', 
        'user_response' => 'nullable|string', 
    ]);

    try {
        // Vérifier si la transaction existe déjà avec la même référence et societe_id
        $transaction = Transaction::where('reference', $request->input('ref'))
                                   ->where('societe_id', $societeId)
                                   ->first();

        // Vérifier la valeur de user_response
        if ($request->input('user_response') === 'continue') {
            // Si la réponse est "continue", on crée une nouvelle transaction
            Transaction::create([
                'date' => $request->input('date'),
                'reference' => $request->input('ref'),
                'libelle' => $request->input('libelle'),
                'recette' => $request->input('recette', 0),
                'depense' => $request->input('depense', 0),
                'societe_id' => $societeId, // Insertion du societe_id
                'code_journal' => $request->input('journal_code'), // Insertion du code journal
            ]);

            return response()->json(['success' => true, 'message' => 'Transaction créée avec succès.']);
        }
        //  elseif ($request->input('user_response') === 'update' && $transaction) {
        //     // Si la réponse est "update" et que la transaction existe, on la met à jour
        //     $transaction->update([
        //         'date' => $request->input('date'),
        //         'libelle' => $request->input('libelle'),
        //         'recette' => $request->input('recette', 0),
        //         'depense' => $request->input('depense', 0),
        //         'code_journal' => $request->input('journal_code'), // Mettre à jour le code journal
        //     ]);

        //     return response()->json(['success' => true, 'message' => 'Transaction mise à jour avec succès.']);
        // } 
        elseif ($request->input('user_response') === '0') {
            // Si la réponse est "0", vérifier si la transaction existe
            if ($transaction) {
                // Mettre à jour la transaction existante
                $transaction->update([
                    'date' => $request->input('date'),
                    'libelle' => $request->input('libelle'),
                    'recette' => $request->input('recette', 0),
                    'depense' => $request->input('depense', 0),
                    'code_journal' => $request->input('journal_code'), // Mettre à jour le code journal
                ]);

                return response()->json(['success' => true, 'message' => 'Transaction mise à jour avec succès.']);
            } else {
                // Si la référence n'existe pas, créer une nouvelle transaction
                Transaction::create([
                    'date' => $request->input('date'),
                    'reference' => $request->input('ref'),
                    'libelle' => $request->input('libelle'),
                    'recette' => $request->input('recette', 0),
                    'depense' => $request->input('depense', 0),
                    'societe_id' => $societeId, // Insertion du societe_id
                    'code_journal' => $request->input('journal_code'), // Insertion du code journal
                ]);

                return response()->json(['success' => true, 'message' => 'Transaction créée avec succès.']);
            }
        } else {
            // Si la réponse est "update" mais que la transaction n'existe pas
            return response()->json(['success' => false, 'message' => 'Aucune transaction à mettre à jour.']);
        }
    } catch (\Exception $e) {
        // Log l'erreur pour déboguer
        \Log::error('Erreur lors de l\'enregistrement de la transaction', ['error' => $e->getMessage()]);
        return response()->json(['success' => false, 'message' => $e->getMessage()]);
    }
}
    
public function edit($id)
{
    $etatcaisse = Transaction::findOrFail($id);
    return response()->json($etatcaisse);
}

}
