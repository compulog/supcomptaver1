<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class OperationCourante extends Model
{
    use HasFactory;

    // Spécifier le nom de la table si différent du nom du modèle au pluriel
    protected $connection = 'supcompta';

    protected $table = 'operation_courante';

    // Définir les attributs qui peuvent être affectés en masse (mass assignment)
    protected $fillable = [
        'date',
        'numero_dossier',
        'numero_facture',
        'compte',
        'libelle',
        'debit',
        'credit',
        'contre_partie',
        'rubrique_tva',
        'compte_tva',
        'prorat_de_deduction',
        'piece_justificative',
        'type_journal',
        'categorie',
        'filtre_selectionne',
        'societe_id',
        'fact_lettrer',
        'taux_ras_tva',
        'nature_op',
        'date_lettrage',
        'mode_pay',
    ];

    // Définir la relation avec la société
    public function societe()
    {
        return $this->belongsTo(Societe::class);  // Assurez-vous que vous avez une table 'societes' et un modèle 'Societe'
    }
}
