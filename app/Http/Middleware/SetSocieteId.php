<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use App\Models\Societe;

class SetSocieteId
{
    public function handle(Request $request, Closure $next)
    {
        $societeId = session('societeId');

        if ($societeId) {
            $societe = Societe::find($societeId);
            view()->share('societe', $societe);
        // Ajouter l'ID de la société à la requête pour qu'il soit accessible dans le contrôleur
      
    }

        return $next($request);
    }
}
