<?php

namespace App\Http\Controllers;
use App\Models\Message;

use App\Models\File;
 
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;  
use App\Models\Folder;
use App\Models\societe;
use Illuminate\Support\Facades\Auth;
use PhpOffice\PhpWord\IOFactory as PhpWordIOFactory;
use PhpOffice\PhpSpreadsheet\IOFactory as PhpSpreadsheetIOFactory;
use PhpOffice\PhpSpreadsheet\Reader\Exception as PhpSpreadsheetException;

class FoldersDossierPermanant1Controller extends Controller
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
        // Récupérer le dossier avec l'ID stocké dans la session
        $folder = Folder::find($id);

        $societeId = session('societeId'); 

        if ($societeId) {
            // Récupérer les dossiers associés à la société
            $folders = Folder::where('societe_id', $societeId)
                             ->where('folder_id', $id)
                             ->get();   

            // Récupérer les fichiers de type "achat"
            $achatFiles = File::where('societe_id', $societeId)
                              ->where('type', 'Dossier_permanant') 
                              ->where('folders', $id)  
                              ->get();
            
            // Enregistrer l'ID du dossier dans la session
            session(['foldersId' => $id]);  

            // Récupérer l'ID du dossier de la session
            $foldersId = session('foldersId'); 

            // Liste des notifications pour les fichiers
            $notifications = [];

            foreach ($achatFiles as $file) {
                // Vérifier l'extension du fichier pour afficher une prévisualisation
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

                // Vérifier si un message existe pour ce fichier et si le champ 'is_read' est égal à 0
                $unreadMessages = Message::where('file_id', $file->id)
                                         ->where('is_read', 0)
                                         ->get();

                // Si des messages non lus existent pour ce fichier, les ajouter aux notifications
                if ($unreadMessages->count() > 0) {
                    $notifications[$file->id] = $unreadMessages->count(); // Stocker le nombre de messages non lus avec l'ID du fichier
                }
            }

            // Retourner la vue avec les fichiers, dossiers et notifications
            return view('foldersDossierPermanant1', compact('achatFiles', 'folders', 'foldersId', 'folder', 'notifications')); 
        } else {
            // Rediriger si aucune société n'est trouvée dans la session
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
    
        if ($validator->fails()) {
            return redirect()->back()->withErrors($validator)->withInput();
        }
    
        // Création du dossier
        Folder::create([
            'name' => $request->name,
            'societe_id' => $request->societe_id,
            'folder_id' => $request->folders_id,
            'type_folder' => $request->type_folder,

        ]);
    
        // Si folders_id est fourni, on redirige vers la route associée à ce folder_id
        if ($request->has('folders_id') && $request->folders_id) {
            return redirect()->route('foldersDossierPermanant1', ['id' => $request->folders_id])->with('success', 'Dossier créé avec succès');
        }
    
        // Sinon, on retourne vers une vue (par exemple folder.create)
        return redirect()->route('achat.view');
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
