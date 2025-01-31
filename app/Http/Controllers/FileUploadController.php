<?php
namespace App\Http\Controllers;

use App\Models\File;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class FileUploadController extends Controller
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
    // public function show($id)
    // {
       
    //     // Récupérer le nombre de fichiers par type
    //     $achatCount = File::where('type', 'Achat')->count();
    //     $venteCount = File::where('type', 'Vente')->count();
    //     $banqueCount = File::where('type', 'Banque')->count();
    //     $caisseCount = File::where('type', 'Caisse')->count();
    //     $impotCount = File::where('type', 'Impot')->count();
    //     $paieCount = File::where('type', 'Paie')->count();
 
    //     // Retourner la vue avec les données
    //     return view('exercices', compact('achatCount', 'venteCount', 'banqueCount', 'caisseCount', 'impotCount', 'paieCount'));
    // }
    
    public function upload(Request $request)
    {
        // Validation des fichiers uploadés
        $request->validate([
            'file' => 'required|file|mimes:jpg,png,pdf,docx,xlsx,doc', // Types de fichiers acceptés
            'type' => 'required|string', // Le type (Achat, Vente, etc.)
            'folders' => 'nullable|string', 
            'societe_id' => 'required|integer', // Validation pour societe_id
            
        ]);
    
        // Vérifier si un fichier a été téléchargé
        if ($request->hasFile('file')) {
            $file = $request->file('file');
    
            // Créer un nom unique pour le fichier
            $filename = time() . '-' . $file->getClientOriginalName();
    
            // Sauvegarder le fichier dans le stockage public
            $path = $file->storeAs('uploads', $filename, 'public'); // Enregistre le fichier sur disque
    
            // Sauvegarder les informations du fichier dans la base de données
            $fileRecord = new File();
            $fileRecord->name = $filename;  // Nom du fichier
            $fileRecord->path = $path;  // Sauvegarde du chemin d'accès (assurez-vous que le chemin est relatif au dossier 'storage/app/public')
            $fileRecord->type = $request->input('type');  // Type du fichier (Achat, Vente, etc.)
            $fileRecord->societe_id = $request->input('societe_id');  // ID de la société
            $fileRecord->folders = $request->input('folders_id');  // ID de la société
            $fileRecord->save();  // Sauvegarde dans la base de données
    
            return back()->with('success', 'Fichier téléchargé avec succès!');
        } else {
            return back()->withErrors(['file' => 'Aucun fichier téléchargé.']);
        }
    }
    
    
    
}
