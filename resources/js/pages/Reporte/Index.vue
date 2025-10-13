<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue'
import { type BreadcrumbItem, type AppPageProps } from '@/types'
import { Head, Link } from '@inertiajs/vue3'
import { TrendingUp, ShoppingCart, BarChart3, AlertTriangle, Clock } from 'lucide-vue-next'

const breadcrumbs: BreadcrumbItem[] = [
  { title: 'Reportes', href: '/reportes' },
]

// Datos simulados (podrás reemplazarlos con props o API)
const reportes = [
  {
    titulo: 'Ventas',
    descripcion: 'Resumen de ventas realizadas',
    color: 'from-indigo-500 to-purple-600',
    icono: TrendingUp,
    enlace: route('reportes.ventas.page'),
  },
  {
    titulo: 'Compras',
    descripcion: 'Control de compras a proveedores',
    color: 'from-green-500 to-emerald-600',
    icono: ShoppingCart,
    enlace: '#', // Cambia esto cuando crees la ruta y la página
  },
  {
    titulo: 'Stock Mínimo',
    descripcion: 'Productos con inventario bajo',
    color: 'from-rose-500 to-pink-600',
    icono: AlertTriangle,
    enlace: route('reportes.stock_minimo.page'),
  },
  {
    titulo: 'Próximos a Vencer',
    descripcion: 'Productos con fecha de vencimiento cercana',
    color: 'from-yellow-400 to-amber-500',
    icono: Clock,
    enlace: '#', // Cambia esto cuando crees la ruta y la página
  },
  {
    titulo: 'Resumen General',
    descripcion: 'Consolidado de todos los reportes',
    color: 'from-blue-500 to-cyan-600',
    icono: BarChart3,
    enlace: '#', // Cambia esto cuando crees la ruta y la página
  },
]
</script>

<template>
  <Head title="Reportes" />
  <AppLayout :breadcrumbs="breadcrumbs">
    <div class="min-h-screen bg-gradient-to-br from-gray-100 to-gray-200 dark:from-gray-900 dark:to-gray-800 p-8 rounded-2xl">
      <!-- Encabezado -->
      <div class="text-center mb-10">
        <h1 class="text-4xl font-extrabold text-gray-800 dark:text-white tracking-tight">
          Panel de Reportes
        </h1>
        <p class="text-gray-600 dark:text-gray-400 mt-2 text-lg">
          Consulta y descarga reportes detallados del sistema
        </p>
      </div>

      <!-- Grid de tarjetas -->
      <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-8">
        <div
          v-for="(r, index) in reportes"
          :key="index"
          class="group relative bg-white dark:bg-gray-900 rounded-2xl shadow-lg hover:shadow-2xl transform transition duration-300 hover:-translate-y-1 overflow-hidden"
        >
          <!-- Fondo degradado -->
          <div
            class="absolute inset-0 opacity-90 bg-gradient-to-br"
            :class="r.color"
          ></div>

          <!-- Contenido -->
          <div class="relative z-10 flex flex-col items-center justify-center text-center p-8 text-white">
            <component :is="r.icono" class="w-14 h-14 mb-4 opacity-90 group-hover:scale-110 transition-transform duration-300" />
            <h2 class="text-2xl font-bold tracking-wide">{{ r.titulo }}</h2>
            <p class="text-sm mt-2 text-gray-100">{{ r.descripcion }}</p>

            <Link
              :href="r.enlace"
              class="mt-6 inline-block px-5 py-2 text-sm font-semibold bg-white/20 rounded-full border border-white/30 hover:bg-white/30 transition duration-300"
            >
              Ver Reporte
            </Link>
          </div>

          <!-- Efecto de iluminación -->
          <div
            class="absolute inset-0 bg-gradient-to-t from-black/20 via-transparent to-transparent opacity-0 group-hover:opacity-100 transition-opacity duration-300"
          ></div>
        </div>
      </div>
    </div>
  </AppLayout>
</template>
