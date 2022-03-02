<?php

namespace App\Entities;

use Illuminate\Database\Eloquent\Model;
use OwenIt\Auditing\Contracts\Auditable;

class Estado extends Model implements Auditable
{
    use \OwenIt\Auditing\Auditable;

    protected $table = 'estados';

    protected $primaryKey = 'id_estado';

    protected $fillable = [
        'estado',
    ];
}
