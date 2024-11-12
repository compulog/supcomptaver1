<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model; // Assurez-vous que ce namespace est correct
use Illuminate\Database\Eloquent\Factories\HasFactory; // Pour utiliser les factories si nécessaire

class Societe extends Model
{
    use HasFactory; // Utiliser le trait HasFactory si vous envisagez de créer des factories

    protected $connection = 'supcompta';
    protected $table = 'societe';
    
    protected $fillable = [
        'raison_sociale',
        'forme_juridique',
        'siege_social',
        'patente',
        'rc',
        'centre_rc',
        'identifiant_fiscal',
        'ice',
        'assujettie_partielle_tva',
        'prorata_de_deduction',
        'exercice_social_debut', // Champ ajouté
        'exercice_social_fin',   // Champ ajouté
        'date_creation',
        'nature_activite',
        'activite',
        'regime_declaration',
        'fait_generateur',
        'rubrique_tva',
        'designation',
        'nombre_chiffre_compte',  // Champ pour le nombre de chiffres du compte
        'modele_comptable' ,        // Nouveau champ ajouté
    ];

    public function files()
    {
        return $this->hasMany(File::class); // Une société a plusieurs fichiers
    }
    public function fournisseurs()
    {
        return $this->hasMany(Fournisseur::class);
    }

    public function clients()
    {
        return $this->hasMany(Client::class);
    }

    public function planComptable()
    {
        return $this->hasMany(PlanComptable::class);
    }

    public function journaux()
    {
        return $this->hasMany(Journal::class);
    }
    

}
