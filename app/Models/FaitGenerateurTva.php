<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class FaitGenerateurTva extends Model
{
    protected $connection = 'supcompta';

    // Nom de la table associée à ce modèle
    protected $table = 'fait_generateur_tva';

    // Les attributs qui peuvent être assignés en masse
    protected $fillable = ['numero', 'description'];

    // Si vous utilisez les timestamps
    public $timestamps = true;
}
