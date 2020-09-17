<?php

namespace App\Entities;

use Illuminate\Database\Eloquent\Model;

class Catalogo extends Model
{

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
