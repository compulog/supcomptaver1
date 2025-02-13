<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DroitDaccesUser extends Model
{
    use HasFactory;

    // Définir le nom de la table si ce n'est pas le nom par défaut (table plurielle du nom du modèle)
    protected $table = 'droit_dacces_user';

    // Les attributs qui peuvent être assignés en masse
    protected $fillable = [
        'user_id',
        'droit_dacces_id',
    ];

    // Définir la relation avec le modèle User
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    // Définir la relation avec le modèle DroitDacces
    public function droitDacces()
    {
        return $this->belongsTo(DroitDacces::class);
    }
}
