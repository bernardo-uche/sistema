<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Http\Requests\StoreProductoRequest;
use App\Http\Requests\UpdateProductoRequest;
use App\Models\Productos;
use Illuminate\Http\JsonResponse;
use Inertia\Inertia;
use Inertia\Response as InertiaResponse;
use App\Models\Proveedor;
use App\Models\Categoria;

class ProductosController extends Controller
{
    // Listado con filtros (query string): buscar, activos, bajo_stock, umbral, paginar, por_pagina
    public function index(Request $request): InertiaResponse
    {
        $filtros = $request->only(['buscar', 'activos', 'bajo_stock', 'umbral', 'paginar', 'por_pagina']);
        $data = Productos::listarProductos($filtros);
        return Inertia::render('Productos/Index', [
            // La vista espera la prop `producto` (singular), ver resources/js/pages/Productos/Index.vue
            'producto' => $data,
        ]);
    }

    // Mostrar formulario de creación
    public function create(): InertiaResponse
    {
        return Inertia::render('Productos/Create', [
            'proveedores' => Proveedor::select('id', 'nombre')->orderBy('nombre')->get(),
            'categorias'  => Categoria::select('id', 'nombre')->orderBy('nombre')->get(),
        ]);
    }

    // Crear producto (Inertia espera redirect)
    public function store(StoreProductoRequest $request)
    {
        try {
            Productos::crearProducto($request->validated());
            return redirect()->route('productos.index')
                             ->with('success', 'Producto creado correctamente');
        } catch (\Exception $e) {
            return redirect()->route('productos.index')
                             ->with('error', $e->getMessage());
        }
    }

    // Obtener un producto
    public function show(int $id): JsonResponse
    {
        try {
            $producto = Productos::obtenerProducto($id);
            return response()->json([
                'success' => true,
                'data' => $producto,
                'message' => 'Producto obtenido correctamente'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'data' => null,
                'message' => $e->getMessage(),
            ], 404);
        }
    }

    // Mostrar formulario de edición
    public function edit(int $id): InertiaResponse
    {
        $producto = Productos::with(['proveedor:id,nombre', 'categoria:id,nombre'])->findOrFail($id);
        return Inertia::render('Productos/Edit', [
            'producto'    => $producto,
            'proveedores' => Proveedor::select('id', 'nombre')->orderBy('nombre')->get(),
            'categorias'  => Categoria::select('id', 'nombre')->orderBy('nombre')->get(),
        ]);
    }

    // Actualizar (Inertia espera redirect)
    public function update(UpdateProductoRequest $request, int $id)
    {
        try {
            Productos::actualizarProducto($id, $request->validated());
            return redirect()->route('productos.index')
                             ->with('success', 'Producto actualizado correctamente');
        } catch (\Exception $e) {
            return redirect()->route('productos.index')
                             ->with('error', $e->getMessage());
        }
    }

    // Eliminar con validación de movimientos (se maneja en el modelo)
    public function destroy(int $id)
    {
        try {
            Productos::eliminarProducto($id);
            // Inertia espera un redirect después de un DELETE
            return redirect()->route('productos.index')
                             ->with('success', 'Producto eliminado correctamente');
        } catch (\Exception $e) {
            return redirect()->route('productos.index')
                             ->with('error', $e->getMessage());
        }
    }

    // Reportes / estadísticas
    public function reporteInventario(): JsonResponse
    {
        try {
            $reporte = Productos::reporteInventario();
            return response()->json([
                'success' => true,
                'data' => $reporte,
                'message' => 'Reporte de inventario generado'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'data' => null,
                'message' => $e->getMessage(),
            ], 500);
        }
    }

    public function masVendidos(Request $request): JsonResponse
    {
        try {
            $limite = (int) ($request->get('limite', 10));
            $data = Productos::masVendidos($limite);
            return response()->json([
                'success' => true,
                'data' => $data,
                'message' => 'Productos más vendidos obtenidos'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'data' => null,
                'message' => $e->getMessage(),
            ], 500);
        }
    }

    public function estadisticas(): JsonResponse
    {
        try {
            $totales = [
                'total_productos' => Productos::count(),
                'activos' => Productos::activos()->count(),
                'bajo_stock' => Productos::bajoStock()->count(),
            ];
            return response()->json([
                'success' => true,
                'data' => $totales,
                'message' => 'Estadísticas de productos obtenidas'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'data' => null,
                'message' => $e->getMessage(),
            ], 500);
        }
    }
}




