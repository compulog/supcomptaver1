<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class File extends Model
{
    use HasFactory;
    use SoftDeletes; 

    protected $dates = ['deleted_at'];
    protected $connection = 'supcompta'; // Connexion spécifique à la base de données
    protected $table = 'files';

    // Ajout de 'file_data' aux champs remplissables
    protected $fillable = ['name', 'path', 'type', 'societe_id', 'file_data','folders', 'exercice_debut','exercice_fin'];  

    public function societe()
    {
        return $this->belongsTo(Societe::class);
    }
    public function folder()
            {
                return $this->belongsTo(Folder::class, 'folders'); // 
            }
     public function dossier()
            {
                return $this->belongsTo(Dossier::class); // Chaque fichier appartient à un dossier
            }

            public function updatedBy()
{
    return $this->belongsTo(User::class, 'updated_by');
}
    
}
