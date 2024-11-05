<?php

namespace App\Http\Controllers;

use App\Models\Exercice; // Assurez-vous que le modèle est correctement importé
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
        // Trouver l'exercice par ID
    
        // Retourner la vue avec les données de l'exercice
        return view('exercices');
    }
    
}
