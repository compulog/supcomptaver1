<?php



namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Racine extends Model
{
    use HasFactory;
    protected $connection='supcompta';
    protected $table = 'racines'; // Assurez-vous que le nom de la table est correct

    protected $fillable = ['type', 'categorie', 'num_racines', 'nom_racines', 'taux']; // Les champs que vous voulez remplir
}
