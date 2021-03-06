<?php

namespace App\Entities;
// namespace App\Rol;

use Illuminate\Notifications\Notifiable;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Foundation\Auth\User as Authenticatable;

use Spatie\Permission\Traits\HasRoles;
use Laravel\Passport\HasApiTokens;

class User extends Authenticatable
{
    use Notifiable, HasRoles, HasApiTokens;

    protected $table = 'users';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'rol_id',
        'name',
        'email', 
        'password',
    ];

    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */
    protected $hidden = [
        'password', 'remember_token',
    ];

    /**
     * The attributes that should be cast to native types.
     *
     * @var array
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
    ];


    public function vendedor_clientes(){
        /* Argumentos = (Entidad,tabla pivot, llave dentro de la tabla, id de la entidad) */
        return $this->belongsToMany(User::class,'vendedor_clientes','vendedor_id','cliente_id');
    }

    public function cliente_vendedor(){
        /* Argumentos = (Entidad,tabla pivot, llave dentro de la tabla, id de la entidad) */
        return $this->belongsToMany(User::class,'vendedor_clientes','cliente_id','vendedor_id');
    }
}
