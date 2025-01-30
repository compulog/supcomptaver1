<?php

// app/Models/SoldeMensuel.php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class SoldeMensuel extends Model
{
    use HasFactory;
    use SoftDeletes;

    protected $connection = 'supcompta';
    // Nom de la table associée au modèle
    protected $table = 'soldes_mensuels';

    // Colonnes que vous pouvez remplir massivement
    protected $fillable = [
        'mois',
        'annee',
        'solde_initial',
        'total_recette',
        'total_depense',
        'solde_final',
        'societe_id',  
        'code_journal',
        'cloturer',
    ];

    // Pour éviter l'auto-gestion des timestamps (created_at, updated_at) si vous ne les utilisez pas
    public $timestamps = true;
}
