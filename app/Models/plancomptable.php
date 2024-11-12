<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PlanComptable extends Model // Notez le "P" majuscule
{
    

    protected $connection = 'supcompta'; // Assurez-vous que la connexion est bien configurée

    // Spécifiez le nom de la table
    protected $table = 'plan_comptable';

    // Les attributs qui peuvent être assignés en masse
    protected $fillable = ['societe_id',
        'compte',
        'intitule',
        ];
         // Ajoutez 'societe_id' dans le tableau des champs autorisés 

    // Définir la relation avec le modèle Societe
    public function societe()
    {
        return $this->belongsTo(Societe::class);
    }
    
}
