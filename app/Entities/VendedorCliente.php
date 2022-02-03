<?php

namespace App\Entities;

use Illuminate\Database\Eloquent\Model;
use OwenIt\Auditing\Contracts\Auditable;

class VendedorCliente extends Model implements Auditable
{
    use \OwenIt\Auditing\Auditable;

    protected $table = 'vendedor_cliente';

    protected $fillable = [
        'vendedor',
        'cliente',
    ];

    public function user_vendedor()
    {
        return $this->belongsTo(User::class, 'id');
    }
}
