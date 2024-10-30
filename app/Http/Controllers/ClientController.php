<?php

namespace App\Http\Controllers;
use App\Imports\ClientsImport;

use Maatwebsite\Excel\Facades\Excel;
use App\Models\Client;
use Illuminate\Http\Request;

class ClientController extends Controller
{
    // Afficher la liste des clients
    // public function index()
    // {
    //     $clients = Client::all();
    //     return view('client', compact('clients'));
    // }

    // Enregistrer un nouveau client
    public function store(Request $request)
    {
        // Validez les données du formulaire
        $validatedData = $request->validate([
            'compte' => 'required|string|max:255',
            'intitule' => 'required|string|max:255',
            'identifiant_fiscal' => 'required|string|max:255',
            'ICE' => 'required|string|max:255',
            'type_client' => 'required|string|max:255',
        ]);

        try {
            // Créez un nouveau client avec les données validées
            $client = Client::create($validatedData);

            // Retournez une réponse JSON avec le nouveau client
            return response()->json(['success' => true, 'client' => $client]);
        } catch (\Exception $e) {
            // Retournez une réponse JSON en cas d'erreur
            return response()->json(['success' => false, 'error' => $e->getMessage()]);
        }
    }
    public function index()
    {
        $clients = Client::all(); // Récupérer tous les clients
        return view('client', compact('clients')); // Passer la collection de clients
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
    
    // Validez et mettez à jour les données
    $request->validate([
        'compte' => 'required|string',
        'intitule' => 'required|string',
        'identifiant_fiscal' => 'required|string',
        'ICE' => 'nullable|string|max:15', // Utilisez 'ICE' avec la même casse
        'type_client' => 'required|string',
    ]);
    
    

    // Mettez à jour le client avec les données fournies
    $client->update($request->all());

    return redirect()->route('clients.index')->with('success', 'Client mis à jour avec succès.');
}

    
    
public function show($id)
{
    // Récupérer le client par ID
    $client = Client::findOrFail($id);

    return response()->json($client);
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
        } catch (\Exception $e) {
            Log::error('Erreur lors de l\'importation : ' . $e->getMessage());
            return response()->json(['success' => false, 'message' => 'Une erreur est survenue lors de l\'importation.']);
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
