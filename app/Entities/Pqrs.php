<?php

namespace App\Entities;

use Illuminate\Database\Eloquent\Model;

class Pqrs extends Model{
    protected $table = 'pqrs';

    protected $primaryKey = 'id_pqrs';

    protected $fillable = [
        'codigo',
        'fecha_registro',
        'cliente',
        'vendedor',
        'estado',
        'tipo',
    ];
}
