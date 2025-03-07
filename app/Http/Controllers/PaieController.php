<?php

namespace App\Http\Controllers;
use App\Models\Folder;
use App\Models\Message;
use App\Models\File; // Assurez-vous d'importer le modèle File
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
class PaieController extends Controller
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
        $societeId = session('societeId'); // Récupère l'ID de la société depuis la session
        $folders = Folder::where('societe_id', $societeId)
                         ->whereNull('folder_id')
                         ->where('type_folder', 'paie')
                         ->get();
    
        if ($societeId) {
            // Initialiser la requête pour les fichiers de type 'paie'
            $query = File::where('societe_id', $societeId)
                         ->where('type', 'paie');
    
            // Filtrage et tri des fichiers en fonction des paramètres de la requête
            if ($request->has('filter_by')) {
                $filterBy = $request->get('filter_by');
                if ($filterBy == 'name') {
                    $query->orderBy('name', $request->get('order_by', 'asc')); // Tri par nom
                } elseif ($filterBy == 'date') {
                    $query->orderBy('created_at', $request->get('order_by', 'asc')); // Tri par date
                }
            } else {
                $query->orderBy('created_at', 'asc'); // Par défaut, trier par date ascendante
            }
    
            // Récupérer les fichiers filtrés et triés
            $files = $query->get();
    
            // Traitement des notifications et des aperçus de fichiers
            $notifications = [];
            foreach ($files as $file) {
                $extension = strtolower(pathinfo($file->name, PATHINFO_EXTENSION));
    
                // Déterminer l'aperçu en fonction du type de fichier
                if (in_array($extension, ['jpg', 'jpeg', 'png', 'gif'])) {
                    $file->preview = asset('storage/uploads/' . $file->name);

                } elseif (in_array($extension, ['pdf'])) {
                    $file->preview = 'https://via.placeholder.com/80x100.png?text=PDF'; // PDF
                } elseif (in_array($extension, ['doc', 'docx'])) {
                    $file->preview = 'https://via.placeholder.com/80x100.png?text=Word'; // Word
                } elseif (in_array($extension, ['xls', 'xlsx'])) {
                    $file->preview = 'https://via.placeholder.com/80x100.png?text=Excel'; // Excel
                } else {
                    $file->preview = 'https://via.placeholder.com/80x100.png?text=Fichier'; // Fichier générique
                }
                // Récupérer les messages non lus associés à chaque fichier
                $unreadMessages = Message::where('file_id', $file->id)
                                         ->where('is_read', 0)
                                         ->get();
    
                // Ajouter le nombre de messages non lus aux notifications
                if ($unreadMessages->count() > 0) {
                    $notifications[$file->id] = $unreadMessages->count();
                }
            }
    
            // Retourner la vue avec les fichiers, les dossiers et les notifications
            return view('paie', compact('files', 'folders', 'notifications'));
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
