<?php

namespace App\Entities;

use Illuminate\Database\Eloquent\Model;

class VendedorCliente extends Model
{
    protected $table = 'vendedor_cliente';

    protected $fillable = [
        'vendedor',
        'cliente',
    ];

    public function user_vendedor(){
        // return $this->hasMany(User::class);
        // return $this->hasMany(User::class,'vendedor_id');
        return $this->belongsTo(User::class,'id');
    }

}
