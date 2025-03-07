<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Client extends Model
{
    use HasFactory;
    use SoftDeletes; // Active les suppressions douces

    protected $dates = ['deleted_at'];
    // Spécifie la connexion de base de données
    protected $connection = 'supcompta';
    // protected $connection = 'database';
    // Spécifie la table si elle n'est pas le pluriel du modèle
    protected $table = 'clients';

    // Les attributs qui peuvent être assignés en masse
    protected $fillable = [
        'compte',
        'intitule',
        'identifiant_fiscal',
        'ICE',
        'type_client',
        'societe_id', 
    ];

    /**
     * Définir la relation avec le modèle Societe.
     * Un client appartient à une société.
     */
    public function societe()
    {
        return $this->belongsTo(Societe::class);
    }
}
