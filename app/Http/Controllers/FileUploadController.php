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
    //     $venteCount = File::where('type', 'Vente')->();
    //     $banqueCount = File::where('type', 'Banque')->count();
    //     $caisseCount = File::where('type', 'Caisse')->count();
    //     $impotCount = File::where('type', 'Impot')->count();
    //     $paieCount = File::where('type', 'Paie')->count();
 
    //     // Retourner la vue avec les données
    //     return view('exercices', compact('achatCount', 'venteCount', 'banqueCount', 'caisseCount', 'impotCount', 'paieCount'));
    // }
    public function handleFileUpload(Request $request)
    {
        // Vérifiez si le fichier est présent dans la requête
        if ($request->hasFile('document')) {
            $file = $request->file('document');

            // Vous pouvez effectuer des opérations sur le fichier ici
            // Par exemple, le stocker dans un dossier spécifique
            $path = $file->store('uploads'); // Stocke le fichier dans le dossier 'uploads'

            // Retournez une réponse JSON avec le chemin du fichier ou d'autres données
            return response()->json(['success' => true, 'path' => $path]);
        }

        return response()->json(['error' => 'Aucun fichier trouvé'], 400);
    }
    public function upload(Request $request)
    {
        // dd($request);
        // Validation des fichiers uploadés
        $request->validate([
            'file' => 'required|file|mimes:jpg,png,pdf,docx,xlsx,doc', // Types de fichiers acceptés
            'type' => 'required|string', // Le type (Achat, Vente, etc.)
            'folders' => 'nullable|string', 
            'societe_id' => 'required|integer', // Validation pour societe_id
        ]);
        
        $dbName = session('database'); // Assurez-vous que 'database' est bien défini dans la session
// dd($request->folders);
 // Vérifier si un fichier a été téléchargé
 if ($request->hasFile('file')) {
    $file = $request->file('file');
    $originalFilename = $file->getClientOriginalName();

    // Vérifier si le fichier existe déjà dans la base de données
    $existingFile = File::where('name', $originalFilename)
                         ->where('societe_id', $request->input('societe_id'))
                         ->first();

    if ($existingFile) {
        // Si le fichier existe, retourner une vue avec un message d'alerte
        // return back()->with('alert', 'Un fichier avec ce nom existe déjà. Voulez-vous l\'écraser ?')->withInput();
        return back()->with('alert', 'Un fichier avec ce nom existe déjà.')->withInput();

    }
        
            // Sauvegarder les informations du fichier dans la base de données
            $fileRecord = new File();
            $fileRecord->name = $originalFilename;  // Nom original du fichier
            $fileRecord->type = $request->input('type');  // Type du fichier (Achat, Vente, etc.)
            $fileRecord->societe_id = $request->input('societe_id');  // ID de la société
            $fileRecord->folders = $request->input('folders');  // ID du dossier
            // Après avoir déplacé le fichier
            $fileRecord->path = 'storage/uploads/' . $dbName . '/' . $fileRecord->id . '_' . $originalFilename; // Assurez-vous que cette ligne est exécutée
            $fileRecord->save(); // Enregistrez les modifications
            $fileRecord->save();  // Sauvegarde dans la base de données
    
            // Récupérer l'ID du fichier enregistré
            $fileId = $fileRecord->id;
    
            // Créer un nom de fichier avec l'ID
            $filename = $fileId . '_' . $originalFilename;
    
     
            // Vérifiez si le répertoire de la base de données existe et créez-le si nécessaire
            $dbPath = public_path('storage/uploads/' . $dbName);
            if (!file_exists($dbPath)) {
                mkdir($dbPath, 0777, true);
            }
    
            // Déplacer le fichier téléchargé dans le dossier de la base de données
            $file->move($dbPath, $filename);
    
            // Mettre à jour le chemin du fichier dans la base de données
            $fileRecord->path = 'storage/uploads/' . $dbName . '/' . $filename;  // Le chemin est relatif à 'public'
            $fileRecord->save();  // Sauvegarde mise à jour dans la base de données
    
            return back()->with('success', 'Fichier téléchargé avec succès. ID du fichier : ' . $fileId);
            // return back();

        } else {
            return back()->withErrors(['file' => 'Aucun fichier téléchargé.']);
        }
    }
    
    
    
    
}
