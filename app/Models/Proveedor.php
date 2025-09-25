<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class Proveedor extends Model
{
    use HasFactory;
    
    // Forzamos el nombre de la tabla para que coincida con la migración 'proveedor'
    protected $table = 'proveedor';

    protected $fillable = [
        'nombre',
        'telefono',
        'direccion',
    ];

    // RELACIONES
    public function productos()
    {
        return $this->hasMany(Productos::class, 'proveedor_id');
    }

    public function compras()
    {
        return $this->hasMany(Compras::class, 'proveedor_id');
    }

    // SCOPES
    public function scopeBuscar($query, $termino)
    {
        if (!$termino) { return $query; }
        return $query->where(function ($q) use ($termino) {
            $q->where('nombre', 'like', "%{$termino}%")
              ->orWhere('telefono', 'like', "%{$termino}%")
              ->orWhere('direccion', 'like', "%{$termino}%");
        });
    }

    public function scopeConProductos($query)
    {
        return $query->has('productos');
    }

    public function scopeConCompras($query)
    {
        return $query->has('compras');
    }

    public function scopeOrdenadoPorNombre($query)
    {
        return $query->orderBy('nombre', 'asc');
    }

    // MÉTODOS ESTÁTICOS CRUD
    public static function crearProveedor($data)
    {
        try {
            return DB::transaction(function () use ($data) {
                return self::create($data);
            });
        } catch (\Exception $e) {
            throw new \Exception('Error al crear proveedor: ' . $e->getMessage());
        }
    }
    public static function actualizarProveedor($id,$data)
    {
        try {
            return DB::transaction(function () use ($id, $data) {
                $proveedor = self::findOrFail($id);
                $proveedor->update($data);
                return $proveedor->fresh();
            });
        } catch (\Exception $e) {
            throw new \Exception('Error al actualizar proveedor: ' . $e->getMessage());
        }
    }
    public static function eliminarProveedor($id)
    {
        try {
            return DB::transaction(function () use ($id) {
                $proveedor = self::withCount(['productos', 'compras'])->findOrFail($id);
                if ($proveedor->productos_count > 0 || $proveedor->compras_count > 0) {
                    throw new \Exception('No se puede eliminar el proveedor porque tiene productos o compras asociadas');
                }
                return $proveedor->delete();
            });
        } catch (\Exception $e) {
            throw new \Exception('Error al eliminar proveedor: ' . $e->getMessage());
        }
    }
    public static function obtenerCienProveedores()
    {
        //paginar por cantidad de 100 preoductos
        return self::paginate(100);
    }

    public static function obtenerTodosProveedores()
    {
        return self::all();
    }

    public static function obtenerProveedor($id)
    {
        try {
            return self::with(['productos' => function ($q) {
                    $q->orderBy('nombre')->take(10);
                }, 'compras' => function ($q) {
                    $q->latest()->take(5);
                }])
                ->findOrFail($id);
        } catch (\Exception $e) {
            throw new \Exception('Proveedor no encontrado: ' . $e->getMessage());
        }
    }

    public static function listarProveedores($filtros = [])
    {
        try {
            $query = self::query();

            if (!empty($filtros['buscar'])) {
                $query->buscar($filtros['buscar']);
            }
            if (!empty($filtros['con_productos'])) {
                $query->conProductos();
            }
            if (!empty($filtros['con_compras'])) {
                $query->conCompras();
            }

            $query->ordenadoPorNombre();

            return isset($filtros['paginar']) && $filtros['paginar']
                ? $query->paginate($filtros['por_pagina'] ?? 15)
                : $query->get();
        } catch (\Exception $e) {
            throw new \Exception('Error al listar proveedores: ' . $e->getMessage());
        }
    }

    // MÉTODOS DE NEGOCIO
    public static function proveedoresConMasCompras($limite = 10)
    {
        try {
            return self::withCount('compras')
                ->having('compras_count', '>', 0)
                ->orderBy('compras_count', 'desc')
                ->take($limite)
                ->get();
        } catch (\Exception $e) {
            throw new \Exception('Error al obtener proveedores por cantidad de compras: ' . $e->getMessage());
        }
    }

    public static function proveedoresPorMontoTotal($limite = 10)
    {
        try {
            return self::select('proveedor.*', DB::raw('SUM(compras.total) as total_comprado'))
                ->join('compras', 'proveedor.id', '=', 'compras.proveedor_id')
                ->groupBy('proveedor.id', 'proveedor.nombre', 'proveedor.telefono', 'proveedor.direccion', 'proveedor.created_at', 'proveedor.updated_at')
                ->orderByDesc('total_comprado')
                ->take($limite)
                ->get();
        } catch (\Exception $e) {
            throw new \Exception('Error al obtener proveedores por monto total: ' . $e->getMessage());
        }
    }

    public static function estadisticasGenerales()
    {
        try {
            return [
                'total_proveedores' => self::count(),
                'proveedores_con_productos' => self::conProductos()->count(),
                'proveedores_con_compras' => self::conCompras()->count(),
            ];
        } catch (\Exception $e) {
            throw new \Exception('Error al obtener estadísticas de proveedores: ' . $e->getMessage());
        }
    }

    // ACCESSORS
    public function getCantidadProductosAttribute()
    {
        return $this->productos()->count();
    }

    public function getCantidadComprasAttribute()
    {
        return $this->compras()->count();
    }

}
