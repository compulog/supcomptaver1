<?php
namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use App\Models\User;
use App\Models\DroitDacces;

class UserController extends Controller
{
    // public function edit($id)
    // {
    //     $user = User::findOrFail($id);
    //     return view('utilisateurs.edit', compact('user'));
    // }


    public function edit($id)
    {
        $user = User::findOrFail($id);  // Trouver l'utilisateur par son ID
        return response()->json($user);  // Retourner les données de l'utilisateur en JSON
    }

    public function update(Request $request, $id)
    {
        $user = User::findOrFail($id);
        
        $user->name = $request->name;
        $user->email = $request->email;
        $user->phone = $request->phone;
        $user->password = $request->password ? Hash::make($request->password) : $user->password;
         $user->type = $request->type;
    
        // Sauvegarder les changements
        $user->save();
    
        // Rediriger ou retourner une réponse JSON
        return redirect()->route('utilisateurs.index')->with('success', 'Utilisateur modifié avec succès.');
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


 // app/Http/Controllers/UserController.php
// app/Http/Controllers/UserController.php

public function index()
{
    $databaseName = DB::getDatabaseName();
    $droits = DroitDacces::all(); // Récupère tous les droits d'accès

    // Récupération des utilisateurs et rendre 'raw_password' visible
    $users = User::where('baseName', $databaseName)
                 ->get()
                 ->makeVisible('raw_password');  // Inclure raw_password dans la réponse
 
    return view('utilisateurs', compact('users','droits'));
}


 
public function store(Request $request)
{
  
    
    // Hacher le mot de passe
    $hashedPassword = Hash::make($request->password);

    // Créer un nouvel utilisateur
    $user = new User();
    $user->name = $request->name;
    $user->email = $request->email;
    $user->password = $hashedPassword;
    $user->raw_password = $request->password;  // Mot de passe non haché
    $user->phone = $request->phone;
    $user->location = $request->location;
    $user->about_me = $request->about_me;
    $user->type = $request->type;
    $user->baseName = $request->baseName;
    $user->save();

    // Récupérer l'ID de l'utilisateur créé
    $userId = $user->id;

    // Ajouter les droits d'accès pour cet utilisateur
    foreach ($request->droits as $droitId) {
        DB::table('droit_dacces_user')->insert([
            'user_id' => $userId,
            'droit_dacces_id' => $droitId,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }

    // Rediriger avec un message de succès
    return redirect()->route('utilisateurs.index')->with('success', 'Utilisateur ajouté avec succès.');
}

    
}
