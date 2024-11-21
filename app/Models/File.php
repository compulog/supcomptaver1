<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class File extends Model
{
    use HasFactory;

    protected $connection = 'supcompta'; // Connexion spécifique à la base de données
    protected $table = 'files';

    // Ajout de 'file_data' aux champs remplissables
    protected $fillable = ['name', 'path', 'type', 'societe_id', 'file_data','folders'];  

    public function societe()
    {
        return $this->belongsTo(Societe::class);
    }
    public function folder()
    {
        return $this->belongsTo(Folder::class); // Un fichier appartient à un dossier
    }
    
}
