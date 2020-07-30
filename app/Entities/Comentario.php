<?php

namespace App\Entities;

use Illuminate\Database\Eloquent\Model;

class Comentario extends Model
{
    protected $table = 'comentarios';

    protected $fillable = [
        'producto_id',
        'user_id',
        'valoracion_id',
        'titulo',
        'descripcion',
    ];

    public function producto(){
        return $this->belongsTo(Producto::class);
    }

    public function user(){
        return $this->belongsTo(User::class);
    }

    public function valoracion(){
        return $this->belongsTo(Valoracion::class);
    }
}
