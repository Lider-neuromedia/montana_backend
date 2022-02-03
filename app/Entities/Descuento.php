<?php

namespace App\Entities;

use Illuminate\Database\Eloquent\Model;
use OwenIt\Auditing\Contracts\Auditable;

class Descuento extends Model implements Auditable
{
    use \OwenIt\Auditing\Auditable;

    protected $table = 'descuentos';

    protected $fillable = [
        'descuento',
    ];
}
