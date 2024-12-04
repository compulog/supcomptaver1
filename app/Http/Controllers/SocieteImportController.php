<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Societe;
use Maatwebsite\Excel\Facades\Excel;
use App\Imports\SocietesImport;

class SocieteImportController extends Controller
{
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
