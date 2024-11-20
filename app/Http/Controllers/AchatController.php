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
        // Filtrer les dossiers pour la société donnée
        $folders = Folder::where('societe_id', $societeId) // Assurez-vous que "Folder" est le bon modèle
                         ->get();
    
        return view('achat', compact('folders')); // Passez les dossiers à la vue
    } else {
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
