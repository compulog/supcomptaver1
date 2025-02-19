<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes; // Pour utiliser le soft delete

class racine extends Model
{
    use HasFactory, SoftDeletes; // Ajoutez SoftDeletes pour gérer le champ deleted_at
    protected $connection = 'supcompta';
    protected $table = 'racines';


    protected $fillable = [
        'type',
        'categorie',
        'Num_racines',
        'Nom_racines',
        'Taux',
    ];

    // Vous pouvez ajouter d'autres méthodes ou relations si nécessaire
}
