<script setup lang="ts">
import { ref } from 'vue'
import axios from 'axios'
import AppLayout from '@/layouts/AppLayout.vue'
import { type BreadcrumbItem } from '@/types'
import { Head } from '@inertiajs/vue3'
import {
  Table,
  TableBody,
  TableCaption,
  TableCell,
  TableHead,
  TableHeader,
  TableRow,
} from '@/components/ui/table'
import { Button } from '@/components/ui/button'
import { Input } from '@/components/ui/input'
import { Label } from '@/components/ui/label'
import { Calendar, FileDown, LoaderCircle, AlertCircle } from 'lucide-vue-next'

// --- Tipado de Datos ---
interface VentaReporte {
  id: number
  fecha: string
  cliente: {
    nombre: string
  } | null
  total: number
  estado: string
}

interface ReporteData {
  ventas: VentaReporte[]
  totalGeneral: number
  cantidadVentas: number
}

// --- Estado Reactivo ---
const breadcrumbs: BreadcrumbItem[] = [
  { title: 'Reportes', href: route('reportes.index') },
  { title: 'Reporte de Ventas', href: route('reportes.ventas.page') },
]

// Fechas para el filtro. Se inicializan con el mes actual.
const today = new Date()
const firstDayOfMonth = new Date(today.getFullYear(), today.getMonth(), 1)
const fecha_inicio = ref(firstDayOfMonth.toISOString().split('T')[0])
const fecha_fin = ref(today.toISOString().split('T')[0])

// Estado para manejar la carga y los datos del reporte
const reporte = ref<ReporteData | null>(null)
const cargando = ref(false)
const error = ref<string | null>(null)

// --- Lógica ---

/**
 * Formatea un número a moneda boliviana (BOB).
 */
const formatCurrency = (value: number) => {
  return new Intl.NumberFormat('es-BO', {
    style: 'currency',
    currency: 'BOB',
  }).format(value)
}

// Formatea una fecha recibida desde la base de datos sin que el parseo de ISO
// mueva el día por diferencias de zona horaria. Acepta 'YYYY-MM-DD' o
// 'YYYY-MM-DD HH:mm:ss' u objetos Date.
const formatDate = (input: string | Date | null | undefined) => {
  if (!input) return ''
  // Si ya es Date, formateamos respetando la zona de Bolivia
  if (input instanceof Date) {
    return new Intl.DateTimeFormat('es-BO', {
      day: '2-digit',
      month: '2-digit',
      year: 'numeric',
      timeZone: 'America/La_Paz',
    }).format(input)
  }

  const str = String(input)
  // Extrae YYYY-MM-DD del principio
  const m = str.match(/^(\d{4})-(\d{2})-(\d{2})/)
  if (m) {
    const y = Number(m[1])
    const mo = Number(m[2]) - 1
    const d = Number(m[3])
    // Construimos una hora segura (mediodía UTC) para evitar que el cambio de
    // zona horaria ajuste el día al formatear en 'America/La_Paz'.
    const safeUtc = new Date(Date.UTC(y, mo, d, 12, 0, 0))
    return new Intl.DateTimeFormat('es-BO', {
      day: '2-digit',
      month: '2-digit',
      year: 'numeric',
      timeZone: 'America/La_Paz',
    }).format(safeUtc)
  }

  // Fallback: intenta parseo normal y formatea en la zona de Bolivia
  const parsed = new Date(str)
  if (isNaN(parsed.getTime())) return str
  return new Intl.DateTimeFormat('es-BO', {
    day: '2-digit',
    month: '2-digit',
    year: 'numeric',
    timeZone: 'America/La_Paz',
  }).format(parsed)
}


/**
 * Llama a la API para generar el reporte de ventas.
 */
const generarReporte = async () => {
  cargando.value = true
  error.value = null
  reporte.value = null

  try {
    // Hacemos la petición POST al endpoint que creaste en Laravel
    const response = await axios.post(route('reportes.data.ventas'), {
      fecha_inicio: fecha_inicio.value,
      fecha_fin: fecha_fin.value,
    })
    reporte.value = response.data
  } catch (err: any) {
    console.error('Error al generar el reporte:', err)
    if (err.response && err.response.data && err.response.data.message) {
      error.value = err.response.data.message
    } else {
      error.value = 'Ocurrió un error inesperado al generar el reporte.'
    }
  } finally {
    cargando.value = false
  }
}

// Generar el reporte al cargar la página por primera vez
generarReporte()
</script>

