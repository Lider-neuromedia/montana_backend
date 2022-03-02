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

    protected $hidden = [
        'created_at',
        'updated_at',
    ];

    public function detallePedido()
    {
        return $this->belongsTo(Pedido::class, 'pedido');
    }

    public function detalleProducto()
    {
        return $this->belongsTo(Producto::class, 'producto');
    }

    public function detalleTienda()
    {
        return $this->belongsTo(Tienda::class, 'tienda');
    }
}
