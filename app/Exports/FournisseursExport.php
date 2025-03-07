<?php

namespace App\Exports;

use App\Models\Fournisseur;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;

class FournisseursExport implements FromCollection, WithHeadings, WithMapping
{
    public function collection()
    {
        return Fournisseur::select('compte', 'intitule', 'identifiant_fiscal', 'ICE', 'nature_operation', 'rubrique_tva', 'designation', 'contre_partie')->get();
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
        ];
    }

    public function map($fournisseur): array
    {
        return [
            " " . $fournisseur->compte,      // Forcer le format texte pour 'compte'
            $fournisseur->intitule,
            $fournisseur->identifiant_fiscal,
            " " . $fournisseur->ICE,         // Forcer le format texte pour 'ICE'
            $fournisseur->nature_operation,
            $fournisseur->rubrique_tva,
            $fournisseur->designation,
            $fournisseur->contre_partie,
        ];
    }
}
