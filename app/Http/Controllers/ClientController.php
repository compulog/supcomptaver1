<?php

namespace App\Http\Controllers;

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

    public function update(Request $request, $id) {
        $client = Client::findOrFail($id);
        $client->update($request->all());
    
        return response()->json(['success' => true, 'client' => $client]);
    }
    
    


    
    public function destroy($id) {
        \Log::info('Attempting to delete client with ID: ' . $id);
    
        $client = Client::find($id);
        if ($client) {
            $client->delete();
            \Log::info('Client deleted successfully');
            return response()->json(['success' => true]);
        }
    
        \Log::error('Client not found');
        return response()->json(['success' => false, 'error' => 'Client not found']);
    }
    
    

}
