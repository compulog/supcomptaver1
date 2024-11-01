<?php

namespace App\Exports;

use App\Models\Fournisseur;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;

class FournisseursExport implements FromCollection, WithHeadings
{
    public function collection()
    {
        return Fournisseur::all(); // Récupère tous les fournisseurs
    }

    public function headings(): array
    {
        return [
            
            'Compte',
            'Intitulé',
            'Identifiant Fiscal',
            'ICE',
            'Nature de l\'Opération',
            'Rubrique TVA',
            'Désignation',
            'Contre Partie',
            // Ajoutez d'autres en-têtes si nécessaire
        ];
    }
}
