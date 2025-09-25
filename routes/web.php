<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ClienteController;
use App\Http\Controllers\ProveedorController;
use App\Http\Controllers\ProductosController;
use App\Http\Controllers\CategoriaController;
use App\Http\Controllers\ComprasController;
use Inertia\Inertia;

// Página pública principal (renderiza el componente Vue 'Welcome.vue').
Route::get('/', function () {
    return Inertia::render('Welcome');
})->name('home');

// Página de dashboard protegida: requiere usuario autenticado y verificado.
// Renderiza el componente Vue 'Dashboard.vue'.
Route::get('dashboard', function () {
    return Inertia::render('Dashboard');
})->middleware(['auth', 'verified'])->name('dashboard');
// RUTA DE CLIENTE
Route::middleware(['auth', 'verified'])->group(function () {
    // Crea rutas RESTful /cliente (index, create, store, edit, update, destroy, show)
    // Los controladores deberían retornar Inertia::render() para páginas Vue.
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
// PÁGINAS INERTIA PARA CATEGORÍA (UI)
Route::middleware(['auth', 'verified'])->group(function () {
    Route::get('categoria', fn () => Inertia::render('Categoria/Index'))->name('categoria.index.page');
    Route::get('categoria/create', fn () => Inertia::render('Categoria/Create'))->name('categoria.create.page');
    Route::get('categoria/{id}/edit', fn (int $id) => Inertia::render('Categoria/Edit', ['id' => $id]))->name('categoria.edit.page');
});
// RUTA PARA CATEGORIA (API JSON bajo prefijo /api)
Route::middleware(['auth', 'verified'])->prefix('api')->group(function () {
    // Estadísticas de categoría
    Route::get('categoria/estadisticas', [CategoriaController::class, 'estadisticas'])->name('api.categoria.estadisticas');

    // Solo las acciones implementadas en el controlador
    Route::resource('categoria', CategoriaController::class)->only([
        'index', 'store', 'show', 'update', 'destroy'
    ])->names('api.categoria');
});

//RUTA PARA COMPRAS.

Route::middleware(['auth', 'verified'])->group(function () {
    Route::resource('compras', ComprasController::class);
});




// Rutas adicionales separadas (auth: login/register/etc. y ajustes de usuario)
require __DIR__.'/settings.php';
require __DIR__.'/auth.php';

