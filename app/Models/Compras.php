<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class Compras extends Model
{
    use HasFactory;

    protected $fillable = [
        'proveedor_id',
        'fecha',
        'total',
        'estado'

    ];
    public function proveedor()
    {
        return $this->belongsTo(Proveedor::class);
    }
    public function detalles()
    {
        return $this->hasMany(DetalleCompra::class, 'compra_id');
    }

    // SCOPES
    public function scopeBuscar($query, $termino)
    {
        if (!$termino) { return $query; }
        return $query->where(function ($q) use ($termino) {
            $q->where('estado', 'like', "%{$termino}%")
              ->orWhere('fecha', 'like', "%{$termino}%");
        });
    }
    public function scopePorEstado($query, $estado)
    {
        if (!$estado) { return $query; }
        return $query->where('estado', $estado);
    }
    public function scopeEntreFechas($query, $desde, $hasta)
    {
        if ($desde) { $query->whereDate('fecha', '>=', $desde); }
        if ($hasta) { $query->whereDate('fecha', '<=', $hasta); }
        return $query;
    }
    
    // MÉTODOS ESTÁTICOS CRUD
    public static function crearCompra($data)
    {
        // Crea compra sin detalles; preferir registrarCompra para afectar stock
        try {
            return DB::transaction(function () use ($data) {
                return self::create([
                    'proveedor_id' => $data['proveedor_id'] ?? null,
                    'fecha' => $data['fecha'],
                    'total' => $data['total'] ?? 0,
                    'estado' => $data['estado'] ?? 'realizada',
                ]);
            });
        } catch (\Exception $e) {
            throw new \Exception('Error al crear compra: ' . $e->getMessage());
        }
    }

    public static function registrarCompra($data)
    {
        // Estructura esperada: proveedor_id, fecha, estado?, detalles: [ {producto_id, cantidad, precio_unitario} ]
        try {
            return DB::transaction(function () use ($data) {
                $detalles = $data['detalles'] ?? [];
                if (empty($detalles)) {
                    throw new \Exception('La compra debe tener al menos un detalle.');
                }

                // Calcular totales
                $total = 0;
                foreach ($detalles as &$d) {
                    $cantidad = (int) ($d['cantidad'] ?? 0);
                    $precio = (float) ($d['precio_unitario'] ?? 0);
                    if ($cantidad <= 0 || $precio < 0) {
                        throw new \Exception('Detalle de compra inválido.');
                    }
                    $d['subtotal'] = round($cantidad * $precio, 2);
                    $total += $d['subtotal'];
                }

                // Crear compra
                $compra = self::create([
                    'proveedor_id' => $data['proveedor_id'] ?? null,
                    'fecha' => $data['fecha'],
                    'total' => round($total, 2),
                    'estado' => $data['estado'] ?? 'realizada',
                ]);

                // Crear detalles y actualizar stock
                foreach ($detalles as $d) {
                    DetalleCompra::create([
                        'compra_id' => $compra->id,
                        'producto_id' => $d['producto_id'],
                        'cantidad' => $d['cantidad'],
                        'precio_unitario' => $d['precio_unitario'],
                        'subtotal' => $d['subtotal'],
                    ]);

                    $producto = Productos::findOrFail($d['producto_id']);
                    $producto->increment('stock', (int) $d['cantidad']);
                }

                return $compra->load('detalles');
            });
        } catch (\Exception $e) {
            throw new \Exception('Error al registrar la compra: ' . $e->getMessage());
        }
    }

    public static function actualizarCompra($id,$data)
    {
        // Estrategia: reemplazar detalles y re-ajustar stock (revertir primero, luego aplicar nuevos)
        try {
            return DB::transaction(function () use ($id, $data) {
                $compra = self::with('detalles')->findOrFail($id);

                if (!empty($data['detalles'])) {
                    // Revertir stock por detalles actuales
                    foreach ($compra->detalles as $det) {
                        $producto = Productos::findOrFail($det->producto_id);
                        $nuevoStock = $producto->stock - (int) $det->cantidad;
                        if ($nuevoStock < 0) {
                            throw new \Exception('No se puede revertir el stock: quedaría negativo para el producto ID ' . $producto->id);
                        }
                        $producto->decrement('stock', (int) $det->cantidad);
                        $det->delete();
                    }

                    // Crear nuevos detalles y aplicar stock
                    $total = 0;
                    foreach ($data['detalles'] as $d) {
                        $cantidad = (int) ($d['cantidad'] ?? 0);
                        $precio = (float) ($d['precio_unitario'] ?? 0);
                        if ($cantidad <= 0 || $precio < 0) {
                            throw new \Exception('Detalle de compra inválido.');
                        }
                        $subtotal = round($cantidad * $precio, 2);
                        $total += $subtotal;

                        DetalleCompra::create([
                            'compra_id' => $compra->id,
                            'producto_id' => $d['producto_id'],
                            'cantidad' => $cantidad,
                            'precio_unitario' => $precio,
                            'subtotal' => $subtotal,
                        ]);

                        $producto = Productos::findOrFail($d['producto_id']);
                        $producto->increment('stock', $cantidad);
                    }

                    $data['total'] = round($total, 2);
                }

                $compra->update([
                    'proveedor_id' => $data['proveedor_id'] ?? $compra->proveedor_id,
                    'fecha' => $data['fecha'] ?? $compra->fecha,
                    'total' => $data['total'] ?? $compra->total,
                    'estado' => $data['estado'] ?? $compra->estado,
                ]);

                return $compra->fresh(['detalles']);
            });
        } catch (\Exception $e) {
            throw new \Exception('Error al actualizar compra: ' . $e->getMessage());
        }
    }

    public static function eliminarCompra($id)
    {
        try {
            return DB::transaction(function () use ($id) {
                $compra = self::with('detalles')->findOrFail($id);

                // Revertir stock por cada detalle
                foreach ($compra->detalles as $det) {
                    $producto = Productos::findOrFail($det->producto_id);
                    $nuevoStock = $producto->stock - (int) $det->cantidad;
                    if ($nuevoStock < 0) {
                        throw new \Exception('No se puede eliminar la compra: revertir stock dejaría negativo el producto ID ' . $producto->id);
                    }
                    $producto->decrement('stock', (int) $det->cantidad);
                    $det->delete();
                }

                return $compra->delete();
            });
        } catch (\Exception $e) {
            throw new \Exception('Error al eliminar compra: ' . $e->getMessage());
        }
    }

    public static function obtenerCompra($id)
    {
        try {
            return self::with(['proveedor', 'detalles' => function ($q) {
                $q->with('producto');
            }])->findOrFail($id);
        } catch (\Exception $e) {
            throw new \Exception('Compra no encontrada: ' . $e->getMessage());
        }
    }

    public static function listarCompras($filtros = [])
    {
        try {
            $query = self::with(['proveedor']);

            if (!empty($filtros['buscar'])) {
                $query->buscar($filtros['buscar']);
            }
            if (!empty($filtros['estado'])) {
                $query->porEstado($filtros['estado']);
            }
            if (!empty($filtros['desde']) || !empty($filtros['hasta'])) {
                $query->entreFechas($filtros['desde'] ?? null, $filtros['hasta'] ?? null);
            }

            $query->orderByDesc('fecha');

            return isset($filtros['paginar']) && $filtros['paginar']
                ? $query->paginate($filtros['por_pagina'] ?? 15)
                : $query->get();
        } catch (\Exception $e) {
            throw new \Exception('Error al listar compras: ' . $e->getMessage());
        }
    }

    // MÉTODOS DE NEGOCIO
    public static function comprasPorProveedor($proveedorId, $limite = 10)
    {
        return self::where('proveedor_id', $proveedorId)
            ->orderByDesc('fecha')
            ->take($limite)
            ->get();
    }

    public static function estadisticasGenerales()
    {
        try {
            return [
                'total_compras' => self::count(),
                'compras_mes' => self::whereBetween('fecha', [now()->startOfMonth(), now()->endOfMonth()])->count(),
                'monto_mes' => (float) self::whereBetween('fecha', [now()->startOfMonth(), now()->endOfMonth()])->sum('total'),
            ];
        } catch (\Exception $e) {
            throw new \Exception('Error al obtener estadísticas de compras: ' . $e->getMessage());
        }
    }

    // ACCESSORS
    public function getCantidadItemsAttribute()
    {
        return (int) $this->detalles()->sum('cantidad');
    }

    public function getTotalDetalleAttribute()
    {
        return (float) $this->detalles()->sum('subtotal');
    }
}
