<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class DroitDacces extends Model
{
    use HasFactory, SoftDeletes;

    // Nom de la table associée à ce modèle
    protected $table = 'droit_dacces';
 
    // Définir les attributs qui peuvent être assignés en masse (mass assignable)
    protected $fillable = [
        'name',
    ];

    // Attributs à cacher lors de la conversion en tableau ou JSON (si vous en avez besoin)
    protected $hidden = [
        'remember_token',
    ];

    // Activer les timestamps pour les colonnes created_at et updated_at
    public $timestamps = true;

    // Indiquer que le modèle utilise les "soft deletes" (suppression logique)
    protected $dates = ['deleted_at'];
    // Dans le modèle DroitDacces
public function users()
{
    return $this->belongsToMany(User::class, 'droit_dacces_user');
}

}
