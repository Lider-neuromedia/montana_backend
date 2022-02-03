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

    public function products()
    {
        return $this->belongsToMany('App\Entities\Producto', 'pedido_productos', 'pedido', 'producto');
    }
}
