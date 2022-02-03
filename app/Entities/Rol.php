<?php

namespace App\Entities;

use Illuminate\Database\Eloquent\Model;
use OwenIt\Auditing\Contracts\Auditable;

class Rol extends Model implements Auditable
{
    use \OwenIt\Auditing\Auditable;

    protected $table = 'roles';

    protected $fillable = [
        'name',
        'guard_name',
    ];
}
