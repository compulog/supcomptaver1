<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\Storage;

class InfoUserController extends Controller
{
    public function create()
    {
        return view('laravel-examples/user-profile');
    }

    public function store(Request $request)
    {
        // Validation des champs
        $attributes = $request->validate([
            'name' => ['required', 'max:50'],
            'email' => ['required', 'email', 'max:50', Rule::unique('users')->ignore(Auth::user()->id)],
            'phone' => ['max:50'],
            'location' => ['max:70'],
            'about_me' => ['max:150'],
            'profile_image' => ['nullable', 'image', 'mimes:jpeg,png,jpg,gif', 'max:2048'], // Validation de l'image
        ]);
        

        // Si l'email est modifié et l'utilisateur est en mode démo, on interdit cette modification
        if ($request->get('email') != Auth::user()->email) {
            if (env('IS_DEMO') && Auth::user()->id == 1) {
                return redirect()->back()->withErrors(['msg2' => 'You can\'t change the email address.']);
            }
        } else {
            $attribute = $request->validate([
                'email' => ['required', 'email', 'max:50', Rule::unique('users')->ignore(Auth::user()->id)],
            ]);
        }
 
        // Si une image est téléchargée
if ($request->hasFile('profile_image')) {
    // Validation pour l'image
    $request->validate([
        'profile_image' => ['image', 'mimes:jpeg,png,jpg,gif', 'max:2048'],
    ]);

    // Récupérer l'image et la stocker
    $imagePath = $request->file('profile_image')->store('public/profile_images');

    // Extraire le nom de l'image
    $profileImage = basename($imagePath);

    // Mise à jour de l'utilisateur avec le chemin du fichier
    User::where('id', Auth::user()->id)
        ->update([
            'name' => $attributes['name'],
            'email' => $attributes['email'],
            'phone' => $attributes['phone'],
            'location' => $attributes['location'],
            'about_me' => $attributes['about_me'],
            'profile_image' => $profileImage, // Sauvegarde de l'image dans la base de données
        ]);



           
        } else {
            // Si aucune image n'est envoyée, on met simplement à jour les autres champs
            User::where('id', Auth::user()->id)
                ->update([
                    'name' => $attributes['name'],
                    'email' => $attributes['email'],
                    'phone' => $attributes['phone'],
                    'location' => $attributes['location'],
                    'about_me' => $attributes['about_me'],
                ]);
        }

        // Rediriger avec un message de succès
        return redirect('/user-profile')->with('success', 'Profile updated successfully');
    }
}
