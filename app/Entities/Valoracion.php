<?php

namespace App\Entities;

use Illuminate\Database\Eloquent\Model;

class Valoracion extends Model{
    
    protected $table = 'valoraciones';

    protected $primaryKey = 'id_valoracion';

    protected $fillable = [
        'pregunta',
        'usuario',
        'producto',
        'respuesta'
    ];

}
