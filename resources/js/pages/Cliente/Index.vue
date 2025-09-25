<script setup lang="ts">
// Página de listado de Clientes.
// Obtiene props desde Laravel vía Inertia (usePage) y muestra la tabla.
import AppLayout from '@/layouts/AppLayout.vue';
import AppHeaderLayout from '@/layouts/app/AppHeaderLayout.vue';
import { Head, usePage, Link, router } from '@inertiajs/vue3';
import { Cliente, type BreadcrumbItem, type AppPageProps } from '@/types';
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

// 🟢 importamos AlertDialog de shadcn-vue
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

// Tipamos las props de página usando AppPageProps para incluir ziggy/sidebarOpen/auth.
type ClientePageProps = AppPageProps<{ cliente: Cliente[] }>;
// usePage() nos da acceso a las props compartidas por Inertia desde Laravel.
const {props} = usePage<ClientePageProps>();
const cliente = computed(()=>props.cliente)

const breadcrumbs: BreadcrumbItem[]= [{title: 'Cliente', href: '/cliente'}];

// ⚡ Estado del dialog
const openDialog = ref(false)
const selectedId = ref<number|null>(null)

// Método para abrir el dialog
const confirmDelete = (id:number)=>{
  selectedId.value = id
  openDialog.value = true
}

// Método para eliminar cliente
const deleteCliente = async()=>{
  if (!selectedId.value) return;

  // Enviamos DELETE al backend usando Inertia router.
  router.delete(`/cliente/${selectedId.value}`,{
    preserveScroll:true,
    onSuccess:() => {
      router.visit('/cliente', {replace:true});
      openDialog.value = false
      selectedId.value = null
    },
    onError:(errors)=>{
      console.error('Error deleting cliente:',errors);
    }
  });
};
</script>

<template>
  <Head title="Cliente" />
  <AppLayout :breadcrumbs="breadcrumbs">

    <div class="flex h-full flex-1 flex-col gap-4 rounded-xl p-4">
      <!-- Botón para crear nuevo cliente (navega con Inertia a /cliente/create) -->
      <div class="flex">
        <Button as-child size="sm" class="bg-indigo-500 text-white hover:bg-indigo-700">
          <Link href="cliente/create">
            <CirclePlus /> Create
          </Link>
        </Button>
      </div>

      <!-- Tabla de clientes renderizada con componentes de UI -->
      <div class="relative min-h-[100vh] flex-1 rounded-xl border border-gray-300 dark:border-sidebar-border md:min-h-min">
        <Table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
          <TableCaption>CLIENTES</TableCaption>
          <TableHeader>
            <TableRow>
              <TableHead>Nombre</TableHead>
              <TableHead>Telefono</TableHead>
              <TableHead>Direcion</TableHead>
              <TableHead class="text-center">Action</TableHead>
            </TableRow>
          </TableHeader>

          <TableBody>
            <TableRow v-for="cliente in cliente" :key="cliente.id">
              <TableCell class="font-medium">{{ cliente.nombre }}</TableCell>
              <TableCell>{{ cliente.telefono ?? 'N/A' }}</TableCell>
              <TableCell>{{ cliente.direccion }}</TableCell>
              <TableCell class="flex justify-center gap-2">
                <Button as-child size="sm" class="bg-blue-500 text-white hover:bg-blue-700">
                  <Link :href="`/cliente/${cliente.id}/edit`">
                    <Pencil />
                  </Link>
                </Button>
                <Button
                  size="sm"
                  class="bg-rose-500 text-white hover:bg-rose-700"
                  @click="confirmDelete(cliente.id)"
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
    <!-- Diálogo de confirmación antes de eliminar; vinculado con openDialog -->
    <AlertDialog v-model:open="openDialog">
      <AlertDialogContent>
        <AlertDialogHeader>
          <AlertDialogTitle>¿Eliminar cliente?</AlertDialogTitle>
          <AlertDialogDescription>
            Esta acción no se puede deshacer. El cliente será eliminado permanentemente.
          </AlertDialogDescription>
        </AlertDialogHeader>
        <AlertDialogFooter>
          <AlertDialogCancel>Cancelar</AlertDialogCancel>
          <AlertDialogAction @click="deleteCliente" class="bg-rose-600 text-white hover:bg-rose-700">
            Confirmar
          </AlertDialogAction>
        </AlertDialogFooter>
      </AlertDialogContent>
    </AlertDialog>
  </AppLayout>
</template>

