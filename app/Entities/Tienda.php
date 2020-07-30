<?php

namespace App\Entities;

use Illuminate\Database\Eloquent\Model;

class Tienda extends Model
{
    protected $table = 'tiendas';

    protected $fillable = [
        'ciudad_id',
        'name',
        'place',
        'address',
        'phone',
        'code',
    ];
}
