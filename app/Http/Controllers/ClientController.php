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
}
