<?php

namespace App\Entities;

use Illuminate\Database\Eloquent\Model;
use OwenIt\Auditing\Contracts\Auditable;

class SeguimientoPqrs extends Model implements Auditable
{
    use \OwenIt\Auditing\Auditable;

    protected $table = 'seguimiento_pqrs';

    protected $primaryKey = 'id_seguimiento';

    protected $fillable = [
        'usuario',
        'pqrs',
        'mensaje',
        'hora',
    ];

    public function user()
    {
        return $this->belongsTo(User::class, 'usuario');
    }

    public function pqrsGroup()
    {
        return $this->belongsTo(Pqrs::class, 'pqrs');
    }
}
