<?php

namespace App\Entities;

use Illuminate\Database\Eloquent\Model;
use OwenIt\Auditing\Contracts\Auditable;

class Pedido extends Model implements Auditable
{
    use \OwenIt\Auditing\Auditable;

    protected $table = 'pedidos';

    protected $primaryKey = 'id_pedido';

    protected $fillable = [
        'fecha',
        'codigo',
        'sub_total',
        'total',
        'metodo_pago',
        'descuento',
        'notas',
        'notas_facturacion',
        'firma',
        'vendedor',
        'cliente',
        'estado',
    ];

    protected $hidden = [
        'created_at',
        'updated_at',
        'descuento',
    ];

    public function detalles()
    {
        return $this->hasMany(Detalle::class, 'pedido', 'id_pedido');
    }

    public function pedidoVendedor()
    {
        return $this->belongsTo(User::class, 'vendedor');
    }

    public function pedidoCliente()
    {
        return $this->belongsTo(User::class, 'cliente');
    }

    public function pedidoEstado()
    {
        return $this->belongsTo(Estado::class, 'estado', 'id_estado');
    }

    public function novedades()
    {
        return $this->hasMany(Novedad::class, 'pedido', 'id_pedido');
    }
}
