<?php
namespace App\Http\Controllers;

use App\Models\File;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\File as FacadeFile;
use Illuminate\Support\Facades\Response;

class FileController extends Controller
{
    public function view($id)
{
    // Récupérer le fichier de la base de données
    $file = File::findOrFail($id);

    // Le chemin du fichier à consulter
    $filePath = public_path('files/achats/' . $file->name);

    // Vérifier si le fichier existe
    \Log::info("Chemin du fichier : " . $filePath);  // Log du chemin

    if (!FacadeFile::exists($filePath)) {
        \Log::error("Le fichier n'existe pas à ce chemin : " . $filePath);
        return abort(404, 'Fichier non trouvé');
    }

    // Détecter le type MIME du fichier
    $mimeType = mime_content_type($filePath);

    \Log::info("Type MIME du fichier : " . $mimeType);  // Log du type MIME

    // Liste des types de fichiers à afficher dans le navigateur
    $viewableMimeTypes = ['image/jpeg', 'image/png', 'image/gif', 'application/pdf', 'text/plain', 'text/html'];

    // Vérifier si le fichier peut être affiché dans le navigateur
    if (in_array($mimeType, $viewableMimeTypes)) {
        return view('file.view', compact('file', 'filePath', 'mimeType'));
    } else {
        return response()->download($filePath);
    }
}

}
