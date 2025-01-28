<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
class Transaction extends Model
{
    use HasFactory;
    use SoftDeletes;
    protected $fillable = ['date', 'reference', 'libelle', 'recette', 'depense', 'societe_id',];
}

