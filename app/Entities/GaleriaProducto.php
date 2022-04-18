<?php

namespace App\Entities;

use Illuminate\Database\Eloquent\Model;
use OwenIt\Auditing\Contracts\Auditable;

class GaleriaProducto extends Model implements Auditable
{
    use \OwenIt\Auditing\Auditable;

    protected $table = 'galeria_productos';

    protected $primaryKey = 'id_galeria_prod';

    protected $fillable = [
        'image',
        'name_img',
        'destacada',
        'producto',
    ];

    protected $hidden = [
        'created_at',
        'updated_at',
    ];

    public function imagenProducto()
    {
        return $this->belongsTo(Producto::class, 'producto');
    }

    public function getUrlAttribute()
    {
        $timestamp = $this->updated_at->timestamp;
        $url = url($this->image);
        return "$url?id=$timestamp";
    }
}
