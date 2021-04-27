<?php

namespace App\Entities;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Catalogo extends Model
{
    use SoftDeletes;

    protected $table = 'catalogos';

    protected $primaryKey = 'id_catalogo';

    protected $fillable = [
        'descuento_id',
        'estado',
        'tipo',
        'imagen',
        'titulo',
    ];

    public function descuento(){
        return $this->hasOne(Descuento::class);
    }
}
