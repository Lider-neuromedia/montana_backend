<?php

namespace App\Entities;

use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Passport\HasApiTokens;
use Spatie\Permission\Traits\HasRoles;

class User extends Authenticatable
{
    use Notifiable, HasRoles, HasApiTokens;

    protected $table = 'users';

    protected $fillable = [
        'rol_id',
        'name',
        'apellidos',
        'email',
        'dni',
        'tipo_identificacion',
        'password',
        'device_token',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected $casts = [
        'email_verified_at' => 'datetime',
    ];

    public function vendedor_clientes()
    {
        return $this->belongsToMany(User::class, 'vendedor_cliente', 'vendedor', 'cliente');
    }

    public function cliente_vendedor()
    {
        return $this->belongsToMany(User::class, 'vendedor_cliente', 'cliente', 'vendedor');
    }
}
