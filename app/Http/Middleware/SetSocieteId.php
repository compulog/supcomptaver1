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
        }

        return $next($request);
    }
}