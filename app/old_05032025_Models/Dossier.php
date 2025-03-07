<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Dossier extends Model
{
    use HasFactory;
    protected $dates = ['deleted_at'];

    protected $connection = 'supcompta';
    // Les champs qui peuvent être assignés en masse
    protected $fillable = ['name', 'societe_id'];
    public function files()
    {
        return $this->hasMany(File::class);  // Exemple : relation avec les fichiers
    }
}
    