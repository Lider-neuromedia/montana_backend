<?php

namespace App\Entities;

use Illuminate\Database\Eloquent\Model;

class Producto extends Model
{
    protected $table = 'productos';

    protected $fillable = [
        'catalogo_id',
        'categoria_id',
        'descuento_id',
        'iva_id',
        'marca_id',
        'nombre',
        'codigo',
        'referencia',
        'sku',
        'stock',
        'precio',
        'precio_descuento',
        'descripcion_larga',
        'descripcion_corta',
        'imagen',
        'total',
    ];
}
