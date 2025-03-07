<?php

namespace App\Http\Controllers;
use App\Imports\ClientsImport;

use Maatwebsite\Excel\Facades\Excel;
use App\Models\Client;
use App\Models\Societe;

use Illuminate\Http\Request;
use App\Exports\ClientsExport;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;

use Illuminate\Support\Facades\DB;
class ClientController extends Controller

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

    public function deleteSelected(Request $request)
{
    // Validation des données
    $request->validate([
        'ids' => 'required|array',
        'ids.*' => 'integer',  // Chaque ID doit être un entier
    ]);

    try {
        // Supprimer les lignes avec les IDs reçus
        $deletedCount = Client::whereIn('id', $request->ids)->delete();

        return response()->json([
            'status' => 'success',
            'message' => "{$deletedCount} lignes supprimées"
        ]);
    } catch (\Exception $e) {
        return response()->json([
            'status' => 'error',
            'message' => 'Erreur lors de la suppression.',
            'error' => $e->getMessage()  // Retour de l'erreur spécifique
        ]);
    }
}




    public function export(Request $request)
    {
        // Récupère l'ID de la société à partir du champ caché
        $societeId = $request->input('societe_id');

        // Effectuer l'export des clients de cette société
        return Excel::download(new ClientsExport($societeId), 'clients.xlsx');
    }
    // Afficher la liste des clients
    // public function index()
    // {
    //     $clients = Client::all();
    //     return view('client', compact('clients'));
    // }

    // Enregistrer un nouveau client

    // Enregistrer un nouveau client
    public function store(Request $request)
    {
        // Récupérer l'ID de la société depuis les données envoyées du formulaire
    $societeId = $request->input('societe_id'); // Utilisez 'societe_id' ici
    if (!$societeId) {
        return response()->json(['success' => false, 'error' => 'Aucune société sélectionnée dans la session.']);
    }

    // Valider les données du formulaire
    $validatedData = $request->validate([
        'compte' => 'required|string|max:255',
        'intitule' => 'required|string|max:255',
        'identifiant_fiscal' => 'nullable|string|max:255',
        'ICE' => 'nullable|string|max:255',
        'type_client' => 'nullable|string|max:255',
    ]);

    // Ajouter l'ID de la société aux données validées
    $validatedData['societe_id'] = $societeId;

    try {
        // Créez un nouveau client avec les données validées et l'ID de la société
        $client = Client::create($validatedData);

        // Retourner une réponse JSON avec le nouveau client
        return response()->json(['success' => true, 'client' => $client]);
        
    } catch (\Exception $e) {
        // Retourner une réponse JSON en cas d'erreur
        return response()->json(['success' => false, 'error' => $e->getMessage()]);
    }
    }


    public function index()
    {
        $societeId = session('societeId'); // Récupérer l'ID de la société de la session

        $clients = Client::where('societe_id', $societeId)->get(); // Récupérer les clients de la société

        return view('client', compact('clients', 'societeId'));
    }



   // Dans ClientController.php
   public function edit($id)
   {
       $client = Client::findOrFail($id);
       return response()->json($client);
   }





public function update(Request $request, $id)
{
    $client = Client::findOrFail($id);

    // Validez les données
    $request->validate([
        'compte' => 'required|string|max:255',
        'intitule' => 'required|string|max:255',
        'identifiant_fiscal' => 'required|string|max:255',
        'ICE' => 'nullable|string|max:15',
        'type_client' => 'required|string|max:255',
    ]);

    // Mettez à jour le client
    $client->update($request->all());

    // Réponse JSON
    return response()->json(['success' => true, 'client' => $client]);
}

