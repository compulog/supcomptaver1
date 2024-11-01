<?php

namespace App\Http\Controllers;

use App\Models\Client;
use Illuminate\Http\Request;
use Dompdf\Dompdf;
use Dompdf\Options;

class ClientsPDFExportController extends Controller
{
    public function export()
    {
        // Récupérer les données des clients
        $clients = Client::all(['compte', 'intitule', 'identifiant_fiscal', 'ICE', 'type_client']);
    
        // Vérifiez si des données sont récupérées
        if ($clients->isEmpty()) {
            dd("Aucun client trouvé."); // Cela devrait afficher un message si aucune donnée n'est trouvée
        }
    
        // Rendre la vue
        $html = view('pdf.clients', compact('clients'))->render();
    
        // Instancier Dompdf
        $dompdf = new Dompdf();
        $dompdf->loadHtml($html);
        $dompdf->setPaper('A4', 'portrait');
        $dompdf->render();
    
        return $dompdf->stream('clients.pdf');
    }
    
    
}
