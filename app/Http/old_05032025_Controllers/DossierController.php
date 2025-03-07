<?php

namespace App\Http\Controllers;

use App\Models\Dossier;
use Illuminate\Http\Request;
use App\Models\Societe; // Assurez-vous d'importer le modèle Societe
use App\Models\File; // Assurez-vous d'importer le modèle File
use App\Models\Folder;
class DossierController extends Controller
{

    public function update(Request $request, $id)
    {
        // dd($request);
         $dossier = Dossier::findOrFail($id);
        $dossier->name = $request->input('name');
        $dossier->save();
    
        return redirect()->route('exercices.show', ['societe_id' => $request->societe_id])->with('success', 'Dossier créé avec succès');
    }
    
 


    public function destroy($id)
    {
        // Trouver le dossier par son ID et le supprimer
        $dossier = Dossier::findOrFail($id);
        $dossier->delete();

        // Rediriger avec un message de succès
        return redirect()->back()->with('success', 'Dossier supprimé avec succès.');
    }
    
    public function show($id)
    {
        // Récupère tous les dossiers pour la société spécifiée
        $dossiers = Dossier::where('societe_id', $id)->get();
        
        // Récupère la société avec l'ID donné
        $societe = Societe::findOrFail($id);
        session()->put('societeId', $societe->id);
        
        // Récupère tous les types distincts de fichiers pour cette société
        $fileTypes = File::where('societe_id', $societe->id)
                         ->distinct()
                         ->pluck('type'); // On récupère juste les types distincts
        
        // Créer un tableau pour stocker les comptages des différents types
        $fileCounts = [];
        
        // Pour chaque type distinct de fichier, compter le nombre de fichiers de ce type
        foreach ($fileTypes as $type) {
            $fileCounts[$type] = File::where('societe_id', $societe->id)
                                     ->where('type', $type)
                                     ->count();
        }
        
        // Créer un tableau pour stocker le comptage des fichiers par dossier
        $dossierFileCounts = [];
        
        // Pour chaque dossier, compter le nombre de fichiers associés
        foreach ($dossiers as $dossier) {
            $dossierFileCounts[$dossier->id] = File::where('societe_id', $societe->id)
                                                   ->where('type', $dossier->name)
                                                   ->count();
        }
        
        // Passe les variables à la vue
        return view('exercices', compact(
            'societe',
            'dossiers',
            'fileCounts', // Passe le tableau des comptages des types
            'dossierFileCounts' // Passe le tableau des comptages des fichiers par dossier
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
