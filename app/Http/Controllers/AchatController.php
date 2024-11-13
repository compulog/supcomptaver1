<?php

namespace App\Http\Controllers;

use App\Models\File; // Assurez-vous d'importer le modèle File
use Illuminate\Http\Request;

class AchatController extends Controller
{
  public function index()
{
    $societeId = session('societeId'); // Récupère l'ID de la société depuis la session
    
    if ($societeId) {
        $files = File::where('societe_id', $societeId)->get(); // Récupère tous les fichiers associés à la société
        return view('achat', compact('files')); // Passez les fichiers à la vue
    } else {
        return redirect()->route('home')->with('error', 'Aucune société trouvée dans la session');
    }
}


}
