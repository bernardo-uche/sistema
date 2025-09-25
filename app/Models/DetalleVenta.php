<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DetalleVenta extends Model
{
    use HasFactory;

    protected $table = 'detalle_ventas';

    protected $fillable = [
        'venta_id',
        'producto_id',
        'cantidad',
        'precio_unitario',
        'subtotal',
    ];

    protected $casts = [
        'cantidad' => 'integer',
        'precio_unitario' => 'decimal:2',
        'subtotal' => 'decimal:2',
    ];

    // RELACIONES
    public function venta()
    {
        return $this->belongsTo(Venta::class, 'venta_id');
    }

    public function producto()
    {
        return $this->belongsTo(Productos::class, 'producto_id');
    }

    // SCOPES
    public function scopeDeProducto($query, $productoId)
    {
        if (!$productoId) { return $query; }
        return $query->where('producto_id', $productoId);
    }

    public function scopeDeVenta($query, $ventaId)
    {
        if (!$ventaId) { return $query; }
        return $query->where('venta_id', $ventaId);
    }

    // ACCESSORS
    public function getDescripcionProductoAttribute()
    {
        return optional($this->producto)->nombre;
    }

    public function getTotalLineaAttribute()
    {
        return (float) $this->subtotal;
    }
}
