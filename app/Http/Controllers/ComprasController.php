<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Inertia\Inertia;
use App\Models\Compras;



class ComprasController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        //
         return Inertia::render('Compras/Create', [
        'compra'=> Compras::all(),
       ]);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        //
        
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
{
    $request->validate([
        'proveedor_id' => 'required|exists:proveedores,id',
        'detalles' => 'required|array|min:1',
    ]);

    $compra = Compra::create([
        'proveedor_id' => $request->proveedor_id,
        'fecha' => now(),
        'total' => collect($request->detalles)->sum(fn($d)=>$d['cantidad'] * $d['precio']),
    ]);

    foreach($request->detalles as $d){
        $compra->detalles()->create([
            'producto_id'=>$d['productoId'],
            'cantidad'=>$d['cantidad'],
            'precio'=>$d['precio'],
            'subtotal'=>$d['cantidad'] * $d['precio'],
        ]);

        // actualizar stock
        $producto = Producto::find($d['productoId']);
        $producto->stock += $d['cantidad'];
        $producto->save();
    }

    return redirect()->route('compras.create')->with('success','Compra registrada correctamente');
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
    }
}
