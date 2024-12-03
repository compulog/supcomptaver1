<?php

namespace App\Http\Controllers;

use App\Models\File;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\StreamedResponse;
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
        $societeId = session('societeId'); // Récupère l'ID de la société depuis la session
    
        if ($societeId) {
            // Récupère les fichiers de type 'achat' où le champ 'folders' est égal à 0
            $achatFiles = File::where('societe_id', $societeId)
                              ->where('type', 'achat') // Filtrer par type 'achat'
                              ->where('folders', 0) // Filtrer où le champ 'folders' est égal à 0
                              ->get();
            
            // Récupère les dossiers pour la société donnée
            $folders = Folder::where('societe_id', $societeId) // Assurez-vous que "Folder" est le bon modèle
                             ->get();
    
            // Ajouter un champ 'preview' pour chaque fichier afin de passer l'aperçu au front-end
            foreach ($achatFiles as $file) {
                // Détecte l'extension du fichier
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
            }
    
            // Vérifie si la collection de fichiers est vide après le filtre
            if ($achatFiles->isEmpty()) {
                // Retourne les fichiers d'achats si aucun fichier n'est trouvé avec 'folders' = 0
                return view('achat', compact('achatFiles'))->with('message', 'Aucun fichier trouvé avec folders = 0. Voici les fichiers d\'achat.');
            }
    
            // Si des fichiers sont trouvés, passe les fichiers et les dossiers à la vue
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
