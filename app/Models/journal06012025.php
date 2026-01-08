<?php

namespace App\Models;
use Illuminate\Database\Eloquent\SoftDeletes;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Journal extends Model
{
    use SoftDeletes; // Active Soft Deletes

    use HasFactory;
    protected $connection = 'supcompta';
    protected $table = 'journaux';

    protected $fillable = [
        'code_journal',
        'type_journal',
        'intitule',
        'contre_partie',
        'societe_id',
        'if',
        'ice',
    ];
    protected $dates = ['deleted_at']; // Permet de gérer la colonne deleted_at comme une date

    public function societe()
    {
        return $this->belongsTo(Societe::class);
    }

}
