<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Fournisseur extends Model
{
    protected $connection = 'supcompta';
    protected $table = 'fournisseurs';
    
    protected $fillable = [
        'id', 'compte', 'intitule', 'identifiant_fiscal', 
        'ICE', 'nature_operation', 'rubrique_tva', 
        'designation', 'contre_partie',
    ];
}




