<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class ExerciceComptable extends Model
{
    use HasFactory;

    protected $table = 'exercice_comptable';

    protected $fillable = [
        'nom_exercice',
        'id_societe',
        'date_debut',
        'date_fin',
        'cloture',
    ];

    /**
     * Relation avec la société
     */
    public function societe()
    {
        return $this->belongsTo(Societe::class, 'id_societe');
    }
       public function dossiers(): HasMany
    {
        return $this->hasMany(Dossier::class);
    }

    /**
     * Scope pour récupérer l'exercice en cours d'une société
     */
    public function scopeActif($query, $societeId)
    {
        return $query->where('id_societe', $societeId)
                     ->where('cloture', false)
                     ->latest('date_debut');
    }
}
