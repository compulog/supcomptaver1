<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class File extends Model
{
    use HasFactory;
    protected $connection = 'supcompta'; // Le nom de votre connexion
    // Définir les champs remplissables
    protected $fillable = [
        'name',
        'path',
        'type',
        'societe_id',
    ];

    /**
     * La société à laquelle ce fichier appartient.
     */
    public function societe()
    {
        return $this->belongsTo(Societe::class); // La relation appartient à une société
    }
}


