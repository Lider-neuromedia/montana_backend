<?php

namespace App\Entities;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use OwenIt\Auditing\Contracts\Auditable;

class Catalogo extends Model implements Auditable
{
    use \OwenIt\Auditing\Auditable;
    use SoftDeletes;

    protected $table = 'catalogos';

    protected $primaryKey = 'id_catalogo';

    protected $fillable = [
        'descuento_id',
        'estado',
        'tipo',
        'imagen',
        'titulo',
        'etiqueta',
    ];

    public function descuento()
    {
        return $this->hasOne(Descuento::class);
    }
}
