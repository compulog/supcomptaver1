<?php
namespace App\Http\Controllers;


use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;  // Ajouter cette ligne pour importer DB
use App\Models\Folder;
use App\Models\societe;
class FolderController extends Controller
{
    // Afficher tous les dossiers
    public function index()
    {
        $societe = optional(auth()->user()->societe);
        
        // Vérifier si la société existe avant de récupérer son id
        if (!$societe) {
            // Gérer le cas où la société est absente
            return redirect()->back()->with('error', 'Aucune société associée à cet utilisateur.');
        }
    
        $folders = Folder::where('societe_id', $societe->id)->get();
        $societes = Societe::all(); // Pour afficher les sociétés dans le formulaire
    
        return view('achat', compact('folders', 'societes'));
    }
    

    // Créer un dossier
 
    
    // Dans votre méthode create()
    public function create(Request $request)
    {
        // Validation personnalisée
        $validator = Validator::make($request->all(), [
            'folder_name' => 'required|string|max:255',
            'societe_id' => [
                'required',
                function ($attribute, $value, $fail) {
                    // Utilisation de DB::connection pour exécuter la requête sur la base de données 'supcompta'
                    $exists = DB::connection('supcompta')->table('societe')->where('id', $value)->exists();

                    if (!$exists) {
                        $fail('La société avec cet ID n\'existe pas dans la base supcompta.');
                    }
                },
            ],
        ]);

        // Si la validation échoue, rediriger avec les erreurs
        if ($validator->fails()) {
            return redirect()->back()->withErrors($validator)->withInput();
        }

        // Si la validation réussit, créer le dossier
        Folder::create([
            'name' => $request->folder_name,
            'societe_id' => $request->societe_id,
        ]);

        // Rediriger avec un message de succès
        return redirect()->route('folder.index')->with('success', 'Dossier créé avec succès');
    }
}
