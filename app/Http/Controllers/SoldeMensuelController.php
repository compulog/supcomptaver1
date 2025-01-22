<?php
namespace App\Http\Controllers;

use App\Models\SoldeMensuel;
use Illuminate\Http\Request;

class SoldeMensuelController extends Controller
{
    public function store(Request $request)
    {
        try {
            \Log::info($request->all()); // Log les données de la requête
    
            // Validation des données
            $request->validate([
                'mois' => 'required|date',
                'solde_initial' => 'required|numeric',
                'total_recette' => 'required|numeric',
                'total_depense' => 'required|numeric',
                'solde_final' => 'required|numeric',
            ]);
    
            // Créer un enregistrement dans la table `soldes_mensuels`
            $soldeMensuel = new SoldeMensuel();
            $soldeMensuel->mois = $request->mois;
            $soldeMensuel->solde_initial = $request->solde_initial;
            $soldeMensuel->total_recette = $request->total_recette;
            $soldeMensuel->total_depense = $request->total_depense;
            $soldeMensuel->solde_final = $request->solde_final;
            $soldeMensuel->save();
    
            return response()->json(['message' => 'Solde mensuel enregistré avec succès']);
        } catch (\Illuminate\Validation\ValidationException $e) {
            \Log::error('Validation Error: ' . $e->getMessage()); // Log l'erreur de validation
            return response()->json(['error' => 'Erreur de validation des données'], 400);
        } catch (\Exception $e) {
            \Log::error('General Error: ' . $e->getMessage()); // Log l'erreur générale
            return response()->json(['error' => 'Erreur interne'], 500);
        }
    }
    
    
}
