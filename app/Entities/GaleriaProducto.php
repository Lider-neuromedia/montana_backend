<?php

namespace App\Entities;

use Illuminate\Database\Eloquent\Model;
use OwenIt\Auditing\Contracts\Auditable;

class GaleriaProducto extends Model implements Auditable
{
    use \OwenIt\Auditing\Auditable;

    protected $table = 'galeria_productos';

    protected $primaryKey = 'id_galeria_prod';

    protected $fillable = [
        'producto',
        'img',
        'name_img',
        'destacada',
    ];
}
