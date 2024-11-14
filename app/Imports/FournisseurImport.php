<?php 


namespace App\Imports;

use App\Models\Fournisseur;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithStartRow;

class FournisseurImport implements ToModel, WithStartRow
{
    protected $societeId;
    protected $colonneCompte;
    protected $colonneIntitule;
    protected $colonneIdentifiantFiscal;
    protected $colonneICE;
    protected $colonneNatureOperation;
    protected $colonneRubriqueTva;
    protected $colonneDesignation;
    protected $colonneContrePartie;

    public function __construct($societeId, $colonneCompte, $colonneIntitule, $colonneIdentifiantFiscal, $colonneICE, $colonneNatureOperation, $colonneRubriqueTva, $colonneDesignation, $colonneContrePartie)
    {
        $this->societeId = $societeId;
        $this->colonneCompte = $colonneCompte;
        $this->colonneIntitule = $colonneIntitule;
        $this->colonneIdentifiantFiscal = $colonneIdentifiantFiscal;
        $this->colonneICE = $colonneICE;
        $this->colonneNatureOperation = $colonneNatureOperation;
        $this->colonneRubriqueTva = $colonneRubriqueTva;
        $this->colonneDesignation = $colonneDesignation;
        $this->colonneContrePartie = $colonneContrePartie;
    }

    // Spécifie que l'importation commence à la ligne 2
    public function startRow(): int
    {
        return 2;
    }

    public function model(array $row)
    {
        return new Fournisseur([
            'societe_id' => $this->societeId,
            'compte' => $row[$this->colonneCompte - 1],
            'intitule' => $row[$this->colonneIntitule - 1],
            'identifiant_fiscal' => $row[$this->colonneIdentifiantFiscal - 1],
            'ICE' => $row[$this->colonneICE - 1],
            'nature_operation' => $row[$this->colonneNatureOperation - 1],
            'rubrique_tva' => $row[$this->colonneRubriqueTva - 1],
            'designation' => $row[$this->colonneDesignation - 1],
            'contre_partie' => $row[$this->colonneContrePartie - 1],
        ]);
    }
}

