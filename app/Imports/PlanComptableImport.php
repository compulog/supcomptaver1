<?php

namespace App\Imports;

use App\Models\PlanComptable;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithStartRow; // Importer l'interface WithStartRow

class PlanComptableImport implements ToModel, WithStartRow // Implémentez l'interface WithStartRow
{
    protected $colonne_compte;
    protected $colonne_intitule;

    public function __construct($colonne_compte, $colonne_intitule)
    {
        $this->colonne_compte = $colonne_compte;
        $this->colonne_intitule = $colonne_intitule;
    }

    // Indique à partir de quelle ligne commencer l'importation
    public function startRow(): int
    {
        return 2; // Commence à la deuxième ligne
    }

    public function model(array $row)
    {
        // Assurez-vous de traiter les colonnes sans en-tête
        return new PlanComptable([
            'compte' => $row[$this->colonne_compte - 1], // Soustrayez 1 pour un index de tableau
            'intitule' => $row[$this->colonne_intitule - 1], // Soustrayez 1 pour un index de tableau
        ]);
    }
}
