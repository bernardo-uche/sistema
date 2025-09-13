<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ClienteController;
use App\Http\Controllers\ProveedorController;
use App\Http\Controllers\ProductosController;
use App\Http\Controllers\CategoriaController;
use App\Http\Controllers\ComprasController;
use Inertia\Inertia;

Route::get('/', function () {
    return Inertia::render('Welcome');
})->name('home');

Route::get('dashboard', function () {
    return Inertia::render('Dashboard');
})->middleware(['auth', 'verified'])->name('dashboard');
// RUTA DE CLIENTE
Route::middleware(['auth', 'verified'])->group(function () {
    Route::resource('cliente', ClienteController::class);
});
//RUTA DE PROVEEDOR
Route::middleware(['auth', 'verified'])->group(function () {
    Route::resource('proveedor', ProveedorController::class);
});
// RUTA DE PRODUCTOS
Route::middleware(['auth', 'verified'])->group(function () {
    Route::resource('productos', ProductosController::class);
});
// RUTA PARA CATEGORIA
Route::middleware(['auth', 'verified'])->group(function () {
    Route::resource('categoria', CategoriaController::class);
});

//RUTA PARA COMPRAS.

Route::middleware(['auth', 'verified'])->group(function () {
    Route::resource('compras', ComprasController::class);
});




require __DIR__.'/settings.php';
require __DIR__.'/auth.php';
