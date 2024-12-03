<?php

// app/Http/Controllers/OperationCouranteController.php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\OperationCourante;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
class OperationCouranteController extends Controller
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

