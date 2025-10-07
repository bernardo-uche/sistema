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
import { Pencil, Trash, CirclePlus, MessageCircle } from 'lucide-vue-next'
import { computed, ref } from 'vue'


// 🟢 importamos AlertDialog
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
// Tipado de props que envía el controlador: 'Compras' puede ser array o paginator
type ComprasItem = {
  id: number
  proveedor?: { id: number; nombre: string } | null
  fecha: string
  total: number
  estado: string
}
type ComprasPageProps = AppPageProps<{ Compras: { data?: ComprasItem[] } | ComprasItem[] }>
const { props } = usePage<ComprasPageProps>()
const compras = computed<ComprasItem[]>(() => {
  const c: any = (props as any).Compras
  return (c && Array.isArray(c.data)) ? c.data : (Array.isArray(c) ? c : [])
})

const breadcrumbs: BreadcrumbItem[] = [{ title: 'Compras', href: '/compras' }]

// Diálogo de confirmación para eliminar
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
    onError: (errors) => {
      console.error('Error eliminando compra:', errors)
    },
  })
}
</script>

<template>
  <Head title="Compras" />
  <AppLayout :breadcrumbs="breadcrumbs">
    <div class="flex h-full flex-1 flex-col gap-4 -xl p-4 bg-gray-100 dark:bg-gray-800 rounded-xl border border-gray-300 dark:border-sidebar-border md:min-h-min">

      <div class="flex gap-x-4">
        <Button as-child size="sm" class="bg-indigo-500 text-white hover:bg-indigo-700">
          <Link href="compras/create">
            <CirclePlus /> Registrar Compra
          </Link>
        </Button>
      </div>

      <div class="relative min-h-[100vh] flex-1 rounded-xl border border-gray-300 dark:border-sidebar-border md:min-h-min">
        <Table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
          <TableCaption>COMPRAS</TableCaption>
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
              <TableCell class="text-right">{{ new Intl.NumberFormat('es-BO', { style: 'currency', currency: 'BOB' }).format(c.total) }}</TableCell>
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
  </AppLayout>
</template>
