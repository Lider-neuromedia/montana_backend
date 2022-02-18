<?php

namespace App\Entities;

use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Passport\HasApiTokens;
use OwenIt\Auditing\Contracts\Auditable;
use Spatie\Permission\Traits\HasRoles;

class User extends Authenticatable implements Auditable
{
    use \OwenIt\Auditing\Auditable;
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

    /**
     * Obtener vendedores asignados a mi (como cliente).
     */
    public function vendedores()
    {
        return $this->belongsToMany(Tienda::class, 'vendedor_cliente', 'cliente', 'vendedor');
    }

    /**
     * Obtener clientes asignados a mi (como vendedor).
     */
    public function clientes()
    {
        return $this->belongsToMany(Tienda::class, 'vendedor_cliente', 'vendedor', 'cliente');
    }

    /**
     * Obtener mis tiendas asignadas como vendedor.
     */
    public function vendedorTiendas()
    {
        return $this->belongsToMany(Tienda::class, 'tienda_vendedor', 'vendedor_id', 'tienda_id');
    }

    public function tiendas()
    {
        return $this->hasMany(Tienda::class, 'cliente');
    }

    public function datos()
    {
        return $this->hasMany(UserData::class);
    }
}
