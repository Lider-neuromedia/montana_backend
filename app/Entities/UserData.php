<?php

namespace App\Entities;

use Illuminate\Database\Eloquent\Model;
use OwenIt\Auditing\Contracts\Auditable;

class UserData extends Model implements Auditable
{
    use \OwenIt\Auditing\Auditable;

    protected $table = 'user_data';

    protected $fillable = [
        'user_id',
        'field_key',
        'value_key',
    ];

    protected $hidden = [
        'created_at',
        'updated_at',
    ];

    public function usuario()
    {
        return $this->belongsTo(User::class, 'user_id');
    }
}
