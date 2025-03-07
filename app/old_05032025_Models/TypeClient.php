<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TypeClient extends Model
{
    use HasFactory;
    protected $connection = 'supcompta'; // Assurez-vous que la connexion est correcte

    // Indiquer explicitement les champs de la table
    protected $table = 'type_client';

    // La liste des champs qui peuvent être affectés en masse
    protected $fillable = [
        'numero',  // Le numéro du type client
        'description', // La description du type client
    ];

    // Nous allons nous assurer que "numero" est unique
    public static function boot()
    {
        parent::boot();

        static::creating(function ($model) {
            // Assurez-vous qu'aucun autre type client avec le même numéro n'existe
            $existingType = self::where('numero', $model->numero)->first();
            if ($existingType) {
                throw new \Exception("Un type client avec ce numéro existe déjà.");
            }
        });
    }
}
