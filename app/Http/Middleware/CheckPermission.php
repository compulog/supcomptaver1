<?php

// app/Http/Middleware/CheckPermission.php
namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use App\Models\User;

class CheckPermission
{
    public function handle(Request $request, Closure $next, $permission)
    {
        $user = auth()->user();

        // Vérifie si l'utilisateur a la permission
        if (!$user || !$user->droitsAcces()->where('name', $permission)->exists()) {
            abort(403, 'Permission denied');
        }

        return $next($request);
    }
}

