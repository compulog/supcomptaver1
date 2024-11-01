<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PlanComptable extends Model // Notez le "P" majuscule
{
    

    protected $connection = 'supcompta'; // Assurez-vous que la connexion est bien configurée

    // Spécifiez le nom de la table
    protected $table = 'plan_comptable';

    // Les attributs qui peuvent être assignés en masse
    protected $fillable = [
        'compte',
        'intitule',
    ];

    // Si vous utilisez des timestamps, vous pouvez laisser cette propriété à true
    public $timestamps = true; 

    
}
