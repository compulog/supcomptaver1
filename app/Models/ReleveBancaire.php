<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ReleveBancaire extends Model
{
    use HasFactory;

    protected $table = 'releves_bancaires';

    protected $fillable = [
        'idfile',
        'code_journal',
        'mois',
        'annee',
    ];

    public function file()
    {
        return $this->belongsTo(File::class, 'idfile');
    }
}
