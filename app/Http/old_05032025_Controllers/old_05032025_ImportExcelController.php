<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Maatwebsite\Excel\Facades\Excel;
use App\Imports\ClientsImport;
use App\Models\Client;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
class ImportExcelController extends Controller
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
    public function import(Request $request)
    {
        // Valider les champs et le fichier Excel
        $validatedData = $request->validate([
            'excel-file' => 'required|file|mimes:xlsx,xls|max:2048'
        ]);

        try {
            Excel::import(new ClientsImport, $request->file('excel-file'));

            return response()->json(['success' => true, 'message' => 'Fichier importé avec succès.']);
        } catch (\Maatwebsite\Excel\Validators\ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erreur de validation lors de l\'importation.',
                'errors' => $e->failures()
            ], 422);
        } catch (\Exception $e) {
            // Journaliser l'erreur pour l'analyse
            \Log::error('Import error: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Une erreur est survenue lors de l\'importation.'
            ], 500);
        }
    }

    public function getClients()
    {
        $clients = Client::all(); // Récupère tous les clients

        return response()->json([
            'success' => true,
            'data' => $clients
        ]);
    }
}
