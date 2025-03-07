<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model; // Assurez-vous que ce namespace est correct
use Illuminate\Database\Eloquent\Factories\HasFactory; // Pour utiliser les factories si nécessaire
use Illuminate\Database\Eloquent\SoftDeletes;

class Societe extends Model
{
    use HasFactory; // Utiliser le trait HasFactory si vous envisagez de créer des factories
    use SoftDeletes; // Active les suppressions douces

    protected $dates = ['deleted_at'];
    protected $connection = 'supcompta'; // Assurez-vous que la connexion est correcte
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
        'dbName',
        'created_by_user_id',
        'code_societe',
        'cnss',
    ];

    // /**
    //  * Scope pour filtrer les sociétés actives.
    //  *
    //  * @param \Illuminate\Database\Eloquent\Builder $query
    //  * @return \Illuminate\Database\Eloquent\Builder
    //  */
    // public function scopeActives($query)
    // {
    //     return $query->where('active', true);
    // }

    public function files()
{
    return $this->hasMany(File::class);
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
public function section()
{
    return $this->hasMany(Section::class);
}

public function folders()
{
    return $this->hasMany(Folder::class);
}
public function operations()
    {
        return $this->hasMany(OperationCourante::class);  // Relation inverse, une société a plusieurs opérations
    }



}
