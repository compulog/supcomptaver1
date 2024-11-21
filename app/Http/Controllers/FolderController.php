<?php
namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\File;

class FolderController extends Controller
{
    public function create(Request $request)
    {
        // Valider la demande pour le nom du dossier
        $request->validate([
            'folder_name' => 'required|string|max:255',
        ]);

        // Le nom du dossier à créer
        $folderName = $request->input('folder_name');

        // Spécifiez le chemin où les dossiers doivent être créés (par exemple, dans le dossier 'public' ou un autre répertoire)
        $path = public_path('files/achats/' . $folderName);

        // Vérifiez si le dossier existe déjà
        if (File::exists($path)) {
            return back()->with('error', 'Le dossier existe déjà.');
        }

        // Créez le dossier
        File::makeDirectory($path, 0777, true);

        // Retour à la page avec un message de succès
        return back()->with('success', 'Le dossier a été créé avec succès.');
    }
    // Exemple de méthode pour lister les fichiers d'un dossier
public function listFiles($folderName)
{
    $path = public_path('files/achats/' . $folderName);
    
    // Vérifie si le dossier existe
    if (File::exists($path)) {
        // Récupère tous les fichiers dans le dossier
        $files = File::files($path);
        
        return view('files.index', compact('files'));
    }

    return back()->with('error', 'Le dossier n\'existe pas.');
}

}
