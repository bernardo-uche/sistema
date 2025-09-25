<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Inertia\Inertia;
use App\Models\Proveedor;
use App\Http\Requests\StoreProveedorRequest;
use App\Http\Requests\UpdateProveedorRequest;
use Illuminate\Http\JsonResponse;

class ProveedorController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        //
        return Inertia::render('Proveedor/Index', [
        'proveedor'=> Proveedor::all(),
       ]);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        //
         return Inertia::render('Proveedor/Create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'nombre' => 'required|string|max:100',
            'telefono' => 'nullable|string|max:20',
            'direccion' => 'required|string|max:255',
        ]);

        Proveedor::create($validated);

        return redirect()->route('proveedor.index')
                         ->with('success', 'Proveedor creado correctamente.');
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        //
        $proveedor = Proveedor::findOrFail($id);
        return Inertia::render('Proveedor/Edit', [
            'proveedor' => $proveedor
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        //

        $validated = $request->validate([
            'nombre' => 'required|string|max:100',
            'telefono' => 'nullable|string|max:20',
            'direccion' => 'required|string|max:255',
        ]);

        $proveedor = Proveedor::findOrFail($id);

        $proveedor->update($validated);

        return redirect()->route('proveedor.index')
                        ->with('success', 'Proveedor actualizado correctamente.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        //

        $proveedor = Proveedor::findOrFail($id);
        $proveedor->delete();

        return redirect()->route('proveedor.index')
                         ->with('success', 'Proveedor eliminado correctamente.');
    }

    // --- API JSON ENDPOINTS ---
    public function indexApi(Request $request): JsonResponse
    {
        try {
            $filtros = $request->only(['buscar', 'con_productos', 'con_compras', 'paginar', 'por_pagina']);
            $data = Proveedor::listarProveedores($filtros);
            return response()->json([
                'success' => true,
                'data' => $data,
                'message' => 'Listado de proveedores obtenido correctamente'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'data' => null,
                'message' => $e->getMessage(),
            ], 500);
        }
    }

    public function storeApi(StoreProveedorRequest $request): JsonResponse
    {
        try {
            $proveedor = Proveedor::crearProveedor($request->validated());
            return response()->json([
                'success' => true,
                'data' => $proveedor,
                'message' => 'Proveedor creado correctamente'
            ], 201);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'data' => null,
                'message' => $e->getMessage(),
            ], 500);
        }
    }

    public function showApi(int $id): JsonResponse
    {
        try {
            $proveedor = Proveedor::obtenerProveedor($id);
            return response()->json([
                'success' => true,
                'data' => $proveedor,
                'message' => 'Proveedor obtenido correctamente'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'data' => null,
                'message' => $e->getMessage(),
            ], 404);
        }
    }

    public function updateApi(UpdateProveedorRequest $request, int $id): JsonResponse
    {
        try {
            $proveedor = Proveedor::actualizarProveedor($id, $request->validated());
            return response()->json([
                'success' => true,
                'data' => $proveedor,
                'message' => 'Proveedor actualizado correctamente'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'data' => null,
                'message' => $e->getMessage(),
            ], 500);
        }
    }

    public function destroyApi(int $id): JsonResponse
    {
        try {
            Proveedor::eliminarProveedor($id);
            return response()->json([
                'success' => true,
                'data' => null,
                'message' => 'Proveedor eliminado correctamente'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'data' => null,
                'message' => $e->getMessage(),
            ], 400);
        }
    }

    public function masCompras(Request $request): JsonResponse
    {
        try {
            $limite = (int) ($request->get('limite', 10));
            $data = Proveedor::proveedoresConMasCompras($limite);
            return response()->json([
                'success' => true,
                'data' => $data,
                'message' => 'Proveedores con más compras obtenidos'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'data' => null,
                'message' => $e->getMessage(),
            ], 500);
        }
    }

    public function porMonto(Request $request): JsonResponse
    {
        try {
            $limite = (int) ($request->get('limite', 10));
            $data = Proveedor::proveedoresPorMontoTotal($limite);
            return response()->json([
                'success' => true,
                'data' => $data,
                'message' => 'Proveedores por monto total obtenidos'
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
            $data = Proveedor::estadisticasGenerales();
            return response()->json([
                'success' => true,
                'data' => $data,
                'message' => 'Estadísticas de proveedores obtenidas'
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
