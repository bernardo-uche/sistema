<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue'
import { Head, usePage, Link, router } from '@inertiajs/vue3'
import type { AppPageProps, BreadcrumbItem } from '@/types'
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
import { Pencil, Trash, CirclePlus, Eye } from 'lucide-vue-next'
import { computed, ref } from 'vue'
import axios from 'axios'

// IMPORTAMOS ALERTAS
import {
  AlertDialog,
  AlertDialogAction,
  AlertDialogCancel,
  AlertDialogContent,
  AlertDialogDescription,
  AlertDialogFooter,
  AlertDialogHeader,
  AlertDialogTitle,
} from '@/components/ui/alert-dialog'

// Tipado de props que envía el controlador
type VentaItem = {
  id: number
  cliente?: { id: number; nombre: string } | null
  fecha: string
  total: number
  estado: string
}
type VentasPageProps = AppPageProps<{ Ventas: { data?: VentaItem[] } | VentaItem[] }>
const { props } = usePage<VentasPageProps>()

const ventas = computed<VentaItem[]>(() => {
  const v: any = (props as any).Ventas
  return (v && Array.isArray(v.data)) ? v.data : (Array.isArray(v) ? v : [])
})

const breadcrumbs: BreadcrumbItem[] = [{ title: 'Ventas', href: '/ventas' }]

// 🔴 DIALOGO ELIMINAR
const openDialog = ref(false)
const selectedId = ref<number | null>(null)
const confirmDelete = (id: number) => {
  selectedId.value = id
  openDialog.value = true
}
const deleteVenta = async () => {
  if (!selectedId.value) return
  router.delete(`/ventas/${selectedId.value}`, {
    preserveScroll: true,
    onSuccess: () => {
      router.visit('/ventas', { replace: true })
      openDialog.value = false
      selectedId.value = null
    },
  })
}

// Tipado para el detalle de la venta que se mostrará en el diálogo
type DetalleVenta = VentaItem & {
  detalles: {
    producto?: { nombre: string } | null
    cantidad: number
    precio_unitario: number
  }[]
}

// 👁️ DIALOGO DETALLE VENTA
const openDetalle = ref(false)
const ventaSeleccionada = ref<DetalleVenta | null>(null)
const cargando = ref(false)
const errorDetalle = ref<string | null>(null)

const formatCurrency = (value: number) => new Intl.NumberFormat('es-BO', {
  style: 'currency',
  currency: 'BOB',
}).format(value)

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

const verDetalle = async (id: number) => {
  openDetalle.value = true
  cargando.value = true
  ventaSeleccionada.value = null
  errorDetalle.value = null
  try {
    const { data } = await axios.get(`/venta/${id}`) // Asegúrate que esta ruta devuelva JSON
    ventaSeleccionada.value = data
  } catch (error) {
    console.error('Error al cargar detalle de venta:', error)
  } finally {
    cargando.value = false
  }
}
</script>

