<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Cliente;
use App\Http\Requests\StoreClienteRequest;
use App\Http\Requests\UpdateClienteRequest;
use Illuminate\Http\JsonResponse;
use Inertia\Inertia;
use Inertia\Response as InertiaResponse;
use Illuminate\Http\RedirectResponse;

class ClienteController extends Controller
{
    // Lista de clientes: devuelve una página Inertia con los datos necesarios
    public function index(Request $request): InertiaResponse
    {
        $filtros = $request->only(['buscar', 'con_ventas', 'activos', 'paginar', 'por_pagina']);
        $clientes = Cliente::listarClientes($filtros);

        return Inertia::render('Cliente/Index', [
            'cliente' => $clientes,
        ]);
    }

    // Mostrar formulario de creación (página Inertia)
    public function create(): InertiaResponse
    {
        return Inertia::render('Cliente/Create');
    }

    // Crear
    public function store(StoreClienteRequest $request): RedirectResponse
    {
        Cliente::crearCliente($request->validated());
        return redirect()->route('cliente.index');
    }

    // Mostrar formulario de edición (página Inertia)
    public function edit(int $id): InertiaResponse
    {
        $cliente = Cliente::obtenerCliente($id);
        return Inertia::render('Cliente/Edit', [
            'cliente' => $cliente,
        ]);
    }

    // Actualizar
    public function update(UpdateClienteRequest $request, int $id): RedirectResponse
    {
        Cliente::actualizarCliente($id, $request->validated());
        return redirect()->route('cliente.index');
    }

    // Eliminar (valida ventas asociadas en el modelo)
    public function destroy(int $id): RedirectResponse
    {
        Cliente::eliminarCliente($id);
        return redirect()->route('cliente.index');
    }
}

