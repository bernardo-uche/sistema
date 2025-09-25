<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class Cliente extends Model
{
    use HasFactory;

    protected $fillable = [
        'nombre',
        'telefono',
        'direccion'
    ];

    // RELACIONES
    public function ventas()
    {
        return $this->hasMany(Venta::class);
    }

    // SCOPES
    public function scopeBuscar($query, $termino)
    {
        return $query->where(function ($q) use ($termino) {
            $q->where('nombre', 'like', "%{$termino}%")
              ->orWhere('telefono', 'like', "%{$termino}%")
              ->orWhere('direccion', 'like', "%{$termino}%");
        });
    }

    public function scopeActivos($query)
    {
        // No hay columna deleted_at (SoftDeletes) en la migración actual.
        // Este scope queda como no-op para mantener compatibilidad con filtros existentes.
        return $query;
    }

    public function scopeConVentas($query)
    {
        return $query->has('ventas');
    }

    public function scopeOrdenadoPorNombre($query)
    {
        return $query->orderBy('nombre', 'asc');
    }

    // MÉTODOS ESTÁTICOS CRUD
    public static function crearCliente($data)
    {
        try {
            return DB::transaction(function () use ($data) {
                return self::create([
                    'nombre' => $data['nombre'],
                    'telefono' => $data['telefono'] ?? null,
                    'direccion' => $data['direccion'] ?? null
                ]);
            });
        } catch (\Exception $e) {
            throw new \Exception('Error al crear cliente: ' . $e->getMessage());
        }
    }

    public static function actualizarCliente($id, $data)
    {
        try {
            return DB::transaction(function () use ($id, $data) {
                $cliente = self::findOrFail($id);
                $cliente->update([
                    'nombre' => $data['nombre'],
                    'telefono' => $data['telefono'] ?? $cliente->telefono,
                    'direccion' => $data['direccion'] ?? $cliente->direccion
                ]);
                return $cliente->fresh();
            });
        } catch (\Exception $e) {
            throw new \Exception('Error al actualizar cliente: ' . $e->getMessage());
        }
    }

    public static function eliminarCliente($id)
    {
        try {
            return DB::transaction(function () use ($id) {
                $cliente = self::findOrFail($id);
                
                // Verificar si tiene ventas asociadas
                if ($cliente->ventas()->count() > 0) {
                    throw new \Exception('No se puede eliminar el cliente porque tiene ventas asociadas');
                }
                
                return $cliente->delete();
            });
        } catch (\Exception $e) {
            throw new \Exception('Error al eliminar cliente: ' . $e->getMessage());
        }
    }

    public static function obtenerCliente($id)
    {
        try {
            return self::with(['ventas' => function ($query) {
                $query->latest()->take(5);
            }])->findOrFail($id);
        } catch (\Exception $e) {
            throw new \Exception('Cliente no encontrado: ' . $e->getMessage());
        }
    }

    public static function listarClientes($filtros = [])
    {
        try {
            $query = self::query();

            if (!empty($filtros['buscar'])) {
                $query->buscar($filtros['buscar']);
            }

            if (isset($filtros['con_ventas']) && $filtros['con_ventas']) {
                $query->conVentas();
            }

            if (isset($filtros['activos']) && $filtros['activos']) {
                $query->activos();
            }

            $query->ordenadoPorNombre();

            return isset($filtros['paginar']) && $filtros['paginar'] 
                ? $query->paginate($filtros['por_pagina'] ?? 15)
                : $query->get();
        } catch (\Exception $e) {
            throw new \Exception('Error al listar clientes: ' . $e->getMessage());
        }
    }

    // MÉTODOS DE NEGOCIO ESTÁTICOS
    public static function clientesConMasCompras($limite = 10)
    {
        try {
            return self::withCount('ventas')
                ->having('ventas_count', '>', 0)
                ->orderBy('ventas_count', 'desc')
                ->take($limite)
                ->get();
        } catch (\Exception $e) {
            throw new \Exception('Error al obtener clientes con más compras: ' . $e->getMessage());
        }
    }

    public static function clientesPorMontoTotal($limite = 10)
    {
        try {
            return self::select('clientes.*')
                ->join('ventas', 'clientes.id', '=', 'ventas.cliente_id')
                ->select('clientes.*', DB::raw('SUM(ventas.total) as total_comprado'))
                ->groupBy('clientes.id', 'clientes.nombre', 'clientes.telefono', 'clientes.direccion', 'clientes.created_at', 'clientes.updated_at', 'clientes.deleted_at')
                ->orderBy('total_comprado', 'desc')
                ->take($limite)
                ->get();
        } catch (\Exception $e) {
            throw new \Exception('Error al obtener clientes por monto: ' . $e->getMessage());
        }
    }

    public static function estadisticasGenerales()
    {
        try {
            return [
                'total_clientes' => self::count(),
                'clientes_activos' => self::activos()->count(),
                'clientes_con_ventas' => self::conVentas()->count(),
                'clientes_sin_ventas' => self::doesntHave('ventas')->count(),
            ];
        } catch (\Exception $e) {
            throw new \Exception('Error al obtener estadísticas de clientes: ' . $e->getMessage());
        }
    }

    // ACCESSORS
    public function getTotalCompradoAttribute()
    {
        return $this->ventas()->sum('total');
    }

    public function getCantidadVentasAttribute()
    {
        return $this->ventas()->count();
    }

    public function getUltimaCompraAttribute()
    {
        return $this->ventas()->latest()->first();
    }

    public function getNombreCompletoAttribute()
    {
        $info = $this->nombre;
        if ($this->telefono) {
            $info .= ' - ' . $this->telefono;
        }
        return $info;
    }
}