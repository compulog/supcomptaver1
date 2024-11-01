<?php
// app/Models/OperationCourante.php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class OperationCourante extends Model
{
    use HasFactory;

    protected $table = 'operation_courante';

    protected $fillable = [
        'date',
        'dossier',
        'facture',
        'compte',
        'libelle',
        'debit',
        'credit',
        'contrepartie',
        'rubrique_tva',
        'compte_tva',
        'prorata',
        'file',
    ];
}
