<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use App\Models\Venta;
use App\Models\Productos;
use App\Models\Cliente;
use App\Http\Requests\StoreVentaRequest;
use App\Http\Requests\UpdateVentaRequest;
use Inertia\Inertia;
use Inertia\Response as InertiaResponse;
use Illuminate\Http\RedirectResponse;

class VentaController extends Controller
{
    // Listado con filtros: buscar, estado, desde, hasta, con_cliente, paginar, por_pagina
    public function index(Request $request): InertiaResponse|JsonResponse
    {
        try {
            $filtros = $request->only(['buscar', 'estado', 'desde', 'hasta', 'con_cliente', 'paginar', 'por_pagina']);
            $data = Venta::listarVentas($filtros);
            return Inertia::render('Venta/Index', [
                // La vista espera la prop `producto` (singular), ver resources/js/pages/Productos/Index.vue
                'Ventas' => $data,
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
    $cliente = Cliente::select('id','nombre')->orderBy('nombre')->get();
    $productos = Productos::select('id','nombre','precio_unitario')->orderBy('nombre')->get();
    return Inertia::render('Venta/Create', [
        'cliente' => $cliente,
        'productos' => $productos,
    ]);
}


    // Registrar venta con detalles (valida stock y descuenta)
    public function store(StoreVentaRequest $request): RedirectResponse
    {
        try {
            $venta = Venta::registrarVenta($request->validated());
            return redirect()->route('venta.index')
            ->with('success', 'Compra registrada correctamente');
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'data' => null,
                'message' => $e->getMessage(),
            ], 400);
        }
    }

    // Obtener venta con detalles
    public function show(int $id): JsonResponse
    {
        try {
            $venta = Venta::obtenerVenta($id);
            return response()->json([
                'success' => true,
                'data' => $venta,
                'message' => 'Venta obtenida correctamente'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'data' => null,
                'message' => $e->getMessage(),
            ], 404);
        }
    }

    // Actualizar venta (reajusta stock)
    public function update(UpdateVentaRequest $request, int $id): JsonResponse
    {
        try {
            $venta = Venta::actualizarVenta($id, $request->validated());
            return response()->json([
                'success' => true,
                'data' => $venta,
                'message' => 'Venta actualizada correctamente'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'data' => null,
                'message' => $e->getMessage(),
            ], 400);
        }
    }

    // Eliminar venta (revierte stock)
    public function destroy(int $id): JsonResponse
    {
        try {
            Venta::eliminarVenta($id);
            return response()->json([
                'success' => true,
                'data' => null,
                'message' => 'Venta eliminada correctamente'
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
            $data = Venta::estadisticasGenerales();
            return response()->json([
                'success' => true,
                'data' => $data,
                'message' => 'Estadísticas de ventas obtenidas'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'data' => null,
                'message' => $e->getMessage(),
            ], 500);
        }
    }

    public function porCliente(Request $request): JsonResponse
    {
        try {
            $clienteId = (int) $request->get('cliente_id');
            $limite = (int) ($request->get('limite', 10));
            $data = Venta::ventasPorCliente($clienteId, $limite);
            return response()->json([
                'success' => true,
                'data' => $data,
                'message' => 'Ventas por cliente obtenidas'
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
