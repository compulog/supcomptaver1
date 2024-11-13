<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Exercice extends Model
{
    protected $connection = 'supcompta';

    use HasFactory;

    // Définir les colonnes que vous pouvez remplir via l'assignation de masse
    protected $fillable = [
        'societe_id',
        'type',
        'file_path',
        'filename',
    ];

    /**
     * Relation avec la société
     */
    public function societe()
    {
        return $this->belongsTo(Societe::class); // Relation inverse avec la table 'societes'
    }
}
