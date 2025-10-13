<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response as InertiaResponse;
use Illuminate\Http\JsonResponse;
use App\Models\Venta;
use App\Models\Compra;
use App\Models\Productos;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class ReporteController extends Controller
{
    /**
     * Muestra el dashboard principal de reportes.
     * Esta será la página principal de tu módulo.
     */
    public function index(): InertiaResponse
    {
        return Inertia::render('Reporte/Index');
    }

    /**
     * Devuelve los KPIs (Indicadores Clave) para las tarjetas del dashboard de reportes.
     * El frontend llamará a esta ruta para obtener los números.
     */
    public function kpisGenerales(): JsonResponse
    {
        try {
            $ventasHoy = Venta::whereDate('fecha', today())->sum('total');
            $comprasHoy = Compra::whereDate('fecha', today())->sum('total');
            $productosBajoStock = Productos::where('stock', '<', DB::raw('stock_minimo'))->count();

            // Para "próximos a vencer", se necesita una columna 'fecha_vencimiento'.
            // Asumiendo que existe y es de tipo 'date'.
            $proximosAVencer = 0;
            if (Schema::hasColumn('productos', 'fecha_vencimiento')) {
                $proximosAVencer = Productos::where('fecha_vencimiento', '>', now())
                                             ->where('fecha_vencimiento', '<=', now()->addDays(30))
                                             ->count();
            }

            return response()->json([
                'ventasHoy' => $ventasHoy,
                'comprasHoy' => $comprasHoy,
                'productosBajoStock' => $productosBajoStock,
                'proximosAVencer' => $proximosAVencer,
            ]);
        } catch (\Exception $e) {
            return response()->json(['error' => 'Error al obtener los KPIs: ' . $e->getMessage()], 500);
        }
    }

    /**
     * Muestra la página para el reporte detallado de Ventas.
     */
    public function reporteVentasPage(): InertiaResponse
    {
        return Inertia::render('Reporte/ventas');
    }

    /**
     * Obtiene y devuelve los datos para el reporte de Ventas (filtrado por fechas).
     */
    public function reporteVentasData(Request $request): JsonResponse
    {
        $request->validate([
            'fecha_inicio' => 'required|date',
            'fecha_fin' => 'required|date|after_or_equal:fecha_inicio',
        ]);

        try {
            $ventas = Venta::with('cliente:id,nombre')
                ->entreFechas($request->fecha_inicio, $request->fecha_fin)
                ->orderBy('fecha', 'desc')
                ->get();

            return response()->json([
                'ventas' => $ventas,
                'totalGeneral' => $ventas->sum('total'),
                'cantidadVentas' => $ventas->count(),
            ]);
        } catch (\Exception $e) {
            return response()->json(['error' => 'Error al generar el reporte de ventas: ' . $e->getMessage()], 500);
        }
    }

    /**
     * Muestra la página para el reporte de Productos con Stock Mínimo.
     */
    public function reporteStockMinimoPage(): InertiaResponse
    {
        return Inertia::render('Reporte/StockMinimo');
    }

    /**
     * Obtiene y devuelve los datos para el reporte de Stock Mínimo.
     */
    public function reporteStockMinimoData(): JsonResponse
    {
        try {
            $productos = Productos::with('proveedor:id,nombre', 'categoria:id,nombre')
                ->where('stock', '<', DB::raw('stock_minimo'))
                ->orderBy('nombre')
                ->get();
            return response()->json($productos);
        } catch (\Exception $e) {
            return response()->json(['error' => 'Error al generar el reporte de stock mínimo: ' . $e->getMessage()], 500);
        }
    }
}
