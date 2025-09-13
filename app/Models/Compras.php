<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Compras extends Model
{
    //
    protected $fillable = [
        'proveedor_id',
        'fecha',
        'total',
        'estado'

    ];
}
