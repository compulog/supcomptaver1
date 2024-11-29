<?php

namespace App\Models;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Fournisseur extends Model
{
    use HasFactory;
    protected $connection = 'supcompta';
    protected $table = 'fournisseurs';

    protected $fillable = [
        'id', 'compte', 'intitule', 'identifiant_fiscal',
        'ICE', 'nature_operation', 'rubrique_tva',
        'designation', 'contre_partie','societe_id', 'invalid',
    ];

    public function societe()
    {
        return $this->belongsTo(Societe::class); // Une société pour chaque fournisseur
    }
}




