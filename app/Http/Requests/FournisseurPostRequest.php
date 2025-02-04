<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use App\Models\Fournisseur;
use Illuminate\Validation\ValidationException;

class FournisseurPostRequest extends FormRequest
{

    public function Stores(FournisseurPostRequest $request)
    {
        try {
            $fournisseurs = new Fournisseur();
            
            // Assurez-vous que les champs correspondent à votre table
            $fournisseurs->compte = $request->input('compte');
            $fournisseurs->intitule = $request->input('intitule');
            $fournisseurs->identifiant_fiscal = $request->input('identifiant_fiscal');
            $fournisseurs->ICE = $request->input('ICE');
            $fournisseurs->nature_operation = $request->input('nature_operation');
            $fournisseurs->rubrique_tva = $request->input('rubrique_tva');
            $fournisseurs->designation = $request->input('designation');
            $fournisseurs->contre_partie = $request->input('contre_partie');
    
            // Essayez de sauvegarder les données
            $fournisseurs->save();
    
            return response()->json([
                'status' => 200,
                'message' => 'Ajouté avec succès',
            ]);
        } catch (ValidationException $e) {
            return response()->json([
                'status' => 422,
                'message' => 'Validation échouée',
                'errors' => $e->errors(),
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 500,
                'message' => 'Une erreur s\'est produite. ' . $e->getMessage(),
            ]);
        }
    }
    
    


    public function authorize()
    {
        return true; // Autorise tous les utilisateurs à faire cette requête, vous pouvez le modifier selon vos besoins
    }

    public function rules()
    {
        return [
            'compte' => 'required|string|max:255', // Champ compte requis, chaîne de caractères, maximum 255 caractères
            'intitule' => 'required|string|max:255', // Champ intitule requis
            'identifiant_fiscal' => 'required|string|max:255', // Champ identifiant_fiscal requis
            'ICE' => 'required|string|max:255', // Champ ICE requis
            'nature_operation' => 'required|string|max:255', // Champ nature_operation requis
            'rubrique_tva' => 'required|string|max:255', // Champ rubrique_tva requis et doit exister dans la table racines
            'designation' => 'nullable|string|max:255', // Champ designation facultatif
            'contre_partie' => 'nullable|string|max:255', // Champ contre_partie facultatif
        ];
    }

    public function messages()
    {
        return [
            'compte.required' => 'Le champ compte est obligatoire.',
            'intitule.required' => 'Le champ intitule est obligatoire.',
            'identifiant_fiscal.required' => 'Le champ identifiant fiscal est obligatoire.',
            'ICE.required' => 'Le champ ICE est obligatoire.',
            'nature_operation.required' => 'Le champ nature d\'opération est obligatoire.',
            'rubrique_tva.required' => 'Le champ rubrique TVA est obligatoire.',
            
            'designation.max' => 'Le champ désignation ne peut pas dépasser 255 caractères.',
            'contre_partie.max' => 'Le champ contrepartie ne peut pas dépasser 255 caractères.',
        ];
    }
}
