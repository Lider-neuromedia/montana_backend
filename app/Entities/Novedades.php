<?php

namespace App\Entities;

use Illuminate\Database\Eloquent\Model;

class Novedades extends Model
{
    protected $table = 'novedades';

    protected $primaryKey = 'id_novedad';

    protected $fillable = [
        'tipo',
        'descripcion',
        'pedido',
    ];
}
