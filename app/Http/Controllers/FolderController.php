<?php
namespace App\Http\Controllers;


use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;  // Ajouter cette ligne pour importer DB
use App\Models\Folder;
use App\Models\societe;

class FolderController extends Controller
{


    public function show($id)
    {
        // Récupérer le dossier en question
        $folder = Folder::find($id);
        
        // Vérifier si le dossier existe
        if ($folder) {
            // Définir l'ID du dossier dans la session
            session(['foldersId' => $folder->id]);
    
            // Retourner la vue avec les données du dossier
            return view('folders', compact('folder'));
        } else {
            return redirect()->route('folder.index')->withErrors('Dossier non trouvé.');
        }
    }
    



    // Afficher tous les dossiers
    public function index()
    {
        $societe = optional(auth()->user()->societe);
        
        // Vérifier si la société existe avant de récupérer son id
        if (!$societe) {
            // Gérer le cas où la société est absente
            return redirect()->back()->with('error', 'Aucune société associée à cet utilisateur.');
        }
    
        $folders = Folder::where('societe_id', $societe->id)->get();
        $societes = Societe::all(); // Pour afficher les sociétés dans le formulaire
    
        return view('achat', compact('folders', 'societes'));
    }
    

    // Créer un dossier
 
    
    // Dans votre méthode create()
    public function create(Request $request)
    {
        // Validation personnalisée
        $validator = Validator::make($request->all(), [
            'folder_name' => 'required|string|max:255',
            'societe_id' => [
                'required',
                function ($attribute, $value, $fail) {
                    // Utilisation de DB::connection pour exécuter la requête sur la base de données 'supcompta'
                    $exists = DB::connection('supcompta')->table('societe')->where('id', $value)->exists();

                    if (!$exists) {
                        $fail('La société avec cet ID n\'existe pas dans la base supcompta.');
                    }
                },
            ],
        ]);

        // Si la validation échoue, rediriger avec les erreurs
        if ($validator->fails()) {
            return redirect()->back()->withErrors($validator)->withInput();
        }

        // Si la validation réussit, créer le dossier
        Folder::create([
            'name' => $request->folder_name,
            'societe_id' => $request->societe_id,
        ]);

        // Rediriger avec un message de succès
        return redirect()->route('achat.view')->with('success', 'Dossier créé avec succès');
    }

   // app/Http/Controllers/FolderController.php

   public function destroy($id)
   {
       // Trouver le dossier
       $folder = Folder::findOrFail($id);
   
       // Supprimer tous les fichiers associés au dossier (en utilisant une requête SQL brute sur la base de données 'supcompta')
       \DB::connection('supcompta')->table('files')->where('id', $folder->id)->delete();
   
       // Supprimer le dossier
       $folder->delete();
   
       // Retourner une réponse de succès
       return redirect()->back()->with('success', 'Dossier et fichiers supprimés avec succès.');
   }
   
   

}
