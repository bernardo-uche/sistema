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
import { Pencil, Trash, CirclePlus } from 'lucide-vue-next'
import { computed, ref } from 'vue'
//IMPORTAMOS AL ALERTA DINAMICA
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

// Diálogo de confirmación para eliminar
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
    onError: (errors) => {
      console.error('Error eliminando venta:', errors)
    },
  })
}
</script>

<template>
  <Head title="Ventas" />
  <AppLayout :breadcrumbs="breadcrumbs">
    <div class="flex h-full flex-1 flex-col gap-4 rounded-xl p-4">

      <div class="flex gap-x-4">
        <Button as-child size="sm" class="bg-indigo-500 text-white hover:bg-indigo-700">
          <Link href="venta/create">
            <CirclePlus /> Registrar Venta
          </Link>
        </Button>
      </div>

      <div class="relative min-h-[100vh] flex-1 rounded-xl border border-gray-300 dark:border-sidebar-border md:min-h-min">
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
              <TableCell class="font-medium">{{ new Date(v.fecha).toLocaleDateString() }}</TableCell>
              <TableCell>{{ v.cliente?.nombre ?? 'Sin cliente' }}</TableCell>
              <TableCell class="text-right">
                {{ new Intl.NumberFormat('es-BO', { style: 'currency', currency: 'BOB' }).format(v.total) }}
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
                  class="bg-rose-500 text-white hover:bg-rose-700"
                  @click="confirmDelete(v.id)"
                >
                  <Trash />
                </Button>
              </TableCell>
            </TableRow>
          </TableBody>
        </Table>
      </div>
    </div>

    <!-- ⚡ Alert Dialog -->
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
          <AlertDialogAction @click="deleteVenta" class="bg-rose-600 text-white hover:bg-rose-700">
            Confirmar
          </AlertDialogAction>
        </AlertDialogFooter>
      </AlertDialogContent>
    </AlertDialog>
  </AppLayout>
</template>
