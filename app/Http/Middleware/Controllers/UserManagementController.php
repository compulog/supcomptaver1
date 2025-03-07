<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Auth\Events\PasswordReset;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Auth;
class UserManagementController extends Controller
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

public function store(){

$attributes = request()->validate([
    'email' => 'required|unique:users,email',
    'name' =>'required|',
    'password' => 'required|confirmed|min:7',
    'picture' => 'required|mimes:jpg,jpeg,png,bmp,tiff |max:4096',
    'role_id' => 'required|exists:roles,id',
]);

$path = request()->picture->store('profile', 'public');
$attributes['picture'] = "$path";


User::create($attributes);

return redirect('users-management')->withStatus('User successfully created.');
}

}