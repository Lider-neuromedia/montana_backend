<?php

namespace App\Entities;

use Illuminate\Database\Eloquent\Model;

class Encuesta extends Model
{
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
