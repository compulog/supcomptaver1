<?php

namespace App\Models; // Assurez-vous que ce namespace est correct

use Illuminate\Database\Eloquent\Model; // Ajoutez cette ligne

class Societe extends Model
{
    protected $connection = 'supcompta';
    protected $table = 'societe';
    protected $fillable = [
        'raison_sociale', 'forme_juridique', 'siege_social', 'patente',
        'rc', 'centre_rc', 'identifiant_fiscal', 'ice',
        'assujettie_partielle_tva', 'prorata_de_deduction', 'exercice_social',
        'date_creation', 'nature_activite', 'activite',
        'regime_declaration', 'fait_generateur', 'rubrique_tva',
        'designation',
    ];
    
}