<template>
  <Head title="Reporte de Ventas" />
  <AppLayout :breadcrumbs="breadcrumbs">
    <div class="p-6 bg-white dark:bg-gray-900 rounded-2xl shadow-lg">
      <!-- Encabezado y Filtros -->
      <div class="flex flex-col md:flex-row justify-between items-center mb-6 gap-4">
        <h1 class="text-3xl font-bold text-gray-800 dark:text-white">
          Reporte de Ventas
        </h1>
        <div class="flex items-center gap-4">
          <Button variant="outline">
            <FileDown class="w-4 h-4 mr-2" />
            Exportar
          </Button>
        </div>
      </div>

      <!-- Controles de Fecha -->
      <div class="flex flex-col md:flex-row items-center gap-4 p-4 border rounded-lg dark:border-gray-700 mb-6 bg-gray-50 dark:bg-gray-800/50">
        <div class="flex-1 w-full">
          <Label for="fecha_inicio">Fecha de Inicio</Label>
          <Input id="fecha_inicio" v-model="fecha_inicio" type="date" />
        </div>
        <div class="flex-1 w-full">
          <Label for="fecha_fin">Fecha de Fin</Label>
          <Input id="fecha_fin" v-model="fecha_fin" type="date" />
        </div>
        <Button @click="generarReporte" class="w-full md:w-auto self-end bg-indigo-600 hover:bg-indigo-700">
          <Calendar class="w-4 h-4 mr-2" />
          Generar Reporte
        </Button>
      </div>

      <!-- Estado de Carga -->
      <div v-if="cargando" class="text-center py-12 flex flex-col items-center">
        <LoaderCircle class="w-12 h-12 text-indigo-500 animate-spin mb-4" />
        <p class="text-lg font-semibold text-gray-600 dark:text-gray-300">
          Generando reporte...
        </p>
      </div>

      <!-- Estado de Error -->
      <div v-else-if="error" class="text-center py-12 bg-red-50 dark:bg-red-900/20 p-6 rounded-lg">
        <AlertCircle class="w-12 h-12 text-red-500 mx-auto mb-4" />
        <h3 class="text-xl font-bold text-red-700 dark:text-red-400">
          Error al generar el reporte
        </h3>
        <p class="text-red-600 dark:text-red-300 mt-2">
          {{ error }}
        </p>
      </div>

      <!-- Contenido del Reporte -->
      <div v-else-if="reporte">
        <!-- Resumen de Totales -->
        <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-8">
          <div class="p-4 bg-blue-50 dark:bg-blue-900/30 rounded-lg text-center">
            <h3 class="text-sm font-semibold text-blue-800 dark:text-blue-300 uppercase">Total de Ventas</h3>
            <p class="text-2xl font-bold text-blue-900 dark:text-blue-200 mt-1">
              {{ formatCurrency(reporte.totalGeneral) }}
            </p>
          </div>
          <div class="p-4 bg-green-50 dark:bg-green-900/30 rounded-lg text-center">
            <h3 class="text-sm font-semibold text-green-800 dark:text-green-300 uppercase">Cantidad de Ventas</h3>
            <p class="text-2xl font-bold text-green-900 dark:text-green-200 mt-1">
              {{ reporte.cantidadVentas }}
            </p>
          </div>
          <div class="p-4 bg-yellow-50 dark:bg-yellow-900/30 rounded-lg text-center">
            <h3 class="text-sm font-semibold text-yellow-800 dark:text-yellow-300 uppercase">Venta Promedio</h3>
            <p class="text-2xl font-bold text-yellow-900 dark:text-yellow-200 mt-1">
              {{ formatCurrency(reporte.cantidadVentas > 0 ? reporte.totalGeneral / reporte.cantidadVentas : 0) }}
            </p>
          </div>
        </div>

        <!-- Tabla de Ventas -->
        <Table>
          <TableCaption v-if="reporte.ventas.length === 0">
            No se encontraron ventas en el rango de fechas seleccionado.
          </TableCaption>
          <TableHeader>
            <TableRow>
              <TableHead>ID</TableHead>
              <TableHead>Fecha</TableHead>
              <TableHead>Cliente</TableHead>
              <TableHead>Estado</TableHead>
              <TableHead class="text-right">Total</TableHead>
            </TableRow>
          </TableHeader>
          <TableBody>
            <TableRow v-for="venta in reporte.ventas" :key="venta.id">
              <TableCell class="font-medium">{{ venta.id }}</TableCell>
              <TableCell>{{ formatDate(venta.fecha) }}</TableCell>
              <TableCell>{{ venta.cliente?.nombre ?? 'Sin Cliente' }}</TableCell>
              <TableCell>
                <span
                  class="px-2 py-1 text-xs font-semibold rounded-full"
                  :class="{
                    'bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-200': venta.estado === 'Completado',
                    'bg-yellow-100 text-yellow-800 dark:bg-yellow-900 dark:text-yellow-200': venta.estado === 'Pendiente',
                    'bg-red-100 text-red-800 dark:bg-red-900 dark:text-red-200': venta.estado === 'Cancelado',
                  }"
                >
                  {{ venta.estado }}
                </span>
              </TableCell>
              <TableCell class="text-right font-mono">{{ formatCurrency(venta.total) }}</TableCell>
            </TableRow>
          </TableBody>
        </Table>
      </div>
    </div>
  </AppLayout>
</template>
