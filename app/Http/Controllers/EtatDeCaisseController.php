<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class EtatDeCaisseController extends Controller
{
    // Méthode pour afficher la page de l'état de caisse
    public function index()
    {
        // Vous pouvez ici ajouter des données que vous souhaitez passer à la vue
        // Exemple : $caisseData = Caisse::all();

        return view('etat_de_caisse'); // Rendre la vue 'etat_de_caisse.blade.php'
    }
    
}
