<?php

// app/Http/Controllers/NotificationController.php

namespace App\Http\Controllers;

use App\Models\Message;
use App\Models\Dossier;
use App\Models\SoldeMensuel;
use App\Models\File;
use App\Models\Folder;
use Illuminate\Http\Request;

class NotificationController extends Controller
{
public function supprimerNotificationMessage($id)
{
    $message = Message::withTrashed()->find($id);

    if ($message) {
        $message->is_read = 1;
        $message->save();
        return response()->json(['success' => true]);
    } else {
        return response()->json(['error' => 'Message non trouvé'], 404);
    }
}

public function supprimerNotificationDossier($id)
{
    $dossier = Dossier::withTrashed()->find($id);

    if ($dossier) {
        $dossier->is_read = 1;
        $dossier->save();
        return response()->json(['success' => true]);
    } else {
        return response()->json(['error' => 'Dossier non trouvé'], 404);
    }
}

public function supprimerNotificationSolde($id)
{
    $solde = SoldeMensuel::withTrashed()->find($id);

    if ($solde) {
        $solde->is_read = 1;
        $solde->save();
        return response()->json(['success' => true]);
    } else {
        return response()->json(['error' => 'Solde non trouvé'], 404);
    }
}

public function supprimerNotificationFichier($id)
{
    try {
        $fichier = File::withTrashed()->find($id);

        if (!$fichier) {
            return response()->json(['error' => 'Fichier non trouvé'], 404);
        }

        $fichier->is_read = 1;
        $fichier->save();

        return response()->json(['success' => true]);
    } catch (\Exception $e) {
        return response()->json([
            'error' => 'Erreur serveur',
            'message' => $e->getMessage(),
        ], 500);
    }
}


public function supprimerNotificationSousDossier($id)
{
    $sousDossier = Folder::withTrashed()->find($id);

    if ($sousDossier) {
        $sousDossier->is_read = 1;
        $sousDossier->save();

        return response()->json(['success' => true]);
    } else {
        return response()->json(['error' => 'Sous-dossier non trouvé'], 404);
    }
}

}