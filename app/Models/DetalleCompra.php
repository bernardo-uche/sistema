<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\Compras;
use App\Models\Productos;

class DetalleCompra extends Model
{
    use HasFactory;

    protected $table = 'detalle_compras';

    protected $fillable = [
        'compra_id',
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
    public function compra()
    {
        return $this->belongsTo(Compras::class, 'compra_id');
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

    public function scopeDeCompra($query, $compraId)
    {
        if (!$compraId) { return $query; }
        return $query->where('compra_id', $compraId);
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
