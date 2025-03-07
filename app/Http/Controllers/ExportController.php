<?php

namespace App\Http\Controllers;
use App\Models\PlanComptable; 
use App\Models\Fournisseur;
use Illuminate\Http\Request;
use Dompdf\Dompdf;
use Dompdf\Options;

class ExportController extends Controller
{
    public function exportPDF()
    {
        $fournisseurs = Fournisseur::all();

        // Initialiser dompdf
        $options = new Options();
        $options->set('defaultFont', 'Courier');
        $dompdf = new Dompdf($options);

        // Charger la vue HTML
        $html = view('fournisseurpdf', compact('fournisseurs'))->render();
        $dompdf->loadHtml($html);

        // (Optionnel) Définir la taille et l'orientation du papier
        $dompdf->setPaper('A4', 'landscape');

        // Rendre le PDF
        $dompdf->render();

        // Envoyer le PDF au navigateur
        return $dompdf->stream('fournisseurs.pdf');
    }

    public function export(Request $request)
{
    // Récupérer l'ID de la société depuis la session
    $societeId = session('societeId');  // Cela vient de la session partagée par le middleware
    
    // Vérifier si l'ID de la société existe
    if (!$societeId) {
        // Si aucune société n'est sélectionnée, rediriger ou afficher un message d'erreur
        return redirect()->route('plancomptable')->with('error', 'Aucune société sélectionnée');
    }

    // Récupérer les plans comptables en fonction de l'ID de la société
    $plansComptables = PlanComptable::where('societe_id', $societeId)
        ->get(['compte', 'intitule']); // Sélectionner les champs nécessaires
    
    // Vérifier si des plans comptables ont été trouvés
    if ($plansComptables->isEmpty()) {
        // Si aucun plan comptable n'est trouvé, génère un PDF vide ou avec un message
        $html = view('pdf.plan_comptable_empty')->render();
    } else {
        // Si des plans comptables sont trouvés, génère le PDF avec les données
        $html = view('plancomptablepdf', compact('plansComptables'))->render();
    }

    // Instancier Dompdf
    $dompdf = new Dompdf();
    $dompdf->loadHtml($html);
    $dompdf->setPaper('A4', 'portrait'); // Définir le format de papier (portrait ou paysage)
    $dompdf->render();

    // Télécharger le fichier PDF
    return $dompdf->stream('plancomptable.pdf');
}
    
     
}

