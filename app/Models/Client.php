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
    protected $fillable = [
        'compte',
        'intitule',
        'identifiant_fiscal',
        'ICE',
        'type_client',
    ];
}
