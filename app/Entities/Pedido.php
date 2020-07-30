<?php

namespace App\Entities;

use Illuminate\Database\Eloquent\Model;

class Pedido extends Model
{
    protected $table = 'pedidos';

    protected $fillable = [
        'producto_id',
        'estado_id',
        'vendedor_id',
        'cliente_id',
        'direccion',
        'fecha',
        'codigo',
        'valor',
        'total',
    ];

    public function producto(){
        return $this->belongsTo(Producto::class);
    }

    public function estado(){
        return $this->belongsTo(Estado::class);
    }

    public function vendedor(){
        return $this->belongsTo(User::class);
    }

    public function cliente(){
        return $this->belongsTo(User::class);
    }
}
