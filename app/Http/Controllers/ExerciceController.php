<?php

namespace App\Http\Controllers;

use App\Models\Exercice; // Assurez-vous que le modèle Exercice est bien importé
use App\Models\Societe; // Importez le modèle Societe
use Illuminate\Http\Request;

class ExerciceController extends Controller
{
    /**
     * Affiche la vue d'un exercice spécifique.
     *
     * @param  int  $id
     * @return \Illuminate\View\View
     */
    public function show($id)
    {
        // Récupérez l'entité "Societe" en fonction de l'id
        $societe = Societe::findOrFail($id);
        session()->put('societeId',$societe->id);
        // Passez la variable societeId à la vue
        return view('exercices');
    }
}
