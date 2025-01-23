<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\SoldeMensuel; // Assurez-vous d'importer votre modèle

class SoldeMensuelController extends Controller
{

 
  

    
    public function saveSolde(Request $request)
    {
        // Validation des données envoyées
        $request->validate([
            'mois' => 'required|numeric',
            'solde_initial' => 'required|numeric',
            'total_recette' => 'required|numeric',
            'total_depense' => 'required|numeric',
            'solde_final' => 'required|numeric',
        ]);

        // Vérifier si un solde existe déjà pour ce mois et cette année
        $existingSolde = SoldeMensuel::where('mois', $request->mois)
                                     ->where('annee', $request->annee)
                                     ->first();

        if ($existingSolde) {
            // Si un enregistrement existe déjà, vous pouvez soit le mettre à jour, soit retourner un message d'erreur.
            // Exemple : Mise à jour des valeurs
            $existingSolde->update([
                'solde_initial' => $request->solde_initial,
                'total_recette' => $request->total_recette,
                'total_depense' => $request->total_depense,
                'solde_final' => $request->solde_final,
            ]);

            return response()->json(['message' => 'Solde mensuel mis à jour avec succès!']);
        } else {
            // Sinon, créer un nouvel enregistrement
            SoldeMensuel::create([
                'mois' => $request->mois,
                'annee' => $request->annee,  // L'année envoyée depuis la vue
                'solde_initial' => $request->solde_initial,
                'total_recette' => $request->total_recette,
                'total_depense' => $request->total_depense,
                'solde_final' => $request->solde_final,
            ]);

            return response()->json(['message' => 'Solde mensuel enregistré avec succès!']);
        }
    }
}
