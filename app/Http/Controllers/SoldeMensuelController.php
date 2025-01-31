<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\SoldeMensuel; // Assurez-vous d'importer votre modèle

class SoldeMensuelController extends Controller
{

 
  

    
    public function saveSolde(Request $request)
    {
        // Récupérer l'ID de la société à partir de la session
        $societeId = session('societeId');
   
        // Validation des données envoyées
        $request->validate([
            'mois' => 'required|numeric',
            'solde_initial' => 'required|numeric',
            'total_recette' => 'required|numeric',
            'total_depense' => 'required|numeric',
            'solde_final' => 'required|numeric',
        ]);
    
        // Vérifier si un solde existe déjà pour ce mois et cette année pour cette société
        $existingSolde = SoldeMensuel::where('mois', $request->mois)
                                     ->where('annee', $request->annee)
                                     ->where('societe_id', $societeId) // Ajout de la vérification du societe_id
                                     ->first();
    
        if ($existingSolde) {
            // Si un enregistrement existe déjà, mise à jour des valeurs
            $existingSolde->update([
                'solde_initial' => $request->solde_initial,
                'total_recette' => $request->total_recette,
                'total_depense' => $request->total_depense,
                'solde_final' => $request->solde_final,
                'societe_id' => $societeId,
            ]);
    
            return response()->json(['message' => 'Solde mensuel mis à jour avec succès!']);
        } else {
            // Sinon, création d'un nouvel enregistrement
            SoldeMensuel::create([
                'mois' => $request->mois,
                'annee' => $request->annee,  // L'année envoyée depuis la vue
                'solde_initial' => $request->solde_initial,
                'total_recette' => $request->total_recette,
                'total_depense' => $request->total_depense,
                'solde_final' => $request->solde_final,
                'societe_id' => $societeId, 
            ]);
    
            return response()->json(['message' => 'Solde mensuel enregistré avec succès!']);
        }
    }
    
}
