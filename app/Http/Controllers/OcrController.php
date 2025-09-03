<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use thiagoalessio\TesseractOCR\TesseractOCR; // Importation de la classe TesseractOCR

class OcrController extends Controller
{
    public function index()
    {
        return view('Charger-document');  // Vue pour télécharger une image
    }

    public function analyser(Request $request)
    {
        // Validation du fichier image
        $request->validate([
            'file' => 'required|file|mimes:jpeg,png,jpg|max:10240', // Maximum 10 Mo
        ]);

        // Sauvegarder le fichier téléchargé
        $file = $request->file('file');
        $path = $file->storeAs('public/ocr_files', $file->getClientOriginalName());

        // Obtenir le chemin complet du fichier
        $filePath = storage_path('app/' . $path);

        // Utiliser Tesseract pour extraire le texte de l'image
        $text = (new TesseractOCR($filePath))->run();

        // Retourner la vue avec le texte extrait
        return view('Charger-documment', ['text' => $text]);
    }
}
