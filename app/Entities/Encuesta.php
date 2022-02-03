<?php

namespace App\Entities;

use Illuminate\Database\Eloquent\Model;
use OwenIt\Auditing\Contracts\Auditable;

class Encuesta extends Model implements Auditable
{
    use \OwenIt\Auditing\Auditable;

    protected $table = 'encuestas';

    protected $primaryKey = 'id_form';

    protected $fillable = [
        'codigo',
        'fecha_creacion',
        'catalogo',
        'tipo',
        'estado',
    ];
}
