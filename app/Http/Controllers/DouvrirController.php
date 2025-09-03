<?php

namespace App\Http\Controllers;
use Illuminate\Support\Facades\DB;
use App\Models\Message;

use Illuminate\Http\Request;
use App\Models\Dossier;
use App\Models\File;
use App\Models\Folder;
use Illuminate\Support\Facades\Validator;

class DouvrirController extends Controller
{
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
            return redirect()->route('Douvrir', ['id' => $request->folders_id])->with('success', 'Dossier créé avec succès');
        }
    
        // Sinon, on retourne vers une vue (par exemple folder.create)
        return redirect()->route('Douvrir', ['id' => $request->dossier_id])->with('success', 'Dossier créé avec succès');


    }
        
    public function show($id, Request $request)
    {
        // Trouver le dossier par son ID
        $dossier = Dossier::findOrFail($id); // Si le dossier n'existe pas, il retournera une erreur 404
    
        // Récupérer les fichiers ayant le même type que le dossier
        $query = File::where('type', $dossier->name)
        ->where(function($q) {
            $q->whereNull('folders')
              ->orWhere('folders', 0);
        });

        // Appliquer un filtrage ou un tri si des paramètres sont fournis dans la requête
        if ($request->has('filter_by')) {
            $filterBy = $request->get('filter_by');
            if ($filterBy == 'name') {
                $query->orderBy('name', $request->get('order_by', 'asc')); // Tri par nom
            } elseif ($filterBy == 'date') {
                $query->orderBy('created_at', $request->get('order_by', 'asc')); // Tri par date
            }
        } else {
            $query->orderBy('created_at', 'asc'); // Par défaut, trier par date ascendante
        }
    
        // Récupérer les fichiers filtrés et triés
        $files = $query->get();
    
        // Récupérer les dossiers associés au type du dossier
        $folders = Folder::where('type_folder', $dossier->name)->get();
    
        // Notifications pour les fichiers (messages non lus)
        $notifications = [];
        foreach ($files as $file) {
            $extension = strtolower(pathinfo($file->name, PATHINFO_EXTENSION));
    
   

            if (in_array($extension, ['jpg', 'jpeg', 'png', 'gif'])) {
                $file->preview = asset($file->path);

            } elseif (in_array($extension, ['pdf'])) {
                $file->preview = 'https://via.placeholder.com/80x100.png?text=PDF'; // PDF
            } elseif (in_array($extension, ['doc', 'docx'])) {
                $file->preview = 'https://via.placeholder.com/80x100.png?text=Word'; // Word
            } elseif (in_array($extension, ['xls', 'xlsx'])) {
                $file->preview = 'https://via.placeholder.com/80x100.png?text=Excel'; // Excel
            } else {
                $file->preview = 'https://via.placeholder.com/80x100.png?text=Fichier'; // Fichier générique
            }

            // Récupérer les messages non lus associés à ce fichier
            $unreadMessages = Message::where('file_id', $file->id)
                                     ->where('is_read', 0)
                                     ->get();
    
            // Ajouter le nombre de messages non lus aux notifications
            if ($unreadMessages->count() > 0) {
                $notifications[$file->id] = $unreadMessages->count();
            }
        }
    
        // Passer le dossier, les fichiers, les dossiers et les notifications à la vue
        return view('Douvrir', compact('dossier', 'files', 'folders', 'notifications'));
    }
    
    public function uploadFile(Request $request)
    {
    //    dd($request);
        // Validation des fichiers uploadés
        $request->validate([
            'file' => 'required|file|mimes:jpg,png,pdf,docx,xlsx,doc', // Types de fichiers acceptés
            'folder_type' => 'required|string', // Le type (Achat, Vente, etc.)
            'societe_id' => 'required|integer', // Validation pour societe_id
            'exercice_debut' => 'nullable|date',
            'exercice_fin' => 'nullable|date',
        ]);
        
        $dbName = session('database'); // Assurez-vous que 'database' est bien défini dans la session
// dd($request->folders);
 // Vérifier si un fichier a été téléchargé
 if ($request->hasFile('file')) {
    $file = $request->file('file');
    $originalFilename = $file->getClientOriginalName();

    // Vérifier si le fichier existe déjà dans la base de données
    $existingFile = File::where('name', $originalFilename)
                         ->where('societe_id', $request->input('societe_id'))
                         ->first();

    if ($existingFile) {
        // Si le fichier existe, retourner une vue avec un message d'alerte
        // return back()->with('alert', 'Un fichier avec ce nom existe déjà. Voulez-vous l\'écraser ?')->withInput();
        return back()->with('alert', 'Un fichier avec ce nom existe déjà.')->withInput();

    }
        
            // Sauvegarder les informations du fichier dans la base de données
            $fileRecord = new File();
            $fileRecord->name = $originalFilename;  // Nom original du fichier
            $fileRecord->type = $request->input('folder_type');  // Type du fichier (Achat, Vente, etc.)
            $fileRecord->societe_id = $request->input('societe_id');  // ID de la société
            $fileRecord->exercice_debut = $request->input('exercice_debut');
            $fileRecord->exercice_fin = $request->input('exercice_fin');
            // Après avoir déplacé le fichier
            $fileRecord->path = 'storage/uploads/' . $dbName . '/' . $fileRecord->id . '_' . $originalFilename; // Assurez-vous que cette ligne est exécutée
 
        $fileRecord->updated_by = auth()->id();    
        $fileRecord->save();  // Sauvegarde dans la base de données
    
            // Récupérer l'ID du fichier enregistré
            $fileId = $fileRecord->id;
    
            // Créer un nom de fichier avec l'ID
            $filename = $fileId . '_' . $originalFilename;
    
     
            // Vérifiez si le répertoire de la base de données existe et créez-le si nécessaire
            $dbPath = public_path('storage/uploads/' . $dbName);
            if (!file_exists($dbPath)) {
                mkdir($dbPath, 0777, true);
            }
    
            // Déplacer le fichier téléchargé dans le dossier de la base de données
            $file->move($dbPath, $filename);
    
            // Mettre à jour le chemin du fichier dans la base de données
            $fileRecord->path = 'storage/uploads/' . $dbName . '/' . $filename;  // Le chemin est relatif à 'public'
            $fileRecord->save();  // Sauvegarde mise à jour dans la base de données
    
            return back()->with('success', 'Fichier téléchargé avec succès. ID du fichier : ' . $fileId);
            // return back();

        } else {
            return back()->withErrors(['file' => 'Aucun fichier téléchargé.']);
        }
    }

}
