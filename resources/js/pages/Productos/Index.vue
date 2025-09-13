<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import { Head, usePage, Link, router } from '@inertiajs/vue3';
import { Producto, type BreadcrumbItem, type SharedData} from '@/types';
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
import { computed, ref } from 'vue';

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

interface ProductoPageProps extends SharedData {
  producto: (Producto & { categoria?: { nombre: string } })[]; 
  // 👆 Incluí la relación para mostrar el nombre de la categoría
}

const {props} = usePage<ProductoPageProps>();
const producto = computed(() => props.producto)

const breadcrumbs: BreadcrumbItem[] = [{ title: 'Producto', href: '/producto' }];

// ⚡ Estado del dialog
const openDialog = ref(false)
const selectedId = ref<number|null>(null)

// Método para abrir el dialog
const confirmDelete = (id:number)=>{
  selectedId.value = id
  openDialog.value = true
}

// Método para eliminar producto
const deleteProducto = async()=>{
  if (!selectedId.value) return;

  router.delete(`/productos/${selectedId.value}`,{
    preserveScroll:true,
    onSuccess:() => {
      router.visit('/productos', {replace:true});
      openDialog.value = false
      selectedId.value = null
    },
    onError:(errors)=>{
      console.error('Error deleting producto:',errors);
    }
  });
};
</script>

<template>
  <Head title="Producto" />
  <AppLayout :breadcrumbs="breadcrumbs">
    <div class="flex h-full flex-1 flex-col gap-4 rounded-xl p-4">

      <!-- Botón Crear -->
      <div class="flex gap-x-10">
  <Button as-child size="sm" class="bg-indigo-500 text-white hover:bg-indigo-700">
    <Link href="productos/create">
      <CirclePlus /> Crear
    </Link>
  </Button>

  <Button as-child size="sm" class="bg-indigo-500 text-white hover:bg-indigo-700">
    <Link href="categoria/index">
      <CirclePlus /> Categoria
    </Link>
  </Button>
</div>

      

      <!-- Tabla -->
      <div class="relative min-h-[100vh] flex-1 rounded-xl border border-gray-300 dark:border-sidebar-border md:min-h-min">
        <Table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
          <TableCaption>PRODUCTOS</TableCaption>
          <TableHeader>
            <TableRow>
              <TableHead>Nombre</TableHead>
              <TableHead>Stock</TableHead>
              <TableHead>Proveedor</TableHead>
              <TableHead>Precio</TableHead>
              <TableHead>Costo</TableHead>
              <TableHead>Vencimiento</TableHead>
              <TableHead>Categoria</TableHead>
              <TableHead>Estado</TableHead>
              <TableHead class="text-center">Acciones</TableHead>
            </TableRow>
          </TableHeader>

          <TableBody>
  <TableRow v-for="producto in producto" :key="producto.id">
    <TableCell class="font-medium">{{ producto.nombre }}</TableCell>
    <TableCell>{{ producto.stock }}</TableCell>

    <!-- 👇 Mostrar nombre del proveedor -->
   <TableCell>{{ producto.proveedor?.nombre ?? 'Sin proveedor' }}</TableCell>
     <!--<TableCell>{{ producto.proveedor ?? 'Sin proveedor' }}</TableCell>-->

    <TableCell>
      {{ new Intl.NumberFormat('es-BO', { style: 'currency', currency: 'BOB' }).format(producto.precio_unitario) }}
    </TableCell>
    <TableCell>
      {{ new Intl.NumberFormat('es-BO', { style: 'currency', currency: 'BOB' }).format(producto.costo_unitario) }}
    </TableCell>
    <TableCell>{{ producto.fecha_vencimiento ?? 'N/A' }}</TableCell>
    <TableCell>{{ producto.categoria?.nombre ?? 'Sin categoría' }}</TableCell>
    <TableCell>{{ producto.estado }}</TableCell>
    <TableCell class="flex justify-center gap-2">
      <Button as-child size="sm" class="bg-blue-500 text-white hover:bg-blue-700">
        <Link :href="`/producto/${producto.id}/edit`">
          <Pencil />
          </Link>
      </Button>
      <Button
          size="sm"
          class="bg-rose-500 text-white hover:bg-rose-700"
          @click="confirmDelete(producto.id)"
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
    <AlertDialog v-model:open="openDialog">
      <AlertDialogContent>
        <AlertDialogHeader>
          <AlertDialogTitle>¿Eliminar producto?</AlertDialogTitle>
          <AlertDialogDescription>
            Esta acción no se puede deshacer. El producto será eliminado permanentemente.
          </AlertDialogDescription>
        </AlertDialogHeader>
        <AlertDialogFooter>
          <AlertDialogCancel>Cancelar</AlertDialogCancel>
          <AlertDialogAction @click="deleteProducto" class="bg-rose-600 text-white hover:bg-rose-700">
            Confirmar
          </AlertDialogAction>
        </AlertDialogFooter>
      </AlertDialogContent>
    </AlertDialog>
  </AppLayout>
</template>