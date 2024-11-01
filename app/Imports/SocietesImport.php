<?php

namespace App\Imports;

use App\Models\Societe; // Assurez-vous que ce modèle existe
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithHeadingRow;

class SocieteImport implements ToModel, WithHeadingRow
{
    protected $params;

    public function __construct(array $params)
    {
        $this->params = $params;
    }

    public function model(array $row)
    {
        return new Societe([
            'compte' => $row[$this->params['colonne_nom_entreprise']], // Nom d'entreprise
            'intitule' => $row[$this->params['colonne_forme_juridique']], // Forme Juridique
            'siege_social' => $row[$this->params['colonne_siege_social']], // Siège Social
            'patente' => $row[$this->params['colonne_patente']], // Patente
            'rc' => $row[$this->params['colonne_rc']], // RC
            'centre_rc' => $row[$this->params['colonne_centre_rc']], // Centre RC
            'identifiant_fiscal' => $row[$this->params['colonne_identifiant_fiscal']], // Identifiant Fiscal
            'ice' => $row[$this->params['colonne_ice']], // ICE
            'assujettie_partielle_tva' => $row[$this->params['colonne_assujettie_partielle_tva']], // Assujettie Partielle TVA
            'prorata_de_deduction' => $row[$this->params['colonne_prorata_de_deduction']], // Prorata de Déduction
            'date_creation' => \PhpOffice\PhpSpreadsheet\Shared\Date::excelToDateTimeObject($row[$this->params['colonne_date_creation']]), // Date de Création
            'exercice_social' => $row[$this->params['colonne_exercice_social']], // Exercice Social
            'nature_activite' => $row[$this->params['colonne_nature_activite']], // Nature de l'Activité
            'activite' => $row[$this->params['colonne_activite']], // Activité
            'regime_declaration' => $row[$this->params['colonne_regime_declaration']], // Régime de Déclaration
            'fait_generateur' => \PhpOffice\PhpSpreadsheet\Shared\Date::excelToDateTimeObject($row[$this->params['colonne_fait_generateur']]), // Fait Générateur
            'rubrique_tva' => $row[$this->params['colonne_rubrique_tva']], // Rubrique TVA
            'designation' => $row[$this->params['colonne_designation']], // Désignation
        ]);
    }
}
