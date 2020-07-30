<?php

namespace App\Entities;

use Illuminate\Database\Eloquent\Model;

class GaleriaProducto extends Model
{
    protected $table = 'galeria_productos';

    protected $fillable = [
        'producto_id',
        'image',
    ];
}
