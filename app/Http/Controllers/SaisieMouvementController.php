<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
class SaisieMouvementController extends Controller
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
    // Méthode pour afficher le formulaire de saisie
    public function index()
    {
        return view('saisie mouvement(J ACH-VTE)'); // Remplacez 'saisie.index' par le nom de votre vue
    }

    // Méthode pour traiter la soumission du formulaire
    public function store(Request $request)
    {
        // Valider les données
        $request->validate([
            'journal' => 'required',
            'mois' => 'required',
            'annee' => 'required',
            // Ajoutez d'autres règles de validation ici
        ]);

        // Traiter les données
        // Exemple : Enregistrement dans la base de données

        return redirect()->route('saisie.index')->with('success', 'Mouvement enregistré avec succès !');
    }

    // Autres méthodes (show, edit, update, destroy) selon les besoins...
}
