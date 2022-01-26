<?php

namespace App\Entities;

use Illuminate\Database\Eloquent\Model;

class Marca extends Model
{
    protected $table = 'marcas';

    protected $primaryKey = 'id_marca';

    public $timestamps = false;

    protected $fillable = [
        'nombre_marca',
    ];

    public function productos()
    {
        return $this->hasMany(Producto::class, 'marca', 'id_marca');
    }
}
