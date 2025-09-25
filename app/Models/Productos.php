<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class Productos extends Model
{
    use HasFactory;

    protected $table = 'productos';
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
        return $this->belongsTo(Proveedor::class);
    }

    // Relaciones detalle
    public function detalleCompras()
    {
        return $this->hasMany(DetalleCompra::class, 'producto_id');
    }
    public function detalleVentas()
    {
        return $this->hasMany(DetalleVenta::class, 'producto_id');
    }

    // Scopes
    public function scopeBuscar($query, $termino)
    {
        if (!$termino) { return $query; }
        return $query->where(function ($q) use ($termino) {
            $q->where('nombre', 'like', "%{$termino}%")
              ->orWhere('estado', 'like', "%{$termino}%");
        });
    }
    public function scopeActivos($query)
    {
        return $query->where('estado', 'activo');
    }
    public function scopeBajoStock($query, $umbral = 5)
    {
        return $query->where('stock', '<', $umbral);
    }

    public static function crearProducto($data)
    {
        try {
            return DB::transaction(function () use ($data) {
                return self::create($data);
            });
        } catch (\Exception $e) {
            throw new \Exception('Error al crear producto: ' . $e->getMessage());
        }
    }
    public static function actualizarProducto($id,$data)
    {
        try {
            return DB::transaction(function () use ($id, $data) {
                $producto = self::findOrFail($id);
                $producto->update($data);
                return $producto->fresh();
            });
        } catch (\Exception $e) {
            throw new \Exception('Error al actualizar producto: ' . $e->getMessage());
        }
    }
    public static function eliminarProducto($id)
    {
        try {
            return DB::transaction(function () use ($id) {
                $producto = self::withCount(['detalleCompras', 'detalleVentas'])->findOrFail($id);
                if ($producto->detalle_compras_count > 0 || $producto->detalle_ventas_count > 0) {
                    throw new \Exception('No se puede eliminar el producto porque tiene movimientos asociados');
                }
                return $producto->delete();
            });
        } catch (\Exception $e) {
            throw new \Exception('Error al eliminar producto: ' . $e->getMessage());
        }
    }
    public static function obtenerCienProductos($Productos = null)
    {
        //paginar por cantidad de 100 preoductos
        return self::paginate(100);
    }

    public static function obtenerTodosProductos($Productos = null)
    {
        return self::all();
    }

    // Listado con filtros
    public static function listarProductos($filtros = [])
    {
        try {
            $query = self::with(['categoria', 'proveedor']);

            if (!empty($filtros['buscar'])) {
                $query->buscar($filtros['buscar']);
            }
            if (!empty($filtros['activos'])) {
                $query->activos();
            }
            if (!empty($filtros['bajo_stock'])) {
                $query->bajoStock($filtros['umbral'] ?? 5);
            }

            $query->orderBy('nombre');

            return isset($filtros['paginar']) && $filtros['paginar']
                ? $query->paginate($filtros['por_pagina'] ?? 15)
                : $query->get();
        } catch (\Exception $e) {
            throw new \Exception('Error al listar productos: ' . $e->getMessage());
        }
    }

    // Métodos de negocio
    public static function reporteInventario()
    {
        return self::select(
                'productos.*',
                DB::raw('(stock * costo_unitario) as valor_inventario_costo'),
                DB::raw('(stock * precio_unitario) as valor_inventario_venta')
            )
            ->with(['categoria', 'proveedor'])
            ->orderBy('nombre')
            ->get();
    }

    public static function masVendidos($limite = 10)
    {
        return self::select('productos.*', DB::raw('SUM(detalle_ventas.cantidad) as cantidad_vendida'))
            ->join('detalle_ventas', 'detalle_ventas.producto_id', '=', 'productos.id')
            ->groupBy('productos.id', 'productos.nombre', 'productos.stock', 'productos.precio_unitario', 'productos.costo_unitario', 'productos.fecha_vencimiento', 'productos.proveedor_id', 'productos.categoria_id', 'productos.estado', 'productos.created_at', 'productos.updated_at')
            ->orderByDesc('cantidad_vendida')
            ->take($limite)
            ->get();
    }

    // Accessors
    public function getGananciaUnitariaAttribute()
    {
        return max(0, ($this->precio_unitario - $this->costo_unitario));
    }

    public function getMargenPorcentajeAttribute()
    {
        if ($this->costo_unitario <= 0) { return null; }
        return round((($this->precio_unitario - $this->costo_unitario) / $this->costo_unitario) * 100, 2);
    }

}
