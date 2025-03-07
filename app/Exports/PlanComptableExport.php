<?php

namespace App\Exports;

use App\Models\PlanComptable;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;

class PlanComptableExport implements FromCollection, WithHeadings
{
    protected $societeId;

    // Constructeur pour recevoir l'ID de la société
    public function __construct($societeId)
    {
        $this->societeId = $societeId;
    }

    // Collecte des données en fonction de l'ID de la société
    public function collection()
    {
        return PlanComptable::where('societe_id', $this->societeId)
                            ->get(['compte', 'intitule']); // Sélectionner uniquement les champs nécessaires
    }

    // En-têtes pour l'exportation
    public function headings(): array
    {
        return [
            'Compte',
            'Intitulé',
        ];
    }
}
