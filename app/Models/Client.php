<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Client extends Model
{
    use HasFactory;
    protected $connection = 'supcompta';
    protected $table = 'clients';
    // Définir les champs qui peuvent être remplis
=======
    // Spécifiez la table si le nom n'est pas le pluriel du modèle
    protected $table = 'clients';

    // Indiquez les champs qui peuvent être remplis
>>>>>>> ee0e4952a638033bbf386ac775f5dff87ae0b49e
    protected $fillable = [
        'compte',
        'intitule',
        'identifiant_fiscal',
        'ICE',
        'type_client',
    ];
}
