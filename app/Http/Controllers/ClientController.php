<?php

namespace App\Http\Controllers;
use App\Imports\ClientsImport;

use Maatwebsite\Excel\Facades\Excel;
use App\Models\Client;
use Illuminate\Http\Request;

class ClientController extends Controller
{
    
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
        $clients = Client::all(); // Récupérer tous les clients
    $societeId = session('societeId'); // Récupérer l'ID de la société de la session
    
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
    

    public function import(Request $request)
    {
        // Validation   
        $request->validate([
            'excel-file' => 'required|mimes:xlsx,xls,csv|max:2048',
        ]);
    
        try {
            Excel::import(new ClientsImport, $request->file('excel-file'));
            return response()->json(['success' => true, 'message' => 'Importation réussie !']);
        }catch (\Exception $e) {
            // Gérer l'exception en retournant un message d'erreur
            return redirect()->back()->with('error', 'Erreur lors de l\'importation : ' . $e->getMessage());
        }
    }
    
    public function importClients(Request $request)
    {
        $request->validate([
            'file' => 'required|mimes:xlsx,xls,csv',
            'mapping' => 'required|array', // Assurez-vous que le mapping est fourni
        ]);

        $mapping = $request->input('mapping'); // Récupérez le mapping depuis la requête

        Excel::import(new ClientsImport($mapping), $request->file('file'));

        return redirect()->back()->with('success', 'Clients imported successfully.');
    }

}
