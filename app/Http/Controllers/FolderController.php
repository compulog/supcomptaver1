<?php
namespace App\Http\Controllers;

use App\Models\File;
 
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;  // Ajouter cette ligne pour importer DB
use App\Models\Folder;
use App\Models\societe;
use Illuminate\Support\Facades\Auth;
class FolderController extends Controller
{

    public function __construct()
    {
        $this->middleware(function ($request, $next) {
            // Récupérer le nom de la base de données depuis la session.
            $dbName = session('database');
    
            if ($dbName) {
                // Définir la connexion à la base de données dynamiquement.
                config(['database.connections.supcompta.database' => $dbName]);
                DB::setDefaultConnection('supcompta');  // Configurer la connexion par défaut
            }
            return $next($request);
        });
    }

    public function index($id)
    {
        $societeId = session('societeId'); 
        
        if ($societeId) {

            $folders = Folder::where('societe_id', $societeId)
                             ->where('folder_id', $id)
                             ->get();   
            // 
            $achatFiles = File::where('societe_id', $societeId)
                              ->where('type', 'achat') 
                        ->where('folders', $id)  
                    //    ->where('folders', 0) 
                              ->get();
                      
                session(['foldersId' => $id]);  
    
            $foldersId = session('foldersId'); 
    
            foreach ($achatFiles as $file) {
                
                $extension = strtolower(pathinfo($file->name, PATHINFO_EXTENSION));
    
                if (in_array($extension, ['jpg', 'jpeg', 'png', 'gif'])) {
                    $file->preview = asset('storage/' . $file->path); // Image
                } elseif (in_array($extension, ['pdf'])) {
                    $file->preview = 'https://via.placeholder.com/80x100.png?text=PDF'; // PDF
                } elseif (in_array($extension, ['doc', 'docx'])) {
                    $file->preview = 'https://via.placeholder.com/80x100.png?text=Word'; // Word
                } elseif (in_array($extension, ['xls', 'xlsx'])) {
                    $file->preview = 'https://via.placeholder.com/80x100.png?text=Excel'; // Excel
                } else {
                    $file->preview = 'https://via.placeholder.com/80x100.png?text=Fichier'; // Fichier générique
                }
            }
    
           
    
             return view('folders', compact('achatFiles', 'folders', 'foldersId')); 
        } else {
             return redirect()->route('home')->with('error', 'Aucune société trouvée dans la session');
        }
    }
    
    
 
    // public function index($id)
    // {
    //     $societeId = session('societeId'); // Récupère l'ID de la société depuis la session
        
    //     if ($societeId) {
    //         // Récupère les fichiers de type 'achat' où le champ 'folders' est égal à $id
    //         $achatFiles = File::where('societe_id', $societeId)
    //                           ->where('type', 'achat') // Filtrer par type 'achat'
    //                           ->where('folders', $id) // Filtrer où le champ 'folders' est égal à $id
    //                           ->get();
                                  
    //         // Récupère les dossiers pour la société donnée et où le 'folder_id' est égal à $id
    //         $folders = Folder::where('societe_id', $societeId)
    //                          ->where('folder_id', $id)
    //                          ->get();
            
    //         // Stocke l'ID du dossier dans la session pour une utilisation future dans la vue
    //         session(['foldersId' => $id]);  // Cette ligne enregistre l'ID dans la session
    
    //         // Assurez-vous que $foldersId est bien défini ici
    //         $foldersId = session('foldersId'); // Récupère l'ID du dossier depuis la session
    
    //         // Ajouter un champ 'preview' pour chaque fichier afin de passer l'aperçu au front-end
    //         foreach ($achatFiles as $file) {
    //             // Détecte l'extension du fichier
    //             $extension = strtolower(pathinfo($file->name, PATHINFO_EXTENSION));
    
    //             // Déterminer l'aperçu en fonction du type de fichier
    //             if (in_array($extension, ['jpg', 'jpeg', 'png', 'gif'])) {
    //                 $file->preview = asset('storage/' . $file->path); // Image
    //             } elseif (in_array($extension, ['pdf'])) {
    //                 $file->preview = 'https://via.placeholder.com/80x100.png?text=PDF'; // PDF
    //             } elseif (in_array($extension, ['doc', 'docx'])) {
    //                 $file->preview = 'https://via.placeholder.com/80x100.png?text=Word'; // Word
    //             } elseif (in_array($extension, ['xls', 'xlsx'])) {
    //                 $file->preview = 'https://via.placeholder.com/80x100.png?text=Excel'; // Excel
    //             } else {
    //                 $file->preview = 'https://via.placeholder.com/80x100.png?text=Fichier'; // Fichier générique
    //             }
    //         }
    
    //         // Vérifie si la collection de fichiers est vide après le filtre
    //         if ($achatFiles->isEmpty()) {
    //             // Retourne les fichiers d'achats si aucun fichier n'est trouvé avec 'folders' = $id
    //             return view('folders', compact('achatFiles'))->with('message', 'Aucun fichier trouvé avec l\'ID du dossier. Voici les fichiers d\'achat.');
    //         }
    
    //         // Si des fichiers sont trouvés, passe les fichiers et les dossiers à la vue
    //         return view('folders', compact('achatFiles', 'folders', 'foldersId')); // Assurez-vous que $foldersId est bien passé
    //     } else {
    //         // Si l'ID de la société n'est pas trouvé dans la session, redirige vers la page d'accueil
    //         return redirect()->route('home')->with('error', 'Aucune société trouvée dans la session');
    //     }
    // }

    
    public function create(Request $request)
    {
        // Affiche la requête pour debug
        // dd($request);
    
        // Validation personnalisée
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255', // Correspond au champ 'name' du formulaire
            'societe_id' => [
                'required',
                function ($attribute, $value, $fail) {
                    // Vérifier si la société existe dans la base 'supcompta'
                    $exists = DB::connection('supcompta')->table('societe')->where('id', $value)->exists();
                    if (!$exists) {
                        $fail('La société avec cet ID n\'existe pas dans la base supcompta.');
                    }
                },
            ],
            'folders_id' => 'nullable|integer', // Correspond au champ 'folders_id' du formulaire
        ]);
    
        // Si la validation échoue, rediriger avec les erreurs
        if ($validator->fails()) {
            return redirect()->back()->withErrors($validator)->withInput();
        }
    
        // Créer le dossier avec les données validées
        Folder::create([
            'name' => $request->name,          // Champ 'name' du formulaire
            'societe_id' => $request->societe_id,  // Champ 'societe_id' du formulaire
            'folder_id' => $request->folders_id, // Champ 'folders_id' du formulaire
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

