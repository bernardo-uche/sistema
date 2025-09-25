<script setup lang="ts">
// Listado de Proveedores con acciones de editar y eliminar usando Inertia.
import AppLayout from '@/layouts/AppLayout.vue';
import AppHeaderLayout from '@/layouts/app/AppHeaderLayout.vue';
import { Head, usePage, Link, router } from '@inertiajs/vue3';
import {Proveedor, type BreadcrumbItem, type AppPageProps} from '@/types';
import {
  Table,
  TableBody,
  TableCaption,
  TableCell,
  TableHead,
  TableHeader,
  TableRow,
} 
from '@/components/ui/table'
import { Button } from '@/components/ui/button';
import { Pencil, Trash, CirclePlus } from 'lucide-vue-next';
import { computed, ref } from 'vue';

// 🟢 importamos alert dialog
import {
  AlertDialog,
  AlertDialogAction,
  AlertDialogCancel,
  AlertDialogContent,
  AlertDialogDescription,
  AlertDialogFooter,
  AlertDialogHeader,
  AlertDialogTitle,
  AlertDialogTrigger,
} from '@/components/ui/alert-dialog'

// Tipado de props de página para incluir ziggy/sidebarOpen/auth y la data propia
type ProveedorPageProps = AppPageProps<{ proveedor: Proveedor[] }>;
const {props} = usePage<ProveedorPageProps>();
const proveedor = computed(()=>props.proveedor)

const breadcrumbs: BreadcrumbItem[]= [{title: 'Proveedor', href: '/proveedor'}];

// ⚡ Estado del dialog
const openDialog = ref(false)
const selectedId = ref<number|null>(null)

// Método eliminar proveedor
const confirmDelete = (id:number)=>{
  selectedId.value = id
  openDialog.value = true
}

const deleteProveedor = async()=>{
  if (!selectedId.value) return;

  router.delete(`/proveedor/${selectedId.value}`,{
    preserveScroll:true,
    onSuccess:() => {
      router.visit('/proveedor', {replace:true});
      openDialog.value = false
      selectedId.value = null
    },
    onError:(errors)=>{
      console.error('Error deleting Proveedor:',errors);
    }
  });
};
</script>

<template>
  <Head title="Proveedor" />
  <AppLayout :breadcrumbs="breadcrumbs">
    <div class="flex h-full flex-1 flex-col gap-4 rounded-xl p-4">

      <div class="flex">
        <Button as-child size="sm" class="bg-indigo-500 text-white hover:bg-indigo-700">
          <Link href="proveedor/create">
            <CirclePlus /> Create
          </Link>
        </Button>
      </div>

      <div class="relative min-h-[100vh] flex-1 rounded-xl border border-gray-300 dark:border-sidebar-border md:min-h-min">
        <Table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
          <TableCaption>PROVEEDORES</TableCaption>
          <TableHeader>
            <TableRow>
              <TableHead>Nombre</TableHead>
              <TableHead>Telefono</TableHead>
              <TableHead>Direcion</TableHead>
              <TableHead class="text-center">Action</TableHead>
            </TableRow>
          </TableHeader>

          <TableBody>
            <TableRow v-for="proveedor in proveedor" :key="proveedor.id">
              <TableCell class="font-medium">{{ proveedor.nombre }}</TableCell>
              <TableCell>{{ proveedor.telefono ?? 'N/A' }}</TableCell>
              <TableCell>{{ proveedor.direccion }}</TableCell>
              <TableCell class="flex justify-center gap-2">
                <Button as-child size="sm" class="bg-blue-500 text-white hover:bg-blue-700">
                  <Link :href="`/proveedor/${proveedor.id}/edit`">
                    <Pencil />
                  </Link>
                </Button>
                <Button
                  size="sm"
                  class="bg-rose-500 text-white hover:bg-rose-700"
                  @click="confirmDelete(proveedor.id)"
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
          <AlertDialogTitle>¿Eliminar proveedor?</AlertDialogTitle>
          <AlertDialogDescription>
            Esta acción no se puede deshacer. El proveedor será eliminado permanentemente.
          </AlertDialogDescription>
        </AlertDialogHeader>
        <AlertDialogFooter>
          <AlertDialogCancel>Cancelar</AlertDialogCancel>
          <AlertDialogAction @click="deleteProveedor" class="bg-rose-600 text-white hover:bg-rose-700">
            Confirmar
          </AlertDialogAction>
        </AlertDialogFooter>
      </AlertDialogContent>
    </AlertDialog>
  </AppLayout>
</template>
