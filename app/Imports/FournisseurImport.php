<?php
namespace App\Imports;

use App\Models\Fournisseur;
use Maatwebsite\Excel\Concerns\ToModel;

class FournisseurImport implements ToModel
{
    protected $colonne_compte;
    protected $colonne_intitule;
    protected $colonne_identifiant_fiscal;
    protected $colonne_ICE;
    protected $colonne_nature_operation;
    protected $colonne_rubrique_tva;
    protected $colonne_designation;
    protected $colonne_contre_partie;

    public function __construct($colonne_compte, $colonne_intitule, $colonne_identifiant_fiscal, $colonne_ICE, $colonne_nature_operation, $colonne_rubrique_tva, $colonne_designation, $colonne_contre_partie)
    {
        $this->colonne_compte = $colonne_compte;
        $this->colonne_intitule = $colonne_intitule;
        $this->colonne_identifiant_fiscal = $colonne_identifiant_fiscal;
        $this->colonne_ICE = $colonne_ICE;
        $this->colonne_nature_operation = $colonne_nature_operation;
        $this->colonne_rubrique_tva = $colonne_rubrique_tva;
        $this->colonne_designation = $colonne_designation;
        $this->colonne_contre_partie = $colonne_contre_partie;
    }

    public function model(array $row)
    {
        return new Fournisseur([
            'compte' => $row[$this->colonne_compte - 1],
            'intitule' => $row[$this->colonne_intitule - 1],
            'identifiant_fiscal' => $row[$this->colonne_identifiant_fiscal - 1],
            'ICE' => $row[$this->colonne_ICE - 1],
            'nature_operation' => $row[$this->colonne_nature_operation - 1],
            'rubrique_tva' => $row[$this->colonne_rubrique_tva - 1],
            'designation' => $row[$this->colonne_designation - 1],
            'contre_partie' => $row[$this->colonne_contre_partie - 1],
        ]);

        return Fournisseur::updateOrCreate(
            ['compte' => $row[$this->colonne_compte - 1]], // Identifiant unique pour vérifier les doublons
            [
                'intitule' => $row[$this->colonne_intitule - 1],
                'identifiant_fiscal' => $row[$this->colonne_identifiant_fiscal - 1],
                'ICE' => $row[$this->colonne_ICE - 1],
                'nature_operation' => $row[$this->colonne_nature_operation - 1],
                'rubrique_tva' => $row[$this->colonne_rubrique_tva - 1],
                'designation' => $row[$this->colonne_designation - 1],
                'contre_partie' => $row[$this->colonne_contre_partie - 1],
            ]
        );

    }

  

}
