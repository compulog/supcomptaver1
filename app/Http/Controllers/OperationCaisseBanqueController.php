<?php

namespace App\Http\Controllers;
 
use App\Models\PlanComptable;
use Illuminate\Http\Request;
use App\Models\Societe;
use App\Models\OperationCourante;

class OperationCaisseBanqueController extends Controller
{

    public function destroy(Request $request)
{                
    $societeId = session('societeId');

    // Vérifier si le societeId est valide, sinon renvoyer une erreur
    if (!$societeId) {
        return response()->json(['error' => 'Societe ID non trouvé dans la session.'], 400);
    }

    // Validation des IDs des opérations à supprimer
    $validatedData = $request->validate([
        'ids' => 'required|array', // S'assurer que 'ids' est un tableau
        'ids.*' => 'exists:operation_courante,id', // Vérifier que chaque ID existe
    ]);

    // Supprimer les opérations
    OperationCourante::whereIn('id', $validatedData['ids'])
                     ->where('societe_id', $societeId)
                     ->delete();

    // Retourner une réponse JSON
    return response()->json(['message' => 'Opérations supprimées avec succès.']);
}

    // public function store(Request $request)
    // {
    //     // Récupérer le societe_id depuis la session
    //     $societeId = session('societeId');
    
    //     // Validation des données
    //     $validatedData = $request->validate([
    //         'date' => 'required|date',
    //         'numero_dossier' => 'nullable|string',
    //         'fact_lettrer' => 'nullable|string',
    //         'compte' => 'nullable|string',
    //         'libelle' => 'nullable|string',
    //         'debit' => 'nullable|numeric',
    //         'credit' => 'nullable|numeric',
    //         'contre_partie' => 'nullable|string',
    //         'piece_justificative' => 'nullable|string',
    //         'taux_ras_tva' => 'nullable|string',
    //         'nature_op' => 'nullable|string',
    //         'date_lettrage' => 'nullable|date',
    //         'mode_pay' => 'nullable|string',
    //         'type_journal' => 'nullable|numeric',
            
    //     ]);
        
    //     // Ajouter le champ numero_facture avec la valeur par défaut
    //     $validatedData['numero_facture'] = 'pas de facture';
    
    //     // Ajouter le societe_id récupéré de la session
    //     $validatedData['societe_id'] = $societeId;
    //     $validatedData['categorie'] = 'caisse';
    //     // Assurez-vous que les données sont bien formatées avant de les enregistrer
    //     OperationCourante::create($validatedData);
    
    //     // Retourner une réponse JSON
    //     return response()->json(['message' => 'Données enregistrées avec succès.']);
    // }
    

