<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Client extends Model
{
    use HasFactory;

    // Spécifie la connexion de base de données
    protected $connection = 'supcompta';

    // Spécifie la table si elle n'est pas le pluriel du modèle
    protected $table = 'clients';

    // Les attributs qui peuvent être assignés en masse
    protected $fillable = [
        'compte',
        'intitule',
        'identifiant_fiscal',
        'ICE',
        'type_client',
        'societe_id', // Ajouter la colonne 'societe_id' dans $fillable pour la gestion de la masse
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
