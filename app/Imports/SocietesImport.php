<?php

namespace App\Imports;

use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithStartRow;
use App\Models\Societe;

class SocietesImport implements ToModel, WithStartRow
{
    protected $mappings;

    public function __construct($mappings)
    {
        $this->mappings = $mappings;
    }

    /**
     * Définir la ligne à partir de laquelle commencer l'importation
     * Ici, on commence à partir de la ligne 2 (pour ignorer la première ligne)
     */
    public function startRow(): int
    {
        return 2; // Commencer à la ligne 2 pour ignorer la ligne 1 (les en-têtes)
    }

    public function model(array $row)
    {
        // Récupérer la valeur de la raison sociale
        $raisonSociale = $this->getValue($row, 'raison_sociale');

        // Vérifier si la société existe déjà
        $societe = Societe::where('raison_sociale', $raisonSociale)->first();

        if ($societe) {
            // Si la société existe, on la met à jour
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
            $societe->nature_activite = $this->getValue($row, 'nature_activite');
            $societe->activite = $this->getValue($row, 'activite');
            $societe->assujettie_partielle_tva = $this->getValue($row, 'assujettie_partielle_tva');
            $societe->prorata_de_deduction = $this->getValue($row, 'prorata_de_deduction');
            $societe->regime_declaration = $this->getValue($row, 'regime_declaration');
            $societe->fait_generateur = $this->getValue($row, 'fait_generateur');
            $societe->rubrique_tva = $this->getValue($row, 'rubrique_tva');
            $societe->designation = $this->getValue($row, 'designation');
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
                'nature_activite' => $this->getValue($row, 'nature_activite'),
                'activite' => $this->getValue($row, 'activite'),
                'assujettie_partielle_tva' => $this->getValue($row, 'assujettie_partielle_tva'),
                'prorata_de_deduction' => $this->getValue($row, 'prorata_de_deduction'),
                'regime_declaration' => $this->getValue($row, 'regime_declaration'),
                'fait_generateur' => $this->getValue($row, 'fait_generateur'),
                'rubrique_tva' => $this->getValue($row, 'rubrique_tva'),
                'designation' => $this->getValue($row, 'designation'),
            ]);
            $societe->save(); // Enregistrer la nouvelle société
        }

        return $societe; // Retourne l'objet Societe (ou mis à jour ou créé)
    }

    /**
     * Fonction pour obtenir la valeur d'un champ, ou 0 si le champ n'existe pas dans le mapping.
     *
     * @param array $row
     * @param string $field
     * @return mixed
     */
    private function getValue(array $row, $field)
    {
        $columnIndex = $this->mappings[$field] - 1; // Conversion de l'indice pour correspondre à l'index du tableau (commence à 0)

        // Si le champ est configuré avec 0 ou s'il n'existe pas dans la ligne, on retourne 0
        if ($this->mappings[$field] == 0 || !isset($row[$columnIndex])) {
            return 0;
        }

        return $row[$columnIndex] ?? 0; // Retourne la valeur ou 0 si la valeur est vide
    }
}
