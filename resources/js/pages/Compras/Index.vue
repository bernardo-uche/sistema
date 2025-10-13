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

// 🟢 AlertDialog Components
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

// ===============================
// 📦 TIPADO
// ===============================
type CompraItem = {
  id: number
  proveedor?: { id: number; nombre: string } | null
  fecha: string
  total: number
  estado: string
}

type ComprasPageProps = AppPageProps<{ Compras: { data?: CompraItem[] } | CompraItem[] }>
const { props } = usePage<ComprasPageProps>()

const compras = computed<CompraItem[]>(() => {
  const c: any = (props as any).Compras
  return Array.isArray(c?.data) ? c.data : Array.isArray(c) ? c : []
})

const breadcrumbs: BreadcrumbItem[] = [{ title: 'Compras', href: '/compras' }]

// ===============================
// 🗑️ ELIMINAR COMPRA
// ===============================
const openDialog = ref(false)
const selectedId = ref<number | null>(null)

const confirmDelete = (id: number) => {
  selectedId.value = id
  openDialog.value = true
}

const deleteCompra = async () => {
  if (!selectedId.value) return
  router.delete(`/compras/${selectedId.value}`, {
    preserveScroll: true,
    onSuccess: () => {
      router.visit('/compras', { replace: true })
      openDialog.value = false
      selectedId.value = null
    },
    onError: (errors) => console.error('Error eliminando compra:', errors),
  })
}

// ===============================
// 👁️ DETALLE DE COMPRA
// ===============================
type DetalleCompra = {
  id: number
  proveedor?: { nombre: string } | null
  fecha: string
  total: number
  estado: string
  detalles: {
    producto?: { nombre: string } | null
    cantidad: number
    precio_unitario: number
  }[]
}

const openDetalle = ref(false)
const compraSeleccionada = ref<DetalleCompra | null>(null)
const cargando = ref(false)
const errorDetalle = ref<string | null>(null)

const formatCurrency = (value: number) =>
  new Intl.NumberFormat('es-BO', {
    style: 'currency',
    currency: 'BOB',
  }).format(value)

const verDetalle = async (id: number) => {
  openDetalle.value = true
  cargando.value = true
  compraSeleccionada.value = null
  errorDetalle.value = null

  try {
    // 👁️ Ruta backend: GET /compras/{id} debe devolver JSON
    const { data } = await axios.get(`/compras/${id}`)
    compraSeleccionada.value = data
  } catch (error) {
    console.error('Error al cargar detalle de compra:', error)
    errorDetalle.value = 'Error al cargar el detalle de la compra.'
  } finally {
    cargando.value = false
  }
}
</script>

<template>
  <Head title="Compras" />
  <AppLayout :breadcrumbs="breadcrumbs">
    <div
      class="flex h-full flex-1 flex-col gap-4 p-4 bg-gray-100 dark:bg-gray-800 rounded-xl border border-gray-300 dark:border-sidebar-border"
    >
      <!-- 🔵 Botón registrar compra -->
      <div class="flex gap-x-4">
        <Button as-child size="sm" class="bg-indigo-500 text-white hover:bg-indigo-700">
          <Link href="/compras/create">
            <CirclePlus /> Registrar Compra
          </Link>
        </Button>
      </div>

      <!-- 🧾 Tabla de compras -->
      <div
        class="relative min-h-[60vh] flex-1 rounded-xl border border-gray-300 dark:border-sidebar-border overflow-x-auto"
      >
        <Table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
          <TableCaption>Listado de Compras</TableCaption>
          <TableHeader>
            <TableRow>
              <TableHead>Fecha</TableHead>
              <TableHead>Proveedor</TableHead>
              <TableHead class="text-right">Total</TableHead>
              <TableHead>Estado</TableHead>
              <TableHead class="text-center">Acciones</TableHead>
            </TableRow>
          </TableHeader>

          <TableBody>
            <TableRow v-for="c in compras" :key="c.id">
              <TableCell class="font-medium">{{ new Date(c.fecha).toLocaleDateString() }}</TableCell>
              <TableCell>{{ c.proveedor?.nombre ?? 'Sin proveedor' }}</TableCell>
              <TableCell class="text-right">{{ formatCurrency(c.total) }}</TableCell>
              <TableCell>{{ c.estado }}</TableCell>
              <TableCell class="flex justify-center gap-2">
                <Button as-child size="sm" class="bg-blue-500 text-white hover:bg-blue-700">
                  <Link :href="`/compras/${c.id}/edit`">
                    <Pencil />
                  </Link>
                </Button>
                <Button
                  size="sm"
                  class="bg-rose-500 text-white hover:bg-rose-700"
                  @click="confirmDelete(c.id)"
                >
                  <Trash />
                </Button>
                <Button
                  size="sm"
                  class="bg-green-500 text-white hover:bg-green-700"
                  @click="verDetalle(c.id)"
                >
                  <Eye />
                </Button>
              </TableCell>
            </TableRow>
          </TableBody>
        </Table>
      </div>
    </div>

    <!-- ⚡ Dialog eliminar compra -->
    <AlertDialog v-model:open="openDialog">
      <AlertDialogContent>
        <AlertDialogHeader>
          <AlertDialogTitle>¿Eliminar compra?</AlertDialogTitle>
          <AlertDialogDescription>
            Esta acción no se puede deshacer. La compra será eliminada permanentemente.
          </AlertDialogDescription>
        </AlertDialogHeader>
        <AlertDialogFooter>
          <AlertDialogCancel>Cancelar</AlertDialogCancel>
          <AlertDialogAction @click="deleteCompra" class="bg-rose-600 text-white hover:bg-rose-700">
            Confirmar
          </AlertDialogAction>
        </AlertDialogFooter>
      </AlertDialogContent>
    </AlertDialog>

    <!-- 👁️ Dialog detalle compra -->
    <AlertDialog v-model:open="openDetalle">
      <AlertDialogContent
        class="bg-white dark:bg-gray-900 rounded-xl shadow-xl max-w-2xl w-full p-6 sm:p-8"
      >
        <AlertDialogHeader class="mb-4 border-b border-gray-200 dark:border-gray-700 pb-3">
          <AlertDialogTitle
            class="text-2xl font-bold text-gray-900 dark:text-gray-100 text-center"
          >
            Detalle Compra
          </AlertDialogTitle>
          <AlertDialogDescription class="mt-1 text-gray-600 dark:text-gray-400 text-sm">
            <div v-if="cargando" class="text-center py-8 text-indigo-600 font-semibold animate-pulse">
              Cargando...
            </div>

            <div v-else-if="compraSeleccionada" class="space-y-4 text-gray-800 dark:text-gray-200">
              <p><span class="font-semibold">Proveedor:</span> {{ compraSeleccionada.proveedor?.nombre ?? 'N/A' }}</p>
              <p><span class="font-semibold">Fecha:</span> {{ new Date(compraSeleccionada.fecha).toLocaleDateString() }}</p>
              <p><span class="font-semibold">Estado:</span> {{ compraSeleccionada.estado }}</p>
              <p><span class="font-semibold">Total:</span> {{ formatCurrency(compraSeleccionada.total) }}</p>

              <hr class="my-4 border-gray-300 dark:border-gray-700" />

              <h4 class="font-semibold text-lg mb-3">Productos</h4>

              <ul class="divide-y divide-gray-200 dark:divide-gray-700 max-h-60 overflow-y-auto">
                <li
                  v-for="(d, index) in compraSeleccionada.detalles"
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
