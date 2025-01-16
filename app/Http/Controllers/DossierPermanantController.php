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
                         $notifications = [];
                         foreach ($files as $file) {
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
            return view('Dossier_permanant', compact('files', 'folders', 'notifications')); // Passez les fichiers à la vue
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
