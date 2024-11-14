<?php

namespace App\Http\Controllers;

use App\Models\File; // Assurez-vous d'importer le modèle File
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\StreamedResponse;
use Illuminate\Support\Facades\Log;

class AchatController extends Controller
{
    public function index()
    {
        $societeId = session('societeId'); // Récupère l'ID de la société depuis la session
        
        if ($societeId) {
            // Filtrer les fichiers de type 'achat' pour la société donnée
            $files = File::where('societe_id', $societeId)
                         ->where('type', 'achat') // Filtrer par type 'achat'
                         ->get();
                         
            return view('achat', compact('files')); // Passez les fichiers à la vue
        } else {
            return redirect()->route('home')->with('error', 'Aucune société trouvée dans la session');
        }
    }
   public function download($fileId)
{
    // Récupérer le fichier depuis la base de données
    $file = File::findOrFail($fileId);

    // Vérifier si le fichier existe
    if (!$file) {
        return redirect()->route('achat.index')->with('error', 'Fichier introuvable');
    }

    // Si les données sont en base64, on les décode
    $fileData = base64_decode($file->file_data);

    // Vérifiez si le fichier a bien été décodé
    if (!$fileData) {
        return redirect()->route('achat.index')->with('error', 'Le fichier est corrompu');
    }

    // Détecter l'extension du fichier et définir le type MIME
    $fileExtension = pathinfo($file->name, PATHINFO_EXTENSION);
    $mimeType = '';

    // Détection des types MIME en fonction de l'extension
    if (in_array(strtolower($fileExtension), ['jpg', 'jpeg', 'png', 'gif', 'bmp'])) {
        $mimeType = 'image/' . strtolower($fileExtension);
    } elseif ($fileExtension == 'pdf') {
        $mimeType = 'application/pdf';
    } elseif (in_array(strtolower($fileExtension), ['xls', 'xlsx'])) {
        $mimeType = 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet';
    } elseif (in_array(strtolower($fileExtension), ['doc', 'docx'])) {
        $mimeType = 'application/vnd.openxmlformats-officedocument.wordprocessingml.document';
    } else {
        // Utiliser finfo pour détecter les autres types
        $finfo = new \finfo(FILEINFO_MIME_TYPE);
        $mimeType = $finfo->buffer($fileData);
    }

    // Log de débogage pour vérifier le MIME et la taille du fichier
    Log::debug('Downloading file:', ['file_name' => $file->name, 'mime_type' => $mimeType, 'file_size' => strlen($fileData)]);

    // Retourner le fichier binaire avec un type MIME correct
    return response()->stream(
        function () use ($fileData) {
            echo $fileData;  // Envoie les données binaires du fichier
        },
        200,
        [
            'Content-Type' => $mimeType, // Utilisation du type MIME correct
            'Content-Disposition' => 'attachment; filename="' . $file->name . '"', // Assurer que le nom du fichier contient l'extension
            'Content-Length' => strlen($fileData), // Utiliser la longueur correcte du fichier
        ]
    );
}

    
}
