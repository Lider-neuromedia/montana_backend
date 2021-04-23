<?php

namespace App\Entities;

use Illuminate\Database\Eloquent\Model;

class EncuestaPregunta extends Model
{
    protected $table = 'encuesta_preguntas';

    protected $fillable = [
        'encuesta_id',
        'pregunta_id',
        'user_id',
        'valoracion_id',
    ];
}
