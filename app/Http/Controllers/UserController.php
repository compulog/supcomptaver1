<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;

class UserController extends Controller
{
    // Méthode pour ajouter un utilisateur
    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'password' => 'required|string|min:8',
            'phone' => 'required|string|max:15',
            'location' => 'required|string|max:255',
            'about_me' => 'required|string',
            'type' => 'required|in:admin,utilisateur',
            'baseName' => 'required|string|max:255',
        ]);
    
        // Créer un nouvel utilisateur
        User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => bcrypt($request->password), // Crypter le mot de passe
            'phone' => $request->phone,
            'location' => $request->location,
            'about_me' => $request->about_me,
            'type' => $request->type,
            'BaseName' => $request->baseName,
        ]);
    
        return response()->json(['message' => 'Utilisateur ajouté avec succès'], 201);
    }
    
}
