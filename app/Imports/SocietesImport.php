<?php

namespace App\Imports;

use App\Models\Societe;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithHeadingRow;

class SocietesImport implements ToModel, WithHeadingRow
{
    public function model(array $row)
    {
        return new Societe([
            'raison_sociale' => $row['raison_sociale'],
            'siege_social' => $row['siege_social'],
            'ice' => $row['ice'],
            'rc' => $row['rc'],
            'identifiant_fiscal' => $row['identifiant_fiscal'],
            'patente' => $row['patente'],
            'centre_rc' => $row['centre_rc'],
            'forme_juridique' => $row['forme_juridique'],
            'exercice_social_debut' => $row['exercice_social_debut'],
            'exercice_social_fin' => $row['exercice_social_fin'],
            'date_creation' => $row['date_creation'],
            'assujettie_partielle_tva' => $row['assujettie_partielle_tva'],
            'prorata_de_deduction' => $row['prorata_de_deduction'],
            'nature_activite' => $row['nature_activite'],
            'activite' => $row['activite'],
            'regime_declaration' => $row['regime_declaration'],
            'fait_generateur' => $row['fait_generateur'],
            'rubrique_tva' => $row['rubrique_tva'],
            'designation' => $row['designation'],
            'nombre_chiffre_compte' => $row['nombre_chiffre_compte'],
            'modele_comptable' => $row['modele_comptable'],
        ]);
    }
}

