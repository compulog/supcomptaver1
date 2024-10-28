<?php

namespace App\Imports;

namespace App\Imports;
use PhpOffice\PhpSpreadsheet\Shared\Date;
use App\Models\Societe;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithHeadingRow;

class SocietesImport implements ToModel, WithHeadingRow
{
    public function model(array $row)
    {
        $exerciceDebut = Date::excelToDateTimeObject($row['exercice_social_debut']);
    $exerciceFin = Date::excelToDateTimeObject($row['exercice_social_fin']);
    $dateCreation = Date::excelToDateTimeObject($row['date_creation']);
    $faitGenerateur = Date::excelToDateTimeObject($row['fait_generateur']); // Ajouter cette ligne

    // Assurez-vous que les dates sont valides
    if (!$exerciceDebut || !$exerciceFin || !$dateCreation) {
        throw new \Exception('Invalid date format for exercise period.');
    }

        return new Societe([
            'raison_sociale' => $row['raison_sociale'] ?? null,
        'siege_social' => $row['siege_social'] ?? null,
        'ice' => $row['ice'] ?? null,
        'rc' => $row['rc'] ?? null,
        'identifiant_fiscal' => $row['identifiant_fiscal'] ?? null,
        'patente' => $row['patente'] ?? null,
        'centre_rc' => $row['centre_rc'] ?? null,
        'forme_juridique' => $row['forme_juridique'] ?? null,
        'exercice_social_debut' => $exerciceDebut,
        'exercice_social_fin' => $exerciceFin,
        'date_creation' => $dateCreation,
        'assujettie_partielle_tva' => $row['assujettie_partielle_tva'] ?? null,
        'prorata_de_deduction' => $row['prorata_de_deduction'] ?? null,
        'nature_activite' => $row['nature_activite'] ?? null,
        'activite' => $row['activite'] ?? null,
        'regime_declaration' => $row['regime_declaration'] ?? null,
        'fait_generateur' => $faitGenerateur, 
        'rubrique_tva' => $row['rubrique_tva'] ?? null,
        'designation' => $row['designation'] ?? null,
        'nombre_chiffre_compte' => $row['nombre_chiffre_compte'] ?? null,
        'modele_comptable' => $row['modele_comptable'] ?? null,

        ]);
    }

    public function headingRow(): int
    {
        return 1; // Indique que la première ligne du fichier contient les en-têtes
    }
}