<template>
  <Head title="Ventas" />
  <AppLayout :breadcrumbs="breadcrumbs">
    <div
      class="flex h-full flex-1 flex-col gap-4 p-4 bg-gray-100 dark:bg-gray-800 rounded-xl border border-gray-300 dark:border-sidebar-border"
    >
      <div class="flex gap-x-4">
        <Button as-child size="sm" class="bg-indigo-500 text-white hover:bg-indigo-700">
          <Link href="venta/create">
            <CirclePlus /> Registrar Venta
          </Link>
        </Button>
      </div>

      <div
        class="relative min-h-[100vh] flex-1 rounded-xl border border-gray-300 dark:border-sidebar-border"
      >
        <Table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
          <TableCaption>VENTAS</TableCaption>
          <TableHeader>
            <TableRow>
              <TableHead>Fecha</TableHead>
              <TableHead>Cliente</TableHead>
              <TableHead class="text-right">Total</TableHead>
              <TableHead>Estado</TableHead>
              <TableHead class="text-center">Acciones</TableHead>
            </TableRow>
          </TableHeader>

          <TableBody>
            <TableRow v-for="v in ventas" :key="v.id">
              <TableCell class="font-medium">{{ formatDate(v.fecha) }}</TableCell>
              <TableCell>{{ v.cliente?.nombre ?? 'Sin cliente' }}</TableCell>
              <TableCell class="text-right">
                {{
                  new Intl.NumberFormat('es-BO', {
                    style: 'currency',
                    currency: 'BOB',
                  }).format(v.total)
                }}
              </TableCell>
              <TableCell>{{ v.estado }}</TableCell>
              <TableCell class="flex justify-center gap-2">
                <Button as-child size="sm" class="bg-blue-500 text-white hover:bg-blue-700">
                  <Link :href="`/ventas/${v.id}/edit`">
                    <Pencil />
                  </Link>
                </Button>
                <Button
                  size="sm"
                  class="bg-green-500 text-white hover:bg-green-700"
                  @click="verDetalle(v.id)"
                >
                  <Eye />
                </Button>
              </TableCell>
            </TableRow>
          </TableBody>
        </Table>
      </div>
    </div>

    <!-- ⚡ ALERT DIALOG - ELIMINAR -->
    <AlertDialog v-model:open="openDialog" v-if="openDialog">
      <AlertDialogContent>
        <AlertDialogHeader>
          <AlertDialogTitle>¿Eliminar venta?</AlertDialogTitle>
          <AlertDialogDescription>
            Esta acción no se puede deshacer. La venta será eliminada permanentemente.
          </AlertDialogDescription>
        </AlertDialogHeader>
        <AlertDialogFooter>
          <AlertDialogCancel>Cancelar</AlertDialogCancel>
          <AlertDialogAction
            @click="deleteVenta"
            class="bg-rose-600 text-white hover:bg-rose-700"
          >
            Confirmar
          </AlertDialogAction>
        </AlertDialogFooter>
      </AlertDialogContent>
    </AlertDialog>

    <!-- 👁️ ALERT DIALOG - DETALLE DE VENTA -->
    <AlertDialog v-model:open="openDetalle" class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50">
  <AlertDialogContent class="bg-white dark:bg-gray-900 rounded-xl shadow-xl max-w-2xl w-full p-6 sm:p-8">
    <AlertDialogHeader class="mb-4 border-b border-gray-200 dark:border-gray-700 pb-3">
      <AlertDialogTitle class="text-2xl font-bold text-gray-900 dark:text-gray-100 text-center">
        Detalle Venta
      </AlertDialogTitle>
      <AlertDialogDescription class="mt-1 text-gray-600 dark:text-gray-400 text-sm">
        <div v-if="cargando" class="text-center py-8 text-indigo-600 font-semibold animate-pulse">
          Cargando...
        </div>
        <div v-else-if="ventaSeleccionada" class="space-y-4 text-gray-800 dark:text-gray-200">
          <p><span class="font-semibold">Cliente:</span> {{ ventaSeleccionada.cliente?.nombre ?? 'N/A' }}</p>
          <p><span class="font-semibold">Fecha:</span> {{ formatDate(ventaSeleccionada.fecha) }}</p>
          <p><span class="font-semibold">Estado:</span> 
            <span
              :class="{
                'text-green-600': ventaSeleccionada.estado === 'Completado',
                'text-yellow-600': ventaSeleccionada.estado === 'Pendiente',
                'text-red-600': ventaSeleccionada.estado === 'Cancelado'
              }"
            >
              {{ ventaSeleccionada.estado }}
            </span>
          </p>
          <p><span class="font-semibold">Total:</span> {{ formatCurrency(ventaSeleccionada.total) }}</p>

          <hr class="my-4 border-gray-300 dark:border-gray-700" />

          <h4 class="font-semibold text-lg mb-3">Productos</h4>

          <ul class="divide-y divide-gray-200 dark:divide-gray-700 max-h-60 overflow-y-auto">
            <li
              v-for="(d, index) in ventaSeleccionada.detalles"
              :key="index"
              class="flex justify-between py-2"
            >
              <span class="text-gray-900 dark:text-gray-100 font-medium">
                {{ d.producto?.nombre ?? 'Desconocido' }} ({{ d.cantidad }})
              </span>
              <span class="text-indigo-600 font-semibold">
                {{ formatCurrency(d.precio_unitario ?? 0) }}
              </span>
            </li>
          </ul>
        </div>
        <div v-else-if="errorDetalle" class="text-center py-6 text-red-500 font-semibold">
          {{ errorDetalle }}
        </div>
        <div v-else class="text-center py-6 text-gray-400 dark:text-gray-500 italic">
          No se encontraron detalles.
        </div>
      </AlertDialogDescription>
    </AlertDialogHeader>
    <AlertDialogFooter class="mt-6 flex justify-end">
      <AlertDialogAction
        @click="openDetalle = false"
        class="px-6 py-2 rounded-lg bg-indigo-600 text-white hover:bg-indigo-700 transition-colors duration-200 font-semibold"
      >
        Cerrar
      </AlertDialogAction>
    </AlertDialogFooter>
  </AlertDialogContent>
</AlertDialog>

  </AppLayout>
</template>
