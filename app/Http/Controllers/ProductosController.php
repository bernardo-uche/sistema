<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Inertia\Inertia;
use App\Models\Productos;
use App\Models\Proveedor;
use App\Models\Categoria;

class ProductosController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        //
        $productos = Productos::with(['categoria', 'proveedor'])->get();
        return Inertia::render('Productos/Index', [
            'producto' => $productos,
        ]);

    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return Inertia::render('Productos/Create', [
            'proveedores' => Proveedor::select('id', 'nombre')->get(),
            'categorias' => Categoria::select('id', 'nombre')->get(),
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'nombre' => 'required|string|max:255',
            'stock' => 'required|integer|min:0',
            'precio_unitario' => 'required|numeric|min:0',
            'costo_unitario' => 'required|numeric|min:0',
            'fecha_vencimiento' => 'nullable|date',
            'proveedor_id' => 'required|exists:proveedors,id',
            'categoria_id' => 'required|exists:categorias,id',
            'estado' => 'required|string|in:Activo,Inactivo',
        ]);

        Productos::create($validated);

        return redirect()->route('productos.index')
                         ->with('success', 'Productos creado correctamente.');
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
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        //

        $productos = Productos::findOrFail($id);
        $productos->delete();

        return redirect()->route('productos.index')
                         ->with('success', 'Cliente eliminado correctamente.');
    }
}




