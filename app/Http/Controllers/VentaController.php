<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use App\Models\Venta;
use App\Http\Requests\StoreVentaRequest;
use App\Http\Requests\UpdateVentaRequest;

class VentaController extends Controller
{
    // Listado con filtros: buscar, estado, desde, hasta, con_cliente, paginar, por_pagina
    public function index(Request $request): JsonResponse
    {
        try {
            $filtros = $request->only(['buscar', 'estado', 'desde', 'hasta', 'con_cliente', 'paginar', 'por_pagina']);
            $data = Venta::listarVentas($filtros);
            return response()->json([
                'success' => true,
                'data' => $data,
                'message' => 'Listado de ventas obtenido'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'data' => null,
                'message' => $e->getMessage(),
            ], 500);
        }
    }

    // Registrar venta con detalles (valida stock y descuenta)
    public function store(StoreVentaRequest $request): JsonResponse
    {
        try {
            $venta = Venta::registrarVenta($request->validated());
            return response()->json([
                'success' => true,
                'data' => $venta,
                'message' => 'Venta registrada correctamente'
            ], 201);
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
