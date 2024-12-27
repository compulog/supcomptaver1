<?php
namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use App\Models\User;

class UserController extends Controller
{

 // app/Http/Controllers/UserController.php
// app/Http/Controllers/UserController.php

    public function index()
    {
        $users = User::all()->makeVisible('password');
        // dd($users);
 
        return view('utilisateurs', compact('users'));
        
    }


 
    // La fonction pour enregistrer un utilisateur
    public function store(Request $request)
    {
        // Valider les données d'entrée
        // $request->validate([
        //     'name' => 'required|string|max:255',
        //     'email' => 'required|string|email|max:255|unique:users',
        //     'password' => 'required|string|min:8|confirmed',
        //     'phone' => 'required|string|max:15',
        //     'location' => 'required|string|max:255',
        //     'about_me' => 'required|string',
        //     'type' => 'required|in:admin,utilisateur',
        //     'baseName' => 'required|string|max:255',
        // ]);

        // Hacher le mot de passe
        // $hashedPassword = Hash::make($request->password);


        $User = new User();
        $User->name=$request->name;
        $User->email=$request->email;
        $User->password=$request->password;
        $User->phone=$request->phone;
        $User->location=$request->location;
        $User->about_me=$request->about_me;
        $User->type=$request->type;
        $User->baseName=$request->baseName;
        $User->save();


        // DB::table('users')->insert([
        //     'name' => $request->name,
        //     'email' => $request->email,
        //     'password' => $hashedPassword,
        //     'phone' => $request->phone,
        //     'location' => $request->location,
        //     'about_me' => $request->about_me,
        //     'type' => $request->type,
        //     'baseName' => $request->baseName,
        //     'created_at' => now(),
        //     'updated_at' => now(),
        // ]);

        // Réponse JSON pour confirmer la création
        return view('utilisateurs');
    }
}
