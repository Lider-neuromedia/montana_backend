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
        'device_token',
        'email_verified_at',
    ];

    protected $casts = [
        'email_verified_at' => 'datetime',
    ];

    public function rol()
    {
        return $this->belongsTo(Rol::class);
    }

    /**
     * Obtener vendedores asignados a mi (como cliente).
     */
    public function vendedores()
    {
        return $this->belongsToMany(User::class, 'vendedor_cliente', 'cliente', 'vendedor');
    }

    /**
     * Obtener clientes asignados a mi (como vendedor).
     */
    public function clientes()
    {
        return $this->belongsToMany(User::class, 'vendedor_cliente', 'vendedor', 'cliente');
    }

    /**
     * Obtener mis tiendas asignadas como vendedor.
     */
    public function vendedorTiendas()
    {
        return $this->belongsToMany(Tienda::class, 'tienda_vendedor', 'vendedor_id', 'tienda_id');
    }

    /**
     * Obtener mis tiendas asignadas como cliente.
     */
    public function tiendas()
    {
        return $this->hasMany(Tienda::class, 'cliente');
    }

    public function datos()
    {
        return $this->hasMany(UserData::class);
    }

    public function clientePedidos()
    {
        return $this->hasMany(Pedido::class, 'cliente');
    }

    public function vendedorPedidos()
    {
        return $this->hasMany(Pedido::class, 'vendedor');
    }

    public function clienteTickets()
    {
        return $this->hasMany(Pqrs::class, 'cliente');
    }

    public function vendedorTickets()
    {
        return $this->hasMany(Pqrs::class, 'vendedor');
    }

    public function getNombreCompletoAttribute()
    {
        return trim("{$this->name} {$this->apellidos}");
    }

    public function getInicialesAttribute()
    {
        $a = substr($this->name, 0, 1);
        $b = substr($this->apellidos, 0, 1);
        return trim("{$a}{$b}");
    }

    public function obtenerDato($key)
    {
        return $this->datos()
            ->where('field_key', $key)
            ->first()
            ->value_key ?? null;
    }
}
