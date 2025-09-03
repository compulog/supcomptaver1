<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Dossier extends Model
{
    use HasFactory;
    protected $dates = ['deleted_at'];
    use SoftDeletes; // Active les suppressions douces
    protected $connection = 'supcompta';
    // Les champs qui peuvent être assignés en masse
// App\Models\Dossier.php
protected $fillable = [
    'name',
    'societe_id',
    'color',
    'exercice_debut',
    'exercice_fin',
    'updated_by', // ← obligatoire pour que Dossier::create() le prenne en compte
];
    public function files()
    {
        return $this->hasMany(File::class);  // Exemple : relation avec les fichiers
    }
      public function exercice(): BelongsTo
    {
        return $this->belongsTo(ExerciceComptable::class, 'exercice_id');
    }
    // Dans Dossier.php
public function user()
{
    return $this->belongsTo(User::class, 'updated_by');  // ou 'created_by' selon ta colonne
}


}
    