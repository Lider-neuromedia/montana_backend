<?php

namespace App\Entities;

use Illuminate\Database\Eloquent\Model;

class RolHasPermission extends Model
{
    protected $table = 'role_has_permissions';

    protected $fillable = [
        'permission_id',
        'role_id',
    ];
}
