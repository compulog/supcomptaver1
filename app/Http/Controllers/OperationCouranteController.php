<?php

// app/Http/Controllers/OperationCouranteController.php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\OperationCourante;

class OperationCouranteController extends Controller
{
   public function store(Request $request)
{
    $data = $request->validate([
        'date' => 'required|date',
        'dossier' => 'required|string|max:255',
        'facture' => 'required|string|max:255',
        'compte' => 'required|string|max:255',
        'libelle' => 'required|string|max:255',
        'debit' => 'required|numeric',
        'credit' => 'required|numeric',
        'contrepartie' => 'nullable|string|max:255',
        'rubrique_tva' => 'nullable|string|max:255',
        'compte_tva' => 'nullable|string|max:255',
        'prorata' => 'nullable|numeric',
        'file' => 'nullable|string|max:255',
    ]);

    // Crée une nouvelle instance de OperationCourante
    OperationCourante::create($data);

    return response()->json(['success' => 'Opération enregistrée avec succès.']);
}

}

