<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class Venta extends Model
{
    use HasFactory;

    protected $fillable = [
        'cliente_id',
        'fecha',
        'total',
        'estado',
    ];

    // RELACIONES
    public function cliente()
    {
        return $this->belongsTo(Cliente::class, 'cliente_id');
    }

    public function detalles()
    {
        return $this->hasMany(DetalleVenta::class, 'venta_id');
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

    public function scopeConCliente($query)
    {
        return $query->whereNotNull('cliente_id');
    }

    // MÉTODOS ESTÁTICOS CRUD
    public static function crearVenta($data)
    {
        // Crea venta sin detalles; preferir registrarVenta para afectar stock
        try {
            return DB::transaction(function () use ($data) {
                return self::create([
                    'cliente_id' => $data['cliente_id'] ?? null,
                    'fecha' => $data['fecha'],
                    'total' => $data['total'] ?? 0,
                    'estado' => $data['estado'] ?? 'completada',
                ]);
            });
        } catch (\Exception $e) {
            throw new \Exception('Error al crear venta: ' . $e->getMessage());
        }
    }

    public static function registrarVenta($data)
    {
        // Estructura esperada: cliente_id?, fecha, estado?, detalles: [ {producto_id, cantidad, precio_unitario} ]
        try {
            return DB::transaction(function () use ($data) {
                $detalles = $data['detalles'] ?? [];
                if (empty($detalles)) {
                    throw new \Exception('La venta debe tener al menos un detalle.');
                }

                // Validaciones y cálculo de totales
                $total = 0;
                // Pre-validar stock
                foreach ($detalles as $d) {
                    $producto = Productos::findOrFail($d['producto_id']);
                    $cantidad = (int) ($d['cantidad'] ?? 0);
                    $precio = (float) ($d['precio_unitario'] ?? $producto->precio_unitario);
                    if ($cantidad <= 0 || $precio < 0) {
                        throw new \Exception('Detalle de venta inválido.');
                    }
                    if ($producto->stock < $cantidad) {
                        throw new \Exception('Stock insuficiente para el producto "' . $producto->nombre . '"');
                    }
                }

                foreach ($detalles as &$d) {
                    $producto = Productos::findOrFail($d['producto_id']);
                    $cantidad = (int) $d['cantidad'];
                    $precio = (float) ($d['precio_unitario'] ?? $producto->precio_unitario);
                    $d['precio_unitario'] = $precio;
                    $d['subtotal'] = round($cantidad * $precio, 2);
                    $total += $d['subtotal'];
                }

                // Crear venta
                $venta = self::create([
                    'cliente_id' => $data['cliente_id'] ?? null,
                    'fecha' => $data['fecha'],
                    'total' => round($total, 2),
                    'estado' => $data['estado'] ?? 'completada',
                ]);

                // Crear detalles y descontar stock
                foreach ($detalles as $d) {
                    DetalleVenta::create([
                        'venta_id' => $venta->id,
                        'producto_id' => $d['producto_id'],
                        'cantidad' => $d['cantidad'],
                        'precio_unitario' => $d['precio_unitario'],
                        'subtotal' => $d['subtotal'],
                    ]);

                    $producto = Productos::findOrFail($d['producto_id']);
                    $producto->decrement('stock', (int) $d['cantidad']);
                }

                return $venta->load(['cliente', 'detalles']);
            });
        } catch (\Exception $e) {
            throw new \Exception('Error al registrar la venta: ' . $e->getMessage());
        }
    }

    public static function actualizarVenta($id, $data)
    {
        // Reemplazar detalles y re-ajustar stock (revertir primero, luego aplicar nuevos)
        try {
            return DB::transaction(function () use ($id, $data) {
                $venta = self::with('detalles')->findOrFail($id);

                if (!empty($data['detalles'])) {
                    // Revertir stock por detalles actuales (devolver al inventario)
                    foreach ($venta->detalles as $det) {
                        $producto = Productos::findOrFail($det->producto_id);
                        $producto->increment('stock', (int) $det->cantidad);
                        $det->delete();
                    }

                    // Validar y aplicar nuevos detalles
                    $total = 0;
                    // Prevalidar stock
                    foreach ($data['detalles'] as $d) {
                        $producto = Productos::findOrFail($d['producto_id']);
                        $cantidad = (int) ($d['cantidad'] ?? 0);
                        $precio = (float) ($d['precio_unitario'] ?? $producto->precio_unitario);
                        if ($cantidad <= 0 || $precio < 0) {
                            throw new \Exception('Detalle de venta inválido.');
                        }
                        if ($producto->stock < $cantidad) {
                            throw new \Exception('Stock insuficiente para el producto "' . $producto->nombre . '"');
                        }
                    }

                    foreach ($data['detalles'] as $d) {
                        $producto = Productos::findOrFail($d['producto_id']);
                        $cantidad = (int) $d['cantidad'];
                        $precio = (float) ($d['precio_unitario'] ?? $producto->precio_unitario);
                        $subtotal = round($cantidad * $precio, 2);
                        $total += $subtotal;

                        DetalleVenta::create([
                            'venta_id' => $venta->id,
                            'producto_id' => $d['producto_id'],
                            'cantidad' => $cantidad,
                            'precio_unitario' => $precio,
                            'subtotal' => $subtotal,
                        ]);

                        $producto->decrement('stock', $cantidad);
                    }

                    $data['total'] = round($total, 2);
                }

                $venta->update([
                    'cliente_id' => $data['cliente_id'] ?? $venta->cliente_id,
                    'fecha' => $data['fecha'] ?? $venta->fecha,
                    'total' => $data['total'] ?? $venta->total,
                    'estado' => $data['estado'] ?? $venta->estado,
                ]);

                return $venta->fresh(['detalles']);
            });
        } catch (\Exception $e) {
            throw new \Exception('Error al actualizar venta: ' . $e->getMessage());
        }
    }

    public static function eliminarVenta($id)
    {
        try {
            return DB::transaction(function () use ($id) {
                $venta = self::with('detalles')->findOrFail($id);

                // Revertir stock (devolver) por cada detalle
                foreach ($venta->detalles as $det) {
                    $producto = Productos::findOrFail($det->producto_id);
                    $producto->increment('stock', (int) $det->cantidad);
                    $det->delete();
                }

                return $venta->delete();
            });
        } catch (\Exception $e) {
            throw new \Exception('Error al eliminar venta: ' . $e->getMessage());
        }
    }

    public static function obtenerVenta($id)
    {
        try {
            return self::with(['cliente', 'detalles' => function ($q) {
                $q->with('producto');
            }])->findOrFail($id);
        } catch (\Exception $e) {
            throw new \Exception('Venta no encontrada: ' . $e->getMessage());
        }
    }

    public static function listarVentas($filtros = [])
    {
        try {
            $query = self::with(['cliente']);

            if (!empty($filtros['buscar'])) {
                $query->buscar($filtros['buscar']);
            }
            if (!empty($filtros['estado'])) {
                $query->porEstado($filtros['estado']);
            }
            if (!empty($filtros['desde']) || !empty($filtros['hasta'])) {
                $query->entreFechas($filtros['desde'] ?? null, $filtros['hasta'] ?? null);
            }
            if (!empty($filtros['con_cliente'])) {
                $query->conCliente();
            }

            $query->orderByDesc('fecha');

            return isset($filtros['paginar']) && $filtros['paginar']
                ? $query->paginate($filtros['por_pagina'] ?? 15)
                : $query->get();
        } catch (\Exception $e) {
            throw new \Exception('Error al listar ventas: ' . $e->getMessage());
        }
    }

    // MÉTODOS DE NEGOCIO
    public static function ventasPorCliente($clienteId, $limite = 10)
    {
        return self::where('cliente_id', $clienteId)
            ->orderByDesc('fecha')
            ->take($limite)
            ->get();
    }

    public static function estadisticasGenerales()
    {
        try {
            return [
                'total_ventas' => self::count(),
                'ventas_mes' => self::whereBetween('fecha', [now()->startOfMonth(), now()->endOfMonth()])->count(),
                'monto_mes' => (float) self::whereBetween('fecha', [now()->startOfMonth(), now()->endOfMonth()])->sum('total'),
            ];
        } catch (\Exception $e) {
            throw new \Exception('Error al obtener estadísticas de ventas: ' . $e->getMessage());
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
