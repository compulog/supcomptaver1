<?php

namespace App\Imports;

use App\Models\PlanComptable;
use Maatwebsite\Excel\Concerns\ToModel;

class PlanComptableImport implements ToModel
{
    protected $colonneCompte;
    protected $colonneIntitule;

    public function __construct($colonneCompte, $colonneIntitule)
    {
        $this->colonneCompte = $colonneCompte;
        $this->colonneIntitule = $colonneIntitule;
    }

    // Cette fonction est appelée pour chaque ligne du fichier Excel
    public function model(array $row)
    {
        // Si les indices sont valides dans le tableau Excel, insérez-les dans la base de données
        return new PlanComptable([
            'compte' => $row[$this->colonneCompte - 1],  // Le compte est dans la colonne spécifiée
            'intitule' => $row[$this->colonneIntitule - 1],  // L'intitulé est dans la colonne spécifiée
            'societe_id' => session('societeId'), // Associer la société de l'utilisateur
        ]);
    }
}

