<?php


namespace App\Http\Controllers;

use App\Models\File;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class FileUploadController extends Controller
{
    public function upload(Request $request)
    {
        // Validation des fichiers uploadés
        $request->validate([
            'file' => 'required|file|mimes:jpg,png,pdf,docx,xlsx', // Types de fichiers acceptés
            'type' => 'required|string', // Le type (Achat, Vente, etc.)
        ]);

        // Obtenez le fichier téléchargé
        $file = $request->file('file');

        // Créer un nom unique pour le fichier
        $filename = time() . '-' . $file->getClientOriginalName();

        // Sauvegarder le fichier dans le dossier 'uploads' de stockage local
        $filePath = $file->storeAs('uploads', $filename);

        // Sauvegarder les informations du fichier dans la base de données
        $fileRecord = new File();
        $fileRecord->name = $filename;
        $fileRecord->path = $filePath;
        $fileRecord->type = $request->input('type');  // Assigner la valeur du type
        $fileRecord->save();  // Sauvegarder dans la base de données

        // Retourner un message de succès à l'utilisateur
        return back()->with('success', 'Fichier téléchargé avec succès!');
    }
}
