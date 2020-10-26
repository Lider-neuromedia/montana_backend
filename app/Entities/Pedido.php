<?php

namespace App\Entities;

use Illuminate\Database\Eloquent\Model;

class Pedido extends Model
{
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
        'vendedor',
        'cliente',
        'estado',
    ];
    
    public function products(){
        return $this->belongsToMany('App\Entities\Producto', 'pedido_productos', 'pedido', 'producto');
    }

}
