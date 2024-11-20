<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Folder extends Model
{
    use HasFactory;
    protected $connection = 'supcompta';
    protected $table = 'folders';

    // Définir les champs autorisés pour l'attribution de masse
    protected $fillable = ['name', 'societe_id'];
    public function societe()
    {
        return $this->belongsTo(Societe::class);
    }
}
