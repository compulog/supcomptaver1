<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Transaction;  // Assurez-vous que le modèle Transaction existe

class TransactionController extends Controller
{
    public function save(Request $request)
    {
        dd($request);   

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
            // En cas d'erreur, retourner une réponse d'erreur
            return response()->json(['success' => false, 'message' => $e->getMessage()]);
        }
    }
}
