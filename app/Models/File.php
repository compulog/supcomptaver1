<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class File extends Model
{
    use HasFactory;
    protected $connection = 'supcompta'; // Le nom de votre connexion
    protected $table = 'files';

    protected $fillable = ['name', 'path', 'type', 'societe_id'];  // Assurez-vous que 'societe_id' est inclus si nécessaire
    public function societe()
    {
        return $this->belongsTo(Societe::class);
    }
    
}


