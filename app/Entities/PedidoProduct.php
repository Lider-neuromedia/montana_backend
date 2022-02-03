<?php

namespace App\Entities;

use Illuminate\Database\Eloquent\Model;
use OwenIt\Auditing\Contracts\Auditable;

class PedidoProduct extends Model implements Auditable
{
    use \OwenIt\Auditing\Auditable;

    protected $table = 'pedido_productos';

    protected $primaryKey = 'id_pedido_prod';

    protected $fillable = [
        'pedido',
        'producto',
        'cantidad_producto',
        'tienda',
    ];
}
