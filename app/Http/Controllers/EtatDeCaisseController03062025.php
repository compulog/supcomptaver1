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
        $transactions = Transaction::where('societe_id', $societeId)
        ->orderBy('created_at', 'asc') // Remplacez 'created_at' par le champ de tri souhaité
        ->get();
    
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
        \Log::info($request->all()); 
        $societeId = session('societeId'); 
        \Log::info('Societe ID : ' . $societeId);
    
        $request->validate([
            'date' => 'required|date',
            'ref' => 'nullable|string|max:50',
            'libelle' => 'nullable|string',
            'recette' => 'nullable|numeric',
            'depense' => 'nullable|numeric',
            'journal_code' => 'nullable|string|max:10', 
            'user_response' => 'nullable|string', 
        ]);
    
        try {
            $ref = $request->input('ref');
            $isRefValid = !empty($ref) && $ref !== '0';
    
            if ($request->input('user_response') === 'continue') {
                // Création directe
                Transaction::create([
                    'date' => $request->input('date'),
                    'reference' => $ref,
                    'libelle' => $request->input('libelle'),
                    'recette' => $request->input('recette', 0),
                    'depense' => $request->input('depense', 0),
                    'societe_id' => $societeId,
                    'code_journal' => $request->input('journal_code'),
                ]);
    
                return response()->json(['success' => true, 'message' => 'Transaction créée avec succès.']);
            }
    
            elseif ($request->input('user_response') === '0') {
                // Si la référence est vide, nulle ou "0", on crée sans modifier les autres
                if (!$isRefValid) {
                    Transaction::create([
                        'date' => $request->input('date'),
                        'reference' => null, // ou laisser vide
                        'libelle' => $request->input('libelle'),
                        'recette' => $request->input('recette', 0),
                        'depense' => $request->input('depense', 0),
                        'societe_id' => $societeId,
                        'code_journal' => $request->input('journal_code'),
                    ]);
    
                    return response()->json(['success' => true, 'message' => 'Transaction créée (sans référence).']);
                }
    
                // Sinon, chercher une transaction existante
                $transaction = Transaction::where('reference', $ref)
                                           ->where('societe_id', $societeId)
                                           ->first();
    
                if ($transaction) {
                    $transaction->update([
                        'date' => $request->input('date'),
                        'libelle' => $request->input('libelle'),
                        'recette' => $request->input('recette', 0),
                        'depense' => $request->input('depense', 0),
                        'code_journal' => $request->input('journal_code'),
                    ]);
    
                    return response()->json(['success' => true, 'message' => 'Transaction mise à jour avec succès.']);
                } else {
                    // Créer une nouvelle transaction avec ref valide
                    Transaction::create([
                        'date' => $request->input('date'),
                        'reference' => $ref,
                        'libelle' => $request->input('libelle'),
                        'recette' => $request->input('recette', 0),
                        'depense' => $request->input('depense', 0),
                        'societe_id' => $societeId,
                        'code_journal' => $request->input('journal_code'),
                    ]);
    
                    return response()->json(['success' => true, 'message' => 'Transaction créée avec succès.']);
                }
            } else {
                return response()->json(['success' => false, 'message' => 'Aucune action définie.']);
            }
    
        } catch (\Exception $e) {
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
