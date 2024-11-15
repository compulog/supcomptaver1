<?php

namespace App\Http\Controllers;
use App\Imports\ClientsImport;

use Maatwebsite\Excel\Facades\Excel;
use App\Models\Client;
use Illuminate\Http\Request;
use App\Exports\ClientsExport;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
class ClientController extends Controller

{

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
    
    
    

    public function checkPassword(Request $request)
    {
        // Valider que le mot de passe est bien présent
        $request->validate([
            'password' => 'required|string',
        ]);

        // Récupérer l'utilisateur actuellement connecté
        $user = Auth::user();

        // Vérifier si le mot de passe correspond à celui de l'utilisateur
        if (Hash::check($request->password, $user->password)) {
            return response()->json(['success' => true]);
        } else {
            return response()->json(['success' => false], 401); // Mot de passe incorrect
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




    
    public function destroy($id)
    {
        $client = Client::find($id);
        if ($client) {
            $client->delete();
            return response()->json(['success' => true]);
        }
        return response()->json(['success' => false], 404);
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
