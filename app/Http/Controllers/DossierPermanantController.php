<?php

namespace App\Http\Controllers;

namespace App\Http\Controllers;
use App\Models\Folder;
use App\Models\Message;

use App\Models\File; // Assurez-vous d'importer le modèle File
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
 
class DossierPermanantController extends Controller
{
    public function index()
    {
        $societeId = session('societeId'); // Récupère l'ID de la société depuis la session
        $folders = Folder::where('societe_id', $societeId) 
        ->whereNull('folder_id') 
        ->where('type_folder', 'Dossier_permanant')
        ->get();

        if ($societeId) {
            // Filtrer les fichiers de type 'vente' pour la société donnée
            $files = File::where('societe_id', $societeId)
                         ->where('type', 'Dossier_permanant') // Modifié pour 'vente' au lieu de 'achat'
                         ->get();

            return view('Dossier_permanant', compact('files', 'folders')); // Passez les fichiers à la vue
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
