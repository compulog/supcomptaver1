<?php
namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use App\Models\User;
use App\Models\DroitDacces;

 
 
class AdminController extends Controller
{

   // Méthode pour afficher un utilisateur
public function show($id)
{
    $user = User::findOrFail($id); // Récupère l'utilisateur par son ID
    
    return response()->json($user); // Retourne les données de l'utilisateur sous forme de JSON
}

// public function show($id)
// {
//     // Récupérer l'utilisateur par son ID
//     $user = User::findOrFail($id);

//     // Récupérer tous les droits d'accès
//     $droitsDacces = DroitDacces::all();

//     // Retourner les données de l'utilisateur et les droits d'accès sous forme de JSON
//     return response()->json([
//         'user' => $user,
//         'droits_dacces' => $droitsDacces
//     ]);
// }

public function update(Request $request, $id)
{
    // Mise à jour des informations utilisateur
    $user = User::findOrFail($id);
    $user->update($request->only(['name', 'email', 'phone', 'location', 'about_me']));

    return view('admin');
}




    public function destroy($id)
{
    // Trouver le client par son ID
    $users = User::find($id);

    // Si le client existe, le supprimer
    if ($users) {
        $users->delete();
        return response()->json(['success' => true]); // Retour de la réponse JSON pour indiquer que la suppression a réussi
    }

    // Si le client n'existe pas, retourner une réponse d'erreur
    return response()->json(['success' => false, 'message' => 'utilisateur non trouvé.'], 404);
}


public function index()
{
    // Récupérer le nom de la base de données connectée
    $databaseName = DB::getDatabaseName();
    $droits = DroitDacces::all(); // Récupère tous les droits d'accès

    // Récupérer les utilisateurs dont la colonne 'baseName' correspond au nom de la base de données actuelle
    // et dont le type est 'utilisateur'
    $users = User::where('baseName', $databaseName)
->whereIn('type', ['utilisateur', 'admin'])
                 ->get()
                 ->makeVisible(['password', 'raw_password']); // Rendre 'raw_password' et 'password' visibles

    // Retourner la vue avec les utilisateurs filtrés
    return view('Admin', compact('users','droits'));
}




}
