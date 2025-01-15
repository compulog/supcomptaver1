<?php

namespace App\Http\Controllers;
use Illuminate\Support\Facades\DB;

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
        
    public function show($id)
    {
        // Trouver le dossier par son ID
        $dossier = Dossier::findOrFail($id); // Si le dossier n'existe pas, il retournera une erreur 404
        // dd($dossier);
        // Récupérer les fichiers ayant le même type que le dossier
        $files = File::where('type', $dossier->name)->get(); // Récupère les fichiers dont le type correspond au type du dossier
        // dd($files);
        $folders = Folder::where('type_folder', $dossier->name)->get(); // Récupère les fichiers dont le type correspond au type du dossier

        // Passer le dossier et les fichiers à la vue
        return view('Douvrir', compact('dossier', 'files', 'folders'));
    }
    public function uploadFile(Request $request)
    {
        $request->validate([
            'file' => 'required|file|mimes:jpg,png,pdf,docx,xlsx,doc', // Types de fichiers acceptés
            'societe_id' => 'required|integer', // Validation pour societe_id
            'folder_type' => 'nullable|string', // Le type de dossier (optionnel)
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
            $fileRecord->societe_id = $request->input('societe_id');  // ID de la société
            $fileRecord->type = $request->input('folder_type');  // Type du dossier
            $fileRecord->save();  // Sauvegarde dans la base de données
    
            return back()->with('success', 'Fichier téléchargé avec succès!');
        } else {
            return back()->withErrors(['file' => 'Aucun fichier téléchargé.']);
        }
    }
}
