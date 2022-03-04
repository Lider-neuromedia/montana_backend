<?php

namespace App\Entities;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use OwenIt\Auditing\Contracts\Auditable;

class Catalogo extends Model implements Auditable
{
    use \OwenIt\Auditing\Auditable;
    use SoftDeletes;

    protected $table = 'catalogos';

    protected $primaryKey = 'id_catalogo';

    protected $fillable = [
        'titulo',
        'estado',
        'tipo',
        'imagen',
        'etiqueta',
        'descuento',
        'cantidad',
    ];

    protected $hidden = [
        'deleted_at',
    ];

    public function productos()
    {
        return $this->hasMany(Producto::class, 'catalogo');
    }

    public function descuento()
    {
        return $this->hasOne(Descuento::class);
    }

    /**
     * Actualizar stock de productos en cantidades de catalogos.
     */
    public static function corregirCantidadDeProductosEnCatalogos()
    {
        $catalogos = Catalogo::all();

        foreach ($catalogos as $catalogo) {
            self::refrescarCantidadDeProductos($catalogo);
        }
    }

    public static function refrescarCantidadDeProductos(Catalogo $catalogo)
    {
        $catalogo->update([
            'cantidad' => $catalogo->productos()->count(),
        ]);
    }
}