    public function store(Request $request)
    {
        // Récupérer le societe_id depuis la session
        $societeId = session('societeId');
    
        // Validation des données
        $validatedData = $request->validate([
            'id' => 'nullable|exists:operation_courantes,id', // Validation pour l'ID
            'date' => 'required|date',
            'numero_dossier' => 'nullable|string',
            'fact_lettrer' => 'nullable|string',
            'compte' => 'nullable|string',
            'libelle' => 'nullable|string',
            'debit' => 'nullable|numeric',
            'credit' => 'nullable|numeric',
            'contre_partie' => 'nullable|string',
            'piece_justificative' => 'nullable|string',
            'taux_ras_tva' => 'nullable|string',
            'nature_op' => 'nullable|string',
            'date_lettrage' => 'nullable|date',
            'mode_pay' => 'nullable|string',
            'type_journal' => 'nullable|numeric',
            'saisie_choisie' => 'required|string', 
        ]);
    
        // Ajouter le champ numero_facture avec la valeur par défaut
        $validatedData['numero_facture'] = 'pas de facture';
    
        // Ajouter le societe_id récupéré de la session
        $validatedData['societe_id'] = $societeId;
        $validatedData['categorie'] = 'caisse';
    
        // Vérifier si des opérations existantes avec le même fact_lettrer existent
        $existingOperations = OperationCourante::where('fact_lettrer', $validatedData['fact_lettrer'])
            ->where('societe_id', $societeId)
            ->get();
    
        if ($existingOperations->isNotEmpty()) {
            // Si le numéro de facture existe, mettre à jour tous les enregistrements
            foreach ($existingOperations as $existingOperation) {
                $existingOperation->update($validatedData);
            }
        } else {
            // Sinon, enregistrer l'opération principale
            $operation = OperationCourante::create($validatedData);
        }
    
        // Vérifier si la saisie choisie est "contre partie auto"
        if ($validatedData['saisie_choisie'] === 'contre-partie') {
            // Vérifier si des opérations existantes ont été mises à jour
            if ($existingOperations->isEmpty()) {
                // Créer la ligne de contrepartie uniquement si aucune opération existante n'a été mise à jour
                $contrePartieData = [
                    'date' => $validatedData['date'],
                    'fact_lettrer' => $validatedData['fact_lettrer'],
                    'compte' => $validatedData['contre_partie'], // Utiliser le champ contre_partie pour le compte
                    'contre_partie' => $validatedData['compte'],
                    'libelle' => 'Paiement ' . $validatedData['libelle'], // Libellé pour la contrepartie
                    'debit' => $validatedData['credit'], // Le crédit devient le débit pour la contrepartie
                    'credit' => $validatedData['debit'], // Le débit devient le crédit pour la contrepartie
                    'piece_justificative' => $validatedData['piece_justificative'],
                    'taux_ras_tva' => $validatedData['taux_ras_tva'],
                    'nature_op' => $validatedData['nature_op'],
                    'date_lettrage' => $validatedData['date_lettrage'],
                    'mode_pay' => $validatedData['mode_pay'],
                    'type_journal' => $validatedData['type_journal'],
                    'numero_facture' => 'pas de facture', // Ajouter le champ numero_facture avec la valeur par défaut
                    'societe_id' => $societeId,
                    'categorie' => 'caisse',
                ];
    
                // Enregistrer la ligne de contrepartie
                OperationCourante::create($contrePartieData);
            }
        }
    
        return response()->json(['message' => 'Données enregistrées avec succès.']);
    }
// public function store(Request $request)
// {
//     // Récupérer le societe_id depuis la session
//     $societeId = session('societeId');

//     // Validation des données
//     $validatedData = $request->validate([
//         'date' => 'required|date',
//         'numero_dossier' => 'nullable|string',
//         'fact_lettrer' => 'nullable|string',
//         'compte' => 'nullable|string',
//         'libelle' => 'nullable|string',
//         'debit' => 'nullable|numeric',
//         'credit' => 'nullable|numeric',
//         'contre_partie' => 'nullable|string',
//         'piece_justificative' => 'nullable|string',
//         'taux_ras_tva' => 'nullable|string',
//         'nature_op' => 'nullable|string',
//         'date_lettrage' => 'nullable|date',
//         'mode_pay' => 'nullable|string',
//         'type_journal' => 'nullable|numeric',
//         'saisie_choisie' => 'required|string',
//     ]);

//     // Ajouter le champ numero_facture avec la valeur par défaut
//     $validatedData['numero_facture'] = 'pas de facture';

//     // Ajouter le societe_id récupéré de la session
//     $validatedData['societe_id'] = $societeId;
//     $validatedData['categorie'] = 'caisse';

//     // Vérifier si une ligne avec le même numéro de facture existe déjà
//     $existingOperation = OperationCourante::where('numero_facture', $validatedData['numero_facture'])
//                                            ->where('societe_id', $societeId)
//                                            ->first();

//     if ($existingOperation) {
//         // Si une ligne existe déjà, on met à jour cette ligne avec les nouvelles données
//         $existingOperation->update($validatedData);
//     } else {
//         // Si aucune ligne n'existe, on crée une nouvelle ligne
//         $existingOperation = OperationCourante::create($validatedData);
//     }

//     // Vérifier si la saisie choisie est "contre partie auto"
//     if ($validatedData['saisie_choisie'] === 'contre-partie') {
//         // Créer la ligne de contrepartie
//         $contrePartieData = [
//             'date' => $validatedData['date'],
//             'fact_lettrer' => $validatedData['fact_lettrer'],
//             'compte' => $validatedData['contre_partie'], // Utiliser le champ contre_partie pour le compte
//             'contre_partie' => $validatedData['compte'],
//             'libelle' => 'Paiement ' . $validatedData['libelle'], // Libellé pour la contrepartie
//             'debit' => $validatedData['credit'], // Le crédit devient le débit pour la contrepartie
//             'credit' => $validatedData['debit'], // Le débit devient le crédit pour la contrepartie
//             'piece_justificative' => $validatedData['piece_justificative'],
//             'taux_ras_tva' => $validatedData['taux_ras_tva'],
//             'nature_op' => $validatedData['nature_op'],
//             'date_lettrage' => $validatedData['date_lettrage'],
//             'mode_pay' => $validatedData['mode_pay'],
//             'type_journal' => $validatedData['type_journal'],
//             'numero_facture' => 'pas de facture', // Ajouter le champ numero_facture avec la valeur par défaut
//             'societe_id' => $societeId,
//             'categorie' => 'caisse',
//         ];

//         // Enregistrer la ligne de contrepartie
//         OperationCourante::create($contrePartieData);
//     }

//     return response()->json(['message' => 'Données enregistrées avec succès.']);
// }
public function storeBanque(Request $request)
{
//   dd($request->all());
    // Récupérer le societe_id depuis la session
    $societeId = session('societeId');
   
    // Validation des données
    $validatedData = $request->validate([
        'date' => 'required|date',
        'numero_dossier' => 'nullable|string',
        'fact_lettrer' => 'nullable|string',
        'compte' => 'nullable|string',
        'libelle' => 'nullable|string',
        'debit' => 'nullable|numeric',
        'credit' => 'nullable|numeric',
        'contre_partie' => 'nullable|string',
        'piece_justificative' => 'nullable|string',
        'taux_ras_tva' => 'nullable|string',
        'nature_op' => 'nullable|string',
        'date_lettrage' => 'nullable|date',
        'mode_pay' => 'nullable|string',
        'type_journal' => 'nullable|numeric',
        'saisie_choisie' => 'required|string', 
    ]);

    // Ajouter le champ numero_facture avec la valeur par défaut
    $validatedData['numero_facture'] = 'pas de facture';

    // Ajouter le societe_id récupéré de la session
    $validatedData['societe_id'] = $societeId;
    $validatedData['categorie'] = 'Banque';

    // Vérifier si le numéro de facture existe déjà
    $existingOperations = OperationCourante::where('fact_lettrer', $validatedData['fact_lettrer'])
        ->where('societe_id', $societeId)
        ->get();

    if ($existingOperations->isNotEmpty()) {
        // Si le numéro de facture existe, mettre à jour tous les enregistrements
        foreach ($existingOperations as $existingOperation) {
            $existingOperation->update($validatedData);
        }
    } else {
        // Sinon, enregistrer l'opération principale
        $operation = OperationCourante::create($validatedData);
    }

    // Vérifier si la saisie choisie est "contre partie auto"
    if ($validatedData['saisie_choisie'] === 'contre-partie') {
        // Vérifier si des opérations existantes ont été mises à jour
        if ($existingOperations->isEmpty()) {
            // Créer la ligne de contrepartie uniquement si aucune opération existante n'a été mise à jour
            $contrePartieData = [
                'date' => $validatedData['date'],
                'fact_lettrer' => $validatedData['fact_lettrer'],
                'compte' => $validatedData['contre_partie'], // Utiliser le champ contre_partie pour le compte
                'contre_partie' => $validatedData['compte'],
                'libelle' => 'Paiement ' . $validatedData['libelle'], // Libellé pour la contrepartie
                'debit' => $validatedData['credit'], // Le crédit devient le débit pour la contrepartie
                'credit' => $validatedData['debit'], // Le débit devient le crédit pour la contrepartie
                'piece_justificative' => $validatedData['piece_justificative'],
                'taux_ras_tva' => $validatedData['taux_ras_tva'],
                'nature_op' => $validatedData['nature_op'],
                'date_lettrage' => $validatedData['date_lettrage'],
                'mode_pay' => $validatedData['mode_pay'],
                'type_journal' => $validatedData['type_journal'],
                'numero_facture' => 'pas de facture', // Ajouter le champ numero_facture avec la valeur par défaut
                'societe_id' => $societeId,
                'categorie' => 'Banque',
            ];

            // Enregistrer la ligne de contrepartie
            OperationCourante::create($contrePartieData);
        }
    }

    return response()->json(['message' => 'Données enregistrées avec succès.']);
}


public function getBanque(Request $request)
{
    // dd($request->all());
    // Récupérer le societe_id depuis la session
    $societeId = session('societeId');
    
    // Vérifier si le societeId est valide, sinon renvoyer une erreur
    if (!$societeId) {
        return response()->json(['error' => 'Societe ID non trouvé dans la session.'], 400);
    }

    // Récupérer les opérations courantes pour la société et avec la catégorie 'caisse'
    $operations = OperationCourante::where('societe_id', $societeId)
                                   ->where('categorie', 'banque')
                                   ->get();

    // Vérifier si des opérations ont été trouvées
    if ($operations->isEmpty()) {
        return response()->json(['message' => 'Aucune donnée trouvée pour cette société avec la catégorie "caisse".'], 404);
    }

    // Retourner les données récupérées sous forme de réponse JSON
    return response()->json($operations);
}




    public function get(Request $request)
{
    // Récupérer le societe_id depuis la session
    $societeId = session('societeId');
    
    // Vérifier si le societeId est valide, sinon renvoyer une erreur
    if (!$societeId) {
        return response()->json(['error' => 'Societe ID non trouvé dans la session.'], 400);
    }

    // Récupérer les opérations courantes pour la société et avec la catégorie 'caisse'
    $operations = OperationCourante::where('societe_id', $societeId)
                                   ->where('categorie', 'caisse')
                                   ->get();

    // Vérifier si des opérations ont été trouvées
    if ($operations->isEmpty()) {
        return response()->json(['message' => 'Aucune donnée trouvée pour cette société avec la catégorie "caisse".'], 404);
    }

    // Retourner les données récupérées sous forme de réponse JSON
    return response()->json($operations);
}


}
