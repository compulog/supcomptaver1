<?php

namespace App\Exports;

use App\Models\Societe; // Assurez-vous d'importer le modèle approprié
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;

class SocietesExport implements FromCollection, WithHeadings
{
    public function collection()
    {
        return Societe::all();
    }

    public function headings(): array
    {
        return [
            'id',
        'raison_sociale',
        'forme_juridique',
        'siege_social',
        'patente',
        'rc',
        'centre_rc',
        'identifiant_fiscal',
        'ice',
        'assujettie_partielle_tva',
        'prorata_de_deduction',
        'exercice_social_debut', 
        'exercice_social_fin',   
        'date_creation',
        'nature_activite',
        'activite',
        'regime_declaration',
        'fait_generateur',
        'rubrique_tva',
        'designation',
        'nombre_chiffre_compte', 
        'modele_comptable' ,  

        ];
    }
}
