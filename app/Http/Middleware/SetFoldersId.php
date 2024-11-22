<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use App\Models\Folder; // Assurez-vous d'importer la classe Folder avec un "F" majuscule

class SetFoldersId
{
    public function handle(Request $request, Closure $next)
    {
        $foldersId = session('foldersId');

        if ($foldersId) {
            $folders = Folder::find($foldersId); // Utiliser Folder avec un "F" majuscule
            view()->share('folders', $folders);
        }

        return $next($request);
    }
}
