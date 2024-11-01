<?php

namespace App\Http\Controllers;

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
}

