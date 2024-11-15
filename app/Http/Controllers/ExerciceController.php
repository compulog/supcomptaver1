<?php

namespace App\Http\Controllers;

use App\Models\File; // Assurez-vous d'importer le modèle File
use App\Models\Societe; // Assurez-vous d'importer le modèle Societe
use Illuminate\Http\Request;

class ExerciceController extends Controller
{
    
    /**
     * Affiche la vue d'un exercice spécifique.
     *
     * @param  int  $id
     * @return \Illuminate\View\View
     */
    public function show($id)
    {
        // Récupère la société avec l'ID donné
        $societe = Societe::findOrFail($id);
        session()->put('societeId', $societe->id);

        // Récupère le total des pièces pour chaque type de fichier
        $achatCount = File::where('societe_id', $societe->id)->where('type', 'Achat')->count();
        $venteCount = File::where('societe_id', $societe->id)->where('type', 'Vente')->count();
        $banqueCount = File::where('societe_id', $societe->id)->where('type', 'Banque')->count();
        $caisseCount = File::where('societe_id', $societe->id)->where('type', 'Caisse')->count();
        $impotCount = File::where('societe_id', $societe->id)->where('type', 'Impot')->count();
        $paieCount = File::where('societe_id', $societe->id)->where('type', 'Paie')->count();

        // Passe les variables à la vue
        return view('exercices', compact(
            'societe',
            'achatCount',
            'venteCount',
            'banqueCount',
            'caisseCount',
            'impotCount',
            'paieCount'
        ));
    }
}
