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


class AchatController extends Controller
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
    

    public function index()
    {
        
        $societeId = session('societeId');
    
        if ($societeId) {
            $achatFiles = File::where('societe_id', $societeId)
                              ->where('type', 'achat') 
                              ->where('folders', 0) 
                              ->get();
            
            $folders = Folder::where('societe_id', $societeId) 
            ->whereNull('folder_id') 
            ->where('type_folder', 'achat')
            ->get();
            $notifications = [];
            foreach ($achatFiles as $file) {
                $extension = strtolower(pathinfo($file->name, PATHINFO_EXTENSION));
    
                // Déterminer l'aperçu en fonction du type de fichier
                if (in_array($extension, ['jpg', 'jpeg', 'png', 'gif'])) {
                    // Si c'est une image, l'aperçu sera l'image elle-même
                    $file->preview = asset('storage/' . $file->path);
                } elseif (in_array($extension, ['pdf'])) {
                    // Si c'est un PDF, afficher une image d'aperçu générique
                    $file->preview = 'https://via.placeholder.com/80x100.png?text=PDF';
                } elseif (in_array($extension, ['doc', 'docx'])) { 
                    // Si c'est un fichier Word, afficher une image d'aperçu générique
                    $file->preview = 'https://via.placeholder.com/80x100.png?text=Word';
                } elseif (in_array($extension, ['xls', 'xlsx'])) {
                    // Si c'est un fichier Excel, afficher une image d'aperçu générique
                    $file->preview = 'https://via.placeholder.com/80x100.png?text=Excel';
                } else {
                    // Pour tous les autres fichiers, une image d'aperçu générique
                    $file->preview = 'https://via.placeholder.com/80x100.png?text=Fichier';
                }
                $unreadMessages = Message::where('file_id', $file->id)
                ->where('is_read', 0)
                ->get();

                // Si des messages non lus existent pour ce fichier, les ajouter aux notifications
                if ($unreadMessages->count() > 0) {
                $notifications[$file->id] = $unreadMessages->count(); // Stocker le nombre de messages non lus avec l'ID du fichier
                }
            }
    
         
    
            // Si des fichiers sont trouvés, passe les fichiers et les dossiers à la vue
            return view('achat', compact('achatFiles', 'folders', 'notifications'));
        } else {
            // Si l'ID de la société n'est pas trouvé dans la session, redirige vers la page d'accueil
            return redirect()->route('home')->with('error', 'Aucune société trouvée dans la session');
        }
    }  
    
    

    public function viewFile($fileId)
{
    
    // Récupérer le fichier de type "Achat" à partir de la base de données
    $file = File::findOrFail($fileId);
    // dd($file);
    // Afficher une vue avec les détails du fichier
    return view('achat.view', compact('file'));
}



// public function viewFile($fileId,$folderId)
// {
//     // Récupérer le fichier de type "Achat" à partir de la base de données
//     $file = File::findOrFail($fileId);
//     // dd($file);
//     // Afficher une vue avec les détails du fichier
//     return view('achat.view', compact('file', 'folderId'));
// }






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
