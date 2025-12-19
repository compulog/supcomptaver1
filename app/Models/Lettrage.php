<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Lettrage extends Model
{
    use HasFactory;

    // Nom de la table (facultatif si le nom du modèle correspond au nom de la table au pluriel)
    protected $table = 'lettrage';

    // Les champs que l'on peut remplir via assignation massive
    protected $fillable = [
        'compte',
        'Acompte',
        'NFacture',
        'id_operation',
        'id_user',
        'lettrage_id',
    ];

    /**
     * Relation avec le modèle OperationCourante
     * Une ligne de lettrage appartient à une opération
     */
    public function operation()
    {
        return $this->belongsTo(OperationCourante::class, 'id_operation');
    }

    /**
     * Relation avec le modèle User
     * Une ligne de lettrage appartient à un utilisateur
     */
    public function user()
    {
        return $this->belongsTo(User::class, 'id_user');
    }
}
