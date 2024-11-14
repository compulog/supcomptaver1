<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class journal extends Model
{
    use HasFactory;
    protected $connection = 'supcompta';
    protected $table = 'journaux';

    protected $fillable = [
        'code_journal',
        'type_journal',
        'intitule',
        'contre_partie',
        'societe_id',
    ];

    public function societe()
    {
        return $this->belongsTo(Societe::class);
    }
    
}
