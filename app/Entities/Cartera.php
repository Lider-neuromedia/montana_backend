<?php

namespace App\Entities;

use Illuminate\Database\Eloquent\Model;
use OwenIt\Auditing\Contracts\Auditable;

class Cartera extends Model implements Auditable
{
    use \OwenIt\Auditing\Auditable;

    protected $table = 'carteras';

    protected $fillable = [
        "identificador",
        "sucursal",
        "tipo",
        "factura",
        "fecha_factura",
        "fecha_vencimiento",
        "total",
        "saldo",
    ];

    protected $dates = [
        "fecha_factura",
        "fecha_vencimiento",
    ];

    public function vendedor()
    {
        return $this->belongsTo(User::class);
    }
}
