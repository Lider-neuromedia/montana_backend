<?php

namespace App\Entities;

use Illuminate\Database\Eloquent\Model;

class Preguntas extends Model
{
    protected $table = 'preguntas';

    protected $primaryKey = 'id_pregunta';

    protected $fillable = [
        'encuesta',
        'pregunta',
    ];
}
