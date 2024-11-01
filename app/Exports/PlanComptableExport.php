<?php

namespace App\Exports;

use App\Models\PlanComptable;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;

class PlanComptableExport implements FromCollection, WithHeadings
{
    public function collection()
    {
        return PlanComptable::all(['compte', 'intitule']); // Sélectionner uniquement les champs nécessaires
    }

    public function headings(): array
    {
        return [
            'Compte',
            'Intitulé',
        ];
    }
}
