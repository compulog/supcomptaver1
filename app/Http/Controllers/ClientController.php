<?php

namespace App\Http\Controllers;
use App\Imports\ClientsImport;

use Maatwebsite\Excel\Facades\Excel;
use App\Models\Client;
use Illuminate\Http\Request;

class ClientController extends Controller
{
    // Afficher la liste des clients
    public function index()
    {
        $clients = Client::all();
        return view('client', compact('clients'));
    }

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

    public function edit($id)
    {
        $client = Client::findOrFail($id); // Trouver le client ou lever une exception 404
        return view('clients.edit', compact('client')); // Passer la variable $client à la vue
    }

    public function update(Request $request, Client $client)
    {
        // Validez les données
        $request->validate([
            'compte' => 'required',
            'intitule' => 'required',
            'identifiant_fiscal' => 'required',
            'ICE' => 'required',
            'type_client' => 'required',
        ]);
    
        // Mettez à jour le client
        $client->update($request->all());
    
        return response()->json([
            'success' => true,
            'client' => $client
        ]);
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
    try {
        Log::info('Importing file: ' . $request->file('file')->getClientOriginalName());
        Excel::import(new ClientsImport, $request->file('file'));
        return response()->json(['success' => true]);
    } catch (\Exception $e) {
        Log::error('Import failed: ' . $e->getMessage());
        return response()->json(['success' => false, 'error' => $e->getMessage()]);
    }
}

    

}
