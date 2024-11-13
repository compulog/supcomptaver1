<?php

namespace App\Imports;

use App\Models\PlanComptable;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithStartRow;

class PlanComptableImport implements ToModel, WithStartRow
{
    protected $societeId;
    protected $colonneCompte;
    protected $colonneIntitule;

    public function __construct($societeId, $colonneCompte, $colonneIntitule)
    {
        $this->societeId = $societeId;
        $this->colonneCompte = $colonneCompte;
        $this->colonneIntitule = $colonneIntitule;
    }

    // Spécifie que l'importation commence à la ligne 2
    public function startRow(): int
    {
        return 2;
    }

    public function model(array $row)
    {
        return new PlanComptable([
            'societe_id' => $this->societeId,
            'compte' => $row[$this->colonneCompte - 1], // Les index commencent à 0, donc on soustrait 1
            'intitule' => $row[$this->colonneIntitule - 1], // Idem pour le champ intitule
        ]);
    }
}


