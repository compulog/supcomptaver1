<?php

namespace App\Http\Controllers;
use App\Models\Message;

use App\Models\File;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use App\Models\Folder;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
 

class DasousdossierController extends Controller
{

    public function upload(Request $request)
    {
        // dd($request);

        // Validation des fichiers uploadés
        $request->validate([
            'file' => 'required|file|mimes:jpg,png,pdf,docx,xlsx,doc', // Types de fichiers acceptés
            'type' => 'nullable|string', // Le type (Achat, Vente, etc.)
            'folders' => 'nullable|string', // Le type (Achat, Vente, etc.)
            'societe_id' => 'required|integer', // Validation pour societe_id
            
        ]);
    
        // Vérifier si un fichier a été téléchargé
        if ($request->hasFile('file')) {
            $file = $request->file('file');
    
            // Créer un nom unique pour le fichier
            $filename = time() . '-' . $file->getClientOriginalName();
    
            // Sauvegarder le fichier dans le stockage public
            $path = $file->storeAs('uploads', $filename, 'public'); // Enregistre le fichier sur disque
    
            // Sauvegarder les informations du fichier dans la base de données
            $fileRecord = new File();
            $fileRecord->name = $filename;  // Nom du fichier
            $fileRecord->path = $path;  // Sauvegarde du chemin d'accès (assurez-vous que le chemin est relatif au dossier 'storage/app/public')
            $fileRecord->type = 'Null';  // Type du fichier (Achat, Vente, etc.)
            $fileRecord->societe_id = $request->input('societe_id');  // ID de la société
            $fileRecord->folders = $request->input('folders_id');  // ID de la société
            $fileRecord->save();  // Sauvegarde dans la base de données
    
            return back()->with('success', 'Fichier téléchargé avec succès!');
        } else {
            return back()->withErrors(['file' => 'Aucun fichier téléchargé.']);
        }
    }



//     public function download($fileId)
// {
//     // Récupérer le fichier depuis la base de données
//     $file = File::findOrFail($fileId);

//     // Vérifier si le fichier existe
//     $filePath = storage_path('app/public/' . $file->path); // Utiliser le chemin du fichier stocké

//     if (!file_exists($filePath)) {
//         return back()->withErrors(['file' => 'Le fichier n\'existe pas.']);
//     }

//     // Retourner le fichier en téléchargement
//     return response()->download($filePath, $file->name);
// }



    public function create(Request $request)
    {
        // dd($request);
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
            'type_folder' => 'Null',

        ]); 
    
        // Si folders_id est fourni, on redirige vers la route associée à ce folder_id
        if ($request->has('folders_id') && $request->folders_id) {
            return redirect()->route('dasousdossier.show', ['folderId' => $request->folders_id])->with('success', 'Dossier créé avec succès');
        }
    
        // Sinon, on retourne vers une vue (par exemple folder.create)
        return redirect()->route('exercices.show', ['societe_id' => $request->societe_id])->with('success', 'Dossier créé avec succès');
    }
    
 
    public function showSousDossier($id)
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
            return view('Douvrirsous', compact('achatFiles', 'folders', 'foldersId', 'folder', 'notifications')); 
        } else {
            // Rediriger si aucune société n'est trouvée dans la session
            return redirect()->route('home')->with('error', 'Aucune société trouvée dans la session');
        }
    }


     



}
