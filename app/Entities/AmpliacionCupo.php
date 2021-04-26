<?php

namespace App\Entities;

use Illuminate\Database\Eloquent\Model;

class AmpliacionCupo extends Model
{

    protected $table = 'ampliacion_cupo';

    protected $primaryKey = 'id_cupo';

    protected $fillable = [
        'codigo_solicitud',
        'fecha_solicitud',
        'vendedor',
        'cliente',
        'file_doc_identidad',
        'file_rut',
        'file_camara_comercio',
        'monto',
        'estado'
    ];

    public function vendedor(){
        return $this->hasOne('App\Entities\User', 'vendedor');
    }

    public function cliente(){
        return $this->hasOne('App\Entities\User', 'cliente');
    }

}