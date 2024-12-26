<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Dossier extends Model
{
    use HasFactory;
    protected $connection = 'supcompta';
    // Les champs qui peuvent être assignés en masse
    protected $fillable = ['name', 'societe_id'];
}
