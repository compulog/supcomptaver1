<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\SoldeMensuel; 

class SoldeMensuelController extends Controller
{

 
    public function saveSolde(Request $request)
    {
       
         // Récupérer l'ID de la société à partir de la session
        $societeId = session('societeId');
    
        // Validation des données envoyées
        $request->validate([
            'mois' => 'required|numeric',
            'annee' => 'required|numeric', // Assurez-vous que l'année est également validée
            'solde_initial' => 'required|numeric',
            'total_recette' => 'required|numeric',
            'total_depense' => 'required|numeric',
            'solde_final' => 'required|numeric',
            'journal_code' => 'nullable|string|max:10', // Validation pour le code journal
        ]);
    
        // Vérifier si un solde existe déjà pour ce mois, cette année et ce code journal pour cette société
        $existingSolde = SoldeMensuel::where('mois', $request->mois)
                                     ->where('annee', $request->annee)
                                     ->where('societe_id', $societeId) // Ajout de la vérification du societe_id
                                     ->where('code_journal', $request->journal_code) // Vérification du code journal
                                     ->first();
    
        if ($existingSolde) {
            // Si un enregistrement existe déjà, mise à jour des valeurs
            $existingSolde->update([
                'solde_initial' => $request->solde_initial,
                'total_recette' => $request->total_recette,
                'total_depense' => $request->total_depense,
                'solde_final' => $request->solde_final,
                // 'societe_id' => $societeId, // Pas besoin de mettre à jour, car il ne change pas
                // 'code_journal' => $request->input('journal_code'), // Pas besoin de mettre à jour, car il ne change pas
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
                'code_journal' => $request->input('journal_code'), // Enregistrer le code journal
            ]);
    
            return response()->json(['message' => 'Solde mensuel enregistré avec succès!']);
        }
    }

    
    public function cloturerSolde(Request $request)
{
    // Validation des données envoyées
    $request->validate([
        'mois' => 'required|numeric',
        'annee' => 'required|numeric',
        'journal_code' => 'nullable|string|max:10',
    ]);

    // Récupérer l'ID de la société à partir de la session
    $societeId = session('societeId');

    // Trouver le solde correspondant au mois et à l'année
    $solde = SoldeMensuel::where('mois', $request->mois)
                         ->where('annee', $request->annee)
                         ->where('societe_id', $societeId)
                         ->where('code_journal', $request->journal_code)
                         ->first();

    if ($solde) {
        // Mettre à jour le champ cloturer
        $solde->cloturer = true; // ou 1
        $solde->save();

        return response()->json(['message' => 'Le solde a été clôturé avec succès !']);
    } else {
        return response()->json(['message' => 'Aucun solde trouvé pour ce mois et cette année.'], 404);
    }
}




}
