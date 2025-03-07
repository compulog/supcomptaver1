<?php
namespace App\Http\Controllers;

use App\Models\File;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\File as FacadeFile;
use Illuminate\Support\Facades\Response;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
class FileController extends Controller
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


    public function update(Request $request, $id)
    {
        // Validation de l'entrée
        $request->validate([
            'name' => 'required|string|max:255',
        ]);
    
        // Récupération du fichier de la base de données
        $file = File::findOrFail($id);
    
        // Le nom du fichier actuel dans le stockage
        $oldFileName = $file->name;
    
        // Nouveau nom du fichier
        $newFileName = $request->input('name');
    
        // Chemin du fichier dans le stockage
        $oldFilePath = public_path('storage/uploads/' . $oldFileName);
        $newFilePath = public_path('storage/uploads/' . $newFileName);
    
        // Vérification si le fichier existe avant de tenter de le renommer
        if (file_exists($oldFilePath)) {
            // Renommer le fichier
            rename($oldFilePath, $newFilePath);
        }
    
        // Mise à jour du nom dans la base de données
        $file->name = $newFileName;
        $file->save();
    
        // Retour à la page précédente
        return redirect()->back();
    }
    
    public function view($id)
    {
        // Récupérer le fichier de la base de données
        $file = File::findOrFail($id);
    
        // Le chemin du fichier à consulter
        $filePath = public_path('files/achats/' . $file->name);
    
        // Log du chemin pour vérifier si le fichier existe
        \Log::info("Chemin du fichier : " . $filePath);  // Affiche le chemin dans les logs
    
        // Vérifier si le fichier existe
        if (!FacadeFile::exists($filePath)) {
            \Log::error("Le fichier n'existe pas à ce chemin : " . $filePath); // Log l'erreur
            return abort(404, 'Fichier non trouvé');
        }
    
        // Détecter le type MIME du fichier
        $mimeType = mime_content_type($filePath);
        \Log::info("Type MIME du fichier : " . $mimeType);  // Log du type MIME pour vérification
    
        // Liste des types de fichiers à afficher dans le navigateur
        $viewableMimeTypes = ['image/jpeg', 'image/png', 'image/gif', 'application/pdf', 'text/plain', 'text/html'];
    
        // Vérifier si le fichier peut être affiché dans le navigateur
        if (in_array($mimeType, $viewableMimeTypes)) {
            return view('file.view', compact('file', 'filePath', 'mimeType'));
        } else {
            return response()->download($filePath);
        }
    }
    
    // FileController.php
public function destroy($id)
{
    $file = File::findOrFail($id);
    Storage::delete($file->path); // Si vous stockez les fichiers sur le système de fichiers
    $file->delete();

    return redirect()->back();
}
}
