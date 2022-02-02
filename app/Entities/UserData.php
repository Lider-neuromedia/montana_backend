<?php

namespace App\Entities;

use Illuminate\Database\Eloquent\Model;

class UserData extends Model
{
    protected $table = 'user_data';

    protected $fillable = [
        'user_id',
        'field_key',
        'value_key',
    ];

    protected $hidden = [
        'created_at',
        'updated_at',
    ];
}
