<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
 use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use App\Models\User;
class InterlocuteursController extends Controller
{


     // Méthode pour récupérer un utilisateur pour l'édition
     public function edit($id)
     {
         $user = User::findOrFail($id); // Recherche de l'utilisateur par ID

         return response()->json($user); // Retourne les données sous forme JSON
     }
 
     // Méthode pour mettre à jour les informations de l'utilisateur
     public function update(Request $request, $id)
     {
         // Validation des données envoyées
       
     
         // Récupérer l'utilisateur à partir de son ID
         $user = User::findOrFail($id);
     
         // Vérifier les données envoyées pour le débogage
         // Vous pouvez décommenter la ligne ci-dessous pour inspecter la requête
         // dd($request->all());  // Cela va vous montrer toutes les données envoyées par le formulaire
     
         // Mettre à jour les informations de l'utilisateur
         $user->name = $request->input('name');
         $user->email = $request->input('email');
         $user->phone = $request->input('phone');
     
         // Si un mot de passe est fourni, on le met à jour
         if ($request->input('raw_password')) {
            // Si un mot de passe brut est fourni, le mettre à jour sans le hacher dans la base de données
            $user->raw_password = $request->input('raw_password');
        }
    
        // Si un mot de passe (password) est fourni, on le hache et le met à jour
        if ($request->input('raw_password')) {
            $user->password = bcrypt($request->input('raw_password')); // Crypter le mot de passe
        }
         // Enregistrer les modifications
         $user->save();
     
         // Retourner une réponse JSON
         return response()->json(['success' => true, 'message' => 'Utilisateur mis à jour avec succès']);
     }
     



    public function index()
{
    // Récupérer le nom de la base de données connectée
    $databaseName = DB::getDatabaseName();

    // Récupérer les utilisateurs dont la colonne 'baseName' correspond au nom de la base de données actuelle
    // et dont le type est 'utilisateur'
    $users = User::where('baseName', $databaseName)
                 ->where('type', 'interlocuteurs')
                 ->get()
                 ->makeVisible(['password', 'raw_password']); // Rendre 'raw_password' et 'password' visibles
// dd($users);
    // Retourner la vue avec les utilisateurs filtrés
    return view('interlocuteurs', compact('users'));
}

}
