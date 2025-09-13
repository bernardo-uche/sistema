<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Inertia\Inertia;
use App\Models\Cliente;

class ClienteController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        //
        return Inertia::render('Cliente/Index', [
        'cliente'=> Cliente::all(),
       ]);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        //
        return Inertia::render('Cliente/Create');
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

        Cliente::create($validated);

        return redirect()->route('cliente.index')
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
        $cliente = Cliente::findOrFail($id);
        return Inertia::render('Cliente/Edit', [
            'cliente' => $cliente
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

        $cliente = Cliente::findOrFail($id);

        $cliente->update($validated);

        return redirect()->route('cliente.index')
                        ->with('success', 'Cliente actualizado correctamente.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        //

        $cliente = Cliente::findOrFail($id);
        $cliente->delete();

        return redirect()->route('cliente.index')
                         ->with('success', 'Cliente eliminado correctamente.');
    }
}
