<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\ExerciceComptable;

class ExerciceComptableController extends Controller
{
    public function cloturerExercice(Request $request)
    {
        // Validation des dates
        $data = $request->validate([
            'date_debut' => 'required|date',
            'date_fin'   => 'required|date|after_or_equal:date_debut',
        ]);

        // Récupération de l'année depuis date_debut
        $annee = date('Y', strtotime($data['date_debut']));

        // Obtenir l'ID de la société depuis la session
        $societeId = session('societeId');
        if (!$societeId) {
            return response()->json([
                'message' => 'ID de la société introuvable en session.'
            ], 403);
        }

        // Vérifier si l'exercice pour cette année est déjà clôturé
        $exerciceExist = ExerciceComptable::where('id_societe', $societeId)
            ->where('nom_exercice', $annee)
            ->where('cloture', true)
            ->exists();

        if ($exerciceExist) {
            return response()->json([
                'message' => "L'exercice de l'année $annee a déjà été clôturé pour cette société. Veuillez vérifier les exercices existants."
            ], 409); // Conflit
        }

        // Clôturer l'exercice en cours s’il existe
        $exerciceEnCours = ExerciceComptable::where('id_societe', $societeId)
            ->where('cloture', false)
            ->first();

        if ($exerciceEnCours) {
            $exerciceEnCours->cloture = true;
            $exerciceEnCours->save();
        }

        // Création du nouvel exercice clôturé
        $nouvelExercice = ExerciceComptable::create([
            'nom_exercice' => $annee,
            'date_debut'   => $data['date_debut'],
            'date_fin'     => $data['date_fin'],
            'id_societe'   => $societeId,
            'cloture'      => true,
        ]);

        return response()->json([
            'message'  => 'Exercice précédent clôturé (le cas échéant), nouvel exercice créé avec succès.',
            'exercice' => $nouvelExercice
        ]);
    }
}
 