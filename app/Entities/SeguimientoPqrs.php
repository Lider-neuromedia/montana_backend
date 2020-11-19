<?php

namespace App\Entities;

use Illuminate\Database\Eloquent\Model;

class SeguimientoPqrs extends Model{
    protected $table = 'seguimiento_pqrs';

    protected $primaryKey = 'id_seguimiento';

    protected $fillable = [
        'usuario',
        'pqrs',
        'mensaje',
        'hora'
    ];
}
