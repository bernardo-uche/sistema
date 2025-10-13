<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ClienteController;
use App\Http\Controllers\ProveedorController;
use App\Http\Controllers\ProductosController;
use App\Http\Controllers\CategoriaController;
use App\Http\Controllers\ComprasController;
use App\Http\Controllers\VentaController;
use App\Http\Controllers\ReporteController;
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

//RUTA PARA VENTAS
Route::middleware(['auth', 'verified'])->group(function () {
    Route::resource('venta', VentaController::class);
});

// RUTAS PARA EL MÓDULO DE REPORTES
Route::middleware(['auth', 'verified'])->prefix('reportes')->name('reportes.')->group(function () {
    // --- Páginas (UI) ---
    // Dashboard de Reportes
    Route::get('/', [ReporteController::class, 'index'])->name('index');
    // Página de Reporte de Ventas
    Route::get('/ventas', [ReporteController::class, 'reporteVentasPage'])->name('ventas.page');
    // Página de Reporte de Stock Mínimo
    Route::get('/stock-minimo', [ReporteController::class, 'reporteStockMinimoPage'])->name('stock_minimo.page');

    // --- Endpoints de Datos (API-like) ---
    // Datos para el dashboard (KPIs)
    Route::get('/data/kpis', [ReporteController::class, 'kpisGenerales'])->name('data.kpis');
    // Datos para el reporte de ventas (filtrado)
    Route::post('/data/ventas', [ReporteController::class, 'reporteVentasData'])->name('data.ventas');
    // Datos para el reporte de stock mínimo
    Route::get('/data/stock-minimo', [ReporteController::class, 'reporteStockMinimoData'])->name('data.stock_minimo');
});





// Rutas adicionales separadas (auth: login/register/etc. y ajustes de usuario)
require __DIR__.'/settings.php';
require __DIR__.'/auth.php';
