<?php

namespace App\Entities;

use Illuminate\Database\Eloquent\Model;

class Cartera extends Model
{
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
