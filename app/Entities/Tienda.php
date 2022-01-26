<?php

namespace App\Entities;

use Illuminate\Database\Eloquent\Model;

class Tienda extends Model
{
    protected $table = 'tiendas';

    protected $primaryKey = 'id_tiendas';

    protected $fillable = [
        'nombre',
        'lugar',
        'local',
        'direccion',
        'telefono',
        'cliente',
    ];
}
