<?php

namespace App\Entities;

use Illuminate\Database\Eloquent\Model;
use OwenIt\Auditing\Contracts\Auditable;

class Valoracion extends Model implements Auditable
{
    use \OwenIt\Auditing\Auditable;

    protected $table = 'valoraciones';

    protected $primaryKey = 'id_valoracion';

    protected $fillable = [
        'pregunta',
        'usuario',
        'producto',
        'respuesta',
    ];
}
