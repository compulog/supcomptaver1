<?php
namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Session;
class SessionsController extends Controller
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
    public function store(Request $request)
    {
       
        $attributes = request()->validate([
            'name'=>'required',
            'password'=>'required'
        ]);

        if(Auth::attempt($attributes))
        {  $dbName = $request->input('database');

        
            $user = Auth::user();
            if($dbName===null){

                $dbName=$user->BaseName;

            }

            $envPath = base_path('.env');
            $envContent = file_get_contents($envPath);
            $updatedEnvContent = preg_replace(
                '/DB_SECOND_DATABASE=.*$/m',
                'DB_SECOND_DATABASE=' . $dbName,
                $envContent
            );
            file_put_contents($envPath, $updatedEnvContent);
        
            // Reconnect to the database with the new configuration.
            config(['database.connections.supcompta.database' => $dbName]);
            // dd($databaseName);
            DB::reconnect('supcompta');
            session()->regenerate();
            session(['database' => $dbName]);
            // dd(session('database'));
           // Supposons que l'utilisateur est déjà authentifié et que vous avez accès à l'objet $user.
if ($user->type === 'interlocuteurs') {
    // Récupérer l'ID de la société associée à l'utilisateur
    $societeId = \App\Models\Societe::where('user_id', $user->id)->value('id');

    // Si une société existe pour cet utilisateur, rediriger vers l'exercice correspondant à l'ID de la société
    if ($societeId) {
        return redirect("exercices/{$societeId}")->with(['success' => 'You are logged in.' . $dbName]);
    } else {
        // Gérer le cas où l'utilisateur n'a pas de société associée
        return redirect('error')->with(['error' => 'No company associated with this user.']);
    }
} else {
    return redirect('dashboard')->with(['success' => 'You are logged in.' . $dbName]);
}
        }
        else{

            return back()->withErrors(['name'=>'le nom ou email incorrect.']);
        }
    }
    

    public function create()
    {
       
            // Si BaseName est 'compulog', récupérer les bases de données qui commencent par 'supcompta'
            $databases = DB::select("SHOW DATABASES LIKE 'supcompta%'");

            // // Extraire les noms des bases de données
            // $dbNames = collect($databases)->pluck('Database')->toArray();
            $dbNames = [];
            foreach ($databases as $db) {
                // Vérifier la structure exacte du résultat
                // if (isset($db->Database)) {
                    $dbNames[] = $db->{'Database (supcompta%)'};
                // }
            }
          
        // dd($dbNames);
        return view('session.login-session',compact('dbNames'));
    }

    public function destroy()
    {
        Auth::logout();
        return redirect('/login')->with(['success' => 'You\'ve been logged out.']);
    }
}
