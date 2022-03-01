<?php

namespace App\Entities;

use Illuminate\Database\Eloquent\Model;
use OwenIt\Auditing\Contracts\Auditable;

class AmpliacionCupo extends Model implements Auditable
{
    use \OwenIt\Auditing\Auditable;

    protected $table = 'ampliacion_cupo';

    protected $primaryKey = 'id_cupo';

    protected $fillable = [
        'codigo_solicitud',
        'fecha_solicitud',
        'vendedor',
        'cliente',
        'doc_identidad',
        'doc_rut',
        'doc_camara_com',
        'monto',
        'estado',
    ];

    protected $hidden = [
        'created_at',
        'updated_at',
    ];

    public function ampliacionVendedor()
    {
        return $this->belongsTo(User::class, 'vendedor');
    }

    public function ampliacionCliente()
    {
        return $this->belongsTo(User::class, 'cliente');
    }
}
