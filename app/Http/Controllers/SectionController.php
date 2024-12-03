<?php

namespace App\Http\Controllers;

use App\Models\Section;
use App\Models\Societe;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
class SectionController extends Controller
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
        $request->validate([
            'name' => 'required|string|max:255',
            'societe_id' => 'required|exists:societes,id',  // Vérifie l'existence de la société dans la table 'societes' de la base supcompta
        ]);

        try {
            // Créer une nouvelle section
            $section = Section::create([
                'name' => $request->name,
                'societe_id' => $request->societe_id,
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Section ajoutée avec succès',
                'section' => $section,
            ]);
        } catch (\Exception $e) {
            \Log::error('Erreur lors de l\'ajout de la section: ' . $e->getMessage());
            \Log::error('Trace: ' . $e->getTraceAsString());

            return response()->json([
                'success' => false,
                'message' => 'Une erreur s\'est produite lors de l\'ajout de la section.',
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ], 500);
        }
    }
}
