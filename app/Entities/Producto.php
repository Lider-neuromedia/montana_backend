<?php

namespace App\Entities;

use Illuminate\Database\Eloquent\Model;

class Producto extends Model
{
    protected $table = 'productos';

    protected $primaryKey = 'id_producto';

    protected $fillable = [
        'nombre',
        'codigo',
        'referencia',
        'stock',
        'precio',
        'descripcion',
        'sku',
        'total',
        'descuento',
        'iva',
        'catalogo',
        'marca',
    ];
    
}
