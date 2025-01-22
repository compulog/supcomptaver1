<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Transaction;  

class EtatDeCaisseController extends Controller
{
    // Méthode pour afficher la page de l'état de caisse
    public function index()
    {
        // Récupérer toutes les transactions de la base de données
        $transactions = Transaction::all();

        // Passer les données à la vue 'etat_de_caisse.blade.php'
        return view('etat_de_caisse', compact('transactions'));
    }
    public function save(Request $request)
{
    // Vérifier les données reçues
    \Log::info($request->all()); // Log les données

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
        ]);

        // Retourner une réponse de succès
        return response()->json(['success' => true]);
    } catch (\Exception $e) {
        // Log l'erreur pour déboguer
        \Log::error('Erreur lors de l\'enregistrement de la transaction', ['error' => $e->getMessage()]);
        return response()->json(['success' => false, 'message' => $e->getMessage()]);
    }
}


 
}
