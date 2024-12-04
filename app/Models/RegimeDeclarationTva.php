<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class RegimeDeclarationTva extends Model
{
    // Nom de la table associée à ce modèle
    protected $table = 'regimes_declaration_tva';
    protected $connection = 'supcompta';
    // Les attributs qui peuvent être assignés en masse
    protected $fillable = [
        'numero', 'description'
    ];

    // Si vous utilisez les timestamps
    public $timestamps = true;
}
