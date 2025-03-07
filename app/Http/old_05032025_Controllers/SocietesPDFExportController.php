<?php

namespace App\Http\Controllers;

use App\Models\Societe; // Assurez-vous que le modèle Societe est importé
use Illuminate\Http\Request;
use Dompdf\Dompdf;
use Dompdf\Options;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
class SocietesPDFExportController extends Controller
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
    /**
     * Générer et télécharger le PDF des sociétés.
     *
     * @return \Illuminate\Http\Response
     */
    public function exportPDF()
    {
        // Récupérer les données des sociétés
        $societes = Societe::all([ 
        'id',  
        'raison_sociale',
        'forme_juridique',
        'siege_social',
        'patente',
        'rc',
        'centre_rc',
        'identifiant_fiscal',
        'ice',
        'assujettie_partielle_tva',
        'prorata_de_deduction',
        'exercice_social_debut', 
        'exercice_social_fin', 
        'date_creation',
        'nature_activite',
        'activite',
        'regime_declaration',
        'fait_generateur',
        'rubrique_tva',
        'designation',
        'nombre_chiffre_compte', 
        'modele_comptable' ,]); 

        // Vérifiez si des données sont récupérées
        if ($societes->isEmpty()) {
            dd("Aucune société trouvée."); // Affiche un message si aucune donnée n'est trouvée
        }

        // Rendre la vue
        $html = view('pdf.societes', compact('societes'))->render();

        // Instancier Dompdf
        $dompdf = new Dompdf();
        $dompdf->loadHtml($html);
        $dompdf->setPaper('A3', 'landscape');
        $dompdf->render();

        return $dompdf->stream('societes.pdf'); // Changez le nom du fichier selon vos besoins
    }
}
