<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class OperationCaisseBanqueController extends Controller
{
    public function readFile(Request $request)
{
 
    // Récupérer le nom du fichier depuis la requête
    $fileName = $request->input('file_name');

    // Récupérer le fichier depuis le système de fichiers
    $filePath = storage_path('app/files/' . $fileName); // Ajustez le chemin selon votre structure
   
    if (file_exists($filePath)) {
        // Lire le contenu du fichier
        $content = file_get_contents($filePath);
       
        // Initialiser un tableau pour stocker les données
        $data = [];

        // Vérifier si le fichier est un CSV
        if (pathinfo($fileName, PATHINFO_EXTENSION) === 'csv') {
            // Parser le contenu CSV
            $rows = array_map('str_getcsv', explode("\n", $content));
            foreach ($rows as $row) {
                // Assurez-vous que la ligne n'est pas vide
                if (count($row) >= 12) { // Vérifiez que la ligne a suffisamment de colonnes
                    $data[] = [
                        'date' => $row[0],
                        'mode_paiement' => $row[1],
                        'compte' => $row[2],
                        'libelle' => $row[3],
                        'debit' => $row[4],
                        'credit' => $row[5],
                        'facture' => $row[6],
                        'taux_ras_tva' => $row[7],
                        'nature_operation' => $row[8],
                        'date_lettrage' => $row[9],
                        'contre_partie' => $row[10],
                        'piece_justificative' => $row[11],
                    ];
                }
            }
        }

        // Retourner les données au format JSON
        return response()->json($data);
    }

    return response()->json(['error' => 'Fichier non trouvé'], 404);
}
}
