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
    

    public function index(Request $request)
    {
        $societeId = session('societeId');
        
        if ($societeId) {
            // Filtrage et tri des fichiers de type 'achat'
            $query = File::where('societe_id', $societeId)
                         ->where('type', 'achat')
                         ->where('folders', 0);
    
            // Appliquer le filtre par nom ou date
            if ($request->has('filter_by')) {
                $filterBy = $request->get('filter_by');
                if ($filterBy == 'name') {
                    $query->orderBy('name', $request->get('order_by', 'asc'));  // Tri par nom
                } elseif ($filterBy == 'date') {
                    $query->orderBy('created_at', $request->get('order_by', 'asc'));  // Tri par date
                }
            } else {
                $query->orderBy('created_at', 'asc');  // Par défaut, trier par date ascendante
            }
    
            $achatFiles = $query->get();
    
            // Récupérer les dossiers de la même manière
            $folders = Folder::where('societe_id', $societeId)
                             ->whereNull('folder_id')
                             ->where('type_folder', 'achat');
    
            // Appliquer le filtre par nom ou date pour les dossiers
            if ($request->has('filter_by')) {
                $filterBy = $request->get('filter_by');
                if ($filterBy == 'name') {
                    $folders->orderBy('name', $request->get('order_by', 'asc'));  // Tri par nom
                } elseif ($filterBy == 'date') {
                    $folders->orderBy('created_at', $request->get('order_by', 'asc'));  // Tri par date
                }
            } else {
                $folders->orderBy('created_at', 'asc');  // Par défaut, trier par date ascendante
            }
    
            $folders = $folders->get();
    
            // Notification des messages non lus
            $notifications = [];
            foreach ($achatFiles as $file) {
                $unreadMessages = Message::where('file_id', $file->id)
                                         ->where('is_read', 0)
                                         ->get();
    
                if ($unreadMessages->count() > 0) {
                    $notifications[$file->id] = $unreadMessages->count();
                }
            }
    
            return view('achat', compact('achatFiles', 'folders', 'notifications'));
        } else {
            return redirect()->route('home')->with('error', 'Aucune société trouvée dans la session');
        }
    }
    
    

    public function viewFile($fileId)
    {
        // Récupérer le fichier recherché par son ID
        $file = File::findOrFail($fileId);
    
        
        $files = File::where('folders', $file->folders)->get();
    
        // Trouver l'index du fichier recherché pour la navigation
        $currentFileIndex = $files->search(fn($f) => $f->id == $fileId);
    
        // Passer le fichier recherché, tous les fichiers, et l'index à la vue
        return view('achat.view', compact('file', 'files', 'currentFileIndex'));
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
