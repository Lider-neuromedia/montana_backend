<?php

namespace App\Entities;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use OwenIt\Auditing\Contracts\Auditable;

class Producto extends Model implements Auditable
{
    use \OwenIt\Auditing\Auditable;
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

    protected $hidden = [
        'deleted_at',
    ];

    public function productoMarca()
    {
        return $this->belongsTo(Marca::class, 'marca');
    }

    public function productoCatalogo()
    {
        return $this->belongsTo(Catalogo::class, 'catalogo');
    }

    public function imagenes()
    {
        return $this->hasMany(GaleriaProducto::class, 'producto');
    }

    public function detalles()
    {
        return $this->hasMany(Detalle::class, 'producto', 'id_producto');
    }
}
