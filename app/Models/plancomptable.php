<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\Societe;
use App\Models\Client;
use App\Models\Fournisseur;

class PlanComptable extends Model
{
    use HasFactory;

    /**
     * Connexion spécifique
     * @var string
     */
    protected $connection = 'supcompta';

    /**
     * Nom de la table
     * @var string
     */
    protected $table = 'plan_comptable';

    /**
     * Champs assignables en masse
     * @var array
     */
    protected $fillable = [
        'societe_id',
        'compte',
        'intitule',
        'etat',
    ];

    /**
     * Boot : suppression en cascade des relations
     */
    protected static function boot()
    {
        parent::boot();

        static::deleting(function (PlanComptable $plan) {
            // Supprimer les clients liés
            $plan->clients()->delete();
            // Supprimer les fournisseurs liés
            $plan->fournisseurs()->delete();
        });
    }

    /**
     * Relation vers la société
     */
    public function societe()
    {
        return $this->belongsTo(Societe::class, 'societe_id');
    }

    /**
     * Relation vers les clients
     */
    public function clients()
    {
        return $this->hasMany(Client::class, 'compte','compte');
    }

    /**
     * Relation vers les fournisseurs
     */
    public function fournisseurs()
    {
        return $this->hasMany(Fournisseur::class, 'compte','compte');
    }
}
