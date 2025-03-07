<?php

namespace App\Exports;

use App\Models\Balance;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;

class BalanceExport implements FromCollection, WithHeadings
{
    protected $compte_debut;
    protected $compte_fin;
    protected $periode_debut;
    protected $periode_fin;

    public function __construct($compte_debut, $compte_fin, $periode_debut, $periode_fin)
    {
        $this->compte_debut  = $compte_debut;
        $this->compte_fin    = $compte_fin;
        $this->periode_debut = $periode_debut;
        $this->periode_fin   = $periode_fin;
    }

    public function collection()
    {
        $query = Balance::query();

        if ($this->compte_debut) {
            $query->where('compte', '>=', $this->compte_debut);
        }
        if ($this->compte_fin) {
            $query->where('compte', '<=', $this->compte_fin);
        }
        if ($this->periode_debut) {
            $query->whereDate('date_operation', '>=', $this->periode_debut);
        }
        if ($this->periode_fin) {
            $query->whereDate('date_operation', '<=', $this->periode_fin);
        }

        return $query->get();
    }

    public function headings(): array
    {
        return [
            'Compte',
            'Intitulé',
            'A nouveau Débit',
            'A nouveau Crédit',
            'Opération Débit',
            'Opération Crédit',
            'Cumul Débit',
            'Cumul Crédit',
            'Solde Débit',
            'Solde Crédit',
            'Date Opération',
            'Societe ID'
        ];
    }
}
