<?php

namespace App\Entities;

use Illuminate\Database\Eloquent\Model;

class Ciudad extends Model
{
    protected $table = 'ciudads';

    protected $fillable = [
        'departamento_id',
        'name',
    ];

    public function descuento(){
        return $this->belongsTo(Departamento::class);
    }
}
