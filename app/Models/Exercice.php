<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Exercice extends Model
{
    use HasFactory;

    /**
     * Le nom de la table associée au modèle.
     *
     * @var string
     */
    protected $connection = 'supcompta';
    protected $table = 'files';

    /**
     * Les attributs qui sont assignables en masse.
     *
     * @var array
     */
    protected $fillable = [
        // Ajoutez ici les colonnes de votre table exercices, par exemple :
        'name',        // Nom de l'exercice
        'start_date',  // Date de début
        'end_date',    // Date de fin
        'societe_id',  // ID de la société associée (si applicable)
    ];

    /**
     * Relation avec le modèle Societe.
     */
    public function societe()
    {
        return $this->belongsTo(Societe::class);
    }
}
