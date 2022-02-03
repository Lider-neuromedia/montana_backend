<?php

namespace App\Entities;

use Illuminate\Database\Eloquent\Model;
use OwenIt\Auditing\Contracts\Auditable;

class Pqrs extends Model implements Auditable
{
    use \OwenIt\Auditing\Auditable;

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
