<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Compras;
use App\Models\Proveedor;
use App\Models\Productos;
use App\Http\Requests\StoreCompraRequest;
use App\Http\Requests\UpdateCompraRequest;
use Illuminate\Http\JsonResponse;
use Inertia\Inertia;
use Inertia\Response as InertiaResponse;
use Illuminate\Http\RedirectResponse;



class ComprasController extends Controller
{
    // Listado con filtros: buscar, estado, desde, hasta, paginar, por_pagina
    public function index(Request $request): InertiaResponse|JsonResponse
    {
        try {
            $filtros = $request->only(['buscar', 'estado', 'desde', 'hasta', 'paginar', 'por_pagina']);
            $data = Compras::listarCompras($filtros);
            return Inertia::render('Compras/Index', [
                // La vista espera la prop `producto` (singular), ver resources/js/pages/Productos/Index.vue
                'Compras' => $data,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'data' => null,
                'message' => $e->getMessage(),
            ], 500);
        }
    }

    // Página de creación (Inertia)
    public function create(): InertiaResponse
    {
        $proveedores = Proveedor::select('id','nombre')->orderBy('nombre')->get();
        $productos = Productos::select('id','nombre','precio_unitario')->orderBy('nombre')->get();
        return Inertia::render('Compras/Create', [
            'proveedores' => $proveedores,
            'productos' => $productos,
        ]);
    }

    // Registrar compra con detalles (ajusta stock)
    public function store(StoreCompraRequest $request): RedirectResponse
    {
        try {
            $compra = Compras::registrarCompra($request->validated());
            return redirect()->route('compras.index')
                ->with('success', 'Compra registrada correctamente');
        } catch (\Exception $e) {
            return back()
                ->withErrors(['general' => $e->getMessage()])
                ->withInput();
        }
    }

    // Obtener compra con detalles
    public function show(int $id): JsonResponse
    {
        try {
            $compra = Compras::obtenerCompra($id);
            return response()->json([
                'success' => true,
                'data' => $compra,
                'message' => 'Compra obtenida correctamente'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'data' => null,
                'message' => $e->getMessage(),
            ], 404);
        }
    }

    // Actualizar compra (reajusta stock)
    public function update(UpdateCompraRequest $request, int $id): JsonResponse
    {
        try {
            $compra = Compras::actualizarCompra($id, $request->validated());
            return response()->json([
                'success' => true,
                'data' => $compra,
                'message' => 'Compra actualizada correctamente'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'data' => null,
                'message' => $e->getMessage(),
            ], 400);
        }
    }

    // Eliminar compra (revierte stock)
    public function destroy(int $id): JsonResponse
    {
        try {
            Compras::eliminarCompra($id);
            return response()->json([
                'success' => true,
                'data' => null,
                'message' => 'Compra eliminada correctamente'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'data' => null,
                'message' => $e->getMessage(),
            ], 400);
        }
    }

    // Estadísticas
    public function estadisticas(): JsonResponse
    {
        try {
            $data = Compras::estadisticasGenerales();
            return response()->json([
                'success' => true,
                'data' => $data,
                'message' => 'Estadísticas de compras obtenidas'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'data' => null,
                'message' => $e->getMessage(),
            ], 500);
        }
    }

    public function porProveedor(Request $request): JsonResponse
    {
        try {
            $proveedorId = (int) $request->get('proveedor_id');
            $limite = (int) ($request->get('limite', 10));
            $data = Compras::comprasPorProveedor($proveedorId, $limite);
            return response()->json([
                'success' => true,
                'data' => $data,
                'message' => 'Compras por proveedor obtenidas'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'data' => null,
                'message' => $e->getMessage(),
            ], 400);
        }
    }
}
