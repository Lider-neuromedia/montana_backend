<?php

namespace App\Entities;

use Illuminate\Database\Eloquent\Model;
use OwenIt\Auditing\Contracts\Auditable;

class Marca extends Model implements Auditable
{
    use \OwenIt\Auditing\Auditable;

    protected $table = 'marcas';

    protected $primaryKey = 'id_marca';

    public $timestamps = false;

    protected $fillable = [
        'nombre_marca',
        'codigo',
    ];

    public function productos()
    {
        return $this->hasMany(Producto::class, 'marca', 'id_marca');
    }
}
