<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Inertia\Inertia;
use App\Models\Proveedor;

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
            'nombre' => 'required|string|max:255',
            'telefono' => 'nullable|string|max:20',
            'direccion' => 'required|string|max:255',
        ]);

        Proveedor::create($validated);

        return redirect()->route('proveedor.index')
                         ->with('success', 'Cliente creado correctamente.');
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
            'nombre' => 'required|string|max:255',
            'telefono' => 'nullable|string|max:20',
            'direccion' => 'required|string|max:255',
        ]);

        $proveedor = Proveedor::findOrFail($id);

        $proveedor->update($validated);

        return redirect()->route('proveedor.index')
                        ->with('success', 'Cliente actualizado correctamente.');
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
                         ->with('success', 'Cliente eliminado correctamente.');
    }
}
