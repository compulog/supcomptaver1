<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class SetSocieteId
{
    /**
     * Gérer une demande entrante.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle(Request $request, Closure $next)
    {
        // Vérifier si l'ID de société est présent dans la route
        if ($request->route('id')) {
            // Stocker l'ID de société dans la session
            session(['societe_id' => $request->route('id')]);
        }

        // Assurez-vous que l'ID de société existe dans la session
        if (!session()->has('societe_id')) {
            // Vous pouvez gérer cette situation selon votre logique métier, par exemple :
            // Retourner une réponse d'erreur ou rediriger
            // return response()->json(['error' => 'ID de société manquant.'], 400);
        }

        return $next($request);
    }
}
