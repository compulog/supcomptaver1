<?php

namespace App\Imports;

use App\Models\Societe;
use Maatwebsite\Excel\Concerns\ToModel;

class SocietesImport implements ToModel
{
    protected $mapping;

    public function __construct($mapping)
    {
        $this->mapping = $mapping;
    }

    public function model(array $row)
    {
        // Récupérer la raison sociale à partir de la ligne importée
        $raisonSociale = $row[$this->mapping['raison_sociale']] ?? null;

        // Vérifier si la société existe déjà
        $societe = Societe::where('raison_sociale', $raisonSociale)->first();

        // Si la société existe, mettez à jour ses informations
        if ($societe) {
            $societe->update([
                'siege_social' => $row[$this->mapping['siege_social']],
                'ice' => $row[$this->mapping['ice']],
                'rc' => $row[$this->mapping['rc']],
                'identifiant_fiscal' => $row[$this->mapping['identifiant_fiscal']],
                'patente' => $row[$this->mapping['patente']],
                'centre_rc' => $row[$this->mapping['centre_rc']],
                'forme_juridique' => $row[$this->mapping['forme_juridique']],
                'exercice_social_debut' => $row[$this->mapping['exercice_social_debut']],
                'exercice_social_fin' => $row[$this->mapping['exercice_social_fin']],
                'date_creation' => $row[$this->mapping['date_creation']],
                'assujettie_partielle_tva' => $row[$this->mapping['assujettie_partielle_tva']],
                'prorata_de_deduction' => $row[$this->mapping['prorata_de_deduction']],
                'nature_activite' => $row[$this->mapping['nature_activite']],
                'activite' => $row[$this->mapping['activite']],
                'regime_declaration' => $row[$this->mapping['regime_declaration']],
                'fait_generateur' => $row[$this->mapping['fait_generateur']],
                'rubrique_tva' => $row[$this->mapping['rubrique_tva']],
                'designation' => $row[$this->mapping['designation']],
                'nombre_chiffre_compte' => $row[$this->mapping['nombre_chiffre_compte']],
                'modele_comptable' => $row[$this->mapping['modele_comptable']],
            ]);
            return $societe; // Retourner l'instance mise à jour
        }

        // Si la société n'existe pas, en créer une nouvelle
        return new Societe([
            'raison_sociale' => $raisonSociale,
            'siege_social' => $row[$this->mapping['siege_social']],
            'ice' => $row[$this->mapping['ice']],
            'rc' => $row[$this->mapping['rc']],
            'identifiant_fiscal' => $row[$this->mapping['identifiant_fiscal']],
            'patente' => $row[$this->mapping['patente']],
            'centre_rc' => $row[$this->mapping['centre_rc']],
            'forme_juridique' => $row[$this->mapping['forme_juridique']],
            'exercice_social_debut' => $row[$this->mapping['exercice_social_debut']],
            'exercice_social_fin' => $row[$this->mapping['exercice_social_fin']],
            'date_creation' => $row[$this->mapping['date_creation']],
            'assujettie_partielle_tva' => $row[$this->mapping['assujettie_partielle_tva']],
            'prorata_de_deduction' => $row[$this->mapping['prorata_de_deduction']],
            'nature_activite' => $row[$this->mapping['nature_activite']],
            'activite' => $row[$this->mapping['activite']],
            'regime_declaration' => $row[$this->mapping['regime_declaration']],
            'fait_generateur' => $row[$this->mapping['fait_generateur']],
            'rubrique_tva' => $row[$this->mapping['rubrique_tva']],
            'designation' => $row[$this->mapping['designation']],
            'nombre_chiffre_compte' => $row[$this->mapping['nombre_chiffre_compte']],
            'modele_comptable' => $row[$this->mapping['modele_comptable']],
        ]);
    }
}