public function getNextCompteForClient($societeId)
{
    // Récupérer la société
    $societe = Societe::find($societeId);
    if (!$societe) {
        Log::error("Société introuvable pour l'ID: $societeId");
        return response()->json([
            'success' => false,
            'message' => 'Société introuvable'
        ], 404);
    }

    $nombreChiffres = $societe->nombre_chiffre_compte; // Nombre de chiffres pour le compte
    $prefix = '3421'; // Préfixe des comptes clients

    Log::info("Génération du compte pour la société $societeId", [
        'nombreChiffres' => $nombreChiffres,
        'prefix' => $prefix,
    ]);

    // Vérifier que le nombre total de chiffres est suffisant
    if ($nombreChiffres < strlen($prefix) + 1) {
        Log::error("Le nombre de chiffres du compte est trop court", [
            'nombreChiffres' => $nombreChiffres,
            'prefixLength' => strlen($prefix),
        ]);
        return response()->json([
            'success' => false,
            'message' => 'Le nombre de chiffres du compte est trop court.'
        ], 400);
    }

    // Récupérer tous les comptes clients pour cette société triés par ordre croissant
    $comptesExistants = Client::where('societe_id', $societeId)
        ->where('compte', 'like', $prefix . '%')
        ->orderBy('compte', 'asc')
        ->pluck('compte')
        ->toArray();

    // Calculer le nombre de chiffres à générer après le préfixe
    $chiffresRestants = $nombreChiffres - strlen($prefix);

    Log::info("Chiffres restants à générer", ['chiffresRestants' => $chiffresRestants]);

    // Si aucun compte n'existe, retourner le premier compte
    if (empty($comptesExistants)) {
        $firstCompte = $prefix . str_pad('1', $chiffresRestants, '0', STR_PAD_LEFT);
        Log::info("Aucun compte existant, renvoi du premier compte", ['firstCompte' => $firstCompte]);
        return response()->json(['success' => true, 'nextCompte' => $firstCompte]);
    }

    // Extraire les séquences numériques des comptes existants
    $sequences = array_map(function ($compte) use ($prefix) {
        return (int) substr($compte, strlen($prefix));
    }, $comptesExistants);

    // Rechercher un trou dans la séquence
    $nextSequence = null;
    for ($i = 1; $i <= max($sequences); $i++) {
        if (!in_array($i, $sequences)) {
            $nextSequence = $i;
            break;
        }
    }

    // Si aucun trou n'est trouvé, prendre le numéro suivant après le plus grand
    if ($nextSequence === null) {
        $nextSequence = max($sequences) + 1;
    }

    // Générer le prochain compte avec le préfixe et le format approprié
    $nextCompte = $prefix . str_pad($nextSequence, $chiffresRestants, '0', STR_PAD_LEFT);

    Log::info("Compte généré", ['nextCompte' => $nextCompte]);

    return response()->json(['success' => true, 'nextCompte' => $nextCompte]);
}



public function destroy($id)
{
    // Trouver le client par son ID
    $client = Client::find($id);

    // Si le client existe, le supprimer
    if ($client) {
        $client->delete();
        return response()->json(['success' => true]); // Retour de la réponse JSON pour indiquer que la suppression a réussi
    }

    // Si le client n'existe pas, retourner une réponse d'erreur
    return response()->json(['success' => false, 'message' => 'Client non trouvé.'], 404);
}



    // public function import(Request $request)
    // {
    //     // Validation
    //     $request->validate([
    //         'excel-file' => 'required|mimes:xlsx,xls,csv|max:2048',
    //     ]);

    //     try {
    //         Excel::import(new ClientsImport, $request->file('excel-file'));
    //         return response()->json(['success' => true, 'message' => 'Importation réussie !']);
    //     } catch (\Exception $e) {
    //         Log::error('Erreur lors de l\'importation : ' . $e->getMessage());
    //         return response()->json(['success' => false, 'message' => 'Une erreur est survenue lors de l\'importation.']);
    //     }
    // }

    public function importClients(Request $request)
    {
        $request->validate([
            'file' => 'required|mimes:xlsx,xls,csv',
            'mapping' => 'required|array', // Assurez-vous que le mapping est fourni
            'societe_id' => 'required|integer', // Ajouter la validation pour societe_id

        ]);

        $mapping = $request->input('mapping'); // Récupérez le mapping depuis la requête
        $societe_id = $request->input('societe_id'); // Récupérer l'ID de la société


        Excel::import(new ClientsImport($mapping, $societe_id), $request->file('file'));

        return redirect()->back()->with('success', 'Clients imported successfully.');
    }

}
