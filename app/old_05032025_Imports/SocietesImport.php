<?php

namespace App\Imports;

use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithStartRow;
use App\Models\Societe;
use App\Models\RegimeDeclarationTva;
use App\Models\FaitGenerateurTva; // Importer le modèle FaitGenerateurTva
use App\Models\NatureActivite; // Importer le modèle NatureActivite

class SocietesImport implements ToModel, WithStartRow
{
    protected $mappings;

    public function __construct($mappings)
    {
        $this->mappings = $mappings;
    }

    public function startRow(): int
    {
        return 2; // Commencer à la ligne 2 pour ignorer la ligne 1 (les en-têtes)
    }

    public function model(array $row)
    {
        // dd($row);
        // Récupérer la valeur de la raison sociale
        $raisonSociale = $this->getValue($row, 'raison_sociale');

        // Vérifier si la société existe déjà
        $societe = Societe::where('raison_sociale', $raisonSociale)->first();

        // Récupérer la valeur de 'regime_declaration'
        $regimeDeclaration = $this->getValue($row, 'regime_declaration');

        // Si le champ 'regime_declaration' est un numéro
        if (is_numeric($regimeDeclaration)) {
            // Chercher dans la table 'regimes_declaration_tva' si un numéro existe
            $regime = RegimeDeclarationTva::where('numero', $regimeDeclaration)->first();
            
            // Si un régime est trouvé, on enregistre sa description
            if ($regime) {
                $regimeDeclaration = $regime->description;
            }
        }

        // Récupérer la valeur de 'nature_activite'
        $natureActivite = $this->getValue($row, 'nature_activite');

        // Si le champ 'nature_activite' est un numéro
        if (is_numeric($natureActivite)) {
            // Chercher dans la table 'nature_activites' si un numéro existe
            $nature = NatureActivite::where('numero', $natureActivite)->first();

            // Si une nature d'activité est trouvée, on enregistre sa description
            if ($nature) {
                $natureActivite = $nature->description;
            }
        }

        // Récupérer la valeur de 'fait_generateur'
        $faitGenerateur = $this->getValue($row, 'fait_generateur');

        // Si le champ 'fait_generateur' est un numéro
        if (is_numeric($faitGenerateur)) {
            // Chercher dans la table 'fait_generateur_tva' si un numéro existe
            $fait = FaitGenerateurTva::where('numero', $faitGenerateur)->first();

            // Si un fait générateur est trouvé, on enregistre sa description
            if ($fait) {
                $faitGenerateur = $fait->description;
            }
        }

        // Si la société existe déjà, on met à jour ses informations
        if ($societe) {
            $societe->forme_juridique = $this->getValue($row, 'forme_juridique');
            $societe->siege_social = $this->getValue($row, 'siege_social');
            $societe->patente = $this->getValue($row, 'patente');
            $societe->rc = $this->getValue($row, 'rc');
            $societe->centre_rc = $this->getValue($row, 'centre_rc');
            $societe->identifiant_fiscal = $this->getValue($row, 'identifiant_fiscal');
            $societe->ice = $this->getValue($row, 'ice');
            $societe->date_creation = $this->getValue($row, 'date_creation');
            $societe->exercice_social_debut = $this->getValue($row, 'exercice_social_debut');
            $societe->exercice_social_fin = $this->getValue($row, 'exercice_social_fin');
            $societe->modele_comptable = $this->getValue($row, 'modele_comptable');
            $societe->nombre_chiffre_compte = $this->getValue($row, 'nombre_chiffre_compte');
            $societe->nature_activite = $natureActivite; // Mise à jour avec la description de la nature d'activité
            $societe->activite = $this->getValue($row, 'activite');
            $societe->assujettie_partielle_tva = $this->getValue($row, 'assujettie_partielle_tva');
            $societe->prorata_de_deduction = $this->getValue($row, 'prorata_de_deduction');
            $societe->regime_declaration = $regimeDeclaration; // Mise à jour avec la description du régime
            $societe->fait_generateur = $faitGenerateur; // Mise à jour avec la description du fait générateur
            $societe->rubrique_tva = $this->getValue($row, 'rubrique_tva');
            $societe->designation = $this->getValue($row, 'designation');
            $validatedData['created_by_user_id'] = auth()->id(); // L'ID de l'utilisateur connecté
            $societe->cnss = $this->getValue($row, 'cnss');
            
            $societe->save(); // Enregistrer les modifications
        } else {
            // Si la société n'existe pas, on la crée
            $societe = new Societe([ 
                'raison_sociale' => $raisonSociale,
                'forme_juridique' => $this->getValue($row, 'forme_juridique'),
                'siege_social' => $this->getValue($row, 'siege_social'),
                'patente' => $this->getValue($row, 'patente'),
                'rc' => $this->getValue($row, 'rc'),
                'centre_rc' => $this->getValue($row, 'centre_rc'),
                'identifiant_fiscal' => $this->getValue($row, 'identifiant_fiscal'),
                'ice' => $this->getValue($row, 'ice'),
                'date_creation' => $this->getValue($row, 'date_creation'),
                'exercice_social_debut' => $this->getValue($row, 'exercice_social_debut'),
                'exercice_social_fin' => $this->getValue($row, 'exercice_social_fin'),
                'modele_comptable' => $this->getValue($row, 'modele_comptable'),
                'nombre_chiffre_compte' => $this->getValue($row, 'nombre_chiffre_compte'),
                'nature_activite' => $natureActivite, // Ajout de la nature d'activité
                'activite' => $this->getValue($row, 'activite'),
                'assujettie_partielle_tva' => $this->getValue($row, 'assujettie_partielle_tva'),
                'prorata_de_deduction' => $this->getValue($row, 'prorata_de_deduction'),
                'regime_declaration' => $regimeDeclaration,
                'fait_generateur' => $faitGenerateur, // Ajout de la description du fait générateur
                'rubrique_tva' => $this->getValue($row, 'rubrique_tva'),
                'designation' => $this->getValue($row, 'designation'),
                'created_by_user_id' => auth()->id(),  // Ici vous assurez que la valeur est bien insérée
                'designation' => $this->getValue($row, 'designation'),
                'cnss' => $this->getValue($row, 'cnss'), 
               
            ]);
            $societe->save(); // Enregistrer la nouvelle société
        }

        return $societe; // Retourne l'objet Societe (ou mis à jour ou créé)
    }

    private function getValue(array $row, $field)
    {
        $columnIndex = $this->mappings[$field] - 1;

        if ($this->mappings[$field] == 0 || !isset($row[$columnIndex])) {
            return 0;
        }

        return $row[$columnIndex] ?? 0;
    }
}
