<?php
namespace App\Http\Controllers;

use App\Models\Client;
use Illuminate\Http\Request;
use Dompdf\Dompdf;
use Dompdf\Options;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
class ClientsPDFExportController extends Controller
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
    public function export(Request $request)
    {
        // Récupérer l'ID de la société depuis la requête
        $societeId = $request->input('societe_id');
        
        // Récupérer les clients en fonction de l'ID de la société
        $clients = Client::where('societe_id', $societeId)
            ->get(['compte', 'intitule', 'identifiant_fiscal', 'ICE', 'type_client']);
    
        // Vérifie si des clients ont été trouvés
        if ($clients->isEmpty()) {
            // Si aucun client n'est trouvé, crée un PDF vide ou avec un message
            $html = view('pdf.clients_empty')->render();
        } else {
            // Si des clients sont trouvés, génère le PDF avec les clients
            $html = view('pdf.clients', compact('clients'))->render();
        }
    
        // Instancier Dompdf
        $dompdf = new Dompdf();
        $dompdf->loadHtml($html);
        $dompdf->setPaper('A4', 'portrait');
        $dompdf->render();
    
        // Télécharger le fichier PDF
        return $dompdf->stream('clients.pdf');
    }
}
