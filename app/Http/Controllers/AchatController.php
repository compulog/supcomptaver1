<?php

namespace App\Http\Controllers;

use App\Models\File;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\StreamedResponse;
use App\Models\Folder;


class AchatController extends Controller
{
    public function index()
    {
        $societeId = session('societeId'); // Récupère l'ID de la société depuis la session
        
        if ($societeId) {
            // Récupère les fichiers de type 'achat'
            $achatFiles = File::where('societe_id', $societeId)
                              ->where('type', 'achat') // Filtrer par type 'achat'
                              ->get();
    
            // Récupère les dossiers pour la société donnée
            $folders = Folder::where('societe_id', $societeId) // Assurez-vous que "Folder" est le bon modèle
                             ->get();
            
            // Vérifie si la collection de dossiers est vide
            if ($folders->isEmpty()) {
                // Retourne les fichiers d'achats si les dossiers sont vides
                return view('achat', compact('achatFiles'))->with('message', 'Aucun dossier trouvé. Voici les fichiers d\'achat.');
            }
    
            // Si des dossiers sont trouvés, passe les dossiers à la vue
            return view('achat', compact('achatFiles', 'folders'));
            
        } else {
            // Si l'ID de la société n'est pas trouvé dans la session, redirige vers la page d'accueil
            return redirect()->route('home')->with('error', 'Aucune société trouvée dans la session');
        }
    }
    

    

    public function download($fileId)
{
    // Récupérer le fichier depuis la base de données
    $file = File::findOrFail($fileId);

    // Vérifier si le fichier existe
    $filePath = storage_path('app/public/' . $file->path); // Utiliser le chemin du fichier stocké

    if (!file_exists($filePath)) {
        return back()->withErrors(['file' => 'Le fichier n\'existe pas.']);
    }

    // Retourner le fichier en téléchargement
    return response()->download($filePath, $file->name);
}

}
