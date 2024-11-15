<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Section extends Model
{
    protected $connection = 'supcompta'; 
    protected $table = 'sections';
    protected $fillable = ['name', 'societe_id'];

    public function societe()
    {
        return $this->belongsTo(Societe::class);
    }
}
