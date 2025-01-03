<?php

namespace App\Http\Controllers;

use App\Models\Dossier;
use Illuminate\Http\Request;
use App\Models\Societe; // Assurez-vous d'importer le modèle Societe
use App\Models\File; // Assurez-vous d'importer le modèle File

class DossierController extends Controller
{
  
    
    public function show($id)
    {
        $dossiers = Dossier::where('societe_id', $id)
        ->get();
    
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
        $Dossier_permanantCount = File::where('societe_id', $societe->id)->where('type', 'Dossier_permanant')->count();
        // Passe les variables à la vue
        return view('exercices', compact(
            'societe',
            'achatCount',
            'venteCount',
            'banqueCount',
            'caisseCount',
            'impotCount',
            'paieCount',
            'dossiers',
            'Dossier_permanantCount'
        ));
    }
    public function store(Request $request)
    {
        // Valider les données envoyées par le formulaire
        $request->validate([
            'name' => 'required|string|max:255',
            'societe_id' => 'required|exists:societe,id',
        ]);

        // Créer un nouveau dossier dans la base de données
        Dossier::create([
            'name' => $request->name,
            'societe_id' => $request->societe_id,
        ]);

        // Retourner une réponse de succès (redirection ou message)
        return redirect()->back()->with('success', 'Dossier créé avec succès');
    }
}
