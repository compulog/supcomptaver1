<?php

namespace App\Models;

use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;
use Illuminate\Database\Eloquent\SoftDeletes;

class User extends Authenticatable
{
    use SoftDeletes; // Active les suppressions douces
    protected $dates = ['deleted_at'];
    protected $connection = 'mysql';
    protected $table = 'users';

    use HasApiTokens, HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var string[]
     */
    protected $fillable = [
        'name', 'email', 'password', 'raw_password', 'phone', 'location', 'about_me', 'BaseName', 'type', 'societe_id'
    ];


    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array
     */
    protected $hidden = [
        'password',
        'raw_password',  // Masquer le mot de passe non haché dans les réponses JSON
        'remember_token',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
    ];
  // Dans le modèle User
// Dans le modèle User
public function droits()
{
    return $this->belongsToMany(DroitDacces::class, 'droit_dacces_user', 'user_id', 'droit_dacces_id');
}
public function droitsAcces()
{
    return $this->belongsToMany(DroitDacces::class, 'droit_dacces_user', 'user_id', 'droit_dacces_id');
}
}
