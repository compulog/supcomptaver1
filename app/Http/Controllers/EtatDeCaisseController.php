<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Transaction;  
use App\Models\SoldeMensuel; // Assurez-vous d'importer votre modèle

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
    
        // Passer les données à la vue
        return view('etat_de_caisse', compact('transactions', 'soldesMensuels'));
    }
    
    
    public function save(Request $request)
{
    // Vérifier les données reçues
    \Log::info($request->all()); // Log les données
    $societeId = session('societeId'); 
       \Log::info('Societe ID : ' . $societeId);
     
    // Validation des données
    $request->validate([
        'date' => 'required|date',
        'ref' => 'required|string|max:50',
        'libelle' => 'required|string',
        'recette' => 'nullable|numeric',
        'depense' => 'nullable|numeric',
    ]);

    try {
        // Création d'une nouvelle transaction avec les données reçues
        Transaction::create([
            'date' => $request->input('date'),
            'reference' => $request->input('ref'),
            'libelle' => $request->input('libelle'),
            'recette' => $request->input('recette', 0),
            'depense' => $request->input('depense', 0),
            'societe_id' => $societeId, // Insertion du societe_id

        ]);

        // Retourner une réponse de succès
        return response()->json(['success' => true]);
    } catch (\Exception $e) {
        // Log l'erreur pour déboguer
        \Log::error('Erreur lors de l\'enregistrement de la transaction', ['error' => $e->getMessage()]);
        return response()->json(['success' => false, 'message' => $e->getMessage()]);
    }
}
public function update(Request $request, $id)
{
    try {
        $transaction = Transaction::findOrFail($id);
        $transaction->update([
            'date' => $request->input('date'),
            'reference' => $request->input('N° Référence'),
            'libelle' => $request->input('Libellé'),
            'recette' => $request->input('Recette'),
            'depense' => $request->input('Dépense'),
        ]);

        return response()->json(['success' => true]);
    } catch (\Exception $e) {
        \Log::error('Erreur lors de la modification de la transaction', ['error' => $e->getMessage()]);
        return response()->json(['success' => false, 'message' => $e->getMessage()]);
    }
}

public function edit($id)
{
    $etatcaisse = Transaction::findOrFail($id);
    return response()->json($etatcaisse);
}

}
