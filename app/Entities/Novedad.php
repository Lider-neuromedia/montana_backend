<?php

namespace App\Entities;

use Illuminate\Database\Eloquent\Model;
use OwenIt\Auditing\Contracts\Auditable;

class Novedad extends Model implements Auditable
{
    use \OwenIt\Auditing\Auditable;

    protected $table = 'novedades';

    protected $primaryKey = 'id_novedad';

    protected $fillable = [
        'tipo',
        'descripcion',
        'pedido',
    ];

    public function novedadPedido()
    {
        return $this->belongsTo(Pedido::class, 'pedido');
    }
}
