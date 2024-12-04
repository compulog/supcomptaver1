<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class NatureActivite extends Model
{
    protected $connection = 'supcompta';

    // Nom de la table associée au modèle
    protected $table = 'nature_activites';

    // Attributs qui peuvent être assignés en masse
    protected $fillable = ['numero', 'description'];

    // Si vous utilisez les timestamps
    public $timestamps = true;
}
