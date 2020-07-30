<?php

namespace App\Entities;

use Illuminate\Database\Eloquent\Model;

class Iva extends Model
{
    protected $table = 'ivas';

    protected $fillable = [
        'iva',
    ];
}
