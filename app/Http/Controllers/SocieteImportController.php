<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Societe;
use Maatwebsite\Excel\Facades\Excel;
use App\Imports\SocietesImport;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
class SocieteImportController extends Controller
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
    public function showImportForm()
    {
        return view('import_form');
    }

    public function import(Request $request)
    {
        // Validation du fichier
        $request->validate([
            'file' => 'required|mimes:xlsx,xls'
        ]);

        // Importer les données depuis le fichier Excel
        Excel::import(new SocietesImport, $request->file('file'));

        return redirect()->route('societes.index')->with('success', 'Sociétés importées avec succès!');
    }
}
