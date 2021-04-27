<?php

namespace App\Entities;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Producto extends Model
{
    use SoftDeletes;

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
