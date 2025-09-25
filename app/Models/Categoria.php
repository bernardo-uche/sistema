<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class Categoria extends Model
{
    use HasFactory;

    protected $fillable = [
        'nombre',
    ];

    // RELACIONES
    public function productos()
    {
        return $this->hasMany(Productos::class, 'categoria_id');
    }

    // SCOPES
    public function scopeBuscar($query, $termino)
    {
        if (!$termino) { return $query; }
        return $query->where('nombre', 'like', "%{$termino}%");
    }

    public function scopeOrdenadoPorNombre($query)
    {
        return $query->orderBy('nombre', 'asc');
    }

    public function scopeConProductos($query)
    {
        return $query->has('productos');
    }

    // MÉTODOS ESTÁTICOS CRUD
    public static function crearCategoria($data)
    {
        try {
            return DB::transaction(function () use ($data) {
                return self::create([
                    'nombre' => $data['nombre'],
                ]);
            });
        } catch (\Exception $e) {
            throw new \Exception('Error al crear categoría: ' . $e->getMessage());
        }
    }

    public static function actualizarCategoria($id, $data)
    {
        try {
            return DB::transaction(function () use ($id, $data) {
                $categoria = self::findOrFail($id);
                $categoria->update([
                    'nombre' => $data['nombre'] ?? $categoria->nombre,
                ]);
                return $categoria->fresh();
            });
        } catch (\Exception $e) {
            throw new \Exception('Error al actualizar categoría: ' . $e->getMessage());
        }
    }

    public static function eliminarCategoria($id)
    {
        try {
            return DB::transaction(function () use ($id) {
                $categoria = self::withCount('productos')->findOrFail($id);
                
                // Verificar si tiene productos asociados
                if ($categoria->productos_count > 0) {
                    throw new \Exception('No se puede eliminar la categoría porque tiene productos asociados');
                }
                
                return $categoria->delete();
            });
        } catch (\Exception $e) {
            throw new \Exception('Error al eliminar categoría: ' . $e->getMessage());
        }
    }

    public static function obtenerCategoria($id)
    {
        try {
            return self::with(['productos' => function ($query) {
                $query->activos()->take(10);
            }])->findOrFail($id);
        } catch (\Exception $e) {
            throw new \Exception('Categoría no encontrada: ' . $e->getMessage());
        }
    }

    public static function listarCategorias($filtros = [])
    {
        try {
            $query = self::query();

            if (!empty($filtros['buscar'])) {
                $query->buscar($filtros['buscar']);
            }

            if (isset($filtros['con_productos']) && $filtros['con_productos']) {
                $query->conProductos();
            }

            $query->ordenadoPorNombre();

            return isset($filtros['paginar']) && $filtros['paginar'] 
                ? $query->paginate($filtros['por_pagina'] ?? 15)
                : $query->get();
        } catch (\Exception $e) {
            throw new \Exception('Error al listar categorías: ' . $e->getMessage());
        }
    }

    // MÉTODOS DE NEGOCIO ESTÁTICOS
    public static function categoriasConMasProductos($limite = 10)
    {
        try {
            return self::withCount('productos')
                ->having('productos_count', '>', 0)
                ->orderBy('productos_count', 'desc')
                ->take($limite)
                ->get();
        } catch (\Exception $e) {
            throw new \Exception('Error al obtener categorías con más productos: ' . $e->getMessage());
        }
    }

    public static function estadisticasGenerales()
    {
        try {
            return [
                'total_categorias' => self::count(),
                'categorias_con_productos' => self::conProductos()->count(),
                'categorias_sin_productos' => self::doesntHave('productos')->count(),
            ];
        } catch (\Exception $e) {
            throw new \Exception('Error al obtener estadísticas de categorías: ' . $e->getMessage());
        }
    }

    // ACCESSORS
    public function getCantidadProductosAttribute()
    {
        return $this->productos()->count();
    }

    public function getProductosActivosAttribute()
    {
        return $this->productos()->activos()->count();
    }
}
