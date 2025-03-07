<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;


class Balance extends Model
{
    use HasFactory;
    use SoftDeletes;

    protected $fillable = [
        'compte',
        'intitule',
        'anv_debit',
        'anv_credit',
        'ope_debit',
        'ope_credit',
        'cumul_debit',
        'cumul_credit',
        'solde_debit',
        'solde_credit',
        'date_operation',
        'societe_id',
    ];

    protected $casts = [
        'anv_debit'      => 'float',
        'anv_credit'     => 'float',
        'ope_debit'      => 'float',
        'ope_credit'     => 'float',
        'cumul_debit'    => 'float',
        'cumul_credit'   => 'float',
        'solde_debit'    => 'float',
        'solde_credit'   => 'float',
        'date_operation' => 'date',
    ];

    // Relation avec la société
    public function societe()
    {
        return $this->belongsTo(Societe::class);
    }
}
