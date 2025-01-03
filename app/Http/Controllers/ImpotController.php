<?php

namespace App\Http\Controllers;
use App\Models\Message;
use App\Models\Folder;
use App\Models\File; // Assurez-vous d'importer le modèle File
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
class ImpotController extends Controller
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
        $societeId = session('societeId'); // Récupère l'ID de la société depuis la session
        $folders = Folder::where('societe_id', $societeId) 
        ->whereNull('folder_id') 
        ->where('type_folder', 'impot')
        ->get();

        if ($societeId) {
            // Filtrer les fichiers de type 'vente' pour la société donnée
            $files = File::where('societe_id', $societeId)
                         ->where('type', 'impot') // Modifié pour 'vente' au lieu de 'achat'
                         ->get();

            return view('impot', compact('files', 'folders')); // Passez les fichiers à la vue
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
