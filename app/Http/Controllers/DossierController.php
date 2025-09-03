<?php

namespace App\Http\Controllers;

use App\Models\Dossier;
use Illuminate\Http\Request;
use App\Models\Societe; // Assurez-vous d'importer le modèle Societe
use App\Models\File; // Assurez-vous d'importer le modèle File
use App\Models\Folder;
use App\Models\SoldeMensuel;
class DossierController extends Controller
{

    public function update(Request $request, $id)
    {
        // dd($request);
         $dossier = Dossier::findOrFail($id);
        $dossier->name = $request->input('name');
         $dossier->is_read = 0;
        $dossier->save();
    
        return redirect()->route('exercices.show', ['societe_id' => $request->societe_id])->with('success', 'Dossier créé avec succès');
    }
    
 


    public function destroy($id)
    {
        // Trouver le dossier par son ID et le supprimer
        $dossier = Dossier::findOrFail($id);
        $dossier->is_read = 0;
    $dossier->save();
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
                     ->pluck('type');
    // Comptage des fichiers par type
    $fileCounts = [];
    foreach ($fileTypes as $type) {
        $fileCounts[$type] = File::where('societe_id', $societe->id)
                                 ->where('type', $type)
                                 ->count();
    }
    // Comptage des fichiers par dossier
    $dossierFileCounts = [];
    foreach ($dossiers as $dossier) {
        $dossierFileCounts[$dossier->id] = File::where('societe_id', $societe->id)
                                               ->where('type', $dossier->name)
                                               ->count();
    }
$closedCount = SoldeMensuel::where('societe_id', $societe->id)
                           ->where('cloturer', 1)
                           ->count();
    // Passe les variables à la vue
    return view('exercices', compact(
        'societe',
        'dossiers',
        'fileCounts',
        'dossierFileCounts',
        'closedCount' // :flèche_bas_petite: on ajoute cette variable à la vue
    ));
}
    
 public function store(Request $request)
{
    // dd($request->all());
    // Valider les données envoyées par le formulaire
    $request->validate([
        'name' => 'required|string|max:255',
        'societe_id' => 'required|exists:societe,id', // correction "societes"
        'color' => 'nullable|string|max:7',
        'exercice_debut' => 'required|date',
        'exercice_fin' => 'required|date',
    ]);
// dd(auth()->id()); // temporairement pour déboguer

    // Créer un nouveau dossier avec l'utilisateur connecté comme "updated_by"
    Dossier::create([
        'name' => $request->name,
        'societe_id' => $request->societe_id,
        'color' => $request->color,
        'exercice_debut' => $request->exercice_debut,
        'exercice_fin' => $request->exercice_fin,
        'updated_by' => auth()->id(), // ici on ajoute l'ID de l'utilisateur connecté
    ]);

    return redirect()->back()->with('success', 'Dossier créé avec succès');
}


}
