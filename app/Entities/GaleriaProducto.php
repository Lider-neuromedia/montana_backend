<?php

namespace App\Entities;

use Illuminate\Database\Eloquent\Model;

class GaleriaProducto extends Model
{
    protected $table = 'galeria_productos';

    protected $primaryKey = 'id_galeria_prod';

    protected $fillable = [
        'producto',
        'img',
        'destacada',
    ];

}
