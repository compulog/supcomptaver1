<?php
namespace App\Http\Controllers;

use App\Models\File;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\File as FacadeFile;
use Illuminate\Support\Facades\Response;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use setasign\Fpdi\Fpdi;
use Illuminate\Support\Facades\Log; 
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


    // public function update(Request $request, $id)
    // {
    //     // Validation de l'entrée
    //     $request->validate([
    //         'name' => 'required|string|max:255',
    //     ]);
    
    //     // Récupération du fichier de la base de données
    //     $file = File::findOrFail($id);
    
    //     // Le nom du fichier actuel dans le stockage
    //     $oldFileName = $file->name;
    
    //     // Nouveau nom du fichier
    //     $newFileName = $request->input('name');
    
    //     // Chemin du fichier dans le stockage
    //     $oldFilePath = public_path('storage/uploads/' . $oldFileName);
    //     $newFilePath = public_path('storage/uploads/' . $newFileName);
    
    //     // Vérification si le fichier existe avant de tenter de le renommer
    //     if (file_exists($oldFilePath)) {
    //         // Renommer le fichier
    //         rename($oldFilePath, $newFilePath);
    //     }
    
    //     // Mise à jour du nom dans la base de données
    //     $file->name = $newFileName;
    //     $file->save();
    
    //     // Retour à la page précédente
    //     return redirect()->back();
    // }
    public function update(Request $request, $id)
    {
        // Validation de l'entrée
        $request->validate([
            'name' => 'required|string|max:255',
        ]);
    
        // Récupération du nom de la base depuis la session
        $dbName = session('database');
    
        // Récupération du fichier depuis la base de données
        $file = File::findOrFail($id);
    
        // Ancien nom avec extension (ex: rapport.pdf)
        $oldFileName = $file->name;
    
        // Chemin vers l'ancien fichier
        $oldFilePath = public_path('storage/uploads/' . $dbName . '/' . $file->id . '_' . $oldFileName);
    
        // Extraction de l'extension
        $extension = pathinfo($oldFileName, PATHINFO_EXTENSION);
    
        // Nouveau nom depuis la requête
        $newNameWithoutExt = $request->input('name');
    
        // Nouveau nom complet avec extension pour la BDD
        $newFileName = $newNameWithoutExt . '.' . $extension;
    
        // Nouveau nom avec l'ID pour le fichier dans le dossier
        $newStoredFileName = $file->id . '_' . $newFileName;
    
        // Nouveau chemin absolu (pour renommer le fichier)
        $newFilePath = public_path('storage/uploads/' . $dbName . '/' . $newStoredFileName);
    
        // Création du répertoire si nécessaire
        $directoryPath = public_path('storage/uploads/' . $dbName);
        if (!file_exists($directoryPath)) {
            mkdir($directoryPath, 0755, true);
        }
    
        // Si l'ancien fichier existe, on le renomme
        if (file_exists($oldFilePath)) {
            rename($oldFilePath, $newFilePath);
        }
    
        // Mise à jour dans la base de données
        $file->name = $newFileName;
        $file->path = 'storage/uploads/' . $dbName . '/' . $newStoredFileName; // chemin relatif
 $file->updated_by = auth()->id();
  $file->is_read = 0;
        $file->save();
    
        return redirect()->back()->with('success', 'Fichier renommé avec succès.');
    }
    
    public function view($id)
    {
        try {
            \Log::info("ID du fichier demandé : " . $id);
    
            // Récupérer le fichier de la base de données
            $file = File::find($id);
            if (!$file) {
                \Log::error("Fichier non trouvé avec l'ID : " . $id);
                return abort(404, 'Fichier non trouvé');
            }
    
            $databaseName = DB::getDatabaseName();
            $filename = $id . '_' . $file->name;
            $filePath = public_path('storage/uploads/' . $databaseName . '/' . $filename);
    
            \Log::info("Chemin complet du fichier : " . $filePath);
    
            if (!file_exists($filePath)) {
                \Log::error("Le fichier n'existe pas à ce chemin : " . $filePath);
                return abort(404, 'Fichier non trouvé');
            }
    
            $mimeType = mime_content_type($filePath);
            \Log::info("Type MIME du fichier : " . $mimeType);
    
            $viewableMimeTypes = ['image/jpeg', 'image/png', 'image/gif', 'application/pdf', 'text/plain', 'text/html'];
    
            if (in_array($mimeType, $viewableMimeTypes)) {
                \Log::info("Le fichier est affichable dans le navigateur.");
                return response()->file($filePath);
            } else {
                \Log::info("Le fichier sera téléchargé.");
                return response()->download($filePath);
            }
        } catch (\Exception $e) {
            \Log::error("Erreur lors de l'affichage du fichier : " . $e->getMessage());
            return abort(500, 'Erreur interne du serveur');
        }
    }
    
    // FileController.php
