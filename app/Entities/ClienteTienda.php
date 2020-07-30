<?php

namespace App\Entities;

use Illuminate\Database\Eloquent\Model;

class ClienteTienda extends Model
{
    protected $table = 'cliente_tiendas';

    protected $fillable = [
        'cliente_id',
        'tienda_id',
    ];

}
