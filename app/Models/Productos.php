<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Productos extends Model
{
    //
    protected $fillable = [
        'nombre',
        'stock',
        'precio_unitario',
        'costo_unitario',
        'fecha_vencimiento',
        'proveedor_id',
        'categoria_id',
        'estado',
    ];

public function categoria()
{
    return $this->belongsTo(Categoria::class);
}
public function proveedor()
{
    return $this->belongsTo(proveedor::class);
}
}



