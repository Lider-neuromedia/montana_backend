<?php

namespace App\Entities;

use Illuminate\Database\Eloquent\Model;
use OwenIt\Auditing\Contracts\Auditable;

class SeguimientoPqrs extends Model implements Auditable
{
    use \OwenIt\Auditing\Auditable;

    protected $table = 'seguimiento_pqrs';

    protected $primaryKey = 'id_seguimiento';

    protected $fillable = [
        'usuario',
        'pqrs',
        'mensaje',
        'hora',
    ];
}
