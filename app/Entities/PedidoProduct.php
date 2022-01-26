<?php

namespace App\Entities;

use Illuminate\Database\Eloquent\Model;

class PedidoProduct extends Model
{
    protected $table = 'pedido_productos';

    protected $primaryKey = 'id_pedido_prod';

    protected $fillable = [
        'pedido',
        'producto',
        'cantidad_producto',
        'tienda',
    ];
}
