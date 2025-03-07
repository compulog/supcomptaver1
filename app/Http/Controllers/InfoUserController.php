<?php



namespace App\Http\Controllers;



use App\Models\User;

use Illuminate\Http\Request;

use Illuminate\Support\Facades\Auth;

use Illuminate\Validation\Rule;

use Illuminate\Support\Facades\Storage;

use Illuminate\Support\Facades\DB;

use Illuminate\Support\Facades\Hash; // Correctement importé ici



class InfoUserController extends Controller

{

    public function __construct()

    {

        $this->middleware(function ($request, $next) {

            // Récupérer le nom de la base de données depuis la session.

            $dbName = session('database');

    

            if ($dbName) {

                // Définir la connexion à la base de données dynamiquement.

                config(['database.connections.mysql.database' => $dbName]);

                DB::setDefaultConnection('mysql');  // Configurer la connexion par défaut

            }

            return $next($request);

        });

    }



    public function create()

    {
        $societeId = session('societeId');

        return view('laravel-examples/user-profile', compact('societeId'));

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

            'raw_password' => ['nullable', 'string', 'min:6'], // Validation pour raw_password

            'password' => ['nullable', 'string', 'min:6'], // Validation pour le mot de passe

        ]);

    

        // Si l'email est modifié et l'utilisateur est en mode démo, on interdit cette modification

        if ($request->get('email') != Auth::user()->email) {

            if (env('IS_DEMO') && Auth::user()->id == 1) {

                return redirect()->back()->withErrors(['msg2' => 'You can\'t change the email address.']);

            }

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

    

            // Mise à jour de l'utilisateur avec le chemin du fichier et les autres informations

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

    

        // Si raw_password est fourni, on le sauvegarde tel quel dans la base de données

        if ($request->has('raw_password') && $request->raw_password) {

            User::where('id', Auth::user()->id)

                ->update([

                    'raw_password' => $request->raw_password, // Sauvegarde du mot de passe brut

                ]);

        }

    

        // Si un mot de passe est fourni (et valide), on le hache et on le met à jour dans la base de données

        if ($request->has('raw_password') && $request->raw_password) {

            // Hachage du mot de passe

            $hashedPassword = Hash::make($request->raw_password);

            

            // Mise à jour du mot de passe haché dans la base de données

            User::where('id', Auth::user()->id)

                ->update([

                    'password' => $hashedPassword, // Sauvegarde du mot de passe haché

                ]);

        }

    

        // Rediriger avec un message de succès

        return redirect('/user-profile')->with('success', 'Profile updated successfully');

    }

}

