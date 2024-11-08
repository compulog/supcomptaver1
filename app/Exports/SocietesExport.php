<?php

namespace App\Exports;

use App\Models\Societe;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;

class SocietesExport implements FromCollection, WithHeadings, WithMapping
{
    public function collection()
    {
        // Remplacer "all()" par une sélection explicite des colonnes
        return Societe::select([
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
            'modele_comptable'
        ])->get();  // Utilisation de "get()" pour récupérer les résultats
    }

    public function headings(): array
    { 
        return [
            'Raison Sociale',
            'Forme Juridique',
            'Siège Social',
            'Patente',
            'RC',
            'Centre RC',
            'Identifiant Fiscal',
            'ICE',
            'Assujettie Partielle TVA',
            'Prorata de Déduction',
            'Exercice Social Début',
            'Exercice Social Fin',
            'Date de Création',
            'Nature d\'Activité',
            'Activité',
            'Régime de Déclaration',
            'Fait Générateur',
            'Rubrique TVA',
            'Désignation',
            'Nombre de Chiffres de Compte',
            'Modèle Comptable'
        ];
    }
    public function map($societe): array
    {
        return [
            $societe->raison_sociale,
            $societe->forme_juridique,
            $societe->siege_social,
            $societe->patente,
            $societe->rc,
            $societe->centre_rc,
            $societe->identifiant_fiscal,
            " " . $societe->ice,
            $societe->assujettie_partielle_tva,  // Assurez-vous que cette colonne existe dans la base
            $societe->prorata_de_deduction,
            $societe->exercice_social_debut,
            $societe->exercice_social_fin,
            $societe->date_creation,
            $societe->nature_activite,
            $societe->activite,
            $societe->regime_declaration,
            $societe->fait_generateur,
            $societe->rubrique_tva,
            $societe->designation,
            $societe->nombre_chiffre_compte,
            $societe->modele_comptable
        ];
    }
}