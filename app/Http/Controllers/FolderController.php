<?php

namespace App\Http\Controllers;

use App\Models\Message;



use App\Models\File;



use Illuminate\Http\Request;

use Illuminate\Support\Facades\Validator;

use Illuminate\Support\Facades\DB;

use App\Models\Folder;

use App\Models\societe;

use Illuminate\Support\Facades\Auth;

use PhpOffice\PhpWord\IOFactory as PhpWordIOFactory;

use PhpOffice\PhpSpreadsheet\IOFactory as PhpSpreadsheetIOFactory;

use PhpOffice\PhpSpreadsheet\Reader\Exception as PhpSpreadsheetException;



class FolderController extends Controller

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

 public function update(Request $request, $id)
{
    $request->validate([
        'name' => 'required|string|max:255',
    ]);

    $folder = Folder::findOrFail($id);
    $folder->name = $request->input('name');
    $folder->updated_by = auth()->id(); // Ajout de l'utilisateur connecté
    $folder->is_read = 0;
    $folder->save();

    return redirect()->back()->with('success', 'Dossier renommé avec succès.');
}


  public function index($id, Request $request)
{
    // Récupérer le dossier
    $folder = Folder::find($id);

    $societeId = session('societeId');

    if (!$societeId) {
        return redirect()->route('home')->with('error', 'Aucune société trouvée dans la session');
    }

    // Récupérer un paramètre 'type' pour choisir la vue, ex : ?type=achat
    $type = $request->query('type', null);

    // Récupérer breadcrumb et clicked (le dossier cliqué) depuis la requête
    $breadcrumb = $request->query('breadcrumb', null);
    $clicked = $request->query('clicked', null);

    // Construire le chemin (breadcrumbs) à partir du dossier courant (comme dans ta vue)
    $currentFolder = $folder;
    $breadcrumbs = [];
    while ($currentFolder) {
        $breadcrumbs[] = $currentFolder->name;  // juste les noms pour la logique
        $currentFolder = $currentFolder->parent;
    }
    $breadcrumbs = array_reverse($breadcrumbs); // ordre du plus haut vers le plus bas

    // Requête pour les dossiers liés
    $foldersQuery = Folder::where('societe_id', $societeId)
                          ->where('folder_id', $id);

    // Filtrage/tris des dossiers
    if ($request->has('filter_by')) {
        $filterBy = $request->get('filter_by');
        $orderBy = $request->get('order_by', 'asc');
        if ($filterBy === 'name') {
            $foldersQuery->orderBy('name', $orderBy);
        } elseif ($filterBy === 'date') {
            $foldersQuery->orderBy('created_at', $orderBy);
        }
    } else {
        $foldersQuery->orderBy('created_at', 'asc');
    }
    $folders = $foldersQuery->get();

    // Requête pour les fichiers liés
    $filesQuery = File::where('societe_id', $societeId)
                      ->where('folders', $id);

    // Filtrage/tris des fichiers
    if ($request->has('filter_by')) {
        $filterBy = $request->get('filter_by');
        $orderBy = $request->get('order_by', 'asc');
        if ($filterBy === 'name') {
            $filesQuery->orderBy('name', $orderBy);
        } elseif ($filterBy === 'date') {
            $filesQuery->orderBy('created_at', $orderBy);
        }
    } else {
        $filesQuery->orderBy('created_at', 'asc');
    }
    $achatFiles = $filesQuery->get();

    // Stocker l'ID du dossier dans la session
    session(['foldersId' => $id]);
    $foldersId = session('foldersId');

    // Notifications messages non lus pour dossiers
    $unreadMessagesForFolder = Message::whereHas('file', function ($q) use ($foldersId) {
        $q->where('folders', $foldersId);
    })->where('is_read', 0)->get();

    $folderNotifications = [];
    if ($unreadMessagesForFolder->count() > 0) {
        $folderNotifications['folder_'.$id] = $unreadMessagesForFolder->count();
    }

    // Notifications messages non lus pour fichiers
    $fileNotifications = [];
    foreach ($achatFiles as $file) {
        $extension = strtolower(pathinfo($file->name, PATHINFO_EXTENSION));
        if (in_array($extension, ['jpg', 'jpeg', 'png', 'gif'])) {
            $file->preview = asset($file->path);
        } elseif ($extension === 'pdf') {
            $file->preview = 'https://via.placeholder.com/80x100.png?text=PDF';
        } elseif (in_array($extension, ['doc', 'docx'])) {
            $file->preview = 'https://via.placeholder.com/80x100.png?text=Word';
        } elseif (in_array($extension, ['xls', 'xlsx'])) {
            $file->preview = 'https://via.placeholder.com/80x100.png?text=Excel';
        } else {
            $file->preview = 'https://via.placeholder.com/80x100.png?text=Fichier';
        }

        $unreadMessagesForFile = Message::where('file_id', $file->id)
                                        ->where('is_read', 0)
                                        ->get();

        if ($unreadMessagesForFile->count() > 0) {
            $fileNotifications[$file->id] = $unreadMessagesForFile->count();
        }
    }
//   dd($clicked,$type);
    // Logique spéciale pour le type 'vente' selon le dossier cliqué
 if ($type === 'vente' && $clicked !== null) {
  

    // Si le dossier cliqué est le premier du chemin
    if ($clicked === $breadcrumbs[0]) {
                return view('foldersVente1', compact('achatFiles', 'folders', 'foldersId', 'folder', 'fileNotifications', 'folderNotifications', 'breadcrumb'));

          // return view('foldersVente', compact('venteFiles', 'folders', 'foldersId', 'folder', 'fileNotifications', 'folderNotifications', 'breadcrumb'));
    }
    // Sinon (clicked dans le chemin mais pas premier)
    elseif (in_array($clicked, $breadcrumbs)) {
        return view('foldersVente1', compact('achatFiles', 'folders', 'foldersId', 'folder', 'fileNotifications', 'folderNotifications', 'breadcrumb'));
    }
}else if ($type === 'banque' && $clicked !== null) {
  

    // Si le dossier cliqué est le premier du chemin
    if ($clicked === $breadcrumbs[0]) {
        //   $banqueFiles = $achatFiles; // Renommage logique
        // dd($achatFiles);
        // return view('folderbanque', compact('achatFiles', 'folders', 'foldersId', 'folder', 'fileNotifications', 'folderNotifications', 'breadcrumb'));
           return view('foldersBanque1', compact('achatFiles', 'folders', 'foldersId', 'folder', 'fileNotifications', 'folderNotifications', 'breadcrumb'));

    }
    // Sinon (clicked dans le chemin mais pas premier)
    elseif (in_array($clicked, $breadcrumbs)) {
        return view('foldersBanque1', compact('achatFiles', 'folders', 'foldersId', 'folder', 'fileNotifications', 'folderNotifications', 'breadcrumb'));
    }
}else if ($type === 'impot' && $clicked !== null) {
  

    // Si le dossier cliqué est le premier du chemin
    if ($clicked === $breadcrumbs[0]) {
        //   $banqueFiles = $achatFiles; // Renommage logique
        // dd($achatFiles);
        // return view('foldersImpot', compact('achatFiles', 'folders', 'foldersId', 'folder', 'fileNotifications', 'folderNotifications', 'breadcrumb'));
         return view('foldersImpot1', compact('achatFiles', 'folders', 'foldersId', 'folder', 'fileNotifications', 'folderNotifications', 'breadcrumb'));

    }
    // Sinon (clicked dans le chemin mais pas premier)
    elseif (in_array($clicked, $breadcrumbs)) {
        return view('foldersImpot1', compact('achatFiles', 'folders', 'foldersId', 'folder', 'fileNotifications', 'folderNotifications', 'breadcrumb'));
    }
}else if ($type === 'paie' && $clicked !== null) {
  

    // Si le dossier cliqué est le premier du chemin
    if ($clicked === $breadcrumbs[0]) {
        //   $banqueFiles = $achatFiles; // Renommage logique
        // dd($achatFiles);
        // return view('foldersPaie', compact('achatFiles', 'folders', 'foldersId', 'folder', 'fileNotifications', 'folderNotifications', 'breadcrumb'));
            return view('foldersPaie1', compact('achatFiles', 'folders', 'foldersId', 'folder', 'fileNotifications', 'folderNotifications', 'breadcrumb'));

    }
    // Sinon (clicked dans le chemin mais pas premier)
    elseif (in_array($clicked, $breadcrumbs)) {
        return view('foldersPaie1', compact('achatFiles', 'folders', 'foldersId', 'folder', 'fileNotifications', 'folderNotifications', 'breadcrumb'));
    }
}else if ($type === 'dossier_permanant' && $clicked !== null) {
  

    // Si le dossier cliqué est le premier du chemin
    if ($clicked === $breadcrumbs[0]) {
        //   $banqueFiles = $achatFiles; // Renommage logique
        // dd($achatFiles);
        // return view('foldersDossierPermanant', compact('achatFiles', 'folders', 'foldersId', 'folder', 'fileNotifications', 'folderNotifications', 'breadcrumb'));
            return view('foldersDossierPermanant1', compact('achatFiles', 'folders', 'foldersId', 'folder', 'fileNotifications', 'folderNotifications', 'breadcrumb'));

    }
    // Sinon (clicked dans le chemin mais pas premier)
    elseif (in_array($clicked, $breadcrumbs)) {
        return view('foldersDossierPermanant1', compact('achatFiles', 'folders', 'foldersId', 'folder', 'fileNotifications', 'folderNotifications', 'breadcrumb'));
    }
} else if (!in_array($type, ['achat', 'vente', 'banque', 'impot', 'paie', 'dossier_permanent']) && $clicked !== null) {
    return view('Douvrirsous', compact('achatFiles', 'folders', 'foldersId', 'folder', 'fileNotifications', 'folderNotifications', 'breadcrumb', 'type'));
}
// else if (!in_array($type, ['achat', 'vente', 'banque', 'impot', 'paie', 'dossier_permanent'])) {
//     return view('Douvrir', compact('achatFiles', 'folders', 'foldersId', 'folder', 'fileNotifications', 'folderNotifications', 'breadcrumb', 'type'));
// }




    // Choix de la vue selon le paramètre 'type' (autres cas)
    switch ($type) {
        case 'achat':
            return view('achat', compact('achatFiles', 'folders', 'foldersId', 'folder', 'fileNotifications', 'folderNotifications', 'breadcrumb'));
        case 'vente':
            return view('vente', compact('achatFiles', 'folders', 'foldersId', 'folder', 'fileNotifications', 'folderNotifications', 'breadcrumb'));
        case 'banque':
            return view('banque', compact('achatFiles', 'folders', 'foldersId', 'folder', 'fileNotifications', 'folderNotifications', 'breadcrumb'));
        case 'impot':
            return view('impot', compact('achatFiles', 'folders', 'foldersId', 'folder', 'fileNotifications', 'folderNotifications', 'breadcrumb'));
        case 'paie':
            return view('paie', compact('achatFiles', 'folders', 'foldersId', 'folder', 'fileNotifications', 'folderNotifications', 'breadcrumb'));
        case 'dossier_permanent':
            return view('dossier_permanent', compact('achatFiles', 'folders', 'foldersId', 'folder', 'fileNotifications', 'folderNotifications', 'breadcrumb'));
        default:
            // Vue par défaut
            return view('folders', compact('achatFiles', 'folders', 'foldersId', 'folder', 'fileNotifications', 'folderNotifications', 'breadcrumb'));
    }
}




    // public function index($id)

    // {

    //     $societeId = session('societeId'); // Récupère l'ID de la société depuis la session



    //     if ($societeId) {

    //         // Récupère les fichiers de type 'achat' où le champ 'folders' est égal à $id

    //         $achatFiles = File::where('societe_id', $societeId)

    //                           ->where('type', 'achat') // Filtrer par type 'achat'

    //                           ->where('folders', $id) // Filtrer où le champ 'folders' est égal à $id

    //                           ->get();



    //         // Récupère les dossiers pour la société donnée et où le 'folder_id' est égal à $id

    //         $folders = Folder::where('societe_id', $societeId)

    //                          ->where('folder_id', $id)

    //                          ->get();



    //         // Stocke l'ID du dossier dans la session pour une utilisation future dans la vue

    //         session(['foldersId' => $id]);  // Cette ligne enregistre l'ID dans la session



    //         // Assurez-vous que $foldersId est bien défini ici

    //         $foldersId = session('foldersId'); // Récupère l'ID du dossier depuis la session



    //         // Ajouter un champ 'preview' pour chaque fichier afin de passer l'aperçu au front-end

    //         foreach ($achatFiles as $file) {

    //             // Détecte l'extension du fichier

    //             $extension = strtolower(pathinfo($file->name, PATHINFO_EXTENSION));



    //             // Déterminer l'aperçu en fonction du type de fichier

    //             if (in_array($extension, ['jpg', 'jpeg', 'png', 'gif'])) {

    //                 $file->preview = asset('storage/' . $file->path); // Image

    //             } elseif (in_array($extension, ['pdf'])) {

    //                 $file->preview = 'https://via.placeholder.com/80x100.png?text=PDF'; // PDF

    //             } elseif (in_array($extension, ['doc', 'docx'])) {

    //                 $file->preview = 'https://via.placeholder.com/80x100.png?text=Word'; // Word

    //             } elseif (in_array($extension, ['xls', 'xlsx'])) {

    //                 $file->preview = 'https://via.placeholder.com/80x100.png?text=Excel'; // Excel

    //             } else {

    //                 $file->preview = 'https://via.placeholder.com/80x100.png?text=Fichier'; // Fichier générique

    //             }

    //         }



    //         // Vérifie si la collection de fichiers est vide après le filtre

    //         if ($achatFiles->isEmpty()) {

    //             // Retourne les fichiers d'achats si aucun fichier n'est trouvé avec 'folders' = $id

    //             return view('folders', compact('achatFiles'))->with('message', 'Aucun fichier trouvé avec l\'ID du dossier. Voici les fichiers d\'achat.');

    //         }



    //         // Si des fichiers sont trouvés, passe les fichiers et les dossiers à la vue

    //         return view('folders', compact('achatFiles', 'folders', 'foldersId')); // Assurez-vous que $foldersId est bien passé

    //     } else {

    //         // Si l'ID de la société n'est pas trouvé dans la session, redirige vers la page d'accueil

    //         return redirect()->route('home')->with('error', 'Aucune société trouvée dans la session');

    //     }

    // }





    public function create(Request $request)

    {
      
   
        $validator = Validator::make($request->all(), [

            'name' => 'required|string|max:255', // Correspond au champ 'name' du formulaire

            'societe_id' => [

                'required',

                function ($attribute, $value, $fail) {

                    // Vérifier si la société existe dans la base 'supcompta'

                    $exists = DB::connection('supcompta')->table('societe')->where('id', $value)->exists();

                    if (!$exists) {

                        $fail('La société avec cet ID n\'existe pas dans la base supcompta.');

                    }

                },

            ],

            'folders_id' => 'nullable|integer', // Correspond au champ 'folders_id' du formulaire

        ]);



        // Si la validation échoue, rediriger avec les erreurs



        if ($validator->fails()) {

            return redirect()->back()->withErrors($validator)->withInput();

        }



        // Créer le dossier avec les données validées



        // Création du dossier

        Folder::create([

            'name' => $request->name,

            'societe_id' => $request->societe_id,

            'folder_id' => $request->folders_id,

            'type_folder' => $request->type_folder,
        'updated_by' => auth()->id(),
 'is_read' => 0,


        ]);



        // Rediriger avec un message de succès

    //     return redirect()->route('achat.view')->with('success', 'Dossier créé avec succès');

    // }







        // Si folders_id est fourni, on redirige vers la route associée à ce folder_id

        if ($request->has('folders_id') && $request->folders_id) {

            return redirect()->route('folder.show', ['id' => $request->folders_id])->with('success', 'Dossier créé avec succès');

        }

    // Redirection selon le type du dossier

    switch ($request->type_folder) {

        case 'achat': // Exemple de type de dossier

            return redirect()->route('achat.view')->with('success', 'Dossier créé avec succès');



        case 'vente': // Exemple d'un autre type de dossier

            return redirect()->route('vente.view')->with('success', 'Dossier créé avec succès');



            case 'banque': // Exemple d'un autre type de dossier

                return redirect()->route('banque.view')->with('success', 'Dossier créé avec succès');



                case 'Caisse': // Exemple d'un autre type de dossier

                    return redirect()->route('caisse.view')->with('success', 'Dossier créé avec succès');



                    case 'impot': // Exemple d'un autre type de dossier

                        return redirect()->route('impot.view')->with('success', 'Dossier créé avec succès');



                        case 'paie': // Exemple d'un autre type de dossier

                            return redirect()->route('paie.view')->with('success', 'Dossier créé avec succès');



                            case 'Dossier_permanant': // Exemple d'un autre type de dossier

                                return redirect()->route('Dossier_permanant.view')->with('success', 'Dossier créé avec succès');



        // Ajouter d'autres types de dossier si nécessaire

        default:

            return redirect()->route('achat.view')->with('success', 'Dossier créé avec succès');

    }

        // Sinon, on retourne vers une vue (par exemple folder.create)



    }





   // app/Http/Controllers/FolderController.php



   public function destroy($id)

   {

       // Trouver le dossier

       $folder = Folder::findOrFail($id);



       // Supprimer tous les fichiers associés au dossier (en utilisant une requête SQL brute sur la base de données 'supcompta')

       DB::connection('supcompta')->table('files')->where('id', $folder->id)->delete();

    $folder->is_read = 0;
    $folder->save();

       // Supprimer le dossier

       $folder->delete();



       // Retourner une réponse de succès

       return redirect()->back();

   }











}



