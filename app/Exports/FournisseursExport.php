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
        // Récupérer tous les fournisseurs mais en excluant les champs 'id', 'created_at', et 'updated_at'
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
            // Ajoutez d'autres en-têtes si nécessaire
        ];
    }

    public function map($fournisseur): array
    {
        // Mapping des données pour chaque fournisseur
        return [
            $fournisseur->compte,
            $fournisseur->intitule,
            $fournisseur->identifiant_fiscal,
            $fournisseur->ICE,
            $fournisseur->nature_operation,
            $fournisseur->rubrique_tva,
            $fournisseur->designation,
            $fournisseur->contre_partie,
            // Ajoutez d'autres champs si nécessaire
        ];
    }
}
