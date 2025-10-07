<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import { Head, Link } from '@inertiajs/vue3';
import type { BreadcrumbItem } from '@/types';
import {
  Table,
  TableBody,
  TableCaption,
  TableCell,
  TableHead,
  TableHeader,
  TableRow,
} from '@/components/ui/table'
import { Button } from '@/components/ui/button';
import { Pencil, Trash, CirclePlus } from 'lucide-vue-next';
import { onMounted, ref } from 'vue';
import axios from 'axios';

// Estado
type Categoria = { id: number; nombre: string }
const categorias = ref<Categoria[]>([])
const loading = ref<boolean>(false)
const errorMsg = ref<string | null>(null)

const breadcrumbs: BreadcrumbItem[]= [{title: 'Categorías', href: '/categoria'}];

// Dialogo de confirmación
const openDialog = ref(false)
const selectedId = ref<number|null>(null)

const confirmDelete = (id:number)=>{
  selectedId.value = id
  openDialog.value = true
}

const fetchCategorias = async () => {
  try {
    loading.value = true
    errorMsg.value = null
    const { data } = await axios.get('/api/categoria', { params: { paginar: 0 } })
    // data esperado: { success, data, message }
    categorias.value = Array.isArray(data?.data) ? data.data : []
  } catch (e: any) {
    errorMsg.value = e?.response?.data?.message || 'Error al cargar categorías'
    categorias.value = []
  } finally {
    loading.value = false
  }
}

const deleteCategoria = async () => {
  if (!selectedId.value) return
  try {
    await axios.delete(`/api/categoria/${selectedId.value}`)
    openDialog.value = false
    const removedId = selectedId.value
    selectedId.value = null
    // Refrescar lista localmente para evitar otra carga completa
    categorias.value = categorias.value.filter(c => c.id !== removedId)
  } catch (e) {
    console.error('Error eliminando categoría', e)
  }
}

onMounted(fetchCategorias)
</script>

<template>
  <Head title="Categorías" />
  <AppLayout :breadcrumbs="breadcrumbs">

    <div class="flex h-full flex-1 flex-col gap-4 -xl p-4 bg-gray-100 dark:bg-gray-800 rounded-xl border border-gray-300 dark:border-sidebar-border md:min-h-min">
      
      <div class="flex">
        <Button as-child size="sm" class="bg-indigo-500 text-white hover:bg-indigo-700">
          <Link href="/categoria/create">
            <CirclePlus /> Crear
          </Link>
        </Button>
      </div>

      <div class="relative min-h-[100vh] flex-1 rounded-xl border border-gray-300 dark:border-sidebar-border md:min-h-min">
        <Table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
          <TableCaption>CATEGORÍAS</TableCaption>
          <TableHeader>
            <TableRow>
              <TableHead>Nombre</TableHead>
              <TableHead class="text-center">Acciones</TableHead>
            </TableRow>
          </TableHeader>

          <TableBody>
            <TableRow v-if="loading">
              <TableCell colspan="2">Cargando...</TableCell>
            </TableRow>
            <TableRow v-else-if="errorMsg">
              <TableCell colspan="2" class="text-red-600">{{ errorMsg }}</TableCell>
            </TableRow>
            <TableRow v-else-if="categorias.length === 0">
              <TableCell colspan="2">Sin categorías</TableCell>
            </TableRow>
            <TableRow v-else v-for="categoria in categorias" :key="categoria.id">
              <TableCell class="font-medium">{{ categoria.nombre }}</TableCell>
              <TableCell class="flex justify-center gap-2">
                <Button as-child size="sm" class="bg-blue-500 text-white hover:bg-blue-700">
                  <Link :href="`/categoria/${categoria.id}/edit`">
                    <Pencil />
                  </Link>
                </Button>
                <Button
                  size="sm"
                  class="bg-rose-500 text-white hover:bg-rose-700"
                  @click="confirmDelete(categoria.id)"
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
          <AlertDialogTitle>¿Eliminar categoría?</AlertDialogTitle>
          <AlertDialogDescription>
            Esta acción no se puede deshacer. La categoría será eliminada permanentemente.
          </AlertDialogDescription>
        </AlertDialogHeader>
        <AlertDialogFooter>
          <AlertDialogCancel>Cancelar</AlertDialogCancel>
          <AlertDialogAction @click="deleteCategoria" class="bg-rose-600 text-white hover:bg-rose-700">
            Confirmar
          </AlertDialogAction>
        </AlertDialogFooter>
      </AlertDialogContent>
    </AlertDialog>
  </AppLayout>
</template>