public function destroy($id)
{
    // Récupère le fichier depuis la base de données
    $file = File::findOrFail($id);
    $file->is_read = 0;

    // Renomme le champ `name` (par exemple : "23_document.pdf")
    $newName = $file->id . '_' . $file->name;
    $file->name = $newName;
    $file->save();

    // Supprime le fichier du disque
    Storage::delete($file->path);

    // Supprime l'enregistrement de la base de données
    $file->delete();

    return redirect()->back();
}


// public function mergeFiles(Request $request)
// {
//     try {
//         Log::info('Début de la fusion des fichiers.');

//         // Valider les fichiers
//         $request->validate([
//             'files.*' => 'required|file|mimes:pdf',
//         ]);

//         Log::info('Validation des fichiers réussie.');

//         // Créer un nouvel objet FPDI
//         $pdf = new Fpdi();

//         // Parcourir les fichiers et les ajouter au PDF
//         foreach ($request->file('files') as $file) {
//             Log::info('Traitement du fichier : ' . $file->getClientOriginalName());

//             if (!$file->isValid()) {
//                 Log::error('Fichier non valide : ' . $file->getClientOriginalName());
//                 return response()->json(['success' => false, 'message' => 'Un ou plusieurs fichiers ne sont pas valides.'], 400);
//             }

//             $pageCount = $pdf->setSourceFile($file->getPathname());
//             Log::info('Nombre de pages dans le fichier : ' . $pageCount);

//             // Ajouter chaque page au PDF
//             for ($pageNo = 1; $pageNo <= $pageCount; $pageNo++) {
//                 $templateId = $pdf->importPage($pageNo);
//                 $pdf->addPage();
//                 $pdf->useTemplate($templateId);
//             }
//         }

//         Log::info('Tous les fichiers ont été ajoutés au PDF.');

//         // Définir le nom du fichier fusionné
//         $mergedFileName = 'merged_' . time() . '.pdf';
//         $storagePath = public_path('storage/uploads/');

//         // Créer le répertoire si nécessaire
//         if (!file_exists($storagePath)) {
//             mkdir($storagePath, 0755, true);
//             Log::info('Répertoire créé : ' . $storagePath);
//         }

//         // Enregistrer le fichier fusionné
//         $pdf->Output('F', $storagePath . $mergedFileName);
//         Log::info('Fichier fusionné enregistré : ' . $mergedFileName);

//         // Enregistrer le chemin dans la base de données
//         $fileRecord = new File();
//         $fileRecord->name = $mergedFileName;
//         $fileRecord->path = 'storage/uploads/' . $mergedFileName;
//         $fileRecord->societe_id = session()->get('societeId');
//         $fileRecord->save();

//         Log::info('Enregistrement du fichier dans la base de données réussi.');

//         return response()->json(['success' => true, 'file' => $fileRecord]);
//     } catch (\Exception $e) {
//         Log::error("Erreur lors de la fusion des fichiers : " . $e->getMessage());
//         return response()->json(['success' => false, 'message' => 'Une erreur s\'est produite : ' . $e->getMessage()], 500);
//     }
// }
}
