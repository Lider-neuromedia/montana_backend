<?php

namespace App\Entities;

use Illuminate\Database\Eloquent\Model;
use OwenIt\Auditing\Contracts\Auditable;

class Pregunta extends Model implements Auditable
{
    use \OwenIt\Auditing\Auditable;

    protected $table = 'preguntas';

    protected $primaryKey = 'id_pregunta';

    protected $fillable = [
        'encuesta',
        'pregunta',
    ];
}
