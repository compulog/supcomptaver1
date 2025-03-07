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

    public function index($id, Request $request)
    {

        // Récupérer le dossier avec l'ID passé en paramètre
        $folder = Folder::find($id);
        $societeId = session('societeId');

        if ($societeId) {
            // Filtrage et tri des dossiers associés à la société
            $folders = Folder::where('societe_id', $societeId)
                             ->where('folder_id', $id);

            // Appliquer le filtre pour les dossiers
            if ($request->has('filter_by')) {
                $filterBy = $request->get('filter_by');
                if ($filterBy == 'name') {
                    $folders->orderBy('name', $request->get('order_by', 'asc'));  // Tri par nom
                } elseif ($filterBy == 'date') {
                    $folders->orderBy('created_at', $request->get('order_by', 'asc'));  // Tri par date
                }
            } else {
                $folders->orderBy('created_at', 'asc');  // Par défaut, trier par date ascendante
            }

            $folders = $folders->get();

            // Filtrage et tri des fichiers de type "achat"
            $query = File::where('societe_id', $societeId)
                         ->where('type', 'achat')
                         ->where('folders', $id);

            // Appliquer le filtre pour les fichiers
            if ($request->has('filter_by')) {
                $filterBy = $request->get('filter_by');
                if ($filterBy == 'name') {
                    $query->orderBy('name', $request->get('order_by', 'asc'));  // Tri par nom
                } elseif ($filterBy == 'date') {
                    $query->orderBy('created_at', $request->get('order_by', 'asc'));  // Tri par date
                }
            } else {
                $query->orderBy('created_at', 'asc');  // Par défaut, trier par date ascendante
            }

            $achatFiles = $query->get();

            // Enregistrer l'ID du dossier dans la session
            session(['foldersId' => $id]);

            // Récupérer l'ID du dossier de la session
            $foldersId = session('foldersId');

            // Liste des notifications pour les dossiers
            $folderNotifications = [];

            // Vérifier les messages non lus dans le dossier
            $unreadMessagesForFolder = Message::whereHas('file', function ($query) use ($foldersId) {
                // Vérifier que le fichier est dans le dossier spécifié
                $query->where('folders', $foldersId); // Vérifie que le fichier appartient au dossier
            })
            ->where('is_read', 0) // Filtrer pour les messages non lus
            ->get();

            // Si des messages non lus existent pour ce dossier, les ajouter aux notifications
            if ($unreadMessagesForFolder->count() > 0) {
                // Ajouter le nombre de messages non lus pour ce dossier
                $folderNotifications['folder_'.$id] = $unreadMessagesForFolder->count();
            }

            // Liste des notifications pour les fichiers
            $fileNotifications = [];

            foreach ($achatFiles as $file) {

                // Vérifier l'extension du fichier pour afficher une prévisualisation
                $extension = strtolower(pathinfo($file->name, PATHINFO_EXTENSION));

                if (in_array($extension, ['jpg', 'jpeg', 'png', 'gif'])) {
                    $file->preview = asset('storage/' . $file->path); // Image
                } elseif (in_array($extension, ['pdf'])) {
                    $file->preview = 'https://via.placeholder.com/80x100.png?text=PDF'; // PDF
                } elseif (in_array($extension, ['doc', 'docx'])) {
                    $file->preview = 'https://via.placeholder.com/80x100.png?text=Word'; // Word
                } elseif (in_array($extension, ['xls', 'xlsx'])) {
                    $file->preview = 'https://via.placeholder.com/80x100.png?text=Excel'; // Excel
                } else {
                    $file->preview = 'https://via.placeholder.com/80x100.png?text=Fichier'; // Fichier générique
                }

                // Vérifier si un message existe pour ce fichier et si le champ 'is_read' est égal à 0
                $unreadMessagesForFile = Message::where('file_id', $file->id)
                                                ->where('is_read', 0)
                                                ->get();

                // Si des messages non lus existent pour ce fichier, les ajouter aux notifications
                if ($unreadMessagesForFile->count() > 0) {
                    $fileNotifications[$file->id] = $unreadMessagesForFile->count(); // Stocker le nombre de messages non lus avec l'ID du fichier
                }
            }

            // Retourner la vue avec les fichiers, dossiers et notifications
             return view('folders', compact('achatFiles', 'folders', 'foldersId', 'folder', 'fileNotifications', 'folderNotifications'));
        } else {
            // Rediriger si aucune société n'est trouvée dans la session
            return redirect()->route('home')->with('error', 'Aucune société trouvée dans la session');
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
    //    dd($request);
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

       // Supprimer le dossier
       $folder->delete();

       // Retourner une réponse de succès
       return redirect()->back()->with('success', 'Dossier et fichiers supprimés avec succès.');
   }





}

