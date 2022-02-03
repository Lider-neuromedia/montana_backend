<?php

namespace App\Entities;

use Illuminate\Database\Eloquent\Model;
use OwenIt\Auditing\Contracts\Auditable;

class Tienda extends Model implements Auditable
{
    use \OwenIt\Auditing\Auditable;

    protected $table = 'tiendas';

    protected $primaryKey = 'id_tiendas';

    protected $fillable = [
        'nombre',
        'lugar',
        'local',
        'direccion',
        'telefono',
        'cliente',
        "sucursal",
        "fecha_ingreso",
        "fecha_ultima_compra",
        "cupo",
        "ciudad_codigo",
        "zona",
        "bloqueado",
        "bloqueado_fecha",
        "nombre_representante",
        "plazo",
        "escala_factura",
        "observaciones",
    ];

    protected $dates = [
        "fecha_ingreso",
        "fecha_ultima_compra",
        "bloqueado_fecha",
    ];
}
